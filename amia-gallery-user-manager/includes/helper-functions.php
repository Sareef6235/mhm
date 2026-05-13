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
	);
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
