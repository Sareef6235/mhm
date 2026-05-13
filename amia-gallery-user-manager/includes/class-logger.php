<?php
/**
 * Activity logger and live notification service.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Logger {
	public static function log( $action, $message, $user_id = null, $context = array() ) {
		global $wpdb;
		$context = self::normalize_context( $context, $user_id, $action );
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
		$context = self::normalize_context( $context, isset( $context['agum_user_id'] ) ? absint( $context['agum_user_id'] ) : null, $type );
		$inserted = $wpdb->insert(
			AGUM_DB::notifications_table(),
			array(
				'type'       => self::notification_severity( $type ),
				'title'      => self::notification_title( $type ),
				'message'    => sanitize_text_field( $message ),
				'is_read'    => 0,
				'user_id'    => get_current_user_id(),
				'context'    => wp_json_encode( array_merge( array( 'event' => $type, 'category' => self::notification_category( $type ) ), $context ) ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
		);

		if ( false === $inserted ) {
			self::debug( 'notification_insert_failed', 'Could not write AGUM notification.', array( 'last_error' => $wpdb->last_error, 'type' => $type ) );
		}
	}

	private static function normalize_context( $context, $agum_user_id = null, $event = '' ) {
		$context = is_array( $context ) ? $context : array();
		$actor = wp_get_current_user();
		$context['actor_id'] = $actor && $actor->ID ? (int) $actor->ID : 0;
		$context['actor_name'] = $actor && $actor->ID ? $actor->display_name : __( 'System', 'amia-gallery-user-manager' );
		$context['source_system'] = ! empty( $context['source_system'] ) ? sanitize_text_field( $context['source_system'] ) : self::source_from_event( $event );
		if ( $agum_user_id ) {
			$context['agum_user_id'] = absint( $agum_user_id );
			$profile = AGUM_Users::get( $agum_user_id );
			if ( $profile ) {
				$context['username'] = ! empty( $context['username'] ) ? $context['username'] : $profile->username;
				$context['role'] = ! empty( $context['role'] ) ? $context['role'] : $profile->role;
				$context['profile_image'] = ! empty( $context['profile_image'] ) ? $context['profile_image'] : ( ! empty( $profile->profile_photo ) ? $profile->profile_photo : $profile->image_path );
			}
		}
		return $context;
	}

	private static function source_from_event( $event ) {
		if ( 'sync_wp_users' === $event || 'user_login' === $event || 'failed_login' === $event ) { return 'WordPress Users'; }
		if ( 'csv_import' === $event ) { return 'CSV Upload'; }
		if ( false !== strpos( $event, 'upload' ) || 'image_uploaded' === $event ) { return 'Bulk Upload'; }
		return 'Plugin Form';
	}

	private static function notification_title( $type ) {
		$titles = array(
			'user_created'      => __( 'New User Added', 'amia-gallery-user-manager' ),
			'user_updated'      => __( 'User Updated', 'amia-gallery-user-manager' ),
			'users_deleted'     => __( 'User Deleted', 'amia-gallery-user-manager' ),
			'csv_import'        => __( 'CSV Upload Completed', 'amia-gallery-user-manager' ),
			'bulk_image_upload' => __( 'Bulk Upload Completed', 'amia-gallery-user-manager' ),
			'image_uploaded'    => __( 'Image Uploaded', 'amia-gallery-user-manager' ),
			'role_changed'      => __( 'Role Changed', 'amia-gallery-user-manager' ),
			'user_login'        => __( 'Login Activity', 'amia-gallery-user-manager' ),
			'failed_login'      => __( 'Failed Login Attempt', 'amia-gallery-user-manager' ),
			'settings_updated'  => __( 'Settings Updated', 'amia-gallery-user-manager' ),
			'settings_saved'    => __( 'Settings Updated', 'amia-gallery-user-manager' ),
		);
		return isset( $titles[ $type ] ) ? $titles[ $type ] : ucwords( str_replace( '_', ' ', $type ) );
	}

	private static function notification_category( $type ) {
		if ( in_array( $type, array( 'user_created', 'user_updated', 'users_deleted', 'role_changed' ), true ) ) { return 'users'; }
		if ( in_array( $type, array( 'csv_import', 'image_uploaded', 'bulk_image_upload' ), true ) ) { return 'uploads'; }
		if ( in_array( $type, array( 'user_login', 'failed_login', 'otp_generated' ), true ) ) { return 'security'; }
		if ( in_array( $type, array( 'settings_updated', 'settings_saved' ), true ) ) { return 'settings'; }
		if ( false !== strpos( $type, 'report' ) ) { return 'reports'; }
		if ( false !== strpos( $type, 'failed' ) || false !== strpos( $type, 'error' ) ) { return 'errors'; }
		return 'all';
	}

	private static function notification_severity( $type ) {
		if ( 0 === strpos( $type, 'debug_' ) || false !== strpos( $type, 'failed' ) || false !== strpos( $type, 'error' ) ) { return 'error'; }
		if ( in_array( $type, array( 'user_created', 'user_updated', 'users_deleted', 'csv_import', 'image_uploaded', 'bulk_image_upload', 'role_changed', 'settings_updated', 'settings_saved', 'user_login' ), true ) ) { return 'success'; }
		return 'info';
	}

	public static function notifications( $limit = 10, $unread_only = false, $args = array() ) {
		global $wpdb;
		$table = AGUM_DB::notifications_table();
		$where = array( '1=1' );
		$params = array();
		if ( $unread_only ) { $where[] = 'is_read = 0'; }
		if ( ! empty( $args['type'] ) && 'all' !== $args['type'] ) { $where[] = 'type = %s'; $params[] = sanitize_key( $args['type'] ); }
		if ( ! empty( $args['category'] ) && 'all' !== $args['category'] ) { $where[] = 'context LIKE %s'; $params[] = '%' . $wpdb->esc_like( '"category":"' . sanitize_key( $args['category'] ) . '"' ) . '%'; }
		if ( ! empty( $args['date'] ) ) { $where[] = 'DATE(created_at) = %s'; $params[] = sanitize_text_field( $args['date'] ); }
		if ( ! empty( $args['search'] ) ) {
			$search = sanitize_text_field( $args['search'] );
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$where[] = '(title LIKE %s OR message LIKE %s OR context LIKE %s OR type LIKE %s OR created_at LIKE %s)';
			array_push( $params, $like, $like, $like, $like, $like );
		}
		$sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY created_at DESC LIMIT %d';
		$params[] = max( 1, absint( $limit ) );
		$items = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
		return array_map( array( __CLASS__, 'format_notification' ), (array) $items );
	}

	public static function format_notification( $item ) {
		$context = json_decode( $item->context, true );
		$context = is_array( $context ) ? $context : array();
		$item->title = sanitize_text_field( $item->title );
		$item->message = sanitize_text_field( $item->message );
		$item->context = $context;
		$item->event = isset( $context['event'] ) ? sanitize_key( $context['event'] ) : '';
		$item->category = isset( $context['category'] ) ? sanitize_key( $context['category'] ) : 'all';
		$item->username = isset( $context['username'] ) ? sanitize_text_field( $context['username'] ) : '';
		$item->role = isset( $context['role'] ) ? sanitize_text_field( $context['role'] ) : '';
		$item->profile_image = ! empty( $context['profile_image'] ) ? esc_url_raw( $context['profile_image'] ) : AGUM_Upload::fallback_image_url();
		$item->source_system = isset( $context['source_system'] ) ? sanitize_text_field( $context['source_system'] ) : '';
		$item->actor_name = isset( $context['actor_name'] ) ? sanitize_text_field( $context['actor_name'] ) : '';
		$item->time_ago = human_time_diff( mysql2date( 'U', $item->created_at ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'amia-gallery-user-manager' );
		return $item;
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

	public static function delete_notifications( $ids = array() ) {
		global $wpdb;
		$ids = array_filter( array_map( 'absint', (array) $ids ) );
		if ( empty( $ids ) ) { return 0; }
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM " . AGUM_DB::notifications_table() . " WHERE id IN ({$placeholders})", $ids ) );
	}

	public static function clear_notifications() {
		global $wpdb;
		$deleted = $wpdb->query( 'TRUNCATE TABLE ' . AGUM_DB::notifications_table() );
		return false === $deleted ? 0 : $deleted;
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
		$deleted = $wpdb->query( 'TRUNCATE TABLE ' . AGUM_DB::logs_table() );
		return false === $deleted ? 0 : $deleted;
	}
}
