<?php

namespace WPCoder\Publisher;

use Throwable;
use WPCoder\Dashboard\DBManager;
use WPCoder\Dashboard\FolderManager;
use WPCoder\WPCoder;

class PHPIncludes {
	public function __construct() {
		add_action( 'init', [ $this, 'include_php' ] );
		add_action( 'admin_notices', [ $this, 'display_admin_error_notice' ] );
	}

	public function include_php(): void {
		// The parameter alone used to do it, for any visitor (SafeMode::is_safe_mode()).
		if ( SafeMode::is_safe_mode() ) {
			return;
		}

		$this->include_global();
		$this->include_file();
	}

	private function include_global(): void {
		$file_path = FolderManager::path_upload_dir() . 'global-php.php';

		if ( file_exists( $file_path ) && get_option( '_wpcoder_enable_php' ) ) {
			$this->try_include_file( $file_path );
		}
	}

	private function include_file(): void {
		$all_data = DBManager::get_all_data();

		if ( empty( $all_data ) ) {
			return;
		}

		foreach ( $all_data as $data ) {
			if ( ! empty( $data->php_include ) && empty( $data->status ) ) {
				$path = FolderManager::code_path( $data );

				if ( absint( $data->php_include ) === 1 && is_admin() ) {
					$this->try_include_file( $path );
				}

				if ( absint( $data->php_include ) === 2 && ! is_admin() ) {
					$this->try_include_file( $path );
				}

				if ( absint( $data->php_include ) === 3 ) {
					$this->try_include_file( $path );
				}
			}
		}

	}

	private function try_include_file( $file_path ) {
		// The forbidden-function check, as in WP Coder Pro (CodeSafety): Global PHP
		// and "PHP include" files used to be included unchecked.
		[ $safe, $hits ] = CodeSafety::scan_file( $file_path );
		if ( $safe === null ) {
			return; // File missing or unreadable.
		}
		if ( ! $safe ) {
			error_log( '[WP-Coder] Blocked dangerous PHP include: ' . implode( ', ', $hits ) );
			set_transient( 'wp_coder_admin_error_php_notice', esc_html__( 'WP Coder blocked a PHP file containing forbidden functions: ', 'wp-coder' ) . esc_html( implode( ', ', $hits ) ), 30 );

			return;
		}

		// Throwable, not only ParseError: an Error or exception thrown while the file
		// runs (undefined function, TypeError) is just as fatal to the request.
		try {
			@include_once $file_path;
		} catch ( Throwable $e ) {
			$parts     = explode( '/', $file_path );
			$file_name = end( $parts );
			if ( $file_name === 'global-php.php' ) {
				$page_url = admin_url() . 'admin.php?page=' . WPCoder::SLUG . '-global';

				$message  = sprintf(
				/* translators: 1. link to the page 2. error name. */
					__( 'An error occurred while loading the PHP script from <a href="%1$s">Global PHP</a>. Error: %2$s', 'wp-coder' ),
					esc_url( $page_url ),
					esc_attr( $e->getMessage() )
				);
			} else {
				$name_parts = explode('-', $file_name);
				$num_part = end($name_parts);
				$id = str_replace('.php', '', $num_part);
				$page_url = admin_url() . 'admin.php?page=' . WPCoder::SLUG . '-settings&action=update&id='.absint($id);

				$message  = sprintf(
				/* translators: 1. link to the code 2. code ID. 3. error message */
					__( 'An error occurred while loading the PHP script from <a href="%1$s">WP Code ID %2$d</a>. Error: %3$s', 'wp-coder' ),
					esc_url( $page_url ),
					absint($id),
					esc_attr( $e->getMessage() ),
				);
			}

			error_log( "Parse error in file '$file_path': " . $e->getMessage() );
			set_transient( 'wp_coder_admin_error_php_notice', $message, 30 );

		}
	}

	public function display_admin_error_notice(): void {
		// Check if the transient exists
		$error_message = get_transient( 'wp_coder_admin_error_php_notice' );
		if ( $error_message ) {
			?>
            <div class="notice notice-error">
                <p><?php echo wp_kses_post( $error_message ); ?></p>
            </div>
			<?php
			// Delete the transient to prevent repeated notices
			delete_transient( 'wp_coder_admin_error_php_notice' );
		}
	}

}