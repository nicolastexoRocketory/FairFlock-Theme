<?php
/**
 * Title: Mehr von dieser Art
 * Slug: fairflock/related-products
 * Categories: fairflock, fairflock-shop
 * Inserter: false
 * Description: Produktvorschläge unter dem Produkt; Titel = Kategorie, Desktop 5 / Tablet 4 / Mobile 1,5 Spalten.
 */
?>
<!-- wp:group {"align":"wide","className":"ff-related-slider ff-pattern-products--woo","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide ff-related-slider ff-pattern-products--woo">
	<!-- wp:group {"className":"ff-pattern-section-head ff-related-slider__head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
	<div class="wp-block-group ff-pattern-section-head ff-related-slider__head">
		<!-- wp:heading -->
		<h2 class="wp-block-heading"><?php echo esc_html( function_exists( 'fairflock_related_products_heading' ) ? fairflock_related_products_heading() : __( 'Mehr von dieser Art', 'fairflock' ) ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:buttons {"className":"ff-prod-slider__nav","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-buttons ff-prod-slider__nav">
			<!-- wp:button {"className":"ff-prod-slider__btn ff-card-slider__btn ff-card-slider__btn--prev is-style-outline"} -->
			<div class="wp-block-button ff-prod-slider__btn ff-card-slider__btn ff-card-slider__btn--prev is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-prod-prev" aria-label="<?php echo esc_attr__( 'Zurück', 'fairflock' ); ?>"><?php echo fairflock_chevron_svg( array( 'dir' => 'left', 'size' => 16 ) ); ?></a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"ff-prod-slider__btn ff-card-slider__btn ff-card-slider__btn--next is-style-outline"} -->
			<div class="wp-block-button ff-prod-slider__btn ff-card-slider__btn ff-card-slider__btn--next is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-prod-next" aria-label="<?php echo esc_attr__( 'Weiter', 'fairflock' ); ?>"><?php echo fairflock_chevron_svg( array( 'dir' => 'right', 'size' => 16 ) ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"ff-prod-slider","layout":{"type":"default"}} -->
	<div class="wp-block-group ff-prod-slider">
		<!-- wp:woocommerce/product-collection {"queryId":0,"query":{"perPage":10,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"title","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false,"relatedBy":{"categories":true,"tags":false}},"tagName":"div","displayLayout":{"type":"flex","columns":5,"shrinkColumns":false},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/related","hideControls":["inherit"],"queryContextIncludes":["collection"],"className":"ff-shop__collection ff-prod-slider__track"} -->
		<div class="wp-block-woocommerce-product-collection ff-shop__collection ff-prod-slider__track">
			<!-- wp:woocommerce/product-template -->
				<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true,"className":"ff-prod-card__media"} /-->
				<!-- wp:group {"className":"ff-prod-card__body","style":{"spacing":{"blockGap":"6px","padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"default"}} -->
				<div class="wp-block-group ff-prod-card__body" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)">
					<!-- wp:post-title {"textAlign":"left","level":3,"isLink":true,"fontSize":"medium","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
					<!-- wp:woocommerce/product-price {"textAlign":"left","isDescendentOfQueryLoop":true,"fontSize":"medium","className":"ff-prod-card__price"} /-->
				</div>
				<!-- /wp:group -->
			<!-- /wp:woocommerce/product-template -->
		</div>
		<!-- /wp:woocommerce/product-collection -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
