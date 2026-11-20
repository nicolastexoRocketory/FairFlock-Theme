<?php
/**
 * Checkout: positive green accents and German CTA / terms copy.
 *
 * @package FairFlock
 * @since 0.9.302
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a place-order label is empty or English.
 *
 * @since 0.9.302
 *
 * @param string $label Button label.
 * @return bool
 */
function fairflock_checkout_is_english_cta( $label ) {
	$n = strtolower( trim( wp_strip_all_tags( (string) $label ) ) );
	if ( '' === $n ) {
		return true;
	}
	return (bool) preg_match( '/^(buy\s*now|place\s*order)$/', $n );
}

/**
 * Whether terms copy looks English / default Woo wording.
 *
 * @since 0.9.302
 *
 * @param string $text Terms HTML/text.
 * @return bool
 */
function fairflock_checkout_is_english_terms( $text ) {
	$n = strtolower( wp_strip_all_tags( (string) $text ) );
	if ( '' === trim( $n ) ) {
		return true;
	}
	return (bool) preg_match(
		'/with your order|terms and conditions|cancellation policy|you must accept our|privacy policy/',
		$n
	);
}

/**
 * Whether a checkout step description looks English / default Woo wording.
 *
 * @since 0.9.425
 *
 * @param string $text Description text.
 * @return bool
 */
function fairflock_checkout_is_english_step_description( $text ) {
	$n = strtolower( wp_strip_all_tags( (string) $text ) );
	if ( '' === trim( $n ) ) {
		return false;
	}
	return (bool) preg_match(
		'/\b(enter your|select a|choose a|add a note|shipping options|contact details|billing address|payment options|we\'ll use this|we will use this|for order updates)\b/',
		$n
	);
}

/**
 * German CTA label.
 *
 * @since 0.9.302
 *
 * @return string
 */
function fairflock_checkout_cta_label() {
	return __( 'Jetzt kaufen', 'fairflock' );
}

/**
 * Linked label helper.
 *
 * @since 0.9.302
 *
 * @param int    $page_id Page ID.
 * @param string $fallback Plain fallback label.
 * @return string
 */
function fairflock_checkout_linked_label( $page_id, $fallback ) {
	$page_id = absint( $page_id );
	if ( $page_id > 0 ) {
		$url = get_permalink( $page_id );
		if ( $url ) {
			return '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $fallback ) . '</a>';
		}
	}
	return esc_html( $fallback );
}

/**
 * German terms checkbox / notice copy with AGB + Widerruf links.
 *
 * @since 0.9.302
 *
 * @return string
 */
function fairflock_checkout_terms_text() {
	$terms_id = function_exists( 'wc_terms_and_conditions_page_id' ) ? (int) wc_terms_and_conditions_page_id() : 0;
	if ( $terms_id <= 0 ) {
		$agb = get_page_by_path( 'agb' );
		$terms_id = $agb instanceof WP_Post ? (int) $agb->ID : 0;
	}

	$widerruf = get_page_by_path( 'widerruf' );
	$widerruf_id = $widerruf instanceof WP_Post ? (int) $widerruf->ID : 0;

	$terms_link    = fairflock_checkout_linked_label( $terms_id, __( 'AGB', 'fairflock' ) );
	$refund_link   = fairflock_checkout_linked_label( $widerruf_id, __( 'Widerrufsbelehrung', 'fairflock' ) );

	return sprintf(
		/* translators: 1: AGB link, 2: cancellation policy link */
		__( 'Mit deiner Bestellung bestätigst du, unsere %1$s und %2$s gelesen und verstanden zu haben.', 'fairflock' ),
		$terms_link,
		$refund_link
	);
}

/**
 * Force German labels on checkout action / terms blocks (also when pages store English attrs).
 *
 * @since 0.9.302
 *
 * @param array $parsed_block Block data.
 * @return array
 */
function fairflock_checkout_render_block_data( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) ) {
		return $parsed_block;
	}

	if ( 'woocommerce/checkout-actions-block' === $parsed_block['blockName'] ) {
		$current = isset( $parsed_block['attrs']['placeOrderButtonLabel'] )
			? (string) $parsed_block['attrs']['placeOrderButtonLabel']
			: '';
		if ( fairflock_checkout_is_english_cta( $current ) ) {
			$parsed_block['attrs']['placeOrderButtonLabel'] = fairflock_checkout_cta_label();
		}
	}

	if ( 'woocommerce/checkout-terms-block' === $parsed_block['blockName'] ) {
		// Woo default: checkbox=false → kein Kästchen. Immer verlangen.
		$parsed_block['attrs']['checkbox'] = true;
		$current = isset( $parsed_block['attrs']['text'] ) ? (string) $parsed_block['attrs']['text'] : '';
		if ( fairflock_checkout_is_english_terms( $current ) ) {
			$parsed_block['attrs']['text'] = fairflock_checkout_terms_text();
		}
	}

	// Englische Step-Beschreibungen ausblenden (Block-Attr leeren).
	$step_blocks = array(
		'woocommerce/checkout-contact-information-block',
		'woocommerce/checkout-shipping-address-block',
		'woocommerce/checkout-billing-address-block',
		'woocommerce/checkout-shipping-methods-block',
		'woocommerce/checkout-payment-block',
		'woocommerce/checkout-pickup-options-block',
		'woocommerce/checkout-additional-information-block',
	);
	if ( in_array( $parsed_block['blockName'], $step_blocks, true ) ) {
		$desc = isset( $parsed_block['attrs']['description'] ) ? (string) $parsed_block['attrs']['description'] : '';
		if ( '' !== $desc && fairflock_checkout_is_english_step_description( $desc ) ) {
			$parsed_block['attrs']['description'] = '';
		}
	}

	return $parsed_block;
}
add_filter( 'render_block_data', 'fairflock_checkout_render_block_data' );

/**
 * Translate leftover WooCommerce English CTAs.
 *
 * @since 0.9.302
 *
 * @param string $translation Translated text.
 * @param string $text        Original text.
 * @param string $domain      Text domain.
 * @return string
 */
function fairflock_checkout_gettext( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translation;
	}

	$map = array(
		'Buy now'     => fairflock_checkout_cta_label(),
		'Buy Now'     => fairflock_checkout_cta_label(),
		'Place order' => fairflock_checkout_cta_label(),
		'Place Order' => fairflock_checkout_cta_label(),
	);

	return isset( $map[ $text ] ) ? $map[ $text ] : $translation;
}
add_filter( 'gettext', 'fairflock_checkout_gettext', 20, 3 );

/**
 * Checkout front-end script for payment-method CTAs that stay English in JS.
 *
 * @since 0.9.302
 *
 * @return void
 */
function fairflock_checkout_enqueue_script() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	wp_enqueue_script(
		'fairflock-checkout',
		FAIRFLOCK_URI . '/assets/js/checkout.js',
		array(),
		FAIRFLOCK_VERSION,
		true
	);

	wp_add_inline_script(
		'fairflock-checkout',
		'window.fairflockCheckout=' . wp_json_encode(
			array(
				'cta'   => fairflock_checkout_cta_label(),
				'terms' => fairflock_checkout_terms_text(),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'fairflock_checkout_enqueue_script', 30 );
