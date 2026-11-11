<?php

namespace WPCoder\Publisher;

defined( 'ABSPATH' ) || exit;

use WPCoder\Dashboard\DBManager;

/**
 * Snippets displayed automatically on every page (Display: Everywhere).
 *
 * Stored the way WP Coder Pro stores it — `param[show] = [ 'everywhere' ]` and
 * `param[location]` — so a snippet behaves the same in either version. Pro adds
 * the page, user, device and schedule rules on top.
 */
class Display {

	/** Where the HTML of an auto-displayed snippet can be printed. */
	public const LOCATIONS = [ 'head', 'body_open', 'before_content', 'after_content', 'footer' ];

	private static $cache = null;

	/** @return array Snippet rows keyed by id. */
	public static function auto(): array {
		if ( self::$cache !== null ) {
			return self::$cache;
		}

		self::$cache = [];

		if ( is_admin() ) {
			return self::$cache;
		}

		$rows = DBManager::get_all_data();

		foreach ( (array) $rows as $row ) {
			if ( ! is_object( $row ) || empty( $row->id ) ) {
				continue;
			}

			$param = ! empty( $row->param ) ? maybe_unserialize( $row->param ) : [];

			if ( ! is_array( $param ) || ! Conditions::is_auto( $param ) ) {
				continue;
			}

			if ( Conditions::init( $row ) === false ) {
				continue;
			}

			self::$cache[ (int) $row->id ] = $row;
		}

		return self::$cache;
	}

	/**
	 * The snippet's Location — footer for anything else, which matches Pro: an
	 * auto-displayed snippet saved before the option existed went to the footer.
	 */
	public static function location( $param ): string {
		$location = is_array( $param ) ? (string) ( $param['location'] ?? '' ) : '';

		return in_array( $location, self::LOCATIONS, true ) ? $location : 'footer';
	}
}
