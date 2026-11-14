<?php

namespace WPCoder\Publisher;

defined( 'ABSPATH' ) || exit;

/**
 * Writes values into the {{…}} placeholders of a snippet's HTML
 * (WOWP_Public::replace_placeholders()) — a copy of WP Coder Pro's.
 *
 * A value placed inside a tag is in an attribute, and there it keeps no raw "<"
 * or ">". An HTML or Rich text attribute of a snippet from Pro keeps its tags
 * (WOWP_Public::sanitize_attrs()), and inside a block WordPress then runs
 * wptexturize() over the rendered output, which took the ">" of `<b>` for the
 * end of the tag, turned the attribute's closing quote into “ — and the rest of
 * the page went into the attribute. As &lt; / &gt; the attribute holds the very
 * same text.
 */
class Placeholders {

	/** Inside a tag a value keeps no raw "<" or ">". */
	private const IN_TAG = [ '<' => '&lt;', '>' => '&gt;' ];

	/** …an untrusted one in a quoted value no quote either: kses keeps a `"` that is text. */
	private const IN_QUOTES_UNTRUSTED = [ '<' => '&lt;', '>' => '&gt;', '"' => '&quot;', "'" => '&#039;' ];

	/**
	 * …and anywhere else in the tag no whitespace too, which ends an unquoted
	 * value (class={{x}}) — esc_attr() keeps it. The browser decodes all of
	 * these back in an attribute value.
	 */
	private const IN_TAG_UNTRUSTED = [
		'<'  => '&lt;',
		'>'  => '&gt;',
		'"'  => '&quot;',
		"'"  => '&#039;',
		' '  => '&#32;',
		"\t" => '&#9;',
		"\n" => '&#10;',
		"\r" => '&#13;',
		"\f" => '&#12;',
	];

	/**
	 * @param string               $html      The template.
	 * @param array<string, mixed> $values    What stands between the braces ("$label",
	 *                                        "label") => its value.
	 *                                        Placeholders not named here are left as written.
	 * @param string[]             $untrusted Keys of $values that came from someone who may
	 *                                        not write raw HTML (a block, a Contributor's
	 *                                        shortcode). In title="{{label}}" a quote of
	 *                                        theirs closed the attribute.
	 */
	public static function fill( string $html, array $values, array $untrusted = [] ): string {
		if ( $values === [] || ! preg_match_all( '/\{\{([^{}]*)\}\}/', $html, $found, PREG_OFFSET_CAPTURE ) ) {
			return $html;
		}

		$tags      = null;
		$out       = '';
		$at        = 0;
		$untrusted = array_flip( $untrusted );

		// One pass over the template: a value is never searched for placeholders
		// itself, as the str_replace() for the next name used to.
		foreach ( $found[0] as $i => [ $whole, $offset ] ) {
			$name = $found[1][ $i ][0];
			if ( ! array_key_exists( $name, $values ) ) {
				continue;
			}

			$value  = (string) $values[ $name ];
			$escape = isset( $untrusted[ $name ] ) ? self::IN_TAG_UNTRUSTED : self::IN_TAG;
			if ( strpbrk( $value, implode( '', array_keys( $escape ) ) ) !== false ) {
				$tags  = $tags ?? self::tag_ranges( $html );
				$start = self::tag_start( $offset, $tags );
				if ( $start !== null ) {
					if ( $escape === self::IN_TAG_UNTRUSTED && self::in_quotes( $html, $start, $offset ) ) {
						$escape = self::IN_QUOTES_UNTRUSTED;
					}
					$value = strtr( $value, $escape );
				}
			}

			$out .= substr( $html, $at, $offset - $at ) . $value;
			$at   = $offset + strlen( $whole );
		}

		return $out . substr( $html, $at );
	}

	/**
	 * Where the tags of a template are, as [start, end) byte offsets — a value
	 * placed in one sits in an attribute. A quoted attribute value may hold ">";
	 * a comment is not a tag; the content of a script or a style is text, so
	 * `a < b` there starts nothing.
	 *
	 * @return array<int, array{0: int, 1: int}> In order.
	 */
	public static function tag_ranges( string $html ): array {
		$ranges = [];
		$length = strlen( $html );
		$at     = 0;

		while ( $at < $length && ( $start = strpos( $html, '<', $at ) ) !== false ) {
			if ( substr_compare( $html, '<!--', $start, 4 ) === 0 ) {
				$close = strpos( $html, '-->', $start + 4 );
				$at    = $close === false ? $length : $close + 3;
				continue;
			}

			if ( ! preg_match( '#</?([a-z][a-z0-9:-]*)#iA', $html, $m, 0, $start ) ) {
				$at = $start + 1;
				continue;
			}

			// To the ">" that is not inside a quoted value.
			$end = $start + strlen( $m[0] );
			while ( $end < $length ) {
				$end += strcspn( $html, '"\'>', $end );
				if ( $end >= $length || $html[ $end ] === '>' ) {
					break;
				}
				$close = strpos( $html, $html[ $end ], $end + 1 );
				$end   = $close === false ? $length : $close + 1;
			}

			$ranges[] = [ $start, min( $end + 1, $length ) ];
			$at       = $end + 1;

			$name = strtolower( $m[1] );
			if ( $m[0][1] !== '/' && ( $name === 'script' || $name === 'style' ) ) {
				$close = stripos( $html, '</' . $name, $at );
				$at    = $close === false ? $length : $close;
			}
		}

		return $ranges;
	}

	/**
	 * Where the tag holding $offset starts, or null outside every tag.
	 *
	 * @param array<int, array{0: int, 1: int}> $ranges From tag_ranges().
	 */
	private static function tag_start( int $offset, array $ranges ): ?int {
		foreach ( $ranges as [ $start, $end ] ) {
			if ( $offset < $start ) {
				return null;
			}
			if ( $offset < $end ) {
				return $start;
			}
		}

		return null;
	}

	/** Whether $offset, in the tag that starts at $start, is in a quoted value — read as tag_ranges() does. */
	private static function in_quotes( string $html, int $start, int $offset ): bool {
		$at = $start;
		while ( true ) {
			$at += strcspn( $html, '"\'', $at, $offset - $at );
			if ( $at >= $offset ) {
				return false;
			}

			$close = strpos( $html, $html[ $at ], $at + 1 );
			if ( $close === false || $close > $offset ) {
				return true;
			}
			$at = $close + 1;
		}
	}
}
