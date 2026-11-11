<?php

namespace WPCoder\Update;

use WPCoder\Dashboard\FolderManager;
use WPCoder\Dashboard\Tags;
use WPCoder\WPCoder;

class UpdateDB {

	const NEW_DB_VERSION = '3.6';

	const TABLE_COLUMNS = "
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			title VARCHAR(200) NOT NULL,
			html_code LONGTEXT,
			css_code LONGTEXT,
			js_code LONGTEXT,
			php_code LONGTEXT,
			param LONGTEXT,
			status BOOLEAN,
			mode BOOLEAN,
			tag TEXT,
			php_include int(11) NOT NULL DEFAULT '0',
			pinned tinyint(1) NOT NULL DEFAULT '0',
			UNIQUE KEY id (id),
			INDEX id_index (id)
	";

	/**
	 * Option keys renamed in 5.0 (the same renames WP Coder Pro made in its 4.0),
	 * grouped by the option that stores them. Snippet and tool states are flat
	 * `key => value` maps, so without this every affected setting would silently
	 * switch off on update.
	 */
	const RENAMED_KEYS = [
		'_wp_coder_snippets' => [
			'disable_XML_RPC'                  => 'disable_xml_rpc',
			'change_oEmbed_size'               => 'change_oembed_size',
			'change_oEmbed_size_width'         => 'change_oembed_size_width',
			'change_oEmbed_size_height'        => 'change_oembed_size_height',
			'disable_comment_from_website_url' => 'disable_comment_form_website_url',
		],
		'_wp_coder_tools'    => [
			'tracking_tool_pintrest' => 'tracking_tool_pinterest',
		],
	];

	/**
	 * Its own flag, not `_db_version`: plugin_activate() hard-writes that to '4.0',
	 * which would pin a version gate shut on every site that was ever re-activated.
	 */
	const OPTION_KEYS_VERSION = '_wp_coder_keys_version';
	const KEYS_VERSION        = '5.0';

	/**
	 * Schema and data changes of 5.0 (the `pinned` column, normalised tags), on a
	 * flag of their own for the same reason as the keys: the `_db_version` gate
	 * above never opens on a site that was activated after 4.0.
	 */
	const OPTION_SCHEMA_VERSION = '_wp_coder_schema_version';
	const SCHEMA_VERSION        = '5.0';

	public static function init(): void {
		self::migrate_option_keys();
		self::migrate_schema();

		$current_db_version = get_option( WPCoder::PREFIX . '_db_version' );

		if ( $current_db_version && version_compare( $current_db_version, self::NEW_DB_VERSION, '>=' ) ) {
			return;
		}

		self::start_update();
		update_option( WPCoder::PREFIX . '_db_version', self::NEW_DB_VERSION );
	}

	private static function start_update(): void {
		self::update_database();
	}

	private static function update_database(): void {
		global $wpdb;

		$table           = $wpdb->prefix . WPCoder::PREFIX;
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE $table (" . self::TABLE_COLUMNS . ") $charset_collate;";
		dbDelta( $sql );
	}

	private static function migrate_schema(): void {
		if ( get_option( self::OPTION_SCHEMA_VERSION ) === self::SCHEMA_VERSION ) {
			return;
		}

		// dbDelta only adds what is missing, so this is safe to repeat — and on a
		// table WP Coder Pro already extended, a no-op.
		self::update_database();

		if ( ! self::has_column( 'pinned' ) ) {
			// Leave the flag unset so the next request tries again. Until then the
			// list reads a missing `pinned` as 0 and pinning simply does nothing.
			return;
		}

		self::normalize_tags();
		self::clear_stale_php_files();
		update_option( self::OPTION_SCHEMA_VERSION, self::SCHEMA_VERSION );
	}

	private static function has_column( string $column ): bool {
		global $wpdb;
		$table = $wpdb->prefix . WPCoder::PREFIX;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		return (bool) $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM $table LIKE %s", $column ) );
	}

	/**
	 * Rewrite stored tags in the canonical form (Tags::normalize()).
	 *
	 * Before 5.0 a tag was one free-form string, so "landing, hero" was a single
	 * tag. It now reads as two, and the filter looks them up with FIND_IN_SET —
	 * which would never find " hero" with its leading space.
	 */
	private static function normalize_tags(): void {
		global $wpdb;
		$table = $wpdb->prefix . WPCoder::PREFIX;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results( "SELECT id, tag FROM $table WHERE tag IS NOT NULL AND tag <> ''" );

		foreach ( (array) $rows as $row ) {
			$normal = Tags::normalize( $row->tag );
			if ( $normal !== $row->tag ) {
				$wpdb->update( $table, [ 'tag' => $normal ], [ 'id' => (int) $row->id ], [ '%s' ], [ '%d' ] );
			}
		}
	}

	/**
	 * Empty the PHP file of every snippet whose PHP code was cleared.
	 *
	 * Before 5.0 clearing the PHP tab left wp-code-{id}.php untouched, and
	 * PHPIncludes runs that file for every row with an include mode — so the
	 * deleted code kept executing. Saving now rewrites it; this catches the
	 * files left over from earlier saves.
	 */
	private static function clear_stale_php_files(): void {
		global $wpdb;
		$table = $wpdb->prefix . WPCoder::PREFIX;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$ids = $wpdb->get_col( "SELECT id FROM $table WHERE php_code IS NULL OR php_code = ''" );

		foreach ( (array) $ids as $id ) {
			$path = FolderManager::path_upload_dir() . 'wp-code-' . absint( $id ) . '.php';
			if ( file_exists( $path ) ) {
				FolderManager::update_code( [ 'php_code' => '' ], absint( $id ) );
			}
		}
	}

	private static function migrate_option_keys(): void {
		if ( get_option( self::OPTION_KEYS_VERSION ) === self::KEYS_VERSION ) {
			return;
		}

		self::rename_legacy_option_keys();
		update_option( self::OPTION_KEYS_VERSION, self::KEYS_VERSION );
	}

	/**
	 * Idempotent: an old key is moved only when present, and never overwrites a
	 * value already stored under the new key (a site that ran WP Coder Pro).
	 */
	public static function rename_legacy_option_keys(): void {
		foreach ( self::RENAMED_KEYS as $option_name => $map ) {
			$stored = get_option( $option_name, [] );

			if ( ! is_array( $stored ) ) {
				continue;
			}

			$changed = false;

			foreach ( $map as $old => $new ) {
				if ( ! array_key_exists( $old, $stored ) ) {
					continue;
				}

				if ( ! array_key_exists( $new, $stored ) ) {
					$stored[ $new ] = $stored[ $old ];
				}

				unset( $stored[ $old ] );
				$changed = true;
			}

			if ( $changed ) {
				update_option( $option_name, $stored );
			}
		}
	}

}
