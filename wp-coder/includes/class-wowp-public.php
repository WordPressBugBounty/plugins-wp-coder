<?php

namespace WPCoder;

defined( 'ABSPATH' ) || exit;

use WPCoder\Dashboard\DBManager;
use WPCoder\Dashboard\FolderManager;
use WPCoder\Optimization\HTMLMinifier;
use WPCoder\Publisher\Conditions;
use WPCoder\Publisher\Display;
use WPCoder\Publisher\EnqueueScript;
use WPCoder\Publisher\EnqueueStyle;
use WPCoder\Publisher\CodeSafety;
use WPCoder\Publisher\PHPIncludes;
use WPCoder\Publisher\Placeholders;
use WPCoder\Publisher\SafeMode;
use WPCoder\Publisher\Singleton;

class WOWP_Public {

	/** For callers that hold attributes as an array (the block), see render_atts(). */
	private static $instance = null;

	/** Set while render_atts() runs: block values are always escaped. */
	private $block_render = false;

	/** Content positions are applied once per request (see wrap_content_html()). */
	private $content_wrapped = false;

	public function __construct() {
		self::$instance = $this;

		add_shortcode( 'WP-Coder', [ $this, 'shortcode' ] );
		add_shortcode( WPCoder::SHORTCODE, [ $this, 'shortcode' ] );

		// Display: Everywhere — the snippet's assets, then its HTML at its Location.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_auto' ], 50 );
		add_action( 'wp_head', [ $this, 'print_head_html' ], 99 );
		add_action( 'wp_head', [ $this, 'print_head_styles' ], 100 );
		add_action( 'wp_body_open', [ $this, 'print_body_open_html' ], 1 );
		add_filter( 'the_content', [ $this, 'wrap_content_html' ], 20 );
		add_action( 'wp_footer', [ $this, 'print_footer_html' ], 49 );

		add_action( 'wp_footer', [ $this, 'print_footer' ], 50 );

		new PHPIncludes;
	}

	/**
	 * Render a snippet from an attribute array. The block used to build a
	 * shortcode string and pass it to do_shortcode(), which ended at the first
	 * "]" inside any value and printed the rest on the page.
	 */
	public static function render_atts( array $atts, $content = null ) {
		if ( ! self::$instance ) {
			return '';
		}

		// Block values are escaped whoever views them, as in WP Coder Pro: the
		// editor preview runs over REST with no post to judge the author by, so
		// an administrator opening a Contributor's post would render their values raw.
		self::$instance->block_render = true;
		try {
			return self::$instance->shortcode( $atts, $content );
		} finally {
			self::$instance->block_render = false;
		}
	}

	public function shortcode( $atts, $shortcode_content = null ) {
		if ( ! empty( $atts['id'] ) ) {
			$result = DBManager::get_data_by_id( $atts['id'] );
		} elseif ( ! empty( $atts['title'] ) ) {
			$result = DBManager::get_data_by_title( $atts['title'] );
		} else {
			return false;
		}

		if ( empty( $result ) ) {
			return false;
		}

		$default_atts      = [ 'id' => "", 'title' => '' ];
		$add_atts          = $this->get_shortcode_atts( $result );
		$filtered_new_atts = array_diff_key( $add_atts, $default_atts );
		$result_atts       = array_merge( $default_atts, $filtered_new_atts );

		$attrs = shortcode_atts( $result_atts, $atts, WPCoder::SHORTCODE );

		if ( $this->block_render || self::untrusted_author() ) {
			$attrs = self::sanitize_attrs( $attrs, $this->param( $result ) );
		}

		if ( Conditions::init( $result ) === false ) {
			return false;
		}


		$wp_coder_content_out = $this->get_content( $result, $shortcode_content, $attrs );

		$singleton = Singleton::getInstance();
		$singleton->setValue( $result->id, $result );

		EnqueueStyle::init( $result );
		EnqueueScript::init( $result );

		return $wp_coder_content_out;
	}

	private function get_shortcode_atts( $result ): array {
		$param = maybe_unserialize( $result->param );
		if ( empty( $param['shortcode_attribute'] ) || ! is_array( $param['shortcode_attribute'] ) ) {
			return array();
		}

		return array_fill_keys( $param['shortcode_attribute'], '' );
	}


	/**
	 * Fills `{{name}}` / `{{$name}}` from the snippet's variables. Only scalars:
	 * an array reaching str_replace() as the replacement is a TypeError on PHP 8
	 * that took down every page showing the snippet. Others are left as written.
	 *
	 * Written by Placeholders::fill(), as in WP Coder Pro: inside a tag a value
	 * keeps no raw "<" or ">" — an HTML attribute with `<b>` in a tag broke the
	 * page of a block (wptexturize()).
	 */
	public static function replace_placeholders( string $content, array $vars ): string {
		if ( ! preg_match_all( '/\{\{([^{}]*)\}\}/', $content, $matches ) ) {
			return $content;
		}

		$values = [];
		foreach ( array_unique( $matches[1] ) as $match ) {
			$name = str_replace( '$', '', $match );
			if ( isset( $vars[ $name ] ) && is_scalar( $vars[ $name ] ) ) {
				$values[ $match ] = (string) $vars[ $name ];
			}
		}

		return Placeholders::fill( $content, $values );
	}

	/**
	 * Whether the post being rendered was written by someone who may not publish
	 * raw HTML. A Contributor can put any value in a shortcode or block
	 * attribute, and those values are printed into the snippet's HTML as written.
	 */
	private static function untrusted_author(): bool {
		$post = get_post();

		if ( ! $post || empty( $post->post_author ) ) {
			return false;
		}

		return ! user_can( (int) $post->post_author, 'unfiltered_html' );
	}

	/**
	 * Escape attribute values by type, as WP Coder Pro does. Attributes are
	 * defined in Pro; a snippet that came from Pro keeps its types, an older
	 * one is treated as text.
	 */
	public static function sanitize_attrs( array $attrs, array $param ): array {
		$types = [];
		foreach ( (array) ( $param['shortcode_attribute'] ?? [] ) as $key => $name ) {
			$types[ $name ] = $param['shortcode_type'][ $key ] ?? 'text';
		}

		foreach ( $attrs as $name => $value ) {
			// `id` and `title` too: they also pick the snippet, but that lookup has
			// already happened, and their values still reach {{id}} / {{title}}.
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			switch ( $types[ $name ] ?? 'text' ) {
				case 'url':
				case 'link':
					$attrs[ $name ] = esc_url( (string) $value );
					break;
				case 'image':
				case 'image_preload':
					$attrs[ $name ] = esc_url_raw( (string) $value );
					break;
				case 'post':
				case 'term':
				case 'number':
				case 'range':
					$attrs[ $name ] = is_numeric( $value ) ? (string) $value : '';
					break;
				case 'textarea':
					// get_content() escapes it for every author (nl2br + esc_html).
					break;
				case 'richtext':
				case 'wysiwyg':
				case 'html':
					$attrs[ $name ] = wp_kses_post( (string) $value );
					break;
				default:
					$attrs[ $name ] = esc_attr( (string) $value );
			}
		}

		return $attrs;
	}

	/** Sub-variables of an Image attribute: {{hero_url}}, {{hero_html}}, … */
	private const IMAGE_SUFFIXES = [ '_url', '_width', '_height', '_alt', '_srcset', '_sizes', '_html', '_loading', '_fetchpriority', '_decoding', '_html_high' ];

	private static $image_cache = [];

	/**
	 * What an attribute's type does to its value on the page, as in WP Coder
	 * Pro: textarea keeps its line breaks, an image grows its sub-variables, a
	 * post or term is an id or nothing.
	 */
	private function typed_attrs( array $attrs, array $param ): array {
		foreach ( (array) ( $param['shortcode_attribute'] ?? [] ) as $key => $name ) {
			if ( ! array_key_exists( $name, $attrs ) || ! is_scalar( $attrs[ $name ] ) ) {
				continue;
			}

			switch ( $param['shortcode_type'][ $key ] ?? 'text' ) {
				case 'textarea':
					$attrs[ $name ] = nl2br( esc_html( (string) $attrs[ $name ] ) );
					break;
				case 'image':
				case 'image_preload':
					// An empty image still defines its sub-variables, as empty strings,
					// so {{hero_html}} does not reach the page as written.
					$vars = (string) $attrs[ $name ] !== ''
						? self::expand_image_url( (string) $attrs[ $name ] )
						: array_fill_keys( self::IMAGE_SUFFIXES, '' );
					foreach ( $vars as $suffix => $value ) {
						$attrs[ $name . $suffix ] = $value;
					}
					break;
				case 'post':
				case 'term':
					$id             = absint( $attrs[ $name ] );
					$attrs[ $name ] = $id ? (string) $id : '';
					break;
			}
		}

		return $attrs;
	}

	/**
	 * An image URL as ready-to-use values. {{name_html}} goes through
	 * wp_get_loading_optimization_attributes() (WP 6.3+), so the first image on
	 * the page gets fetchpriority="high" and later ones loading="lazy".
	 */
	private static function expand_image_url( string $url ): array {
		if ( isset( self::$image_cache[ $url ] ) ) {
			return self::$image_cache[ $url ];
		}

		$image_url     = esc_url_raw( $url );
		$width         = 0;
		$height        = 0;
		$alt           = '';
		$srcset        = '';
		$sizes         = '';
		$html          = '';
		$loading       = '';
		$fetchpriority = '';
		$decoding      = '';
		$attachment_id = attachment_url_to_postid( $image_url );

		if ( $attachment_id ) {
			$size = wp_get_attachment_image_src( $attachment_id, 'full' );
			if ( $size ) {
				$width  = (int) $size[1];
				$height = (int) $size[2];
			}
			$alt    = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' ) ?: '';
			$sizes  = wp_get_attachment_image_sizes( $attachment_id, 'full' ) ?: '';

			if ( function_exists( 'wp_get_loading_optimization_attributes' ) ) {
				$opt           = wp_get_loading_optimization_attributes( 'img', [
					'src'    => $image_url,
					'width'  => $width,
					'height' => $height,
					'srcset' => $srcset,
					'sizes'  => $sizes,
				], 'wp_get_attachment_image' );
				$loading       = $opt['loading'] ?? '';
				$fetchpriority = $opt['fetchpriority'] ?? '';
				$decoding      = $opt['decoding'] ?? '';
			}

			$html = wp_get_attachment_image( $attachment_id, 'full', false, array_filter( [
				'loading'       => $loading,
				'fetchpriority' => $fetchpriority,
				'decoding'      => $decoding,
			] ) );
		}

		if ( $alt === '' ) {
			$alt = str_replace( [ '-', '_' ], ' ', pathinfo( basename( $image_url ), PATHINFO_FILENAME ) );
		}

		// An image from outside the media library still gets a usable tag.
		if ( $html === '' && $image_url !== '' ) {
			$html = '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">';
		}

		return self::$image_cache[ $url ] = [
			'_url'           => $image_url,
			'_width'         => $width,
			'_height'        => $height,
			'_alt'           => $alt,
			'_srcset'        => $srcset,
			'_sizes'         => $sizes,
			'_html'          => $html,
			'_loading'       => $loading,
			'_fetchpriority' => $fetchpriority,
			'_decoding'      => $decoding,
			'_html_high'     => $attachment_id
				? wp_get_attachment_image( $attachment_id, 'full', false, [ 'fetchpriority' => 'high', 'loading' => '', 'decoding' => 'sync' ] )
				: $html,
		];
	}

	private function param( $result ): array {
		$param = ! empty( $result->param ) ? maybe_unserialize( $result->param ) : [];

		return is_array( $param ) ? $param : [];
	}

	public function enqueue_auto(): void {
		foreach ( Display::auto() as $result ) {
			EnqueueStyle::init( $result );
			EnqueueScript::init( $result );
		}
	}

	/**
	 * Auto-displayed snippets whose HTML belongs at $location. A snippet also
	 * placed by shortcode or block on this page prints there instead.
	 */
	private function auto_html( string $location ): string {
		$shortcodes = Singleton::getInstance()->getValue();
		$html       = '';

		foreach ( Display::auto() as $id => $result ) {
			if ( array_key_exists( $id, $shortcodes ) || Display::location( $this->param( $result ) ) !== $location ) {
				continue;
			}
			$html .= $this->get_content( $result );
		}

		return $html;
	}

	public function print_head_html(): void {
		echo $this->auto_html( 'head' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- snippet markup
	}

	public function print_body_open_html(): void {
		echo $this->auto_html( 'body_open' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Before/after post content: only the main query's single post or page, in
	 * the loop, and once — the_content also runs for excerpts, feeds, SEO meta
	 * and widgets. Priority 20 is after wpautop and do_shortcode.
	 */
	public function wrap_content_html( $content ) {
		if ( $this->content_wrapped || ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$this->content_wrapped = true;

		return $this->auto_html( 'before_content' ) . $content . $this->auto_html( 'after_content' );
	}

	public function print_footer_html(): void {
		$html = $this->auto_html( 'footer' );

		// A theme that never calls wp_body_open() would drop those snippets.
		if ( ! did_action( 'wp_body_open' ) ) {
			$html .= $this->auto_html( 'body_open' );
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/** Inline CSS of auto-displayed snippets belongs in <head>. */
	public function print_head_styles(): void {
		foreach ( Display::auto() as $result ) {
			EnqueueStyle::inline( $result );
		}
	}

	public function print_footer() {
		$singleton  = Singleton::getInstance();
		$shortcodes = $singleton->getValue();

		foreach ( $shortcodes as $id => $result ) {
			// Its inline CSS already went to <head>.
			if ( ! array_key_exists( $id, Display::auto() ) ) {
				EnqueueStyle::inline( $result );
			}
			EnqueueScript::inline( $result );
		}

		// Auto-displayed snippets not also placed on the page by a shortcode.
		foreach ( Display::auto() as $id => $result ) {
			if ( ! array_key_exists( $id, $shortcodes ) ) {
				EnqueueScript::inline( $result );
			}
		}
	}

	private function get_content( $result, $shortcode_content = null, $attrs = null ) {
		$shortcode_content = !empty($shortcode_content) ? do_shortcode( $shortcode_content ) : '';
		$wp_coder_content = !empty($result->html_code) ? do_shortcode( $result->html_code ) : '';

		if ( ! empty( $attrs ) ) {
			$attrs = $this->typed_attrs( $attrs, $this->param( $result ) );
			extract( $attrs, EXTR_PREFIX_SAME, "wpcp" );
		}

		if ( ! empty( $result->php_code ) ) {
			$path = FolderManager::code_path( $result );
		}

		// The file is missing only when uploads/wp-coder cannot be written; skip it
		// rather than warn on every view (the plugin's pages report the folder).
		// Safe mode skips it — it ran there too, so a fatal in a PHP tab still broke
		// the page meant to recover from it — and so does a forbidden call
		// (CodeSafety, as in WP Coder Pro): the tab used to be included unchecked.
		$wpc_php_safe = null;
		// What the PHP tab prints goes in front of the HTML, returned with it, as in
		// WP Coder Pro. It was echoed while the content was being built, so it landed
		// above the post's content instead of where the shortcode or block stands.
		$wpc_php_output = '';
		if ( ! empty( $result->php_code ) && is_readable( $path ) ) {
			if ( SafeMode::is_safe_mode() ) {
				if ( current_user_can( 'manage_options' ) ) {
					$wpc_php_output = '<!-- WP Coder: PHP tab skipped (safe mode) -->';
				}
			} else {
				[ $wpc_php_safe, $wpc_php_hits ] = CodeSafety::scan_file( $path );
			}
		}

		if ( $wpc_php_safe === false ) {
			error_log( '[WP-Coder] Snippet #' . absint( $result->id ?? 0 ) . ': Blocked: forbidden functions detected (' . implode( ', ', $wpc_php_hits ) . ')' );
			if ( current_user_can( 'manage_options' ) ) {
				$wpc_php_output = '<!-- blocked: forbidden functions detected -->';
			}
		} elseif ( $wpc_php_safe === true ) {
			// An error in the PHP tab (undefined function, TypeError, …) must not take
			// the whole page down: drop the snippet's partial output and log it.
			ob_start();
			try {
				include $path;
				$wpc_php_output = (string) ob_get_clean();
			} catch ( \Throwable $wpc_php_error ) {
				ob_end_clean();
				$wpc_error_message = $wpc_php_error->getMessage() . ' at ' . $wpc_php_error->getFile() . ':' . $wpc_php_error->getLine();
				error_log( '[WP-Coder] Snippet #' . absint( $result->id ?? 0 ) . ': ' . $wpc_error_message );

				if ( current_user_can( 'manage_options' ) ) {
					$wpc_php_output = '<!-- WP Coder snippet #' . absint( $result->id ?? 0 ) . ' PHP error: ' . esc_html( $wpc_error_message ) . ' -->';
				}
			}
		}

		$wp_coder_content = self::replace_placeholders( $wp_coder_content, get_defined_vars() );

		$param = ! empty( $result->param ) ? maybe_unserialize( $result->param ) : [];
		if ( ! empty( $param['minified_html'] ) ) {
			$wp_coder_content = HTMLMinifier::minify( $wp_coder_content );
		}

		return $wpc_php_output . $wp_coder_content;
	}

}