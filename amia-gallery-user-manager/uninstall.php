<?php
/**
 * Uninstall cleanup.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'agum_settings', array() );
if ( ! empty( $settings['delete_on_uninstall'] ) ) {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}agum_users" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}agum_logs" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}agum_otps" );
	delete_option( 'agum_settings' );
	delete_option( 'agum_native_user_sync_complete' );
}
