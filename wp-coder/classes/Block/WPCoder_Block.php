<?php

namespace WPCoder\Block;

use WP_REST_Request;
use WPCoder\WOWP_Public;
use WPCoder\WPCoder;

class WPCoder_Block {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'init', [ $this, 'register_block' ] );
		add_action( 'rest_api_init', [ $this, 'rest_api' ] );
	}

	public function admin_scripts( $hook ): void {
		wp_localize_script( 'wpcoder-block-editor', 'wpcoderBlockData', array(
			'editBaseUrl' => admin_url( 'admin.php?page=' . WPCoder::SLUG . '-settings' )
		) );
	}

	public function register_block(): void {
		// No wp-editor: as the block's editor_script this loads in the widgets
		// screen and the Customizer too, and WordPress 5.8+ reports
		// _doing_it_wrong when wp-editor is enqueued beside wp-edit-widgets or
		// wp-customize-widgets. block.js never used it — only wp.apiFetch,
		// which wp-editor had been bringing in.
		wp_register_script(
			'wpcoder-block-editor',
			plugins_url( 'block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-api-fetch' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'block.js' )
		);

		register_block_type( 'wpcoder/shortcode', array(
			// Same as apiVersion in block.js (the client's value wins in the editor).
			'api_version'     => 3,
			'editor_script'   => 'wpcoder-block-editor',
			'render_callback' => [ $this, 'render_block' ],
			'attributes'      => array(
				'id' => array(
					'type'    => 'string',
					'default' => ''
				),
			),
			'icon' => 'editor-code',
		) );
	}

	public function render_block( $attributes ): string {
		if ( empty( $attributes['id'] ) ) {
			return '<p style="color:red;">' . esc_html__( 'Please select a WP Coder snippet.', 'wp-coder' ) . '</p>';
		}

		return (string) WOWP_Public::render_atts( self::snippet_atts( (int) $attributes['id'], $attributes['extraAttrs'] ?? [] ) );
	}

	/**
	 * Shortcode attributes as an array. Building a "[wp_code …]" string for
	 * do_shortcode() broke on the first "]" inside a value and printed the rest.
	 */
	private static function snippet_atts( int $id, $extra ): array {
		$atts = [ 'id' => (string) $id ];

		if ( is_array( $extra ) ) {
			foreach ( $extra as $attr ) {
				$key = is_array( $attr ) ? sanitize_key( $attr['key'] ?? '' ) : '';
				// Not `id`: it is the block's own setting. `title` is a usual attribute name.
				if ( $key === '' || $key === 'id' ) {
					continue;
				}
				$value = $attr['value'] ?? '';
				if ( is_scalar( $value ) && (string) $value !== '' ) {
					$atts[ $key ] = (string) $value;
				}
			}
		}

		return $atts;
	}

	public function rest_api(): void {
		$permission = static function () {
			return current_user_can( 'edit_posts' );
		};

		register_rest_route( 'wpcoder/v1', '/preview', array(
			'methods'  => 'POST',
			'callback' => [ $this, 'preview_callback' ],
			'permission_callback' => $permission
		));

		register_rest_route( 'wpcoder/v1', '/attributes', array(
			'methods'  => 'POST',
			'callback' => [ $this, 'attributes_callback' ],
			'permission_callback' => $permission
		));
	}

	public function preview_callback( WP_REST_Request $request ): array {
		global $wpdb;

		$id    = (int) $request['id'];
		$extra = $request['extraAttrs'] ?? [];


		$table = $wpdb->prefix . WPCoder::PREFIX;

		$snippet = $wpdb->get_row( $wpdb->prepare(
			"SELECT html_code, css_code FROM $table WHERE id = %d",
			$id
		) );

		if ( ! $snippet ) {
			return [
				'html' => '<p style="color:red;">'.__('Snippet not found', 'wp-coder').'</p>',
				'css'  => '',
			];
		}

		$html = (string) WOWP_Public::render_atts( self::snippet_atts( $id, $extra ) );

		return [
			'html' => $html,
			'css'  => $snippet->css_code,
		];
	}

	public function attributes_callback( WP_REST_Request $request ): array {
		global $wpdb;
		$id    = (int) $request['id'];
		$table = $wpdb->prefix . WPCoder::PREFIX;

		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT param FROM $table WHERE id = %d", $id
		) );

		if ( ! $row ) {
			return [];
		}

		$attrs = [];
		if ( ! empty( $row->param ) ) {
			$data = maybe_unserialize( $row->param );

			if ( isset( $data['shortcode_attribute'] ) && is_array( $data['shortcode_attribute'] ) ) {
				foreach ( $data['shortcode_attribute'] as $key => $attr ) {
					$attrs[] = array(
						'key'   => $attr,
						// The Default value field: what a new block starts with
						// (for a Range, its limits "min:max:step:default").
						'value' => (string) ( $data['shortcode_value'][ $key ] ?? '' ),
						'type'  => (string) ( $data['shortcode_type'][ $key ] ?? '' ) ?: 'text',
						// Set in WP Coder Pro; empty for attributes made here.
						'label' => (string) ( $data['shortcode_label'][ $key ] ?? '' ),
					);
				}
			}
		}

		return $attrs;
	}
}