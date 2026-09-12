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
