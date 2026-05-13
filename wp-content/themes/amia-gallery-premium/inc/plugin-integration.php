<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function amia_premium_agum_available() { return class_exists( 'AGUM_Users' ) && class_exists( 'AGUM_DB' ); }

function amia_premium_agum_stats() {
	if ( amia_premium_agum_available() ) { return AGUM_Users::stats(); }
	return array( 'total' => count_users()['total_users'], 'students' => 0, 'ustads' => 0, 'images' => 0 );
}

function amia_premium_agum_users( $args = array() ) {
	if ( amia_premium_agum_available() ) { return AGUM_Users::query( wp_parse_args( $args, array( 'per_page' => 12 ) ) ); }
	return array( 'items' => array(), 'total' => 0 );
}

function amia_premium_agum_logs( $limit = 8 ) {
	return class_exists( 'AGUM_Logger' ) ? AGUM_Logger::recent( $limit ) : array();
}

function amia_premium_agum_notifications( $limit = 8 ) {
	return class_exists( 'AGUM_Logger' ) ? AGUM_Logger::notifications( $limit ) : array();
}

function amia_premium_user_image( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	$image = get_user_meta( $user_id, 'agum_profile_image', true );
	return $image ? esc_url( $image ) : esc_url( AMIA_PREMIUM_URI . '/assets/images/default-profile.svg' );
}

function amia_premium_agum_columns( $context = 'export' ) {
	if ( function_exists( 'agum_get_columns_for_context' ) ) {
		return agum_get_columns_for_context( $context );
	}
	return array(
		array( 'key' => 'name', 'label' => __( 'Name', 'amia-gallery-premium' ) ),
		array( 'key' => 'class', 'label' => __( 'Class', 'amia-gallery-premium' ) ),
	);
}

function amia_premium_agum_profile_for_wp_user( $wp_user_id = 0 ) {
	if ( ! amia_premium_agum_available() ) {
		return null;
	}
	global $wpdb;
	$wp_user_id = $wp_user_id ? absint( $wp_user_id ) : get_current_user_id();
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AGUM_DB::users_table() . ' WHERE wp_user_id = %d LIMIT 1', $wp_user_id ) );
}
