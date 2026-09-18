<?php
/**
 * Bento editor UX — card spans + grid content source.
 *
 * @package FairFlock
 * @since 0.9.97
 * @since 0.9.237 Content source modes on .ff-bento (collections | products | posts).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues inspector controls for bento card spans and grid content.
 *
 * @since 0.9.97
 * @since 0.9.237 Content source panel + apiFetch for categories/posts.
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
			'wp-api-fetch',
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
				'panel'            => __( 'Bento-Raster', 'fairflock' ),
				'cols'             => __( 'Spalten', 'fairflock' ),
				'rows'             => __( 'Zeilen', 'fairflock' ),
				'contentPanel'     => __( 'Bento-Inhalt', 'fairflock' ),
				'mode'             => __( 'Inhalt', 'fairflock' ),
				'manual'           => __( 'Manuell (eigene Karten)', 'fairflock' ),
				'collections'      => __( 'Kollektionen (live)', 'fairflock' ),
				'products'         => __( 'Produkte einer Kategorie (live)', 'fairflock' ),
				'postsMode'        => __( 'Seiten / Beiträge (live)', 'fairflock' ),
				'category'         => __( 'Kategorie', 'fairflock' ),
				'categoryHelp'     => __( 'Welche Produktkategorie angezeigt werden soll.', 'fairflock' ),
				'posts'            => __( 'Seiten / Beiträge', 'fairflock' ),
				'loadingCats'      => __( 'Kategorien werden geladen…', 'fairflock' ),
				'loadingPosts'     => __( 'Seiten und Beiträge werden geladen…', 'fairflock' ),
				'noCategory'       => __( '— Kategorie wählen —', 'fairflock' ),
				'hintManual'       => __( 'Karten im Raster selbst bearbeiten (Titel, Text, Link).', 'fairflock' ),
				'hintCollections'  => __( 'Frontend zeigt alle Produktkategorien live. Die Karten im Editor dienen nur als Platzhalter.', 'fairflock' ),
				'hintProducts'     => __( 'Frontend zeigt Produkte der gewählten Kategorie live. Die Karten im Editor dienen nur als Platzhalter.', 'fairflock' ),
				'hintPosts'        => __( 'Frontend zeigt die gewählten Seiten/Beiträge live. Die Karten im Editor dienen nur als Platzhalter.', 'fairflock' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_bento_editor_assets' );
