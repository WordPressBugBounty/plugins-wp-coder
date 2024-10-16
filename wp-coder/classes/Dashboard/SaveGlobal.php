<?php

namespace WPCoder\Dashboard;

defined( 'ABSPATH' ) || exit;

use WPCoder\WPCoder;

class SaveGlobal {

	public static function init(): void {
		self::send();
		$content = self::get_content();
		$enabled = get_option( '_wpcoder_enable_php' );
		?>
        <div class="wrap wowp-wrap wpcoder-global-php">

            <form method="post">
                <fieldset>
                    <legend>
						<?php
						esc_html_e( 'PHP code', 'wp-coder' ); ?>
                    </legend>
                    <div class="wowp-field is-full">
                        <ol id="phpNavigationMenu" class="wowp-php-nav-menu"></ol>
                        <span class="button-editor button" id="phpglobalnav">Add NAV Comment</span>
                    </div>

                    <div class="wowp-field is-full">
                        <textarea name="wp_coder_global_php" id="wpcoder-global-php" cols="40" rows="5"><?php
	                        echo esc_textarea( $content ); ?></textarea>
                    </div>
                    <div class="wowp-field">
                        <label>
                            <input type="checkbox" value="1" name="wp_coder_global_php_enable"
                                   id="wpcoder-global-php-enable"<?php
							checked( $enabled ); ?>>
							<?php
							esc_html_e( 'Enable PHP code', 'wp-coder' ); ?>
                        </label>
                    </div>
                    <div class="wowp-field is-full">
						<?php
						submit_button( __( 'Save', 'wp-coder' ), 'primary large', 'submit', false ); ?>
                    </div>

					<?php
					wp_nonce_field( WPCoder::PREFIX . '_global_action', WPCoder::PREFIX . '_save_name' ); ?>
                </fieldset>
            </form>
        </div>

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
			file_put_contents( $path_global,
				"<?php\ndefined( 'ABSPATH' ) || exit;\n\n" . wp_unslash( $_POST['wp_coder_global_php'] ) );
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
				$nonce_action ) && current_user_can( 'manage_options' );
	}
}
