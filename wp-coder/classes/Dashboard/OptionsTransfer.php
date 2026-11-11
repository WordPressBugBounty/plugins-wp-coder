<?php

namespace WPCoder\Dashboard;

defined( 'ABSPATH' ) || exit;

use WPCoder\Update\UpdateDB;
use WPCoder\WPCoder;

/**
 * Export / import of the Snippets and Tools pages' settings — the
 * `_wp_coder_snippets` and `_wp_coder_tools` options — to move them between
 * sites.
 *
 * The file is a JSON object, unlike a snippet export (a list of rows), so the
 * one Import form tells them apart (ImporterExporter::import_data()):
 *
 * Same format as WP Coder Pro's OptionsTransfer (a copy of it): a file made
 * by either plugin imports into the other — they share both options.
 *
 *     { "format": "wpcoder-settings", "version": "5.0",
 *       "snippets": { "disable_emojis": "1", … }, "tools": { … } }
 *
 * A section present in the file replaces that option whole: both are flat
 * maps where an absent key means "off", so merging could only ever turn
 * things on. A section left out of the file is not touched.
 */
class OptionsTransfer {

	public const FORMAT = 'wpcoder-settings';

	/** Section in the file => option it holds. */
	public const SECTIONS = [
		'snippets' => '_wp_coder_snippets',
		'tools'    => '_wp_coder_tools',
	];

	/**
	 * Never leave the site, and never arrive: an import keeps the receiving
	 * site's own value. Pro's AI key (billed to its owner) shares this option.
	 */
	public const SECRET_KEYS = [
		'tools' => [ 'ai_api_key' ],
	];

	/** Multi-line values, sanitised as such (as Pro's ToolsManager::send() does). */
	private const TEXTAREA_KEYS = [
		'tools' => [ 'global_vars' ],
	];

	/**
	 * The file's content.
	 *
	 * @param array<string, array> $options  Section => current option value.
	 * @param string[]             $sections Sections to include.
	 */
	public static function build( array $options, array $sections, string $version ): array {
		$data = [
			'format'  => self::FORMAT,
			'version' => $version,
		];

		foreach ( array_keys( self::SECTIONS ) as $section ) {
			if ( ! in_array( $section, $sections, true ) ) {
				continue;
			}
			$value = is_array( $options[ $section ] ?? null ) ? $options[ $section ] : [];
			foreach ( self::SECRET_KEYS[ $section ] ?? [] as $secret ) {
				unset( $value[ $secret ] );
			}
			// An object even when empty: `[]` would read back as "no settings"
			// just the same, but `{}` keeps the file's shape predictable.
			$data[ $section ] = (object) $value;
		}

		return $data;
	}

	/** Whether a decoded JSON file is a settings export rather than snippet rows. */
	public static function is_settings_file( $decoded ): bool {
		return is_object( $decoded ) && ( $decoded->format ?? '' ) === self::FORMAT;
	}

	/**
	 * The sections a decoded settings file carries, cleaned and ready to store.
	 *
	 * Keys renamed since the file was made are brought up to date
	 * (UpdateDB::RENAMED_KEYS), values are sanitised as the settings pages
	 * sanitise them, and secret keys are dropped.
	 *
	 * @param object $decoded json_decode() output, objects not arrays.
	 *
	 * @return array<string, array> Section => option value.
	 */
	public static function read( $decoded ): array {
		$result = [];

		foreach ( self::SECTIONS as $section => $option ) {
			if ( ! isset( $decoded->$section ) || ! is_object( $decoded->$section ) ) {
				continue;
			}

			$value = json_decode( (string) wp_json_encode( $decoded->$section ), true );
			$value = is_array( $value ) ? $value : [];

			$clean = [];
			foreach ( $value as $key => $item ) {
				$key = (string) $key;
				$key = UpdateDB::RENAMED_KEYS[ $option ][ $key ] ?? $key;

				if ( in_array( $key, self::SECRET_KEYS[ $section ] ?? [], true ) ) {
					continue;
				}

				if ( in_array( $key, self::TEXTAREA_KEYS[ $section ] ?? [], true ) && is_string( $item ) ) {
					$clean[ $key ] = sanitize_textarea_field( $item );
				} elseif ( is_scalar( $item ) || is_array( $item ) ) {
					$clean[ $key ] = map_deep( $item, 'sanitize_text_field' );
				}
			}

			$result[ $section ] = $clean;
		}

		return $result;
	}

	/**
	 * Store what read() returned.
	 *
	 * @param array<string, array> $sections
	 *
	 * @return string[] Sections written.
	 */
	public static function apply( array $sections ): array {
		$written = [];

		foreach ( $sections as $section => $value ) {
			$option = self::SECTIONS[ $section ] ?? null;
			if ( $option === null ) {
				continue;
			}

			$current = get_option( $option, [] );
			$current = is_array( $current ) ? $current : [];
			foreach ( self::SECRET_KEYS[ $section ] ?? [] as $secret ) {
				if ( isset( $current[ $secret ] ) ) {
					$value[ $secret ] = $current[ $secret ];
				}
			}

			update_option( $option, $value );
			$written[] = $section;
		}

		return $written;
	}

	/** Download the file for the sections ticked on the Import / Export page. */
	public static function export(): void {
		$sections = isset( $_POST['wowp_settings_sections'] ) && is_array( $_POST['wowp_settings_sections'] )
			? array_values( array_intersect( array_keys( self::SECTIONS ), array_map( 'strval', wp_unslash( $_POST['wowp_settings_sections'] ) ) ) )
			: [];

		if ( empty( $sections ) ) {
			wp_die( esc_html__( 'Choose Snippets, Tools or both to export.', 'wp-coder' ), esc_html__( 'Error', 'wp-coder' ),
				[ 'response' => 400, 'back_link' => true ] );
		}

		$options = [];
		foreach ( $sections as $section ) {
			$options[ $section ] = get_option( self::SECTIONS[ $section ], [] );
		}

		$file_name = sanitize_file_name( WPCoder::info( 'data' ) . '-settings-' . implode( '-', $sections ) . '-' . gmdate( 'Y-m-d-H-i-s' ) . '.json' );

		ignore_user_abort( true );
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		header( 'Expires: 0' );

		echo wp_json_encode( self::build( $options, $sections, (string) WPCoder::info( 'version' ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/** The export form on the Import / Export page. */
	public static function form_export(): void {
		?>
        <form method="post">
            <p>
                <label>
                    <input type="checkbox" name="wowp_settings_sections[]" value="snippets" checked>
					<?php esc_html_e( 'Snippets', 'wp-coder' ); ?>
                </label>
                &nbsp;&nbsp;
                <label>
                    <input type="checkbox" name="wowp_settings_sections[]" value="tools" checked>
					<?php esc_html_e( 'Tools', 'wp-coder' ); ?>
                </label>
            </p>
            <p class="description">
				<?php esc_html_e( 'The file also imports into WP Coder Pro.', 'wp-coder' ); ?>
            </p>
            <p>
				<?php submit_button( __( 'Export Settings', 'wp-coder' ), 'secondary', 'submit', false ); ?>
				<?php wp_nonce_field( WPCoder::PREFIX . '_nonce', WPCoder::PREFIX . '_export_options' ); ?>
            </p>
        </form>
		<?php
	}
}
