<?php
/**
 * CSV import and export.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_CSV {
	public static function expected_headers() {
		return array( 'student_id', 'admission_no', 'name', 'class', 'dob', 'role', 'username', 'email', 'password', 'phone_number', 'image_path', 'profile_photo', 'telegram_username', 'telegram_id' );
	}

	public static function import( $file ) {
		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'invalid_csv', __( 'Please upload a valid CSV file.', 'amia-gallery-user-manager' ) );
		}
		$type = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'csv' => 'text/csv' ) );
		if ( 'csv' !== $type['ext'] ) {
			return new WP_Error( 'invalid_csv_type', __( 'Only CSV files are allowed.', 'amia-gallery-user-manager' ) );
		}

		$handle = fopen( $file['tmp_name'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $handle ) {
			return new WP_Error( 'csv_open_failed', __( 'Could not read CSV file.', 'amia-gallery-user-manager' ) );
		}

		$headers = array_map( 'sanitize_key', (array) fgetcsv( $handle ) );
		$report = array( 'created' => 0, 'skipped' => 0, 'errors' => array() );
		$line = 1;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			++$line;
			$data = array();
			foreach ( $headers as $index => $header ) {
				$data[ $header ] = isset( $row[ $index ] ) ? $row[ $index ] : '';
			}
			$data = wp_parse_args( $data, array_fill_keys( self::expected_headers(), '' ) );
			$missing = array();
			foreach ( AGUM_Security::required_user_fields() as $required_field ) {
				if ( empty( $data[ $required_field ] ) ) {
					$missing[] = $required_field;
				}
			}
			if ( $missing ) {
				++$report['skipped'];
				$report['errors'][] = sprintf( 'Line %d skipped: missing required fields: %s.', $line, implode( ', ', $missing ) );
				continue;
			}

			$image_valid = AGUM_Upload::validate_image_reference( $data['image_path'] );
			$photo_valid = AGUM_Upload::validate_image_reference( $data['profile_photo'] );
			if ( is_wp_error( $image_valid ) || is_wp_error( $photo_valid ) ) {
				++$report['skipped'];
				$report['errors'][] = sprintf( 'Line %d skipped: invalid image_path/profile_photo.', $line );
				continue;
			}

			$result = AGUM_Users::create( $data );
			if ( is_wp_error( $result ) ) {
				++$report['skipped'];
				$report['errors'][] = sprintf( 'Line %d skipped: %s', $line, $result->get_error_message() );
				continue;
			}
			++$report['created'];
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		AGUM_Logger::log( 'csv_import', sprintf( 'CSV import completed: %d created, %d skipped.', $report['created'], $report['skipped'] ), null, $report );
		return $report;
	}

	public static function export() {
		AGUM_Security::require_capability();
		AGUM_Security::verify_nonce();
		global $wpdb;
		$table = AGUM_DB::users_table();
		$rows = $wpdb->get_results( "SELECT wp_user_id, student_id, admission_no, name, class, dob, role, username, email, phone_number, image_path, profile_photo, telegram_username, telegram_id, created_at FROM {$table} ORDER BY created_at DESC", ARRAY_A );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=amia-gallery-users-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fputcsv( $out, array( 'wp_user_id', 'student_id', 'admission_no', 'name', 'class', 'dob', 'role', 'username', 'email', 'phone_number', 'image_path', 'profile_photo', 'telegram_username', 'telegram_id', 'created_at' ) );
		foreach ( $rows as $row ) {
			fputcsv( $out, $row );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		AGUM_Logger::log( 'csv_export', 'Exported users CSV.' );
		exit;
	}
}
