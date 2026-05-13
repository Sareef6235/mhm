<?php
/**
 * User CRUD service.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Users {
	public static function create( $payload ) {
		global $wpdb;
		$clean = AGUM_Security::clean_user_payload( $payload );
		if ( is_wp_error( $clean ) ) {
			return $clean;
		}

		$now = current_time( 'mysql' );
		$result = $wpdb->insert(
			AGUM_DB::users_table(),
			array(
				'student_id'         => $clean['student_id'],
				'admission_no'       => $clean['admission_no'],
				'name'               => $clean['name'],
				'class'              => $clean['class'],
				'dob'                => $clean['dob'] ? $clean['dob'] : null,
				'role'               => $clean['role'],
				'username'           => $clean['username'],
				'password_hash'      => $clean['password'] ? wp_hash_password( $clean['password'] ) : '',
				'phone_number'       => $clean['phone_number'],
				'telegram_username'  => $clean['telegram_username'],
				'telegram_id'        => $clean['telegram_id'],
				'image_path'         => $clean['image_path'],
				'created_at'         => $now,
				'updated_at'         => $now,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_insert_failed', __( 'Could not create user. Username may already exist.', 'amia-gallery-user-manager' ) );
		}

		$user_id = (int) $wpdb->insert_id;
		AGUM_Upload::auto_assign_existing_image( $user_id, $clean['name'] );
		AGUM_Logger::log( 'user_created', sprintf( 'Created user %s', $clean['username'] ), $user_id );
		return $user_id;
	}

	public static function update( $user_id, $payload ) {
		global $wpdb;
		$user_id = absint( $user_id );
		$clean = AGUM_Security::clean_user_payload( $payload );
		if ( is_wp_error( $clean ) ) {
			return $clean;
		}

		$data = array(
			'student_id'        => $clean['student_id'],
			'admission_no'      => $clean['admission_no'],
			'name'              => $clean['name'],
			'class'             => $clean['class'],
			'dob'               => $clean['dob'] ? $clean['dob'] : null,
			'role'              => $clean['role'],
			'username'          => $clean['username'],
			'phone_number'      => $clean['phone_number'],
			'telegram_username' => $clean['telegram_username'],
			'telegram_id'       => $clean['telegram_id'],
			'image_path'        => $clean['image_path'],
			'updated_at'        => current_time( 'mysql' ),
		);
		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		if ( $clean['password'] ) {
			$data['password_hash'] = wp_hash_password( $clean['password'] );
			$formats[] = '%s';
		}

		$result = $wpdb->update( AGUM_DB::users_table(), $data, array( 'id' => $user_id ), $formats, array( '%d' ) );
		if ( false === $result ) {
			return new WP_Error( 'db_update_failed', __( 'Could not update user.', 'amia-gallery-user-manager' ) );
		}

		AGUM_Upload::auto_assign_existing_image( $user_id, $clean['name'] );
		AGUM_Logger::log( 'user_updated', sprintf( 'Updated user %s', $clean['username'] ), $user_id );
		return true;
	}

	public static function get( $user_id ) {
		global $wpdb;
		$table = AGUM_DB::users_table();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $user_id ) ) );
	}

	public static function delete( $ids ) {
		global $wpdb;
		$ids = array_filter( array_map( 'absint', (array) $ids ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$table = AGUM_DB::users_table();
		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ) );
		AGUM_Logger::log( 'users_deleted', sprintf( 'Deleted %d users', (int) $deleted ), null, array( 'ids' => $ids ) );
		return (int) $deleted;
	}

	public static function query( $args = array() ) {
		global $wpdb;
		$defaults = array( 'search' => '', 'role' => '', 'paged' => 1, 'per_page' => 20 );
		$args = wp_parse_args( $args, $defaults );
		$table = AGUM_DB::users_table();
		$where = array( '1=1' );
		$params = array();

		if ( $args['search'] ) {
			$like = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[] = '(name LIKE %s OR username LIKE %s OR phone_number LIKE %s OR admission_no LIKE %s OR student_id LIKE %s)';
			$params = array_merge( $params, array( $like, $like, $like, $like, $like ) );
		}
		if ( $args['role'] ) {
			$where[] = 'role = %s';
			$params[] = sanitize_key( $args['role'] );
		}

		$where_sql = implode( ' AND ', $where );
		$total_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total = $params ? (int) $wpdb->get_var( $wpdb->prepare( $total_sql, $params ) ) : (int) $wpdb->get_var( $total_sql );

		$offset = max( 0, ( absint( $args['paged'] ) - 1 ) * absint( $args['per_page'] ) );
		$sql_params = array_merge( $params, array( absint( $args['per_page'] ), $offset ) );
		$sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$items = $wpdb->get_results( $wpdb->prepare( $sql, $sql_params ) );

		return array( 'items' => $items, 'total' => $total );
	}

	public static function stats() {
		global $wpdb;
		$table = AGUM_DB::users_table();
		return array(
			'total'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ),
			'students' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE role = %s", 'student' ) ),
			'ustads'   => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE role = %s", 'ustad' ) ),
			'images'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE image_path IS NOT NULL AND image_path != ''" ),
		);
	}
}
