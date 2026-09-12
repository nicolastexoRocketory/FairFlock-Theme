<?php
/**
 * Title: Produkte Teaser
 * Slug: fairflock/products-teaser
 * Categories: fairflock, fairflock-shop
 * Description: Abschnitt mit vier Produkt-Platzhaltern (Fallback ohne WooCommerce).
 * Viewport Width: 1240
 * Inserter: yes
 */
?>
<!-- wp:group {"metadata":{"name":"Produkte Teaser"},"align":"full","className":"ff-pattern-products","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ff-pattern-products" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:group {"className":"ff-pattern-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group ff-pattern-section-head">
		<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Hier stehen deine Produkte</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"muted"} -->
			<p class="has-muted-color has-text-color">Lorem ipsum dolor sit amet — Platzhalter bis der Shop live ist.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"brand"} -->
		<p class="has-brand-color has-text-color" style="font-style:normal;font-weight:700"><a href="#">Alle Produkte →</a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"className":"ff-pattern-product-grid"} -->
	<div class="wp-block-columns ff-pattern-product-grid">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"ff-pattern-product-card","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group ff-pattern-product-card">
				<!-- wp:group {"className":"ff-pattern-product-card__media","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
				<div class="wp-block-group ff-pattern-product-card__media has-base-background-color has-background" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)"></div>
				<!-- /wp:group -->

				<!-- wp:heading {"level":3,"fontSize":"medium"} -->
				<h3 class="wp-block-heading has-medium-font-size">Beispielprodukt Alpha</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->
				<p style="font-style:normal;font-weight:700">24,90 €</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
				<p class="has-muted-color has-text-color has-small-font-size">inkl. MwSt. · Lieferzeit 3–5 Werktage</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"ff-pattern-product-card","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group ff-pattern-product-card">
				<!-- wp:group {"className":"ff-pattern-product-card__media","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
				<div class="wp-block-group ff-pattern-product-card__media has-base-background-color has-background" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)"></div>
				<!-- /wp:group -->

				<!-- wp:heading {"level":3,"fontSize":"medium"} -->
				<h3 class="wp-block-heading has-medium-font-size">Beispielprodukt Beta</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->
				<p style="font-style:normal;font-weight:700">39,00 €</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
				<p class="has-muted-color has-text-color has-small-font-size">inkl. MwSt. · Lieferzeit 5–8 Werktage</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"ff-pattern-product-card","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group ff-pattern-product-card">
				<!-- wp:group {"className":"ff-pattern-product-card__media","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
				<div class="wp-block-group ff-pattern-product-card__media has-base-background-color has-background" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)"></div>
				<!-- /wp:group -->

				<!-- wp:heading {"level":3,"fontSize":"medium"} -->
				<h3 class="wp-block-heading has-medium-font-size">Beispielprodukt Gamma</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->
				<p style="font-style:normal;font-weight:700">14,90 €</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
				<p class="has-muted-color has-text-color has-small-font-size">inkl. MwSt. · Sofort lieferbar</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"ff-pattern-product-card","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group ff-pattern-product-card">
				<!-- wp:group {"className":"ff-pattern-product-card__media","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
				<div class="wp-block-group ff-pattern-product-card__media has-base-background-color has-background" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)"></div>
				<!-- /wp:group -->

				<!-- wp:heading {"level":3,"fontSize":"medium"} -->
				<h3 class="wp-block-heading has-medium-font-size">Beispielprodukt Delta</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->
				<p style="font-style:normal;font-weight:700">29,90 €</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
				<p class="has-muted-color has-text-color has-small-font-size">inkl. MwSt. · Lieferzeit 3–5 Werktage</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
