<?php
/**
 * AMIA Gallery Premium Theme functions.
 *
 * @package AMIA_Gallery_Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AMIA_PREMIUM_VERSION', '1.1.0' );
define( 'AMIA_PREMIUM_URI', get_template_directory_uri() );
define( 'AMIA_PREMIUM_DIR', get_template_directory() );
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

require_once AMIA_PREMIUM_DIR . '/inc/template-functions.php';
require_once AMIA_PREMIUM_DIR . '/inc/customizer.php';
require_once AMIA_PREMIUM_DIR . '/inc/plugin-integration.php';
require_once AMIA_PREMIUM_DIR . '/inc/theme-builder.php';

function amia_premium_setup() {
	load_theme_textdomain( 'amia-gallery-premium', AMIA_PREMIUM_DIR . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 240, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'custom-background', array( 'default-color' => '0f172a' ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'amia-gallery-premium' ),
			'footer'  => __( 'Footer Menu', 'amia-gallery-premium' ),
			'dashboard' => __( 'Dashboard Menu', 'amia-gallery-premium' ),
		)
	);
}
add_action( 'after_setup_theme', 'amia_premium_setup' );

function amia_premium_widgets_init() {
	$areas = array(
		'sidebar-1' => __( 'Main Sidebar', 'amia-gallery-premium' ),
		'footer-1'  => __( 'Footer Column 1', 'amia-gallery-premium' ),
		'footer-2'  => __( 'Footer Column 2', 'amia-gallery-premium' ),
		'footer-3'  => __( 'Footer Column 3', 'amia-gallery-premium' ),
		'dashboard' => __( 'Frontend Dashboard Widgets', 'amia-gallery-premium' ),
	);
	foreach ( $areas as $id => $name ) {
		register_sidebar(
			array(
				'name'          => $name,
				'id'            => $id,
				'before_widget' => '<section id="%1$s" class="widget amia-card %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'amia_premium_widgets_init' );

function amia_premium_scripts() {
	wp_enqueue_style( 'amia-premium-style', get_stylesheet_uri(), array(), AMIA_PREMIUM_VERSION );
	wp_enqueue_style( 'amia-premium-theme', AMIA_PREMIUM_URI . '/assets/css/theme.css', array( 'amia-premium-style' ), AMIA_PREMIUM_VERSION );
	wp_enqueue_script( 'amia-premium-theme', AMIA_PREMIUM_URI . '/assets/js/theme.js', array( 'jquery' ), AMIA_PREMIUM_VERSION, true );
	wp_localize_script(
		'amia-premium-theme',
		'amiaTheme',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'amia_theme_nonce' ),
			'isUser'  => is_user_logged_in(),
		)
	);
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'amia_premium_scripts' );

function amia_premium_body_classes( $classes ) {
	$classes[] = 'amia-premium-theme';
	$classes[] = get_theme_mod( 'amia_dark_mode_default', true ) ? 'amia-dark-default' : 'amia-light-default';
	return $classes;
}
add_filter( 'body_class', 'amia_premium_body_classes' );

function amia_premium_ajax_search() {
	check_ajax_referer( 'amia_theme_nonce', 'nonce' );
	$query = isset( $_GET['query'] ) ? sanitize_text_field( wp_unslash( $_GET['query'] ) ) : '';
	$posts = get_posts( array( 's' => $query, 'post_type' => 'any', 'posts_per_page' => 8 ) );
	ob_start();
	foreach ( $posts as $post ) {
		printf( '<a class="amia-search-result" href="%s"><strong>%s</strong><span>%s</span></a>', esc_url( get_permalink( $post ) ), esc_html( get_the_title( $post ) ), esc_html( get_post_type( $post ) ) );
	}
	wp_send_json_success( array( 'html' => ob_get_clean() ) );
}
add_action( 'wp_ajax_amia_theme_search', 'amia_premium_ajax_search' );
add_action( 'wp_ajax_nopriv_amia_theme_search', 'amia_premium_ajax_search' );

function amia_premium_clear_activity() {
	check_ajax_referer( 'amia_theme_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) || ! class_exists( 'AGUM_Logger' ) ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'amia-gallery-premium' ) ), 403 );
	}
	AGUM_Logger::clear();
	wp_send_json_success( array( 'message' => __( 'Recent activity cleared.', 'amia-gallery-premium' ) ) );
}
add_action( 'wp_ajax_amia_clear_activity', 'amia_premium_clear_activity' );


function amia_premium_manifest() {
	wp_send_json( array( 'name' => 'AMIA Gallery', 'short_name' => 'AMIA', 'start_url' => home_url( '/' ), 'display' => 'standalone', 'background_color' => '#0f172a', 'theme_color' => '#2563eb', 'icons' => array( array( 'src' => AMIA_PREMIUM_URI . '/assets/images/default-profile.svg', 'sizes' => '192x192', 'type' => 'image/svg+xml' ) ) ) );
}
add_action( 'wp_ajax_nopriv_amia_manifest', 'amia_premium_manifest' );
add_action( 'wp_ajax_amia_manifest', 'amia_premium_manifest' );

function amia_premium_pwa_tags() {
	echo '<link rel="manifest" href="' . esc_url( admin_url( 'admin-ajax.php?action=amia_manifest' ) ) . '"><meta name="theme-color" content="#2563eb">';
}
add_action( 'wp_head', 'amia_premium_pwa_tags' );
