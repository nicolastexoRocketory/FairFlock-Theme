<?php
/**
 * Performance-related filters and asset strategy.
 *
 * @package FairFlock
 * @since 0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load core block CSS only when the block is rendered.
 *
 * Block themes often default to true; we set it explicitly for clarity
 * and so classic/plugin contexts stay consistent.
 *
 * @see https://developer.wordpress.org/reference/hooks/should_load_separate_core_block_assets/
 *
 * @since 0.2.0
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * Prefer separate block style handles for any registered blocks that opt in.
 *
 * @since 0.2.0
 */
add_filter( 'should_load_block_assets_on_demand', '__return_true' );

/**
 * Frontend asset policy.
 *
 * @since 0.2.0
 * @since 0.3.0 Enqueues lightweight header/footer component CSS.
 * @since 0.4.0 Enqueues header search script (defer).
 *
 * @return void
 */
function fairflock_enqueue_frontend_assets() {
	wp_enqueue_style(
		'fairflock-components',
		FAIRFLOCK_URI . '/assets/css/components.css',
		array(),
		FAIRFLOCK_VERSION
	);

	wp_enqueue_style(
		'fairflock-hero-slider',
		FAIRFLOCK_URI . '/assets/css/hero-slider.css',
		array( 'fairflock-components' ),
		FAIRFLOCK_VERSION
	);

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wp_enqueue_style(
			'fairflock-checkout',
			FAIRFLOCK_URI . '/assets/css/checkout.css',
			array( 'fairflock-components' ),
			FAIRFLOCK_VERSION
		);
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		$cart_deps = array( 'fairflock-components' );
		foreach ( array( 'wc-blocks-style', 'wc-blocks-style-cart', 'wc-blocks-vendors-style' ) as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) || wp_style_is( $handle, 'enqueued' ) ) {
				$cart_deps[] = $handle;
			}
		}
		wp_enqueue_style(
			'fairflock-cart',
			FAIRFLOCK_URI . '/assets/css/cart.css',
			$cart_deps,
			FAIRFLOCK_VERSION
		);
	}

	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		$account_deps = array( 'fairflock-components' );
		foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-blocks-style' ) as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) || wp_style_is( $handle, 'enqueued' ) ) {
				$account_deps[] = $handle;
			}
		}
		wp_enqueue_style(
			'fairflock-account',
			FAIRFLOCK_URI . '/assets/css/account.css',
			$account_deps,
			FAIRFLOCK_VERSION
		);
	}

	if ( is_page( array( 'impressum', 'datenschutz', 'agb', 'widerruf', 'versand-zahlung' ) ) ) {
		wp_enqueue_style(
			'fairflock-legal',
			FAIRFLOCK_URI . '/assets/css/legal.css',
			array( 'fairflock-components' ),
			FAIRFLOCK_VERSION
		);
	}

	if ( is_page( 'ueber-uns' ) ) {
		wp_enqueue_style(
			'fairflock-about',
			FAIRFLOCK_URI . '/assets/css/about.css',
			array( 'fairflock-components' ),
			FAIRFLOCK_VERSION
		);
	}

	wp_enqueue_script(
		'fairflock-header-search',
		FAIRFLOCK_URI . '/assets/js/header-search.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'fairflock-header-scroll',
		FAIRFLOCK_URI . '/assets/js/header-scroll.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'fairflock-nav-mega',
		FAIRFLOCK_URI . '/assets/js/nav-mega.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'fairflock-hero-slider',
		FAIRFLOCK_URI . '/assets/js/hero-slider.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'fairflock-products-slider',
		FAIRFLOCK_URI . '/assets/js/products-slider.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'fairflock-cart-drawer',
		FAIRFLOCK_URI . '/assets/js/cart-drawer.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'fairflock-product-modal',
		FAIRFLOCK_URI . '/assets/js/product-modal.js',
		array( 'fairflock-personalization' ),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'fairflock-account-popup',
		FAIRFLOCK_URI . '/assets/js/account-popup.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'fairflock-faq-accordion',
		FAIRFLOCK_URI . '/assets/js/faq-accordion.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	$shop     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$cart     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'cart' ) : home_url( '/cart/' );
	$checkout = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'checkout' ) : home_url( '/checkout/' );
	$api      = function_exists( 'fairflock_get_store_products_api_url' ) ? fairflock_get_store_products_api_url() : '';
	$cart_api = function_exists( 'rest_url' ) ? rest_url( 'wc/store/v1/cart' ) : '/wp-json/wc/store/v1/cart';

	wp_add_inline_script(
		'fairflock-header-search',
		'window.fairflockSearchHome=' . wp_json_encode( home_url( '/' ) ) . ';'
		. 'window.fairflockSearchShop=' . wp_json_encode( $shop ) . ';'
		. 'window.fairflockSearchApi=' . wp_json_encode( $api ) . ';'
		. 'window.fairflockSearchCatalog=' . wp_json_encode( fairflock_get_search_catalog() ) . ';',
		'before'
	);

	wp_add_inline_script(
		'fairflock-cart-drawer',
		'window.fairflockCartApi=' . wp_json_encode( $cart_api ) . ';'
		. 'window.fairflockCartUrl=' . wp_json_encode( $cart ) . ';'
		. 'window.fairflockCheckoutUrl=' . wp_json_encode( $checkout ) . ';',
		'before'
	);

	$personalized_api = function_exists( 'rest_url' ) ? rest_url( 'fairflock/v1/cart/add-personalized' ) : '/wp-json/fairflock/v1/cart/add-personalized';
	$product_bases    = function_exists( 'fairflock_get_product_link_bases' )
		? fairflock_get_product_link_bases()
		: array( 'product', 'produkt' );

	wp_add_inline_script(
		'fairflock-product-modal',
		'window.fairflockSearchApi=' . wp_json_encode( $api ) . ';'
		. 'window.fairflockCartApi=' . wp_json_encode( $cart_api ) . ';'
		. 'window.fairflockPersonalizedAddApi=' . wp_json_encode( $personalized_api ) . ';'
		. 'window.fairflockProductBases=' . wp_json_encode( array_values( $product_bases ) ) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'fairflock_enqueue_frontend_assets', 20 );

/**
 * Removes Dashicons for logged-out visitors (admin bar does not need them).
 *
 * @since 0.2.0
 *
 * @return void
 */
function fairflock_dequeue_dashicons_for_guests() {
	if ( is_user_logged_in() ) {
		return;
	}

	wp_dequeue_style( 'dashicons' );
}
add_action( 'wp_enqueue_scripts', 'fairflock_dequeue_dashicons_for_guests', 100 );

/**
 * Whether the request hits local Playground / loopback staging.
 *
 * @since 0.9.85
 *
 * @return bool
 */
function fairflock_is_local_staging() {
	if ( defined( 'FAIRFLOCK_IS_PLAYGROUND' ) ) {
		return (bool) FAIRFLOCK_IS_PLAYGROUND;
	}

	$host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
	return (bool) preg_match( '/^(127\.0\.0\.1|localhost)(:\d+)?$/', $host );
}

/**
 * Lighten local Playground: fewer background PHP hits (each costs ~1–2s in WASM).
 *
 * @since 0.9.85
 *
 * @return void
 */
function fairflock_playground_lighten() {
	if ( ! fairflock_is_local_staging() ) {
		return;
	}

	// Heartbeat hammered admin/editor with extra PHP boots.
	add_action(
		'init',
		static function () {
			wp_deregister_script( 'heartbeat' );
		},
		1
	);

	add_filter( 'wp_refresh_nonces', '__return_false', 99 );

	// WooCommerce: skip remote/marketplace noise and cart-fragment polling.
	add_filter( 'woocommerce_allow_marketplace_suggestions', '__return_false' );
	add_filter( 'woocommerce_helper_suppress_admin_notices', '__return_true' );

	if ( ! is_admin() ) {
		add_action(
			'wp_enqueue_scripts',
			static function () {
				wp_dequeue_script( 'wc-cart-fragments' );
				wp_deregister_script( 'wc-cart-fragments' );
			},
			100
		);
	}

	// Emoji → extra work on every page.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'after_setup_theme', 'fairflock_playground_lighten', 5 );
