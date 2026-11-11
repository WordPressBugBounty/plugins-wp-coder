<?php

namespace WPCoder\Dashboard;

defined( 'ABSPATH' ) || exit;

use WPCoder\WPCoder;

class SaveGlobal {

	/**
	 * Code posted but not written. Global PHP lives only in its file, so after a
	 * failed write the page would reload the old file and the new code would be
	 * gone from the editor; it is shown again instead, with an error.
	 */
	private static ?string $unsaved = null;

	public static function init(): void {
		self::send();
		$content = self::$unsaved ?? self::get_content();
		$enabled = get_option( '_wpcoder_enable_php' );
		if ( self::$unsaved !== null ) {
			echo '<div class="wowp-notification notification-error notice notice-error"><p>'
			     . esc_html__( 'Global PHP was not saved: its folder is not writable. Your code is still in the editor below.', 'wp-coder' )
			     . '</p></div>';
		}
		?>


            <form method="post">
                <div class="wowp-settings">

                    <div class="wowp-settings__page">


                        <div class="wowp-settings__page-sidebar">

                            <div class="wowp-field has-checkbox is-reverse">
                                <span class="label">
                                    <?php esc_html_e( 'Enable PHP code', 'wp-coder' ); ?>
                                    <sup class="wowp-tooltip" data-tooltip="Enable execution of this PHP code globally. Be careful: errors may affect your site.">ℹ</sup>
                                </span>
                                <label class="switch">
                                    <input type="checkbox" value="1" name="wp_coder_global_php_enable" id="wpcoder-global-php-enable"<?php checked( $enabled ); ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

	                        <?php submit_button( __( 'Save', 'wp-coder' ), 'wowp-button button button-dark', 'submit', false ); ?>


                            <button class="button-editor button" id="phpglobalnav"><?php esc_html_e( 'Add NAV Comment', 'wp-coder' ); ?></button>
                            <ol id="phpNavigationMenu" class="wowp-php-nav-menu"></ol>

                        </div>

                        <div class="wowp-settings__page-content">
                            <textarea name="wp_coder_global_php" id="wpcoder-global-php" cols="40" rows="5"><?php
	                            echo esc_textarea( $content ); ?></textarea>

	                        <?php
	                        // Under the editor, where code is pasted: a second <?php
	                        // is the usual way Global PHP breaks (FolderManager::strip_open_tag()).
	                        Settings::editor_hint( 'global_php', sprintf(
	                        /* translators: %1$s: <?php */
		                        __( 'Enter the code without %1$s — the file already starts with it. If your code begins with %1$s, it is removed when you save.', 'wp-coder' ),
		                        '<code>&lt;?php</code>'
	                        ) ); ?>
                        </div>


                    </div>

	            <?php wp_nonce_field( WPCoder::PREFIX . '_global_action', WPCoder::PREFIX . '_save_name' ); ?>
                </div>
            </form>


		<?php
	}

	private static function get_content() {
		$content     = '';
		$path_global = FolderManager::path_upload_dir() . 'global-php.php';

		if ( file_exists( $path_global ) ) {
			$content_file = file_get_contents( $path_global );
			$lines        = explode( PHP_EOL, $content_file );
			$lines        = array_slice( $lines, 3 );
			$content      = implode( PHP_EOL, $lines );
		}

		return $content;
	}

	private static function send(): void {
		if ( ! self::verify() ) {
			return;
		}
		$checked = ! empty( $_POST['wp_coder_global_php_enable'] ) ? 1 : 0;
		update_option( '_wpcoder_enable_php', $checked );

		$path_global = FolderManager::path_upload_dir() . 'global-php.php';

		if ( isset( $_POST['wp_coder_global_php'] ) ) {
			$code = FolderManager::strip_open_tag( (string) wp_unslash( $_POST['wp_coder_global_php'] ) );
			if ( FolderManager::write( $path_global, "<?php\ndefined( 'ABSPATH' ) || exit;\n\n" . $code ) === false ) {
				self::$unsaved = $code;
			}
		}
	}

	private static function verify(): bool {
		$wp_coder     = $_POST['wp_coder_global_php'] ?? '';
		$nonce_name   = WPCoder::PREFIX . '_save_name';
		$nonce_action = WPCoder::PREFIX . '_global_action';
		if ( empty( $_POST[ $nonce_name ] ) ) {
			return false;
		}

		return isset( $wp_coder ) && wp_verify_nonce( $_POST[ $nonce_name ],
				$nonce_action ) && WPCoder::user_can_manage();
	}
}
