<?php
/**
 * Collections editor UX — exclude product categories.
 *
 * core/group.ff-pattern-collections: className token
 * ff-collections-exclude-{id}-{id}-…
 *
 * @package FairFlock
 * @since 0.9.250
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues inspector controls for collections category exclusions.
 *
 * @since 0.9.250
 *
 * @return void
 */
function fairflock_enqueue_collections_editor_assets() {
	$path = FAIRFLOCK_DIR . '/assets/js/editor-collections.js';

	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'fairflock-editor-collections',
		FAIRFLOCK_URI . '/assets/js/editor-collections.js',
		array(
			'wp-api-fetch',
			'wp-block-editor',
			'wp-components',
			'wp-compose',
			'wp-element',
			'wp-hooks',
			'wp-i18n',
		),
		FAIRFLOCK_VERSION,
		true
	);

	wp_localize_script(
		'fairflock-editor-collections',
		'fairflockCollections',
		array(
			'i18n' => array(
				'panel'   => __( 'Kollektionen', 'fairflock' ),
				'exclude' => __( 'Kategorien ausschließen', 'fairflock' ),
				'loading' => __( 'Kategorien werden geladen…', 'fairflock' ),
				'hint'    => __( 'Ausgeschlossene Kategorien erscheinen auf der Website nicht. Die Karten im Editor dienen als Platzhalter.', 'fairflock' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_collections_editor_assets' );
