<?php
/**
 * Security helpers.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Security {
	const NONCE_ACTION = 'agum_admin_action';
	const NONCE_NAME   = 'agum_nonce';

	public static function start_secure_session() {
		if ( headers_sent() || session_id() || is_admin() ) {
			return;
		}

		session_set_cookie_params(
			array(
				'lifetime' => 0,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
		session_start();
	}

	public static function require_capability( $capability = 'manage_options' ) {
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'amia-gallery-user-manager' ) );
		}
	}

	public static function verify_nonce( $action = self::NONCE_ACTION ) {
		$nonce = isset( $_REQUEST[ self::NONCE_NAME ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ self::NONCE_NAME ] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die( esc_html__( 'Security verification failed.', 'amia-gallery-user-manager' ) );
		}
	}

	public static function ajax_guard() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized request.', 'amia-gallery-user-manager' ) ), 403 );
		}
	}

	public static function clean_user_payload( $payload ) {
		$fields = array(
			'student_id', 'admission_no', 'name', 'class', 'dob', 'role', 'username', 'email',
			'password', 'phone_number', 'telegram_username', 'telegram_id', 'image_path',
		);
		$clean = array();
		foreach ( $fields as $field ) {
			$value = isset( $payload[ $field ] ) ? wp_unslash( $payload[ $field ] ) : '';
			$clean[ $field ] = sanitize_text_field( $value );
		}

		if ( ! agum_is_valid_phone( $clean['phone_number'] ) ) {
			return new WP_Error( 'invalid_phone', __( 'Phone number must contain only numbers and an optional country code.', 'amia-gallery-user-manager' ) );
		}

		if ( '' !== $clean['dob'] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $clean['dob'] ) ) {
			return new WP_Error( 'invalid_dob', __( 'Date of birth must use YYYY-MM-DD format.', 'amia-gallery-user-manager' ) );
		}

		$clean['email'] = sanitize_email( $clean['email'] );
		if ( '' !== $clean['email'] && ! is_email( $clean['email'] ) ) {
			return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'amia-gallery-user-manager' ) );
		}

		$clean['role'] = in_array( $clean['role'], array( 'student', 'ustad', 'admin', 'superadmin', 'staff' ), true ) ? $clean['role'] : 'student';
		return $clean;
	}
}
