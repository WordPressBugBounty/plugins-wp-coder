<?php

namespace WPCoder\Dashboard;

defined( 'ABSPATH' ) || exit;

use WPCoder\WPCoder;

class DashboardInitializer {

	public static function init(): void {
		self::header();
		echo '<div class="wrap wowp-wrap">';
//		self::menu();
//		self::include_pages();
		echo '</div>';
	}

	public static function header(): void {
		$logo_url = self::logo_url();
		$add_url  = add_query_arg( [
			'page'   => WPCoder::SLUG,
			'tab'    => 'settings',
			'action' => 'new'
		], admin_url( 'admin.php' ) );
		?>

            <div class="wowp-header-border"></div>
            <div class="wowp-header">
                <div class="wowp-logo">
                    <svg width="32px" height="32px" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <g id="WPCoder" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="code" transform="translate(26.000000, 56.000000)" fill-rule="nonzero">
                                <path d="M135.623849,107.492348 C132.313053,103.34684 127.489388,100.688372 122.21643,100.103113 C116.943473,99.5178542 111.654252,101.053868 107.514995,104.372476 L7.5090733,184.369213 C2.76301474,188.164426 0,193.911677 0,199.988576 C0,206.065476 2.76301474,211.812727 7.5090733,215.607939 L107.504995,295.604677 C111.643224,298.925874 116.933119,300.463522 122.20689,299.878173 C127.48066,299.292824 132.304624,296.632613 135.613849,292.484804 C142.506902,283.862628 141.110501,271.286066 132.493976,264.38595 L52.0172582,199.988576 L132.493976,135.611202 C141.110501,128.711086 142.506902,116.134524 135.613849,107.512348 L135.613849,107.482349 L135.623849,107.482349 L135.623849,107.492348 Z" id="Path" fill="#FF6B6B"></path>
                                <path d="M352.495005,104.372476 C343.863245,97.6171738 331.402277,99.0684778 324.554053,107.626703 C317.705829,116.184927 319.022334,128.660857 327.506024,135.601202 L407.982742,199.988576 L327.506024,264.365951 C319.022334,271.306297 317.705829,283.782226 324.554053,292.340451 C331.402277,300.898675 343.863245,302.349979 352.495005,295.594678 L452.490927,215.59794 C457.236985,211.802728 460,206.055476 460,199.978577 C460,193.901677 457.236985,188.154426 452.490927,184.359214 L352.495005,104.362476 L352.495005,104.382475 L352.495005,104.372476 Z" id="Path" fill="#007EA1"></path>
                                <path d="M285.347743,0.72670242 C274.707912,-2.20949175 263.697957,4.01547032 260.728747,14.6461348 L160.732825,374.631454 C157.911801,385.22514 164.145145,396.115314 174.708452,399.048043 C185.271759,401.980772 196.227146,395.862768 199.271253,385.331018 L299.267175,25.3456984 C302.221117,14.7036863 295.989426,3.68183039 285.347743,0.72670242 L285.347743,0.72670242 Z" id="Path" fill="#007EA1"></path>
                            </g>
                        </g>
                    </svg>
                </div>
                <h1><?php echo esc_html( WPCoder::info('name') ); ?> <sup
                            class="wowp-version"><?php echo esc_html( WPCoder::info('version') ); ?></sup></h1>

				<?php do_action( WPCoder::PREFIX . '_admin_header_links' ); ?>
            </div>


		<?php
	}

	public static function logo_url(): string {
		$logo_url = WPCoder::url() . 'assets/img/plugin-logo.png';
		if ( filter_var( $logo_url, FILTER_VALIDATE_URL ) !== false ) {
			return $logo_url;
		}

		return '';
	}

	public static function menu(): void {

		$pages = DashboardHelper::get_files( 'pages' );
		unset($pages["3"], $pages["4"]);

		$current_page = self::get_current_page();

		$action = ( isset( $_REQUEST["action"] ) ) ? sanitize_text_field( $_REQUEST["action"] ) : '';

		echo '<h2 class="nav-tab-wrapper wowp-nav-tab-wrapper">';
		foreach ( $pages as $key => $page ) {
			$class = ( $page['file'] === $current_page ) ? ' nav-tab-active' : '';
			$id    = '';

			if ( $action === 'update' && $page['file'] === 'settings' ) {
				$id           = ( isset( $_REQUEST["id"] ) ) ? absint( $_REQUEST["id"] ) : '';
				$page['name'] = __( 'Update', 'wpcoder' ) . ' #' . $id;
			} elseif ( $page['file'] === 'settings' && ( $action !== 'new' && $action !== 'duplicate' ) ) {
				continue;
			}

			echo '<a class="nav-tab' . esc_attr( $class ) . '" href="' . esc_url( Link::menu( $page['file'], $action, $id ) ) . '">' . esc_html( $page['name'] ) . '</a>';
		}
		echo '</h2>';


	}

	public static function include_pages(): void {
		$current_page = self::get_current_page();

		$pages   = DashboardHelper::get_files( 'pages' );
		$default = DashboardHelper::first_file( 'pages' );

		$current = DashboardHelper::search_value( $pages, $current_page ) ? $current_page : $default;

		$file = DashboardHelper::get_file( $current, 'pages' );


		if ( $file !== false ) {
			$file = apply_filters( WPCoder::PREFIX . '_admin_filter_file', $file, $current );

			$page_path = DashboardHelper::get_folder_path( 'pages' ) . '/' . $file;

			if ( file_exists( $page_path ) ) {
				require_once $page_path;
			}
		}

	}


	public static function get_current_page(): string {
		$default = DashboardHelper::first_file( 'pages' );

		return ( isset( $_REQUEST["tab"] ) ) ? sanitize_text_field( $_REQUEST["tab"] ) : $default;
	}


}