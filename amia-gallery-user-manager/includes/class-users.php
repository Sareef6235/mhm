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
	 * Guard native WordPress hooks while AGUM is intentionally creating/updating users.
	 *
	 * @var int
	 */
	private static $wp_sync_suspend_count = 0;

	/**
	 * Temporarily suspend user_register/profile_update sync hooks.
	 *
	 * @return void
	 */
	public static function suspend_wp_sync() {
		++self::$wp_sync_suspend_count;
	}

	/**
	 * Resume user_register/profile_update sync hooks.
	 *
	 * @return void
	 */
	public static function resume_wp_sync() {
		self::$wp_sync_suspend_count = max( 0, self::$wp_sync_suspend_count - 1 );
	}

	/**
	 * Whether native WordPress sync hooks should currently be skipped.
	 *
	 * @return bool
	 */
	public static function is_wp_sync_suspended() {
		return self::$wp_sync_suspend_count > 0;
	}

	/**
	 * Create a native WordPress user and linked AGUM profile.
	 *
	 * @param array $payload User payload.
	 * @return int|WP_Error AGUM profile ID or error.
	 */
	public static function create( $payload ) {
		$clean = AGUM_Security::clean_user_payload( $payload, array( 'require_password' => true, 'require_image' => true ) );
		if ( is_wp_error( $clean ) ) {
			AGUM_Logger::debug( 'validation_failed', 'Create payload validation failed.', array( 'code' => $clean->get_error_code(), 'message' => $clean->get_error_message() ) );
			return $clean;
		}

		if ( '' === $clean['username'] ) {
			$clean['username'] = self::fallback_username_from_payload( $clean );
		}
		$username = self::normalize_username( $clean['username'] );
		if ( is_wp_error( $username ) ) {
			AGUM_Logger::debug( 'invalid_username', 'Username normalization failed during create.', array( 'raw_username' => isset( $payload['username'] ) ? wp_unslash( $payload['username'] ) : '' ) );
			return $username;
		}

		if ( '' === $clean['email'] ) {
			$clean['email'] = agum_unique_placeholder_email( $username );
		}
		$email = self::normalize_email( $clean['email'] );
		if ( is_wp_error( $email ) ) {
			AGUM_Logger::debug( 'invalid_email', 'Email normalization failed during create.', array( 'username' => $username, 'message' => $email->get_error_message() ) );
			return $email;
		}

		$duplicate_email = self::email_exists( $email );
		self::debug_duplicate_check( 'email', $email, $duplicate_email );
		if ( $duplicate_email ) {
			return new WP_Error( 'duplicate_email', __( 'A WordPress user with this email already exists.', 'amia-gallery-user-manager' ) );
		}

		$username = self::generate_available_username( $username );
		$clean['username'] = $username;
		$clean['email'] = $email;

		$unique = self::validate_unique_profile_fields( $clean );
		if ( is_wp_error( $unique ) ) {
			AGUM_Logger::debug( 'profile_duplicate', 'AGUM profile uniqueness validation failed before insert.', array( 'code' => $unique->get_error_code(), 'message' => $unique->get_error_message(), 'username' => $username, 'email' => $email ) );
			return $unique;
		}

		$password = $clean['password'] ? $clean['password'] : wp_generate_password( 16, true, true );
		$user_data = array(
			'user_login'   => $username,
			'user_pass'    => $password,
			'user_email'   => $email,
			'display_name' => $clean['name'],
			'first_name'   => $clean['name'],
			'role'         => agum_map_role_to_wp_role( $clean['role'] ),
		);

		self::suspend_wp_sync();
		$wp_user_id = wp_insert_user( $user_data );
		self::resume_wp_sync();

		if ( is_wp_error( $wp_user_id ) ) {
			AGUM_Logger::debug( 'wp_insert_user_error', 'wp_insert_user() failed during AGUM create.', array( 'username' => $username, 'email' => $email, 'code' => $wp_user_id->get_error_code(), 'message' => $wp_user_id->get_error_message(), 'data' => $wp_user_id->get_error_data() ) );
			return new WP_Error( $wp_user_id->get_error_code(), sprintf( __( 'WordPress user creation failed: %s', 'amia-gallery-user-manager' ), $wp_user_id->get_error_message() ), $wp_user_id->get_error_data() );
		}

		$result = self::insert_agum_profile( $clean, $wp_user_id, $email );
		if ( is_wp_error( $result ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			self::suspend_wp_sync();
			wp_delete_user( $wp_user_id );
			self::resume_wp_sync();
			return $result;
		}

		self::sync_user_meta( $wp_user_id, $clean, $result );
		self::save_dynamic_columns( $result, $payload );
		AGUM_Upload::auto_assign_existing_image( $result, $clean['name'] );
		AGUM_Logger::log( 'user_created', sprintf( 'Created user %s.', $username ), $result, array( 'wp_user_id' => $wp_user_id, 'username' => $username, 'email' => $email, 'role' => $clean['role'], 'profile_image' => ! empty( $clean['profile_photo'] ) ? $clean['profile_photo'] : $clean['image_path'], 'source_system' => isset( $payload['source_system'] ) ? sanitize_text_field( wp_unslash( $payload['source_system'] ) ) : 'Plugin Form' ) );
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

		if ( '' === $clean['username'] && ! empty( $existing->username ) ) {
			$clean['username'] = $existing->username;
		}
		if ( '' === $clean['email'] && ! empty( $existing->email ) ) {
			$clean['email'] = $existing->email;
		}
		$username = self::normalize_username( $clean['username'] );
		if ( is_wp_error( $username ) ) {
			return $username;
		}
		$clean['username'] = $username;

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

		self::suspend_wp_sync();
		$wp_result = wp_update_user( $wp_data );
		self::resume_wp_sync();
		if ( is_wp_error( $wp_result ) ) {
			AGUM_Logger::debug( 'wp_update_user_error', 'wp_update_user() failed during AGUM update.', array( 'agum_id' => $user_id, 'wp_user_id' => $wp_user_id, 'code' => $wp_result->get_error_code(), 'message' => $wp_result->get_error_message() ) );
			return $wp_result;
		}

		$data = self::agum_profile_data( $clean, $wp_user_id, $email );
		$filtered = self::filter_data_formats_by_columns( $data, self::agum_profile_formats() );
		$result = $wpdb->update( AGUM_DB::users_table(), $filtered['data'], array( 'id' => $user_id ), $filtered['formats'], array( '%d' ) );
		if ( false === $result ) {
			return new WP_Error( 'db_update_failed', __( 'Could not update AGUM profile.', 'amia-gallery-user-manager' ) );
		}

		self::sync_user_meta( $wp_user_id, $clean, $user_id );
		self::save_dynamic_columns( $user_id, $payload );
		AGUM_Upload::auto_assign_existing_image( $user_id, $clean['name'] );
		$action = ( isset( $existing->role ) && $existing->role !== $clean['role'] ) ? 'role_changed' : 'user_updated';
		AGUM_Logger::log( $action, sprintf( 'Updated user %s.', $username ), $user_id, array( 'wp_user_id' => $wp_user_id, 'old_role' => isset( $existing->role ) ? $existing->role : '', 'new_role' => $clean['role'] ) );
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
		$filtered = self::filter_data_formats_by_columns( $data, array_merge( self::agum_profile_formats(), array( '%s' ) ) );

		$result = $wpdb->insert( AGUM_DB::users_table(), $filtered['data'], $filtered['formats'] );
		if ( false === $result ) {
			AGUM_Logger::debug( 'db_insert_failed', 'Could not create AGUM profile row.', array( 'last_error' => $wpdb->last_error, 'last_query' => $wpdb->last_query, 'wp_user_id' => $wp_user_id, 'username' => $clean['username'], 'email' => $email ) );
			return new WP_Error( 'db_insert_failed', sprintf( __( 'Could not create AGUM profile. Database error: %s', 'amia-gallery-user-manager' ), $wpdb->last_error ? $wpdb->last_error : __( 'unknown database failure', 'amia-gallery-user-manager' ) ) );
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
			'username'           => self::normalize_username_for_storage( $clean['username'] ),
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
	 * Remove write data for columns that no longer exist after dynamic field deletion.
	 *
	 * @param array $data Data keyed by column.
	 * @param array $formats Formats aligned with data order.
	 * @return array
	 */
	private static function filter_data_formats_by_columns( $data, $formats ) {
		$existing = AGUM_DB::user_columns();
		$filtered_data = array();
		$filtered_formats = array();
		$index = 0;
		foreach ( $data as $key => $value ) {
			if ( in_array( sanitize_key( $key ), $existing, true ) ) {
				$filtered_data[ $key ] = $value;
				$filtered_formats[] = isset( $formats[ $index ] ) ? $formats[ $index ] : '%s';
			}
			++$index;
		}
		return array( 'data' => $filtered_data, 'formats' => $filtered_formats );
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
			self::dynamic_search_sql( $where, $params, $args['search'] );
		}
		if ( $args['role'] && in_array( 'role', AGUM_DB::user_columns(), true ) ) {
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
		$columns = AGUM_DB::user_columns();
		return array(
			'total'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ),
			'students' => in_array( 'role', $columns, true ) ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE role = %s", 'student' ) ) : 0,
			'ustads'   => in_array( 'role', $columns, true ) ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE role = %s", 'ustad' ) ) : 0,
			'images'   => in_array( 'image_path', $columns, true ) ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE image_path IS NOT NULL AND image_path != ''" ) : 0,
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
		);

		foreach ( $checks as $field => $message ) {
			$value = isset( $clean[ $field ] ) ? $clean[ $field ] : '';
			if ( self::profile_value_exists( $field, $value, $exclude_id ) ) {
				AGUM_Logger::debug( 'duplicate_check_match', 'AGUM duplicate check found an existing profile value.', array( 'field' => $field, 'value' => $value, 'exclude_id' => absint( $exclude_id ) ) );
				return new WP_Error( 'duplicate_' . $field, $message );
			}
		}

		return true;
	}

	/**
	 * Check whether an AGUM profile field value exists with normalized comparisons.
	 *
	 * @param string $field Field name.
	 * @param string $value Field value.
	 * @param int    $exclude_id Existing AGUM ID to exclude.
	 * @return bool
	 */
	private static function profile_value_exists( $field, $value, $exclude_id = 0 ) {
		global $wpdb;
		$allowed = array( 'student_id', 'admission_no', 'username', 'email' );
		if ( ! in_array( $field, $allowed, true ) || ! in_array( $field, AGUM_DB::user_columns(), true ) ) {
			return false;
		}

		$value = 'email' === $field ? self::normalize_email_for_storage( $value ) : self::normalize_plain_value( $value );
		if ( 'username' === $field ) {
			$value = self::normalize_username_for_storage( $value );
		}
		if ( '' === $value ) {
			return false;
		}

		$table = AGUM_DB::users_table();
		$where = "LOWER(TRIM({$field})) = LOWER(TRIM(%s))";
		$params = array( $value );
		if ( $exclude_id ) {
			$where .= ' AND id != %d';
			$params[] = absint( $exclude_id );
		}
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where}", $params ) );
		self::debug_duplicate_check( 'agum_' . $field, $value, $count > 0, array( 'exclude_id' => absint( $exclude_id ) ) );

		return $count > 0;
	}


	/**
	 * Build a safe fallback username when settings make username optional.
	 *
	 * @param array $clean Clean payload.
	 * @return string
	 */
	private static function fallback_username_from_payload( $clean ) {
		$candidates = array( 'name', 'student_id', 'admission_no', 'email' );
		foreach ( $candidates as $field ) {
			if ( ! empty( $clean[ $field ] ) ) {
				$parts = explode( '@', (string) $clean[ $field ] );
				$username = self::normalize_username_for_storage( $parts[0] );
				if ( $username ) {
					return $username;
				}
			}
		}
		return 'agum_user_' . strtolower( wp_generate_password( 8, false, false ) );
	}

	/**
	 * Normalize free-form identifiers and strip hidden whitespace.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	private static function normalize_plain_value( $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		$value = preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value );
		$value = preg_replace( '/\s+/u', ' ', $value );
		return trim( $value );
	}

	/**
	 * Normalize username for WordPress and AGUM storage.
	 *
	 * @param mixed $username Raw username.
	 * @return string|WP_Error
	 */
	private static function normalize_username( $username ) {
		$username = self::normalize_username_for_storage( $username );
		if ( '' === $username ) {
			return new WP_Error( 'invalid_username', __( 'A valid username is required.', 'amia-gallery-user-manager' ) );
		}
		if ( ! validate_username( $username ) ) {
			return new WP_Error( 'invalid_username', __( 'Username contains unsupported characters after normalization.', 'amia-gallery-user-manager' ) );
		}
		return $username;
	}

	/**
	 * Normalize username as a plain string without returning WP_Error.
	 *
	 * @param mixed $username Raw username.
	 * @return string
	 */
	private static function normalize_username_for_storage( $username ) {
		$username = self::normalize_plain_value( $username );
		$username = str_replace( ' ', '_', $username );
		$username = sanitize_user( $username, true );
		$username = strtolower( $username );
		$username = preg_replace( '/_+/', '_', $username );
		return trim( $username, '._-' );
	}

	/**
	 * Normalize and validate email for duplicate checks.
	 *
	 * @param mixed $email Raw email.
	 * @return string|WP_Error
	 */
	private static function normalize_email( $email ) {
		$email = self::normalize_email_for_storage( $email );
		if ( '' === $email ) {
			return new WP_Error( 'missing_email', __( 'Email is required.', 'amia-gallery-user-manager' ) );
		}
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'amia-gallery-user-manager' ) );
		}
		return $email;
	}

	/**
	 * Normalize email as a plain string.
	 *
	 * @param mixed $email Raw email.
	 * @return string
	 */
	private static function normalize_email_for_storage( $email ) {
		return strtolower( sanitize_email( self::normalize_plain_value( $email ) ) );
	}

	/**
	 * WordPress email existence check with cache cleanup for stale reads.
	 *
	 * @param string $email Email.
	 * @return int|false
	 */
	private static function email_exists( $email ) {
		$owner = email_exists( $email );
		if ( $owner ) {
			clean_user_cache( $owner );
			$owner = email_exists( $email );
		}
		return $owner;
	}

	/**
	 * WordPress username existence check with cache cleanup for stale reads.
	 *
	 * @param string $username Username.
	 * @return int|false
	 */
	private static function username_exists( $username ) {
		$owner = username_exists( $username );
		if ( $owner ) {
			clean_user_cache( $owner );
			$owner = username_exists( $username );
		}
		return $owner;
	}

	/**
	 * Generate an available username by appending _1, _2, ... only on real conflicts.
	 *
	 * @param string $base Base username.
	 * @return string
	 */
	private static function generate_available_username( $base ) {
		$base = self::normalize_username_for_storage( $base );
		$candidate = $base;
		$suffix = 1;
		while ( self::username_conflicts( $candidate ) ) {
			$candidate = $base . '_' . $suffix;
			++$suffix;
		}
		if ( $candidate !== $base ) {
			AGUM_Logger::debug( 'username_auto_generated', 'Username conflict was real; generated an available username.', array( 'requested' => $base, 'generated' => $candidate ) );
		}
		return $candidate;
	}

	/**
	 * Check both WordPress and AGUM profile stores for username conflicts.
	 *
	 * @param string $username Username.
	 * @param int    $exclude_id Existing AGUM ID to exclude.
	 * @param int    $exclude_wp_user_id Existing WordPress user ID to exclude.
	 * @return bool
	 */
	private static function username_conflicts( $username, $exclude_id = 0, $exclude_wp_user_id = 0 ) {
		$wp_owner = self::username_exists( $username );
		$wp_conflict = $wp_owner && absint( $wp_owner ) !== absint( $exclude_wp_user_id );
		$agum_conflict = self::profile_value_exists( 'username', $username, $exclude_id );
		self::debug_duplicate_check( 'username', $username, $wp_conflict || $agum_conflict, array( 'wp_owner' => $wp_owner, 'agum_conflict' => $agum_conflict ) );
		return $wp_conflict || $agum_conflict;
	}

	/**
	 * Write detailed duplicate validation traces for administrators.
	 *
	 * @param string $field Field checked.
	 * @param string $value Checked value.
	 * @param bool   $exists Whether a duplicate exists.
	 * @param array  $extra Extra context.
	 * @return void
	 */
	private static function debug_duplicate_check( $field, $value, $exists, $extra = array() ) {
		AGUM_Logger::debug( 'duplicate_check', sprintf( 'Duplicate check for %s: %s.', $field, $exists ? 'match' : 'clear' ), array_merge( array( 'field' => $field, 'value' => $value, 'exists' => (bool) $exists ), $extra ) );
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
		$username = self::normalize_username( $profile->username );
		if ( is_wp_error( $username ) ) {
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
				self::suspend_wp_sync();
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
				self::resume_wp_sync();
				if ( is_wp_error( $wp_user_id ) ) {
					AGUM_Logger::debug( 'wp_insert_user_error', 'wp_insert_user() failed during AGUM migration.', array( 'username' => $username, 'code' => $wp_user_id->get_error_code(), 'message' => $wp_user_id->get_error_message() ) );
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
		$migration_data = array( 'wp_user_id' => $wp_user_id, 'email' => $clean['email'], 'image_path' => $clean['image_path'], 'profile_photo' => $clean['profile_photo'], 'password_hash' => '', 'updated_at' => current_time( 'mysql' ) );
		$migration_formats = array( '%d', '%s', '%s', '%s', '%s', '%s' );
		$filtered = self::filter_data_formats_by_columns( $migration_data, $migration_formats );
		$wpdb->update( AGUM_DB::users_table(), $filtered['data'], array( 'id' => absint( $profile->id ) ), $filtered['formats'], array( '%d' ) );

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
		$email = self::normalize_email_for_storage( $email );
		if ( $email ) {
			if ( ! is_email( $email ) ) {
				return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'amia-gallery-user-manager' ) );
			}
			if ( self::email_exists( $email ) ) {
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
		$email = self::normalize_email_for_storage( $email );
		if ( ! $email ) {
			return new WP_Error( 'missing_email', __( 'Email is required.', 'amia-gallery-user-manager' ) );
		}
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'amia-gallery-user-manager' ) );
		}
		$owner_id = self::email_exists( $email );
		if ( $owner_id && absint( $owner_id ) !== absint( $wp_user_id ) ) {
			return new WP_Error( 'duplicate_email', __( 'A different WordPress user already uses this email.', 'amia-gallery-user-manager' ) );
		}

		return $email;
	}

	public static function save_dynamic_columns( $agum_id, $payload ) {
		global $wpdb;
		$data = array(); $formats = array();
		foreach ( agum_get_columns() as $column ) {
			$key = $column['key'];
			if ( in_array( $key, agum_core_column_keys(), true ) || 'password' === $key ) { continue; }
			$value = isset( $payload[ $key ] ) ? wp_unslash( $payload[ $key ] ) : '';
			$value = 'email' === $column['type'] ? sanitize_email( $value ) : sanitize_textarea_field( $value );
			if ( ! in_array( $key, AGUM_DB::user_columns(), true ) ) { continue; }
			$data[ $key ] = $value;
			$formats[] = 'number' === $column['type'] ? '%f' : ( 'toggle' === $column['type'] ? '%d' : '%s' );
		}
		if ( $data ) { $wpdb->update( AGUM_DB::users_table(), $data, array( 'id' => absint( $agum_id ) ), $formats, array( '%d' ) ); }
	}

	public static function dynamic_search_sql( &$where, &$params, $search ) {
		global $wpdb;
		$parts = array(); $like = '%' . $wpdb->esc_like( sanitize_text_field( $search ) ) . '%';
		$existing_columns = AGUM_DB::user_columns();
		foreach ( agum_get_columns() as $column ) {
			$key = sanitize_key( $column['key'] );
			if ( empty( $column['searchable'] ) || in_array( $key, array( 'password' ), true ) || ! in_array( $key, $existing_columns, true ) ) { continue; }
			$parts[] = $key . ' LIKE %s'; $params[] = $like;
		}
		if ( $parts ) { $where[] = '(' . implode( ' OR ', $parts ) . ')'; }
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
		$username = self::normalize_username_for_storage( $user->user_login );
		$email = self::normalize_email_for_storage( $user->user_email );
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE wp_user_id = %d OR LOWER(TRIM(username)) = LOWER(TRIM(%s)) OR LOWER(TRIM(email)) = LOWER(TRIM(%s)) LIMIT 1", $user->ID, $username, $email ) );
		$role = get_user_meta( $user->ID, 'agum_role', true );
		if ( ! $role ) {
			$role = in_array( 'editor', (array) $user->roles, true ) ? 'ustad' : ( in_array( 'administrator', (array) $user->roles, true ) ? 'admin' : 'student' );
		}
		$data = array(
			'wp_user_id' => $user->ID,
			'name' => $user->display_name ? $user->display_name : $user->user_login,
			'username' => $username,
			'email' => $email,
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
			$filtered = self::filter_data_formats_by_columns( $data, $formats );
			$updated = $wpdb->update( $table, $filtered['data'], array( 'id' => absint( $existing->id ) ), $filtered['formats'], array( '%d' ) );
			if ( false === $updated ) {
				AGUM_Logger::debug( 'sync_update_failed', 'Failed updating AGUM profile during WP sync.', array( 'wp_user_id' => $user->ID, 'last_error' => $wpdb->last_error ) );
			}
			return absint( $existing->id );
		}
		$data['created_at'] = current_time( 'mysql' );
		$filtered = self::filter_data_formats_by_columns( $data, array_merge( $formats, array( '%s' ) ) );
		$inserted = $wpdb->insert( $table, $filtered['data'], $filtered['formats'] );
		if ( false === $inserted ) {
			AGUM_Logger::debug( 'sync_insert_failed', 'Failed inserting AGUM profile during WP sync.', array( 'wp_user_id' => $user->ID, 'last_error' => $wpdb->last_error ) );
			return new WP_Error( 'sync_insert_failed', $wpdb->last_error );
		}
		$agum_id = absint( $wpdb->insert_id );
		AGUM_Logger::log( 'user_created', sprintf( 'Created user %s from WordPress.', $username ), $agum_id, array( 'wp_user_id' => $user->ID, 'username' => $username, 'email' => $email, 'role' => $role, 'profile_image' => $data['profile_photo'], 'source_system' => 'WordPress Users' ) );
		return $agum_id;
	}

	public static function record_login( $user_login, $user ) {
		global $wpdb;
		self::sync_wp_user( $user->ID );
		$wpdb->update( AGUM_DB::users_table(), array( 'last_login' => current_time( 'mysql' ), 'last_seen' => current_time( 'mysql' ) ), array( 'wp_user_id' => absint( $user->ID ) ), array( '%s', '%s' ), array( '%d' ) );
		update_user_meta( $user->ID, 'agum_last_login', current_time( 'mysql' ) );
		AGUM_Logger::log( 'user_login', sprintf( 'User %s logged in.', $user_login ), null, array( 'wp_user_id' => $user->ID, 'username' => $user_login, 'source_system' => 'WordPress Login' ) );
	}

	public static function record_logout() {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			AGUM_Logger::log( 'user_logout', sprintf( 'User #%d logged out.', $user_id ), null, array( 'wp_user_id' => $user_id ) );
		}
	}

}
