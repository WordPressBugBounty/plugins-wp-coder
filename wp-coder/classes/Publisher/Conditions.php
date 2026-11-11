<?php

namespace WPCoder\Publisher;


defined( 'ABSPATH' ) || exit;

class Conditions {

	public static function init( $result ): bool {
		$param = ! empty( $result->param ) ? maybe_unserialize( $result->param ) : [];
		$check = [
			'status'   => self::status( $result->status ),
			'mode'     => self::mode( $result->mode ),

		];

		if ( in_array( false, $check, true ) ) {
			return false;
		}

		return true;

	}

	private static function status( $status ): bool {
		return empty( $status );
	}

	private static function mode( $mode ): bool {
		return empty( $mode ) || current_user_can( 'administrator' );
	}

	/**
	 * Whether WP Coder Pro set a visitor condition on this snippet (devices, user
	 * roles, browsers, OS, language, schedule, cookie, referrer, URL, visitor).
	 *
	 * The free version cannot evaluate them, so a snippet that has any is never
	 * auto-displayed here — showing it to everyone would undo the restriction.
	 * The keys and switches mirror Pro's Conditions::init().
	 */
	public static function has_pro_rules( array $param ): bool {
		$switches = [
			'is_mobile',
			'is_desktop',
			'show_operating_systems',
			'cookie_enabled',
			'referrer_enabled',
			'url_enabled',
			'visitor_enabled',
		];

		foreach ( $switches as $key ) {
			if ( ! empty( $param[ $key ] ) ) {
				return true;
			}
		}

		if ( ! empty( $param['item_user'] ) && (string) $param['item_user'] !== '1' ) {
			return true;
		}

		// Pro keeps the browser ticks while their switch is off; a row saved
		// before the switch existed obeys the ticks alone.
		if ( ! empty( $param['browsers'] ) && ( ! array_key_exists( 'hide_browsers', $param ) || ! empty( $param['hide_browsers'] ) ) ) {
			return true;
		}

		if ( ! empty( $param['depending_language'] ) && ! empty( $param['language'] ) ) {
			return true;
		}

		return ! empty( $param['weekday'] ) && is_array( $param['weekday'] );
	}

	/**
	 * Whether the free version displays this snippet automatically: its only
	 * display rule is "everywhere" (what the Conditions tab's Display field writes)
	 * and Pro set no visitor condition. Any other rule — specific pages, post
	 * types, archives — is Pro's to evaluate, so here the snippet stays
	 * shortcode-only, as it was before the free version could auto-display.
	 */
	public static function is_auto( array $param ): bool {
		$show = $param['show'] ?? [];

		if ( ! is_array( $show ) ) {
			return false;
		}

		$rules = array_values( array_filter( array_map( 'strval', $show ), static function ( $rule ) {
			return $rule !== '';
		} ) );

		return $rules === [ 'everywhere' ] && ! self::has_pro_rules( $param );
	}

	/** Whether the snippet's display rules can only be edited in WP Coder Pro. */
	public static function is_pro_display( array $param ): bool {
		$show = ( isset( $param['show'] ) && is_array( $param['show'] ) ) ? $param['show'] : [];

		foreach ( $show as $rule ) {
			if ( ! in_array( (string) $rule, [ '', 'shortcode', 'everywhere' ], true ) ) {
				return true;
			}
		}

		return count( $show ) > 1 || self::has_pro_rules( $param );
	}
}
