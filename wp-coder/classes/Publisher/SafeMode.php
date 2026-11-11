<?php

namespace WPCoder\Publisher;

use WPCoder\WPCoder;

defined( 'ABSPATH' ) || exit;

/**
 * Safe mode: Global PHP and "PHP include" files are skipped for the request
 * (PHPIncludes::include_php()), so the admin stays reachable when that PHP breaks it.
 *
 * Same rule as WP Coder Pro (WPCoderPro\Publisher\SafeMode::is_safe_mode()), and the
 * same constant, so one line in wp-config.php covers both.
 */
class SafeMode {

	/** Defined true in wp-config.php: snippet PHP is skipped for every request. */
	public const CONSTANT = 'WPCODER_SAFE_MODE';

	/**
	 * Whether this request skips snippet PHP: ?wpcoder-safe-mode=1 from someone
	 * who may manage the plugin, or the WPCODER_SAFE_MODE constant.
	 *
	 * The parameter used to work for anyone: a visitor could switch Global PHP
	 * off for their own request — redirects, access rules, security headers
	 * written there. Locked out and logged out, the constant is the way back
	 * (access to wp-config.php is the proof). A link to the admin with the
	 * parameter still works through the login screen, which hands it back after
	 * signing in.
	 */
	public static function is_safe_mode(): bool {
		if ( self::by_constant() ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only recovery flag.
		if ( ! isset( $_GET['wpcoder-safe-mode'] ) || 1 !== absint( $_GET['wpcoder-safe-mode'] ) ) {
			return false;
		}

		// Asked from plugins_loaded on, when pluggable.php — the current user — is loaded.
		return function_exists( 'wp_get_current_user' ) && WPCoder::user_can_manage();
	}

	/** Safe mode set in wp-config.php: no URL parameter to keep, no button to leave it. */
	public static function by_constant(): bool {
		return defined( self::CONSTANT ) && constant( self::CONSTANT );
	}
}
