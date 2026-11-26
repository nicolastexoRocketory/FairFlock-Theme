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
 * Additive only: inserts the mega-panel slot if missing. Does not replace
 * other header markup. Skipped when tenant-safety forbids DB migrators
 * unless the slot is still absent (required for Mega to work).
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

		// Additive inject only — never replace the whole header.
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

/**
 * One-time: mark mega-panel layout migration as done — never overwrite DB parts.
 *
 * Uncustomized shops already load parts/mega-panel.html from the theme on deploy.
 * Customized Mega-Menü parts in the DB must stay untouched (multi-tenant rule).
 * To adopt a new theme default: Site Editor → Template-Teil zurücksetzen, or Demo/Repair.
 *
 * @since 0.9.211
 * @since 0.9.218 Dynamic mega lists (categories / products modes).
 * @since 0.9.223 Stop overwriting customized mega-panel content on theme push.
 *
 * @return void
 */
function fairflock_maybe_refresh_mega_panel_layout() {
	if ( get_option( 'fairflock_mega_panel_layout' ) === '4col-v2' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	// Intentionally no wp_update_post: preserves tenant Site Editor edits.
	update_option( 'fairflock_mega_panel_layout', '4col-v2', false );
}
add_action( 'init', 'fairflock_maybe_refresh_mega_panel_layout', 41 );

/**
 * Token-safe className check (same contract as editor JS hasClass).
 *
 * @since 0.9.225
 *
 * @param string $class_name Class attribute.
 * @param string $token      Class token.
 * @return bool
 */
function fairflock_mega_list_has_class( $class_name, $token ) {
	return (bool) preg_match( '/(?:^|\s)' . preg_quote( (string) $token, '/' ) . '(?:\s|$)/', (string) $class_name );
}

/**
 * Parses mega-list mode from a className string.
 *
 * @since 0.9.218
 * @since 0.9.225 Token-boundary match (align with editor JS).
 *
 * @param string $class_name Block className.
 * @return string manual|categories|products
 */
function fairflock_mega_list_parse_mode( $class_name ) {
	if ( fairflock_mega_list_has_class( $class_name, 'ff-mega-list--categories' ) ) {
		return 'categories';
	}
	if ( fairflock_mega_list_has_class( $class_name, 'ff-mega-list--products' ) ) {
		return 'products';
	}
	return 'manual';
}

/**
 * Parses product category id from mega-list className.
 *
 * @since 0.9.218
 *
 * @param string $class_name Block className.
 * @return int
 */
function fairflock_mega_list_parse_category_id( $class_name ) {
	if ( preg_match( '/(?:^|\s)ff-mega-list-cat-(\d+)(?:\s|$)/', (string) $class_name, $m ) ) {
		return (int) $m[1];
	}
	return 0;
}

/**
 * Builds an HTML link list for the mega panel.
 *
 * @since 0.9.218
 *
 * @param array<int, array{url:string,label:string}> $items Links.
 * @return string
 */
function fairflock_mega_list_links_markup( array $items ) {
	if ( ! $items ) {
		return '<ul class="wp-block-list ff-mega-panel__links has-small-font-size ff-mega-list__live"></ul>';
	}

	$html = '<ul class="wp-block-list ff-mega-panel__links has-small-font-size ff-mega-list__live">';
	foreach ( $items as $item ) {
		$url   = isset( $item['url'] ) ? (string) $item['url'] : '';
		$label = isset( $item['label'] ) ? (string) $item['label'] : '';
		if ( $url === '' || $label === '' ) {
			continue;
		}
		$html .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	$html .= '</ul>';

	return $html;
}

/**
 * Groups product categories by parent term id.
 *
 * @since 0.9.567
 *
 * @return array<int, array<int, WP_Term>>
 */
function fairflock_mega_list_terms_by_parent() {
	$by_parent = array();

	if ( ! function_exists( 'fairflock_get_category_overview_terms' ) ) {
		return $by_parent;
	}

	foreach ( fairflock_get_category_overview_terms() as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$parent = (int) $term->parent;
		if ( ! isset( $by_parent[ $parent ] ) ) {
			$by_parent[ $parent ] = array();
		}
		$by_parent[ $parent ][] = $term;
	}

	return $by_parent;
}

/**
 * Nested category list: parents drill into children, with Zurück at the top.
 *
 * @since 0.9.567
 *
 * @param array<int, WP_Term>             $terms     Categories at this level.
 * @param array<int, array<int, WP_Term>> $by_parent Children keyed by parent id.
 * @return string
 */
function fairflock_mega_list_parent_items_markup( array $terms, array $by_parent ) {
	$html    = '';
	$chevron = function_exists( 'fairflock_chevron_svg' )
		? fairflock_chevron_svg( array( 'dir' => 'right', 'size' => 16 ) )
		: '';
	$back_icon = function_exists( 'fairflock_chevron_svg' )
		? fairflock_chevron_svg( array( 'dir' => 'left', 'size' => 16 ) )
		: '';
	$back_label = esc_html__( 'Zurück', 'fairflock' );

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$label    = esc_html( $term->name );
		$children = isset( $by_parent[ (int) $term->term_id ] ) ? $by_parent[ (int) $term->term_id ] : array();
		if ( ! $children ) {
			$html .= '<li class="ff-mega-list__leaf"><a href="' . esc_url( (string) $link ) . '">' . $label . '</a></li>';
			continue;
		}
		$kids  = fairflock_mega_list_parent_items_markup( $children, $by_parent );
		$html .= '<li class="ff-mega-list__branch">';
		$html .= '<button type="button" class="ff-mega-list__drill"><span class="ff-mega-list__label">' . $label . '</span>' . $chevron . '</button>';
		$html .= '<ul class="ff-mega-list__kids" hidden>';
		$html .= '<li class="ff-mega-list__back"><button type="button" class="ff-mega-list__back-btn">' . $back_icon . '<span class="ff-mega-list__label">' . $back_label . '</span></button></li>';
		$html .= $kids;
		$html .= '</ul></li>';
	}

	return $html;
}

/**
 * Live category links for mega list.
 *
 * @since 0.9.218
 * @since 0.9.567 Optional parents-only drill-down.
 *
 * @param bool $parents_only Only Überkategorien; click opens children.
 * @return string
 */
function fairflock_mega_list_categories_markup( $parents_only = false ) {
	if ( $parents_only ) {
		$by_parent = fairflock_mega_list_terms_by_parent();
		$roots     = isset( $by_parent[0] ) ? $by_parent[0] : array();
		$inner     = fairflock_mega_list_parent_items_markup( $roots, $by_parent );
		if ( $inner !== '' ) {
			return '<ul class="wp-block-list ff-mega-panel__links has-small-font-size ff-mega-list__live ff-mega-list__tree">' . $inner . '</ul>';
		}
	}

	$items = array();

	if ( function_exists( 'fairflock_get_category_overview_terms' ) ) {
		foreach ( fairflock_get_category_overview_terms() as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$items[] = array(
				'url'   => (string) $link,
				'label' => $term->name,
			);
		}
	}

	if ( ! $items ) {
		$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		$items[] = array(
			'url'   => (string) $shop,
			'label' => __( 'Zum Shop', 'fairflock' ),
		);
	}

	return fairflock_mega_list_links_markup( $items );
}

/**
 * Live product links for one category.
 *
 * @since 0.9.218
 *
 * @param int $category_id product_cat term ID.
 * @return string
 */
function fairflock_mega_list_products_markup( $category_id ) {
	$category_id = (int) $category_id;
	$items       = array();

	if ( $category_id > 0 && taxonomy_exists( 'product_cat' ) ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => 10,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => array( $category_id ),
					),
				),
			)
		);

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				if ( ! $post instanceof WP_Post ) {
					continue;
				}
				$items[] = array(
					'url'   => get_permalink( $post ),
					'label' => get_the_title( $post ),
				);
			}
		}
		wp_reset_postdata();
	}

	if ( ! $items ) {
		$term_link = $category_id > 0 ? get_term_link( $category_id, 'product_cat' ) : '';
		if ( ! is_wp_error( $term_link ) && is_string( $term_link ) && $term_link !== '' ) {
			$items[] = array(
				'url'   => $term_link,
				'label' => __( 'Zur Kategorie', 'fairflock' ),
			);
		} else {
			$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
			$items[] = array(
				'url'   => (string) $shop,
				'label' => __( 'Zum Shop', 'fairflock' ),
			);
		}
	}

	return fairflock_mega_list_links_markup( $items );
}

/**
 * Replaces mega-list group inner content with live links on the frontend.
 *
 * @since 0.9.218
 * @since 0.9.225 Robust UL match (class token anywhere in attributes).
 *
 * @param string               $block_content Rendered HTML.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function fairflock_render_mega_list_group( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'core/group' !== $block['blockName'] ) {
		return $block_content;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';

	if ( ! fairflock_mega_list_has_class( $class, 'ff-mega-list' ) ) {
		return $block_content;
	}

	$mode = fairflock_mega_list_parse_mode( $class );
	if ( 'manual' === $mode ) {
		return $block_content;
	}

	$live = '';
	if ( 'categories' === $mode ) {
		$parents = fairflock_mega_list_has_class( $class, 'ff-mega-list--parents' );
		$live    = fairflock_mega_list_categories_markup( $parents );
	} elseif ( 'products' === $mode ) {
		$live = fairflock_mega_list_products_markup( fairflock_mega_list_parse_category_id( $class ) );
	}

	if ( '' === $live ) {
		return $block_content;
	}

	// Match <ul … ff-mega-panel__links …> regardless of attribute order/quotes.
	$pattern_links = '/<ul\b(?=[^>]*\bff-mega-panel__links\b)[^>]*>.*?<\/ul>/is';
	if ( preg_match( $pattern_links, $block_content ) ) {
		$replaced = preg_replace( $pattern_links, $live, $block_content, 1 );
		return is_string( $replaced ) ? $replaced : $block_content;
	}

	$pattern_ul = '/<ul\b[^>]*>.*?<\/ul>/is';
	if ( preg_match( $pattern_ul, $block_content ) ) {
		$replaced = preg_replace( $pattern_ul, $live, $block_content, 1 );
		return is_string( $replaced ) ? $replaced : $block_content;
	}

	$replaced = preg_replace( '/<\/div>\s*$/', $live . '</div>', $block_content, 1 );
	return is_string( $replaced ) ? $replaced : $block_content;
}
add_filter( 'render_block_core/group', 'fairflock_render_mega_list_group', 20, 2 );

/**
 * Registers ffMegaLink on core/cover so the attribute persists in block markup.
 *
 * @since 0.9.459
 *
 * @param array<string, mixed> $args Block type args.
 * @param string               $name Block name.
 * @return array<string, mixed>
 */
function fairflock_register_cover_mega_link_attribute( $args, $name ) {
	if ( $name !== 'core/cover' || ! is_array( $args ) ) {
		return $args;
	}
	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}
	$args['attributes']['ffMegaLink'] = array(
		'type'    => 'string',
		'default' => '',
	);
	return $args;
}
add_filter( 'register_block_type_args', 'fairflock_register_cover_mega_link_attribute', 10, 2 );

/**
 * Wraps Cover blocks that have ffMegaLink so they are clickable + zoom on hover.
 *
 * WP 7 Cover has no native toolbar link; the theme adds Link in the editor toolbar/sidebar.
 *
 * @since 0.9.458
 *
 * @param string               $block_content Rendered block HTML.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function fairflock_render_cover_mega_link( $block_content, $block ) {
	if ( ! is_string( $block_content ) || $block_content === '' ) {
		return $block_content;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$link  = isset( $attrs['ffMegaLink'] ) ? trim( (string) $attrs['ffMegaLink'] ) : '';
	if ( $link === '' ) {
		return $block_content;
	}

	$url = esc_url( $link );
	if ( $url === '' || $url === '#' ) {
		return $block_content;
	}

	return '<a class="ff-mega-cover-link" href="' . $url . '">' . $block_content . '</a>';
}
add_filter( 'render_block_core/cover', 'fairflock_render_cover_mega_link', 20, 2 );
