<?php
/**
 * AJAX endpoints.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Ajax {
	public static function init() {
		add_action( 'wp_ajax_agum_search_users', array( __CLASS__, 'search_users' ) );
		add_action( 'wp_ajax_agum_save_user', array( __CLASS__, 'save_user' ) );
		add_action( 'wp_ajax_agum_delete_users', array( __CLASS__, 'delete_users' ) );
		add_action( 'wp_ajax_agum_generate_otp', array( __CLASS__, 'generate_otp' ) );
		add_action( 'wp_ajax_agum_upload_image', array( __CLASS__, 'upload_image' ) );
		add_action( 'wp_ajax_agum_validate_image', array( __CLASS__, 'validate_image' ) );
		add_action( 'wp_ajax_agum_clear_activity', array( __CLASS__, 'clear_activity' ) );
		add_action( 'wp_ajax_agum_save_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'wp_ajax_agum_reset_settings', array( __CLASS__, 'reset_settings' ) );
		add_action( 'wp_ajax_agum_export_settings', array( __CLASS__, 'export_settings' ) );
		add_action( 'wp_ajax_agum_get_notifications', array( __CLASS__, 'get_notifications' ) );
		add_action( 'wp_ajax_agum_mark_notifications_read', array( __CLASS__, 'mark_notifications_read' ) );
		add_action( 'wp_ajax_agum_delete_notification', array( __CLASS__, 'delete_notification' ) );
		add_action( 'wp_ajax_agum_clear_notifications', array( __CLASS__, 'clear_notifications' ) );
		add_action( 'wp_ajax_agum_delete_column', array( __CLASS__, 'delete_column' ) );
		add_action( 'wp_ajax_agum_create_field', array( __CLASS__, 'create_field' ) );
	}

	public static function search_users() {
		AGUM_Security::ajax_guard();
		$result = AGUM_Users::query( array(
			'search' => isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '',
			'role' => isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : '',
			'paged' => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1,
			'per_page' => isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 20,
		) );
		ob_start();
		agum_template( 'tables', array( 'users' => $result['items'] ) );
		$page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page = isset( $_GET['per_page'] ) ? max( 1, absint( $_GET['per_page'] ) ) : 20;
		$total_pages = max( 1, (int) ceil( $result['total'] / $per_page ) );
		wp_send_json_success( array( 'html' => ob_get_clean(), 'total' => $result['total'], 'page' => $page, 'total_pages' => $total_pages ) );
	}

	public static function save_user() {
		AGUM_Security::ajax_guard();
		$user_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		AGUM_Logger::debug( 'ajax_save_user', 'AJAX user save requested.', array( 'agum_id' => $user_id, 'mode' => $user_id ? 'update' : 'create' ) );
		$result = $user_id ? AGUM_Users::update( $user_id, $_POST ) : AGUM_Users::create( $_POST );
		if ( is_wp_error( $result ) ) {
			AGUM_Logger::debug( 'ajax_save_user_error', 'AJAX user save failed.', array( 'agum_id' => $user_id, 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ) );
			wp_send_json_error( array( 'message' => $result->get_error_message(), 'code' => $result->get_error_code() ) );
		}
		wp_send_json_success( array( 'message' => $user_id ? __( 'User updated successfully.', 'amia-gallery-user-manager' ) : __( 'User created successfully.', 'amia-gallery-user-manager' ), 'id' => $result ) );
	}

	public static function delete_users() {
		AGUM_Security::ajax_guard();
		$confirm = isset( $_POST['confirm_text'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_text'] ) ) : '';
		if ( 'DELETE' !== $confirm ) {
			wp_send_json_error( array( 'message' => __( 'Type DELETE to confirm deletion.', 'amia-gallery-user-manager' ) ), 400 );
		}
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		$deleted = AGUM_Users::delete( $ids );
		wp_send_json_success( array( 'deleted' => $deleted, 'message' => sprintf( __( 'Deleted %d users.', 'amia-gallery-user-manager' ), $deleted ) ) );
	}

	public static function generate_otp() {
		AGUM_Security::ajax_guard();
		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		wp_send_json_success( array( 'otp' => AGUM_OTP::generate( $user_id ) ) );
	}



	public static function clear_activity() {
		AGUM_Security::ajax_guard();
		self::require_delete_confirmation();
		AGUM_Logger::clear();
		wp_send_json_success( array( 'message' => __( 'Recent activity cleared.', 'amia-gallery-user-manager' ) ) );
	}


	public static function save_settings() {
		AGUM_Security::ajax_guard();
		$settings = agum_save_settings_from_request( $_POST );
		AGUM_Logger::notify( 'settings_saved', __( 'Settings and columns updated.', 'amia-gallery-user-manager' ), $settings );
		wp_send_json_success(
			array(
				'message' => __( 'Settings saved and all dynamic surfaces refreshed.', 'amia-gallery-user-manager' ),
				'settings' => $settings,
				'columns'  => agum_get_columns(),
				'required' => AGUM_Security::required_user_fields(),
			)
		);
	}

	public static function reset_settings() {
		AGUM_Security::ajax_guard();
		$previous_columns = agum_get_columns();
		update_option( 'agum_settings', agum_default_settings() );
		AGUM_DB::sync_dynamic_columns( $previous_columns );
		wp_cache_delete( 'agum_settings', 'agum' );
		wp_cache_delete( 'agum_required_fields', 'agum' );
		wp_send_json_success( array( 'message' => __( 'Settings reset.', 'amia-gallery-user-manager' ), 'settings' => agum_default_settings() ) );
	}

	public static function export_settings() {
		AGUM_Security::ajax_guard();
		wp_send_json_success( array( 'settings' => agum_get_settings() ) );
	}


	public static function get_notifications() {
		AGUM_Security::ajax_guard();
		$args = array(
			'search'   => isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '',
			'type'     => isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '',
			'category' => isset( $_GET['category'] ) ? sanitize_key( wp_unslash( $_GET['category'] ) ) : '',
			'date'     => isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : '',
		);
		$items = AGUM_Logger::notifications( isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 20, false, $args );
		$unread = count( AGUM_Logger::notifications( 100, true ) );
		wp_send_json_success( array( 'items' => $items, 'unread' => $unread, 'serverTime' => current_time( 'mysql' ) ) );
	}

	public static function mark_notifications_read() {
		AGUM_Security::ajax_guard();
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		AGUM_Logger::mark_notifications_read( $ids );
		wp_send_json_success( array( 'message' => __( 'Notifications marked as read.', 'amia-gallery-user-manager' ) ) );
	}

	public static function delete_notification() {
		AGUM_Security::ajax_guard();
		self::require_delete_confirmation();
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$deleted = $id ? AGUM_Logger::delete_notifications( array( $id ) ) : 0;
		wp_send_json_success( array( 'deleted' => $deleted, 'message' => __( 'Notification deleted.', 'amia-gallery-user-manager' ) ) );
	}

	public static function clear_notifications() {
		AGUM_Security::ajax_guard();
		self::require_delete_confirmation();
		AGUM_Logger::clear_notifications();
		wp_send_json_success( array( 'message' => __( 'All notifications cleared.', 'amia-gallery-user-manager' ) ) );
	}


	public static function create_field() {
		AGUM_Security::ajax_guard();
		$raw_key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$key = sanitize_key( $raw_key );
		$key = preg_replace( '/[^a-z0-9_]/', '_', $key );
		$key = preg_replace( '/_+/', '_', $key );
		if ( $key && ! preg_match( '/^[a-z_]/', $key ) ) {
			$key = 'field_' . $key;
		}
		if ( ! $key || ! preg_match( '/^[a-z_][a-z0-9_]*$/', $key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid field name. Use letters, numbers, and underscores; start with a letter.', 'amia-gallery-user-manager' ) ), 400 );
		}
		if ( 'password' === $key || in_array( $key, array( 'id', 'wp_user_id', 'created_at', 'updated_at', 'password_hash' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'This field name is reserved.', 'amia-gallery-user-manager' ) ), 400 );
		}

		$settings = agum_get_settings();
		$previous_columns = isset( $settings['columns'] ) ? $settings['columns'] : array();
		foreach ( agum_sanitize_columns( $previous_columns ) as $existing ) {
			if ( $key === $existing['key'] ) {
				wp_send_json_error( array( 'message' => __( 'Field already exists.', 'amia-gallery-user-manager' ) ), 409 );
			}
		}

		$type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'text';
		if ( ! in_array( $type, agum_supported_field_types(), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Unsupported field type.', 'amia-gallery-user-manager' ) ), 400 );
		}

		$column = array(
			'key'           => $key,
			'label'         => isset( $_POST['label'] ) && '' !== trim( wp_unslash( $_POST['label'] ) ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : ucwords( str_replace( '_', ' ', $key ) ),
			'type'          => $type,
			'enabled'       => 1,
			'required'      => ! empty( $_POST['required'] ) ? 1 : 0,
			'form'          => 1,
			'edit'          => 1,
			'csv'           => ! empty( $_POST['csv'] ) ? 1 : 0,
			'bulk'          => ! empty( $_POST['bulk_upload'] ) ? 1 : 0,
			'bulk_upload'   => ! empty( $_POST['bulk_upload'] ) ? 1 : 0,
			'image_field'   => in_array( $type, array( 'image', 'file' ), true ) ? 1 : 0,
			'searchable'    => ! empty( $_POST['searchable'] ) ? 1 : 0,
			'filterable'    => ! empty( $_POST['filterable'] ) ? 1 : 0,
			'export'        => 1,
			'options'       => isset( $_POST['options'] ) ? sanitize_text_field( wp_unslash( $_POST['options'] ) ) : '',
			'placeholder'   => isset( $_POST['placeholder'] ) ? sanitize_text_field( wp_unslash( $_POST['placeholder'] ) ) : '',
			'default_value' => isset( $_POST['default_value'] ) ? sanitize_text_field( wp_unslash( $_POST['default_value'] ) ) : '',
			'order'         => count( (array) $previous_columns ),
		);

		$settings['columns'][] = $column;
		$settings['columns'] = agum_sanitize_columns( $settings['columns'] );
		update_option( 'agum_settings', $settings );
		wp_cache_delete( 'agum_settings', 'agum' );
		AGUM_DB::sync_dynamic_columns( $previous_columns );
		$columns = AGUM_DB::user_columns();
		if ( 'password' !== $key && ! in_array( $key, $columns, true ) ) {
			$settings['columns'] = $previous_columns;
			update_option( 'agum_settings', $settings );
			wp_cache_delete( 'agum_settings', 'agum' );
			wp_send_json_error( array( 'message' => __( 'Database error: field metadata was not saved because the column could not be created.', 'amia-gallery-user-manager' ) ), 500 );
		}

		AGUM_Logger::notify( 'settings_saved', sprintf( __( 'Field %s created.', 'amia-gallery-user-manager' ), $key ), array( 'column' => $column ) );
		wp_send_json_success(
			array(
				'message' => __( 'Field created successfully.', 'amia-gallery-user-manager' ),
				'column'  => agum_get_column( $key ),
				'columns' => agum_get_columns(),
			)
		);
	}

	public static function delete_column() {
		AGUM_Security::ajax_guard();
		self::require_delete_confirmation();
		$key = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';
		if ( ! $key ) {
			wp_send_json_error( array( 'message' => __( 'Column key is required.', 'amia-gallery-user-manager' ) ), 400 );
		}
		$settings = agum_get_settings();
		$previous_columns = isset( $settings['columns'] ) ? $settings['columns'] : array();
		$settings['columns'] = array_values( array_filter( $previous_columns, static function ( $column ) use ( $key ) {
			return ! isset( $column['key'] ) || sanitize_key( $column['key'] ) !== $key;
		} ) );
		update_option( 'agum_settings', $settings );
		AGUM_DB::sync_dynamic_columns( $previous_columns );
		wp_cache_delete( 'agum_settings', 'agum' );
		AGUM_Logger::notify( 'settings_saved', sprintf( __( 'Column %s deleted.', 'amia-gallery-user-manager' ), $key ), array( 'column' => $key ) );
		wp_send_json_success( array( 'message' => sprintf( __( 'Column %s deleted and database synchronized.', 'amia-gallery-user-manager' ), $key ), 'columns' => $settings['columns'] ) );
	}

	private static function require_delete_confirmation() {
		$confirm = isset( $_REQUEST['confirm_text'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['confirm_text'] ) ) : '';
		if ( 'DELETE' !== $confirm ) {
			wp_send_json_error( array( 'message' => __( 'Type DELETE to confirm deletion.', 'amia-gallery-user-manager' ) ), 400 );
		}
	}

	public static function validate_image() {
		AGUM_Security::ajax_guard();
		if ( empty( $_FILES['image'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No image selected.', 'amia-gallery-user-manager' ) ) );
		}
		$result = AGUM_Upload::validate_upload_file( $_FILES['image'] );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Image is valid.', 'amia-gallery-user-manager' ), 'type' => $result['type'], 'ext' => $result['ext'] ) );
	}

	public static function upload_image() {
		AGUM_Security::ajax_guard();
		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$result = AGUM_Upload::handle_user_image( $_FILES['image'], $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( $result );
	}
}
