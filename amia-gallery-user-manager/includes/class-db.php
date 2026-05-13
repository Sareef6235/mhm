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
			UNIQUE KEY username (username),
			UNIQUE KEY wp_user_id (wp_user_id),
			UNIQUE KEY student_id (student_id),
			UNIQUE KEY admission_no (admission_no),
			UNIQUE KEY email (email),
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

		self::sync_dynamic_columns();
	}

	public static function sync_dynamic_columns() {
		global $wpdb;
		$table = self::users_table();
		$existing = $wpdb->get_col( "DESC {$table}", 0 );
		foreach ( agum_get_columns() as $column ) {
			$key = sanitize_key( $column['key'] );
			if ( ! $key || in_array( $key, $existing, true ) || in_array( $key, array( 'password' ), true ) ) {
				continue;
			}
			$type = self::sql_type_for_column( $column['type'] );
			$wpdb->query( "ALTER TABLE {$table} ADD COLUMN {$key} {$type} NULL" );
		}
	}

	private static function sql_type_for_column( $type ) {
		switch ( $type ) {
			case 'number': return 'decimal(20,4)';
			case 'date': return 'date';
			case 'textarea': return 'text';
			case 'toggle': return 'tinyint(1) DEFAULT 0';
			default: return 'text';
		}
	}
}

