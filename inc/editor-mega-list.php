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
 * Registers a Group variation “Mega-Liste”.
 *
 * @since 0.9.218
 *
 * @return void
 */
function fairflock_register_mega_list_variation() {
	if ( ! function_exists( 'register_block_variation' ) ) {
		return;
	}

	register_block_variation(
		'core/group',
		array(
			'name'        => 'fairflock-mega-list',
			'title'       => __( 'Mega-Liste', 'fairflock' ),
			'description' => __( 'Liste im Mega-Menü: manuell, alle Kategorien oder Produkte einer Kategorie.', 'fairflock' ),
			'category'    => 'theme',
			'icon'        => 'menu',
			'keywords'    => array( 'mega', 'liste', 'kategorie', 'produkte', 'menü' ),
			'attributes'  => array(
				'className' => 'ff-mega-list ff-mega-list--manual',
				'layout'    => array(
					'type' => 'default',
				),
				'metadata'  => array(
					'name' => __( 'Mega-Liste', 'fairflock' ),
				),
			),
			'innerBlocks' => array(
				array(
					'core/list',
					array(
						'className' => 'ff-mega-panel__links',
						'fontSize'  => 'small',
					),
					array(
						array(
							'core/list-item',
							array(
								'content' => '<a href="/shop/">' . __( 'Beispiel-Link', 'fairflock' ) . '</a>',
							),
						),
					),
				),
			),
			'scope'       => array( 'inserter', 'block', 'transform' ),
			'isActive'    => array( 'className' ),
		)
	);
}
add_action( 'init', 'fairflock_register_mega_list_variation' );

/**
 * Enqueues inspector controls for Mega-Liste groups.
 *
 * @since 0.9.218
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
		'fairflock-editor-mega-list',
		'fairflockMegaList',
		array(
			'i18n' => array(
				'panel'       => __( 'Mega-Liste', 'fairflock' ),
				'mode'        => __( 'Inhalt', 'fairflock' ),
				'manual'      => __( 'Manuell (eigene Links)', 'fairflock' ),
				'categories'  => __( 'Alle Kategorien (live)', 'fairflock' ),
				'products'    => __( 'Produkte einer Kategorie (live)', 'fairflock' ),
				'category'    => __( 'Kategorie', 'fairflock' ),
				'categoryHelp'=> __( 'Welche Produktkategorie angezeigt werden soll.', 'fairflock' ),
				'loading'     => __( 'Kategorien werden geladen…', 'fairflock' ),
				'hintManual'  => __( 'Links in der Liste unten selbst bearbeiten.', 'fairflock' ),
				'hintCats'    => __( 'Frontend zeigt alle Produktkategorien live. Die Liste im Editor dient nur als Platzhalter.', 'fairflock' ),
				'hintProds'   => __( 'Frontend zeigt Produkte der gewählten Kategorie live. Die Liste im Editor dient nur als Platzhalter.', 'fairflock' ),
				'noCategory'  => __( '— Kategorie wählen —', 'fairflock' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_mega_list_editor_assets' );
