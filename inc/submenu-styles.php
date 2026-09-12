<?php
/**
 * Syncs Untermenü template-part colors to live Navigation dropdowns.
 *
 * The design surface (.ff-nav-sub) is edited in the Site Editor; on the
 * frontend WordPress renders core Navigation submenus. Shared CSS variables
 * keep both in sync.
 *
 * @package FairFlock
 * @since 0.5.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme palette slug → hex (matches theme.json).
 *
 * @since 0.5.7
 *
 * @return array<string, string>
 */
function fairflock_color_palette_map() {
	return array(
		'base'       => '#f3f5f4',
		'contrast'   => '#0e1114',
		'brand'      => '#377530',
		'brand-deep' => '#2a5c25',
		'brand-soft' => '#4a8f42',
		'muted'      => '#66707a',
		'white'      => '#ffffff',
	);
}

/**
 * Resolves a block color value (preset slug or CSS string) to a CSS color.
 *
 * @since 0.5.7
 *
 * @param mixed $value Preset slug, array with color key, or CSS color string.
 * @return string Empty string if unresolved.
 */
function fairflock_resolve_color_value( $value ) {
	if ( is_array( $value ) ) {
		if ( ! empty( $value['color'] ) && is_string( $value['color'] ) ) {
			$value = $value['color'];
		} else {
			return '';
		}
	}

	if ( ! is_string( $value ) || $value === '' ) {
		return '';
	}

	if ( str_starts_with( $value, 'var:' ) ) {
		// e.g. var:preset|color|contrast
		$parts = explode( '|', $value );
		$slug  = end( $parts );
		$map   = fairflock_color_palette_map();
		return $map[ $slug ] ?? '';
	}

	$map = fairflock_color_palette_map();
	if ( isset( $map[ $value ] ) ) {
		return $map[ $value ];
	}

	// Already a CSS color (#hex, rgb(), etc.).
	if ( preg_match( '/^(#|rgb|hsl|var\()/i', $value ) ) {
		return $value;
	}

	return '';
}

/**
 * Finds the root .ff-nav-sub group attrs inside parsed blocks.
 *
 * @since 0.5.7
 *
 * @param array<int, array<string, mixed>> $blocks Parsed blocks.
 * @return array<string, mixed>|null
 */
function fairflock_find_nav_sub_attrs( array $blocks ) {
	foreach ( $blocks as $block ) {
		$name  = $block['blockName'] ?? '';
		$attrs = $block['attrs'] ?? array();
		$class = $attrs['className'] ?? '';

		if ( $name === 'core/group' && is_string( $class ) && str_contains( $class, 'ff-nav-sub' ) ) {
			return $attrs;
		}

		$inner = $block['innerBlocks'] ?? array();
		if ( $inner ) {
			$found = fairflock_find_nav_sub_attrs( $inner );
			if ( $found !== null ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Reads design tokens from the Untermenü template part.
 *
 * @since 0.5.7
 *
 * @return array{bg: string, color: string, hover_bg: string}
 */
function fairflock_get_submenu_design_tokens() {
	$defaults = array(
		'bg'      => 'rgba(14, 17, 20, 0.94)',
		'color'   => 'rgba(255, 255, 255, 0.82)',
		'hover_bg'=> 'rgba(255, 255, 255, 0.08)',
	);

	if ( ! function_exists( 'get_block_template' ) ) {
		return $defaults;
	}

	$template = get_block_template( get_stylesheet() . '//submenu', 'wp_template_part' );
	if ( ! $template || empty( $template->content ) ) {
		return $defaults;
	}

	$attrs = fairflock_find_nav_sub_attrs( parse_blocks( $template->content ) );
	if ( ! is_array( $attrs ) ) {
		return $defaults;
	}

	$bg = '';
	if ( ! empty( $attrs['backgroundColor'] ) ) {
		$bg = fairflock_resolve_color_value( $attrs['backgroundColor'] );
	} elseif ( ! empty( $attrs['style']['color']['background'] ) ) {
		$bg = fairflock_resolve_color_value( $attrs['style']['color']['background'] );
	}

	$color = '';
	if ( ! empty( $attrs['textColor'] ) ) {
		$color = fairflock_resolve_color_value( $attrs['textColor'] );
	} elseif ( ! empty( $attrs['style']['color']['text'] ) ) {
		$color = fairflock_resolve_color_value( $attrs['style']['color']['text'] );
	}

	// If only a light background was set, default text to contrast (ink).
	if ( $bg && ! $color ) {
		$light_slugs = array( 'white', 'base' );
		$bg_slug     = $attrs['backgroundColor'] ?? '';
		if ( in_array( $bg_slug, $light_slugs, true ) || strcasecmp( $bg, '#ffffff' ) === 0 || strcasecmp( $bg, '#f3f5f4' ) === 0 ) {
			$color    = fairflock_color_palette_map()['contrast'];
			$hover_bg = 'rgba(14, 17, 20, 0.06)';
		} else {
			$color    = $defaults['color'];
			$hover_bg = $defaults['hover_bg'];
		}
	} elseif ( $color && $bg ) {
		$bg_slug  = $attrs['backgroundColor'] ?? '';
		$hover_bg = in_array( $bg_slug, array( 'white', 'base' ), true )
			? 'rgba(14, 17, 20, 0.06)'
			: 'rgba(255, 255, 255, 0.08)';
	} else {
		$hover_bg = $defaults['hover_bg'];
	}

	return array(
		'bg'       => $bg !== '' ? $bg : $defaults['bg'],
		'color'    => $color !== '' ? $color : $defaults['color'],
		'hover_bg' => $hover_bg ?? $defaults['hover_bg'],
	);
}

/**
 * Prints CSS variables so live Navigation dropdowns match the Untermenü design.
 *
 * @since 0.5.7
 *
 * @return void
 */
function fairflock_print_submenu_design_css() {
	if ( is_admin() ) {
		return;
	}

	$tokens = fairflock_get_submenu_design_tokens();
	$bg     = esc_html( $tokens['bg'] );
	$color  = esc_html( $tokens['color'] );
	$hover  = esc_html( $tokens['hover_bg'] );

	printf(
		'<style id="fairflock-submenu-design">
.ff-header{
	--ff-submenu-bg:%1$s;
	--ff-submenu-color:%2$s;
	--ff-submenu-hover-bg:%3$s;
}
.ff-header__nav .wp-block-navigation__submenu-container{
	background:var(--ff-submenu-bg) !important;
	color:var(--ff-submenu-color) !important;
}
.ff-header__nav .wp-block-navigation__submenu-container .wp-block-navigation-item__content,
.ff-header__nav .wp-block-navigation__submenu-container .wp-block-navigation-item__content:hover,
.ff-header__nav .wp-block-navigation__submenu-container .wp-block-navigation-item.current-menu-item > .wp-block-navigation-item__content{
	color:var(--ff-submenu-color) !important;
}
.ff-header__nav .wp-block-navigation__submenu-container .wp-block-navigation-item__content:hover{
	background:var(--ff-submenu-hover-bg) !important;
}
</style>' . "\n",
		$bg,
		$color,
		$hover
	);
}
add_action( 'wp_head', 'fairflock_print_submenu_design_css', 30 );
