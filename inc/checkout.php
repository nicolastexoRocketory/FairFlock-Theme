<?php
/**
 * Checkout: German CTA / terms copy, terms checkbox and buy-panel data.
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
 * German terms checkbox copy with AGB + Widerruf links (spaces around links required).
 *
 * @since 0.9.302
 * @since 0.9.470 Checkbox wording — not “Mit der Bestellung bestätigst du” without a box.
 *
 * @return string
 */
function fairflock_checkout_terms_text() {
	$terms_id = function_exists( 'wc_terms_and_conditions_page_id' ) ? (int) wc_terms_and_conditions_page_id() : 0;
	if ( $terms_id <= 0 ) {
		$agb      = get_page_by_path( 'agb' );
		$terms_id = $agb instanceof WP_Post ? (int) $agb->ID : 0;
	}

	$widerruf    = get_page_by_path( 'widerruf' );
	$widerruf_id = $widerruf instanceof WP_Post ? (int) $widerruf->ID : 0;

	$terms_link  = fairflock_checkout_linked_label( $terms_id, __( 'AGB', 'fairflock' ) );
	$refund_link = fairflock_checkout_linked_label( $widerruf_id, __( 'Widerrufsbelehrung', 'fairflock' ) );

	return sprintf(
		/* translators: 1: AGB link HTML, 2: cancellation policy link HTML */
		__( 'Ich habe die %1$s und die %2$s gelesen und stimme zu.', 'fairflock' ),
		$terms_link,
		$refund_link
	);
}

/**
 * German validation message when terms are not accepted.
 *
 * @since 0.9.470
 *
 * @return string
 */
function fairflock_checkout_terms_error() {
	return __( 'Bitte bestätige AGB und Widerrufsbelehrung, um die Bestellung abzuschließen.', 'fairflock' );
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
		// Woo default: checkbox=false → kein Kästchen, aber Validierung/Germanized blockiert.
		$parsed_block['attrs']['checkbox'] = true;
		$current = isset( $parsed_block['attrs']['text'] ) ? (string) $parsed_block['attrs']['text'] : '';
		if (
			fairflock_checkout_is_english_terms( $current )
			|| '' === trim( wp_strip_all_tags( $current ) )
			|| false !== stripos( $current, 'Mit deiner Bestellung bestätigst du' )
		) {
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
	if ( 'woocommerce' === $domain ) {
		$map = array(
			'Buy now'     => fairflock_checkout_cta_label(),
			'Buy Now'     => fairflock_checkout_cta_label(),
			'Place order' => fairflock_checkout_cta_label(),
			'Place Order' => fairflock_checkout_cta_label(),
		);
		if ( isset( $map[ $text ] ) ) {
			return $map[ $text ];
		}
	}

	/* Germanized / Woo: englische AGB-Pflichttexte → Deutsch (Platzhalter beibehalten) */
	if ( in_array( $domain, array( 'woocommerce-germanized', 'woocommerce-germanized-pro', 'woocommerce' ), true ) ) {
		if ( false !== stripos( $text, 'To complete the order you have to accept' ) ) {
			return fairflock_checkout_terms_error();
		}
		if ( false !== stripos( $text, 'With your order, you agree to have read' ) ) {
			return __( 'Ich habe die {term_link}AGB{/term_link} und die {revocation_link}Widerrufsbelehrung{/revocation_link} gelesen und stimme zu.', 'fairflock' );
		}
		if ( 'Please read and accept the terms and conditions.' === $text ) {
			return fairflock_checkout_terms_error();
		}
	}

	return $translation;
}
add_filter( 'gettext', 'fairflock_checkout_gettext', 20, 3 );

/**
 * Trust points shown below the buy button.
 *
 * @since 0.9.499
 *
 * @return array<int, array{icon: string, label: string}>
 */
function fairflock_checkout_trust_items() {
	$items = array(
		array(
			'icon'  => 'lock',
			'label' => __( 'SSL-verschlüsselt', 'fairflock' ),
		),
		array(
			'icon'  => 'box',
			'label' => __( 'Versand per DHL', 'fairflock' ),
		),
		array(
			'icon'  => 'store',
			'label' => __( 'Verkauf durch FairFlock GmbH', 'fairflock' ),
		),
	);

	/**
	 * Filters the checkout trust points.
	 *
	 * @since 0.9.499
	 *
	 * @param array $items List of `icon` (lock|box|store|return) + `label`.
	 */
	return (array) apply_filters( 'fairflock_checkout_trust_items', $items );
}

/**
 * Checkout front-end script: buy panel + German fallbacks.
 *
 * @since 0.9.302
 *
 * @return void
 */
function fairflock_checkout_enqueue_script() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
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
				'cta'        => fairflock_checkout_cta_label(),
				'terms'      => fairflock_checkout_terms_text(),
				'termsError' => fairflock_checkout_terms_error(),
				'trust'      => fairflock_checkout_trust_items(),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'fairflock_checkout_enqueue_script', 30 );

/**
 * One-time: enable Terms checkbox on the Checkout page block (DB content).
 *
 * React-Checkout liest die gespeicherten Block-Attribute — render_block_data allein reicht nicht.
 *
 * @since 0.9.470
 *
 * @return void
 */
function fairflock_checkout_maybe_enable_terms_checkbox() {
	if ( get_option( 'fairflock_checkout_terms_checkbox_v470' ) ) {
		return;
	}
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return;
	}

	$page_id = (int) wc_get_page_id( 'checkout' );
	if ( $page_id <= 0 ) {
		return;
	}

	$post = get_post( $page_id );
	if ( ! $post instanceof WP_Post || ! is_string( $post->post_content ) || '' === $post->post_content ) {
		update_option( 'fairflock_checkout_terms_checkbox_v470', 1, false );
		return;
	}

	$content = $post->post_content;
	$updated = $content;

	/* checkbox:false → true am Terms-Block */
	$updated = preg_replace(
		'/(<!--\s*wp:woocommerce\/checkout-terms-block\b[^>]*"checkbox"\s*:\s*)false/i',
		'${1}true',
		$updated,
		1
	);

	/* Kein checkbox-Attr → ergänzen */
	if ( is_string( $updated ) && $updated === $content ) {
		$updated = preg_replace(
			'/(<!--\s*wp:woocommerce\/checkout-terms-block)(\s*\{)/i',
			'${1}{"checkbox":true,',
			$updated,
			1
		);
		if ( is_string( $updated ) && false !== strpos( $updated, '{"checkbox":true,{' ) ) {
			$updated = str_replace( '{"checkbox":true,{', '{"checkbox":true,', $updated );
		}
		if ( is_string( $updated ) && false !== strpos( $updated, '{"checkbox":true,}' ) ) {
			$updated = str_replace( '{"checkbox":true,}', '{"checkbox":true}', $updated );
		}
	}

	if ( is_string( $updated ) && $updated !== $content ) {
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $updated,
			)
		);
	}

	update_option( 'fairflock_checkout_terms_checkbox_v470', 1, false );
}
add_action( 'init', 'fairflock_checkout_maybe_enable_terms_checkbox', 30 );
