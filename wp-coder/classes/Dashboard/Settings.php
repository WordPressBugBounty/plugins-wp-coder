<?php

namespace WPCoder\Dashboard;

defined( 'ABSPATH' ) || exit;

use WPCoder\WPCoder;

class Settings {

	/**
	 * Tabs a user may hide while they are empty: short key => tab file.
	 * Conditions is not among them — it says where and to whom the snippet is
	 * shown.
	 */
	public const HIDEABLE_TABS = [
		'html'       => 'html-code',
		'css'        => 'css-code',
		'js'         => 'js-code',
		'php'        => 'php-code',
		'attributes' => 'attributes',
		'include'    => 'include',
	];

	/**
	 * The WordPress user setting that holds them: hidden keys joined by "_",
	 * or "none". A view preference, so it lives with the user, not the snippet.
	 * Same name and format as WP Coder Pro, so the choice carries over.
	 * Not "-": wp_user_settings() drops it when it copies the cookie into user
	 * meta, so "js-php" came back from another browser as "jsphp".
	 */
	public const TABS_SETTING = 'wpcoder_hidden_tabs';

	/**
	 * What each tab's label tells about its content: a dot on a code tab that
	 * holds code, the number of attributes on Attributes. Kept live while
	 * editing by admin.js (code) and admin-jquery.js (attribute rows).
	 * Same as WP Coder Pro's Settings::tab_marks().
	 *
	 * @param array $default Field::getDefault().
	 *
	 * @return array<string, array{class:string, count:int}> Keyed by the tab's file.
	 */
	public static function tab_marks( array $default ): array {
		$marks = [];

		$code_tabs = [ 'html-code' => 'html_code', 'css-code' => 'css_code', 'js-code' => 'js_code', 'php-code' => 'php_code' ];
		foreach ( $code_tabs as $file => $column ) {
			if ( trim( (string) ( $default[ $column ] ?? '' ) ) !== '' ) {
				$marks[ $file ] = [ 'class' => 'has-content', 'count' => 0 ];
			}
		}

		$attributes = $default['param']['shortcode_attribute'] ?? [];
		$count      = is_array( $attributes ) ? count( $attributes ) : 0;
		if ( $count > 0 ) {
			$marks['attributes'] = [ 'class' => 'has-count', 'count' => $count ];
		}

		// Files only: removed handles are set up in WP Coder Pro and not shown here.
		$files = is_array( $default['param']['include'] ?? null ) ? count( $default['param']['include'] ) : 0;
		if ( $files > 0 ) {
			$marks['include'] = [ 'class' => 'has-count', 'count' => $files ];
		}

		return $marks;
	}

	/**
	 * The current user's hidden tabs, or null if they never chose — then each
	 * snippet's own flags (param[hide_*], how Hide Tabs used to be saved) still
	 * decide, and admin-jquery.js stores them as the user's choice.
	 *
	 * @return string[]|null Keys of HIDEABLE_TABS.
	 */
	public static function tabs_preference(): ?array {
		$value = get_user_setting( self::TABS_SETTING );

		if ( ! is_string( $value ) || $value === '' ) {
			return null;
		}

		return array_values( array_intersect( array_keys( self::HIDEABLE_TABS ), preg_split( '/[_-]/', $value ) ) );
	}

	/**
	 * Tabs to hide on this snippet: those the user hides, unless they hold
	 * something — a hidden tab with code still runs that code, out of sight.
	 *
	 * @param array         $default    Field::getDefault().
	 * @param string[]|null $preference tabs_preference().
	 *
	 * @return string[] Keys of HIDEABLE_TABS.
	 */
	public static function hidden_tabs( array $default, ?array $preference ): array {
		$param = is_array( $default['param'] ?? null ) ? $default['param'] : [];

		if ( $preference === null ) {
			$preference = array_values( array_filter( array_keys( self::HIDEABLE_TABS ), static function ( $key ) use ( $param ) {
				return ! empty( $param[ 'hide_' . $key ] );
			} ) );
		}

		$marks = self::tab_marks( $default );

		return array_values( array_filter( $preference, static function ( $key ) use ( $marks ) {
			return isset( self::HIDEABLE_TABS[ $key ] ) && empty( $marks[ self::HIDEABLE_TABS[ $key ] ] );
		} ) );
	}


	/**
	 * The hint under a code editor ("Just enter the HTML content…"): read once,
	 * then only space. Its × hides it for good for this user — a WordPress user
	 * setting (setUserSetting() in admin-jquery.js, the wp-settings cookie synced
	 * to user meta), so a dismissed hint is never printed. Same as WP Coder Pro,
	 * and the same setting names, so dismissing it in one hides it in both.
	 *
	 * @param string $key  Short name: html, css, js, php.
	 * @param string $html The hint; inline markup only.
	 */
	public static function editor_hint( string $key, string $html ): void {
		$setting = 'wpcoder_hint_' . $key;

		if ( get_user_setting( $setting ) ) {
			return;
		}
		?>
        <div class="wowp-notification notification-info wowp-editor-hint" data-hint="<?php echo esc_attr( $setting ); ?>">
            <div class="wowp-editor-hint__text"><?php echo wp_kses_post( $html ); ?></div>
            <button type="button" class="wowp-editor-hint__close"
                    title="<?php esc_attr_e( 'Got it — do not show again', 'wp-coder' ); ?>"
                    aria-label="<?php esc_attr_e( 'Got it — do not show again', 'wp-coder' ); ?>">
                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
            </button>
        </div>
		<?php
	}

	public static function init(): void {
		$pages = DashboardHelper::get_files( 'settings' );

		$default = Field::getDefault();

		$marks      = self::tab_marks( $default );
		$preference = self::tabs_preference();
		$hidden     = self::hidden_tabs( $default, $preference );
		$hidden_files = array_map( static function ( $key ) {
			return self::HIDEABLE_TABS[ $key ];
		}, $hidden );

		// The remembered tab, unless it is hidden now: then the first one shown.
		$tab = ! empty( $default['param']['tab'] ) ? $default['param']['tab'] : 'html-code';
		if ( in_array( $tab, $hidden_files, true ) ) {
			foreach ( $pages as $page ) {
				if ( ! in_array( $page['file'], $hidden_files, true ) ) {
					$tab = $page['file'];
					break;
				}
			}
		}

		$i = 0;
		echo '<div class="wowp-settins__tabs" id="wowp-settings-tabs">';
		foreach ( $pages as $key => $page ) {
			$mark      = $marks[ $page['file'] ] ?? null;
			$is_hidden = in_array( $page['file'], $hidden_files, true );

			// A hidden tab's radio is disabled too, so arrow keys skip it.
			echo '<input type="radio" class="wowp-settins__tabs-radio" name="param[tab]" value="' . esc_attr( $page['file'] ) . '" id="wowp-tab-' . esc_attr( $page['file'] ) . '" ' . checked( $tab, $page['file'], false ) . disabled( $is_hidden, true, false ) . '>';
			echo '<label class="wowp-settins__tabs-tab' . ( $mark ? ' ' . esc_attr( $mark['class'] ) : '' ) . ( $is_hidden ? ' is-hidden' : '' ) . '"'
			     . ' for="wowp-tab-' . esc_attr( $page['file'] ) . '"'
			     . ( ! empty( $mark['count'] ) ? ' data-count="' . absint( $mark['count'] ) . '"' : '' ) . '>'
			     . esc_html( $page['name'] ) . '</label>';

			$i ++;
		}
		?>

        <?php
        // "Customize tabs": which tabs to show while they are empty. A user
        // preference (TABS_SETTING, set by admin-jquery.js), applied at once — no
        // Save, no revision, no "Unsaved changes". It used to be saved with each
        // snippet (param[hide_*]), so it was repeated snippet by snippet, and it
        // could hide a tab whose code was still running. A tab that holds
        // something is always shown; its switch says why.
        $tab_names = [
	        'html'       => __( 'HTML', 'wp-coder' ),
	        'css'        => __( 'CSS', 'wp-coder' ),
	        'js'         => __( 'JS', 'wp-coder' ),
	        'php'        => __( 'PHP', 'wp-coder' ),
	        'attributes' => __( 'Attributes', 'wp-coder' ),
	        'include'    => __( 'Scripts & Styles', 'wp-coder' ),
        ];
        $hidden_count = count( $hidden );
        // What the user hides — their setting, or this snippet's old flags until
        // they have one (admin-jquery.js then stores it: data-seed).
        $chosen = $preference ?? array_values( array_filter( array_keys( self::HIDEABLE_TABS ), static function ( $key ) use ( $default ) {
	        return ! empty( $default['param'][ 'hide_' . $key ] );
        } ) );
        ?>
        <div class="popover-container">
            <button type="button" id="popover-toggle" class="wowp-button button button-secondary"
                    aria-expanded="false" aria-controls="popover-box">
                <?php esc_html_e( 'Customize tabs', 'wp-coder' ); ?>
                <span class="wowp-hidden-tabs" id="wowp-hidden-tabs"
                      data-label="<?php /* translators: %d: number of hidden tabs, filled in by the script */ esc_attr_e( '%d hidden', 'wp-coder' ); ?>"<?php echo $hidden_count ? '' : ' hidden'; ?>><?php
	                /* translators: %d: number of hidden tabs */
	                echo esc_html( sprintf( __( '%d hidden', 'wp-coder' ), $hidden_count ) ); ?></span>
            </button>

            <div class="popover wowp-tabs-popover" id="popover-box" role="dialog" aria-labelledby="wowp-tabs-popover-title" hidden
                 data-setting="<?php echo esc_attr( self::TABS_SETTING ); ?>"
                 data-hidden="<?php echo esc_attr( implode( '_', $chosen ) ); ?>"
                 data-seed="<?php echo $preference === null ? '1' : '0'; ?>">
                <p class="wowp-tabs-popover__title" id="wowp-tabs-popover-title"><?php esc_html_e( 'Tabs to show', 'wp-coder' ); ?></p>
	            <?php foreach ( self::HIDEABLE_TABS as $tab_key => $tab_file ) :
		            $locked = ! empty( $marks[ $tab_file ] );
		            $code   = in_array( $tab_key, [ 'html', 'css', 'js', 'php' ], true );
		            ?>
                    <div class="wowp-field has-checkbox wowp-tabs-popover__row<?php echo $locked ? ' is-locked' : ''; ?>">
                        <span class="wowp-tabs-popover__name" id="wowp-tab-name-<?php echo esc_attr( $tab_key ); ?>">
                            <?php echo esc_html( $tab_names[ $tab_key ] ); ?>
                            <span class="wowp-tabs-popover__note"><?php echo $code ? esc_html__( 'has code', 'wp-coder' ) : esc_html__( 'in use', 'wp-coder' ); ?></span>
                        </span>
                        <label class="switch">
                            <input type="checkbox" class="wowp-tab-toggle"
                                   data-tab="<?php echo esc_attr( $tab_key ); ?>" data-file="<?php echo esc_attr( $tab_file ); ?>"
                                   aria-labelledby="wowp-tab-name-<?php echo esc_attr( $tab_key ); ?>"
		                           <?php checked( ! in_array( $tab_key, $hidden, true ) ); ?><?php disabled( $locked ); ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
	            <?php endforeach; ?>
                <div class="wowp-field has-checkbox wowp-tabs-popover__row is-locked">
                    <span class="wowp-tabs-popover__name" id="wowp-tab-name-settings">
                        <?php esc_html_e( 'Conditions', 'wp-coder' ); ?>
                        <span class="wowp-tabs-popover__note"><?php esc_html_e( 'always shown', 'wp-coder' ); ?></span>
                    </span>
                    <label class="switch">
                        <input type="checkbox" checked disabled aria-labelledby="wowp-tab-name-settings">
                        <span class="slider"></span>
                    </label>
                </div>
                <p class="wowp-tabs-popover__foot"><?php esc_html_e( 'Applies to all your snippets. A tab that holds code, attributes or files is always shown.', 'wp-coder' ); ?></p>
            </div>
        </div>

		<?php

		$i = 0;
		foreach ( $pages as $key => $page ) {
			$file = DashboardHelper::get_folder_path( 'settings' ) . '/' . $key . '.' . $page['file'] . '.php';

			if ( file_exists( $file ) ) {

				echo '<div class="wowp-settins__tabs-content" data-content="wowp-tab-' . esc_attr( $page['file'] ) . '">';

				require_once $file;

				echo '</div>';
			}
			$i ++;
		}
		echo '</div>';

		self::save_bar();
	}

	/**
	 * One Save for every tab, kept at the bottom of the window while the card is
	 * on screen — each tab used to end in its own button, far below a long
	 * editor or attribute list. The status is filled by admin.js from the same
	 * check as the "Leave site?" prompt. Same markup as WP Coder Pro.
	 */
	private static function save_bar(): void {
		?>
        <div class="wowp-savebar">
            <span class="wowp-savebar__status" id="wowp-savebar-status" aria-live="polite"
                  data-dirty="<?php esc_attr_e( 'Unsaved changes', 'wp-coder' ); ?>"></span>
            <kbd class="wowp-savebar__key" id="wowp-savebar-key">Ctrl + S</kbd>
            <input type="submit" name="submit_settings" id="submit_settings" class="wowp-button button button-dark"
                   value="<?php esc_attr_e( 'Save', 'wp-coder' ); ?>">
        </div>
		<?php
	}


	public static function save_item() {

		if ( empty( $_POST['submit_settings'] ) ) {
			return false;
		}

		$id       = absint( $_POST['tool_id'] );
		$settings = apply_filters( WPCoder::PREFIX . '_save_settings', [ 'data' => [], 'formats' => [] ], $_POST );

		if ( empty( $id ) ) {
			$id_item = DBManager::insert( $settings['data'], $settings['formats'] );
		} else {
			$where = [
				'id' => absint( $id ),
			];
			DBManager::update( $settings['data'], $where, $settings['formats'] );
			$id_item = $id;
		}

		FolderManager::update_files( $settings['data'], $id_item );

		wp_safe_redirect( Link::save_item( $id_item ) );
		exit;

	}

	/**
	 * The snippet a toggle acts on: the id passed in, else the row link's `?id=`.
	 *
	 * The passed id has to win. Bulk actions call these once per ticked row, and
	 * the list form posts back to the current URL — which, right after a row
	 * link, still carries that link's `id`. Reading `$_GET` first applied every
	 * bulk toggle to that one snippet and left the ticked ones untouched.
	 */
	private static function target_id( $id ): int {
		$id = absint( $id );
		if ( $id ) {
			return $id;
		}

		return isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	}

	public static function deactivate_item( $id = 0 ): void {
		$id = self::target_id( $id );

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'status' => '1' ], [ 'ID' => $id ], [ '%d' ] );
		}

	}

	public static function activate_item( $id = 0 ): void {
		$id = self::target_id( $id );

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'status' => '' ], [ 'ID' => $id ], [ '%d' ] );
		}

	}

	public static function deactivate_mode( $id = 0 ): void {
		$id = self::target_id( $id );

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'mode' => '' ], [ 'ID' => $id ], [ '%d' ] );
		}

	}

	public static function activate_mode( $id = 0 ): void {
		$id = self::target_id( $id );

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'mode' => '1' ], [ 'ID' => $id ], [ '%d' ] );
		}

	}

	public static function pin_item( $id = 0 ): void {
		$id = self::target_id( $id );

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'pinned' => 1 ], [ 'ID' => $id ], [ '%d' ] );
		}
	}

	public static function unpin_item( $id = 0 ): void {
		$id = self::target_id( $id );

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'pinned' => 0 ], [ 'ID' => $id ], [ '%d' ] );
		}
	}

}
