<?php
/**
 * Product Collection editor UX — lock FairFlock slideshow layout + Ansehen colors.
 *
 * @package FairFlock
 * @since 0.9.140
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues editor script/CSS that hide Layout controls on product sliders.
 *
 * @since 0.9.140
 *
 * @return void
 */
function fairflock_enqueue_product_collection_editor_assets() {
	$js  = FAIRFLOCK_DIR . '/assets/js/editor-product-collection.js';
	$css = FAIRFLOCK_DIR . '/assets/css/editor-product-collection.css';
	$ansehen_js = FAIRFLOCK_DIR . '/assets/js/editor-products-ansehen.js';

	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'fairflock-editor-product-collection',
			FAIRFLOCK_URI . '/assets/js/editor-product-collection.js',
			array(
				'wp-blocks',
				'wp-data',
				'wp-dom-ready',
			),
			FAIRFLOCK_VERSION,
			true
		);
	}

	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'fairflock-editor-product-collection',
			FAIRFLOCK_URI . '/assets/css/editor-product-collection.css',
			array(),
			FAIRFLOCK_VERSION
		);
	}

	if ( is_readable( $ansehen_js ) ) {
		wp_enqueue_script(
			'fairflock-editor-products-ansehen',
			FAIRFLOCK_URI . '/assets/js/editor-products-ansehen.js',
			array(
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
			'fairflock-editor-products-ansehen',
			'fairflockProductsAnsehen',
			array(
				'i18n' => array(
					'panel'      => __( 'Ansehen-Button', 'fairflock' ),
					'background' => __( 'Hintergrund', 'fairflock' ),
					'text'       => __( 'Text', 'fairflock' ),
				),
			)
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_product_collection_editor_assets' );

/**
 * Allows safe CSS color values for the Ansehen hover button.
 *
 * @since 0.9.257
 *
 * @param string $color Raw color from block metadata.
 * @return string Sanitized color or empty string.
 */
function fairflock_sanitize_prod_view_color( $color ) {
	$color = trim( (string) $color );
	if ( '' === $color ) {
		return '';
	}

	$hex = sanitize_hex_color( $color );
	if ( $hex ) {
		return $hex;
	}

	if ( preg_match( '/^var\(--wp--preset--color--[a-z0-9\-]+\)$/i', $color ) ) {
		return $color;
	}

	if ( preg_match( '/^(rgb|rgba|hsl|hsla)\(/i', $color ) ) {
		$cleaned = preg_replace( '/[^a-z0-9%,.\s()#\-]/i', '', $color );
		return is_string( $cleaned ) ? $cleaned : '';
	}

	return '';
}

/**
 * Injects --ff-prod-view-bg / --ff-prod-view-fg from block metadata.
 *
 * @since 0.9.257
 *
 * @param string               $block_content Rendered HTML.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function fairflock_render_products_ansehen_colors( $block_content, $block ) {
	if ( ! is_string( $block_content ) || '' === $block_content || ! is_array( $block ) ) {
		return $block_content;
	}

	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( '' === $class ) {
		return $block_content;
	}

	$targets = array(
		'ff-pattern-products--woo',
		'ff-prod-slider',
		'ff-prod-slider__track',
		'ff-shop__collection',
	);

	$matched = false;
	$padded  = ' ' . $class . ' ';
	foreach ( $targets as $token ) {
		if ( false !== strpos( $padded, ' ' . $token . ' ' ) ) {
			$matched = true;
			break;
		}
	}

	if ( ! $matched ) {
		return $block_content;
	}

	$meta = ( isset( $block['attrs']['metadata'] ) && is_array( $block['attrs']['metadata'] ) )
		? $block['attrs']['metadata']
		: array();

	$bg = isset( $meta['ffProdViewBg'] ) ? fairflock_sanitize_prod_view_color( $meta['ffProdViewBg'] ) : '';
	$fg = isset( $meta['ffProdViewFg'] ) ? fairflock_sanitize_prod_view_color( $meta['ffProdViewFg'] ) : '';

	if ( '' === $bg && '' === $fg ) {
		return $block_content;
	}

	$vars = '';
	if ( '' !== $bg ) {
		$vars .= '--ff-prod-view-bg:' . $bg . ';';
	}
	if ( '' !== $fg ) {
		$vars .= '--ff-prod-view-fg:' . $fg . ';';
	}

	return (string) preg_replace_callback(
		'/^(\s*<[a-z0-9:-]+)([^>]*)>/i',
		static function ( $matches ) use ( $vars ) {
			$tag   = $matches[1];
			$attrs = $matches[2];

			if ( preg_match( '/\sstyle=(["\'])(.*?)\1/i', $attrs, $style_match ) ) {
				$quote = $style_match[1];
				$attrs = preg_replace(
					'/\sstyle=(["\'])(.*?)\1/i',
					' style=' . $quote . $vars . $style_match[2] . $quote,
					$attrs,
					1
				);
			} else {
				$attrs .= ' style="' . esc_attr( $vars ) . '"';
			}

			return $tag . $attrs . '>';
		},
		$block_content,
		1
	);
}
add_filter( 'render_block', 'fairflock_render_products_ansehen_colors', 10, 2 );
