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
 * Live category links for mega list.
 *
 * @since 0.9.218
 *
 * @return string
 */
function fairflock_mega_list_categories_markup() {
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
		$live = fairflock_mega_list_categories_markup();
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
