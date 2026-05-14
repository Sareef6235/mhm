<?php
/**
 * OTP service.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_OTP {
	public static function generate( $user_id ) {
		global $wpdb;
		$settings = agum_get_settings();
		$otp = (string) wp_rand( 100000, 999999 );
		$expires = gmdate( 'Y-m-d H:i:s', time() + ( absint( $settings['otp_expiry_minutes'] ) * MINUTE_IN_SECONDS ) );

		$wpdb->insert( AGUM_DB::otp_table(), array(
			'user_id'    => absint( $user_id ),
			'otp_hash'   => wp_hash_password( $otp ),
			'expires_at' => get_date_from_gmt( $expires ),
			'created_at' => current_time( 'mysql' ),
		), array( '%d', '%s', '%s', '%s' ) );

		$wpdb->update( AGUM_DB::users_table(), array( 'otp_code' => $otp, 'updated_at' => current_time( 'mysql' ) ), array( 'id' => absint( $user_id ) ), array( '%s', '%s' ), array( '%d' ) );
		AGUM_Logger::log( 'otp_generated', 'Generated one-time password.', $user_id );
		return $otp;
	}

	public static function cleanup_expired() {
		global $wpdb;
		$table = AGUM_DB::otp_table();
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE expires_at < %s OR used_at IS NOT NULL", current_time( 'mysql' ) ) );
	}
}
