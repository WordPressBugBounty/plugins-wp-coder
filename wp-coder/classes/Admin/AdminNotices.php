<?php

namespace WPCoder\Admin;

use WPCoder\Dashboard\FolderManager;
use WPCoder\WPCoder;

defined( 'ABSPATH' ) || exit;

class AdminNotices {

	public function __construct() {
		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
	}

	public function admin_notice() {
		$this->folder_notice();

		if ( ! isset( $_GET['page'], $_GET['notice'] ) ) {
			return;
		}

		$page   = $_GET['page'];
		$notice = $_GET['notice'];

		if ( in_array( $page, [ WPCoder::SLUG, WPCoder::SLUG . '-settings' ], true ) ) {
			switch ( $notice ) {
				case 'save_item':
					$this->save_item();
					break;
				case 'remove_item':
					$this->remove_item();
					break;
				case 'settings_import':
					$this->settings_import();
					break;
			}
		}
	}

	public function save_item() {
		$text = __( 'Item saved successfully.', 'wp-coder' );
		echo '<div class="wowp-notification notification-success notice notice-success is-dismissible">' . esc_html( $text ) . '</div>';
	}

	public function remove_item() {
		$text = __( 'Item has been deleted', 'wp-coder' );
		echo '<div class="wowp-notification notification-warning notice notice-warning is-dismissible">' . esc_html( $text ) . '</div>';
	}

	/** After a Snippets / Tools settings file was imported (OptionsTransfer). */
	public function settings_import() {
		$sections = isset( $_GET['settings'] )
			? array_intersect( [ 'snippets', 'tools' ], explode( ',', sanitize_text_field( wp_unslash( $_GET['settings'] ) ) ) )
			: [];

		if ( in_array( 'snippets', $sections, true ) && in_array( 'tools', $sections, true ) ) {
			$text = __( 'Snippets and Tools settings replaced.', 'wp-coder' );
		} elseif ( in_array( 'snippets', $sections, true ) ) {
			$text = __( 'Snippets settings replaced.', 'wp-coder' );
		} elseif ( in_array( 'tools', $sections, true ) ) {
			$text = __( 'Tools settings replaced.', 'wp-coder' );
		} else {
			echo '<div class="wowp-notification notification-warning notice notice-warning is-dismissible">'
			     . esc_html__( 'The settings file contained no Snippets or Tools settings.', 'wp-coder' ) . '</div>';

			return;
		}

		echo '<div class="wowp-notification notification-success notice notice-success is-dismissible">' . esc_html( $text ) . '</div>';
	}

	/**
	 * Snippet CSS, JS and PHP are served as files from uploads/wp-coder. When the
	 * folder cannot be written FolderManager::write() skips them without a PHP
	 * warning, so the missing parts are reported here instead.
	 */
	private function folder_notice(): void {
		if ( ! isset( $_GET['page'] ) || strpos( (string) $_GET['page'], WPCoder::SLUG ) !== 0 ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) || FolderManager::is_writable() ) {
			return;
		}

		$folder = str_replace( wp_normalize_path( ABSPATH ), '', wp_normalize_path( FolderManager::path_upload_dir() ) );

		echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'WP Coder cannot write to its folder.', 'wp-coder' ) . '</strong> '
		     . esc_html( sprintf(
			     /* translators: %s: folder path, e.g. wp-content/uploads/wp-coder/ */
			     __( 'Snippet CSS, JavaScript and PHP files are saved in %s, which does not exist or is not writable by the web server. Until it can be written, those parts of your snippets are not loaded on the site.', 'wp-coder' ),
			     $folder
		     ) ) . '</p></div>';
	}
}
