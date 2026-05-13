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

	/**
	 * Required user fields.
	 *
	 * @return array
	 */
	public static function required_user_fields() {
		$required = array();
		foreach ( agum_get_columns() as $column ) {
			if ( ! empty( $column['enabled'] ) && ! empty( $column['required'] ) ) {
				$required[] = $column['key'];
			}
		}
		return array_values( array_diff( array_unique( $required ), array( 'image_path', 'profile_photo' ) ) );
	}

	/**
	 * Clean and validate a user payload.
	 *
	 * @param array $payload Input payload.
	 * @param array $args Validation args.
	 * @return array|WP_Error
	 */
	public static function clean_user_payload( $payload, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'require_password' => true,
				'require_image'    => true,
				'existing_id'      => 0,
			)
		);
		$fields = array(
			'student_id', 'admission_no', 'name', 'class', 'dob', 'role', 'username', 'email',
			'password', 'phone_number', 'image_path', 'profile_photo', 'approval_status', 'notes', 'remarks',
		);
		foreach ( agum_get_columns() as $column ) {
			$fields[] = $column['key'];
		}
		$fields = array_values( array_unique( $fields ) );
		$clean = array();
		foreach ( $fields as $field ) {
			$value = isset( $payload[ $field ] ) ? wp_unslash( $payload[ $field ] ) : '';
			$value = is_scalar( $value ) ? (string) $value : '';
			$value = preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value );
			$value = preg_replace( '/\s+/u', ' ', $value );
			$value = trim( $value );
			$clean[ $field ] = 'image_path' === $field || 'profile_photo' === $field ? esc_url_raw( $value ) : sanitize_text_field( $value );
		}

		$required = self::required_user_fields();
		if ( ! $args['require_password'] ) {
			$required = array_diff( $required, array( 'password' ) );
		}
		$required = array_diff( $required, array( 'image_path', 'profile_photo' ) );
		$missing = array();
		foreach ( $required as $field ) {
			if ( '' === trim( (string) $clean[ $field ] ) ) {
				$missing[] = $field;
			}
		}
		if ( $missing ) {
			return new WP_Error( 'missing_required_fields', sprintf( __( 'Missing required fields: %s', 'amia-gallery-user-manager' ), implode( ', ', $missing ) ) );
		}

		if ( ! agum_is_valid_phone( $clean['phone_number'] ) ) {
			return new WP_Error( 'invalid_phone', __( 'Phone number must contain numbers only, with an optional leading country-code plus sign.', 'amia-gallery-user-manager' ) );
		}

		if ( '' !== $clean['dob'] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $clean['dob'] ) ) {
			return new WP_Error( 'invalid_dob', __( 'Date of birth must use YYYY-MM-DD format.', 'amia-gallery-user-manager' ) );
		}

		$clean['username'] = strtolower( sanitize_user( str_replace( ' ', '_', $clean['username'] ), true ) );
		$clean['email'] = strtolower( sanitize_email( $clean['email'] ) );
		if ( '' !== $clean['email'] && ! is_email( $clean['email'] ) ) {
			return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'amia-gallery-user-manager' ) );
		}

		if ( in_array( 'password', $required, true ) && strlen( (string) $clean['password'] ) < 8 ) {
			return new WP_Error( 'weak_password', __( 'Password is required and must be at least 8 characters.', 'amia-gallery-user-manager' ) );
		}
		if ( ! $args['require_password'] && '' !== $clean['password'] && strlen( (string) $clean['password'] ) < 8 ) {
			return new WP_Error( 'weak_password', __( 'Password must be at least 8 characters when changed.', 'amia-gallery-user-manager' ) );
		}

		foreach ( array( 'image_path', 'profile_photo' ) as $image_field ) {
			if ( '' !== $clean[ $image_field ] ) {
				$image_error = AGUM_Upload::validate_image_reference( $clean[ $image_field ] );
				if ( is_wp_error( $image_error ) ) {
					return $image_error;
				}
			}
		}

		$clean['approval_status'] = in_array( $clean['approval_status'], array( 'approved', 'pending', 'rejected' ), true ) ? $clean['approval_status'] : 'approved';

		$clean['role'] = in_array( $clean['role'], array( 'student', 'ustad', 'admin', 'superadmin', 'staff' ), true ) ? $clean['role'] : 'student';
		return $clean;
	}
}
