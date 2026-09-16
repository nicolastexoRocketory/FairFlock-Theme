<?php
/**
 * Opt-in Mega menu: Navigation style “Mega” opens template part “Mega-Menü”.
 *
 * Edit layout in Site Editor → Template parts → Mega-Menü (columns, images, buttons).
 * Assign style “Mega” to a header nav item to open that panel.
 *
 * @package FairFlock
 * @since 0.9.202
 * @since 0.9.203 Panel content via template part (block builder), hover bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the “Mega” block style on navigation items.
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
	register_block_style( 'core/navigation-link', $style );
}
add_action( 'init', 'fairflock_register_navigation_mega_styles' );

/**
 * One-time: inject Mega-Menü template-part into customized Header parts.
 *
 * @since 0.9.203
 *
 * @return void
 */
function fairflock_maybe_inject_mega_panel_into_header() {
	if ( get_option( 'fairflock_mega_panel_slot' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$snippet = '<!-- wp:template-part {"slug":"mega-panel","theme":"fairflock","area":"uncategorized","tagName":"div","className":"ff-mega-panel-slot"} /-->';

	$parts = get_posts(
		array(
			'post_type'      => 'wp_template_part',
			'name'           => 'header',
			'post_status'    => array( 'publish', 'draft', 'auto-draft' ),
			'posts_per_page' => 20,
			'no_found_rows'  => true,
		)
	);

	foreach ( $parts as $part ) {
		if ( ! $part instanceof WP_Post || ! is_string( $part->post_content ) ) {
			continue;
		}

		if ( str_contains( $part->post_content, 'ff-mega-panel-slot' ) || str_contains( $part->post_content, '"slug":"mega-panel"' ) ) {
			continue;
		}

		$content = $part->post_content;

		if ( str_contains( $content, 'ff-submenu-slot' ) || str_contains( $content, '"slug":"submenu"' ) ) {
			$replaced = preg_replace(
				'/(<!--\s*wp:template-part\s*\{[^}]*"slug"\s*:\s*"submenu"[^}]*\}\s*\/-->)/',
				'$1' . "\n\n\t" . $snippet,
				$content,
				1
			);
			$content = is_string( $replaced ) ? $replaced : $content;
		} elseif ( str_contains( $content, 'ff-search-overlay-slot' ) || str_contains( $content, '"slug":"search-overlay"' ) ) {
			$replaced = preg_replace(
				'/(<!--\s*wp:template-part\s*\{[^}]*"slug"\s*:\s*"search-overlay"[^}]*\}\s*\/-->)/',
				$snippet . "\n\n\t" . '$1',
				$content,
				1
			);
			$content = is_string( $replaced ) ? $replaced : $content;
		} else {
			continue;
		}

		if ( $content === $part->post_content ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => (int) $part->ID,
				'post_content' => $content,
			)
		);
	}

	update_option( 'fairflock_mega_panel_slot', '1' );
}
add_action( 'init', 'fairflock_maybe_inject_mega_panel_into_header', 40 );
