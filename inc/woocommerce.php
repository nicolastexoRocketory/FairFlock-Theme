<?php
/**
 * WooCommerce integration (safe when plugin is inactive).
 *
 * @package FairFlock
 * @since 0.2.0
 * @since 0.7.0 Shop block templates + Woo product pattern when active.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is a WooCommerce surface.
 *
 * Includes classic shop views and pages that typically embed cart/checkout blocks.
 *
 * @since 0.2.0
 *
 * @return bool
 */
function fairflock_is_woocommerce_view() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}

	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		return true;
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return true;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}

	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		return true;
	}

	return false;
}

/**
 * Dequeues classic WooCommerce stylesheets off shop surfaces.
 *
 * Does not touch WooCommerce Blocks stylesheets: those belong on pages that
 * render product/cart blocks (e.g. homepage patterns) and load on demand.
 *
 * @since 0.2.0
 *
 * @return void
 */
function fairflock_dequeue_woocommerce_styles() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( fairflock_is_woocommerce_view() ) {
		return;
	}

	wp_dequeue_style( 'woocommerce-general' );
	wp_dequeue_style( 'woocommerce-layout' );
	wp_dequeue_style( 'woocommerce-smallscreen' );
}
add_action( 'wp_enqueue_scripts', 'fairflock_dequeue_woocommerce_styles', 99 );

/**
 * Limits classic WooCommerce stylesheets to shop surfaces.
 *
 * @since 0.2.0
 *
 * @param array $styles Style handles queued by WooCommerce.
 * @return array
 */
function fairflock_woocommerce_enqueue_styles( $styles ) {
	if ( ! is_array( $styles ) ) {
		return array();
	}

	if ( ! fairflock_is_woocommerce_view() ) {
		return array();
	}

	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'fairflock_woocommerce_enqueue_styles' );

/**
 * Remove classic WooCommerce “Angebot!” / sale flash on product images.
 *
 * @since 0.9.131
 *
 * @param string $html Sale flash HTML.
 * @return string
 */
function fairflock_remove_sale_flash( $html ) {
	return '';
}
add_filter( 'woocommerce_sale_flash', 'fairflock_remove_sale_flash', 10, 1 );

/**
 * Product breadcrumb reads as a faded path label.
 *
 * @since 0.9.264
 *
 * @param array<int, array{0:string,1:string}> $crumbs Crumbs.
 * @return array<int, array{0:string,1:string}>
 */
function fairflock_product_breadcrumb_label( $crumbs ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return $crumbs;
	}

	return array(
		array( __( 'Zurückgelegter Weg', 'fairflock' ), '' ),
	);
}
add_filter( 'woocommerce_get_breadcrumb', 'fairflock_product_breadcrumb_label' );

/**
 * Prevents Woo from auto-inserting customer-account after Navigation.
 *
 * The account icon lives explicitly in the header actions (left of search).
 *
 * @since 0.9.1
 *
 * @param array  $hooked_blocks      Hooked block types.
 * @param string $relative_position  before|after.
 * @param string $anchor_block_type  Anchor block name.
 * @param mixed  $context            Template context.
 * @return array
 */
function fairflock_unhook_customer_account_after_nav( $hooked_blocks, $relative_position, $anchor_block_type, $context ) {
	if ( 'core/navigation' !== $anchor_block_type || 'after' !== $relative_position ) {
		return $hooked_blocks;
	}

	return array_values(
		array_filter(
			(array) $hooked_blocks,
			static function ( $block_name ) {
				return 'woocommerce/customer-account' !== $block_name;
			}
		)
	);
}
add_filter( 'hooked_block_types', 'fairflock_unhook_customer_account_after_nav', 20, 4 );

/**
 * Registers Woo-dependent block patterns once WooCommerce is active.
 *
 * @since 0.7.0
 * @since 0.9.56 Collection slider pattern (products of one category).
 *
 * @return void
 */
function fairflock_register_woocommerce_patterns() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	register_block_pattern(
		'fairflock/product-collection',
		array(
			'title'       => __( 'Produkt-Collection', 'fairflock' ),
			'description' => __( 'Produkt-Slider mit WooCommerce-Produkten. Im Editor den Block „Produktsammlung“ wählen → unter Filter Kategorie/Kollektion setzen oder alle Produkte lassen.', 'fairflock' ),
			'categories'  => array( 'fairflock', 'fairflock-shop', 'woocommerce' ),
			'keywords'    => array(
				__( 'Produkte', 'fairflock' ),
				__( 'Kategorie', 'fairflock' ),
				__( 'Kollektion', 'fairflock' ),
				__( 'Slider', 'fairflock' ),
			),
			'content'     => fairflock_get_product_collection_pattern_content(),
		)
	);

	register_block_pattern(
		'fairflock/collections-slider',
		array(
			'title'       => __( 'Kollektionen Slider', 'fairflock' ),
			'description' => __( 'Produkt-Slider für eine Kollektion (Produktkategorie). Kategorie im Editor: Produktsammlung auswählen → Filter → Kategorien.', 'fairflock' ),
			'categories'  => array( 'fairflock', 'fairflock-shop', 'woocommerce' ),
			'keywords'    => array(
				__( 'Kollektion', 'fairflock' ),
				__( 'Slider', 'fairflock' ),
				__( 'Kategorie', 'fairflock' ),
				'collection',
			),
			'content'     => fairflock_get_collections_slider_pattern_content(),
		)
	);

	register_block_pattern(
		'fairflock/bestsellers-slider',
		array(
			'title'       => __( 'Bestseller Slider', 'fairflock' ),
			'description' => __( 'Produkt-Slider für die Kategorie „Bestseller“ (Slug bestseller / bestsellers).', 'fairflock' ),
			'categories'  => array( 'fairflock', 'fairflock-shop', 'woocommerce' ),
			'keywords'    => array(
				__( 'Bestseller', 'fairflock' ),
				__( 'Slider', 'fairflock' ),
				__( 'Kategorie', 'fairflock' ),
			),
			'content'     => fairflock_get_bestsellers_slider_pattern_content(),
		)
	);
}
add_action( 'init', 'fairflock_register_woocommerce_patterns', 12 );

/**
 * Picks a product category for collection-slider defaults.
 *
 * Prefers a non-default category; falls back to the shop default category.
 *
 * @since 0.9.56
 *
 * @return WP_Term|null
 */
function fairflock_get_default_collection_term() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return null;
	}

	$exclude = array();
	$default = (int) get_option( 'default_product_cat', 0 );
	if ( $default > 0 ) {
		$exclude[] = $default;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => 1,
			'exclude'    => $exclude,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ( empty( $terms ) || is_wp_error( $terms ) ) && $default > 0 ) {
		$term = get_term( $default, 'product_cat' );
		return ( $term instanceof WP_Term ) ? $term : null;
	}

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return null;
	}

	return $terms[0];
}

/**
 * Shared product-slider section markup (shop-wide or one collection).
 *
 * @since 0.9.56
 *
 * @param array<string, mixed> $args {
 *     @type string               $name       Block metadata name.
 *     @type string               $extra_class Extra section class.
 *     @type string               $heading    Section heading.
 *     @type string               $lead       Section lead.
 *     @type string               $more_url   CTA URL.
 *     @type string               $more_label CTA label (without arrow).
 *     @type int                  $query_id   Product Collection queryId.
 *     @type array<string, mixed> $tax_query  taxQuery attribute (e.g. product_cat => ids).
 *     @type string               $collection Woo collection name (optional).
 * }
 * @return string
 */
function fairflock_get_product_slider_pattern_markup( array $args ) {
	$name        = isset( $args['name'] ) ? (string) $args['name'] : __( 'Produkt-Collection', 'fairflock' );
	$extra_class = isset( $args['extra_class'] ) ? trim( (string) $args['extra_class'] ) : '';
	$heading     = isset( $args['heading'] ) ? (string) $args['heading'] : esc_html__( 'Hier stehen deine Produkte', 'fairflock' );
	$lead        = isset( $args['lead'] ) ? (string) $args['lead'] : esc_html__( 'Aktuelle Stücke aus dem Shop — bereit zum Entdecken.', 'fairflock' );
	$more_url    = isset( $args['more_url'] ) ? (string) $args['more_url'] : esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
	$more_label  = isset( $args['more_label'] ) ? (string) $args['more_label'] : esc_html__( 'Alle Artikel →', 'fairflock' );
	$query_id    = isset( $args['query_id'] ) ? (int) $args['query_id'] : 21;
	$tax_query   = isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ? $args['tax_query'] : array();
	$collection  = isset( $args['collection'] ) ? (string) $args['collection'] : '';

	$section_class = trim( 'ff-pattern-products ff-pattern-products--woo ' . $extra_class );
	$prev_label    = esc_attr__( 'Zurück', 'fairflock' );
	$next_label    = esc_attr__( 'Weiter', 'fairflock' );
	$name_attr     = esc_attr( $name );
	$heading_html  = esc_html( $heading );
	$lead_html     = esc_html( $lead );
	$more_url_attr = esc_url( $more_url );
	$more_label_esc = esc_html( $more_label );

	$query_for_json = array(
		'perPage'                  => 8,
		'pages'                    => 0,
		'offset'                   => 0,
		'postType'                 => 'product',
		'order'                    => 'desc',
		'orderBy'                  => 'date',
		'search'                   => '',
		'exclude'                  => array(),
		'inherit'                  => false,
		'taxQuery'                 => $tax_query ? $tax_query : new stdClass(),
		'isProductCollectionBlock' => true,
		'woocommerceStockStatus'   => array( 'instock', 'onbackorder' ),
	);

	$collection_attrs = array(
		'queryId'              => $query_id,
		'query'                => $query_for_json,
		'displayLayout'        => array(
			'type'          => 'flex',
			'columns'       => 4,
			'shrinkColumns' => true,
		),
		'queryContextIncludes' => array( 'collection' ),
		'className'            => 'ff-shop__collection ff-prod-slider__track',
	);

	if ( $collection !== '' ) {
		$collection_attrs['collection'] = $collection;
	}

	$collection_json = wp_json_encode( $collection_attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	if ( ! is_string( $collection_json ) ) {
		$collection_json = '{}';
	}

	return <<<HTML
<!-- wp:group {"metadata":{"name":"{$name_attr}"},"align":"full","className":"{$section_class}","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull {$section_class}" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-pattern-products__shell","style":{"spacing":{"padding":{"left":"0","right":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-pattern-products__shell">
		<!-- wp:group {"className":"ff-pattern-section-head ff-pattern-products__head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
		<div class="wp-block-group ff-pattern-section-head ff-pattern-products__head">
			<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"constrained","contentSize":"34rem"}} -->
			<div class="wp-block-group">
				<!-- wp:heading -->
				<h2 class="wp-block-heading">{$heading_html}</h2>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"textColor":"muted"} -->
				<p class="has-muted-color has-text-color">{$lead_html}</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"ff-prod-slider__actions","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right","verticalAlignment":"center"}} -->
			<div class="wp-block-group ff-prod-slider__actions">
				<!-- wp:buttons {"metadata":{"name":"Slider-Pfeile"},"className":"ff-prod-slider__nav","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-buttons ff-prod-slider__nav">
					<!-- wp:button {"metadata":{"name":"Zurück"},"className":"ff-prod-slider__btn ff-card-slider__btn ff-card-slider__btn--prev is-style-outline"} -->
					<div class="wp-block-button ff-prod-slider__btn ff-card-slider__btn ff-card-slider__btn--prev is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-prod-prev" aria-label="{$prev_label}">←</a></div>
					<!-- /wp:button -->

					<!-- wp:button {"metadata":{"name":"Weiter"},"className":"ff-prod-slider__btn ff-card-slider__btn ff-card-slider__btn--next is-style-outline"} -->
					<div class="wp-block-button ff-prod-slider__btn ff-card-slider__btn ff-card-slider__btn--next is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-prod-next" aria-label="{$next_label}">→</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

				<!-- wp:paragraph {"className":"ff-pattern-link-more","style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->
				<p class="ff-pattern-link-more" style="font-style:normal;font-weight:700"><a href="{$more_url_attr}">{$more_label_esc}</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ff-prod-slider","layout":{"type":"default"}} -->
		<div class="wp-block-group ff-prod-slider">
			<!-- wp:woocommerce/product-collection {$collection_json} -->
			<div class="wp-block-woocommerce-product-collection ff-shop__collection ff-prod-slider__track">
				<!-- wp:woocommerce/product-template -->
					<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true,"className":"ff-prod-card__media"} /-->
					<!-- wp:group {"className":"ff-prod-card__body","style":{"spacing":{"blockGap":"6px","padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"default"}} -->
					<div class="wp-block-group ff-prod-card__body" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)">
						<!-- wp:post-title {"textAlign":"left","level":3,"isLink":true,"fontSize":"medium","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
						<!-- wp:woocommerce/product-price {"textAlign":"left","isDescendentOfQueryLoop":true,"fontSize":"medium","className":"ff-prod-card__price"} /-->
					</div>
					<!-- /wp:group -->
				<!-- /wp:woocommerce/product-template -->
			</div>
			<!-- /wp:woocommerce/product-collection -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
HTML;
}

/**
 * Markup for the Woo product-collection pattern.
 *
 * Homepage teaser mirrors the styleguide products section: section head,
 * prev/next controls, horizontal product track, shop link.
 *
 * @since 0.7.0
 * @since 0.9.4 Styleguide slider layout + shop CTA.
 * @since 0.9.56 Shared slider markup helper.
 *
 * @return string
 */
function fairflock_get_product_collection_pattern_content() {
	return fairflock_get_product_slider_pattern_markup(
		array(
			'name'       => __( 'Produkt-Collection', 'fairflock' ),
			'heading'    => __( 'Hier stehen deine Produkte', 'fairflock' ),
			'lead'       => __( 'Aktuelle Stücke aus dem Shop — bereit zum Entdecken.', 'fairflock' ),
			'more_url'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
			'more_label' => __( 'Alle Artikel →', 'fairflock' ),
			'query_id'   => 21,
			'tax_query'  => array(),
		)
	);
}

/**
 * Markup for the “Kollektionen Slider” pattern (one product category).
 *
 * @since 0.9.56
 *
 * @return string
 */
function fairflock_get_collections_slider_pattern_content() {
	$term     = fairflock_get_default_collection_term();
	$tax_query = array();
	$more_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$heading   = __( 'Kollektion', 'fairflock' );
	$lead      = __( 'Produkte aus einer Kollektion — Kategorie im Editor unter Filter wählen.', 'fairflock' );
	$more_label = __( 'Zur Kollektion', 'fairflock' );

	if ( $term instanceof WP_Term ) {
		$tax_query[ 'product_cat' ] = array( (int) $term->term_id );
		$link                       = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			$more_url = $link;
		}
		$heading = sprintf(
			/* translators: %s: product category name */
			__( 'Kollektion: %s', 'fairflock' ),
			$term->name
		);
		$lead = __( 'Nur Produkte aus dieser Kollektion — im Editor die Kategorie unter „Filter“ ändern.', 'fairflock' );
	}

	return fairflock_get_product_slider_pattern_markup(
		array(
			'name'        => __( 'Kollektionen Slider', 'fairflock' ),
			'extra_class' => 'ff-pattern-products--collection',
			'heading'     => $heading,
			'lead'        => $lead,
			'more_url'    => $more_url,
			'more_label'  => $more_label,
			'query_id'    => 42,
			'tax_query'   => $tax_query,
			'collection'  => 'woocommerce/product-collection/by-category',
		)
	);
}

/**
 * Finds the Bestseller product category (slug or name).
 *
 * @since 0.9.252
 *
 * @return WP_Term|null
 */
function fairflock_get_bestsellers_term() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return null;
	}

	$slugs = array( 'bestseller', 'bestsellers', 'best-seller', 'best-sellers' );
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => 50,
		)
	);

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return null;
	}

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$name = function_exists( 'mb_strtolower' ) ? mb_strtolower( $term->name ) : strtolower( $term->name );
		if ( $name === 'bestseller' || $name === 'bestsellers' ) {
			return $term;
		}
	}

	return null;
}

/**
 * Markup for the Bestseller product slider (Kollektionen-Seite).
 *
 * @since 0.9.252
 *
 * @return string
 */
function fairflock_get_bestsellers_slider_pattern_content() {
	$term      = fairflock_get_bestsellers_term();
	$tax_query = array();
	$more_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$heading   = __( 'Bestseller', 'fairflock' );
	$lead      = __( 'Die beliebtesten Stücke — Kategorie „Bestseller“ im Shop anlegen bzw. zuweisen.', 'fairflock' );
	$more_label = __( 'Alle Bestseller →', 'fairflock' );

	if ( $term instanceof WP_Term ) {
		$tax_query['product_cat'] = array( (int) $term->term_id );
		$link                     = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			$more_url = $link;
		}
		$heading = $term->name;
		$lead    = __( 'Aktuelle Highlights aus der Bestseller-Kategorie.', 'fairflock' );
	}

	return fairflock_get_product_slider_pattern_markup(
		array(
			'name'        => __( 'Bestseller', 'fairflock' ),
			'extra_class' => 'ff-pattern-products--bestsellers',
			'heading'     => $heading,
			'lead'        => $lead,
			'more_url'    => $more_url,
			'more_label'  => $more_label,
			'query_id'    => 52,
			'tax_query'   => $tax_query,
			'collection'  => 'woocommerce/product-collection/by-category',
		)
	);
}

/**
 * One-time: live shop catalog + German shop page title for Alle Produkte.
 *
 * WooCommerce defaults to Coming Soon (store-only), which hides the catalog.
 * FairFlock shops need the Product Catalog template visible after install.
 *
 * @since 0.9.72
 *
 * @return void
 */
function fairflock_maybe_setup_shop_catalog() {
	if ( get_option( 'fairflock_shop_catalog_ready' ) === '1' ) {
		return;
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( get_option( 'woocommerce_coming_soon' ) === 'yes' ) {
		update_option( 'woocommerce_coming_soon', 'no' );
	}

	$shop_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
	if ( $shop_id > 0 ) {
		$shop = get_post( $shop_id );
		if ( $shop instanceof WP_Post ) {
			$title = trim( (string) $shop->post_title );
			if ( $title === '' || strcasecmp( $title, 'Shop' ) === 0 ) {
				wp_update_post(
					array(
						'ID'         => $shop_id,
						'post_title' => __( 'Alle Produkte', 'fairflock' ),
					)
				);
			}
		}
	}

	update_option( 'fairflock_shop_catalog_ready', '1' );
}
add_action( 'init', 'fairflock_maybe_setup_shop_catalog', 30 );

/**
 * Price filter block markup for the shop sidebar.
 *
 * @since 0.9.102
 *
 * @return string
 */
function fairflock_get_shop_price_filter_markup() {
	return '				<!-- wp:woocommerce/product-filter-price {"className":"ff-shop__filter-group"} -->
				<div class="wp-block-woocommerce-product-filter-price ff-shop__filter-group">
					<!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"0","bottom":"0.625rem"}}}} -->
					<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">Preis</h3>
					<!-- /wp:heading -->

					<!-- wp:woocommerce/product-filter-price-slider {"lock":{"remove":true}} -->
					<div class="wp-block-woocommerce-product-filter-price-slider wc-block-product-filter-price-slider"></div>
					<!-- /wp:woocommerce/product-filter-price-slider -->
				</div>
				<!-- /wp:woocommerce/product-filter-price -->
';
}

/**
 * One-time: restore the price slider in customized shop templates.
 *
 * @since 0.9.102
 *
 * @return void
 */
function fairflock_maybe_restore_shop_price_filter() {
	if ( get_option( 'fairflock_shop_price_filter' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$block = fairflock_get_shop_price_filter_markup();
	$templates = get_posts(
		array(
			'post_type'      => 'wp_template',
			'post_status'    => array( 'publish', 'draft', 'auto-draft' ),
			'posts_per_page' => 40,
			'no_found_rows'  => true,
		)
	);

	foreach ( $templates as $template ) {
		if ( ! $template instanceof WP_Post || ! is_string( $template->post_content ) ) {
			continue;
		}

		if ( ! str_contains( $template->post_content, 'ff-shop__filters' ) ) {
			continue;
		}

		if ( str_contains( $template->post_content, 'product-filter-price' ) ) {
			continue;
		}

		$updated = preg_replace(
			'/(<!--\s*wp:woocommerce\/product-filter-status)/',
			$block . "\n\t\t\t\t$1",
			$template->post_content,
			1
		);

		if ( ! is_string( $updated ) || $updated === $template->post_content ) {
			$updated = preg_replace(
				'/(<!--\s*\/wp:woocommerce\/product-filter-taxonomy\s*-->)/',
				"$1\n\n" . $block,
				$template->post_content,
				1
			);
		}

		if ( ! is_string( $updated ) || $updated === $template->post_content ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => (int) $template->ID,
				'post_content' => $updated,
			),
			true
		);
	}

	update_option( 'fairflock_shop_price_filter', '1' );
}
add_action( 'init', 'fairflock_maybe_restore_shop_price_filter', 36 );

/**
 * Suppress the top “added to cart” banner — feedback is on the button + drawer.
 *
 * @since 0.9.228
 *
 * @param string $message Notice HTML.
 * @return string
 */
function fairflock_suppress_add_to_cart_banner( $message ) {
	return '';
}
add_filter( 'wc_add_to_cart_message_html', 'fairflock_suppress_add_to_cart_banner' );
add_filter( 'woocommerce_add_to_cart_message_html', 'fairflock_suppress_add_to_cart_banner' );

/**
 * Remember a successful add so the next page load can open the cart drawer.
 *
 * Skips AJAX/REST (drawer opens immediately via JS in those flows).
 *
 * @since 0.9.228
 *
 * @return void
 */
function fairflock_flag_just_added_to_cart() {
	if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}
	WC()->session->set( 'ff_atc_feedback', 1 );
}
add_action( 'woocommerce_add_to_cart', 'fairflock_flag_just_added_to_cart', 20 );

/**
 * Expose just-added flag to the cart drawer script.
 *
 * @since 0.9.228
 *
 * @return void
 */
function fairflock_expose_just_added_flag() {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}
	if ( ! WC()->session->get( 'ff_atc_feedback' ) ) {
		return;
	}
	WC()->session->set( 'ff_atc_feedback', null );

	if ( wp_script_is( 'fairflock-cart-drawer', 'enqueued' ) ) {
		wp_add_inline_script( 'fairflock-cart-drawer', 'window.fairflockJustAddedToCart=true;', 'before' );
	}
}
add_action( 'wp_enqueue_scripts', 'fairflock_expose_just_added_flag', 25 );

/**
 * Products per row chosen in the shop toolbar (2–5).
 *
 * @since 0.9.269
 *
 * @return int
 */
function fairflock_shop_columns_count() {
	$n = 3;
	if ( isset( $_COOKIE['ff_shop_cols'] ) ) {
		$raw = absint( wp_unslash( $_COOKIE['ff_shop_cols'] ) );
		if ( $raw >= 2 && $raw <= 5 ) {
			$n = $raw;
		}
	}
	return $n;
}

/**
 * Applies the saved column count before the grid paints.
 *
 * @since 0.9.269
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fairflock_shop_columns_body_class( $classes ) {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return $classes;
	}

	$listing = ( function_exists( 'is_shop' ) && is_shop() )
		|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
		|| ( function_exists( 'is_search' ) && is_search() );

	if ( ! $listing ) {
		return $classes;
	}

	$classes[] = 'is-cols-' . fairflock_shop_columns_count();
	return $classes;
}
add_filter( 'body_class', 'fairflock_shop_columns_body_class' );

/**
 * Column picker immediately left of the catalog sort control.
 *
 * @since 0.9.269
 *
 * @param string $content Rendered block.
 * @param array  $block   Block data.
 * @return string
 */
function fairflock_shop_columns_control( $content, $block ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $content;
	}
	if ( ( $block['blockName'] ?? '' ) !== 'woocommerce/catalog-sorting' ) {
		return $content;
	}

	$current = fairflock_shop_columns_count();
	$options = '';
	foreach ( array( 2, 3, 4, 5 ) as $cols ) {
		$options .= sprintf(
			'<button type="button" class="ff-shop-cols__option" role="menuitemradio" data-cols="%1$d" aria-checked="%2$s">%1$d Spalten</button>',
			$cols,
			$cols === $current ? 'true' : 'false'
		);
	}

	$icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="4.5" height="16" rx="1" fill="currentColor"/><rect x="9.75" y="4" width="4.5" height="16" rx="1" fill="currentColor"/><rect x="16.5" y="4" width="4.5" height="16" rx="1" fill="currentColor"/></svg>';

	$control  = '<div class="ff-shop-cols">';
	$control .= '<button type="button" class="ff-shop-cols__btn" aria-expanded="false" aria-haspopup="menu" aria-controls="ff-shop-cols-menu">';
	$control .= '<span class="ff-shop-cols__icon">' . $icon . '</span>';
	$control .= '<span class="ff-shop-cols__label">' . esc_html__( 'Ansicht', 'fairflock' ) . '</span>';
	$control .= '</button>';
	$control .= '<div class="ff-shop-cols__menu" id="ff-shop-cols-menu" role="menu" aria-label="' . esc_attr__( 'Produkte pro Zeile', 'fairflock' ) . '" hidden>';
	$control .= $options;
	$control .= '</div></div>';

	if ( ! has_action( 'wp_footer', 'fairflock_shop_columns_script' ) ) {
		add_action( 'wp_footer', 'fairflock_shop_columns_script' );
	}

	return '<div class="ff-shop__view">' . $control . $content . '</div>';
}
add_filter( 'render_block', 'fairflock_shop_columns_control', 10, 2 );

/**
 * Keeps the column choice for the next visit.
 *
 * @since 0.9.269
 *
 * @return void
 */
function fairflock_shop_columns_script() {
	?>
	<script>
	(function () {
		var roots = document.querySelectorAll(".ff-shop-cols");
		if (!roots.length) return;
		var classes = ["is-cols-2", "is-cols-3", "is-cols-4", "is-cols-5"];

		function apply(n) {
			var i;
			for (i = 0; i < classes.length; i++) {
				document.body.classList.remove(classes[i]);
			}
			document.body.classList.add("is-cols-" + n);
			document.cookie = "ff_shop_cols=" + n + ";path=/;max-age=31536000;SameSite=Lax";
			roots.forEach(function (root) {
				root.querySelectorAll(".ff-shop-cols__option").forEach(function (option) {
					option.setAttribute("aria-checked", option.getAttribute("data-cols") === String(n) ? "true" : "false");
				});
			});
		}

		function closeAll(except) {
			roots.forEach(function (root) {
				if (root === except) return;
				var btn = root.querySelector(".ff-shop-cols__btn");
				var menu = root.querySelector(".ff-shop-cols__menu");
				if (!btn || !menu) return;
				btn.setAttribute("aria-expanded", "false");
				menu.hidden = true;
			});
		}

		roots.forEach(function (root) {
			var btn = root.querySelector(".ff-shop-cols__btn");
			var menu = root.querySelector(".ff-shop-cols__menu");
			if (!btn || !menu) return;

			btn.addEventListener("click", function () {
				var open = btn.getAttribute("aria-expanded") === "true";
				closeAll(root);
				btn.setAttribute("aria-expanded", open ? "false" : "true");
				menu.hidden = open;
				if (!open) {
					var current = menu.querySelector('[aria-checked="true"]') || menu.querySelector(".ff-shop-cols__option");
					if (current) current.focus();
				}
			});

			menu.addEventListener("click", function (event) {
				var option = event.target.closest(".ff-shop-cols__option");
				if (!option) return;
				apply(option.getAttribute("data-cols"));
				btn.setAttribute("aria-expanded", "false");
				menu.hidden = true;
				btn.focus();
			});
		});

		document.addEventListener("click", function (event) {
			if (!event.target.closest(".ff-shop-cols")) closeAll();
		});

		document.addEventListener("keydown", function (event) {
			if (event.key === "Escape") closeAll();
		});

		var saved = document.cookie.match(/(?:^|; )ff_shop_cols=([2-5])/);
		if (saved) apply(saved[1]);
	})();
	</script>
	<?php
}

/**
 * Delivery sentence for a product card.
 *
 * @since 0.9.292
 *
 * @param WC_Product $product Product.
 * @return string Empty when the product names no delivery time.
 */
function fairflock_product_delivery_label( $product ) {
	if ( function_exists( 'fairflock_product_delivery_view' ) ) {
		$view = fairflock_product_delivery_view( $product );
		return is_array( $view ) ? $view['label'] : '';
	}

	return '';
}

/**
 * Truck icon plus the delivery sentence, for a product card.
 *
 * @since 0.9.292
 *
 * @param string $label Already translated label.
 * @return string
 */
function fairflock_product_delivery_markup( $label, $tooltip = '' ) {
	$icon = '<svg viewBox="0 0 24 24" width="18" height="16" aria-hidden="true"><path d="M2.5 7.5h11v7.5h-11z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><path d="M13.5 10.5h4.2L21 14v1.5h-7.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><circle cx="6.5" cy="17.2" r="1.5" fill="none" stroke="currentColor" stroke-width="1.75"/><circle cx="17" cy="17.2" r="1.5" fill="none" stroke="currentColor" stroke-width="1.75"/></svg>';
	$info = function_exists( 'fairflock_delivery_info_markup' ) ? fairflock_delivery_info_markup( $tooltip ) : '';

	return '<p class="ff-prod-card__delivery">' . $icon . '<span>' . esc_html( $label ) . '</span>' . $info . '</p>';
}

/**
 * Shows „Lieferung in X Tagen“ under the price on product cards.
 *
 * Injected on render so a customized collection in the Site Editor still
 * gets the line. The single-product price stays unchanged.
 *
 * @since 0.9.292
 *
 * @param string   $content  Rendered block.
 * @param array    $block    Block data.
 * @param WP_Block $instance Block instance, when WordPress passes it.
 * @return string
 */
function fairflock_render_product_card_delivery( $content, $block, $instance = null ) {
	if ( ( $block['blockName'] ?? '' ) !== 'woocommerce/product-price' ) {
		return $content;
	}
	if ( ! function_exists( 'wc_get_product' ) ) {
		return $content;
	}

	$attrs     = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$is_single = ! empty( $attrs['isDescendentOfSingleProductTemplate'] );
	$class     = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	$is_card   = ! empty( $attrs['isDescendentOfQueryLoop'] ) || false !== strpos( $class, 'ff-prod-card__price' );
	if ( $is_single || ! $is_card ) {
		return $content;
	}

	$product = null;
	if ( $instance instanceof WP_Block ) {
		$post_id = isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : 0;
		if ( $post_id > 0 ) {
			$product = wc_get_product( $post_id );
		}
	}
	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product();
	}
	if ( ! $product instanceof WC_Product ) {
		return $content;
	}

	$view = function_exists( 'fairflock_product_delivery_view' ) ? fairflock_product_delivery_view( $product ) : null;
	if ( ! is_array( $view ) || '' === $view['label'] ) {
		return $content;
	}

	return $content . fairflock_product_delivery_markup( $view['label'], $view['tooltip'] );
}
add_filter( 'render_block', 'fairflock_render_product_card_delivery', 12, 3 );
