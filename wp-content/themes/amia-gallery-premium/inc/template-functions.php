<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function amia_premium_brand() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	echo '<span class="amia-brand-mark">A</span><span>' . esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'AMIA Gallery' ) . '</span>';
}

function amia_premium_get_user_role_key( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	$role = get_user_meta( $user_id, 'agum_role', true );
	if ( $role ) { return sanitize_key( $role ); }
	$user = get_userdata( $user_id );
	return $user && ! empty( $user->roles ) ? sanitize_key( $user->roles[0] ) : 'guest';
}

function amia_premium_page_hero( $title = '', $subtitle = '' ) {
	$title = $title ? $title : get_the_title();
	printf( '<section class="amia-section"><div class="amia-container amia-card amia-animate"><span class="amia-kicker">AMIA Gallery</span><h1>%s</h1><p class="amia-muted">%s</p></div></section>', esc_html( $title ), esc_html( $subtitle ) );
}

function amia_premium_posts_grid( $query = null ) {
	global $wp_query;
	$q = $query ? $query : $wp_query;
	echo '<div class="amia-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		get_template_part( 'components/card', 'post' );
	}
	echo '</div>';
	wp_reset_postdata();
}
