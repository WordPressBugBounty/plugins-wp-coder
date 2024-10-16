<?php

namespace WPCoder\Tools;

use WPCoder\WPCoder;

class ToolsManager {

	public static function init(): void {
		self::send();

		?>
        <div class="wrap wowp-wrap wpcoder-tools-wrap wowp-settings">
            <form method="post">
                <fieldset style="display: block;">
                    <legend>
						<?php esc_html_e( 'Tools', 'wp-coder' ); ?>
                    </legend>
                    <div class="tab-content-wrapper wowp-tab-content">
						<?php require_once plugin_dir_path( __FILE__ ) . '/page.php'; ?>
                    </div>
                    <div class="wowp-field is-full has-mt">
						<?php
						submit_button( __( 'Save', 'wp-coder' ), 'primary large', 'submit', false ); ?>
                    </div>

					<?php
					wp_nonce_field( WPCoder::PREFIX . '_tools_action', WPCoder::PREFIX . '_save_tools' ); ?>
                </fieldset>
            </form>
        </div>
		<?php

	}

	private static function option( $name = '', $def = '' ) {
		$options = get_option( '_wp_coder_tools', [] );
		$res     = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;

		return $res;
	}

	private static function expand( $name ) {
		$res = self::option( $name );
		if ( ! empty( $res ) ) {
			echo ' open';
		}
	}

	private static function field( $type = 'checkbox', $name = '', $def = '', $placeholder = '' ) {
		$options = get_option( '_wp_coder_tools', [] );
		if ( $type === 'checkbox' ) {
			$checked = array_key_exists( $name, $options ) ? 'checked' : '';
			echo '<input type="checkbox" name="wp_coder_tool[' . esc_attr( $name ) . ']" ' . esc_attr( $checked ) . ' value="1">';
		} elseif ( $type === 'textarea' ) {
			$value = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;
			echo '<textarea name="wp_coder_tool[' . esc_attr( $name ) . ']" placeholder="' . esc_attr( $placeholder ) . '">' . esc_attr( $value ) . '</textarea>';
		} else {
			$value = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;
			echo '<input type="' . esc_attr( $type ) . '" name="wp_coder_tool[' . esc_attr( $name ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
		}
	}

	private static function send(): void {
		if ( ! self::verify() ) {
			return;
		}

		if ( empty( $_POST['wp_coder_tool'] ) ) {
			$snippets = [];
		} else {
			$snippets = map_deep( $_POST['wp_coder_tool'], 'sanitize_text_field' );
		}


		update_option( '_wp_coder_tools', $snippets );
	}

	private static function verify(): bool {
		$wp_coder     = $_POST['wp_coder_tool'] ?? '';
		$nonce_name   = WPCoder::PREFIX . '_save_tools';
		$nonce_action = WPCoder::PREFIX . '_tools_action';
		if ( empty( $_POST[ $nonce_name ] ) ) {
			return false;
		}

		return wp_verify_nonce( $_POST[ $nonce_name ],
				$nonce_action ) && current_user_can( 'manage_options' );
	}
}