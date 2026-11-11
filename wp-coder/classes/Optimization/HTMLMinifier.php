<?php

namespace WPCoder\Optimization;

/**
 * Whitespace and comment minifier for snippet HTML output.
 *
 * Only changes that cannot alter what the browser renders or runs:
 *
 * - `<pre>`, `<textarea>`, `<script>` and `<style>` are kept byte for byte —
 *   whitespace there is content, and their bodies are not HTML at all.
 * - Attribute values are never touched; only the gaps between attributes are.
 *   Tags are matched quote-aware, so a `>` inside a value cannot end one.
 * - A run of whitespace becomes one space, never nothing. In normal flow a
 *   run renders as a single space, so this is invisible; removing it is not —
 *   `<b>big</b>⏎<i>world</i>` would read "bigworld", and inline-block items
 *   would lose their gaps.
 * - Comments go, except IE conditional comments and `noindex` markers.
 * - Entities such as `&nbsp;` are left alone.
 *
 * The one known difference: an element styled `white-space: pre` in CSS has
 * its runs collapsed too, which no HTML-only pass can detect.
 */
class HTMLMinifier {

	public static function minify( $input ): string {
		$input = (string) $input;
		if ( trim( $input ) === '' ) {
			return $input;
		}

		// NUL marks the placeholders below; it is not valid in HTML anyway.
		$html = str_replace( [ "\0", "\r\n" ], [ '', "\n" ], $input );
		$kept = [];

		$keep = static function ( string $chunk ) use ( &$kept ): string {
			$kept[] = $chunk;

			return "\0" . ( count( $kept ) - 1 ) . "\0";
		};

		// 1. Raw-text elements, verbatim. First, so a "<!--" or "<div  >" inside
		//    a script string is never mistaken for markup.
		$html = preg_replace_callback(
			'#<(pre|textarea|script|style)\b(?:"[^"]*"|\'[^\']*\'|[^\'">])*>.*?</\1\s*>#is',
			static fn( $m ) => $keep( $m[0] ),
			$html
		);

		// 2. Comments.
		$html = preg_replace_callback(
			'#<!--(.*?)-->#s',
			static function ( $m ) use ( $keep ) {
				$body = $m[1];
				if ( preg_match( '#^(\[if\s|<!\[endif\]|\s*/?noindex\s*$)#i', $body ) ) {
					return $keep( $m[0] );
				}

				return '';
			},
			$html
		);

		// 3. Tags: rebuild the attribute list with single spaces, values as written.
		$html = preg_replace_callback(
			'#<([a-zA-Z][a-zA-Z0-9:._-]*)((?:"[^"]*"|\'[^\']*\'|[^\'">])*)>#s',
			static fn( $m ) => $keep( '<' . $m[1] . self::attributes( $m[2] ) . '>' ),
			$html
		);

		// 4. Text: every whitespace run becomes one space. trim() is given its
		//    characters because its default set includes NUL, the placeholder mark.
		$html = trim( preg_replace( '#\s+#', ' ', $html ), " \t\n\r\f\v" );

		// Restore; a kept comment can itself hold a kept script, hence the loop.
		do {
			// \x00 as a PCRE escape: a literal NUL byte in a pattern is an error before PHP 8.2.
			$html = preg_replace_callback( '#\x00(\d+)\x00#', static fn( $m ) => $kept[ (int) $m[1] ], $html, -1, $count );
		} while ( $count > 0 );

		return $html;
	}

	/**
	 * The part of a start tag after its name, with each attribute separated by
	 * exactly one space. Quoted values keep their inner whitespace untouched.
	 */
	private static function attributes( string $raw ): string {
		if ( trim( $raw ) === '' ) {
			return '';
		}

		preg_match_all( '#([^\s"\'>/=]+)(?:\s*=\s*("[^"]*"|\'[^\']*\'|[^\s"\'>]+))?|(/)#', $raw, $parts, PREG_SET_ORDER );

		$out = '';
		foreach ( $parts as $part ) {
			if ( ! empty( $part[3] ) ) {
				$out .= '/';
				continue;
			}
			$out .= ' ' . $part[1] . ( isset( $part[2] ) && $part[2] !== '' ? '=' . $part[2] : '' );
		}

		return $out;
	}
}
