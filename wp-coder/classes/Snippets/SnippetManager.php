<?php

namespace WPCoder\Snippets;

use WPCoder\WPCoder;

class SnippetManager {

	public static function init(): void {
		self::send();

		$snippets = [
			'disable' => [
				'html-code',
				__( 'Disable snippets', 'wp-coder' )
			],
			'enable'  => [
				'css-code',
				__( 'Enabled snippets', 'wp-coder' )
			],
			'change'  => [
				'js-code',
				__( 'Changed snippets', 'wp-coder' )
			],
		];

		?>
        <div class="wrap wowp-wrap wpcoder-snippets-wrap wowp-settings">
            <form method="post">
                <fieldset style="display: block;">
                    <legend>
						<?php esc_html_e( 'Snippets', 'wp-coder' ); ?>
                    </legend>

                    <h3 class="nav-tab-wrapper wowp-tab" id="settings-tab">
						<?php
						foreach ( $snippets as $page => $args ) {
							$first = ( $page === array_key_first( $snippets ) ) ? ' nav-tab-active' : '';
							echo '<a class="nav-tab' . esc_attr( $first ) . '" data-tab="' . esc_attr( $args[0] ) . '">' . esc_attr( $args[1] ) . '</a>';
						}
						?>
                    </h3>

                    <div class="tab-content-wrapper wowp-tab-content" id="settings-content">
						<?php
						foreach ( $snippets as $page => $args ) {
							$first = ( $page === array_key_first( $snippets ) ) ? ' tab-content-active' : '';
							echo '<div class="tab-content' . esc_attr( $first ) . '" data-content="' . esc_attr( $args[0] ) . '">';
							echo '<h4>' . esc_attr( $args[1] ) . '</h4>';
							require_once plugin_dir_path( __FILE__ ) . 'pages/' . esc_attr( $page ) . '.php';
							echo '</div>';
						}
						?>
                    </div>

                    <div class="wowp-field is-full has-mt">
						<?php
						submit_button( __( 'Save', 'wp-coder' ), 'primary large', 'submit', false ); ?>
                    </div>

					<?php
					wp_nonce_field( WPCoder::PREFIX . '_snippets_action', WPCoder::PREFIX . '_save_snippet' ); ?>
                </fieldset>
            </form>

        </div>
		<?php
	}

	private static function create_options( $settings ) {
		$options = get_option( '_wp_coder_snippets', [] );
		echo '<dl class="wowp-snippet__list">';
		foreach ( $settings as $name => $args ) {
			$checked = array_key_exists( $name, $options ) ? 'checked' : '';
			echo '<dt><label><input type="checkbox" name="wp_coder_snippet[' . esc_attr( $name ) . ']" ' . esc_attr( $checked ) . ' value="1">' . esc_attr( $args[0] ) . '</label></dt>';
			echo '<dd>' . esc_attr( $args[1] ) . '</dd>';
		}
		echo '</dl>';
	}

	private static function field( $type = 'checkbox', $name = '', $def = '', $placeholder = '' ) {
		$options = get_option( '_wp_coder_snippets', [] );
		if ( $type === 'checkbox' ) {
			$checked = array_key_exists( $name, $options ) ? 'checked' : '';
			echo '<input type="checkbox" name="wp_coder_snippet[' . esc_attr( $name ) . ']" ' . esc_attr( $checked ) . ' value="1">';
		} elseif ( $type === 'text' ) {
			$value = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;
			echo '<input type="text" name="wp_coder_snippet[' . esc_attr( $name ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
		} elseif ( $type === 'textarea' ) {
			$value = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;
			echo '<textarea name="wp_coder_snippet[' . esc_attr( $name ) . ']" placeholder="' . esc_attr( $placeholder ) . '">' . esc_attr( $value ) . '</textarea>';
		} elseif ( $type === 'number' ) {
			$value = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;
			echo '<input type="number" name="wp_coder_snippet[' . esc_attr( $name ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
		}
	}

	private static function send(): void {
		if ( ! self::verify() ) {
			return;
		}

		if ( empty( $_POST['wp_coder_snippet'] ) ) {
			$snippets = [];
		} else {
			$snippets = map_deep( $_POST['wp_coder_snippet'], 'sanitize_text_field' );
		}

		update_option( '_wp_coder_snippets', $snippets );
	}

	private static function verify(): bool {
		$wp_coder     = $_POST['wp_coder_snippet'] ?? '';
		$nonce_name   = WPCoder::PREFIX . '_save_snippet';
		$nonce_action = WPCoder::PREFIX . '_snippets_action';
		if ( empty( $_POST[ $nonce_name ] ) ) {
			return false;
		}

		return isset( $wp_coder ) && wp_verify_nonce( $_POST[ $nonce_name ],
				$nonce_action ) && current_user_can( 'manage_options' );
	}

}