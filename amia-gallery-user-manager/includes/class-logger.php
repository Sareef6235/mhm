<?php
/**
 * Activity logger.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Logger {
	public static function log( $action, $message, $user_id = null, $context = array() ) {
		global $wpdb;
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		$wpdb->insert(
			AGUM_DB::logs_table(),
			array(
				'user_id'    => $user_id ? absint( $user_id ) : null,
				'action'     => sanitize_key( $action ),
				'message'    => sanitize_text_field( $message ),
				'context'    => wp_json_encode( $context ),
				'ip_address' => $ip,
				'created_by' => get_current_user_id(),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s' )
		);
	}

	public static function recent( $limit = 12 ) {
		global $wpdb;
		$table = AGUM_DB::logs_table();
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d", absint( $limit ) ) );
	}

	public static function clear() {
		global $wpdb;
		$deleted = $wpdb->query( "TRUNCATE TABLE " . AGUM_DB::logs_table() );
		return false === $deleted ? 0 : $deleted;
	}
}
