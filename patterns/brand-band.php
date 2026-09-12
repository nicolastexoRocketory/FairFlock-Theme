<?php
/**
 * Title: Brand-Band
 * Slug: fairflock/brand-band
 * Categories: fairflock
 * Description: Full-bleed Markenband 1:1 Styleguide (Copy + Medienfläche).
 * Viewport Width: 1240
 */

$shop = esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
?>
<!-- wp:group {"metadata":{"name":"Brand-Band"},"align":"full","className":"ff-pattern-band","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"margin":{"top":"0","bottom":"0"}},"blockGap":"0"},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-band" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:group {"className":"ff-band","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-band">
		<!-- wp:group {"className":"ff-band__copy","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ff-band__copy">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Hier steht deine Markenstory</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p>Kurzer, klarer Claim für Vertrauen und Haltung — Platzhalter bis die echte Story sitzt.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><a class="ff-band__btn" href="<?php echo $shop; ?>">Produkte entdecken →</a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:cover {"dimRatio":0,"isUserOverlayColor":true,"minHeight":400,"minHeightUnit":"px","contentPosition":"center center","isDark":true,"className":"ff-band__media","layout":{"type":"constrained"}} -->
		<div class="wp-block-cover is-dark ff-band__media" style="min-height:400px">
			<span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
			<div class="wp-block-cover__inner-container">
				<!-- wp:paragraph {"align":"center","placeholder":"Bild hier einfügen (Medien ersetzen)","textColor":"white","fontSize":"small"} -->
				<p class="has-text-align-center has-white-color has-text-color has-small-font-size"></p>
				<!-- /wp:paragraph -->
			</div>
		</div>
		<!-- /wp:cover -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
