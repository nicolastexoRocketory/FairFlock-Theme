<?php
/**
 * Title: Kategorien Bento
 * Slug: fairflock/categories-bento
 * Categories: fairflock, fairflock-shop
 * Description: Bento-Raster (wiederholendes 5er-Mosaik, tall/wide).
 * Viewport Width: 1240
 */

$shop = esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );

$cards = array(
	array(
		'class' => 'ff-bento-card is-tall is-brand ff-bento-cols-1 ff-bento-rows-2',
		'title' => 'Hauptkategorie',
		'text'  => 'Dein stärkstes Sortiment — auf einen Blick.',
	),
	array(
		'class' => 'ff-bento-card is-wide ff-bento-card--1 ff-bento-cols-2 ff-bento-rows-1',
		'title' => 'Kategorie B',
		'text'  => 'Breit, sichtbar, einladend.',
	),
	array(
		'class' => 'ff-bento-card ff-bento-card--2 ff-bento-cols-1 ff-bento-rows-1',
		'title' => 'Kategorie C',
		'text'  => 'Kompakt und klar.',
	),
	array(
		'class' => 'ff-bento-card ff-bento-card--3 ff-bento-cols-1 ff-bento-rows-1',
		'title' => 'Kategorie D',
		'text'  => 'Für den zweiten Blick.',
	),
	array(
		'class' => 'ff-bento-card is-wide ff-bento-card--4 ff-bento-cols-2 ff-bento-rows-1',
		'title' => 'Kategorie E',
		'text'  => 'Abschluss mit Breite.',
	),
);
?>
<!-- wp:group {"metadata":{"name":"Kategorien Bento"},"align":"full","className":"ff-pattern-bento","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-bento" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-pattern-bento__shell","style":{"spacing":{"padding":{"left":"0","right":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-pattern-bento__shell">
		<!-- wp:group {"className":"ff-pattern-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
		<div class="wp-block-group ff-pattern-section-head">
			<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"constrained","contentSize":"34rem"}} -->
			<div class="wp-block-group">
				<!-- wp:heading -->
				<h2 class="wp-block-heading">Entdecke die Kategorien</h2>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"muted"} -->
				<p class="has-muted-color has-text-color">Vom Einstieg bis zum Detail — klar sortiert für schnelle Orientierung.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"ff-card-slider__actions","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right","verticalAlignment":"center"}} -->
			<div class="wp-block-group ff-card-slider__actions">
				<!-- wp:buttons {"metadata":{"name":"Slider-Pfeile"},"className":"ff-card-slider__nav","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-buttons ff-card-slider__nav">
					<!-- wp:button {"metadata":{"name":"Zurück"},"className":"ff-card-slider__btn ff-card-slider__btn--prev is-style-outline"} -->
					<div class="wp-block-button ff-card-slider__btn ff-card-slider__btn--prev is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-cards-prev">←</a></div>
					<!-- /wp:button -->

					<!-- wp:button {"metadata":{"name":"Weiter"},"className":"ff-card-slider__btn ff-card-slider__btn--next is-style-outline"} -->
					<div class="wp-block-button ff-card-slider__btn ff-card-slider__btn--next is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-cards-next">→</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

				<!-- wp:paragraph {"className":"ff-pattern-link-more","style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->
				<p class="ff-pattern-link-more" style="font-style:normal;font-weight:700"><a href="<?php echo $shop; ?>">Alle Produkte →</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ff-bento","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ff-bento">
<?php foreach ( $cards as $card ) : ?>
			<!-- wp:group {"className":"<?php echo esc_attr( $card['class'] ); ?>","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group <?php echo esc_attr( $card['class'] ); ?>">
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading"><?php echo esc_html( $card['title'] ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph -->
				<p><?php echo esc_html( $card['text'] ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"ff-bento-card__cta"} -->
				<p class="ff-bento-card__cta"><a href="<?php echo $shop; ?>">Shoppen →</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

<?php endforeach; ?>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
