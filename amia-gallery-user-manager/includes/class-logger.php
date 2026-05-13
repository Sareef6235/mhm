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

	public static function recent( $limit = 12, $paged = 1, $action = '' ) {
		global $wpdb;
		$table = AGUM_DB::logs_table();
		$where = '1=1';
		$params = array();
		if ( $action ) { $where .= ' AND action = %s'; $params[] = sanitize_key( $action ); }
		$offset = max( 0, ( absint( $paged ) - 1 ) * absint( $limit ) );
		$params[] = absint( $limit ); $params[] = $offset;
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d", $params ) );
	}

	public static function count( $action = '' ) {
		global $wpdb; $table = AGUM_DB::logs_table();
		return $action ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE action = %s", sanitize_key( $action ) ) ) : (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	public static function clear() {
		global $wpdb;
		$deleted = $wpdb->query( "TRUNCATE TABLE " . AGUM_DB::logs_table() );
		return false === $deleted ? 0 : $deleted;
	}
}
