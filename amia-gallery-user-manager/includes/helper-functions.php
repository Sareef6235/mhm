<?php
/**
 * Shared helper functions.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default plugin settings.
 *
 * @return array
 */
function agum_default_settings() {
	return array(
		'items_per_page'     => 20,
		'otp_expiry_minutes' => 10,
		'enable_dark_mode'   => 1,
		'delete_on_uninstall'=> 0,
		'columns'            => agum_default_columns(),
	);
}


/**
 * Default configurable columns.
 *
 * @return array
 */
function agum_default_columns() {
	$keys = array( 'student_id', 'admission_no', 'name', 'class', 'dob', 'role', 'username', 'email', 'password', 'phone_number', 'image_path', 'profile_photo', 'approval_status', 'notes', 'remarks' );
	$columns = array();
	foreach ( $keys as $index => $key ) {
		$columns[] = array( 'key' => $key, 'label' => ucwords( str_replace( '_', ' ', $key ) ), 'enabled' => 1, 'order' => $index );
	}
	return $columns;
}

/**
 * Sanitize configurable column settings.
 *
 * @param array $columns Raw columns.
 * @return array
 */
function agum_sanitize_columns( $columns ) {
	$clean = array();
	foreach ( (array) $columns as $index => $column ) {
		$key = isset( $column['key'] ) ? sanitize_key( $column['key'] ) : '';
		if ( ! $key ) {
			continue;
		}
		$clean[] = array(
			'key'     => $key,
			'label'   => isset( $column['label'] ) ? sanitize_text_field( $column['label'] ) : ucwords( str_replace( '_', ' ', $key ) ),
			'enabled' => ! empty( $column['enabled'] ) ? 1 : 0,
			'order'   => isset( $column['order'] ) ? absint( $column['order'] ) : $index,
		);
	}
	usort( $clean, static function ( $a, $b ) { return $a['order'] <=> $b['order']; } );
	return $clean ? $clean : agum_default_columns();
}

/**
 * Get merged settings.
 *
 * @return array
 */
function agum_get_settings() {
	$settings = get_option( 'agum_settings', array() );
	return wp_parse_args( is_array( $settings ) ? $settings : array(), agum_default_settings() );
}

/**
 * Include a template file.
 *
 * @param string $template Template name.
 * @param array  $args Arguments.
 * @return void
 */
function agum_template( $template, $args = array() ) {
	$file = AGUM_PATH . 'templates/' . sanitize_file_name( $template ) . '.php';
	if ( ! file_exists( $file ) ) {
		return;
	}

	extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	include $file;
}

/**
 * Normalize a username to the canonical media basename.
 *
 * @param string $name User name.
 * @return string
 */
function agum_normalize_image_basename( $name ) {
	$name = sanitize_text_field( $name );
	$name = strtoupper( str_replace( array( ' ', '-' ), '_', $name ) );
	$name = preg_replace( '/[^A-Z0-9_]/', '', $name );
	return trim( $name, '_' );
}

/**
 * Validate phone number with optional plus country code.
 *
 * @param string $phone Phone number.
 * @return bool
 */
function agum_is_valid_phone( $phone ) {
	$phone = trim( (string) $phone );
	return '' === $phone || (bool) preg_match( '/^\+?[0-9]{7,15}$/', $phone );
}

/**
 * Get plugin upload base directory and URL.
 *
 * @return array
 */
function agum_upload_info() {
	$uploads = wp_upload_dir();
	return array(
		'base_dir' => trailingslashit( $uploads['basedir'] ) . 'amia-gallery-user-manager',
		'base_url' => trailingslashit( $uploads['baseurl'] ) . 'amia-gallery-user-manager',
		'image_dir'=> trailingslashit( $uploads['basedir'] ) . 'amia-gallery-user-manager/user-images',
		'image_url'=> trailingslashit( $uploads['baseurl'] ) . 'amia-gallery-user-manager/user-images',
	);
}

/**
 * Build admin page URL.
 *
 * @param string $page Page slug.
 * @param array  $args Query args.
 * @return string
 */
function agum_admin_url( $page, $args = array() ) {
	return esc_url( add_query_arg( array_merge( array( 'page' => $page ), $args ), admin_url( 'admin.php' ) ) );
}


/**
 * Map plugin roles to native WordPress roles.
 *
 * @param string $role Plugin role.
 * @return string
 */
function agum_map_role_to_wp_role( $role ) {
	$map = array(
		'student'    => 'subscriber',
		'ustad'      => 'editor',
		'admin'      => 'administrator',
		'superadmin' => 'administrator',
		'staff'      => 'subscriber',
	);

	$role = sanitize_key( $role );
	return isset( $map[ $role ] ) ? $map[ $role ] : 'subscriber';
}

/**
 * Create a deterministic email when CSV/form data does not include one.
 *
 * @param string $username Username.
 * @return string
 */
function agum_generate_placeholder_email( $username ) {
	$username = sanitize_user( $username, true );
	if ( ! $username ) {
		$username = 'agum_user_' . wp_generate_password( 8, false, false );
	}

	return strtolower( $username ) . '@amia-gallery.local';
}

/**
 * Resolve a unique placeholder email without colliding with existing WordPress users.
 *
 * @param string $username Username.
 * @return string
 */
function agum_unique_placeholder_email( $username ) {
	$base  = agum_generate_placeholder_email( $username );
	$email = $base;
	$index = 2;

	while ( get_user_by( 'email', $email ) ) {
		$email = preg_replace( '/@/', '+' . $index . '@', $base, 1 );
		++$index;
	}

	return $email;
}
