<?php
/**
 * Title: Zitat / Fullbleed
 * Slug: fairflock/quote
 * Categories: fairflock
 * Description: Vollbild-Hintergrundsektion mit Text. Bild im Cover-Block ersetzen.
 * Viewport Width: 1240
 */
?>
<!-- wp:group {"metadata":{"name":"Vollbild 1"},"align":"full","className":"ff-pattern-quote","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-quote" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">
	<!-- wp:cover {"dimRatio":55,"overlayColor":"contrast","isUserOverlayColor":true,"minHeight":56,"minHeightUnit":"vh","contentPosition":"center center","isDark":true,"className":"ff-fullbleed ff-fullbleed--cover","layout":{"type":"constrained"}} -->
	<div class="wp-block-cover is-dark has-custom-content-position is-position-center-center ff-fullbleed ff-fullbleed--cover" style="min-height:56vh">
		<span aria-hidden="true" class="wp-block-cover__background has-contrast-background-color has-background-dim"></span>
		<div class="wp-block-cover__inner-container">
			<!-- wp:group {"className":"ff-fullbleed__inner","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"constrained","contentSize":"40rem"}} -->
			<div class="wp-block-group ff-fullbleed__inner">
				<!-- wp:heading {"textAlign":"center"} -->
				<h2 class="wp-block-heading has-text-align-center">Hier steht dein starker Moment</h2>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center"} -->
				<p class="has-text-align-center">Kurzer Claim mit Haltung — Hintergrundbild im Editor ersetzen.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
	</div>
	<!-- /wp:cover -->
</div>
<!-- /wp:group -->
