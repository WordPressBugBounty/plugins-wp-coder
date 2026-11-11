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

	/**
	 * @param string               $html   The template.
	 * @param array<string, mixed> $values What stands between the braces ("$label",
	 *                                     "label") => its value.
	 *                                     Placeholders not named here are left as written.
	 */
	public static function fill( string $html, array $values ): string {
		if ( $values === [] || ! preg_match_all( '/\{\{([^{}]*)\}\}/', $html, $found, PREG_OFFSET_CAPTURE ) ) {
			return $html;
		}

		$tags = null;
		$out  = '';
		$at   = 0;

		// One pass over the template: a value is never searched for placeholders
		// itself, as the str_replace() for the next name used to.
		foreach ( $found[0] as $i => [ $whole, $offset ] ) {
			$name = $found[1][ $i ][0];
			if ( ! array_key_exists( $name, $values ) ) {
				continue;
			}

			$value = (string) $values[ $name ];
			if ( strpbrk( $value, '<>' ) !== false ) {
				$tags = $tags ?? self::tag_ranges( $html );
				if ( self::inside( $offset, $tags ) ) {
					$value = str_replace( [ '<', '>' ], [ '&lt;', '&gt;' ], $value );
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

	/** @param array<int, array{0: int, 1: int}> $ranges From tag_ranges(). */
	private static function inside( int $offset, array $ranges ): bool {
		foreach ( $ranges as [ $start, $end ] ) {
			if ( $offset < $start ) {
				return false;
			}
			if ( $offset < $end ) {
				return true;
			}
		}

		return false;
	}
}
