<?php

namespace WPCoder\Publisher;

use DOMDocument;
use DOMElement;

defined( 'ABSPATH' ) || exit;

/**
 * Removes anything executable from SVG markup.
 *
 * Used on upload ("Allow SVG upload" snippet) and again whenever an SVG
 * attribute inlines a file (SvgIcon) — inline markup runs in the page, so a
 * file that reached the media library some other way is checked before use.
 * Fails closed: markup that cannot be parsed returns null.
 */
class SvgSanitizer {

	/** Elements that can execute code or pull in outside content. */
	private const SVG_FORBIDDEN_ELEMENTS = [
		'script', 'foreignobject', 'iframe', 'embed', 'object',
		'handler', 'audio', 'video', 'listener',
	];

	/** SMIL elements — harmless until they retarget a link or an event attribute. */
	private const SVG_ANIMATION_ELEMENTS = [ 'animate', 'set', 'animatetransform', 'animatemotion' ];

	/** Attributes whose value the browser resolves as a URL. */
	private const SVG_URL_ATTRIBUTES = [
		'href', 'xlink:href', 'src', 'action', 'formaction', 'from', 'to', 'by', 'values',
	];

	/**
	 * @param string $svg Raw SVG markup.
	 * @return string|null Sanitised markup, or null when it isn't usable SVG.
	 */
	public static function clean( string $svg ): ?string {
		// loadXML() on an empty string is a ValueError on PHP 8.5, not a false
		// return — an empty file must simply fail.
		if ( trim( $svg ) === '' ) {
			return null;
		}

		// A DOCTYPE is the way in for XXE and entity-expansion attacks, and a real
		// image asset never needs one. The optional [...] group matters: an internal
		// subset contains its own '>' characters, so a lazy match to the first '>'
		// would leave a stray ']>' behind and break otherwise valid artwork —
		// Illustrator exports routinely carry a DOCTYPE.
		$svg = preg_replace( '/<!DOCTYPE\s+[^\[>]*(\[[^\]]*\])?[^>]*>/is', '', $svg );

		if ( $svg === null ) {
			return null;
		}

		$dom                     = new DOMDocument();
		$dom->preserveWhiteSpace = false;

		$previous = libxml_use_internal_errors( true );
		// LIBXML_NONET blocks network fetches. LIBXML_NOENT is deliberately NOT
		// passed — despite the name it *enables* entity substitution.
		$loaded = $dom->loadXML( $svg, LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( ! $loaded || ! $dom->documentElement ) {
			return null;
		}

		if ( strtolower( $dom->documentElement->localName ?: $dom->documentElement->nodeName ) !== 'svg' ) {
			return null;
		}

		self::strip_unsafe_svg_nodes( $dom );

		$out = $dom->saveXML();

		return $out === false ? null : $out;
	}

	private static function strip_unsafe_svg_nodes( DOMDocument $dom ): void {
		// Snapshot first: removing nodes while walking the live list skips siblings.
		foreach ( iterator_to_array( $dom->getElementsByTagName( '*' ), false ) as $element ) {
			if ( ! $element instanceof DOMElement || ! $element->parentNode ) {
				continue;
			}

			$name = strtolower( $element->localName ?: $element->nodeName );

			if ( in_array( $name, self::SVG_FORBIDDEN_ELEMENTS, true ) ) {
				$element->parentNode->removeChild( $element );
				continue;
			}

			// An animation that rewrites a link or an event attribute is a script
			// wearing a different hat.
			if ( in_array( $name, self::SVG_ANIMATION_ELEMENTS, true ) ) {
				$target = strtolower( $element->getAttribute( 'attributeName' ) );

				if ( $target === 'href' || $target === 'xlink:href' || strpos( $target, 'on' ) === 0 ) {
					$element->parentNode->removeChild( $element );
					continue;
				}
			}

			if ( $name === 'style' && self::is_unsafe_css( $element->textContent ) ) {
				$element->parentNode->removeChild( $element );
				continue;
			}

			self::strip_unsafe_svg_attributes( $element );
		}
	}

	private static function strip_unsafe_svg_attributes( DOMElement $element ): void {
		if ( ! $element->hasAttributes() ) {
			return;
		}

		foreach ( iterator_to_array( $element->attributes, false ) as $attribute ) {
			$name  = strtolower( $attribute->nodeName );
			$value = (string) $attribute->nodeValue;

			// Every inline event handler: onload, onclick, onerror, onbegin…
			if ( strpos( $name, 'on' ) === 0 ) {
				$element->removeAttributeNode( $attribute );
				continue;
			}

			if ( in_array( $name, self::SVG_URL_ATTRIBUTES, true ) && self::is_unsafe_url( $value ) ) {
				$element->removeAttributeNode( $attribute );
				continue;
			}

			if ( ( $name === 'style' || $name === 'filter' ) && self::is_unsafe_css( $value ) ) {
				$element->removeAttributeNode( $attribute );
			}
		}
	}

	/** Browsers ignore whitespace and control characters when resolving a scheme. */
	private static function normalise_for_scheme_check( string $value ): string {
		return strtolower( (string) preg_replace( '/[\s\x00-\x20]+/', '', $value ) );
	}

	private static function is_unsafe_url( string $value ): bool {
		$value = self::normalise_for_scheme_check( $value );

		if ( $value === '' ) {
			return false;
		}

		if ( strpos( $value, 'javascript:' ) === 0 || strpos( $value, 'vbscript:' ) === 0 ) {
			return true;
		}

		if ( strpos( $value, 'data:' ) === 0 ) {
			// A nested SVG can carry its own script, so only bitmap data URLs pass.
			return strpos( $value, 'data:image/' ) !== 0 || strpos( $value, 'data:image/svg' ) === 0;
		}

		return false;
	}

	private static function is_unsafe_css( string $value ): bool {
		$value = self::normalise_for_scheme_check( $value );

		return strpos( $value, 'javascript:' ) !== false
			|| strpos( $value, 'expression(' ) !== false
			|| strpos( $value, '@import' ) !== false
			|| strpos( $value, '-moz-binding' ) !== false;
	}
}
