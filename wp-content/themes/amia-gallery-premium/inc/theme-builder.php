<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function amia_builder_defaults() {
	return array(
		'header' => array( 'sticky' => 1, 'layout' => 'split', 'cta' => 'Open Dashboard' ),
		'hero'   => array( 'title' => 'AMIA Gallery Premium Platform', 'subtitle' => 'Enterprise LMS, CRM and gallery dashboards.', 'button' => 'Get Started' ),
		'footer' => array( 'columns' => 3, 'text' => 'AMIA Gallery premium frontend experience.' ),
		'colors' => array( 'primary' => '#2563eb', 'secondary' => '#7c3aed', 'accent' => '#06b6d4' ),
		'cards'  => array( 'radius' => 28, 'glass' => 1 ),
	);
}
function amia_builder_settings() { return wp_parse_args( get_option( 'amia_visual_builder', array() ), amia_builder_defaults() ); }
function amia_builder_menu() { add_theme_page( 'AMIA Visual Theme Builder', 'AMIA Visual Builder', 'edit_theme_options', 'amia-visual-builder', 'amia_builder_page' ); }
add_action( 'admin_menu', 'amia_builder_menu' );
function amia_builder_page() { $settings = amia_builder_settings(); ?>
<div class="wrap"><h1>AMIA Visual Theme Builder</h1><p>Drag, edit, auto-save and preview premium frontend sections without using the unsafe WordPress Theme File Editor.</p><div id="amia-builder" class="amia-builder" data-settings='<?php echo esc_attr( wp_json_encode( $settings ) ); ?>'><div class="amia-builder-sidebar"><button class="button button-primary amia-builder-save">Save Builder</button><button class="button amia-builder-reset">Reset</button><h2>Sections</h2><div class="amia-builder-block" draggable="true" data-section="header">Header Builder</div><div class="amia-builder-block" draggable="true" data-section="hero">Hero Builder</div><div class="amia-builder-block" draggable="true" data-section="cards">Card Editor</div><div class="amia-builder-block" draggable="true" data-section="footer">Footer Builder</div><h2>Design</h2><label>Primary <input type="color" data-path="colors.primary" value="<?php echo esc_attr( $settings['colors']['primary'] ); ?>"></label><label>Font <input type="text" data-path="font" value="Inter"></label><label><input type="checkbox" data-path="dark" checked> Dark mode preview</label></div><div class="amia-builder-preview"><div class="amia-card"><span>Live Preview</span><h2 contenteditable data-path="hero.title"><?php echo esc_html( $settings['hero']['title'] ); ?></h2><p contenteditable data-path="hero.subtitle"><?php echo esc_html( $settings['hero']['subtitle'] ); ?></p><button class="button button-primary"><?php echo esc_html( $settings['hero']['button'] ); ?></button></div></div></div></div><?php }
function amia_builder_assets( $hook ) { if ( 'appearance_page_amia-visual-builder' !== $hook ) { return; } wp_enqueue_style( 'amia-builder', AMIA_PREMIUM_URI . '/assets/css/builder.css', array(), AMIA_PREMIUM_VERSION ); wp_enqueue_script( 'amia-builder', AMIA_PREMIUM_URI . '/assets/js/builder.js', array( 'jquery' ), AMIA_PREMIUM_VERSION, true ); wp_localize_script( 'amia-builder', 'amiaBuilder', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'amia_builder' ) ) ); }
add_action( 'admin_enqueue_scripts', 'amia_builder_assets' );
function amia_builder_save() { check_ajax_referer( 'amia_builder', 'nonce' ); if ( ! current_user_can( 'edit_theme_options' ) ) { wp_send_json_error(); } $settings = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : array(); update_option( 'amia_visual_builder', is_array( $settings ) ? $settings : array() ); wp_send_json_success( array( 'message' => 'Builder saved.' ) ); }
add_action( 'wp_ajax_amia_builder_save', 'amia_builder_save' );
