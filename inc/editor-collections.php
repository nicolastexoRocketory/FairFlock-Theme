<?php
/**
 * Collections editor UX — exclude categories + titles-only mode.
 *
 * core/group.ff-pattern-collections: className tokens
 * ff-collections-exclude-{id}-{id}-… · ff-collections-titles · ff-collections-titles-blur
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
				'panel'          => __( 'Kollektionen', 'fairflock' ),
				'titlesOnly'     => __( 'Nur Titel (ohne Bilder)', 'fairflock' ),
				'titlesHelp'     => __( 'Karten zeigen nur den Kategorie-Namen, zentriert — ohne sichtbaren Link und Beschreibung.', 'fairflock' ),
				'titlesBlur'           => __( 'Weichgezeichnetes Hintergrundbild anzeigen', 'fairflock' ),
				'titlesBlurHelp'       => __( 'Kategoriebild unscharf hinter dem Titel, mit Overlay für Lesbarkeit.', 'fairflock' ),
				'titlesBlurNeedTitles' => __( 'Nur verfügbar, wenn „Nur Titel“ aktiv ist.', 'fairflock' ),
				'exclude'        => __( 'Kategorien ausschließen', 'fairflock' ),
				'loading'        => __( 'Kategorien werden geladen…', 'fairflock' ),
				'hint'           => __( 'Ausgeschlossene Kategorien erscheinen auf der Website nicht. Die Karten im Editor dienen als Platzhalter.', 'fairflock' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_collections_editor_assets' );
