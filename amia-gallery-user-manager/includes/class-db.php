<?php
/**
 * Database schema and helpers.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_DB {
	public static function users_table() {
		global $wpdb;
		return $wpdb->prefix . 'agum_users';
	}

	public static function logs_table() {
		global $wpdb;
		return $wpdb->prefix . 'agum_logs';
	}

	public static function otp_table() {
		global $wpdb;
		return $wpdb->prefix . 'agum_otps';
	}

	public static function notifications_table() {
		global $wpdb;
		return $wpdb->prefix . 'agum_notifications';
	}

	public static function jobs_table() {
		global $wpdb;
		return $wpdb->prefix . 'agum_jobs';
	}

	public static function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$users   = self::users_table();
		$logs    = self::logs_table();
		$otps    = self::otp_table();
		$notifications = self::notifications_table();
		$jobs = self::jobs_table();

		dbDelta( "CREATE TABLE {$users} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			wp_user_id bigint(20) unsigned NULL,
			student_id varchar(80) DEFAULT '' NOT NULL,
			admission_no varchar(80) DEFAULT '' NOT NULL,
			name varchar(190) NOT NULL,
			class varchar(80) DEFAULT '' NOT NULL,
			dob date NULL,
			role varchar(40) DEFAULT 'student' NOT NULL,
			username varchar(120) NOT NULL,
			email varchar(190) DEFAULT '' NOT NULL,
			password_hash varchar(255) DEFAULT '' NOT NULL,
			phone_number varchar(20) DEFAULT '' NOT NULL,
			image_path text NULL,
			profile_photo text NULL,
			otp_code varchar(20) DEFAULT '' NOT NULL,
			last_login datetime NULL,
			last_seen datetime NULL,
			approval_status varchar(30) DEFAULT 'approved' NOT NULL,
			notes text NULL,
			remarks text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY username (username),
			KEY wp_user_id (wp_user_id),
			KEY student_id (student_id),
			KEY admission_no (admission_no),
			KEY email (email),
			KEY role (role),
			KEY phone_number (phone_number)
		) {$charset};" );

		dbDelta( "CREATE TABLE {$logs} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NULL,
			action varchar(120) NOT NULL,
			message text NOT NULL,
			context longtext NULL,
			ip_address varchar(64) DEFAULT '' NOT NULL,
			created_by bigint(20) unsigned NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY action (action),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$charset};" );

		dbDelta( "CREATE TABLE {$otps} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			otp_hash varchar(255) NOT NULL,
			expires_at datetime NOT NULL,
			used_at datetime NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY expires_at (expires_at)
		) {$charset};" );


		dbDelta( "CREATE TABLE {$notifications} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			type varchar(80) DEFAULT 'info' NOT NULL,
			title varchar(190) NOT NULL,
			message text NOT NULL,
			is_read tinyint(1) DEFAULT 0 NOT NULL,
			user_id bigint(20) unsigned NULL,
			context longtext NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY is_read (is_read),
			KEY type (type),
			KEY created_at (created_at)
		) {$charset};" );

		dbDelta( "CREATE TABLE {$jobs} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			job_type varchar(120) NOT NULL,
			status varchar(30) DEFAULT 'pending' NOT NULL,
			payload longtext NULL,
			attempts int(11) DEFAULT 0 NOT NULL,
			created_at datetime NOT NULL,
			started_at datetime NULL,
			completed_at datetime NULL,
			PRIMARY KEY  (id),
			KEY job_type (job_type),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset};" );

		self::repair_schema();
		self::sync_dynamic_columns();
	}

	/**
	 * Repair legacy schema problems that caused false duplicate failures.
	 *
	 * @return void
	 */
	private static function repair_schema() {
		global $wpdb;
		$tables = array( self::users_table(), self::logs_table(), self::otp_table(), self::notifications_table(), self::jobs_table() );
		$charset_collate = $wpdb->get_charset_collate();

		foreach ( $tables as $table ) {
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
				continue;
			}
			if ( preg_match( '/DEFAULT CHARSET=([^\s]+)(?: COLLATE=([^\s]+))?/i', $charset_collate, $matches ) ) {
				$charset = sanitize_key( $matches[1] );
				$collate = ! empty( $matches[2] ) ? sanitize_key( $matches[2] ) : '';
				$wpdb->query( "ALTER TABLE {$table} CONVERT TO CHARACTER SET {$charset}" . ( $collate ? " COLLATE {$collate}" : '' ) );
			}
		}

		$users = self::users_table();
		foreach ( array( 'username', 'email', 'student_id', 'admission_no', 'wp_user_id' ) as $index ) {
			$index_row = $wpdb->get_row( $wpdb->prepare( "SHOW INDEX FROM {$users} WHERE Key_name = %s AND Non_unique = 0", $index ) );
			if ( $index_row ) {
				$wpdb->query( "ALTER TABLE {$users} DROP INDEX {$index}" );
				AGUM_Logger::debug( 'schema_repair', sprintf( 'Dropped legacy UNIQUE index %s from AGUM users table.', $index ), array( 'table' => $users ) );
			}
		}

		$existing_columns = $wpdb->get_col( "DESC {$users}", 0 );
		foreach ( array( 'username', 'email', 'student_id', 'admission_no', 'wp_user_id', 'role', 'phone_number' ) as $index ) {
			if ( ! in_array( $index, $existing_columns, true ) ) {
				continue;
			}
			$index_row = $wpdb->get_row( $wpdb->prepare( "SHOW INDEX FROM {$users} WHERE Key_name = %s", $index ) );
			if ( ! $index_row ) {
				$wpdb->query( "ALTER TABLE {$users} ADD INDEX {$index} ({$index})" );
			}
		}

		$wpdb->query( "ALTER TABLE {$users} MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT" );
	}

	public static function sync_dynamic_columns( $previous_columns = array() ) {
		global $wpdb;
		$table = self::users_table();
		wp_cache_delete( 'agum_user_columns', 'agum' );
		$existing = self::user_columns();
		$current_columns = agum_get_columns();
		$current_keys = array_map( 'sanitize_key', wp_list_pluck( $current_columns, 'key' ) );

		foreach ( $current_columns as $column ) {
			$key = isset( $column['key'] ) ? sanitize_key( $column['key'] ) : '';
			if ( ! $key || 'password' === $key || ! preg_match( '/^[a-z_][a-z0-9_]*$/', $key ) ) {
				continue;
			}
			$type = self::sql_type_for_column( isset( $column['type'] ) ? $column['type'] : 'text' );
			if ( ! in_array( $key, $existing, true ) ) {
				$wpdb->query( "ALTER TABLE {$table} ADD COLUMN {$key} {$type} NULL" );
				AGUM_Logger::debug( 'schema_column_added', 'Added AGUM dynamic column.', array( 'column' => $key, 'type' => $type, 'last_error' => $wpdb->last_error ) );
				wp_cache_delete( 'agum_user_columns', 'agum' );
				$existing = self::user_columns();
			} elseif ( ! in_array( $key, self::protected_columns(), true ) ) {
				$wpdb->query( "ALTER TABLE {$table} MODIFY {$key} {$type} NULL" );
				AGUM_Logger::debug( 'schema_column_modified', 'Synchronized AGUM dynamic column type.', array( 'column' => $key, 'type' => $type, 'last_error' => $wpdb->last_error ) );
			}
		}

		foreach ( (array) $previous_columns as $previous ) {
			$key = isset( $previous['key'] ) ? sanitize_key( $previous['key'] ) : '';
			if ( ! $key || in_array( $key, $current_keys, true ) || ! in_array( $key, self::user_columns(), true ) || in_array( $key, self::protected_columns(), true ) || ! preg_match( '/^[a-z_][a-z0-9_]*$/', $key ) ) {
				continue;
			}
			$wpdb->query( "ALTER TABLE {$table} DROP COLUMN {$key}" );
			AGUM_Logger::debug( 'schema_column_dropped', 'Dropped removed AGUM column.', array( 'column' => $key, 'last_error' => $wpdb->last_error ) );
			wp_cache_delete( 'agum_user_columns', 'agum' );
		}

		wp_cache_delete( 'agum_user_columns', 'agum' );
	}

	public static function user_columns() {
		global $wpdb;
		$cached = wp_cache_get( 'agum_user_columns', 'agum' );
		if ( false !== $cached ) { return $cached; }
		$table = self::users_table();
		$columns = $wpdb->get_col( "DESC {$table}", 0 );
		$columns = is_array( $columns ) ? array_map( 'sanitize_key', $columns ) : array();
		wp_cache_set( 'agum_user_columns', $columns, 'agum' );
		return $columns;
	}

	private static function protected_columns() {
		return array_values( array_unique( array_merge( agum_core_column_keys(), array( 'password_hash' ) ) ) );
	}

	private static function sql_type_for_column( $type ) {
		switch ( $type ) {
			case 'number': return 'decimal(20,4)';
			case 'date': return 'date';
			case 'textarea': return 'text';
			case 'checkbox': return 'tinyint(1) DEFAULT 0';
			case 'image':
			case 'file':
				return 'text';
			default: return 'varchar(255)';
		}
	}
}

