<?php
/**
 * Shared product buy-column layout (popup + single product page).
 *
 * @package FairFlock
 * @since 0.9.305
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tax / shipping note under the price.
 *
 * @since 0.9.305
 *
 * @return string
 */
function fairflock_product_tax_note_markup() {
	return '<p class="ff-product-buy__tax">' . esc_html__( 'zzgl. Versandkosten | inkl. MwSt', 'fairflock' ) . '</p>';
}

/**
 * Stock fact chip.
 *
 * @since 0.9.305
 *
 * @param WC_Product $product Product.
 * @return string
 */
function fairflock_product_stock_fact_markup( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	if ( ! $product->is_in_stock() ) {
		$text = __( 'Nicht vorrätig', 'fairflock' );
		$cls  = ' is-out';
	} elseif ( $product->is_on_backorder() ) {
		$text = __( 'Nachlieferung', 'fairflock' );
		$cls  = ' is-backorder';
	} else {
		$text = __( 'Auf Lager', 'fairflock' );
		$cls  = ' is-in';
	}

	$icon = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M3.5 8 12 4l8.5 4v8L12 20l-8.5-4V8z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><path d="M12 12.2 20.5 8M12 12.2V20M12 12.2 3.5 8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/></svg>';

	return '<span class="ff-product-buy__fact' . esc_attr( $cls ) . '">' . $icon . esc_html( $text ) . '</span>';
}

/**
 * Delivery fact chip with optional info tip.
 *
 * @since 0.9.305
 *
 * @param WC_Product $product Product.
 * @return string
 */
function fairflock_product_delivery_fact_markup( $product ) {
	if ( ! function_exists( 'fairflock_product_delivery_view' ) ) {
		return '';
	}
	$view = fairflock_product_delivery_view( $product );
	if ( ! is_array( $view ) || '' === $view['label'] ) {
		return '';
	}

	$icon = '<svg viewBox="0 0 24 24" width="18" height="16" aria-hidden="true"><path d="M2.5 7.5h11v7.5h-11z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><path d="M13.5 10.5h4.2L21 14v1.5h-7.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><circle cx="6.5" cy="17.2" r="1.5" fill="none" stroke="currentColor" stroke-width="1.75"/><circle cx="17" cy="17.2" r="1.5" fill="none" stroke="currentColor" stroke-width="1.75"/></svg>';
	$info = function_exists( 'fairflock_delivery_info_markup' ) ? fairflock_delivery_info_markup( $view['tooltip'] ) : '';

	return '<span class="ff-product-buy__fact">' . $icon . esc_html( $view['label'] ) . $info . '</span>';
}

/**
 * Stock + delivery row under the price.
 *
 * @since 0.9.305
 *
 * @param WC_Product $product Product.
 * @return string
 */
function fairflock_product_facts_markup( $product ) {
	$stock    = fairflock_product_stock_fact_markup( $product );
	$delivery = fairflock_product_delivery_fact_markup( $product );
	if ( '' === $stock && '' === $delivery ) {
		return '';
	}

	$rule = ( $stock && $delivery )
		? '<span class="ff-product-buy__fact-rule" aria-hidden="true"></span>'
		: '';

	return '<p class="ff-product-buy__facts">' . $stock . $rule . $delivery . '</p>';
}

/**
 * One meta row for the Produktinfos tip.
 *
 * @since 0.9.305
 *
 * @param string $label Label.
 * @param string $value_html Safe HTML value.
 * @return string
 */
function fairflock_product_infos_row( $label, $value_html ) {
	$value_html = trim( (string) $value_html );
	if ( '' === $value_html ) {
		return '';
	}
	return '<div class="ff-product-modal__meta-row"><dt>' . esc_html( $label ) . '</dt><dd>' . $value_html . '</dd></div>';
}

/**
 * Produktinfos (i) hover block.
 *
 * @since 0.9.305
 *
 * @param WC_Product $product Product.
 * @return string
 */
function fairflock_product_infos_markup( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$rows = '';

	$sku = $product->get_sku();
	if ( $sku ) {
		$rows .= fairflock_product_infos_row( __( 'Art.-Nr.', 'fairflock' ), esc_html( $sku ) );
	}

	$tags = get_the_terms( $product->get_id(), 'product_tag' );
	if ( is_array( $tags ) && $tags ) {
		$names = array();
		foreach ( $tags as $tag ) {
			$names[] = $tag->name;
		}
		$rows .= fairflock_product_infos_row( __( 'Schlagwörter', 'fairflock' ), esc_html( implode( ', ', $names ) ) );
	}

	$cats = get_the_terms( $product->get_id(), 'product_cat' );
	if ( is_array( $cats ) && $cats ) {
		$links = array();
		foreach ( $cats as $cat ) {
			$url = get_term_link( $cat );
			if ( is_wp_error( $url ) ) {
				$links[] = esc_html( $cat->name );
			} else {
				$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $cat->name ) . '</a>';
			}
		}
		$rows .= fairflock_product_infos_row( __( 'Kategorie', 'fairflock' ), implode( ', ', $links ) );
	}

	$weight = $product->get_weight();
	if ( $weight !== '' && $weight !== null ) {
		$rows .= fairflock_product_infos_row(
			__( 'Gewicht', 'fairflock' ),
			esc_html( wc_format_weight( $weight ) )
		);
	}

	$dims = function_exists( 'wc_format_dimensions' ) ? wc_format_dimensions( $product->get_dimensions( false ) ) : '';
	if ( $dims && __( 'N/A', 'woocommerce' ) !== $dims ) {
		$rows .= fairflock_product_infos_row( __( 'Maße', 'fairflock' ), esc_html( $dims ) );
	}

	if ( '' === $rows ) {
		return '';
	}

	return '<div class="ff-product-infos">'
		. '<span class="ff-product-infos__label">' . esc_html__( 'Produktinfos', 'fairflock' ) . '</span>'
		. '<button type="button" class="ff-product-infos__btn" aria-label="' . esc_attr__( 'Produktinformationen anzeigen', 'fairflock' ) . '"><span aria-hidden="true">i</span></button>'
		. '<span class="ff-product-infos__tip" role="tooltip"><dl class="ff-product-infos__meta">' . $rows . '</dl></span>'
		. '</div>';
}

/**
 * Long description HTML for the buy column.
 *
 * @since 0.9.305
 *
 * @param WC_Product $product Product.
 * @return string
 */
function fairflock_product_description_markup( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}
	$html = $product->get_description();
	if ( '' === trim( wp_strip_all_tags( (string) $html ) ) ) {
		return '';
	}
	return '<div class="ff-product-buy__description">' . wc_format_content( $html ) . '</div>';
}

/**
 * Raw short description HTML when it differs from the long description.
 *
 * @since 0.9.324
 *
 * @param WC_Product $product Product.
 * @return string Empty when missing or identical to long description.
 */
function fairflock_product_excerpt_html( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}
	$html = $product->get_short_description();
	if ( '' === trim( wp_strip_all_tags( (string) $html ) ) ) {
		return '';
	}
	$long  = trim( preg_replace( '/\s+/', '', wp_strip_all_tags( (string) $product->get_description() ) ) );
	$short = trim( preg_replace( '/\s+/', '', wp_strip_all_tags( (string) $html ) ) );
	if ( $long && $long === $short ) {
		return '';
	}
	return (string) $html;
}

/**
 * Short description as a collapsed accordion item.
 *
 * @since 0.9.305
 * @since 0.9.315 Product page uses a dedicated card under the hero.
 * @since 0.9.324 Always accordion, collapsed by default.
 *
 * @param WC_Product $product Product.
 * @param string     $class   Extra class on the details element.
 * @return string
 */
function fairflock_product_excerpt_markup( $product, $class = 'ff-product-buy__excerpt' ) {
	$html = fairflock_product_excerpt_html( $product );
	if ( '' === $html ) {
		return '';
	}

	$extra = preg_replace( '/[^a-zA-Z0-9_\-\s]/', '', (string) $class );
	$extra = trim( preg_replace( '/\s+/', ' ', (string) $extra ) );

	$classes = 'ff-product-excerpt-acc';
	if ( '' !== $extra ) {
		$classes .= ' ' . $extra;
	}

	return '<details class="' . esc_attr( $classes ) . '">'
		. '<summary class="ff-product-excerpt-acc__summary">'
		. '<span class="ff-product-excerpt-acc__title">' . esc_html__( 'Weitere Informationen', 'fairflock' ) . '</span>'
		. '<span class="ff-product-excerpt-acc__icon" aria-hidden="true"></span>'
		. '</summary>'
		. '<div class="ff-product-excerpt-acc__clip">'
		. '<div class="ff-product-excerpt-acc__clip-inner">'
		. '<div class="ff-product-excerpt-acc__body">' . wc_format_content( $html ) . '</div>'
		. '</div></div>'
		. '</details>';
}

/**
 * Short-description card markup under the product hero.
 *
 * @since 0.9.315
 * @since 0.9.324 Accordion card, collapsed by default.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function fairflock_product_excerpt_card_markup( $product ) {
	$inner = fairflock_product_excerpt_markup( $product, 'ff-product__excerpt-card' );
	if ( '' === $inner ) {
		return '';
	}
	return $inner;
}

/**
 * Whether this rendered block sits in the single-product summary column.
 *
 * @since 0.9.305
 *
 * @param array $block Block data.
 * @return bool
 */
function fairflock_is_single_product_summary_block( $block ) {
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	if ( ! empty( $attrs['isDescendentOfQueryLoop'] ) ) {
		return false;
	}
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	if ( false !== strpos( $class, 'ff-prod-card__price' ) ) {
		return false;
	}
	return ! empty( $attrs['isDescendentOfSingleProductTemplate'] );
}

/**
 * After price: tax note + stock/delivery facts.
 *
 * @since 0.9.305
 *
 * @param string $content Rendered HTML.
 * @param array  $block   Block.
 * @return string
 */
function fairflock_product_layout_after_price( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'woocommerce/product-price' ) {
		return $content;
	}
	if ( ! fairflock_is_single_product_summary_block( $block ) ) {
		return $content;
	}
	if ( ! function_exists( 'wc_get_product' ) ) {
		return $content;
	}
	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof WC_Product ) {
		return $content;
	}
	if ( str_contains( $content, 'ff-product-buy__tax' ) ) {
		return $content;
	}

	$wrap  = '<div class="ff-product-buy__price-row">';
	$wrap .= $content;
	$wrap .= fairflock_product_tax_note_markup();
	$wrap .= '</div>';
	$wrap .= fairflock_product_facts_markup( $product );

	return $wrap;
}
add_filter( 'render_block', 'fairflock_product_layout_after_price', 14, 2 );

/**
 * After product title (info column): long description, table, product infos.
 *
 * @since 0.9.305
 * @since 0.9.314 Moved from before add-to-cart into the middle info column.
 * @since 0.9.315 Short description moves to a card under the hero.
 *
 * @param string $content Rendered HTML.
 * @param array  $block   Block.
 * @return string
 */
function fairflock_product_layout_after_title( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/post-title' ) {
		return $content;
	}
	if ( ! is_singular( 'product' ) || ! function_exists( 'wc_get_product' ) ) {
		return $content;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	if ( ! empty( $attrs['isLink'] ) || ! empty( $attrs['isDescendentOfQueryLoop'] ) ) {
		return $content;
	}
	$ns = isset( $attrs['__woocommerceNamespace'] ) ? (string) $attrs['__woocommerceNamespace'] : '';
	if ( $ns && false === strpos( $ns, 'product-title' ) && false === strpos( $ns, 'product-query/product-title' ) ) {
		return $content;
	}
	if ( str_contains( $content, 'ff-product-buy__copy' ) || str_contains( $content, 'ff-product-buy__description' ) || str_contains( $content, 'ff-product-infos' ) ) {
		return $content;
	}

	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof WC_Product ) {
		return $content;
	}

	$after  = '<div class="ff-product-buy__copy">';
	$after .= fairflock_product_description_markup( $product );
	if ( function_exists( 'fairflock_product_table_markup' ) ) {
		$after .= fairflock_product_table_markup( $product );
	}
	$after .= fairflock_product_infos_markup( $product );
	$after .= '</div>';

	return $content . $after;
}
add_filter( 'render_block', 'fairflock_product_layout_after_title', 12, 2 );

/**
 * After the product hero: short description in its own card.
 *
 * @since 0.9.315
 *
 * @param string $content Rendered HTML.
 * @param array  $block   Block.
 * @return string
 */
function fairflock_product_layout_after_hero( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/columns' ) {
		return $content;
	}
	if ( ! is_singular( 'product' ) || ! function_exists( 'wc_get_product' ) ) {
		return $content;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	if ( false === strpos( $class, 'ff-product__hero' ) ) {
		return $content;
	}
	if ( str_contains( $content, 'ff-product__excerpt-card' ) || str_contains( $content, 'ff-product-excerpt-acc' ) ) {
		return $content;
	}

	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof WC_Product ) {
		return $content;
	}

	return $content . fairflock_product_excerpt_card_markup( $product );
}
add_filter( 'render_block', 'fairflock_product_layout_after_hero', 20, 2 );

/**
 * Hide classic product-meta in the summary (replaced by Produktinfos tip).
 *
 * @since 0.9.305
 *
 * @param string $content Rendered HTML.
 * @param array  $block   Block.
 * @return string
 */
function fairflock_product_layout_hide_summary_meta( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'woocommerce/product-meta' ) {
		return $content;
	}
	if ( ! is_singular( 'product' ) ) {
		return $content;
	}
	return '';
}
add_filter( 'render_block', 'fairflock_product_layout_hide_summary_meta', 16, 2 );

/**
 * Related-products section heading on single product.
 *
 * @since 0.9.312
 *
 * @return string
 */
function fairflock_related_products_heading() {
	return __( 'Mehr von dieser Art', 'fairflock' );
}

/**
 * Classic Woo related.php heading.
 *
 * @since 0.9.312
 *
 * @param string $heading Default heading.
 * @return string
 */
function fairflock_related_products_heading_filter( $heading ) {
	unset( $heading );
	return fairflock_related_products_heading();
}
add_filter( 'woocommerce_product_related_products_heading', 'fairflock_related_products_heading_filter' );

/**
 * Woo gettext: “Related products” → category heading.
 *
 * @since 0.9.312
 *
 * @param string $translation Translated text.
 * @param string $text        Original text.
 * @param string $domain      Text domain.
 * @return string
 */
function fairflock_related_products_gettext( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain || 'Related products' !== $text ) {
		return $translation;
	}
	return fairflock_related_products_heading();
}
add_filter( 'gettext', 'fairflock_related_products_gettext', 20, 3 );

/**
 * Replace saved “Ähnliche Produkte” headings in related collections.
 *
 * @since 0.9.312
 *
 * @param string $content Rendered HTML.
 * @param array  $block   Block.
 * @return string
 */
function fairflock_related_products_heading_render( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/heading' || '' === $content ) {
		return $content;
	}
	if ( ! is_singular( 'product' ) ) {
		return $content;
	}

	$plain = trim( wp_strip_all_tags( $content ) );
	$known = array(
		'Related products',
		'Ähnliche Produkte',
		'Ähnliche produkte',
		'Produkte aus dieser Kategorie',
	);
	if ( ! in_array( $plain, $known, true ) ) {
		return $content;
	}

	$label = esc_html( fairflock_related_products_heading() );
	return (string) preg_replace(
		'/(>)([^<]*)(<\/h[1-6]>)/u',
		'$1' . $label . '$3',
		$content,
		1
	);
}
add_filter( 'render_block', 'fairflock_related_products_heading_render', 20, 2 );
