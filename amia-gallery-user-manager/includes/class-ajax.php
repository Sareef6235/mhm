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
	}

	public static function search_users() {
		AGUM_Security::ajax_guard();
		$result = AGUM_Users::query( array(
			'search' => isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '',
			'role' => isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : '',
			'paged' => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1,
		) );
		ob_start();
		agum_template( 'tables', array( 'users' => $result['items'] ) );
		wp_send_json_success( array( 'html' => ob_get_clean(), 'total' => $result['total'] ) );
	}

	public static function save_user() {
		AGUM_Security::ajax_guard();
		$user_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$result = $user_id ? AGUM_Users::update( $user_id, $_POST ) : AGUM_Users::create( $_POST );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'User saved successfully.', 'amia-gallery-user-manager' ) ) );
	}

	public static function delete_users() {
		AGUM_Security::ajax_guard();
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		wp_send_json_success( array( 'deleted' => AGUM_Users::delete( $ids ) ) );
	}

	public static function generate_otp() {
		AGUM_Security::ajax_guard();
		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		wp_send_json_success( array( 'otp' => AGUM_OTP::generate( $user_id ) ) );
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
