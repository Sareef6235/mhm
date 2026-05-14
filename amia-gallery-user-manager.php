<?php
/**
 * Plugin Name: AMIA Gallery User Manager Pro
 * Plugin URI: https://amiagallery.mmhnu.online/
 * Description: Installer-safe loader for AMIA Gallery User Manager Pro. Supports WordPress installs from this repository ZIP and from the packaged plugin folder.
 * Version: 1.0.0
 * Author: AMIA Gallery
 * Author URI: https://amiagallery.mmhnu.online/
 * Text Domain: amia-gallery-user-manager
 * Domain Path: /amia-gallery-user-manager/languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$agum_bootstrap = __DIR__ . '/amia-gallery-user-manager/amia-gallery-user-manager.php';

if ( file_exists( $agum_bootstrap ) ) {
	require_once $agum_bootstrap;

	if ( class_exists( 'AMIA_Gallery_User_Manager_Pro' ) ) {
		register_activation_hook( __FILE__, array( 'AMIA_Gallery_User_Manager_Pro', 'activate' ) );
		register_deactivation_hook( __FILE__, array( 'AMIA_Gallery_User_Manager_Pro', 'deactivate' ) );
	}
} else {
	add_action(
		'admin_notices',
		static function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'AMIA Gallery User Manager Pro could not load because the plugin bootstrap folder is missing. Reinstall the complete plugin package.', 'amia-gallery-user-manager' )
			);
		}
	);
}
