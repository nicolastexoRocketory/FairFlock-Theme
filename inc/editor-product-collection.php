<?php
/**
 * Product Collection editor UX — lock FairFlock slideshow layout.
 *
 * @package FairFlock
 * @since 0.9.140
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues editor script/CSS that hide Layout controls on product sliders.
 *
 * @since 0.9.140
 *
 * @return void
 */
function fairflock_enqueue_product_collection_editor_assets() {
	$js  = FAIRFLOCK_DIR . '/assets/js/editor-product-collection.js';
	$css = FAIRFLOCK_DIR . '/assets/css/editor-product-collection.css';

	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'fairflock-editor-product-collection',
			FAIRFLOCK_URI . '/assets/js/editor-product-collection.js',
			array(
				'wp-blocks',
				'wp-data',
				'wp-dom-ready',
			),
			FAIRFLOCK_VERSION,
			true
		);
	}

	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'fairflock-editor-product-collection',
			FAIRFLOCK_URI . '/assets/css/editor-product-collection.css',
			array(),
			FAIRFLOCK_VERSION
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_product_collection_editor_assets' );
