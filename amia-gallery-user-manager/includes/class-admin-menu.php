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
		add_action( 'admin_post_agum_sync_wp_users', array( __CLASS__, 'handle_sync_wp_users' ) );
		add_action( 'admin_post_agum_media_upload', array( __CLASS__, 'handle_media_upload' ) );
		add_action( 'admin_post_agum_bulk_image_upload', array( __CLASS__, 'handle_bulk_image_upload' ) );
	}

	public static function register_menu() {
		add_menu_page( __( 'AMIA Gallery', 'amia-gallery-user-manager' ), __( 'AMIA Gallery', 'amia-gallery-user-manager' ), 'manage_options', 'agum-dashboard', array( __CLASS__, 'dashboard_page' ), 'dashicons-groups', 26 );
		add_submenu_page( 'agum-dashboard', __( 'Dashboard', 'amia-gallery-user-manager' ), '🏠 ' . __( 'Dashboard', 'amia-gallery-user-manager' ), 'manage_options', 'agum-dashboard', array( __CLASS__, 'dashboard_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Users', 'amia-gallery-user-manager' ), '👥 ' . __( 'Users', 'amia-gallery-user-manager' ), 'manage_options', 'agum-users', array( __CLASS__, 'users_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Ustads', 'amia-gallery-user-manager' ), '🧑‍🏫 ' . __( 'Ustads', 'amia-gallery-user-manager' ), 'manage_options', 'agum-ustads', array( __CLASS__, 'ustads_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'CSV Upload', 'amia-gallery-user-manager' ), '📤 ' . __( 'CSV Upload', 'amia-gallery-user-manager' ), 'manage_options', 'agum-csv', array( __CLASS__, 'csv_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Bulk Image Upload', 'amia-gallery-user-manager' ), '🖼 ' . __( 'Bulk Image Upload', 'amia-gallery-user-manager' ), 'manage_options', 'agum-bulk-images', array( __CLASS__, 'bulk_images_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Media Upload', 'amia-gallery-user-manager' ), '🖼 ' . __( 'Media Upload', 'amia-gallery-user-manager' ), 'manage_options', 'agum-media', array( __CLASS__, 'media_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Reports', 'amia-gallery-user-manager' ), '📊 ' . __( 'Reports', 'amia-gallery-user-manager' ), 'manage_options', 'agum-reports', array( __CLASS__, 'reports_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Settings', 'amia-gallery-user-manager' ), '⚙ ' . __( 'Settings', 'amia-gallery-user-manager' ), 'manage_options', 'agum-settings', array( __CLASS__, 'settings_page' ) );
		add_submenu_page( 'agum-dashboard', __( 'Security Logs', 'amia-gallery-user-manager' ), '🔒 ' . __( 'Security Logs', 'amia-gallery-user-manager' ), 'manage_options', 'agum-logs', array( __CLASS__, 'logs_page' ) );
	}

	public static function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'agum' ) && ! in_array( $hook, array( 'profile.php', 'user-edit.php' ), true ) ) {
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
	public static function bulk_images_page() { self::wrap( 'upload', array( 'type' => 'bulk-images', 'report' => get_transient( 'agum_bulk_image_report_' . get_current_user_id() ) ) ); }
	public static function reports_page() { self::wrap( 'dashboard', array( 'view' => 'reports', 'stats' => AGUM_Users::stats(), 'logs' => AGUM_Logger::recent( 30 ) ) ); }
	public static function settings_page() { self::wrap( 'settings', array( 'settings' => agum_get_settings(), 'sync_report' => get_transient( 'agum_sync_report_' . get_current_user_id() ) ) ); }
	public static function logs_page() { self::wrap( 'dashboard', array( 'view' => 'logs', 'logs' => AGUM_Logger::recent( 50 ) ) ); }

	public static function handle_save_user() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$payload = $_POST;

		$image = self::prepare_profile_photo_payload( $payload, $id );
		if ( is_wp_error( $image ) ) {
			set_transient( 'agum_form_error_' . get_current_user_id(), $image->get_error_message(), MINUTE_IN_SECONDS * 5 );
			wp_safe_redirect( agum_admin_url( 'agum-users', array( 'message' => 'error' ) ) );
			exit;
		}

		$result = $id ? AGUM_Users::update( $id, $payload ) : AGUM_Users::create( $payload );
		if ( is_wp_error( $result ) ) {
			set_transient( 'agum_form_error_' . get_current_user_id(), $result->get_error_message(), MINUTE_IN_SECONDS * 5 );
		}
		wp_safe_redirect( agum_admin_url( 'agum-users', array( 'message' => is_wp_error( $result ) ? 'error' : 'saved' ) ) );
		exit;
	}

	private static function prepare_profile_photo_payload( &$payload, $id = 0 ) {
		$has_file = ! empty( $_FILES['profile_photo_file']['name'] );
		if ( ! $has_file && ! empty( $_FILES['user_image']['name'] ) ) {
			$_FILES['profile_photo_file'] = $_FILES['user_image'];
			$has_file = true;
		}

		if ( $has_file ) {
			$name = isset( $payload['name'] ) ? sanitize_text_field( wp_unslash( $payload['name'] ) ) : '';
			$result = AGUM_Upload::handle_named_image( $_FILES['profile_photo_file'], $name, $id );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			$payload['image_path'] = $result['url'];
			$payload['profile_photo'] = $result['url'];
			return true;
		}

		if ( $id ) {
			$profile = AGUM_Users::get( $id );
			if ( $profile ) {
				$payload['image_path'] = ! empty( $payload['image_path'] ) ? $payload['image_path'] : $profile->image_path;
				$payload['profile_photo'] = ! empty( $payload['profile_photo'] ) ? $payload['profile_photo'] : ( ! empty( $profile->profile_photo ) ? $profile->profile_photo : $profile->image_path );
			}
		}

		return true;
	}

	public static function handle_csv_import() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$report = AGUM_CSV::import( $_FILES['csv_file'] );
		set_transient( 'agum_last_import_report_' . get_current_user_id(), $report, MINUTE_IN_SECONDS * 10 );
		wp_safe_redirect( agum_admin_url( 'agum-csv', array( 'message' => is_wp_error( $report ) ? 'error' : 'imported' ) ) );
		exit;
	}


	public static function handle_bulk_image_upload() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$report = AGUM_Upload::bulk_upload_images( isset( $_FILES['bulk_images'] ) ? $_FILES['bulk_images'] : array() );
		set_transient( 'agum_bulk_image_report_' . get_current_user_id(), $report, MINUTE_IN_SECONDS * 10 );
		wp_safe_redirect( agum_admin_url( 'agum-bulk-images', array( 'message' => 'images-uploaded' ) ) );
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

	public static function handle_sync_wp_users() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$report = AGUM_Users::migrate_all_to_wp_users();
		set_transient( 'agum_sync_report_' . get_current_user_id(), $report, MINUTE_IN_SECONDS * 10 );
		wp_safe_redirect( agum_admin_url( 'agum-settings', array( 'message' => 'synced' ) ) );
		exit;
	}

	public static function handle_settings() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		$settings = agum_save_settings_from_request( $_POST );
		AGUM_Logger::log( 'settings_updated', 'Updated plugin settings.', null, array( 'columns' => wp_list_pluck( $settings['columns'], 'key' ) ) );
		wp_safe_redirect( agum_admin_url( 'agum-settings', array( 'message' => 'saved' ) ) );
		exit;
	}
}
