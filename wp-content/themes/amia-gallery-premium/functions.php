<?php
/**
 * AMIA Gallery Premium Theme functions.
 *
 * @package AMIA_Gallery_Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AMIA_PREMIUM_VERSION', '1.2.0' );
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
			'themeUri' => AMIA_PREMIUM_URI,
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
	$posts = get_posts( array( 's' => $query, 'post_type' => 'any', 'posts_per_page' => 6 ) );
	ob_start();
	if ( amia_premium_agum_available() && $query ) {
		$users = amia_premium_agum_users( array( 'search' => $query, 'per_page' => 6 ) );
		foreach ( (array) $users['items'] as $user ) {
			printf( '<a class="amia-search-result" href="%s"><strong>%s</strong><span>%s · %s</span></a>', esc_url( home_url( '/teacher-dashboard/' ) ), esc_html( isset( $user->name ) ? $user->name : '' ), esc_html__( 'User', 'amia-gallery-premium' ), esc_html( isset( $user->role ) ? $user->role : '' ) );
		}
	}
	foreach ( $posts as $post ) {
		printf( '<a class="amia-search-result" href="%s"><strong>%s</strong><span>%s</span></a>', esc_url( get_permalink( $post ) ), esc_html( get_the_title( $post ) ), esc_html( get_post_type( $post ) ) );
	}
	wp_send_json_success( array( 'html' => ob_get_clean() ) );
}
add_action( 'wp_ajax_amia_theme_search', 'amia_premium_ajax_search' );
add_action( 'wp_ajax_nopriv_amia_theme_search', 'amia_premium_ajax_search' );


function amia_premium_manifest() {
	wp_send_json(
		array(
			'name'             => 'AMIA Gallery Premium Platform',
			'short_name'       => 'AMIA',
			'start_url'        => home_url( '/dashboard/' ),
			'scope'            => home_url( '/' ),
			'display'          => 'standalone',
			'orientation'      => 'portrait',
			'background_color' => '#0f172a',
			'theme_color'      => '#2563eb',
			'description'      => 'Installable AMIA Gallery dashboard with offline shell, mobile navigation, notifications and reports.',
			'icons'            => array(
				array( 'src' => AMIA_PREMIUM_URI . '/assets/images/default-profile.svg', 'sizes' => '192x192', 'type' => 'image/svg+xml', 'purpose' => 'any maskable' ),
			),
			'shortcuts'        => array(
				array( 'name' => 'Dashboard', 'url' => home_url( '/dashboard/' ) ),
				array( 'name' => 'Gallery', 'url' => home_url( '/media-gallery/' ) ),
				array( 'name' => 'Reports', 'url' => home_url( '/reports/' ) ),
			),
		)
	);
}
add_action( 'wp_ajax_nopriv_amia_manifest', 'amia_premium_manifest' );
add_action( 'wp_ajax_amia_manifest', 'amia_premium_manifest' );

function amia_premium_pwa_tags() {
	echo '<link rel="manifest" href="' . esc_url( admin_url( 'admin-ajax.php?action=amia_manifest' ) ) . '"><meta name="theme-color" content="#2563eb">';
}
add_action( 'wp_head', 'amia_premium_pwa_tags' );

/**
 * Build premium dashboard analytics while preserving AGUM compatibility.
 *
 * @return array
 */
function amia_premium_dashboard_analytics() {
	$stats = amia_premium_agum_stats();
	$data  = array(
		'stats'        => $stats,
		'growth'       => array( 8, 12, 18, 24, 31, 44, max( 1, (int) $stats['total'] ) ),
		'uploads'      => array( 2, 4, 7, 9, 13, 18, max( 1, (int) $stats['images'] ) ),
		'roles'        => array(
			'students' => (int) $stats['students'],
			'ustads'   => (int) $stats['ustads'],
			'other'    => max( 0, (int) $stats['total'] - (int) $stats['students'] - (int) $stats['ustads'] ),
		),
		'devices'      => array( 'mobile' => 58, 'desktop' => 34, 'tablet' => 8 ),
		'browsers'     => array( 'chrome' => 62, 'safari' => 21, 'firefox' => 9, 'edge' => 8 ),
		'logins'       => array( 5, 10, 8, 14, 21, 17, 26 ),
		'activity'     => array( 12, 19, 10, 22, 28, 25, 33 ),
		'recent_users' => array(),
	);

	$users = amia_premium_agum_users( array( 'per_page' => 6 ) );
	foreach ( (array) $users['items'] as $user ) {
		$data['recent_users'][] = array(
			'name'      => isset( $user->name ) ? $user->name : '',
			'role'      => isset( $user->role ) ? $user->role : '',
			'last_seen' => isset( $user->last_seen ) && $user->last_seen ? human_time_diff( mysql2date( 'U', $user->last_seen ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'amia-gallery-premium' ) : __( 'Offline', 'amia-gallery-premium' ),
			'image'     => ! empty( $user->profile_photo ) ? esc_url( $user->profile_photo ) : amia_premium_user_image( isset( $user->wp_user_id ) ? $user->wp_user_id : 0 ),
		);
	}

	return $data;
}

/**
 * Secure AJAX analytics payload for live dashboard widgets.
 *
 * @return void
 */
function amia_premium_ajax_dashboard() {
	check_ajax_referer( 'amia_theme_nonce', 'nonce' );
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Login required.', 'amia-gallery-premium' ) ), 403 );
	}
	wp_send_json_success( amia_premium_dashboard_analytics() );
}
add_action( 'wp_ajax_amia_dashboard_data', 'amia_premium_ajax_dashboard' );

/**
 * View-only live notification center endpoint.
 *
 * @return void
 */
function amia_premium_ajax_notifications() {
	check_ajax_referer( 'amia_theme_nonce', 'nonce' );
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Login required.', 'amia-gallery-premium' ) ), 403 );
	}
	$args = array(
		'search'   => isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '',
		'category' => isset( $_GET['category'] ) ? sanitize_key( wp_unslash( $_GET['category'] ) ) : 'all',
	);
	$items  = class_exists( 'AGUM_Logger' ) ? AGUM_Logger::notifications( 20, false, $args ) : array();
	$unread = class_exists( 'AGUM_Logger' ) ? count( AGUM_Logger::notifications( 100, true ) ) : 0;
	wp_send_json_success( array( 'items' => $items, 'unread' => $unread, 'serverTime' => current_time( 'mysql' ) ) );
}
add_action( 'wp_ajax_amia_notifications', 'amia_premium_ajax_notifications' );

/**
 * Mark notifications read/unread without delete/edit behavior.
 *
 * @return void
 */
function amia_premium_ajax_mark_notifications() {
	check_ajax_referer( 'amia_theme_nonce', 'nonce' );
	if ( ! is_user_logged_in() || ! class_exists( 'AGUM_Logger' ) ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'amia-gallery-premium' ) ), 403 );
	}
	AGUM_Logger::mark_notifications_read();
	wp_send_json_success( array( 'message' => __( 'Notifications marked as read.', 'amia-gallery-premium' ) ) );
}
add_action( 'wp_ajax_amia_mark_notifications_read', 'amia_premium_ajax_mark_notifications' );

/**
 * CSV report export for frontend reporting pages.
 *
 * @return void
 */
function amia_premium_export_report() {
	if ( ! is_user_logged_in() || ! wp_verify_nonce( isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '', 'amia_theme_nonce' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'amia-gallery-premium' ) );
	}
	$users = amia_premium_agum_users( array( 'per_page' => 500 ) );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=amia-premium-report-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	fputcsv( $out, array( 'Name', 'Role', 'Email', 'Last Seen' ) );
	foreach ( (array) $users['items'] as $user ) {
		fputcsv( $out, array( isset( $user->name ) ? $user->name : '', isset( $user->role ) ? $user->role : '', isset( $user->email ) ? $user->email : '', isset( $user->last_seen ) ? $user->last_seen : '' ) );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_amia_export_report', 'amia_premium_export_report' );

/**
 * Add SEO and app metadata.
 *
 * @return void
 */
function amia_premium_head_meta() {
	$description = get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : __( 'AMIA Gallery Premium enterprise dashboard platform.', 'amia-gallery-premium' );
	?>
	<meta name="theme-color" content="#2563eb">
	<meta name="description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( wp_get_document_title() ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:type" content="website">
	<script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ), 'potentialAction' => array( '@type' => 'SearchAction', 'target' => home_url( '/?s={search_term_string}' ), 'query-input' => 'required name=search_term_string' ) ) ); ?></script>
	<?php
}
add_action( 'wp_head', 'amia_premium_head_meta', 5 );
