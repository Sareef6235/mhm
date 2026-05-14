<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function amia_builder_defaults() {
	return array(
		'header'     => array( 'sticky' => 1, 'layout' => 'split', 'cta' => 'Open Dashboard', 'style' => 'glass' ),
		'hero'       => array( 'title' => 'AMIA Gallery Premium Platform', 'subtitle' => 'Enterprise LMS, CRM and gallery dashboards.', 'button' => 'Get Started' ),
		'navbar'     => array( 'rounded' => 1, 'blur' => 1 ),
		'footer'     => array( 'columns' => 3, 'text' => 'AMIA Gallery premium frontend experience.' ),
		'typography' => array( 'font' => 'Inter', 'scale' => 'modern' ),
		'colors'     => array( 'primary' => '#2563eb', 'secondary' => '#7c3aed', 'accent' => '#06b6d4', 'surface' => '#0f172a' ),
		'cards'      => array( 'radius' => 28, 'glass' => 1, 'shadow' => 1 ),
		'dark'       => array( 'enabled' => 1, 'contrast' => 'premium' ),
	);
}
function amia_builder_settings() { return wp_parse_args( get_option( 'amia_visual_builder', array() ), amia_builder_defaults() ); }
function amia_builder_menu() { add_theme_page( 'AMIA Visual Theme Builder', 'AMIA Visual Builder', 'edit_theme_options', 'amia-visual-builder', 'amia_builder_page' ); }
add_action( 'admin_menu', 'amia_builder_menu' );
function amia_builder_page() { $settings = amia_builder_settings(); ?>
<div class="wrap amia-builder-wrap"><h1>AMIA Visual Theme Builder</h1><p>Custom drag-drop visual builder. No WordPress Theme File Editor required.</p><div id="amia-builder" class="amia-builder" data-settings='<?php echo esc_attr( wp_json_encode( $settings ) ); ?>'><aside class="amia-builder-sidebar"><button class="button button-primary amia-builder-save">Save Builder</button><button class="button amia-builder-reset">Reset Preview</button><h2>Drag Sections</h2><?php foreach ( array( 'header' => 'Navbar Editor', 'hero' => 'Hero Editor', 'cards' => 'Card Editor', 'footer' => 'Footer Editor' ) as $section => $label ) : ?><div class="amia-builder-block" draggable="true" data-section="<?php echo esc_attr( $section ); ?>"><?php echo esc_html( $label ); ?></div><?php endforeach; ?><h2>Design System</h2><label>Primary <input type="color" data-path="colors.primary" value="<?php echo esc_attr( $settings['colors']['primary'] ); ?>"></label><label>Secondary <input type="color" data-path="colors.secondary" value="<?php echo esc_attr( $settings['colors']['secondary'] ); ?>"></label><label>Accent <input type="color" data-path="colors.accent" value="<?php echo esc_attr( $settings['colors']['accent'] ); ?>"></label><label>Font <input type="text" data-path="typography.font" value="<?php echo esc_attr( $settings['typography']['font'] ); ?>"></label><label>Card Radius <input type="range" min="8" max="40" data-path="cards.radius" value="<?php echo esc_attr( $settings['cards']['radius'] ); ?>"></label><label><input type="checkbox" data-path="dark.enabled" checked> Dark mode editor</label></aside><main class="amia-builder-preview"><nav class="builder-nav">AMIA <button class="button button-primary"><?php echo esc_html( $settings['header']['cta'] ); ?></button></nav><section class="builder-hero"><span>Live Preview</span><h2 contenteditable data-path="hero.title"><?php echo esc_html( $settings['hero']['title'] ); ?></h2><p contenteditable data-path="hero.subtitle"><?php echo esc_html( $settings['hero']['subtitle'] ); ?></p><button class="button button-primary" contenteditable data-path="hero.button"><?php echo esc_html( $settings['hero']['button'] ); ?></button></section><section class="builder-bento"><div class="amia-card">Dashboard Card</div><div class="amia-card">Gallery Card</div><div class="amia-card">Report Card</div></section></main></div></div><?php }
function amia_builder_assets( $hook ) { if ( 'appearance_page_amia-visual-builder' !== $hook ) { return; } wp_enqueue_style( 'amia-builder', AMIA_PREMIUM_URI . '/assets/css/builder.css', array(), AMIA_PREMIUM_VERSION ); wp_enqueue_script( 'amia-builder', AMIA_PREMIUM_URI . '/assets/js/builder.js', array( 'jquery' ), AMIA_PREMIUM_VERSION, true ); wp_localize_script( 'amia-builder', 'amiaBuilder', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'amia_builder' ) ) ); }
add_action( 'admin_enqueue_scripts', 'amia_builder_assets' );
function amia_builder_save() { check_ajax_referer( 'amia_builder', 'nonce' ); if ( ! current_user_can( 'edit_theme_options' ) ) { wp_send_json_error(); } $settings = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : array(); update_option( 'amia_visual_builder', is_array( $settings ) ? $settings : array() ); wp_send_json_success( array( 'message' => 'Builder saved.' ) ); }
add_action( 'wp_ajax_amia_builder_save', 'amia_builder_save' );
