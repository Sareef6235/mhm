<?php
/**
 * Plugin Name: AMIA Gallery User Manager Pro
 * Plugin URI: https://amiagallery.mmhnu.online/
 * Description: Premium WordPress user management dashboard with secure CSV import, media mapping, OTPs, reports, and activity logs.
 * Version: 1.0.0
 * Author: AMIA Gallery
 * Author URI: https://amiagallery.mmhnu.online/
 * Text Domain: amia-gallery-user-manager
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGUM_VERSION', '1.0.0' );
define( 'AGUM_FILE', __FILE__ );
define( 'AGUM_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGUM_URL', plugin_dir_url( __FILE__ ) );
define( 'AGUM_SITE_URL', 'https://amiagallery.mmhnu.online/' );

require_once AGUM_PATH . 'includes/helper-functions.php';
require_once AGUM_PATH . 'includes/class-security.php';
require_once AGUM_PATH . 'includes/class-db.php';
require_once AGUM_PATH . 'includes/class-logger.php';
require_once AGUM_PATH . 'includes/class-upload.php';
require_once AGUM_PATH . 'includes/class-users.php';
require_once AGUM_PATH . 'includes/class-csv.php';
require_once AGUM_PATH . 'includes/class-otp.php';
require_once AGUM_PATH . 'includes/class-ajax.php';
require_once AGUM_PATH . 'includes/class-admin-menu.php';

/**
 * Main plugin bootstrap.
 */
final class AMIA_Gallery_User_Manager_Pro {
	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( AGUM_FILE ), array( $this, 'plugin_action_links' ) );
		add_filter( 'get_avatar_url', array( $this, 'get_agum_avatar_url' ), 10, 3 );
		add_action( 'init', array( 'AGUM_Security', 'start_secure_session' ), 1 );
		add_action( 'admin_init', array( $this, 'maybe_sync_native_users' ) );

		AGUM_Admin_Menu::init();
		AGUM_Ajax::init();
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'amia-gallery-user-manager', false, dirname( plugin_basename( AGUM_FILE ) ) . '/languages' );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=agum-settings' ) ),
			esc_html__( 'Settings', 'amia-gallery-user-manager' )
		);

		array_unshift( $links, $settings );
		return $links;
	}

	/**
	 * Add row meta: Version 1.0.0 | By AMIA Gallery | Visit plugin site.
	 *
	 * @param array  $links Existing links.
	 * @param string $file Plugin file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( AGUM_FILE ) !== $file ) {
			return $links;
		}

		return array(
			'version' => esc_html__( 'Version 1.0.0', 'amia-gallery-user-manager' ),
			'author'  => esc_html__( 'By AMIA Gallery', 'amia-gallery-user-manager' ),
			'site'    => sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( AGUM_SITE_URL ),
				esc_html__( 'Visit plugin site', 'amia-gallery-user-manager' )
			),
		);
	}

	/**
	 * Ensure existing AGUM-only profiles are migrated after plugin updates.
	 *
	 * @return void
	 */
	public function maybe_sync_native_users() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( get_option( 'agum_native_user_sync_complete' ) ) {
			return;
		}

		AGUM_DB::create_tables();
		AGUM_Users::migrate_all_to_wp_users();
		update_option( 'agum_native_user_sync_complete', current_time( 'mysql' ) );
	}

	/**
	 * Use AGUM profile image as the WordPress avatar URL when available.
	 *
	 * @param string $url Default avatar URL.
	 * @param mixed  $id_or_email User identifier.
	 * @param array  $args Avatar args.
	 * @return string
	 */
	public function get_agum_avatar_url( $url, $id_or_email, $args ) {
		$user = false;
		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'id', absint( $id_or_email ) );
		} elseif ( $id_or_email instanceof WP_User ) {
			$user = $id_or_email;
		} elseif ( $id_or_email instanceof WP_Comment ) {
			$user = get_user_by( 'email', $id_or_email->comment_author_email );
		} elseif ( is_string( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
		}

		if ( $user ) {
			$image = get_user_meta( $user->ID, 'agum_profile_image', true );
			if ( $image ) {
				return esc_url_raw( $image );
			}
		}

		return $url;
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		AGUM_DB::create_tables();
		AGUM_Upload::ensure_upload_directories();
		AGUM_Users::migrate_all_to_wp_users();
		update_option( 'agum_native_user_sync_complete', current_time( 'mysql' ) );
		add_option( 'agum_settings', agum_default_settings() );
		flush_rewrite_rules();
	}

	/**
	 * Deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'agum_cleanup_expired_otps' );
		flush_rewrite_rules();
	}
}

register_activation_hook( __FILE__, array( 'AMIA_Gallery_User_Manager_Pro', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AMIA_Gallery_User_Manager_Pro', 'deactivate' ) );

AMIA_Gallery_User_Manager_Pro::instance();
