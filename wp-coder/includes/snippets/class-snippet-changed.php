<?php

defined( 'ABSPATH' ) || exit;

class WPCoder_Lite_Changed_Snippets {
	/**
	 * @var false|mixed|null
	 */
	private $options;

	public function __construct() {
		$options       = get_option( '_wp_coder_snippets', [] );
		$this->options = $options;

		if ( array_key_exists( 'change_revisions_control', $options ) ) {
			add_filter( 'wp_revisions_to_keep', [ $this, 'change_revisions_control' ] );
		}

		if ( array_key_exists( 'change_logo_on_site_icon', $options ) ) {
			add_action( 'login_head', [ $this, 'change_logo_on_site_icon' ] );
		}

		if ( array_key_exists( 'change_logo_link', $options ) ) {
			add_filter( 'login_headerurl', [ $this, 'logo_link' ] );
		}

		if ( array_key_exists( 'change_redirect_after_login', $options ) ) {
			add_filter( 'login_redirect', [ $this, 'login_redirect' ], 10, 3 );
		}

		if ( array_key_exists( 'change_redirect_after_logout', $options ) ) {
			add_filter( 'logout_redirect', [ $this, 'logout_redirect' ], 10, 3 );
		}

		if ( array_key_exists( 'change_oembed_size', $options ) ) {
			add_filter( 'embed_defaults', [ $this, 'oembed_defaults' ] );
		}

		if ( array_key_exists( 'change_read_more', $options ) ) {
			add_filter( 'the_content_more_link', [$this,'change_read_more'], 15, 2 );
		}

		if ( array_key_exists( 'change_expiration_remember_me', $options ) ) {
			add_filter( 'auth_cookie_expiration', [$this, 'change_expiration_remember_me']);
		}

		if ( array_key_exists( 'custom_excerpt_length', $options ) ) {
			add_filter( 'excerpt_length', [ $this, 'custom_excerpt_length' ], 999 );
		}

		if ( array_key_exists( 'change_mail_from', $options ) ) {
			add_filter( 'wp_mail_from', [ $this, 'change_mail_from' ] );
			add_filter( 'wp_mail_from_name', [ $this, 'change_mail_from_name' ] );
		}

		if ( array_key_exists( 'change_max_upload_size', $options ) ) {
			add_filter( 'upload_size_limit', [ $this, 'change_max_upload_size' ] );
		}

		if ( array_key_exists( 'change_autosave_interval', $options ) ) {
			add_filter( 'block_editor_settings_all', [ $this, 'change_autosave_interval' ] );
		}

	}

	/**
	 * Outgoing mail sender address. Left untouched when the field is empty or not a
	 * valid address, so a typo can't silently break every email the site sends.
	 */
	public function change_mail_from( $from ) {
		$email = trim( (string) ( $this->options['change_mail_from_email'] ?? '' ) );

		return is_email( $email ) ? $email : $from;
	}

	public function change_mail_from_name( $name ) {
		$custom = trim( (string) ( $this->options['change_mail_from_name'] ?? '' ) );

		return $custom !== '' ? $custom : $name;
	}

	/**
	 * WordPress can only lower this limit — PHP's upload_max_filesize / post_max_size
	 * remain the hard ceiling — so a larger value is ignored rather than pretended.
	 *
	 * @param int $size Current limit in bytes.
	 */
	public function change_max_upload_size( $size ): int {
		$mb = absint( $this->options['change_max_upload_size_mb'] ?? 0 );

		if ( $mb < 1 ) {
			return (int) $size;
		}

		return min( (int) $size, $mb * MB_IN_BYTES );
	}

	/**
	 * Block editor autosave cadence. The classic editor reads the AUTOSAVE_INTERVAL
	 * constant, which WordPress defines before plugins load, so it can't be changed
	 * from here.
	 *
	 * @param array $settings Block editor settings.
	 */
	public function change_autosave_interval( $settings ): array {
		$seconds = absint( $this->options['change_autosave_interval_seconds'] ?? 0 );

		if ( $seconds > 0 ) {
			$settings['autosaveInterval'] = $seconds;
		}

		return (array) $settings;
	}

	public function change_expiration_remember_me() {
		$days = ! empty( $this->options['change_expiration_remember_me_day'] ) ? $this->options['change_expiration_remember_me_day'] : 14;
		return absint($days) * DAY_IN_SECONDS;
	}

	public function change_read_more( $read_more, $read_more_text ) {
		$custom_text = ! empty( $this->options['change_read_more_text'] ) ? $this->options['change_read_more_text'] : 'Read More';
		$read_more   = str_replace( $read_more_text, $custom_text, $read_more );

		return $read_more;
	}

	public function oembed_defaults( $sizes ) {
		$width  = ! empty( $this->options['change_oembed_size_width'] ) ? $this->options['change_oembed_size_width'] : 400;
		$height = ! empty( $this->options['change_oembed_size_height'] ) ? $this->options['change_oembed_size_height'] : 280;

		return array(
			'width'  => absint( $width ),
			'height' => absint( $height ),
		);
	}

	public function logout_redirect( $redirect_to, $request, $user ) {
		if ( ! empty( $this->options['change_redirect_logout_link'] ) ) {
			$redirect_to = get_site_url() . '/' . esc_attr( $this->options['change_redirect_logout_link'] );
		} else {
			$redirect_to = get_site_url();
		}

		return $redirect_to;
	}

	public function login_redirect( $redirect_to, $request, $user ) {
		if ( ! empty( $this->options['change_redirect_login_link'] ) ) {
			$redirect_to = get_site_url() . '/' . esc_attr( $this->options['change_redirect_login_link'] );
		}

		return $redirect_to;
	}

	public function logo_link() {
		return get_home_url();
	}

	public function change_logo_on_site_icon(): void {
		$url = get_site_icon_url( 84 );
		if ( ! empty( $url ) ) {
			echo '<style>#login h1 a { background-image: url(' . esc_url( $url ) . '); }</style>';
		}
	}

	public function change_revisions_control( $limit ): int {
		$revision = ! empty( $this->options['change_revisions_control_number'] ) ? $this->options['change_revisions_control_number'] : '10';

		return absint( $revision );
	}

	public function custom_excerpt_length( int $length ): int {
		$post_type    = get_post_type();
		$per_type_raw = ! empty( $this->options['custom_excerpt_length_by_type'] )
			? $this->options['custom_excerpt_length_by_type'] : '';

		if ( ! empty( $per_type_raw ) && ! empty( $post_type ) ) {
			$lines = preg_split( '/[\r\n]+/', $per_type_raw, -1, PREG_SPLIT_NO_EMPTY );
			foreach ( $lines as $line ) {
				$parts = explode( '=', $line, 2 );
				if ( count( $parts ) === 2 ) {
					$type = trim( $parts[0] );
					$len  = absint( trim( $parts[1] ) );
					if ( $type === $post_type && $len > 0 ) {
						return $len;
					}
				}
			}
		}

		$default = ! empty( $this->options['custom_excerpt_length_default'] )
			? absint( $this->options['custom_excerpt_length_default'] ) : 0;

		return $default > 0 ? $default : $length;
	}

}

new WPCoder_Lite_Changed_Snippets;