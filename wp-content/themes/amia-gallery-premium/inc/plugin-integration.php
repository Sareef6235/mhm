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

function amia_premium_user_image( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	$image = get_user_meta( $user_id, 'agum_profile_image', true );
	return $image ? esc_url( $image ) : esc_url( AMIA_PREMIUM_URI . '/assets/images/default-profile.svg' );
}
