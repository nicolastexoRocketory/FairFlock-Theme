<?php
/**
 * Shared FairFlock icons — canonical stroke chevron (Größentabelle-Akkordeon).
 *
 * Path: M6 9l6 6 6-6 · stroke 2.25 · round caps/joins.
 *
 * @package FairFlock
 * @since 0.9.376
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical chevron path (points down).
 *
 * @since 0.9.376
 *
 * @return string
 */
function fairflock_chevron_path() {
	return 'M6 9l6 6 6-6';
}

/**
 * Stroke chevron SVG markup.
 *
 * @since 0.9.376
 *
 * @param array{dir?:string,size?:int|string,class?:string} $args Direction down|up|left|right, size, class.
 * @return string
 */
function fairflock_chevron_svg( $args = array() ) {
	$dir   = isset( $args['dir'] ) ? (string) $args['dir'] : 'down';
	$size  = isset( $args['size'] ) ? $args['size'] : 16;
	$class = isset( $args['class'] ) ? trim( (string) $args['class'] ) : 'ff-chevron';
	$ok    = array( 'down', 'up', 'left', 'right' );
	if ( ! in_array( $dir, $ok, true ) ) {
		$dir = 'down';
	}
	$class = trim( $class . ' ff-chevron--' . $dir );

	return sprintf(
		'<svg class="%1$s" viewBox="0 0 24 24" width="%2$s" height="%2$s" aria-hidden="true" focusable="false"><path d="%3$s" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		esc_attr( $class ),
		esc_attr( (string) $size ),
		esc_attr( fairflock_chevron_path() )
	);
}

/**
 * URL-encoded SVG for CSS mask-image (black stroke on transparent).
 *
 * @since 0.9.376
 *
 * @return string
 */
function fairflock_chevron_mask_url() {
	$svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'><path d='M6 9l6 6 6-6' stroke='black' stroke-width='2.25' stroke-linecap='round' stroke-linejoin='round'/></svg>";
	return 'url("data:image/svg+xml,' . rawurlencode( $svg ) . '")';
}
