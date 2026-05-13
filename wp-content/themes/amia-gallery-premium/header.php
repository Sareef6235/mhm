<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?> data-theme="<?php echo esc_attr( get_theme_mod( 'amia_dark_mode_default', true ) ? 'dark' : 'light' ); ?>">
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class( 'amia-loading' ); ?>><?php wp_body_open(); ?>
<header class="amia-site-header">
	<div class="amia-container amia-navbar">
		<a class="amia-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php amia_premium_brand(); ?></a>
		<nav class="amia-primary-menu" aria-label="<?php esc_attr_e( 'Primary menu', 'amia-gallery-premium' ); ?>"><?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'amia-menu', 'fallback_cb' => false ) ); ?></nav>
		<div class="amia-actions"><button class="amia-icon-btn amia-search-toggle" aria-label="Search">⌕</button><button class="amia-icon-btn amia-theme-toggle" aria-label="Toggle theme">◐</button><button class="amia-icon-btn amia-notification-toggle" aria-label="Notifications">🔔</button><?php if ( is_user_logged_in() ) : ?><button class="amia-icon-btn amia-profile-toggle"><img src="<?php echo esc_url( amia_premium_user_image() ); ?>" alt="" width="28" height="28"></button><?php else : ?><a class="amia-btn" href="<?php echo esc_url( wp_login_url() ); ?>">Login</a><?php endif; ?><button class="amia-menu-toggle" aria-label="Menu">☰</button></div>
	</div>
	<div class="amia-dropdown amia-glass amia-notification-dropdown"><strong>Notifications</strong><span class="amia-muted">No new notifications.</span></div>
	<div class="amia-dropdown amia-glass amia-profile-dropdown"><a href="<?php echo esc_url( get_edit_profile_url() ); ?>">Profile</a><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Logout</a></div>
</header>
<div class="amia-search-popup"><div class="amia-search-panel amia-glass"><input type="search" class="amia-ajax-search" placeholder="Search AMIA Gallery..."><div class="amia-search-results"></div></div></div>
<main id="primary" class="site-main">
