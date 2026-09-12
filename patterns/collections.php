<?php
/**
 * Title: Kollektionen
 * Slug: fairflock/collections
 * Categories: fairflock, fairflock-shop
 * Description: Kollektions-Grid aus WooCommerce-Produktkategorien (eine Quelle für Startseite + überall).
 * Viewport Width: 1240
 */

echo function_exists( 'fairflock_get_collections_section_markup' )
	? fairflock_get_collections_section_markup(
		array(
			'show_more_link' => true,
		)
	)
	: '';
