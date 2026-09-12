<?php
/**
 * Header search: cart link + Woo product suggestions / Store API config.
 *
 * Overlay markup: parts/search-overlay.html
 *
 * @package FairFlock
 * @since 0.4.0
 * @since 0.5.2 Overlay is a dedicated template part (no editor placeholder text).
 * @since 0.8.0 Real WooCommerce catalog + Store API search endpoint.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Points the header cart link at WooCommerce when available.
 *
 * @since 0.4.0
 *
 * @return void
 */
function fairflock_print_cart_link_script() {
	if ( is_admin() || ! function_exists( 'wc_get_cart_url' ) ) {
		return;
	}

	$url   = esc_url( wc_get_cart_url() );
	$count = WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
	?>
	<script>
	(function () {
		var link = document.querySelector('.ff-cart .wp-block-button__link') || document.getElementById('ffCartLink');
		var badge = document.getElementById('ffCartCount') || (link && link.querySelector('.ff-cart__count'));
		if (link) link.setAttribute('href', <?php echo wp_json_encode( $url ); ?>);
		if (badge) badge.textContent = <?php echo wp_json_encode( (string) $count ); ?>;
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'fairflock_print_cart_link_script', 20 );

/**
 * Product suggestions for the search mega (empty query).
 *
 * Uses real WooCommerce products when available; otherwise placeholder rows.
 *
 * @since 0.4.0
 * @since 0.8.0 Pulls published products from WooCommerce.
 *
 * @return array<int, array<string, string>>
 */
function fairflock_get_search_catalog() {
	if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_products' ) ) {
		return fairflock_get_search_catalog_fallback();
	}

	$cache_key = 'fairflock_search_catalog_v1';
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 8,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
		)
	);

	if ( empty( $products ) ) {
		return fairflock_get_search_catalog_fallback();
	}

	$items = array();

	foreach ( $products as $product ) {
		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$items[] = fairflock_format_search_catalog_item( $product );
	}

	set_transient( $cache_key, $items, 15 * MINUTE_IN_SECONDS );

	return $items;
}

/**
 * Busts the header search catalog cache when products change.
 *
 * @return void
 */
function fairflock_flush_search_catalog_cache() {
	delete_transient( 'fairflock_search_catalog_v1' );
}
add_action( 'save_post_product', 'fairflock_flush_search_catalog_cache' );
add_action( 'deleted_post', 'fairflock_flush_search_catalog_cache' );
add_action( 'woocommerce_update_product', 'fairflock_flush_search_catalog_cache' );

/**
 * Maps a WC_Product to the search mega item shape.
 *
 * @since 0.8.0
 *
 * @param WC_Product $product Product.
 * @return array<string, string>
 */
function fairflock_format_search_catalog_item( WC_Product $product ) {
	$image_id = $product->get_image_id();
	$img      = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : '';

	$tag_names = array();
	$tags      = get_the_terms( $product->get_id(), 'product_tag' );
	if ( is_array( $tags ) ) {
		foreach ( $tags as $tag ) {
			$tag_names[] = $tag->name;
		}
	}

	$cats = get_the_terms( $product->get_id(), 'product_cat' );
	if ( is_array( $cats ) ) {
		foreach ( $cats as $cat ) {
			$tag_names[] = $cat->name;
		}
	}

	return array(
		'name'  => $product->get_name(),
		'price' => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_price() ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
		'url'   => $product->get_permalink(),
		'q'     => $product->get_name(),
		'img'   => $img ? $img : '',
		'tags'  => implode( ' ', $tag_names ),
	);
}

/**
 * Placeholder catalog when Woo is inactive or has no products.
 *
 * @since 0.8.0
 *
 * @return array<int, array<string, string>>
 */
function fairflock_get_search_catalog_fallback() {
	return array(
		array(
			'name'  => 'Beispielprodukt Alpha',
			'price' => '24,90 €',
			'q'     => 'Alpha',
			'url'   => '',
			'img'   => '',
			'tags'  => 'beispiel alpha',
		),
		array(
			'name'  => 'Beispielprodukt Beta',
			'price' => '39,00 €',
			'q'     => 'Beta',
			'url'   => '',
			'img'   => '',
			'tags'  => 'beispiel beta',
		),
		array(
			'name'  => 'Beispielprodukt Gamma',
			'price' => '14,90 €',
			'q'     => 'Gamma',
			'url'   => '',
			'img'   => '',
			'tags'  => 'beispiel gamma',
		),
	);
}

/**
 * Public Store API products URL for live search (empty when Woo inactive).
 *
 * @since 0.8.0
 *
 * @return string
 */
function fairflock_get_store_products_api_url() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	return esc_url_raw( rest_url( 'wc/store/v1/products' ) );
}

/**
 * Product search results URL for a query string.
 *
 * @since 0.8.0
 *
 * @param string $query Search query.
 * @return string
 */
function fairflock_get_product_search_url( $query = '' ) {
	$args = array(
		'post_type' => 'product',
	);

	$query = trim( (string) $query );
	if ( $query !== '' ) {
		$args['s'] = $query;
	}

	return add_query_arg( $args, home_url( '/' ) );
}
