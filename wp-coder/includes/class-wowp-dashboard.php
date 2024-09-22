<?php

namespace WPCoder;

use WPCoder\Admin\AdminInitializer;
use WPCoder\Dashboard\DashboardInitializer;

defined( 'ABSPATH' ) || exit;

class WOWP_Dashboard {

	public function __construct() {
		add_filter( WPCoder::PREFIX . '_menu_logo', [ $this, 'menu_logo' ] );
		add_action( WPCoder::PREFIX . '_admin_load_styles_scripts', [ $this, 'load_styles_scripts' ] );
		add_action( WPCoder::PREFIX . '_admin_page', [ $this, 'dashboard' ] );
		add_action( WPCoder::PREFIX . '_admin_header_links', [ $this, 'header_links' ] );
		add_filter( WPCoder::PREFIX . '_save_settings', [ $this, 'save_settings' ], 10, 2 );
		add_filter( WPCoder::PREFIX . '_default_custom_post', [ $this, 'default_custom_post' ] );
		AdminInitializer::init();
	}

	public function menu_logo( $logo ) {
		$logo = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="15" height="15" fill="currentColor">
    <path d="M234.8 511.7L196 500.4c-4.2-1.2-6.7-5.7-5.5-9.9L331.3 5.8c1.2-4.2 5.7-6.7 9.9-5.5L380 11.6c4.2 1.2 6.7 5.7 5.5 9.9L244.7 506.2c-1.2 4.3-5.6 6.7-9.9 5.5zm-83.2-121.1l27.2-29c3.1-3.3 2.8-8.5-.5-11.5L72.2 256l106.1-94.1c3.4-3 3.6-8.2.5-11.5l-27.2-29c-3-3.2-8.1-3.4-11.3-.4L2.5 250.2c-3.4 3.2-3.4 8.5 0 11.7L140.3 391c3.2 3 8.2 2.8 11.3-.4zm284.1.4l137.7-129.1c3.4-3.2 3.4-8.5 0-11.7L435.7 121c-3.2-3-8.3-2.9-11.3.4l-27.2 29c-3.1 3.3-2.8 8.5.5 11.5L503.8 256l-106.1 94.1c-3.4 3-3.6 8.2-.5 11.5l27.2 29c3.1 3.2 8.1 3.4 11.3.4z"/>
</svg>';

		return $logo;
	}

	public function default_custom_post( $display_def ) {
		if ( str_contains( $display_def, 'custom_post_selected' ) ) {
			return 'post_selected';
		}
		if ( str_contains( $display_def, 'custom_post_tax' ) ) {
			return 'post_category';
		}
		if ( str_contains( $display_def, 'custom_post_all' ) ) {
			return 'post_all';
		}

		return $display_def;
	}

	public function save_settings( $settings, $request ): array {
		$param = ! empty( $request['param'] ) ? map_deep( $request['param'], [ $this, 'sanitize_param' ] ) : [];

		$settings['data']['title']     = ! empty( $request['title'] ) ? sanitize_text_field( wp_unslash( $request['title'] ) ) : '';
		$settings['formats'][]         = '%s';
		$settings['data']['html_code'] = ! empty( $request['html_code'] ) ? wp_unslash( $request['html_code'] ) : '';
		$settings['formats'][]         = '%s';
		$settings['data']['css_code']  = ! empty( $request['css_code'] ) ? wp_unslash( $request['css_code'] ) : '';
		$settings['formats'][]         = '%s';
		$settings['data']['js_code']   = ! empty( $request['js_code'] ) ? wp_unslash( $request['js_code'] ) : '';
		$settings['formats'][]         = '%s';
		$settings['data']['php_code']  = ! empty( $request['php_code'] ) ? wp_unslash( $request['php_code'] ) : '';
		$settings['formats'][]         = '%s';
		$settings['data']['status']    = ! empty( $request['status'] ) ? sanitize_textarea_field( wp_unslash( $request['status'] ) ) : '';
		$settings['formats'][]         = '%d';
		$settings['data']['mode']      = ! empty( $request['mode'] ) ? sanitize_textarea_field( wp_unslash( $request['mode'] ) ) : '';
		$settings['formats'][]         = '%d';
		$settings['data']['tag']       = ! empty( $request['tag'] ) ? sanitize_textarea_field( wp_unslash( $request['tag'] ) ) : '';
		$settings['formats'][]         = '%s';

		if ( ! empty( $request['param']['include_file'] ) ) {
			$param['include_file'] = ! empty( $request['param']['include_file'] ) ? map_deep( $request['param']['include_file'],
				'esc_url' ) : [];
		}

		$settings['data']['param'] = maybe_serialize( $param );
		$settings['formats'][]     = '%s';

		return $settings;
	}

	public function sanitize_param( $value ) {
		return wp_unslash( sanitize_text_field( $value ) );
	}

	public function load_styles_scripts(): void {
		$assets_url = WPCoder::url() . 'assets/';
		$version    = WPCoder::info('version');
		$slug       = WPCoder::SLUG;

		wp_enqueue_style( $slug . '-icons', $assets_url . 'icons/css/icons.css', null, $version );
		wp_enqueue_style( $slug . '-admin', $assets_url . 'css/admin.css', null, $version );

		wp_enqueue_script( 'code-editor' );
		wp_enqueue_style( 'code-editor' );
		wp_enqueue_script( 'htmlhint' );
		wp_enqueue_script( 'csslint' );
		wp_enqueue_script( 'jshint' );

		wp_enqueue_media();

		wp_enqueue_script( $slug . '-admin', $assets_url . 'js/admin.js', array( 'jquery' ), $version );

        wp_enqueue_script( $slug . '-admin-jquery', $assets_url . 'js/admin-jquery.js', array( 'jquery' ), $version );
	}

	public function dashboard(): void {
		DashboardInitializer::init();
	}


	public function header_links(): void {
		$logo = WPCoder::url() . 'assets/img/wow-icon.png';
		?>
        <div class="wowp-links">
            <a href="https://wpcoder.pro/?utm_source=wordpress&utm_medium=admin-menu&utm_campaign=main" target="_blank">
                <span>PRO Version</span>
            </a>
            <a href="https://wpcoder.pro/category/documentation/?utm_source=wordpress&utm_medium=admin-menu&utm_campaign=documentation"
               target="_blank">
                <span>Documentation</span>
            </a>
            <a href="https://wpcoder.pro/category/snippet/?utm_source=wordpress&utm_medium=admin-menu&utm_campaign=snippets"
               target="_blank">
                <span>Snippets</span>
            </a>
            <a href="https://wpcoder.pro/category/tutorials/?utm_source=wordpress&utm_medium=admin-menu&utm_campaign=tutorials"
               target="_blank">
                <span>Tutorials</span>
            </a>
            <a href="https://wpcoder.pro/category/web-items/?utm_source=wordpress&utm_medium=admin-menu&utm_campaign=items"
               target="_blank">
                <span>Web Elements</span>
            </a>
            <a href="https://wordpress.org/support/plugin/wp-coder/reviews/" target="_blank">
                <span>Reviews</span>
            </a>
        </div>

		<?php
	}


}