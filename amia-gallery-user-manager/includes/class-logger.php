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

		if ( false === $wpdb->insert_id && ! empty( $wpdb->last_error ) ) {
			self::debug( 'log_insert_failed', 'Could not write AGUM activity log.', array( 'last_error' => $wpdb->last_error, 'action' => $action ) );
		}

		self::notify( $action, sanitize_text_field( $message ), $context );
	}

	public static function debug( $event, $message, $context = array() ) {
		global $wpdb;
		$context = is_array( $context ) ? $context : array( 'context' => $context );
		$context['debug_event'] = sanitize_key( $event );

		$wpdb->insert(
			AGUM_DB::logs_table(),
			array(
				'user_id'    => null,
				'action'     => 'debug_' . sanitize_key( $event ),
				'message'    => sanitize_text_field( $message ),
				'context'    => wp_json_encode( $context ),
				'ip_address' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'created_by' => get_current_user_id(),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[AGUM DEBUG] ' . sanitize_key( $event ) . ': ' . $message . ' ' . wp_json_encode( $context ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	public static function notify( $type, $message, $context = array() ) {
		global $wpdb;
		$type = sanitize_key( $type );
		$severity = self::notification_severity( $type );
		$inserted = $wpdb->insert(
			AGUM_DB::notifications_table(),
			array(
				'type'       => $severity,
				'title'      => self::notification_title( $type ),
				'message'    => sanitize_text_field( $message ),
				'is_read'    => 0,
				'user_id'    => get_current_user_id(),
				'context'    => wp_json_encode( array_merge( array( 'event' => $type ), is_array( $context ) ? $context : array() ) ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
		);

		if ( false === $inserted ) {
			self::debug( 'notification_insert_failed', 'Could not write AGUM notification.', array( 'last_error' => $wpdb->last_error, 'type' => $type ) );
		}
	}

	private static function notification_title( $type ) {
		$titles = array(
			'user_created'      => __( 'User created', 'amia-gallery-user-manager' ),
			'user_updated'      => __( 'User updated', 'amia-gallery-user-manager' ),
			'users_deleted'     => __( 'Users deleted', 'amia-gallery-user-manager' ),
			'csv_import'        => __( 'CSV imported', 'amia-gallery-user-manager' ),
			'image_uploaded'    => __( 'Image uploaded', 'amia-gallery-user-manager' ),
			'bulk_image_upload' => __( 'Bulk images uploaded', 'amia-gallery-user-manager' ),
			'role_changed'      => __( 'Role changed', 'amia-gallery-user-manager' ),
		);
		return isset( $titles[ $type ] ) ? $titles[ $type ] : ucwords( str_replace( '_', ' ', $type ) );
	}

	private static function notification_severity( $type ) {
		if ( 0 === strpos( $type, 'debug_' ) || false !== strpos( $type, 'failed' ) || false !== strpos( $type, 'error' ) ) {
			return 'error';
		}
		if ( in_array( $type, array( 'user_created', 'user_updated', 'users_deleted', 'csv_import', 'image_uploaded', 'bulk_image_upload', 'role_changed', 'settings_saved' ), true ) ) {
			return 'success';
		}
		return 'info';
	}

	public static function notifications( $limit = 10, $unread_only = false ) {
		global $wpdb;
		$table = AGUM_DB::notifications_table();
		$where = $unread_only ? 'WHERE is_read = 0' : '';
		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d", max( 1, absint( $limit ) ) ) );
		foreach ( (array) $items as $item ) {
			$item->title = sanitize_text_field( $item->title );
			$item->message = sanitize_text_field( $item->message );
		}
		return $items;
	}

	public static function mark_notifications_read( $ids = array() ) {
		global $wpdb;
		$ids = array_filter( array_map( 'absint', (array) $ids ) );
		if ( $ids ) {
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			return $wpdb->query( $wpdb->prepare( "UPDATE " . AGUM_DB::notifications_table() . " SET is_read = 1 WHERE id IN ({$placeholders})", $ids ) );
		}
		return $wpdb->update( AGUM_DB::notifications_table(), array( 'is_read' => 1 ), array( 'is_read' => 0 ), array( '%d' ), array( '%d' ) );
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
