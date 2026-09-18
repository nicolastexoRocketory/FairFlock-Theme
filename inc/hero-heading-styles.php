<?php
/**
 * Hero-Slider-Titel folgen Global Styles (Elemente h1 / h2 / heading).
 *
 * Theme-CSS setzt nur Layout + Weiß; Typografie kommt aus den Site-Editor-
 * Einstellungen. Block-Schriftgröße (S–XXL / custom) hat Vorrang vor dem
 * Global-Styles-Default.
 *
 * @package FairFlock
 * @since 0.9.207
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves a theme.json / global-styles value to a CSS declaration value.
 *
 * @since 0.9.207
 *
 * @param mixed $value Preset ref (var:preset|…), fluid array, or raw CSS.
 * @return string
 */
function fairflock_resolve_style_value( $value ) {
	if ( is_array( $value ) ) {
		if ( ! empty( $value['size'] ) && is_string( $value['size'] ) ) {
			$value = $value['size'];
		} elseif ( ! empty( $value['fluid'] ) && is_string( $value['fluid'] ) ) {
			$value = $value['fluid'];
		} else {
			return '';
		}
	}

	if ( ! is_string( $value ) || $value === '' ) {
		return '';
	}

	if ( str_starts_with( $value, 'var:preset|' ) ) {
		$parts = explode( '|', $value );
		if ( count( $parts ) >= 3 ) {
			$type = sanitize_key( $parts[1] );
			$slug = sanitize_key( $parts[2] );
			if ( $type && $slug ) {
				return 'var(--wp--preset--' . $type . '--' . $slug . ')';
			}
		}
		return '';
	}

	if ( str_starts_with( $value, 'var:custom|' ) ) {
		$parts = explode( '|', $value );
		array_shift( $parts ); // drop "var:custom"
		$parts = array_map( 'sanitize_key', $parts );
		$parts = array_filter( $parts );
		if ( $parts ) {
			return 'var(--wp--custom--' . implode( '--', $parts ) . ')';
		}
		return '';
	}

	return $value;
}

/**
 * Typography map for a Global Styles element (merged with “heading” base).
 *
 * @since 0.9.207
 *
 * @param string $element Element key: h1, h2, …
 * @return array<string, string> CSS property => value
 */
function fairflock_get_heading_element_css_props( $element ) {
	if ( ! function_exists( 'wp_get_global_styles' ) ) {
		return array();
	}

	$base = wp_get_global_styles( array( 'elements', 'heading', 'typography' ) );
	$own  = wp_get_global_styles( array( 'elements', $element, 'typography' ) );

	if ( ! is_array( $base ) ) {
		$base = array();
	}
	if ( ! is_array( $own ) ) {
		$own = array();
	}

	$merged = array_merge( $base, $own );
	$map    = array(
		'fontFamily'    => 'font-family',
		'fontSize'      => 'font-size',
		'fontWeight'    => 'font-weight',
		'fontStyle'     => 'font-style',
		'lineHeight'    => 'line-height',
		'letterSpacing' => 'letter-spacing',
		'textTransform' => 'text-transform',
		'textDecoration'=> 'text-decoration',
	);

	$out = array();
	foreach ( $map as $camel => $css ) {
		if ( ! isset( $merged[ $camel ] ) || $merged[ $camel ] === '' || $merged[ $camel ] === null ) {
			continue;
		}
		$resolved = fairflock_resolve_style_value( $merged[ $camel ] );
		if ( $resolved !== '' ) {
			$out[ $css ] = $resolved;
		}
	}

	return $out;
}

/**
 * Builds CSS rules applying Global Styles typography to hero titles.
 *
 * font-size only as default — block presets (has-*-font-size) and inline
 * sizes keep priority. Other typography still follows Global Styles.
 *
 * @since 0.9.207
 * @since 0.9.233 Block font-size overrides Global Styles defaults.
 *
 * @return string
 */
function fairflock_get_hero_heading_styles_css() {
	$blocks = array();

	foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $tag ) {
		$props = fairflock_get_heading_element_css_props( $tag );
		if ( ! $props ) {
			continue;
		}

		$font_size = '';
		if ( isset( $props['font-size'] ) ) {
			$font_size = $props['font-size'];
			unset( $props['font-size'] );
		}

		$base_selector = '.fs-slider .fs-slide ' . $tag
			. ',.fs-slider .fs-slide ' . $tag . '.wp-block-heading'
			. ',.fs-slider .fs-slide ' . $tag . '[class*="-font-family"]'
			. ',.block-editor-iframe__body .fs-slider .fs-slide ' . $tag
			. ',.editor-styles-wrapper .fs-slider .fs-slide ' . $tag;

		if ( $props ) {
			$decls = array();
			foreach ( $props as $prop => $val ) {
				$decls[] = $prop . ':' . $val . ' !important';
			}
			$blocks[] = $base_selector . '{' . implode( ';', $decls ) . '}';
		}

		if ( $font_size !== '' ) {
			/* Only when the block has no size class / inline size */
			$size_selector = '.fs-slider .fs-slide ' . $tag . ':not([class*="-font-size"]):not([style*="font-size"])'
				. ',.fs-slider .fs-slide ' . $tag . '.wp-block-heading:not([class*="-font-size"]):not([style*="font-size"])'
				. ',.block-editor-iframe__body .fs-slider .fs-slide ' . $tag . ':not([class*="-font-size"]):not([style*="font-size"])'
				. ',.editor-styles-wrapper .fs-slider .fs-slide ' . $tag . ':not([class*="-font-size"]):not([style*="font-size"])';
			$blocks[]      = $size_selector . '{font-size:' . $font_size . ' !important;}';
		}
	}

	if ( ! $blocks ) {
		return '';
	}

	return "/* FairFlock: Hero-Titel = Global Styles h1–h6 (Größe nur als Fallback) */\n" . implode( "\n", $blocks );
}

/**
 * Prints / inlines hero heading styles on the frontend.
 *
 * @since 0.9.207
 *
 * @return void
 */
function fairflock_print_hero_heading_styles() {
	$css = fairflock_get_hero_heading_styles_css();
	if ( $css === '' ) {
		return;
	}

	if ( wp_style_is( 'fairflock-hero-slider', 'enqueued' ) || wp_style_is( 'fairflock-hero-slider', 'registered' ) ) {
		wp_add_inline_style( 'fairflock-hero-slider', $css );
		return;
	}

	echo '<style id="fairflock-hero-heading-styles">' . "\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_enqueue_scripts', 'fairflock_print_hero_heading_styles', 30 );

/**
 * Same styles in the Site Editor / block editor canvas.
 *
 * @since 0.9.207
 *
 * @return void
 */
function fairflock_print_hero_heading_styles_editor() {
	$css = fairflock_get_hero_heading_styles_css();
	if ( $css === '' ) {
		return;
	}
	wp_add_inline_style( 'wp-edit-blocks', $css );
}
add_action( 'enqueue_block_editor_assets', 'fairflock_print_hero_heading_styles_editor', 30 );

/**
 * Registers “Zentriert” style for Hero slides (Site Editor → Block → Stile).
 *
 * Apply to a Slide group (class fs-slide) or its Inhalts-Group (fs-slide-inner).
 *
 * @since 0.9.232
 *
 * @return void
 */
function fairflock_register_hero_center_style() {
	register_block_style(
		'core/group',
		array(
			'name'  => 'ff-hero-center',
			'label' => __( 'Hero: Inhalt mittig', 'fairflock' ),
		)
	);
	register_block_style(
		'core/group',
		array(
			'name'  => 'ff-hero-nav-cluster',
			'label' => __( 'Hero-Nav: Pfeile neben Punkten', 'fairflock' ),
		)
	);
}
add_action( 'init', 'fairflock_register_hero_center_style' );
