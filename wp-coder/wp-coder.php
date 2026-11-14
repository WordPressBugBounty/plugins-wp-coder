<?php
/**
 * Plugin Name:       WP Coder
 * Plugin URI:        https://wordpress.org/plugins/wp-coder/
 * Description:       Adding custom HTML, CSS, JavaScript and PHP code to your WordPress site.
 * Version:           5.0.1
 * Author:            WPCoder
 * Author URI:        https://wpcoder.pro
 * Author Email:      hey@wow-company.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-coder
 * Requires at least: 5.3
 * Requires PHP:      7.4
 */

namespace WPCoder;

// Exit if accessed directly.
use WPCoder\Block\WPCoder_Block;
use WPCoder\Dashboard\DBManager;
use WPCoder\Dashboard\FolderManager;
use WPCoder\Update\UpdateDB;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'wpcoder' ) ) :

	final class WPCoder {

		// Plugin slug
		public const SLUG = 'wp-coder';

		// Plugin prefix
		public const PREFIX = 'wow_coder';

		public const FOLDER = 'wp-coder';

		public const SHORTCODE = 'wp_code';

		// Plugin ULR
		public const PluginURL = 'https://wordpress.org/plugins/wp-coder/';

		// Where every "Pro" link in the admin points.
		public const PRO_URL = 'https://wpcoder.pro/pricing/';

		private static $instance;
		/**
		 * @var Autoloader
		 */
		private $autoloader;
		/**
		 * @var Wow_Dashboard
		 */
		private $dashboard;
		private $public;

		public static function instance(): WPCoder {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPCoder ) ) {
				self::$instance = new self;

				self::$instance->includes();
				self::$instance->autoloader = new Autoloader( 'WPCoder' );
				self::$instance->dashboard  = new WOWP_Dashboard();
				self::$instance->public     = new WOWP_Public();

				register_activation_hook( __FILE__, [ self::$instance, 'plugin_activate' ] );
				add_action( 'plugins_loaded', [ self::$instance, 'loaded' ] );
				add_action( 'admin_notices', [ self::class, 'edition_notice' ] );
			}


			return self::$instance;
		}

		// Plugin Root File.
		public static function file(): string {
			return __FILE__;
		}

		// Plugin Base Name.
		public static function basename(): string {
			return plugin_basename( __FILE__ );
		}

		// Plugin Folder Path.
		public static function dir(): string {
			return plugin_dir_path( __FILE__ );
		}

		// Plugin Folder URL.
		public static function url(): string {
			return plugin_dir_url( __FILE__ );
		}


		// Capability required to access the plugin (it can execute PHP, so administrators only).
		public const CAPABILITY = 'manage_options';

		// Check if the current user can manage codes. On multisite, unfiltered_html is limited to super admins.
		public static function user_can_manage(): bool {
			return current_user_can( self::CAPABILITY ) && current_user_can( 'unfiltered_html' );
		}

		// Get Plugin Info
		public static function info( $show = '' ): string {
			$data        = [
				'name'    => 'Plugin Name',
				'version' => 'Version',
				'url'     => 'Plugin URI',
				'author'  => 'Author',
				'email'   => 'Author Email',
			];
			$plugin_data = get_file_data( __FILE__, $data, false );
			$plugin_data['data'] = str_replace( ' ', '-', $plugin_data['name'] );

			return $plugin_data[ $show ] ?? '';
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since  1.0
		 */
		private function includes(): void {
			require_once self::dir() . 'includes/safe-mode.php';
			require_once self::dir() . 'includes/snippets.php';
			require_once self::dir() . 'classes/Autoloader.php';
			require_once self::dir() . 'includes/class-wowp-dashboard.php';
			require_once self::dir() . 'includes/class-wowp-public.php';
		}

		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @return void
		 * @access protected
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'wp-coder' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @return void
		 * @access protected
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'wp-coder' ), '1.0' );
		}

		public function plugin_activate(): void {
			$columns = "
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			title VARCHAR(200) NOT NULL,
			html_code LONGTEXT,
			css_code LONGTEXT,
			js_code LONGTEXT,
			php_code LONGTEXT,
			param LONGTEXT,
			status BOOLEAN,
			mode BOOLEAN,
			tag TEXT,
			php_include int(11) NOT NULL DEFAULT '0',
			pinned tinyint(1) NOT NULL DEFAULT '0',
			UNIQUE KEY id (id),
            INDEX id_index (id)
			";


			DBManager::create( $columns );
			FolderManager::create();
			update_option( self::PREFIX . '_db_version', '4.0' );

			// WP Coder Pro shares the snippets table and options, so only one of
			// the two may run: activating either one deactivates the other.
			self::deactivate_other_edition();
		}

		/**
		 * Deactivates WP Coder Pro (and its old "wp-coder-pro" edition),
		 * whatever folder it was installed in, and leaves a notice saying so.
		 * WP Coder Pro does the same for this plugin.
		 */
		private static function deactivate_other_edition(): void {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$files = [ 'wpcoderpro.php', 'wp-coder-pro.php' ];

			$active = (array) get_option( 'active_plugins', [] );
			if ( is_multisite() ) {
				$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) ) );
			}

			$names = [];
			foreach ( array_unique( $active ) as $plugin ) {
				if ( ! in_array( basename( $plugin ), $files, true ) ) {
					continue;
				}
				$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
				// A file name alone could belong to someone else's plugin.
				if ( strpos( (string) ( $data['Name'] ?? '' ), 'WP Coder' ) !== 0 ) {
					continue;
				}
				deactivate_plugins( $plugin );
				$names[] = $data['Name'];
			}

			if ( $names ) {
				set_transient( 'wpcoder_edition_switched', [
					'from' => implode( ', ', $names ),
					'to'   => 'free',
				], HOUR_IN_SECONDS );
			}
		}

		/**
		 * Once, after an activation that deactivated WP Coder Pro.
		 */
		public static function edition_notice(): void {
			$switched = get_transient( 'wpcoder_edition_switched' );
			if ( ! is_array( $switched ) || ( $switched['to'] ?? '' ) !== 'free' || ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			delete_transient( 'wpcoder_edition_switched' );
			printf(
				'<div class="notice notice-info is-dismissible"><p>%s</p></div>',
				sprintf(
					/* translators: %s: name of the deactivated plugin, e.g. WP Coder Pro. */
					esc_html__( '%s has been deactivated: only one of WP Coder and WP Coder Pro can be active. Your snippets and settings are kept, including those of Pro features, for when you activate it again.', 'wp-coder' ),
					esc_html( $switched['from'] )
				)
			);
		}

		/**
		 * Download the folder with languages.
		 *
		 * @access Publisher
		 * @return void
		 */
		public function loaded(): void {
			new WPCoder_Block();
			UpdateDB::init();
			$languages_folder = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			load_plugin_textdomain( 'wp-coder', false, $languages_folder );
		}

	}

endif;

function wp_coder_run() {
	return WPCoder::instance();
}

wp_coder_run();