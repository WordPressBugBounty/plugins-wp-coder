<?php

namespace WPCoder;

use WPCoder\Admin\AdminInitializer;
use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Dashboard\DBManager;
use WPCoder\Dashboard\FolderManager;
use WPCoder\Dashboard\ImporterExporter;
use WPCoder\Dashboard\Tags;

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
		add_action( 'admin_init', [ ImporterExporter::class, 'export_items' ] );
	}

	public function menu_logo( $logo ): string {
		$logo = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><title>code</title><g fill="#212121"><path d="M19.562,18.75c-.69-.862-1.948-1.003-2.811-.312L1.75,30.438c-.474,.379-.75,.954-.75,1.562s.276,1.182,.75,1.562l15,12c.369,.295,.81,.438,1.248,.438,.587,0,1.168-.257,1.563-.75,.69-.863,.55-2.121-.312-2.811l-13.048-10.438,13.048-10.438c.862-.69,1.002-1.948,.312-2.811Z" fill="#212121"></path><path d="M47.25,18.438c-.862-.691-2.121-.55-2.811,.312-.69,.863-.55,2.121,.312,2.811l13.048,10.438-13.048,10.438c-.862,.69-1.002,1.948-.312,2.811,.395,.494,.976,.75,1.563,.75,.438,0,.879-.143,1.248-.438l15-12c.474-.379,.75-.954,.75-1.562s-.276-1.182-.75-1.562l-15-12Z" fill="#212121"></path><path d="M39.539,5.074c-1.063-.298-2.167,.324-2.465,1.387l-14,50c-.298,1.063,.323,2.167,1.387,2.465,.18,.051,.362,.075,.54,.075,.875,0,1.678-.578,1.925-1.461L40.926,7.539c.298-1.063-.323-2.167-1.387-2.465Z" fill="#212121"></path></g></svg>';

		return $logo;
	}

	public function default_custom_post( $display_def ) {
		if ( false !== strpos( (string) $display_def, 'custom_post_selected' ) ) {
			return 'post_selected';
		}
		if ( false !== strpos( (string) $display_def, 'custom_post_tax' ) ) {
			return 'post_category';
		}
		if ( false !== strpos( (string) $display_def, 'custom_post_all' ) ) {
			return 'post_all';
		}

		return $display_def;
	}

	public function save_settings( $settings, $request ): array {
		$param = ! empty( $request['param'] ) ? map_deep( $request['param'], [ $this, 'sanitize_param' ] ) : [];

		$settings['data']['title']       = ! empty( $request['title'] ) ? sanitize_text_field( wp_unslash( $request['title'] ) ) : '';
		$settings['formats'][]           = '%s';
		$settings['data']['html_code']   = ! empty( $request['html_code'] ) ? wp_unslash( $request['html_code'] ) : '';
		$settings['formats'][]           = '%s';
		$settings['data']['css_code']    = ! empty( $request['css_code'] ) ? wp_unslash( $request['css_code'] ) : '';
		$settings['formats'][]           = '%s';
		$settings['data']['js_code']     = ! empty( $request['js_code'] ) ? wp_unslash( $request['js_code'] ) : '';
		$settings['formats'][]           = '%s';
		// Without a pasted leading <?php: the file has its own, a second one stops
		// all of it. Stored stripped too, so the editor shows what runs and an
		// error's line number matches it.
		$settings['data']['php_code']    = ! empty( $request['php_code'] ) ? FolderManager::strip_open_tag( (string) wp_unslash( $request['php_code'] ) ) : '';
		$settings['formats'][]           = '%s';
		$settings['data']['status']      = ! empty( $request['status'] ) ? sanitize_textarea_field( wp_unslash( $request['status'] ) ) : '';
		$settings['formats'][]           = '%d';
		$settings['data']['mode']        = ! empty( $request['mode'] ) ? sanitize_textarea_field( wp_unslash( $request['mode'] ) ) : '';
		$settings['formats'][]           = '%d';
		// The single place a tag is written. Normalised so FIND_IN_SET matches:
		// "landing, hero" would otherwise store the second tag as " hero".
		$settings['data']['tag']         = ! empty( $request['tag'] ) ? Tags::normalize( sanitize_textarea_field( wp_unslash( $request['tag'] ) ) ) : '';
		$settings['formats'][]           = '%s';
		$settings['data']['php_include'] = ! empty( $request['php_include'] ) ? sanitize_textarea_field( wp_unslash( $request['php_include'] ) ) : '';
		$settings['formats'][]           = '%d';


		if ( ! empty( $request['param']['include_file'] ) ) {
			$param['include_file'] = ! empty( $request['param']['include_file'] ) ? map_deep( $request['param']['include_file'],
				'esc_url' ) : [];
		}

		if ( ! empty( $param['shortcode_attribute'] ) && is_array( $param['shortcode_attribute'] ) ) {
			$param = $this->normalize_attributes( $param );
		}

		// The note is plain text for the admin screens only — never printed on the site.
		$note = isset( $request['param']['note'] ) && is_string( $request['param']['note'] )
			? trim( sanitize_textarea_field( wp_unslash( $request['param']['note'] ) ) )
			: '';
		if ( $note !== '' ) {
			$param['note'] = $note;
		} else {
			unset( $param['note'] );
		}

		$param = $this->keep_other_param( $param, $request );

		$settings['data']['param'] = maybe_serialize( $param );
		$settings['formats'][]     = '%s';

		return $settings;
	}

	/**
	 * Settings this version's form writes. Unchecked checkboxes are not posted,
	 * so these are dropped when absent rather than carried over. `show` and
	 * `location` are not listed: the form leaves them out for a snippet whose
	 * display rules come from WP Coder Pro, and then they must be kept.
	 */
	private const FORM_PARAM_KEYS = [
		'preview', 'minified_html', 'minified_css', 'minified_js', 'inline_css', 'inline_js',
		'jquery_dependency', 'js_attributes', 'include', 'include_file', 'css_only_preview',
		'file_js_att', 'hide_html', 'hide_css', 'hide_js', 'hide_php', 'hide_include',
		'link', 'tab', 'time', 'note',
		// The Attributes tab posts all five for every row, Pro-only rows included.
		'shortcode_attribute', 'shortcode_type', 'shortcode_value', 'shortcode_label', 'shortcode_help',
	];

	/** Per-row extras Pro keeps beside include_file, aligned by index. */
	private const INCLUDE_ROW_KEYS = [ 'file_js_position', 'file_js_jquery', 'file_css_media' ];

	/**
	 * Carry over what the free form does not edit — attributes, Pro's display
	 * rules and conditions, Pro-only options. Rebuilding param from the form alone
	 * erased them: a snippet from WP Coder Pro lost its attributes and conditions
	 * the first time it was saved here, and returning to Pro could not restore them.
	 */
	private function keep_other_param( array $param, $request ): array {
		$source = ! empty( $request['tool_id'] ) ? absint( $request['tool_id'] ) : 0;

		// Duplicate posts no id of its own; the copy keeps the original's settings.
		if ( ! $source && isset( $_GET['action'], $_GET['id'] ) && $_GET['action'] === 'duplicate' ) {
			$source = absint( $_GET['id'] );
		}

		if ( ! $source ) {
			return $param;
		}

		$row    = DBManager::get_data_by_id( $source );
		$stored = ( $row && ! empty( $row->param ) ) ? maybe_unserialize( $row->param ) : [];

		if ( ! is_array( $stored ) ) {
			return $param;
		}

		foreach ( $stored as $key => $value ) {
			if ( array_key_exists( $key, $param ) || in_array( $key, self::FORM_PARAM_KEYS, true ) ) {
				continue;
			}

			// Only while the include rows are unchanged — otherwise the indexes
			// would pin Pro's per-file options to the wrong files.
			if ( in_array( $key, self::INCLUDE_ROW_KEYS, true )
			     && count( (array) ( $param['include'] ?? [] ) ) !== count( (array) ( $stored['include'] ?? [] ) ) ) {
				continue;
			}

			$param[ $key ] = $value;
		}

		return $param;
	}

	/**
	 * Attribute names usable in code, and the five arrays kept index-aligned
	 * (as in WP Coder Pro): a row dropped here takes its type, value, label and
	 * help text with it.
	 */
	private function normalize_attributes( array $param ): array {
		$keys  = [ 'shortcode_type', 'shortcode_value', 'shortcode_label', 'shortcode_help' ];
		$lists = [];
		foreach ( $keys as $key ) {
			$lists[ $key ] = ( isset( $param[ $key ] ) && is_array( $param[ $key ] ) ) ? array_values( $param[ $key ] ) : [];
		}

		$clean = array_fill_keys( array_merge( [ 'shortcode_attribute' ], $keys ), [] );
		$seen  = [];

		foreach ( array_values( (array) $param['shortcode_attribute'] ) as $i => $raw ) {
			$name = $this->sanitize_attr_name( $raw );

			// Nothing in the code can reference an unnamed attribute.
			if ( $name === '' ) {
				continue;
			}

			// Duplicates are numbered rather than dropped, as in Pro.
			if ( isset( $seen[ $name ] ) ) {
				$suffix = 2;
				while ( isset( $seen[ $name . '_' . $suffix ] ) ) {
					++ $suffix;
				}
				$name .= '_' . $suffix;
			}

			$seen[ $name ]                  = true;
			$clean['shortcode_attribute'][] = $name;
			$clean['shortcode_type'][]      = (string) ( $lists['shortcode_type'][ $i ] ?? 'text' ) ?: 'text';
			$clean['shortcode_value'][]     = (string) ( $lists['shortcode_value'][ $i ] ?? '' );
			$clean['shortcode_label'][]     = (string) ( $lists['shortcode_label'][ $i ] ?? '' );
			$clean['shortcode_help'][]      = (string) ( $lists['shortcode_help'][ $i ] ?? '' );
		}

		return array_merge( $param, $clean );
	}

	/**
	 * Lower-case letters, digits and underscores. Values reach snippet code
	 * through extract(), and PHP has no variable whose name starts with a digit.
	 */
	private function sanitize_attr_name( $value ): string {
		$value = strtolower( (string) $value );
		$value = preg_replace( '/[^a-z0-9_]/', '_', $value );
		$value = trim( preg_replace( '/_+/', '_', $value ), '_' );

		if ( $value !== '' && ctype_digit( $value[0] ) ) {
			$value = '_' . $value;
		}

		return $value;
	}

	public function sanitize_param( $value ) {
		return wp_unslash( sanitize_text_field( $value ) );
	}

	public function load_styles_scripts(): void {
		$assets_url = WPCoder::url() . 'assets/';
		$version    = WPCoder::info( 'version' );
		$slug       = WPCoder::SLUG;

		wp_enqueue_style( $slug . '-icons', $assets_url . 'icons/css/icons.css', null, $version );
		wp_enqueue_style( $slug . '-admin', $assets_url . 'css/admin.css', null, $version );

		wp_enqueue_script( 'code-editor' );
		wp_enqueue_style( 'code-editor' );
		wp_enqueue_script( 'htmlhint' );
		wp_enqueue_script( 'csslint' );
		wp_enqueue_script( 'jshint' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );

		wp_enqueue_media();

		wp_enqueue_script( $slug . '-admin', $assets_url . 'js/admin.js', array( 'jquery' ), $version );

		wp_enqueue_script( $slug . '-admin-jquery', $assets_url . 'js/admin-jquery.js', array( 'jquery' ), $version );
	}

	public function dashboard(): void {
		DashboardInitializer::init();
	}


	public function header_links(): void {
		?>

        <a href="https://wpcoder.pro/category/documentation/?utm_source=wordpress&utm_medium=admin-menu&utm_campaign=documentation"
           class="wowp-button button button-secondary">
            <span class="dashicons dashicons-book-alt"></span> Documentation
        </a>
        <a href="https://wordpress.org/support/plugin/wp-coder/reviews/" class="wowp-button button button-secondary">
            <span class="dashicons dashicons-star-filled"></span> Reviews
        </a>

        <a href="<?php echo esc_url( WPCoder::PRO_URL ); ?>" target="_blank"
           class="wowp-button button button-dark">
            <span>⭐</span> See PRO Features
        </a>
		<?php
	}


}