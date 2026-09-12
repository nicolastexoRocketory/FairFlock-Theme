<?php
/**
 * Title: End-CTA
 * Slug: fairflock/cta-end
 * Categories: fairflock, featured
 * Description: Abschluss-CTA 1:1 Styleguide (end-box).
 * Viewport Width: 1240
 */

$shop = esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
?>
<!-- wp:group {"metadata":{"name":"End-CTA"},"align":"full","className":"ff-pattern-cta-end","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|70","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"margin":{"top":"0","bottom":"0"},"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-cta-end" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:group {"className":"ff-end-box","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group ff-end-box">
		<!-- wp:heading {"textAlign":"center"} -->
		<h2 class="wp-block-heading has-text-align-center">Bereit für den nächsten Schritt?</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center"} -->
		<p class="has-text-align-center">Fragen zu Größen, Versand oder Motiv? Schreib uns — wir helfen unkompliziert weiter.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"className":"ff-end-box__cta","layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons ff-end-box__cta">
			<!-- wp:button {"className":"ff-end-box__btn ff-end-box__btn--brand"} -->
			<div class="wp-block-button ff-end-box__btn ff-end-box__btn--brand"><a class="wp-block-button__link wp-element-button" href="<?php echo $shop; ?>">Zu den Produkten →</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"ff-end-box__btn ff-end-box__btn--ghost"} -->
			<div class="wp-block-button ff-end-box__btn ff-end-box__btn--ghost"><a class="wp-block-button__link wp-element-button" href="https://www.fairflock.de/kontakt/">Kontakt FairFlock</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
