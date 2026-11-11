<?php

defined( 'ABSPATH' ) || exit;

class WPCoder_Lite_Enabled_Snippets {
	/**
	 * @var false|mixed|null
	 */
	private $options;

	/** @var array<string,bool> Enabled admin-column features (id / thumb / modified). */
	private array $column_features = [];

	/** @var bool True while this class owns the admin-notice output buffer. */
	private bool $notices_buffering = false;

	/** @var int Content images seen so far this request, for the lazy-load threshold. */
	private int $content_images_seen = 0;

	/**
	 * Marks the login-lockout notice so "Hide Login Error Details" can pass it
	 * through instead of replacing it with the generic credentials message.
	 */
	public const LOGIN_LOCKOUT_MARKER = 'wpcoder-login-lockout';

	public function __construct() {
		$options       = get_option( '_wp_coder_snippets', [] );
		$this->options = $options;

		if ( array_key_exists( 'enable_duplication', $options ) ) {
			add_filter( 'post_row_actions', [ $this, 'add_duplicate_link' ], 10, 2 );
			add_filter( 'page_row_actions', [ $this, 'add_duplicate_link' ], 10, 2 );
			add_action( 'admin_action_wpcoder_duplicate_post', [ $this, 'duplicate_post' ] );
			add_action( 'admin_notices', [ $this, 'duplication_admin_notice' ] );
		}

		if ( array_key_exists( 'enable_svg_upload', $options ) ) {
			add_filter( 'upload_mimes', [ $this, 'add_svg_mime' ] );
			add_filter( 'wp_check_filetype_and_ext', [ $this, 'confirm_file_type_is_svg' ], 10, 5 );
			// Strip scripting out of the file before it ever lands in uploads: the
			// upload is admin-only, but the resulting URL is served to every visitor.
			add_filter( 'wp_handle_upload_prefilter', [ $this, 'sanitize_svg_upload' ] );
		}

		if ( array_key_exists( 'enable_pages_excerpt', $options ) ) {
			add_action( 'after_setup_theme', static function () {
				add_post_type_support( 'page', 'excerpt' );
			} );
		}

		if ( array_key_exists( 'enable_shortcode_in_text_widgets', $options ) ) {
			add_filter( 'widget_text', 'do_shortcode' );
		}

		if ( array_key_exists( 'enable_featured_img_rss_feeds', $options ) ) {
			add_filter( 'the_excerpt_rss', [ $this, 'rss_post_thumbnail' ] );
			add_filter( 'the_content_feed', [ $this, 'rss_post_thumbnail' ] );
		}

		if ( array_key_exists( 'enable_page_slug_to_body_class', $options ) ) {
			add_filter( 'body_class', [ $this, 'add_slug_body_class' ] );
		}

		if ( array_key_exists( 'enable_lowercase_filenames_for_uploads', $options ) ) {
			add_filter( 'sanitize_file_name', 'mb_strtolower' );
		}

		if ( array_key_exists( 'enable_default_alt_to_avatar', $options ) ) {
			add_filter( 'pre_get_avatar_data', [ $this, 'default_alt_to_avatar' ] );
		}

		if ( array_key_exists( 'enable_force_external_links_new_tab', $options ) ) {
			add_filter( 'the_content', [ $this, 'force_external_links_new_tab' ] );
		}

		if ( array_key_exists( 'enable_redirect_404_to_home', $options ) ) {
			add_action( 'template_redirect', [ $this, 'redirect_404_to_home' ] );
		}

		if ( array_key_exists( 'custom_editor_styles', $options ) ) {
			add_action( 'enqueue_block_editor_assets', [ $this, 'custom_editor_styles' ] );
		}

		if ( array_key_exists( 'enable_limit_comment_length', $options ) ) {
			add_filter( 'preprocess_comment', [ $this, 'limit_comment_length' ] );
		}

		// wp_content_img_tag only exists from WP 6.0; the plugin still supports 5.4,
		// where there is nothing to hook and the toggle would silently do nothing.
		if ( array_key_exists( 'enable_image_lazy_load', $options )
			&& version_compare( (string) get_bloginfo( 'version' ), '6.0', '>=' ) ) {
			add_filter( 'wp_content_img_tag', [ $this, 'force_image_lazy_load' ], 10, 3 );
		}

		if ( array_key_exists( 'enable_search_by_id', $options ) ) {
			add_action( 'pre_get_posts', [ $this, 'search_posts_by_id' ] );
			add_action( 'pre_get_terms', [ $this, 'search_terms_by_id' ] );
		}

		if ( array_key_exists( 'enable_id_column', $options ) ) {
			$this->column_features['id'] = true;
		}
		if ( array_key_exists( 'enable_featured_image_column', $options ) ) {
			$this->column_features['thumb'] = true;
		}
		if ( array_key_exists( 'enable_last_modified_column', $options ) ) {
			$this->column_features['modified'] = true;
		}
		if ( ! empty( $this->column_features ) ) {
			add_action( 'admin_init', [ $this, 'register_admin_columns' ] );
		}

		if ( array_key_exists( 'enable_security_headers', $options ) ) {
			add_action( 'send_headers', [ $this, 'add_security_headers' ] );
		}

		// Load only the block styles a page actually uses, instead of the whole
		// wp-block-library bundle. The targeted alternative to disable_gutenberg_css.
		if ( array_key_exists( 'enable_separate_block_assets', $options ) ) {
			add_filter( 'should_load_separate_core_block_assets', '__return_true' );
		}

		if ( array_key_exists( 'enable_resource_hints', $options ) ) {
			add_filter( 'wp_resource_hints', [ $this, 'add_resource_hints' ], 10, 2 );
		}

		if ( array_key_exists( 'enable_limit_login_attempts', $options ) ) {
			// Priority 30 so WP's own authentication checks run first.
			add_filter( 'authenticate', [ $this, 'block_locked_out_login' ], 30, 3 );
			add_action( 'wp_login_failed', [ $this, 'record_failed_login' ] );
			add_action( 'wp_login', [ $this, 'clear_login_attempts' ] );
		}

		if ( array_key_exists( 'enable_login_notification', $options ) ) {
			add_action( 'wp_login', [ $this, 'notify_privileged_login' ], 10, 2 );
		}

		if ( array_key_exists( 'enable_hide_admin_notices', $options ) ) {
			add_action( 'admin_notices', [ $this, 'notices_capture_start' ], -9999 );
			add_action( 'all_admin_notices', [ $this, 'notices_capture_end' ], 9999 );
		}

		if ( array_key_exists( 'enable_auto_alt_from_filename', $options ) ) {
			add_action( 'add_attachment', [ $this, 'set_alt_from_filename' ] );
		}

	}

	// ── SVG sanitisation ──────────────────────────────────────────────────────

	/**
	 * Rewrite an uploaded SVG with anything executable removed.
	 *
	 * Runs before WordPress moves the file into uploads, so a rejected file never
	 * reaches the media library. Fails closed: if the markup can't be parsed, or
	 * ext-dom is missing, the upload is refused rather than passed through unchecked.
	 *
	 * @param array $file Entry from $_FILES as assembled by WordPress.
	 */
	public function sanitize_svg_upload( $file ) {
		if ( empty( $file['tmp_name'] ) || ! empty( $file['error'] ) || ! $this->is_svg_upload( $file ) ) {
			return $file;
		}

		/**
		 * Filter whether an uploaded SVG is sanitised. Returning false accepts the
		 * file as-is — only do that for assets from a source you control.
		 *
		 * @param bool  $sanitize Whether to sanitise.
		 * @param array $file     The upload being handled.
		 */
		if ( ! apply_filters( 'wow_coder_sanitize_svg', true, $file ) ) {
			return $file;
		}

		if ( ! class_exists( 'DOMDocument' ) ) {
			$file['error'] = __( 'SVG uploads need the PHP DOM extension so the file can be checked for scripts.', 'wp-coder' );

			return $file;
		}

		$raw = file_get_contents( $file['tmp_name'] );

		if ( $raw === false || trim( $raw ) === '' ) {
			return $file;
		}

		// .svgz is gzipped SVG; unwrap it so the same sanitiser applies.
		$gzipped = strncmp( $raw, "\x1f\x8b", 2 ) === 0;

		if ( $gzipped ) {
			$raw = @gzdecode( $raw );

			if ( $raw === false ) {
				$file['error'] = __( 'This compressed SVG could not be read and was not uploaded.', 'wp-coder' );

				return $file;
			}
		}

		$clean = $this->sanitize_svg_markup( $raw );

		if ( $clean === null ) {
			$file['error'] = __( 'This file could not be read as a valid SVG image and was not uploaded.', 'wp-coder' );

			return $file;
		}

		file_put_contents( $file['tmp_name'], $gzipped ? gzencode( $clean ) : $clean );

		return $file;
	}

	private function is_svg_upload( array $file ): bool {
		if ( ! empty( $file['type'] ) && stripos( (string) $file['type'], 'image/svg' ) === 0 ) {
			return true;
		}

		$ext = strtolower( pathinfo( (string) ( $file['name'] ?? '' ), PATHINFO_EXTENSION ) );

		return in_array( $ext, [ 'svg', 'svgz' ], true );
	}

	/**
	 * @param string $svg Raw SVG markup.
	 * @return string|null Sanitised markup, or null when it isn't usable SVG.
	 */
	public function sanitize_svg_markup( string $svg ): ?string {
		return \WPCoder\Publisher\SvgSanitizer::clean( $svg );
	}

	// ── 404 redirect ──────────────────────────────────────────────────────────

	/**
	 * Send 404s to the homepage.
	 *
	 * 302, not 301: browsers cache a permanent redirect indefinitely, so once a
	 * visitor hit a missing URL they would keep being bounced to the homepage even
	 * after a real page is published there — and the server can't take that back.
	 */
	public function redirect_404_to_home(): void {
		if ( ! is_404() ) {
			return;
		}

		wp_safe_redirect( home_url(), 302 );
		exit;
	}

	// ── Forced image lazy loading ─────────────────────────────────────────────

	/**
	 * Add loading="lazy" / decoding="async" to content images that don't have them.
	 *
	 * The first images on a page are deliberately left eager. WordPress signals
	 * "keep this one eager" by *omitting* the loading attribute, and only adds
	 * fetchpriority="high" when the image is also a plausible LCP candidate — it
	 * skips that for anything below its size threshold. Treating the missing
	 * attribute as "not handled yet" therefore lazy-loaded the very image core
	 * wanted loaded first, hurting LCP instead of helping it.
	 *
	 * @param string $html          The <img> markup.
	 * @param string $context       Where the image came from.
	 * @param int    $attachment_id Attachment ID, 0 when unknown.
	 */
	public function force_image_lazy_load( $html, $context = '', $attachment_id = 0 ) {
		++$this->content_images_seen;

		$has_loading  = strpos( $html, ' loading=' ) !== false;
		$has_decoding = strpos( $html, ' decoding=' ) !== false;

		if ( $has_loading && $has_decoding ) {
			return $html;
		}

		$has_high_priority = strpos( $html, 'fetchpriority="high"' ) !== false
			|| strpos( $html, "fetchpriority='high'" ) !== false;

		$attributes = '';

		if ( ! $has_loading && ! $has_high_priority && $this->content_images_seen > $this->lazy_load_threshold() ) {
			$attributes .= ' loading="lazy"';
		}

		if ( ! $has_decoding ) {
			$attributes .= ' decoding="async"';
		}

		if ( $attributes !== '' ) {
			$html = preg_replace( '/<img\b/i', '<img' . $attributes, $html, 1 );
		}

		return $html;
	}

	/** How many leading images WordPress itself leaves eager. */
	private function lazy_load_threshold(): int {
		if ( function_exists( 'wp_omit_loading_attr_threshold' ) ) {
			return (int) wp_omit_loading_attr_threshold();
		}

		return (int) apply_filters( 'wp_omit_loading_attr_threshold', 1 );
	}

	// ── Resource hints ────────────────────────────────────────────────────────

	/**
	 * Append user-listed domains to WordPress's own resource hints.
	 *
	 * @param array  $hints    URLs already queued for this relation.
	 * @param string $relation preconnect / dns-prefetch / prefetch / prerender.
	 */
	public function add_resource_hints( $hints, $relation ) {
		$map = [
			'preconnect'   => 'resource_hints_preconnect',
			'dns-prefetch' => 'resource_hints_dns_prefetch',
		];

		if ( ! isset( $map[ $relation ] ) || empty( $this->options[ $map[ $relation ] ] ) ) {
			return $hints;
		}

		$lines = preg_split( '/[\r\n]+/', (string) $this->options[ $map[ $relation ] ], -1, PREG_SPLIT_NO_EMPTY );

		foreach ( $lines as $line ) {
			$url = esc_url_raw( trim( $line ) );
			if ( $url !== '' && ! in_array( $url, $hints, true ) ) {
				$hints[] = $url;
			}
		}

		return $hints;
	}

	// ── Limit login attempts ──────────────────────────────────────────────────

	/**
	 * Transient key for the current client.
	 *
	 * Deliberately keyed on REMOTE_ADDR only. X-Forwarded-For is attacker-supplied
	 * unless a trusted proxy is configured, so honouring it would let anyone reset
	 * their own counter by sending a different header.
	 */
	private function login_attempts_key(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';

		return 'wpcoder_lla_' . md5( $ip );
	}

	private function login_attempts_max(): int {
		return max( 1, absint( $this->options['limit_login_attempts_max'] ?? 5 ) );
	}

	private function login_attempts_minutes(): int {
		return max( 1, absint( $this->options['limit_login_attempts_minutes'] ?? 15 ) );
	}

	/** Refuse the attempt while the client is locked out. */
	public function block_locked_out_login( $user, $username = '', $password = '' ) {
		// No credentials submitted — nothing to throttle (also skips cookie auth).
		if ( $username === '' ) {
			return $user;
		}

		if ( (int) get_transient( $this->login_attempts_key() ) >= $this->login_attempts_max() ) {
			// The marker class lets "Hide Login Error Details" recognise this notice
			// and let it through — see generic_login_error(). Without it that snippet
			// replaces this with "Invalid username or password.", leaving the visitor
			// locked out with no idea why and hammering the form to no effect.
			return new WP_Error(
				'wpcoder_too_many_attempts',
				sprintf(
					'<span class="%s">%s</span>',
					self::LOGIN_LOCKOUT_MARKER,
					sprintf(
						/* translators: %d: lockout duration in minutes. */
						__( '<strong>Too many failed login attempts.</strong> Try again in %d minutes.', 'wp-coder' ),
						$this->login_attempts_minutes()
					)
				)
			);
		}

		return $user;
	}

	/** Each failure extends the window, so a burst can't outrun the counter. */
	public function record_failed_login(): void {
		$key   = $this->login_attempts_key();
		$count = (int) get_transient( $key ) + 1;

		set_transient( $key, $count, $this->login_attempts_minutes() * MINUTE_IN_SECONDS );
	}

	public function clear_login_attempts(): void {
		delete_transient( $this->login_attempts_key() );
	}

	// ── Login notification ────────────────────────────────────────────────────

	/** Email the site admin when an account that can manage the site signs in. */
	public function notify_privileged_login( $user_login, $user = null ): void {
		if ( ! $user instanceof WP_User || ! user_can( $user, 'manage_options' ) ) {
			return;
		}

		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';

		$message = sprintf(
			"An administrator account just signed in to %s.\n\nUser: %s\nIP: %s\nTime: %s",
			home_url(),
			$user_login,
			$ip,
			current_time( 'mysql' )
		);

		wp_mail(
			get_option( 'admin_email' ),
			sprintf( '[%s] Administrator login', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ),
			$message
		);
	}

	// ── Collapse admin notices ────────────────────────────────────────────────

	/**
	 * Notices are collected rather than suppressed: a core update warning still has
	 * to be reachable, it just shouldn't push the page down. The buffer opens on the
	 * first notice hook and closes on the last one.
	 */
	public function notices_capture_start(): void {
		$this->notices_buffering = true;
		ob_start();
	}

	public function notices_capture_end(): void {
		// admin_notices fires only outside network/user admin, while all_admin_notices
		// always does. Without this flag the closer could clean a buffer it never
		// opened and swallow someone else's output.
		if ( ! $this->notices_buffering || ob_get_level() === 0 ) {
			return;
		}

		$this->notices_buffering = false;

		$notices = trim( (string) ob_get_clean() );

		if ( $notices === '' ) {
			return;
		}

		$count = max( 1, (int) preg_match_all( '/class="[^"]*\bnotice\b/', $notices ) );

		printf(
			'<details class="wowp-collapsed-notices" style="margin:10px 20px 0 2px;padding:6px 12px;border:1px solid #c3c4c7;border-left-width:4px;background:#fff;">'
			. '<summary style="cursor:pointer;padding:4px 0;">%s</summary><div style="margin-top:8px;">%s</div></details>',
			esc_html( sprintf(
				/* translators: %d: number of hidden admin notices. */
				_n( '%d admin notice', '%d admin notices', $count, 'wp-coder' ),
				$count
			) ),
			$notices // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already-rendered notice markup.
		);
	}

	// ── Alt text from filename ────────────────────────────────────────────────

	/** Fill an empty alt on upload, so the media library starts out accessible. */
	public function set_alt_from_filename( $post_id ): void {
		if ( ! wp_attachment_is_image( $post_id ) ) {
			return;
		}

		if ( trim( (string) get_post_meta( $post_id, '_wp_attachment_image_alt', true ) ) !== '' ) {
			return;
		}

		$name = pathinfo( (string) get_post_meta( $post_id, '_wp_attached_file', true ), PATHINFO_FILENAME );
		$name = preg_replace( '/[-_]+/', ' ', $name );
		$name = preg_replace( '/\s+/', ' ', (string) $name );
		$name = trim( (string) $name );

		if ( $name === '' ) {
			return;
		}

		update_post_meta( $post_id, '_wp_attachment_image_alt', ucfirst( $name ) );
	}

	/**
	 * Let the admin post-list search box find a post/page/CPT by its numeric ID.
	 * Only kicks in when the term is purely numeric and a matching post exists,
	 * otherwise the normal text search is left untouched.
	 */
	public function search_posts_by_id( $query ): void {
		global $pagenow;

		if ( ! is_admin() || $pagenow !== 'edit.php' || ! $query->is_main_query() ) {
			return;
		}

		$search = trim( (string) $query->get( 's' ) );
		if ( $search === '' || ! ctype_digit( $search ) ) {
			return;
		}

		$post = get_post( (int) $search );
		if ( ! $post ) {
			return;
		}

		// Only match when the found post belongs to the list table being viewed.
		$listed_type = $query->get( 'post_type' );
		if ( $listed_type && $listed_type !== 'any' && $post->post_type !== $listed_type ) {
			return;
		}

		$query->set( 's', '' );
		$query->set( 'p', (int) $search );
	}

	/**
	 * Let the admin taxonomy term-list search box find a term by its numeric ID.
	 * Same rule: only when numeric and a matching term in this taxonomy exists.
	 */
	public function search_terms_by_id( $term_query ): void {
		global $pagenow;

		if ( ! is_admin() || $pagenow !== 'edit-tags.php' ) {
			return;
		}

		$vars   = &$term_query->query_vars;
		$search = isset( $vars['search'] ) ? trim( (string) $vars['search'] ) : '';
		if ( $search === '' || ! ctype_digit( $search ) ) {
			return;
		}

		$term = get_term( (int) $search );
		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}

		// Stay within the taxonomy currently being listed.
		if ( ! empty( $vars['taxonomy'] ) ) {
			$taxonomies = (array) $vars['taxonomy'];
			if ( ! in_array( $term->taxonomy, $taxonomies, true ) ) {
				return;
			}
		}

		$vars['search']  = '';
		$vars['include'] = [ (int) $search ];
	}

	/**
	 * Register admin list-table columns for the enabled features across all
	 * post types (ID / featured image / last modified) and term lists (ID).
	 */
	public function register_admin_columns(): void {
		foreach ( get_post_types( [ 'show_ui' => true ], 'names' ) as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", [ $this, 'add_post_columns' ] );
			add_action( "manage_{$post_type}_posts_custom_column", [ $this, 'render_post_column' ], 10, 2 );
		}

		if ( ! empty( $this->column_features['id'] ) ) {
			foreach ( get_taxonomies( [ 'show_ui' => true ], 'names' ) as $taxonomy ) {
				add_filter( "manage_edit-{$taxonomy}_columns", [ $this, 'add_term_id_column' ] );
				add_filter( "manage_{$taxonomy}_custom_column", [ $this, 'render_term_id_column' ], 10, 3 );
			}
		}
	}

	public function add_post_columns( $columns ) {
		if ( ! empty( $this->column_features['id'] ) ) {
			$columns['wpcoder_id'] = __( 'ID', 'wp-coder' );
		}
		if ( ! empty( $this->column_features['thumb'] ) ) {
			$columns['wpcoder_thumb'] = __( 'Image', 'wp-coder' );
		}
		if ( ! empty( $this->column_features['modified'] ) ) {
			$columns['wpcoder_modified'] = __( 'Last Modified', 'wp-coder' );
		}

		return $columns;
	}

	public function render_post_column( $column, $post_id ): void {
		if ( $column === 'wpcoder_id' ) {
			echo (int) $post_id;
		} elseif ( $column === 'wpcoder_thumb' ) {
			echo has_post_thumbnail( $post_id )
				? get_the_post_thumbnail( $post_id, [ 48, 48 ] )
				: '&mdash;';
		} elseif ( $column === 'wpcoder_modified' ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			echo esc_html( get_the_modified_time( $format, $post_id ) );
		}
	}

	public function add_term_id_column( $columns ) {
		$columns['wpcoder_id'] = __( 'ID', 'wp-coder' );

		return $columns;
	}

	public function render_term_id_column( $content, $column, $term_id ) {
		if ( $column === 'wpcoder_id' ) {
			return (string) (int) $term_id;
		}

		return $content;
	}

	/**
	 * Ensure an anchor carries noopener/noreferrer/nofollow, merging into whatever
	 * rel it already has.
	 *
	 * The two checks used to be independent: a link that already had rel="nofollow"
	 * but no target got target="_blank" added and its rel left alone, producing the
	 * one combination this snippet exists to avoid — a new tab with no noopener.
	 *
	 * @param string $tag Opening <a …> tag.
	 */
	private function merge_link_rel( string $tag ): string {
		$needed = [ 'noopener', 'noreferrer', 'nofollow' ];

		if ( ! preg_match( '/\srel=(["\'])(.*?)\1/i', $tag, $rel ) ) {
			return str_ireplace( '<a', '<a rel="' . implode( ' ', $needed ) . '"', $tag );
		}

		$tokens = preg_split( '/\s+/', strtolower( $rel[2] ), -1, PREG_SPLIT_NO_EMPTY ) ?: [];

		foreach ( $needed as $token ) {
			if ( ! in_array( $token, $tokens, true ) ) {
				$tokens[] = $token;
			}
		}

		return str_replace( $rel[0], ' rel="' . implode( ' ', $tokens ) . '"', $tag );
	}

	/**
	 * Send a set of safe, widely-compatible security headers on the front end.
	 */
	public function add_security_headers(): void {
		if ( is_admin() || headers_sent() ) {
			return;
		}

		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}

	public function limit_comment_length( $commentdata ) {
		$max_length = ! empty( $this->options['limit_comment_length'] ) ? absint( $this->options['limit_comment_length'] ) : 300;
		$mess       = ! empty( $this->options['limit_comment_mess'] ) ? $this->options['limit_comment_mess'] : 'Your comment is too long. Max 300 characters.';

		// Characters, not bytes: strlen() counts UTF-8 Cyrillic at 2 bytes and
		// emoji at 4, so a "300 character" limit cut Ukrainian comments at ~150.
		if ( mb_strlen( (string) $commentdata['comment_content'] ) > $max_length ) {
			// esc_html, applied once — the message is body text, and it used to be
			// run through esc_attr twice, so an apostrophe reached the visitor as &#039;.
			wp_die( esc_html( $mess ) );
		}

		return $commentdata;
	}

	public function force_external_links_new_tab( $content ) {
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );

		return preg_replace_callback(
			'#<a\s[^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*>#i',
			function ( $matches ) use ( $site_host ) {
				$href      = $matches[1];
				$link_host = wp_parse_url( $href, PHP_URL_HOST );

				if ( $link_host && $link_host !== $site_host ) {
					$tag = $matches[0];

					if ( stripos( $tag, 'target=' ) === false ) {
						$tag = str_ireplace( '<a', '<a target="_blank"', $tag );
					}

					$tag = $this->merge_link_rel( $tag );

					return $tag;
				}

				return $matches[0];
			},
			$content
		);
	}

	public function default_alt_to_avatar( $atts ) {
		if ( empty( $atts['alt'] ) ) {
			if ( have_comments() ) {
				$author = get_comment_author();
			} else {
				$author = get_the_author_meta( 'display_name' );
			}
			$alt         = sprintf( 'Avatar for %s', $author );
			$atts['alt'] = $alt;
		}

		return $atts;
	}

	public function add_slug_body_class( $classes ) {
		global $post;
		if ( isset( $post ) ) {
			$classes[] = $post->post_type . '-' . $post->post_name;
		}

		return $classes;
	}

	public function rss_post_thumbnail( $content ) {
		global $post;
		if ( has_post_thumbnail( $post->ID ) ) {
			$content = '<p>' . get_the_post_thumbnail( $post->ID ) . '</p>' . $content;
		}

		return $content;
	}

	public function add_duplicate_link( $actions, $post ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'wpcoder_duplicate_post',
					'post'   => $post->ID,
				),
				'admin.php'
			),
			basename( __FILE__ ),
			'duplicate_nonce'
		);

		$actions['duplicate'] = '<a href="' . esc_url( $url ) . '" title="Duplicate this as draft" rel="permalink">Duplicate</a>';

		return $actions;
	}

	public function duplicate_post(): void {
		// check if post ID has been provided and action
		if ( empty( $_GET['post'] ) ) {
			wp_die( 'No post to duplicate has been provided!' );
		}

		// Nonce verification
		if ( ! isset( $_GET['duplicate_nonce'] ) || ! wp_verify_nonce( $_GET['duplicate_nonce'],
				basename( __FILE__ ) ) ) {
			return;
		}

		// Get the original post id
		$post_id = absint( $_GET['post'] );

		// And all the original post data then
		$post = get_post( $post_id );

		/*
		 * if you don't want current user to be the new post author,
		 * then change next couple of lines to this: $new_post_author = $post->post_author;
		 */
		$current_user    = wp_get_current_user();
		$new_post_author = $current_user->ID;

		// if post data exists (I am sure it is, but just in a case), create the post duplicate
		if ( $post ) {
			// new post data array
			// Note: wp_insert_post() internally calls wp_unslash() on the data,
			// so we must use wp_slash() to preserve backslashes in escape sequences like \u0022
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => wp_slash( $post->post_content ),
				'post_excerpt'   => wp_slash( $post->post_excerpt ),
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'draft',
				'post_title'     => wp_slash( $post->post_title ),
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);

			// insert the post by wp_insert_post() function
			$new_post_id = wp_insert_post( $args );

			/*
			 * get all current post terms ad set them to the new post draft
			 */
			$taxonomies = get_object_taxonomies( get_post_type( $post ) ); // returns array of taxonomy names for post type, ex array("category", "post_tag");
			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxonomy ) {
					$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
					wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
				}
			}

			// duplicate all post meta
			$post_meta = get_post_meta( $post_id );
			if ( $post_meta ) {
				foreach ( $post_meta as $meta_key => $meta_values ) {
					if ( '_wp_old_slug' == $meta_key ) { // do nothing for this meta key
						continue;
					}

					foreach ( $meta_values as $meta_value ) {
						// wp_slash is needed because add_post_meta internally calls wp_unslash
						add_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
					}
				}
			}

			// or we can redirect to all posts with a message
			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type' => ( 'post' !== get_post_type( $post ) ? get_post_type( $post ) : false ),
						'saved'     => 'post_duplication_created' // just a custom slug here
					),
					admin_url( 'edit.php' )
				)
			);
			exit;
		}

		wp_die( 'Post creation failed, could not find original post.' );
	}

	public function duplication_admin_notice(): void {
		// Get the current screen
		$screen = get_current_screen();

		if ( 'edit' !== $screen->base ) {
			return;
		}

		//Checks if settings updated
		if ( isset( $_GET['saved'] ) && 'post_duplication_created' === $_GET['saved'] ) {
			echo '<div class="notice notice-success is-dismissible"><p>Post copy created.</p></div>';
		}
	}

	public function custom_editor_styles(): void {
		$url = ! empty( $this->options['custom_editor_styles_url'] )
			? esc_url_raw( $this->options['custom_editor_styles_url'] ) : '';
		if ( empty( $url ) ) {
			return;
		}
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
			return;
		}
		wp_enqueue_style( 'wpcoder-custom-editor-style', $url, [], \WPCoder\WPCoder::info( 'version' ) );
	}

	public function add_svg_mime( $upload_mimes ) {
		// A capability, not a role name: current_user_can('administrator') only
		// works because roles are stored alongside capabilities, and it wrongly
		// says no to a multisite Super Admin with no explicit role on this site.
		if ( ! current_user_can( 'manage_options' ) ) {
			return $upload_mimes;
		}

		$upload_mimes['svg']  = 'image/svg+xml';
		$upload_mimes['svgz'] = 'image/svg+xml';

		return $upload_mimes;
	}

	public function confirm_file_type_is_svg( $wp_check_filetype_and_ext, $file, $filename, $mimes, $real_mime ) {
		if ( ! $wp_check_filetype_and_ext['type'] ) {
			$check_filetype  = wp_check_filetype( $filename, $mimes );
			$ext             = $check_filetype['ext'];
			$type            = $check_filetype['type'];
			$proper_filename = $filename;

			if ( $type && 0 === strpos( $type, 'image/' ) && 'svg' !== $ext ) {
				$ext  = false;
				$type = false;
			}

			$wp_check_filetype_and_ext = compact( 'ext', 'type', 'proper_filename' );
		}

		return $wp_check_filetype_and_ext;
	}
}

new WPCoder_Lite_Enabled_Snippets;