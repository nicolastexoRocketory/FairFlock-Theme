<?php
/**
 * Product quick-view popup (Store API).
 *
 * Markup is printed in the footer so Site-Editor header customizations
 * (colors etc.) are never overwritten by template-part syncs.
 *
 * @package FairFlock
 * @since 0.9.19
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints the product popup shell before scripts run.
 *
 * @since 0.9.19
 *
 * @return void
 */
function fairflock_print_product_modal() {
	if ( is_admin() ) {
		return;
	}
	?>
	<div class="ff-product-layer" id="ffProductLayer" aria-hidden="true">
		<div class="ff-product-backdrop" id="ffProductBackdrop"></div>
		<div
			class="ff-product-modal"
			id="ffProductModal"
			role="dialog"
			aria-modal="true"
			aria-labelledby="ffProductTitle"
			tabindex="-1"
		>
			<button type="button" class="ff-product-modal__close" id="ffProductClose" aria-label="Schließen">
				<svg viewBox="0 0 24 24" aria-hidden="true" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
			</button>
			<div class="ff-product-modal__body" id="ffProductBody">
				<p class="ff-product-modal__loading" id="ffProductLoading">Produkt wird geladen…</p>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'fairflock_print_product_modal', 5 );
