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

/**
 * One-time: apply 4-column Mega-Menü layout with dynamic list support.
 *
 * Overwrites customized mega-panel template parts so the new default appears
 * without manually resetting the Site Editor part.
 *
 * @since 0.9.211
 * @since 0.9.218 Dynamic mega lists (categories / products modes).
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

	$file = FAIRFLOCK_DIR . '/parts/mega-panel.html';
	if ( ! is_readable( $file ) ) {
		return;
	}

	$markup = file_get_contents( $file );
	if ( ! is_string( $markup ) || $markup === '' ) {
		return;
	}

	$parts = get_posts(
		array(
			'post_type'      => 'wp_template_part',
			'name'           => 'mega-panel',
			'post_status'    => array( 'publish', 'draft', 'auto-draft' ),
			'posts_per_page' => 20,
			'no_found_rows'  => true,
		)
	);

	foreach ( $parts as $part ) {
		if ( ! $part instanceof WP_Post ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => (int) $part->ID,
				'post_content' => $markup,
			)
		);
	}

	update_option( 'fairflock_mega_panel_layout', '4col-v2', false );
}
add_action( 'init', 'fairflock_maybe_refresh_mega_panel_layout', 41 );

/**
 * Parses mega-list mode from a className string.
 *
 * @since 0.9.218
 *
 * @param string $class_name Block className.
 * @return string manual|categories|products
 */
function fairflock_mega_list_parse_mode( $class_name ) {
	$class_name = (string) $class_name;
	if ( str_contains( $class_name, 'ff-mega-list--categories' ) ) {
		return 'categories';
	}
	if ( str_contains( $class_name, 'ff-mega-list--products' ) ) {
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

	if ( ! str_contains( $class, 'ff-mega-list' ) ) {
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

	if ( $live === '' || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	/*
	 * Replace the first list (or append after heading) inside the group.
	 * Prefer swapping an existing .ff-mega-panel__links / ul; otherwise inject before </div>.
	 */
	if ( preg_match( '/<ul\b[^>]*class="[^"]*ff-mega-panel__links[^"]*"[^>]*>.*?<\/ul>/is', $block_content ) ) {
		return (string) preg_replace(
			'/<ul\b[^>]*class="[^"]*ff-mega-panel__links[^"]*"[^>]*>.*?<\/ul>/is',
			$live,
			$block_content,
			1
		);
	}

	if ( preg_match( '/<ul\b[^>]*>.*?<\/ul>/is', $block_content ) ) {
		return (string) preg_replace( '/<ul\b[^>]*>.*?<\/ul>/is', $live, $block_content, 1 );
	}

	return (string) preg_replace( '/<\/div>\s*$/', $live . '</div>', $block_content, 1 );
}
add_filter( 'render_block_core/group', 'fairflock_render_mega_list_group', 20, 2 );
