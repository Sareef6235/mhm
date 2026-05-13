<?php
/**
 * Admin pages and routing.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Admin_Menu {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_post_agum_save_user', array( __CLASS__, 'handle_save_user' ) );
		add_action( 'admin_post_agum_csv_import', array( __CLASS__, 'handle_csv_import' ) );
		add_action( 'admin_post_agum_export_csv', array( 'AGUM_CSV', 'export' ) );
		add_action( 'admin_post_agum_save_settings', array( __CLASS__, 'handle_settings' ) );
		add_action( 'admin_post_agum_media_upload', array( __CLASS__, 'handle_media_upload' ) );
	}

	public static function register_menu() {
		add_menu_page( __( 'AMIA Gallery', 'amia-gallery-user-manager' ), __( 'AMIA Gallery', 'amia-gallery-user-manager' ), 'manage_options', 'agum-dashboard', array( __CLASS__, 'dashboard_page' ), 'dashicons-groups', 26 );
		add_submenu_page( 'agum-dashboard', __( 'Dashboard', 'amia-gallery-user-manager' ), '🏠 ' . __( 'Dashboard', 'amia-gallery-user-manager' ), 'manage_options', 'agum-dashboard', array( __CLASS__, 'dashboard_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Users', 'amia-gallery-user-manager' ), '👥 ' . __( 'Users', 'amia-gallery-user-manager' ), 'manage_options', 'agum-users', array( __CLASS__, 'users_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Ustads', 'amia-gallery-user-manager' ), '🧑‍🏫 ' . __( 'Ustads', 'amia-gallery-user-manager' ), 'manage_options', 'agum-ustads', array( __CLASS__, 'ustads_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'CSV Upload', 'amia-gallery-user-manager' ), '📤 ' . __( 'CSV Upload', 'amia-gallery-user-manager' ), 'manage_options', 'agum-csv', array( __CLASS__, 'csv_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Media Upload', 'amia-gallery-user-manager' ), '🖼 ' . __( 'Media Upload', 'amia-gallery-user-manager' ), 'manage_options', 'agum-media', array( __CLASS__, 'media_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Reports', 'amia-gallery-user-manager' ), '📊 ' . __( 'Reports', 'amia-gallery-user-manager' ), 'manage_options', 'agum-reports', array( __CLASS__, 'reports_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Settings', 'amia-gallery-user-manager' ), '⚙ ' . __( 'Settings', 'amia-gallery-user-manager' ), 'manage_options', 'agum-settings', array( __CLASS__, 'settings_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Security Logs', 'amia-gallery-user-manager' ), '🔒 ' . __( 'Security Logs', 'amia-gallery-user-manager' ), 'manage_options', 'agum-logs', array( __CLASS__, 'logs_page' ) );
	}

	public static function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'agum' ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'agum-admin', AGUM_URL . 'assets/css/admin.css', array(), AGUM_VERSION );
		wp_enqueue_style( 'agum-dashboard', AGUM_URL . 'assets/css/dashboard.css', array( 'agum-admin' ), AGUM_VERSION );
		wp_enqueue_style( 'agum-responsive', AGUM_URL . 'assets/css/responsive.css', array( 'agum-admin' ), AGUM_VERSION );
		wp_enqueue_style( 'agum-animations', AGUM_URL . 'assets/css/animations.css', array( 'agum-admin' ), AGUM_VERSION );
		wp_enqueue_script( 'agum-modal', AGUM_URL . 'assets/js/modal.js', array( 'jquery' ), AGUM_VERSION, true );
		wp_enqueue_script( 'agum-upload', AGUM_URL . 'assets/js/upload.js', array( 'jquery' ), AGUM_VERSION, true );
		wp_enqueue_script( 'agum-dashboard', AGUM_URL . 'assets/js/dashboard.js', array( 'jquery' ), AGUM_VERSION, true );
		wp_enqueue_script( 'agum-admin', AGUM_URL . 'assets/js/admin.js', array( 'jquery', 'agum-modal', 'agum-upload' ), AGUM_VERSION, true );
		wp_localize_script( 'agum-admin', 'agumAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( AGUM_Security::NONCE_ACTION ),
			'i18n'    => array( 'confirmDelete' => __( 'Delete selected users?', 'amia-gallery-user-manager' ), 'saved' => __( 'Saved successfully.', 'amia-gallery-user-manager' ) ),
		) );
	}

	public static function wrap( $template, $args = array() ) {
		AGUM_Security::require_capability();
		agum_template( $template, $args );
	}

	public static function dashboard_page() { self::wrap( 'dashboard', array( 'stats' => AGUM_Users::stats(), 'logs' => AGUM_Logger::recent() ) ); }
	public static function users_page() { $settings = agum_get_settings(); self::wrap( 'dashboard', array( 'view' => 'users', 'query' => AGUM_Users::query( array( 'per_page' => $settings['items_per_page'] ) ) ) ); }
	public static function ustads_page() { self::wrap( 'dashboard', array( 'view' => 'users', 'query' => AGUM_Users::query( array( 'role' => 'ustad' ) ), 'role' => 'ustad' ) ); }
	public static function csv_page() { self::wrap( 'upload', array( 'type' => 'csv' ) ); }
	public static function media_page() { self::wrap( 'upload', array( 'type' => 'media', 'users' => AGUM_Users::query( array( 'per_page' => 200 ) ) ) ); }
	public static function reports_page() { self::wrap( 'dashboard', array( 'view' => 'reports', 'stats' => AGUM_Users::stats(), 'logs' => AGUM_Logger::recent( 30 ) ) ); }
	public static function settings_page() { self::wrap( 'settings', array( 'settings' => agum_get_settings() ) ); }
	public static function logs_page() { self::wrap( 'dashboard', array( 'view' => 'logs', 'logs' => AGUM_Logger::recent( 50 ) ) ); }

	public static function handle_save_user() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$result = $id ? AGUM_Users::update( $id, $_POST ) : AGUM_Users::create( $_POST );
		if ( ! is_wp_error( $result ) && ! empty( $_FILES['user_image']['name'] ) ) {
			AGUM_Upload::handle_user_image( $_FILES['user_image'], $id ? $id : $result );
		}
		wp_safe_redirect( agum_admin_url( 'agum-users', array( 'message' => is_wp_error( $result ) ? 'error' : 'saved' ) ) );
		exit;
	}

	public static function handle_csv_import() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$report = AGUM_CSV::import( $_FILES['csv_file'] );
		set_transient( 'agum_last_import_report_' . get_current_user_id(), $report, MINUTE_IN_SECONDS * 10 );
		wp_safe_redirect( agum_admin_url( 'agum-csv', array( 'message' => is_wp_error( $report ) ? 'error' : 'imported' ) ) );
		exit;
	}

	public static function handle_media_upload() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		AGUM_Upload::handle_user_image( $_FILES['user_image'], $user_id );
		wp_safe_redirect( agum_admin_url( 'agum-media', array( 'message' => 'uploaded' ) ) );
		exit;
	}

	public static function handle_settings() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$settings = array(
			'items_per_page'      => isset( $_POST['items_per_page'] ) ? absint( $_POST['items_per_page'] ) : 20,
			'otp_expiry_minutes'  => isset( $_POST['otp_expiry_minutes'] ) ? absint( $_POST['otp_expiry_minutes'] ) : 10,
			'enable_dark_mode'    => isset( $_POST['enable_dark_mode'] ) ? 1 : 0,
			'delete_on_uninstall' => isset( $_POST['delete_on_uninstall'] ) ? 1 : 0,
		);
		update_option( 'agum_settings', $settings );
		AGUM_Logger::log( 'settings_updated', 'Updated plugin settings.' );
		wp_safe_redirect( agum_admin_url( 'agum-settings', array( 'message' => 'saved' ) ) );
		exit;
	}
}
