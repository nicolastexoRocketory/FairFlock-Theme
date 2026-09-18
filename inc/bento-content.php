<?php
/**
 * Bento grid live content — collections, products, or pages/posts.
 *
 * className on .ff-bento:
 * - ff-bento--manual (default)
 * - ff-bento--collections
 * - ff-bento--products + ff-bento-cat-{id}
 * - ff-bento--posts + ff-bento-ids-{id}-{id}-…
 *
 * @package FairFlock
 * @since 0.9.237
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Max live cards in a bento grid. */
const FAIRFLOCK_BENTO_CARD_LIMIT = 10;

/**
 * Whether a class string contains a token.
 *
 * @since 0.9.237
 *
 * @param string $class_name Class attribute.
 * @param string $token      Class token.
 * @return bool
 */
function fairflock_bento_has_class( $class_name, $token ) {
	return (bool) preg_match( '/(?:^|\s)' . preg_quote( (string) $token, '/' ) . '(?:\s|$)/', (string) $class_name );
}

/**
 * Parses bento content mode from className.
 *
 * @since 0.9.237
 *
 * @param string $class_name Block className.
 * @return string manual|collections|products|posts
 */
function fairflock_bento_parse_mode( $class_name ) {
	if ( fairflock_bento_has_class( $class_name, 'ff-bento--collections' ) ) {
		return 'collections';
	}
	if ( fairflock_bento_has_class( $class_name, 'ff-bento--products' ) ) {
		return 'products';
	}
	if ( fairflock_bento_has_class( $class_name, 'ff-bento--posts' ) ) {
		return 'posts';
	}
	return 'manual';
}

/**
 * Parses product category id from bento className.
 *
 * @since 0.9.237
 *
 * @param string $class_name Block className.
 * @return int
 */
function fairflock_bento_parse_category_id( $class_name ) {
	if ( preg_match( '/(?:^|\s)ff-bento-cat-(\d+)(?:\s|$)/', (string) $class_name, $m ) ) {
		return (int) $m[1];
	}
	return 0;
}

/**
 * Parses post/page ids from bento className.
 *
 * @since 0.9.237
 *
 * @param string $class_name Block className.
 * @return int[]
 */
function fairflock_bento_parse_post_ids( $class_name ) {
	if ( ! preg_match( '/(?:^|\s)ff-bento-ids-((?:\d+-)*\d+)(?:\s|$)/', (string) $class_name, $m ) ) {
		return array();
	}

	$ids = array();
	foreach ( explode( '-', $m[1] ) as $part ) {
		$id = (int) $part;
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}

	return array_slice( array_values( array_unique( $ids ) ), 0, 12 );
}

/**
 * Mosaic span classes for card index (repeating 5-tile band).
 *
 * @since 0.9.237
 *
 * @param int $index Zero-based index.
 * @return string
 */
function fairflock_bento_mosaic_classes( $index ) {
	$n = ( (int) $index % 5 + 5 ) % 5;

	switch ( $n ) {
		case 0:
			return 'is-tall is-brand ff-bento-cols-1 ff-bento-rows-2';
		case 1:
			return 'is-wide ff-bento-card--1 ff-bento-cols-2 ff-bento-rows-1';
		case 2:
			return 'ff-bento-card--2 ff-bento-cols-1 ff-bento-rows-1';
		case 3:
			return 'ff-bento-card--3 ff-bento-cols-1 ff-bento-rows-1';
		default:
			return 'is-wide ff-bento-card--4 ff-bento-cols-2 ff-bento-rows-1';
	}
}

/**
 * Renders one live bento card.
 *
 * @since 0.9.237
 *
 * @param array<string, mixed> $item {
 *     @type string $title Title.
 *     @type string $text  Description.
 *     @type string $url   Link URL.
 *     @type string $cta   CTA label.
 *     @type string $image Optional background image URL.
 * }
 * @param int                  $index Zero-based index.
 * @return string
 */
function fairflock_bento_card_html( array $item, $index ) {
	$title = isset( $item['title'] ) ? (string) $item['title'] : '';
	$text  = isset( $item['text'] ) ? (string) $item['text'] : '';
	$url   = isset( $item['url'] ) ? (string) $item['url'] : '';
	$cta   = isset( $item['cta'] ) ? (string) $item['cta'] : __( 'Mehr erfahren →', 'fairflock' );
	$image = isset( $item['image'] ) ? (string) $item['image'] : '';

	if ( $title === '' || $url === '' ) {
		return '';
	}

	$classes = array( 'wp-block-group', 'ff-bento-card' );
	foreach ( preg_split( '/\s+/', fairflock_bento_mosaic_classes( (int) $index ) ) as $token ) {
		if ( $token !== '' ) {
			$classes[] = $token;
		}
	}

	$bg_html = '';
	if ( $image !== '' ) {
		$classes   = array_values(
			array_filter(
				$classes,
				static function ( $c ) {
					return $c !== 'is-brand' && ! preg_match( '/^ff-bento-card--\d+$/', $c );
				}
			)
		);
		$classes[] = 'has-cat-image';
		$bg_html   = '<div class="ff-bento-card__bg" style="background-image:url(' . esc_url( $image ) . ')" aria-hidden="true"></div>';
	}

	$class_attr = esc_attr( implode( ' ', array_unique( $classes ) ) );

	return sprintf(
		'<div class="%1$s">%2$s<h3 class="wp-block-heading">%3$s</h3><p>%4$s</p><p class="ff-bento-card__cta"><a href="%5$s">%6$s</a></p></div>',
		$class_attr,
		$bg_html,
		esc_html( $title ),
		esc_html( $text ),
		esc_url( $url ),
		esc_html( $cta )
	);
}

/**
 * Live cards from all product categories (collections).
 *
 * @since 0.9.237
 *
 * @return string
 */
function fairflock_bento_collections_cards_html() {
	$items = array();
	$cta   = __( 'Zur Kollektion →', 'fairflock' );

	if ( function_exists( 'fairflock_get_category_overview_terms' ) ) {
		foreach ( fairflock_get_category_overview_terms() as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}

			$desc = trim( wp_strip_all_tags( (string) $term->description ) );
			if ( $desc === '' ) {
				$count = (int) $term->count;
				$desc  = sprintf(
					/* translators: %s: product count */
					_n( '%s Artikel', '%s Artikel', $count, 'fairflock' ),
					number_format_i18n( $count )
				);
			}

			$image    = '';
			$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
			if ( $thumb_id > 0 ) {
				$url = wp_get_attachment_image_url( $thumb_id, 'large' );
				if ( is_string( $url ) && $url !== '' ) {
					$image = $url;
				}
			}

			$items[] = array(
				'title' => $term->name,
				'text'  => $desc,
				'url'   => (string) $link,
				'cta'   => $cta,
				'image' => $image,
			);

			if ( count( $items ) >= FAIRFLOCK_BENTO_CARD_LIMIT ) {
				break;
			}
		}
	}

	return fairflock_bento_cards_from_items( $items );
}

/**
 * Live cards from products in one category.
 *
 * @since 0.9.237
 *
 * @param int $category_id product_cat term ID.
 * @return string
 */
function fairflock_bento_products_cards_html( $category_id ) {
	$category_id = (int) $category_id;
	$items       = array();
	$cta         = __( 'Zum Produkt →', 'fairflock' );

	if ( $category_id > 0 && taxonomy_exists( 'product_cat' ) ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => FAIRFLOCK_BENTO_CARD_LIMIT,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
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

				$image = '';
				$thumb = get_the_post_thumbnail_url( $post, 'large' );
				if ( is_string( $thumb ) && $thumb !== '' ) {
					$image = $thumb;
				}

				$excerpt = trim( wp_strip_all_tags( (string) $post->post_excerpt ) );
				if ( $excerpt === '' ) {
					$excerpt = __( 'Jetzt entdecken.', 'fairflock' );
				}

				$items[] = array(
					'title' => get_the_title( $post ),
					'text'  => wp_html_excerpt( $excerpt, 90, '…' ),
					'url'   => get_permalink( $post ),
					'cta'   => $cta,
					'image' => $image,
				);
			}
		}
		wp_reset_postdata();
	}

	if ( ! $items ) {
		$term_link = $category_id > 0 ? get_term_link( $category_id, 'product_cat' ) : '';
		if ( ! is_wp_error( $term_link ) && is_string( $term_link ) && $term_link !== '' ) {
			$items[] = array(
				'title' => __( 'Zur Kategorie', 'fairflock' ),
				'text'  => __( 'Noch keine Produkte in dieser Kategorie.', 'fairflock' ),
				'url'   => $term_link,
				'cta'   => __( 'Shoppen →', 'fairflock' ),
				'image' => '',
			);
		}
	}

	return fairflock_bento_cards_from_items( $items );
}

/**
 * Live cards from selected pages/posts.
 *
 * @since 0.9.237
 *
 * @param int[] $post_ids Post IDs.
 * @return string
 */
function fairflock_bento_posts_cards_html( array $post_ids ) {
	$items = array();

	foreach ( $post_ids as $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 ) {
			continue;
		}

		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
			continue;
		}
		if ( ! in_array( $post->post_type, array( 'page', 'post' ), true ) ) {
			continue;
		}

		$image = '';
		$thumb = get_the_post_thumbnail_url( $post, 'large' );
		if ( is_string( $thumb ) && $thumb !== '' ) {
			$image = $thumb;
		}

		$excerpt = trim( wp_strip_all_tags( (string) $post->post_excerpt ) );
		if ( $excerpt === '' ) {
			$excerpt = wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 18, '…' );
		}
		if ( $excerpt === '' ) {
			$excerpt = 'page' === $post->post_type
				? __( 'Mehr erfahren.', 'fairflock' )
				: __( 'Weiterlesen.', 'fairflock' );
		}

		$cta = 'page' === $post->post_type
			? __( 'Mehr erfahren →', 'fairflock' )
			: __( 'Weiterlesen →', 'fairflock' );

		$items[] = array(
			'title' => get_the_title( $post ),
			'text'  => wp_html_excerpt( $excerpt, 90, '…' ),
			'url'   => get_permalink( $post ),
			'cta'   => $cta,
			'image' => $image,
		);

		if ( count( $items ) >= FAIRFLOCK_BENTO_CARD_LIMIT ) {
			break;
		}
	}

	return fairflock_bento_cards_from_items( $items );
}

/**
 * Builds concatenated card HTML from item list.
 *
 * @since 0.9.237
 *
 * @param array<int, array<string, mixed>> $items Card data.
 * @return string
 */
function fairflock_bento_cards_from_items( array $items ) {
	$html = '';
	foreach ( array_values( $items ) as $i => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$html .= fairflock_bento_card_html( $item, (int) $i );
	}
	return $html;
}

/**
 * Replaces inner HTML of the first matching open/close div pair.
 *
 * @since 0.9.237
 *
 * @param string $html       Full group HTML.
 * @param string $open_regex Regex for the opening tag (without delimiters).
 * @param string $inner      New inner HTML.
 * @return string|null New HTML or null on failure.
 */
function fairflock_bento_replace_group_inner( $html, $open_regex, $inner ) {
	if ( ! preg_match( '/' . $open_regex . '/i', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		return null;
	}

	$open_tag = $m[0][0];
	$open_pos = (int) $m[0][1];
	$inner_start = $open_pos + strlen( $open_tag );
	$depth       = 1;
	$len         = strlen( $html );
	$pos         = $inner_start;

	while ( $pos < $len && $depth > 0 ) {
		$next_open  = stripos( $html, '<div', $pos );
		$next_close = stripos( $html, '</div>', $pos );

		if ( false === $next_close ) {
			return null;
		}

		if ( false !== $next_open && $next_open < $next_close ) {
			++$depth;
			$pos = $next_open + 4;
			continue;
		}

		--$depth;
		if ( 0 === $depth ) {
			$before = substr( $html, 0, $inner_start );
			$after  = substr( $html, $next_close );
			return $before . $inner . $after;
		}
		$pos = $next_close + 6;
	}

	return null;
}

/**
 * Replaces bento inner cards with live content on the frontend.
 *
 * @since 0.9.237
 *
 * @param string               $block_content Rendered HTML.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function fairflock_render_bento_group( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'core/group' !== $block['blockName'] ) {
		return $block_content;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';

	if ( ! fairflock_bento_has_class( $class, 'ff-bento' ) || fairflock_bento_has_class( $class, 'ff-bento-card' ) ) {
		return $block_content;
	}

	$mode = fairflock_bento_parse_mode( $class );
	if ( 'manual' === $mode ) {
		return $block_content;
	}

	$live = '';
	if ( 'collections' === $mode ) {
		$live = fairflock_bento_collections_cards_html();
	} elseif ( 'products' === $mode ) {
		$live = fairflock_bento_products_cards_html( fairflock_bento_parse_category_id( $class ) );
	} elseif ( 'posts' === $mode ) {
		$live = fairflock_bento_posts_cards_html( fairflock_bento_parse_post_ids( $class ) );
	}

	if ( '' === $live ) {
		return $block_content;
	}

	$replaced = fairflock_bento_replace_group_inner(
		$block_content,
		'<div\b(?=[^>]*\bff-bento\b)(?![^>]*\bff-bento-card\b)[^>]*>',
		$live
	);

	return is_string( $replaced ) ? $replaced : $block_content;
}
add_filter( 'render_block_core/group', 'fairflock_render_bento_group', 20, 2 );
