<?php
/**
 * Mega-Liste editor UX — Modus für Listen im Mega-Menü.
 *
 * core/group.ff-mega-list: manuell | alle Kategorien | Produkte einer Kategorie.
 *
 * @package FairFlock
 * @since 0.9.218
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues Mega-Liste variation + inspector (JS registration required for core/group).
 *
 * @since 0.9.218
 * @since 0.9.220 Variation via JS — PHP core/group variations are overwritten in the editor.
 *
 * @return void
 */
function fairflock_enqueue_mega_list_editor_assets() {
	$path = FAIRFLOCK_DIR . '/assets/js/editor-mega-list.js';

	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'fairflock-editor-mega-list',
		FAIRFLOCK_URI . '/assets/js/editor-mega-list.js',
		array(
			'wp-api-fetch',
			'wp-block-editor',
			'wp-blocks',
			'wp-components',
			'wp-compose',
			'wp-dom-ready',
			'wp-element',
			'wp-hooks',
			'wp-i18n',
		),
		FAIRFLOCK_VERSION,
		true
	);

	wp_localize_script(
		'fairflock-editor-mega-list',
		'fairflockMegaList',
		array(
			'i18n' => array(
				'panel'         => __( 'Mega-Liste', 'fairflock' ),
				'variationDesc' => __( 'Liste im Mega-Menü: manuell, alle Kategorien oder Produkte einer Kategorie.', 'fairflock' ),
				'exampleLink'   => __( 'Beispiel-Link', 'fairflock' ),
				'mode'          => __( 'Inhalt', 'fairflock' ),
				'manual'        => __( 'Manuell (eigene Links)', 'fairflock' ),
				'categories'    => __( 'Alle Kategorien (live)', 'fairflock' ),
				'products'      => __( 'Produkte einer Kategorie (live)', 'fairflock' ),
				'category'      => __( 'Kategorie', 'fairflock' ),
				'categoryHelp'  => __( 'Welche Produktkategorie angezeigt werden soll.', 'fairflock' ),
				'loading'       => __( 'Kategorien werden geladen…', 'fairflock' ),
				'hintManual'    => __( 'Links in der Liste unten selbst bearbeiten.', 'fairflock' ),
				'hintCats'      => __( 'Frontend zeigt alle Produktkategorien live. Die Liste im Editor dient nur als Platzhalter.', 'fairflock' ),
				'hintProds'     => __( 'Frontend zeigt Produkte der gewählten Kategorie live. Die Liste im Editor dient nur als Platzhalter.', 'fairflock' ),
				'noCategory'    => __( '— Kategorie wählen —', 'fairflock' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_mega_list_editor_assets' );
