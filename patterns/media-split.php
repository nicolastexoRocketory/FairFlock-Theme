<?php
/**
 * Title: Media &amp; Text
 * Slug: fairflock/media-split
 * Categories: fairflock
 * Description: Text + Medien-Frame 1:1 Styleguide (Video/Bild-Platzhalter).
 * Viewport Width: 1240
 */
?>
<!-- wp:group {"metadata":{"name":"Media Split"},"align":"full","className":"ff-pattern-media-split","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}},"blockGap":"0"},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-media-split" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-media-split","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-media-split">
		<!-- wp:group {"className":"ff-media-split__copy","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ff-media-split__copy">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Hier steht dein Video- oder Bild-Titel</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p>Kurzer Teaser für Marke und Haltung — später echtes Video oder Bild einsetzen.</p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"ff-media-split__cta","layout":{"type":"default"}} -->
			<div class="wp-block-group ff-media-split__cta">
				<!-- wp:paragraph -->
				<p><a class="ff-media-split__btn" href="#">Mehr erfahren →</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ff-media-split__media","layout":{"type":"default"}} -->
		<div class="wp-block-group ff-media-split__media">
			<!-- wp:group {"className":"ff-video-frame","layout":{"type":"default"}} -->
			<div class="wp-block-group ff-video-frame">
				<!-- wp:paragraph {"className":"ff-video-frame__label"} -->
				<p class="ff-video-frame__label">Medienplatzhalter</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
