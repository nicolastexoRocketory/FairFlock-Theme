<?php
/**
 * Bento editor UX — column/row span for cards in .ff-bento.
 *
 * @package FairFlock
 * @since 0.9.97
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues inspector controls for bento card spans.
 *
 * @since 0.9.97
 *
 * @return void
 */
function fairflock_enqueue_bento_editor_assets() {
	$path = FAIRFLOCK_DIR . '/assets/js/editor-bento.js';

	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'fairflock-editor-bento',
		FAIRFLOCK_URI . '/assets/js/editor-bento.js',
		array(
			'wp-block-editor',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-element',
			'wp-hooks',
			'wp-i18n',
		),
		FAIRFLOCK_VERSION,
		true
	);

	wp_localize_script(
		'fairflock-editor-bento',
		'fairflockBento',
		array(
			'i18n' => array(
				'panel' => __( 'Bento-Raster', 'fairflock' ),
				'cols'  => __( 'Spalten', 'fairflock' ),
				'rows'  => __( 'Zeilen', 'fairflock' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_bento_editor_assets' );
