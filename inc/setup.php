<?php
/**
 * Theme setup and supports.
 *
 * @package FairFlock
 * @since 0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers theme supports and textdomain.
 *
 * Block themes get most UI from theme.json. Supports here cover
 * WooCommerce readiness and editor/frontend hygiene.
 *
 * @since 0.2.0
 *
 * @return void
 */
function fairflock_setup() {
	load_theme_textdomain( 'fairflock', FAIRFLOCK_DIR . '/languages' );

	/*
	 * WooCommerce support is registered early so activating the plugin
	 * later does not require a theme change. Gallery features stay off
	 * until classic single-product PHP templates are intentionally used.
	 */
	add_theme_support( 'woocommerce' );

	// Lightweight component CSS in the Site Editor as well.
	add_editor_style( 'assets/css/components.css' );
	add_editor_style( 'assets/css/hero-slider.css' );
}
add_action( 'after_setup_theme', 'fairflock_setup' );

/**
 * Disable WooCommerce product-gallery lightbox (magnifying-glass / fullscreen).
 *
 * Runs late so it overrides WooCommerce defaults after the plugin boots.
 *
 * @since 0.9.128
 *
 * @return void
 */
function fairflock_disable_product_gallery_lightbox() {
	remove_theme_support( 'wc-product-gallery-lightbox' );
}
add_action( 'after_setup_theme', 'fairflock_disable_product_gallery_lightbox', 100 );
add_filter( 'woocommerce_single_product_photoswipe_enabled', '__return_false' );

/**
 * Product image sizes: portrait 2:3 (width × height).
 *
 * @since 0.9.338
 *
 * @param array<string, mixed> $size Size definition.
 * @return array<string, mixed>
 */
function fairflock_product_image_size_2x3( $size ) {
	if ( ! is_array( $size ) ) {
		$size = array();
	}
	$size['width']  = isset( $size['width'] ) ? (int) $size['width'] : 600;
	if ( $size['width'] < 1 ) {
		$size['width'] = 600;
	}
	$size['height'] = (int) round( $size['width'] * 1.5 );
	$size['crop']   = 1;
	return $size;
}
add_filter( 'woocommerce_get_image_size_thumbnail', 'fairflock_product_image_size_2x3' );
add_filter( 'woocommerce_get_image_size_single', 'fairflock_product_image_size_2x3' );
add_filter( 'woocommerce_get_image_size_gallery_thumbnail', 'fairflock_product_image_size_2x3' );

/**
 * Ensure body.front-page is present on the static front page.
 * Some runtimes omit it; homepage hero clearance depends on this class.
 *
 * @since 0.9.16
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fairflock_body_class_front_page( $classes ) {
	if ( is_front_page() && ! in_array( 'front-page', $classes, true ) ) {
		$classes[] = 'front-page';
	}

	return $classes;
}
add_filter( 'body_class', 'fairflock_body_class_front_page' );

/**
 * Keep WP 7.1 responsive block styles editable (padding etc. per device).
 *
 * @since 0.9.249
 *
 * @param array<string, mixed> $settings Editor settings.
 * @return array<string, mixed>
 */
function fairflock_enable_responsive_editing( $settings ) {
	$settings['responsiveEditingEnabled'] = true;
	return $settings;
}
add_filter( 'block_editor_settings_all', 'fairflock_enable_responsive_editing' );
