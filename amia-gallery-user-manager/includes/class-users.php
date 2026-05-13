<?php
/**
 * User CRUD service with native WordPress user sync.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Users {
	/**
	 * Create a native WordPress user and linked AGUM profile.
	 *
	 * @param array $payload User payload.
	 * @return int|WP_Error AGUM profile ID or error.
	 */
	public static function create( $payload ) {
		global $wpdb;
		$clean = AGUM_Security::clean_user_payload( $payload, array( 'require_password' => true, 'require_image' => true ) );
		if ( is_wp_error( $clean ) ) {
			return $clean;
		}

		$username = sanitize_user( $clean['username'], true );
		if ( ! $username ) {
			return new WP_Error( 'invalid_username', __( 'A valid username is required.', 'amia-gallery-user-manager' ) );
		}
		if ( get_user_by( 'login', $username ) ) {
			return new WP_Error( 'duplicate_username', __( 'A WordPress user with this username already exists.', 'amia-gallery-user-manager' ) );
		}

		$unique = self::validate_unique_profile_fields( $clean );
		if ( is_wp_error( $unique ) ) {
			return $unique;
		}

		$email = self::resolve_email_for_create( $clean['email'], $username );
		if ( is_wp_error( $email ) ) {
			return $email;
		}

		$password = $clean['password'];
		$wp_user_id = wp_insert_user(
			array(
				'user_login'   => $username,
				'user_pass'    => $password,
				'user_email'   => $email,
				'display_name' => $clean['name'],
				'first_name'   => $clean['name'],
				'role'         => agum_map_role_to_wp_role( $clean['role'] ),
			)
		);

		if ( is_wp_error( $wp_user_id ) ) {
			return $wp_user_id;
		}

		$result = self::insert_agum_profile( $clean, $wp_user_id, $email );
		if ( is_wp_error( $result ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( $wp_user_id );
			return $result;
		}

		self::sync_user_meta( $wp_user_id, $clean, $result );
		AGUM_Upload::auto_assign_existing_image( $result, $clean['name'] );
		AGUM_Logger::log( 'wp_user_created', sprintf( 'Created WordPress user %s', $username ), $result, array( 'wp_user_id' => $wp_user_id ) );
		return $result;
	}

	/**
	 * Update a native WordPress user and linked AGUM profile.
	 *
	 * @param int   $user_id AGUM profile ID.
	 * @param array $payload User payload.
	 * @return true|WP_Error
	 */
	public static function update( $user_id, $payload ) {
		global $wpdb;
		$user_id = absint( $user_id );
		$existing = self::get( $user_id );
		if ( ! $existing ) {
			return new WP_Error( 'missing_profile', __( 'AGUM profile not found.', 'amia-gallery-user-manager' ) );
		}

		$clean = AGUM_Security::clean_user_payload( $payload, array( 'require_password' => false, 'require_image' => true, 'existing_id' => $user_id ) );
		if ( is_wp_error( $clean ) ) {
			return $clean;
		}

		$username = sanitize_user( $clean['username'], true );
		if ( ! $username ) {
			return new WP_Error( 'invalid_username', __( 'A valid username is required.', 'amia-gallery-user-manager' ) );
		}

		$wp_user_id = absint( $existing->wp_user_id );
		if ( ! $wp_user_id ) {
			$migrated = self::migrate_profile_to_wp_user( $existing );
			if ( is_wp_error( $migrated ) ) {
				return $migrated;
			}
			$wp_user_id = is_array( $migrated ) ? absint( $migrated['wp_user_id'] ) : absint( $migrated );
		}

		$unique = self::validate_unique_profile_fields( $clean, $user_id );
		if ( is_wp_error( $unique ) ) {
			return $unique;
		}

		$login_owner = get_user_by( 'login', $username );
		if ( $login_owner && (int) $login_owner->ID !== $wp_user_id ) {
			return new WP_Error( 'duplicate_username', __( 'A different WordPress user already uses this username.', 'amia-gallery-user-manager' ) );
		}

		$email = self::resolve_email_for_update( $clean['email'], $username, $wp_user_id );
		if ( is_wp_error( $email ) ) {
			return $email;
		}

		$wp_data = array(
			'ID'           => $wp_user_id,
			'user_email'   => $email,
			'display_name' => $clean['name'],
			'first_name'   => $clean['name'],
			'role'         => agum_map_role_to_wp_role( $clean['role'] ),
		);
		if ( $clean['password'] ) {
			$wp_data['user_pass'] = $clean['password'];
		}

		$wp_result = wp_update_user( $wp_data );
		if ( is_wp_error( $wp_result ) ) {
			return $wp_result;
		}

		$data = self::agum_profile_data( $clean, $wp_user_id, $email );
		$result = $wpdb->update( AGUM_DB::users_table(), $data, array( 'id' => $user_id ), self::agum_profile_formats(), array( '%d' ) );
		if ( false === $result ) {
			return new WP_Error( 'db_update_failed', __( 'Could not update AGUM profile.', 'amia-gallery-user-manager' ) );
		}

		self::sync_user_meta( $wp_user_id, $clean, $user_id );
		AGUM_Upload::auto_assign_existing_image( $user_id, $clean['name'] );
		AGUM_Logger::log( 'wp_user_updated', sprintf( 'Updated WordPress user %s', $username ), $user_id, array( 'wp_user_id' => $wp_user_id ) );
		return true;
	}

	/**
	 * Insert AGUM profile row.
	 *
	 * @param array  $clean Clean payload.
	 * @param int    $wp_user_id WordPress user ID.
	 * @param string $email User email.
	 * @return int|WP_Error
	 */
	private static function insert_agum_profile( $clean, $wp_user_id, $email ) {
		global $wpdb;
		$data = self::agum_profile_data( $clean, $wp_user_id, $email );
		$data['created_at'] = current_time( 'mysql' );

		$result = $wpdb->insert( AGUM_DB::users_table(), $data, array_merge( self::agum_profile_formats(), array( '%s' ) ) );
		if ( false === $result ) {
			return new WP_Error( 'db_insert_failed', __( 'Could not create AGUM profile. Username may already exist.', 'amia-gallery-user-manager' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Build profile data.
	 *
	 * @param array  $clean Clean payload.
	 * @param int    $wp_user_id WordPress user ID.
	 * @param string $email Email.
	 * @return array
	 */
	private static function agum_profile_data( $clean, $wp_user_id, $email ) {
		return array(
			'wp_user_id'         => absint( $wp_user_id ),
			'student_id'         => $clean['student_id'],
			'admission_no'       => $clean['admission_no'],
			'name'               => $clean['name'],
			'class'              => $clean['class'],
			'dob'                => $clean['dob'] ? $clean['dob'] : null,
			'role'               => $clean['role'],
			'username'           => sanitize_user( $clean['username'], true ),
			'email'              => sanitize_email( $email ),
			'password_hash'      => '',
			'phone_number'       => $clean['phone_number'],
			'image_path'         => $clean['image_path'],
			'profile_photo'     => $clean['profile_photo'],
			'approval_status'    => isset( $clean['approval_status'] ) ? $clean['approval_status'] : 'approved',
			'notes'              => isset( $clean['notes'] ) ? $clean['notes'] : '',
			'remarks'            => isset( $clean['remarks'] ) ? $clean['remarks'] : '',
			'updated_at'         => current_time( 'mysql' ),
		);
	}

	/**
	 * Profile format list.
	 *
	 * @return array
	 */
	private static function agum_profile_formats() {
		return array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
	}

	/**
	 * Sync extended data into wp_usermeta.
	 *
	 * @param int   $wp_user_id WordPress user ID.
	 * @param array $clean Clean payload.
	 * @param int   $agum_id AGUM profile ID.
	 * @return void
	 */
	public static function sync_user_meta( $wp_user_id, $clean, $agum_id ) {
		$meta = array(
			'agum_profile_id'       => absint( $agum_id ),
			'agum_student_id'       => $clean['student_id'],
			'agum_admission_no'     => $clean['admission_no'],
			'agum_class'            => $clean['class'],
			'agum_dob'              => $clean['dob'],
			'agum_role'             => $clean['role'],
			'agum_phone_number'     => $clean['phone_number'],
			'phone_number'          => $clean['phone_number'],
			'agum_profile_image'    => $clean['image_path'],
			'agum_profile_photo'    => $clean['profile_photo'],
			'agum_approval_status'  => isset( $clean['approval_status'] ) ? $clean['approval_status'] : 'approved',
			'agum_notes'            => isset( $clean['notes'] ) ? $clean['notes'] : '',
			'agum_remarks'          => isset( $clean['remarks'] ) ? $clean['remarks'] : '',
		);
		foreach ( $meta as $key => $value ) {
			update_user_meta( $wp_user_id, $key, $value );
		}
	}

	/**
	 * Get an AGUM profile.
	 *
	 * @param int $user_id AGUM profile ID.
	 * @return object|null
	 */
	public static function get( $user_id ) {
		global $wpdb;
		$table = AGUM_DB::users_table();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $user_id ) ) );
	}

	/**
	 * Delete AGUM profiles and linked WordPress users.
	 *
	 * @param array $ids AGUM IDs.
	 * @return int
	 */
	public static function delete( $ids ) {
		global $wpdb;
		$ids = array_filter( array_map( 'absint', (array) $ids ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $ids as $id ) {
			$profile = self::get( $id );
			if ( $profile && ! empty( $profile->wp_user_id ) ) {
				wp_delete_user( absint( $profile->wp_user_id ) );
			}
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$table = AGUM_DB::users_table();
		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ) );
		AGUM_Logger::log( 'users_deleted', sprintf( 'Deleted %d AGUM profiles and linked WordPress users', (int) $deleted ), null, array( 'ids' => $ids ) );
		return (int) $deleted;
	}

	/**
	 * Query AGUM profiles.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public static function query( $args = array() ) {
		global $wpdb;
		$defaults = array( 'search' => '', 'role' => '', 'paged' => 1, 'per_page' => 20 );
		$args = wp_parse_args( $args, $defaults );
		$table = AGUM_DB::users_table();
		$where = array( '1=1' );
		$params = array();

		if ( $args['search'] ) {
			$like = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[] = '(name LIKE %s OR username LIKE %s OR email LIKE %s OR phone_number LIKE %s OR admission_no LIKE %s OR student_id LIKE %s)';
			$params = array_merge( $params, array( $like, $like, $like, $like, $like, $like ) );
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


	/**
	 * Find a user for bulk image matching by filename, username, or display name.
	 *
	 * @param string $filename Uploaded filename.
	 * @return object|null
	 */
	public static function find_for_image_upload( $filename ) {
		global $wpdb;
		$table = AGUM_DB::users_table();
		$base = pathinfo( sanitize_file_name( $filename ), PATHINFO_FILENAME );
		$candidates = array_unique(
			array_filter(
				array(
					agum_normalize_image_basename( $base ),
					sanitize_user( $base, true ),
					sanitize_text_field( $base ),
				)
			)
		);

		foreach ( $candidates as $candidate ) {
			$like = '%' . $wpdb->esc_like( $candidate ) . '%';
			$user = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE username = %s OR name = %s OR REPLACE(UPPER(name), ' ', '_') = %s OR UPPER(username) = %s OR username LIKE %s OR name LIKE %s ORDER BY id ASC LIMIT 1",
					$candidate,
					$candidate,
					agum_normalize_image_basename( $candidate ),
					strtoupper( $candidate ),
					$like,
					$like
				)
			);
			if ( $user ) {
				return $user;
			}
		}

		$users = $wpdb->get_results( "SELECT * FROM {$table}" );
		$normalized_base = agum_normalize_image_basename( $base );
		foreach ( $users as $user ) {
			if ( $normalized_base === agum_normalize_image_basename( $user->username ) || $normalized_base === agum_normalize_image_basename( $user->name ) ) {
				return $user;
			}
		}

		return null;
	}

	/**
	 * Dashboard stats.
	 *
	 * @return array
	 */
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

	/**
	 * Validate AGUM-only unique fields before writing.
	 *
	 * @param array $clean Clean payload.
	 * @param int   $exclude_id Existing AGUM ID to exclude.
	 * @return true|WP_Error
	 */
	private static function validate_unique_profile_fields( $clean, $exclude_id = 0 ) {
		$checks = array(
			'student_id'   => __( 'Student ID already exists.', 'amia-gallery-user-manager' ),
			'admission_no' => __( 'Admission number already exists.', 'amia-gallery-user-manager' ),
			'username'     => __( 'Username already exists in AGUM profiles.', 'amia-gallery-user-manager' ),
			'email'        => __( 'Email already exists in AGUM profiles.', 'amia-gallery-user-manager' ),
		);

		foreach ( $checks as $field => $message ) {
			if ( self::profile_value_exists( $field, $clean[ $field ], $exclude_id ) ) {
				return new WP_Error( 'duplicate_' . $field, $message );
			}
		}

		return true;
	}

	/**
	 * Check whether an AGUM profile field value exists.
	 *
	 * @param string $field Field name.
	 * @param string $value Field value.
	 * @param int    $exclude_id Existing AGUM ID to exclude.
	 * @return bool
	 */
	private static function profile_value_exists( $field, $value, $exclude_id = 0 ) {
		global $wpdb;
		$allowed = array( 'student_id', 'admission_no', 'username', 'email' );
		if ( ! in_array( $field, $allowed, true ) || '' === (string) $value ) {
			return false;
		}
		$table = AGUM_DB::users_table();
		if ( $exclude_id ) {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$field} = %s AND id != %d", $value, absint( $exclude_id ) ) );
		} else {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$field} = %s", $value ) );
		}

		return (int) $count > 0;
	}

	/**
	 * Migrate all unlinked AGUM profiles into WordPress users.
	 *
	 * @return array
	 */
	public static function migrate_all_to_wp_users() {
		global $wpdb;
		$table = AGUM_DB::users_table();
		$profiles = $wpdb->get_results( "SELECT * FROM {$table} WHERE wp_user_id IS NULL OR wp_user_id = 0" );
		$report = array( 'created' => 0, 'linked' => 0, 'skipped' => 0, 'errors' => array() );

		foreach ( $profiles as $profile ) {
			$result = self::migrate_profile_to_wp_user( $profile );
			if ( is_wp_error( $result ) ) {
				++$report['skipped'];
				$report['errors'][] = sprintf( '%s: %s', $profile->username, $result->get_error_message() );
				continue;
			}
			if ( ! empty( $result['linked'] ) ) {
				++$report['linked'];
			} else {
				++$report['created'];
			}
		}

		AGUM_Logger::log( 'sync_wp_users', 'Synced AGUM profiles to native WordPress users.', null, $report );
		return $report;
	}

	/**
	 * Migrate one profile into WordPress users.
	 *
	 * @param object $profile AGUM profile.
	 * @return int|array|WP_Error WordPress user ID, or array with linked flag for bulk reports.
	 */
	public static function migrate_profile_to_wp_user( $profile ) {
		global $wpdb;
		$username = sanitize_user( $profile->username, true );
		if ( ! $username ) {
			return new WP_Error( 'invalid_username', __( 'Profile has no valid username.', 'amia-gallery-user-manager' ) );
		}

		$wp_user = get_user_by( 'login', $username );
		$linked = false;
		if ( $wp_user ) {
			$wp_user_id = (int) $wp_user->ID;
			$linked = true;
		} else {
			$email = self::resolve_email_for_create( ! empty( $profile->email ) ? $profile->email : agum_unique_placeholder_email( $username ), $username );
			if ( is_wp_error( $email ) ) {
				return $email;
			}
			$wp_user_id = wp_insert_user(
				array(
					'user_login'   => $username,
					'user_pass'    => wp_generate_password( 16, true, true ),
					'user_email'   => $email,
					'display_name' => $profile->name,
					'first_name'   => $profile->name,
					'role'         => agum_map_role_to_wp_role( $profile->role ),
				)
			);
			if ( is_wp_error( $wp_user_id ) ) {
				return $wp_user_id;
			}
		}

		$clean = array(
			'student_id'        => $profile->student_id,
			'admission_no'      => $profile->admission_no,
			'name'              => $profile->name,
			'class'             => $profile->class,
			'dob'               => $profile->dob,
			'role'              => $profile->role,
			'username'          => $username,
			'email'             => get_userdata( $wp_user_id )->user_email,
			'password'          => '',
			'phone_number'      => $profile->phone_number,
			'image_path'        => $profile->image_path ? $profile->image_path : AGUM_Upload::fallback_image_url(),
			'profile_photo'    => ! empty( $profile->profile_photo ) ? $profile->profile_photo : ( $profile->image_path ? $profile->image_path : AGUM_Upload::fallback_image_url() ),
			'approval_status'  => ! empty( $profile->approval_status ) ? $profile->approval_status : 'approved',
			'notes'            => ! empty( $profile->notes ) ? $profile->notes : '',
			'remarks'          => ! empty( $profile->remarks ) ? $profile->remarks : '',
		);
		self::sync_user_meta( $wp_user_id, $clean, $profile->id );
		$wpdb->update(
			AGUM_DB::users_table(),
			array( 'wp_user_id' => $wp_user_id, 'email' => $clean['email'], 'image_path' => $clean['image_path'], 'profile_photo' => $clean['profile_photo'], 'password_hash' => '', 'updated_at' => current_time( 'mysql' ) ),
			array( 'id' => absint( $profile->id ) ),
			array( '%d', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		return $linked ? array( 'wp_user_id' => $wp_user_id, 'linked' => true ) : $wp_user_id;
	}

	/**
	 * Resolve email for a new WordPress user.
	 *
	 * @param string $email Email from input.
	 * @param string $username Username.
	 * @return string|WP_Error
	 */
	private static function resolve_email_for_create( $email, $username ) {
		$email = sanitize_email( $email );
		if ( $email ) {
			if ( ! is_email( $email ) ) {
				return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'amia-gallery-user-manager' ) );
			}
			if ( get_user_by( 'email', $email ) ) {
				return new WP_Error( 'duplicate_email', __( 'A WordPress user with this email already exists.', 'amia-gallery-user-manager' ) );
			}
			return $email;
		}

		return new WP_Error( 'missing_email', __( 'Email is required.', 'amia-gallery-user-manager' ) );
	}

	/**
	 * Resolve email for updating a WordPress user.
	 *
	 * @param string $email Email from input.
	 * @param string $username Username.
	 * @param int    $wp_user_id Current WordPress user ID.
	 * @return string|WP_Error
	 */
	private static function resolve_email_for_update( $email, $username, $wp_user_id ) {
		$email = sanitize_email( $email );
		if ( ! $email ) {
			return new WP_Error( 'missing_email', __( 'Email is required.', 'amia-gallery-user-manager' ) );
		}
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'amia-gallery-user-manager' ) );
		}
		$owner = get_user_by( 'email', $email );
		if ( $owner && (int) $owner->ID !== absint( $wp_user_id ) ) {
			return new WP_Error( 'duplicate_email', __( 'A different WordPress user already uses this email.', 'amia-gallery-user-manager' ) );
		}

		return $email;
	}

	/**
	 * Sync a WordPress user into AGUM profile storage.
	 *
	 * @param int $wp_user_id WordPress user ID.
	 * @return int|WP_Error
	 */
	public static function sync_wp_user( $wp_user_id ) {
		global $wpdb;
		$user = get_userdata( absint( $wp_user_id ) );
		if ( ! $user ) {
			return new WP_Error( 'missing_wp_user', __( 'WordPress user not found.', 'amia-gallery-user-manager' ) );
		}
		$table = AGUM_DB::users_table();
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE wp_user_id = %d OR username = %s OR email = %s LIMIT 1", $user->ID, $user->user_login, $user->user_email ) );
		$role = get_user_meta( $user->ID, 'agum_role', true );
		if ( ! $role ) {
			$role = in_array( 'editor', (array) $user->roles, true ) ? 'ustad' : ( in_array( 'administrator', (array) $user->roles, true ) ? 'admin' : 'student' );
		}
		$data = array(
			'wp_user_id' => $user->ID,
			'name' => $user->display_name ? $user->display_name : $user->user_login,
			'username' => $user->user_login,
			'email' => $user->user_email,
			'role' => $role,
			'student_id' => get_user_meta( $user->ID, 'agum_student_id', true ) ?: 'WP-' . $user->ID,
			'admission_no' => get_user_meta( $user->ID, 'agum_admission_no', true ) ?: 'WP-' . $user->ID,
			'class' => get_user_meta( $user->ID, 'agum_class', true ) ?: 'General',
			'dob' => get_user_meta( $user->ID, 'agum_dob', true ) ?: null,
			'phone_number' => get_user_meta( $user->ID, 'phone_number', true ) ?: '',
			'image_path' => get_user_meta( $user->ID, 'agum_profile_image', true ) ?: '',
			'profile_photo' => get_user_meta( $user->ID, 'agum_profile_photo', true ) ?: get_user_meta( $user->ID, 'agum_profile_image', true ),
			'approval_status' => get_user_meta( $user->ID, 'agum_approval_status', true ) ?: 'approved',
			'updated_at' => current_time( 'mysql' ),
		);
		$formats = array( '%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s' );
		if ( $existing ) {
			$wpdb->update( $table, $data, array( 'id' => absint( $existing->id ) ), $formats, array( '%d' ) );
			return absint( $existing->id );
		}
		$data['created_at'] = current_time( 'mysql' );
		$wpdb->insert( $table, $data, array_merge( $formats, array( '%s' ) ) );
		return absint( $wpdb->insert_id );
	}

	public static function record_login( $user_login, $user ) {
		global $wpdb;
		self::sync_wp_user( $user->ID );
		$wpdb->update( AGUM_DB::users_table(), array( 'last_login' => current_time( 'mysql' ), 'last_seen' => current_time( 'mysql' ) ), array( 'wp_user_id' => absint( $user->ID ) ), array( '%s', '%s' ), array( '%d' ) );
		update_user_meta( $user->ID, 'agum_last_login', current_time( 'mysql' ) );
		AGUM_Logger::log( 'user_login', sprintf( 'User %s logged in.', $user_login ), null, array( 'wp_user_id' => $user->ID ) );
	}

	public static function record_logout() {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			AGUM_Logger::log( 'user_logout', sprintf( 'User #%d logged out.', $user_id ), null, array( 'wp_user_id' => $user_id ) );
		}
	}

}
