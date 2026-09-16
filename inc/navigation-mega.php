<?php
/**
 * Opt-in Mega menu style for core Navigation submenus (Site Editor).
 *
 * @package FairFlock
 * @since 0.9.202
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the “Mega” block style on navigation submenu items.
 *
 * @since 0.9.202
 *
 * @return void
 */
function fairflock_register_navigation_mega_styles() {
	$style = array(
		'name'  => 'ff-mega',
		'label' => __( 'Mega', 'fairflock' ),
	);

	register_block_style( 'core/navigation-submenu', $style );

	// Fallback: some editor builds expose the style on the link wrapper.
	register_block_style( 'core/navigation-link', $style );
}
add_action( 'init', 'fairflock_register_navigation_mega_styles' );
