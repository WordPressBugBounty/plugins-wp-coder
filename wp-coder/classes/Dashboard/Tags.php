<?php

namespace WPCoder\Dashboard;

defined( 'ABSPATH' ) || exit;

/**
 * Snippet tags: several per snippet, stored as one comma-separated string in the
 * existing `tag` column.
 *
 * No migration is needed — a value written before multi-tag is simply a list of
 * one. What does matter is that every write goes through normalize(): FIND_IN_SET
 * compares list items literally, so "landing, hero" stores the second item as
 * " hero" and no query would ever match it, while the admin screen would still
 * show the tag as if it were fine.
 *
 * The trade-off: a comma can no longer be part of a tag name.
 */
class Tags {

	public const SEPARATOR = ',';

	/**
	 * Individual tags from a stored value.
	 *
	 * @param mixed $stored Raw column value.
	 * @return string[]
	 */
	public static function split( $stored ): array {
		if ( ! is_string( $stored ) || trim( $stored ) === '' ) {
			return [];
		}

		$tags = array_map( 'trim', explode( self::SEPARATOR, $stored ) );

		return array_values( array_filter( $tags, static fn( $tag ) => $tag !== '' ) );
	}

	/**
	 * Canonical form for storage: trimmed, de-duplicated, joined without spaces.
	 *
	 * @param mixed $raw Whatever the author typed.
	 */
	public static function normalize( $raw ): string {
		$seen = [];
		$out  = [];

		foreach ( self::split( (string) $raw ) as $tag ) {
			// De-duplicate case-insensitively but keep the author's spelling.
			$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $tag ) : strtolower( $tag );

			if ( isset( $seen[ $key ] ) ) {
				continue;
			}

			$seen[ $key ] = true;
			$out[]        = $tag;
		}

		return implode( self::SEPARATOR, $out );
	}

	/** Whether a stored value carries the given tag (case-insensitive, like the SQL). */
	public static function has( $stored, string $tag ): bool {
		$tag = trim( $tag );

		if ( $tag === '' ) {
			return false;
		}

		foreach ( self::split( $stored ) as $one ) {
			if ( strcasecmp( $one, $tag ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Every distinct tag across a set of stored values, sorted for display.
	 *
	 * @param array $stored_values Raw column values.
	 * @return string[]
	 */
	public static function unique( array $stored_values ): array {
		$all = [];

		foreach ( $stored_values as $value ) {
			foreach ( self::split( $value ) as $tag ) {
				$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $tag ) : strtolower( $tag );

				// First spelling wins, so the list doesn't show Landing and landing.
				if ( ! isset( $all[ $key ] ) ) {
					$all[ $key ] = $tag;
				}
			}
		}

		ksort( $all );

		return array_values( $all );
	}
}
