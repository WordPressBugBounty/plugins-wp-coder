<?php

namespace WPCoder\Dashboard;

defined( 'ABSPATH' ) || exit;

use WPCoder\WPCoder;

class SupportForm {

	public static function init(): void {

		$plugin  = WPCoder::info('name') . ' v.' . WPCoder::info('version');
		$license = get_option( 'wow_license_key_' . WPCoder::PREFIX, 'no' );

		self::send();

		?>

        <form method="post">

            <fieldset>
                <legend>
					<?php esc_html_e( 'Support Form', 'wp-coder' ); ?>
                </legend>

                <div class="wowp-field">
                    <label for="support-name" class="label"><?php esc_html_e( 'Your Name', 'wp-coder' ); ?></label>
                    <input type="text" name="support[name]" id="support-name">
                </div>

                <div class="wowp-field">
                    <label for="support-email" class="label"><?php esc_html_e( 'Contact email', 'wp-coder' ); ?></label>
                    <input type="text" name="support[email]" id="support-email"
                           value="<?php echo sanitize_email( get_option( 'admin_email' ) ); ?>">
                </div>

                <div class="wowp-field">
                    <label for="support-link"
                           class="label"><?php esc_html_e( 'Link to the issue', 'wp-coder' ); ?></label>
                    <input type="text" name="support[link]" id="support-link"
                           value="<?php echo esc_url( get_option( 'home' ) ); ?>">
                </div>

                <div class="wowp-field">
                    <label for="support-type" class="label"><?php esc_html_e( 'Message type', 'wp-coder' ); ?></label>
                    <select name="support[type]" id="support-type">
                        <option value="Issue"><?php esc_html_e( 'Issue', 'wp-coder' ); ?></option>
                        <option value="Idea"><?php esc_html_e( 'Idea', 'wp-coder' ); ?></option>
                    </select>
                </div>

                <div class="wowp-field">
                    <label for="support-plugin" class="label"><?php esc_html_e( 'Plugin', 'wp-coder' ); ?></label>
                    <input type="text" readonly name="support[plugin]" id="support-plugin"
                           value="<?php echo esc_attr( $plugin ); ?>">
                </div>

                <div class="wowp-field">
                    <label for="support-license" class="label"><?php esc_html_e( 'License Key', 'wp-coder' ); ?></label>
                    <input type="text" readonly name="support[license]" id="support-license"
                           value="<?php echo esc_attr( $license ); ?>">
                </div>

                <div class="wowp-field is-full">
					<?php
					$content   = esc_attr__( 'Enter Your Message', 'wp-coder' );
					$editor_id = 'support-message';
					$settings  = array(
						'textarea_name' => 'support[message]',
					);
					wp_editor( $content, $editor_id, $settings ); ?>
                </div>

                <div class="wowp-field">
					<?php submit_button( __( 'Send to Support', 'wp-coder' ), 'primary', 'submit', false ); ?>
                </div>

				<?php wp_nonce_field( WPCoder::PREFIX . '_nonce_action', WPCoder::PREFIX . '_nonce_name' ); ?>
            </fieldset>
        </form>

		<?php


	}

	private static function send(): void {
		if ( ! self::verify() ) {
			return;
		}


		$error = self::error();
		if ( ! empty( $error ) ) {
			echo '<p class="wowp-notice wowp-error">' . esc_html( $error ) . '</p>';

			return;
		}

		$support = $_POST['support'];

		$headers = array(
			'From: ' . esc_attr( $support['name'] ) . ' <' . sanitize_email( $support['email'] ) . '>',
			'content-type: text/html',
		);

		$message_mail = '<html>
                        <head></head>
                        <body>
                        <table>
                        <tr>
                        <td><strong>License Key:</strong></td>
                        <td>' . esc_attr( $support['license'] ) . '</td>
                        </tr>
                        <tr>
                        <td><strong>Plugin:</strong></td>
                        <td>' . esc_attr( $support['plugin'] ) . '</td>
                        </tr>
                        <tr>
                        <td><strong>Website:</strong></td>
                        <td><a href="' . esc_url( $support['link'] ) . '" target="_blank">' . esc_url( $support['link'] ) . '</a></td>
                        </tr>
                        </table>
                        ' . nl2br( wp_kses_post( $support['message'] ) ) . '
                        </body>
                        </html>';
		$type         = sanitize_text_field( $support['type'] );
		$to_mail      = WPCoder::info('email');
		$send         = wp_mail( $to_mail, 'Support Ticket: ' . $type, $message_mail, $headers );

		if ( $send ) {
			$text = __( 'Your message has been sent to the support team.', 'wp-coder' );
			echo '<p class="wowp-notice wowp-success">' . esc_html( $text ) . '</p>';
		} else {
			$text = __( 'Sorry, but message did not send. Please, contact us ', 'wp-coder' ) . $to_mail;
			echo '<p class="wowp-notice wowp-error">' . esc_html( $text ) . '</p>';
		}

	}

	private static function error(): ?string {
		if ( ! self::verify() ) {
			return '';
		}
		$support = $_POST['support'];
		$fields  = [ 'name', 'email', 'link', 'type', 'plugin', 'license', 'message' ];

		foreach ( $fields as $field ) {
			if ( empty( $support[ $field ] ) ) {
				return __( 'Please fill in all the form fields below.', 'wp-coder' );
			}
		}

		return '';
	}

	private static function verify(): bool {
		$support      = $_POST['support'] ?? [];
		$nonce_name   = WPCoder::PREFIX . '_nonce_name';
		$nonce_action = WPCoder::PREFIX . '_nonce_action';

		return ! empty( $support ) && wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action );
	}
}
