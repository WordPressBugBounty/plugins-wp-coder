<?php

defined( 'ABSPATH' ) || exit;

/*
 * "Lite" in the class names keeps them apart from WP Coder Pro's own
 * WPCoder_*_Snippets: activating Pro while this plugin is still active loads
 * both, and a shared global class name is a fatal error.
 */
class WPCoder_Lite_Disabled_Snippets {

	/**
	 * @var false|mixed|null
	 */
	private $options;

	public function __construct() {
		$options = get_option( '_wp_coder_snippets', [] );
		$this->options = $options;

		if ( array_key_exists( 'disable_gutenberg', $options ) ) {
			$this->disable_gutenberg();
		}

		if ( array_key_exists( 'disable_gutenberg_css', $options ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'remove_wp_block_library_css' ] );
		}


		if ( array_key_exists( 'disable_widget_blocks', $options ) ) {
			// Disables the block editor from managing widgets in the Gutenberg plugin.
			add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
			// Disables the block editor from managing widgets.
			add_filter( 'use_widgets_block_editor', '__return_false' );
		}

		if ( array_key_exists( 'remove_wp_version', $options ) ) {
			add_filter( 'the_generator', '__return_empty_string' );
		}

		if ( array_key_exists( 'disable_xml_rpc', $options ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		if ( array_key_exists( 'disable_file_editing', $options ) ) {
			if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
				define( 'DISALLOW_FILE_EDIT', true );
			}
		}

		// The companion to DISALLOW_FILE_EDIT: no installing, updating or deleting
		// plugins and themes from the admin at all.
		if ( array_key_exists( 'disable_file_mods', $options ) ) {
			if ( ! defined( 'DISALLOW_FILE_MODS' ) ) {
				define( 'DISALLOW_FILE_MODS', true );
			}
		}

		if ( array_key_exists( 'disable_author_archives', $options ) ) {
			add_action( 'template_redirect', [ $this, 'disable_author_archives' ] );
			add_filter( 'wp_sitemaps_add_provider', [ $this, 'remove_users_sitemap' ], 10, 2 );
		}

		if ( array_key_exists( 'disable_global_styles', $options ) ) {
			add_action( 'init', [ $this, 'remove_global_styles' ] );
		}

		if ( array_key_exists( 'disable_unused_image_sizes', $options ) ) {
			add_filter( 'intermediate_image_sizes_advanced', [ $this, 'remove_unused_image_sizes' ] );
		}

		if ( array_key_exists( 'disable_gravatar', $options ) ) {
			add_filter( 'pre_get_avatar_data', [ $this, 'replace_gravatar_with_local' ], 10, 2 );
		}

		if ( array_key_exists( 'disable_user_enumeration', $options ) ) {
			// Priority 1: core's redirect_canonical() also runs on template_redirect at
			// 10 and is registered first, so at the default priority it answered
			// ?author=1 with a redirect to /author/<username>/ — the very leak this blocks.
			add_action( 'template_redirect', [ $this, 'block_author_enumeration' ], 1 );
			add_filter( 'rest_endpoints', [ $this, 'block_rest_user_endpoints' ] );
		}

		if ( array_key_exists( 'disable_query_strings', $options ) ) {
			add_filter( 'style_loader_src', [ $this, 'remove_asset_version' ], 15 );
			add_filter( 'script_loader_src', [ $this, 'remove_asset_version' ], 15 );
		}

		if ( array_key_exists( 'disable_heartbeat', $options ) ) {
			add_action( 'init', [ $this, 'disable_heartbeat_frontend' ], 1 );
		}

		if ( array_key_exists( 'disable_login_error_details', $options ) ) {
			add_filter( 'login_errors', [ $this, 'generic_login_error' ] );
		}

		if ( array_key_exists( 'disable_big_image_scaling', $options ) ) {
			add_filter( 'big_image_size_threshold', '__return_false' );
		}

		if ( array_key_exists( 'disable_jquery_migrate', $options ) ) {
			add_action( 'wp_default_scripts', [ $this, 'remove_jquery_migrate' ] );
		}

		if ( array_key_exists( 'disable_dashicons_frontend', $options ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'remove_dashicons_frontend' ] );
		}

		if ( array_key_exists( 'disable_application_passwords', $options ) ) {
			add_filter( 'wp_is_application_passwords_available', '__return_false' );
		}

		if ( array_key_exists( 'disable_admin_email_verification', $options ) ) {
			add_filter( 'admin_email_check_interval', '__return_false' );
		}

		if ( array_key_exists( 'disable_rsd_pingback', $options ) ) {
			remove_action( 'wp_head', 'rsd_link' );
			add_filter( 'wp_headers', [ $this, 'remove_pingback_header' ] );
		}

		if ( array_key_exists( 'default_image_link_none', $options ) ) {
			add_filter( 'pre_option_image_default_link_type', [ $this, 'force_image_link_none' ] );
		}

		if ( array_key_exists( 'disable_admin_bar', $options ) ) {
			add_filter( 'show_admin_bar', [ $this, 'disable_admin_bar' ] );
		}

		if ( array_key_exists( 'disable_automatic_updates', $options ) ) {
			// Disable core auto-updates
			add_filter( 'auto_update_core', '__return_false' );
			// Disable auto-updates for plugins.
			add_filter( 'auto_update_plugin', '__return_false' );
			// Disable auto-updates for themes.
			add_filter( 'auto_update_theme', '__return_false' );
		}

		if ( array_key_exists( 'disable_automatic_updates_emails', $options ) ) {
			// Disable auto-update emails.
			add_filter( 'auto_core_update_send_email', '__return_false' );
			// Disable auto-update emails for plugins.
			add_filter( 'auto_plugin_update_send_email', '__return_false' );
			// Disable auto-update emails for themes.
			add_filter( 'auto_theme_update_send_email', '__return_false' );
		}

		if ( array_key_exists( 'disable_attachment_pages', $options ) ) {
			add_action( 'template_redirect', [ $this, 'redirect_attachment_pages' ], 1 );
		}

		if ( array_key_exists( 'disable_rest_api', $options ) ) {
			add_filter( 'rest_authentication_errors', [ $this, 'disable_rest_api' ] );
		}

		if ( array_key_exists( 'disable_comments', $options ) ) {
			add_action( 'admin_init', [ $this, 'disable_comments' ] );
			// Close comments on the front-end
			add_filter( 'comments_open', '__return_false', 20, 2 );
			add_filter( 'pings_open', '__return_false', 20, 2 );

			// Hide existing comments
			add_filter( 'comments_array', '__return_empty_array', 10, 2 );
			// Remove comments page in menu
			add_action( 'admin_menu', static function () {
				remove_menu_page( 'edit-comments.php' );
			} );
			// Remove comments links from admin bar
			add_action( 'admin_bar_menu', static function () {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
			}, 0 );
		}

		if ( array_key_exists( 'disable_automatic_trash', $options ) ) {
			add_action( 'init', static function () {
				remove_action( 'wp_scheduled_delete', 'wp_scheduled_delete' );
			} );
		}

		if ( array_key_exists( 'disable_emojis', $options ) ) {
			add_action( 'init', [ $this, 'disable_emojis' ] );
		}

		if ( array_key_exists( 'disable_screen_options', $options ) ) {
			add_filter( 'screen_options_show_screen', '__return_false' );
		}

		if ( array_key_exists( 'disable_welcome_panel', $options ) ) {
			add_action( 'admin_init', static function () {
				remove_action( 'welcome_panel', 'wp_welcome_panel' );
			}
			);
		}

		if ( array_key_exists( 'disable_rss_feeds', $options ) ) {
			add_action( 'do_feed_rdf', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_rss', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_rss2', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_atom', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_rss2_comments', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_atom_comments', [ $this, 'disable_feed' ], 1 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
		}

		if ( array_key_exists( 'disable_search', $options ) ) {
			add_action( 'parse_query', [ $this, 'neutralise_search_query' ], 15 );
			// Remove the Search Widget.
			add_action( 'widgets_init', static function () {
				unregister_widget( 'WP_Widget_Search' );
			}
			);
			// Remove the search form.
			add_filter( 'get_search_form', '__return_empty_string', 999 );
			// Remove the core search block.
			add_action( 'init', static function () {
				if ( ! function_exists( 'unregister_block_type' ) || ! class_exists( 'WP_Block_Type_Registry' ) ) {
					return;
				}
				$block = 'core/search';
				if ( WP_Block_Type_Registry::get_instance()->is_registered( $block ) ) {
					unregister_block_type( $block );
				}
			} );
			// Remove admin bar menu search box.
			add_action( 'admin_bar_menu', static function ( $wp_admin_bar ) {
				$wp_admin_bar->remove_menu( 'search' );
			}, 11 );
		}

		if ( array_key_exists( 'disable_login_language_dropdown', $options ) ) {
			add_filter( 'login_display_language_dropdown', '__return_false' );
		}

		if ( array_key_exists( 'disable_login_by_email', $options ) ) {
			remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );
		}

		if ( array_key_exists( 'disable_comment_form_website_url', $options ) ) {
			add_filter( 'comment_form_default_fields', [ $this, 'comment_from_website' ], 150 );
		}

		if ( array_key_exists( 'disable_self_pingbacks', $options ) ) {
			add_action( 'pre_ping', [ $this, 'self_pingbacks' ] );
		}

		if ( array_key_exists( 'disable_wlwmanifest_link', $options ) ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		if ( array_key_exists( 'disable_embeds', $options ) ) {
			add_action( 'init', [ $this, 'disable_embeds' ], 9999 );
		}

		if ( array_key_exists( 'disable_lazy_load', $options ) ) {
			add_filter( 'wp_lazy_loading_enabled', '__return_false' );
		}

		if ( array_key_exists( 'disable_wp_shortlink', $options ) ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		}

		if ( array_key_exists( 'disable_admin_pass_reset_email', $options ) ) {
			remove_action( 'after_password_reset', 'wp_password_change_notification' );
		}

		if ( array_key_exists( 'disable_trackbacks_pingbacks', $options ) ) {
			// Incoming: refuse pingbacks over XML-RPC.
			add_filter( 'xmlrpc_methods', [ $this, 'remove_pingback_methods' ] );
			// New content: ping status closed by default…
			add_filter( 'pre_option_default_ping_status', [ $this, 'return_closed' ] );
			// …and don't notify the linked sites on publish.
			add_filter( 'pre_option_default_pingback_flag', '__return_zero' );
		}

		if ( array_key_exists( 'disable_comments_html', $options ) ) {
			remove_filter('comment_text', 'make_clickable', 9);
			add_filter('pre_comment_content', 'wp_strip_all_tags');
		}

		if ( array_key_exists( 'disable_block_patterns', $options ) ) {
			add_action( 'after_setup_theme', static function () {
				remove_theme_support( 'core-block-patterns' );
			}, 11 );
			add_filter( 'should_load_remote_block_patterns', '__return_false' );
		}

		if ( array_key_exists( 'unsynced_patterns_default', $options ) ) {
			add_action( 'enqueue_block_editor_assets', [ $this, 'unsynced_patterns_default' ] );
		}

	}

	/**
	 * WordPress core doesn't expose a filter for the "Synced" toggle default
	 * in the Create pattern modal, so this switches it off via a small
	 * DOM-watching script instead.
	 */
	public function unsynced_patterns_default(): void {
		$url = \WPCoder\WPCoder::url() . 'assets/js/unsynced-patterns-default.js';
		wp_enqueue_script(
			'wpcoder-unsynced-patterns-default',
			$url,
			[],
			\WPCoder\WPCoder::info( 'version' ),
			true
		);
	}

	public function disable_gutenberg(): void {
		$raw = isset($this->options['disable_gutenberg_except']) ? (string) $this->options['disable_gutenberg_except'] : '';
		$ids = array_values(array_unique(array_filter(array_map(
			static function($v){ $v = trim($v); return ctype_digit($v) ? (int) $v : null; },
			preg_split('/[,\s]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: []
		))));

		add_filter('use_block_editor_for_post', static function( $use_block_editor, $post ) use ( $ids ) {
			if ( empty($post) || ! is_object($post) ) {
				return false;
			}
			if ( empty($ids) ) {
				return false;
			}
			return in_array((int) $post->ID, $ids, true);
		}, 10, 2);

		// Front-end block styles are left alone: content written in the block
		// editor still needs them, and "Disable Gutenberg CSS" removes them on
		// its own. Before 5.0 this snippet only switched the editor.
		add_filter( 'gutenberg_can_edit_post', static function( $can_edit, $post ) use ( $ids ) {
			return ! empty( $ids ) && is_object( $post ) && in_array( (int) $post->ID, $ids, true );
		}, 5, 2 );
	}

	public function disable_embeds(): void {
		// Remove the REST API endpoint.
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );
		// Turn off oEmbed auto discovery.
		add_filter( 'embed_oembed_discover', '__return_false' );
		// Don't filter oEmbed results.
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
		// Remove oEmbed discovery links.
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		// Remove oEmbed-specific JavaScript from the front-end and back-end.
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_filter( 'tiny_mce_plugins', static function ( $plugins ) {
			return array_diff( $plugins, array( 'wpembed' ) );
		} );
		// Remove all embeds rewrite rules.
		add_filter( 'rewrite_rules_array', static function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( false !== strpos( $rewrite, 'embed=true' ) ) {
					unset( $rules[ $rule ] );
				}
			}

			return $rules;
		} );
		// Remove filter of the oEmbed result before any HTTP requests are made.
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
	}

	public function self_pingbacks( &$links ): void {

		$home = get_option( 'home' );
		foreach ( $links as $l => $link ) {
			if ( 0 === strpos( $link, $home ) ) {
				unset( $links[ $l ] );
			}
		}
	}

	public function comment_from_website( $fields ) {
		if ( isset( $fields['url'] ) ) {
			unset( $fields['url'] );
		}

		return $fields;
	}

	/**
	 * 410 Gone: the feed was removed on purpose. wp_die() defaults to 500, which
	 * search engines and feed readers take for a server fault and keep retrying.
	 */
	public function disable_feed(): void {
		wp_die(
			sprintf(
			// Translators: Placeholders for the homepage link.
				esc_html__( 'No feed available, please visit our %1$shomepage%2$s!', 'wp-coder' ),
				' <a href="' . esc_url( home_url( '/' ) ) . '">',
				'</a>'
			),
			'',
			[ 'response' => 410 ]
		);
	}

	public function disable_emojis(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		add_filter( 'tiny_mce_plugins', function ( $plugins ) {
			if ( is_array( $plugins ) ) {
				return array_diff( $plugins, array( 'wpemoji' ) );
			} else {
				return array();
			}
		} );

		// Remove from dns-prefetch.
		add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
			if ( 'dns-prefetch' === $relation_type ) {
				$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
				$urls          = array_diff( $urls, array( $emoji_svg_url ) );
			}

			return $urls;
		}, 10, 2 );
	}

	public function     remove_wp_block_library_css(): void {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
	}

	public function disable_comments(): void {
		global $pagenow;

		if ( $pagenow === 'edit-comments.php' ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
		// Remove comments metabox from dashboard
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		// Disable support for comments and trackbacks in post types
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	public function disable_rest_api( $access ) {
		// When "logged-out only" is on, leave the REST API working for authenticated
		// users (block editor, etc.) and only block anonymous requests.
		if ( array_key_exists( 'disable_rest_api_logged_out_only', $this->options ) && is_user_logged_in() ) {
			return $access;
		}

		return new WP_Error(
			'rest_disabled',
			'The WordPress REST API has been disabled.',
			array(
				'status' => rest_authorization_required_code(),
			)
		);
	}

	public function redirect_attachment_pages(): void {
		global $post;
		if ( ! is_attachment() || ! isset( $post->post_parent ) || ! is_numeric( $post->post_parent ) ) {
			return;
		}
		// Does the attachment have a parent post?
		// If the post is trashed, fallback to redirect to homepage.
		if ( 0 !== $post->post_parent && 'trash' !== get_post_status( $post->post_parent ) ) {
			// Redirect to the attachment parent.
			wp_safe_redirect( get_permalink( $post->post_parent ), 301 );
		} else {
			// For attachment without a parent redirect to homepage.
			wp_safe_redirect( get_bloginfo( 'wpurl' ), 302 );
		}
		exit;
	}

	public function disable_admin_bar( $show_admin_bar ): bool {
		$admin_bar = false;

		$user = wp_get_current_user();

		if ( ! empty( $user->roles ) ) {
			foreach ( $user->roles as $role ) {
				if ( array_key_exists( 'disable_admin_bar_user_' . $role, $this->options ) ) {
					$admin_bar = true;
					break;
				}
			}
		}

		return $admin_bar;
	}

	/**
	 * Redirect ?author=N enumeration probes (used to harvest usernames) to home.
	 */
	public function block_author_enumeration(): void {
		if ( isset( $_GET['author'] ) && ! is_user_logged_in() ) {
			wp_safe_redirect( home_url(), 301 );
			exit;
		}
	}

	/**
	 * Hide the REST users endpoints from anonymous requests (another enumeration vector).
	 */
	public function block_rest_user_endpoints( $endpoints ) {
		if ( is_user_logged_in() ) {
			return $endpoints;
		}
		unset( $endpoints['/wp/v2/users'] );
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

		return $endpoints;
	}

	/**
	 * Strip the ?ver= cache-busting query arg from front-end CSS/JS URLs.
	 */
	public function remove_asset_version( $src ) {
		if ( is_admin() || empty( $src ) ) {
			return $src;
		}
		if ( strpos( $src, 'ver=' ) !== false ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	/**
	 * Drop the Heartbeat API on the front end (kept in admin for autosave / post locks).
	 */
	public function disable_heartbeat_frontend(): void {
		if ( ! is_admin() ) {
			wp_deregister_script( 'heartbeat' );
		}
	}

	/**
	 * Replace detailed login errors with a generic message so valid usernames
	 * can't be discovered.
	 *
	 * The login_errors filter carries every message the login screen shows, not
	 * just credential failures, so the lockout notice from "Limit Login Attempts"
	 * is let through — swallowing it would leave the visitor blocked with no
	 * explanation, retrying and extending their own lockout window.
	 *
	 * @param string $errors Accumulated login error markup.
	 */
	public function generic_login_error( $errors = '' ): string {
		// Literal rather than WPCoder_Lite_Enabled_Snippets::LOGIN_LOCKOUT_MARKER: this
		// class is loaded first (see includes/snippets.php), so referencing the
		// other class here would couple the two load orders. The two are kept in
		// step by a test.
		if ( is_string( $errors ) && strpos( $errors, 'wpcoder-login-lockout' ) !== false ) {
			return $errors;
		}

		return __( 'Invalid username or password.', 'wp-coder' );
	}

	/**
	 * Drop jquery-migrate from jQuery's dependencies on the front end.
	 */
	public function remove_jquery_migrate( $scripts ): void {
		if ( is_admin() ) {
			return;
		}
		if ( ! empty( $scripts->registered['jquery'] ) && ! empty( $scripts->registered['jquery']->deps ) ) {
			$scripts->registered['jquery']->deps = array_diff(
				$scripts->registered['jquery']->deps,
				[ 'jquery-migrate' ]
			);
		}
	}

	/**
	 * Don't load the Dashicons stylesheet for logged-out visitors
	 * (logged-in users keep it for the admin bar).
	 */
	public function remove_dashicons_frontend(): void {
		if ( is_user_logged_in() ) {
			return;
		}
		wp_deregister_style( 'dashicons' );
	}

	/**
	 * Strip the X-Pingback response header (paired with removing the RSD link).
	 */
	public function remove_pingback_header( $headers ) {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	/**
	 * Force the "Link To" default for inserted images to "none" without
	 * persisting the option, so it reverts cleanly when the snippet is off.
	 */
	public function force_image_link_none(): string {
		return 'none';
	}

	// ── Search ────────────────────────────────────────────────────────────────

	/**
	 * Strip the search terms out of a query so nothing can be searched.
	 *
	 * Reads $query->is_search rather than calling the global is_search(): this hook
	 * also fires for secondary queries, where the global describes whichever query
	 * WordPress happens to be showing, not the one being parsed. Only the main
	 * query is turned into a 404 — flagging a widget's query as 404 would be
	 * meaningless.
	 *
	 * @param \WP_Query $query Query being parsed.
	 */
	public function neutralise_search_query( $query ): void {
		if ( is_admin() || empty( $query->is_search ) ) {
			return;
		}

		$query->is_search       = false;
		$query->query_vars['s'] = false;
		$query->query['s']      = false;

		if ( method_exists( $query, 'is_main_query' ) && $query->is_main_query() ) {
			$query->is_404 = true;
		}
	}

	// ── Trackbacks & pingbacks ────────────────────────────────────────────────

	/**
	 * Drop both pingback methods from the XML-RPC surface.
	 *
	 * @param array $methods Registered XML-RPC methods.
	 */
	public function remove_pingback_methods( $methods ) {
		unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );

		return $methods;
	}

	/** Short-circuits the default_ping_status option for newly created posts. */
	public function return_closed(): string {
		return 'closed';
	}

	// ── Author archives ───────────────────────────────────────────────────────

	/**
	 * Send /author/<name>/ home. Complements disable_user_enumeration, which only
	 * covers ?author=N probes and the REST users endpoints — the pretty-permalink
	 * archive is a separate way to confirm a username.
	 */
	public function disable_author_archives(): void {
		if ( ! is_author() ) {
			return;
		}

		wp_safe_redirect( home_url(), 301 );
		exit;
	}

	/**
	 * @param mixed  $provider Sitemap provider instance.
	 * @param string $name     Provider name.
	 * @return mixed False drops the provider.
	 */
	public function remove_users_sitemap( $provider, $name ) {
		return $name === 'users' ? false : $provider;
	}

	// ── Global styles ─────────────────────────────────────────────────────────

	/**
	 * Drop the global-styles inline CSS and the duotone SVG filters that WordPress
	 * prints on every front-end page. Admin is left alone so the editor, which
	 * genuinely needs them, keeps working.
	 */
	public function remove_global_styles(): void {
		if ( is_admin() ) {
			return;
		}

		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles_custom_css' );
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );

		// Core still registers 'block-style-variation-styles' with 'global-styles'
		// as a dependency (WP 6.6+), and since 6.9.1 a missing dependency is a
		// _doing_it_wrong notice. An empty handle satisfies it and prints nothing.
		add_action( 'wp_enqueue_scripts', static function () {
			if ( ! wp_style_is( 'global-styles', 'registered' ) ) {
				wp_register_style( 'global-styles', false );
			}
		} );
	}

	// ── Image sizes ───────────────────────────────────────────────────────────

	/**
	 * Stop generating the extra sizes WordPress adds on top of thumbnail/medium/
	 * large. Only affects new uploads; existing files are untouched.
	 */
	public function remove_unused_image_sizes( $sizes ) {
		unset( $sizes['medium_large'], $sizes['1536x1536'], $sizes['2048x2048'] );

		return $sizes;
	}

	// ── Gravatar ──────────────────────────────────────────────────────────────

	/**
	 * Keep avatar markup but serve a local placeholder, so no visitor request ever
	 * reaches gravatar.com (an external call that also leaks a hashed email).
	 *
	 * @param array $args        Avatar data being assembled.
	 * @param mixed $id_or_email Unused; kept for the filter signature.
	 */
	public function replace_gravatar_with_local( $args, $id_or_email ) {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96">'
		     . '<rect width="96" height="96" fill="#dcdcde"/>'
		     . '<circle cx="48" cy="36" r="16" fill="#a7aaad"/>'
		     . '<path d="M16 96c0-17.7 14.3-32 32-32s32 14.3 32 32z" fill="#a7aaad"/>'
		     . '</svg>';

		$args['url']          = 'data:image/svg+xml;base64,' . base64_encode( $svg );
		$args['found_avatar'] = true;

		return $args;
	}

}

new WPCoder_Lite_Disabled_Snippets;