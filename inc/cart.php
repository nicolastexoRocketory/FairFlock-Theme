<?php
/**
 * Cart: empty-cart state.
 *
 * @package FairFlock
 * @since 0.9.502
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newest products for the empty cart (plain links — no add-to-cart JS inside the cart block).
 *
 * @since 0.9.502
 *
 * @param int $limit Number of products.
 * @return string
 */
function fairflock_cart_empty_products_markup( $limit = 4 ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return '';
	}

	$products = wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => $limit,
			'orderby'    => 'date',
			'order'      => 'DESC',
		)
	);
	if ( empty( $products ) ) {
		return '';
	}

	$items = '';
	foreach ( $products as $product ) {
		$items .= sprintf(
			'<li class="ff-cart-empty__product"><a class="ff-cart-empty__product-link" href="%1$s"><span class="ff-cart-empty__product-media">%2$s</span><span class="ff-cart-empty__product-name">%3$s</span><span class="ff-cart-empty__product-price">%4$s</span></a></li>',
			esc_url( $product->get_permalink() ),
			$product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ),
			esc_html( $product->get_name() ),
			wp_kses_post( $product->get_price_html() )
		);
	}

	$shop_url = wc_get_page_permalink( 'shop' );

	return sprintf(
		'<section class="ff-cart-empty__new" aria-labelledby="ff-cart-empty-new"><div class="ff-cart-empty__new-head"><h2 id="ff-cart-empty-new" class="ff-cart-empty__new-title">%1$s</h2><a class="ff-cart-empty__new-link" href="%2$s">%3$s</a></div><ul class="ff-cart-empty__products">%4$s</ul></section>',
		esc_html__( 'Neu im Shop', 'fairflock' ),
		esc_url( $shop_url ),
		esc_html__( 'Alle Produkte', 'fairflock' ),
		$items
	);
}

/**
 * Replaces the stored empty-cart content (English defaults, legacy product grid) with a clean empty state.
 *
 * @since 0.9.502
 *
 * @param string $block_content Rendered block HTML.
 * @return string
 */
function fairflock_cart_render_empty_block( $block_content ) {
	if ( ! function_exists( 'wc_get_page_permalink' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag() ) {
		return $block_content;
	}
	$processor->add_class( 'ff-cart-empty' );
	$updated = $processor->get_updated_html();

	if ( ! preg_match( '/^\s*<div\b[^>]*>/', $updated, $open ) ) {
		return $block_content;
	}

	$icon = '<span class="ff-cart-empty__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="28" height="28"><path d="M6 7h12l-1 13H7L6 7z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 7V6a3 3 0 0 1 6 0v1" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>';

	$hero = sprintf(
		'<div class="ff-cart-empty__hero">%1$s<h2 class="ff-cart-empty__title">%2$s</h2><p class="ff-cart-empty__text">%3$s</p><a class="ff-cart-empty__cta" href="%4$s">%5$s</a></div>',
		$icon,
		esc_html__( 'Dein Warenkorb ist leer', 'fairflock' ),
		esc_html__( 'Stöbere durch unsere Produkte – bestimmt ist etwas für dich dabei.', 'fairflock' ),
		esc_url( wc_get_page_permalink( 'shop' ) ),
		esc_html__( 'Weiter einkaufen', 'fairflock' )
	);

	return $open[0] . $hero . fairflock_cart_empty_products_markup() . '</div>';
}
add_filter( 'render_block_woocommerce/empty-cart-block', 'fairflock_cart_render_empty_block' );
