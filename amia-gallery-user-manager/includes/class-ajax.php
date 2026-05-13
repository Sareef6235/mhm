<?php
/**
 * AJAX endpoints.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Ajax {
	public static function init() {
		add_action( 'wp_ajax_agum_search_users', array( __CLASS__, 'search_users' ) );
		add_action( 'wp_ajax_agum_save_user', array( __CLASS__, 'save_user' ) );
		add_action( 'wp_ajax_agum_delete_users', array( __CLASS__, 'delete_users' ) );
		add_action( 'wp_ajax_agum_generate_otp', array( __CLASS__, 'generate_otp' ) );
		add_action( 'wp_ajax_agum_upload_image', array( __CLASS__, 'upload_image' ) );
		add_action( 'wp_ajax_agum_validate_image', array( __CLASS__, 'validate_image' ) );
		add_action( 'wp_ajax_agum_clear_activity', array( __CLASS__, 'clear_activity' ) );
		add_action( 'wp_ajax_agum_save_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'wp_ajax_agum_reset_settings', array( __CLASS__, 'reset_settings' ) );
		add_action( 'wp_ajax_agum_export_settings', array( __CLASS__, 'export_settings' ) );
		add_action( 'wp_ajax_agum_get_notifications', array( __CLASS__, 'get_notifications' ) );
		add_action( 'wp_ajax_agum_mark_notifications_read', array( __CLASS__, 'mark_notifications_read' ) );
	}

	public static function search_users() {
		AGUM_Security::ajax_guard();
		$result = AGUM_Users::query( array(
			'search' => isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '',
			'role' => isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : '',
			'paged' => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1,
			'per_page' => isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 20,
		) );
		ob_start();
		agum_template( 'tables', array( 'users' => $result['items'] ) );
		$page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page = isset( $_GET['per_page'] ) ? max( 1, absint( $_GET['per_page'] ) ) : 20;
		$total_pages = max( 1, (int) ceil( $result['total'] / $per_page ) );
		wp_send_json_success( array( 'html' => ob_get_clean(), 'total' => $result['total'], 'page' => $page, 'total_pages' => $total_pages ) );
	}

	public static function save_user() {
		AGUM_Security::ajax_guard();
		$user_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		AGUM_Logger::debug( 'ajax_save_user', 'AJAX user save requested.', array( 'agum_id' => $user_id, 'mode' => $user_id ? 'update' : 'create' ) );
		$result = $user_id ? AGUM_Users::update( $user_id, $_POST ) : AGUM_Users::create( $_POST );
		if ( is_wp_error( $result ) ) {
			AGUM_Logger::debug( 'ajax_save_user_error', 'AJAX user save failed.', array( 'agum_id' => $user_id, 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ) );
			wp_send_json_error( array( 'message' => $result->get_error_message(), 'code' => $result->get_error_code() ) );
		}
		wp_send_json_success( array( 'message' => $user_id ? __( 'User updated successfully.', 'amia-gallery-user-manager' ) : __( 'User created successfully.', 'amia-gallery-user-manager' ), 'id' => $result ) );
	}

	public static function delete_users() {
		AGUM_Security::ajax_guard();
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		$deleted = AGUM_Users::delete( $ids );
		wp_send_json_success( array( 'deleted' => $deleted, 'message' => sprintf( __( 'Deleted %d users.', 'amia-gallery-user-manager' ), $deleted ) ) );
	}

	public static function generate_otp() {
		AGUM_Security::ajax_guard();
		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		wp_send_json_success( array( 'otp' => AGUM_OTP::generate( $user_id ) ) );
	}



	public static function clear_activity() {
		AGUM_Security::ajax_guard();
		AGUM_Logger::clear();
		wp_send_json_success( array( 'message' => __( 'Recent activity cleared.', 'amia-gallery-user-manager' ) ) );
	}


	public static function save_settings() {
		AGUM_Security::ajax_guard();
		$settings = agum_get_settings();
		$settings['items_per_page'] = isset( $_POST['items_per_page'] ) ? absint( $_POST['items_per_page'] ) : $settings['items_per_page'];
		$settings['otp_expiry_minutes'] = isset( $_POST['otp_expiry_minutes'] ) ? absint( $_POST['otp_expiry_minutes'] ) : $settings['otp_expiry_minutes'];
		$settings['enable_dark_mode'] = ! empty( $_POST['enable_dark_mode'] ) ? 1 : 0;
		$settings['columns'] = isset( $_POST['columns'] ) ? agum_sanitize_columns( wp_unslash( $_POST['columns'] ) ) : $settings['columns'];
		update_option( 'agum_settings', $settings );
		AGUM_DB::sync_dynamic_columns();
		AGUM_Logger::notify( 'settings_saved', __( 'Settings and columns updated.', 'amia-gallery-user-manager' ), $settings );
		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'amia-gallery-user-manager' ), 'settings' => $settings ) );
	}

	public static function reset_settings() {
		AGUM_Security::ajax_guard();
		update_option( 'agum_settings', agum_default_settings() );
		wp_send_json_success( array( 'message' => __( 'Settings reset.', 'amia-gallery-user-manager' ), 'settings' => agum_default_settings() ) );
	}

	public static function export_settings() {
		AGUM_Security::ajax_guard();
		wp_send_json_success( array( 'settings' => agum_get_settings() ) );
	}


	public static function get_notifications() {
		AGUM_Security::ajax_guard();
		$items = AGUM_Logger::notifications( isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 20 );
		$unread = count( AGUM_Logger::notifications( 100, true ) );
		wp_send_json_success( array( 'items' => $items, 'unread' => $unread, 'serverTime' => current_time( 'mysql' ) ) );
	}

	public static function mark_notifications_read() {
		AGUM_Security::ajax_guard();
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		AGUM_Logger::mark_notifications_read( $ids );
		wp_send_json_success( array( 'message' => __( 'Notifications marked as read.', 'amia-gallery-user-manager' ) ) );
	}

	public static function validate_image() {
		AGUM_Security::ajax_guard();
		if ( empty( $_FILES['image'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Profile photo is required.', 'amia-gallery-user-manager' ) ) );
		}
		$result = AGUM_Upload::validate_upload_file( $_FILES['image'] );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Image is valid.', 'amia-gallery-user-manager' ), 'type' => $result['type'], 'ext' => $result['ext'] ) );
	}

	public static function upload_image() {
		AGUM_Security::ajax_guard();
		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$result = AGUM_Upload::handle_user_image( $_FILES['image'], $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( $result );
	}
}
