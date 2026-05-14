<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function amia_premium_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'amia_premium_options', array( 'title' => __( 'AMIA Theme Options', 'amia-gallery-premium' ), 'priority' => 25 ) );
	$wp_customize->add_section( 'amia_colors', array( 'title' => __( 'Colors & Mode', 'amia-gallery-premium' ), 'panel' => 'amia_premium_options' ) );
	$wp_customize->add_setting( 'amia_primary_color', array( 'default' => '#2563eb', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'amia_primary_color', array( 'label' => __( 'Primary Color', 'amia-gallery-premium' ), 'section' => 'amia_colors' ) ) );
	$wp_customize->add_setting( 'amia_dark_mode_default', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'amia_dark_mode_default', array( 'label' => __( 'Default to dark mode', 'amia-gallery-premium' ), 'section' => 'amia_colors', 'type' => 'checkbox' ) );
	$wp_customize->add_section( 'amia_typography', array( 'title' => __( 'Typography', 'amia-gallery-premium' ), 'panel' => 'amia_premium_options' ) );
	$wp_customize->add_setting( 'amia_font_stack', array( 'default' => 'Inter, system-ui, sans-serif', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'amia_font_stack', array( 'label' => __( 'Font Stack', 'amia-gallery-premium' ), 'section' => 'amia_typography', 'type' => 'text' ) );
	$wp_customize->add_section( 'amia_footer', array( 'title' => __( 'Footer Editor', 'amia-gallery-premium' ), 'panel' => 'amia_premium_options' ) );
	$wp_customize->add_setting( 'amia_footer_text', array( 'default' => 'Premium learning and gallery experience by AMIA Gallery.', 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'amia_footer_text', array( 'label' => __( 'Footer Text', 'amia-gallery-premium' ), 'section' => 'amia_footer', 'type' => 'textarea' ) );
}
add_action( 'customize_register', 'amia_premium_customize_register' );

function amia_premium_customizer_css() {
	$primary = get_theme_mod( 'amia_primary_color', '#2563eb' );
	$font = get_theme_mod( 'amia_font_stack', 'Inter, system-ui, sans-serif' );
	echo '<style id="amia-customizer-css">:root{--amia-primary:' . esc_html( $primary ) . '}body{font-family:' . esc_html( $font ) . '}</style>';
}
add_action( 'wp_head', 'amia_premium_customizer_css', 20 );
