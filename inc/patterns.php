<?php
/**
 * Block pattern category registration.
 *
 * Theme patterns in /patterns are auto-discovered; this only registers
 * the FairFlock category used by those files.
 *
 * @package FairFlock
 * @since 0.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the FairFlock pattern category.
 *
 * @since 0.6.0
 *
 * @return void
 */
function fairflock_register_pattern_categories() {
	register_block_pattern_category(
		'fairflock',
		array(
			'label' => __( 'FairFlock', 'fairflock' ),
		)
	);

	register_block_pattern_category(
		'fairflock-shop',
		array(
			'label' => __( 'FairFlock Shop', 'fairflock' ),
		)
	);
}
add_action( 'init', 'fairflock_register_pattern_categories', 9 );
