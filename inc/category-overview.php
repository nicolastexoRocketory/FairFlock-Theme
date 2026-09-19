<?php
/**
 * Category overview page — live product categories as a card grid.
 *
 * @package FairFlock
 * @since 0.9.99
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product categories for the overview (skips Uncategorized / shop default).
 *
 * @since 0.9.99
 * @since 0.9.250 Optional extra term IDs to exclude.
 *
 * @param array<int, int> $extra_exclude Extra term IDs to exclude.
 * @return array<int, WP_Term>
 */
function fairflock_get_category_overview_terms( $extra_exclude = array() ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$exclude = array();
	$default = (int) get_option( 'default_product_cat', 0 );
	if ( $default > 0 ) {
		$exclude[] = $default;
	}

	if ( is_array( $extra_exclude ) ) {
		foreach ( $extra_exclude as $id ) {
			$id = (int) $id;
			if ( $id > 0 ) {
				$exclude[] = $id;
			}
		}
	}

	$exclude = array_values( array_unique( array_filter( array_map( 'intval', $exclude ) ) ) );

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'exclude'    => $exclude,
		)
	);

	if ( ! is_array( $terms ) ) {
		return array();
	}

	$out = array();
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		if ( $term->slug === 'uncategorized' ) {
			continue;
		}
		$out[] = $term;
	}

	return $out;
}

/**
 * Parses excluded category IDs from a collections section className.
 *
 * Token: ff-collections-exclude-{id}-{id}-…
 *
 * @since 0.9.250
 *
 * @param string $class_name Block className.
 * @return array<int, int>
 */
function fairflock_collections_parse_exclude_ids( $class_name ) {
	if ( ! preg_match( '/(?:^|\s)ff-collections-exclude-((?:\d+-)*\d+)(?:\s|$)/', (string) $class_name, $m ) ) {
		return array();
	}

	$ids = array();
	foreach ( explode( '-', $m[1] ) as $part ) {
		$id = (int) $part;
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}

	$ids = array_values( array_unique( $ids ) );
	sort( $ids, SORT_NUMERIC );

	return $ids;
}

/**
 * Block markup for one category card.
 *
 * @since 0.9.99
 * @since 0.9.133 Optional CTA label for collections vs categories.
 *
 * @param WP_Term $term       Category.
 * @param int     $index      Zero-based index (first card is brand).
 * @param string  $cta_label  Link label (default: Zur Kategorie).
 * @return string
 */
function fairflock_get_category_overview_card_markup( WP_Term $term, $index, $cta_label = '' ) {
	$link = get_term_link( $term );
	if ( is_wp_error( $link ) ) {
		$link = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	}

	$title = esc_html( $term->name );
	$href  = esc_url( (string) $link );
	$desc  = trim( wp_strip_all_tags( (string) $term->description ) );
	if ( $desc === '' ) {
		$count = (int) $term->count;
		$desc  = sprintf(
			/* translators: %s: product count */
			_n( '%s Artikel', '%s Artikel', $count, 'fairflock' ),
			number_format_i18n( $count )
		);
	}
	$desc = esc_html( $desc );

	if ( $cta_label === '' ) {
		$cta_label = __( 'Zur Kategorie →', 'fairflock' );
	}
	$cta = esc_html( $cta_label );

	$classes = array( 'ff-collection-card' );
	$bg_html = '';

	$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	$image    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';
	if ( is_string( $image ) && $image !== '' ) {
		$classes[] = 'has-cat-image';
		$img_url   = esc_url( $image );
		// Background via wp:html — custom style on core/group breaks block validation in the editor.
		$bg_html   = <<<HTML
				<!-- wp:html -->
				<div class="ff-collection-card__bg" style="background-image:url({$img_url})" aria-hidden="true"></div>
				<!-- /wp:html -->
HTML;
	} elseif ( 0 === (int) $index ) {
		$classes[] = 'is-brand';
	} else {
		$classes[] = 'ff-collection-card--' . ( ( (int) $index % 8 ) + 1 );
	}

	$class_attr = esc_attr( implode( ' ', $classes ) );
	$aria       = esc_attr(
		sprintf(
			/* translators: %s: category/collection name */
			__( '%s – Zur Kollektion', 'fairflock' ),
			$term->name
		)
	);

	return <<<HTML
			<!-- wp:group {"className":"{$class_attr}","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group {$class_attr}">
{$bg_html}				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading">{$title}</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph -->
				<p>{$desc}</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"ff-collection-card__meta"} -->
				<p class="ff-collection-card__meta"><a href="{$href}" aria-label="{$aria}">{$cta}</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
HTML;
}

/**
 * Card markup only (for homepage collections grid).
 *
 * @since 0.9.133
 * @since 0.9.250 Optional category IDs to exclude.
 *
 * @param array<int, int> $exclude Extra term IDs to exclude.
 * @return string
 */
function fairflock_get_collections_cards_markup( $exclude = array() ) {
	$shop  = esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
	$terms = fairflock_get_category_overview_terms( $exclude );
	$cta   = __( 'Zur Kollektion →', 'fairflock' );
	$cards = '';

	if ( $terms ) {
		foreach ( $terms as $i => $term ) {
			$cards .= fairflock_get_category_overview_card_markup( $term, (int) $i, $cta ) . "\n";
		}
		return $cards;
	}

	$placeholders = array(
		array( 'FairFlock Essentials', 'Die Kernstücke deiner Marke — klar, stark, wiedererkennbar.', 'is-brand' ),
		array( 'Neuheiten', 'Frisch im Sortiment.', 'ff-collection-card--1' ),
		array( 'Bestsellers', 'Die starken Dauerbrenner.', 'ff-collection-card--2' ),
	);
	foreach ( $placeholders as $item ) {
		$mod   = esc_attr( $item[2] );
		$title = esc_html( $item[0] );
		$text  = esc_html( $item[1] );
		$label = esc_html( $cta );
		$aria  = esc_attr(
			sprintf(
				/* translators: %s: category/collection name */
				__( '%s – Zur Kollektion', 'fairflock' ),
				$item[0]
			)
		);
		$cards .= <<<HTML
			<!-- wp:group {"className":"ff-collection-card {$mod}","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group ff-collection-card {$mod}">
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading">{$title}</h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p>{$text}</p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"className":"ff-collection-card__meta"} -->
				<p class="ff-collection-card__meta"><a href="{$shop}" aria-label="{$aria}">{$label}</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

HTML;
	}

	return $cards;
}

/**
 * Replaces the collections grid inner HTML with live category cards.
 *
 * @since 0.9.250
 *
 * @param string $html     Rendered section HTML.
 * @param string $inner    Replacement inner HTML.
 * @return string|null
 */
function fairflock_collections_replace_grid_inner( $html, $inner ) {
	if ( ! is_string( $html ) || $html === '' || ! is_string( $inner ) ) {
		return null;
	}

	if ( ! preg_match( '/<div\b(?=[^>]*\bff-collections-grid\b)[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		return null;
	}

	$open_tag   = $m[0][0];
	$open_start = (int) $m[0][1];
	$inner_start = $open_start + strlen( $open_tag );
	$pos         = $inner_start;
	$depth       = 1;
	$len         = strlen( $html );

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
 * Live-replaces collections grid cards on the frontend (respects exclusions).
 *
 * @since 0.9.250
 *
 * @param string               $block_content Rendered HTML.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function fairflock_render_collections_section( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'core/group' !== $block['blockName'] ) {
		return $block_content;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';

	if ( ! str_contains( $class, 'ff-pattern-collections' ) ) {
		return $block_content;
	}

	$exclude = fairflock_collections_parse_exclude_ids( $class );
	$markup  = fairflock_get_collections_cards_markup( $exclude );
	$live    = do_blocks( $markup );

	if ( ! is_string( $live ) || $live === '' ) {
		return $block_content;
	}

	$replaced = fairflock_collections_replace_grid_inner( $block_content, $live );

	return is_string( $replaced ) ? $replaced : $block_content;
}
add_filter( 'render_block_core/group', 'fairflock_render_collections_section', 20, 2 );

/**
 * Full collections section markup (homepage widget + /kategorien/).
 *
 * @since 0.9.142
 *
 * @param array<string, mixed> $args {
 *     @type bool   $show_more_link Show “Alle Produkte →” (default true).
 *     @type string $heading        Section heading.
 *     @type string $lead           Section lead.
 *     @type string $anchor         Optional HTML id / block anchor.
 *     @type string $extra_class    Extra section class.
 * }
 * @return string
 */
function fairflock_get_collections_section_markup( array $args = array() ) {
	$show_more = ! isset( $args['show_more_link'] ) || (bool) $args['show_more_link'];
	$heading   = isset( $args['heading'] ) ? (string) $args['heading'] : __( 'Kollektionen', 'fairflock' );
	$lead      = isset( $args['lead'] ) ? (string) $args['lead'] : __( 'Alle Welten auf einen Blick — von Neuheiten bis Klassikern.', 'fairflock' );
	$anchor    = isset( $args['anchor'] ) ? (string) $args['anchor'] : 'kollektionen';
	$extra     = isset( $args['extra_class'] ) ? trim( (string) $args['extra_class'] ) : '';

	$shop        = esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
	$section_cls = trim( 'ff-pattern-collections ' . $extra );
	$cards       = fairflock_get_collections_cards_markup( fairflock_collections_parse_exclude_ids( $section_cls ) );

	$heading_esc = esc_html( $heading );
	$lead_esc    = esc_html( $lead );
	$anchor_esc  = esc_attr( $anchor );
	$expand_more      = __( 'Mehr anzeigen', 'fairflock' );
	$expand_less      = __( 'Weniger anzeigen', 'fairflock' );
	$expand_more_esc  = esc_html( $expand_more );
	$expand_more_attr = esc_attr( $expand_more );
	$expand_less_attr = esc_attr( $expand_less );

	$more_html = '';
	if ( $show_more ) {
		$more_label = esc_html__( 'Alle Produkte →', 'fairflock' );
		$more_html  = <<<HTML
				<!-- wp:paragraph {"className":"ff-pattern-link-more","style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->
				<p class="ff-pattern-link-more" style="font-style:normal;font-weight:700"><a href="{$shop}">{$more_label}</a></p>
				<!-- /wp:paragraph -->
HTML;
	}

	$anchor_attr = $anchor !== '' ? ',"anchor":"' . $anchor_esc . '"' : '';
	$id_attr     = $anchor !== '' ? ' id="' . $anchor_esc . '"' : '';

	return <<<HTML
<!-- wp:group {"metadata":{"name":"Kollektionen"},"align":"full","className":"{$section_cls}","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}{$anchor_attr}} -->
<div{$id_attr} class="wp-block-group alignfull {$section_cls}" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-pattern-collections__shell","style":{"spacing":{"padding":{"left":"0","right":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-pattern-collections__shell">
		<!-- wp:group {"className":"ff-pattern-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
		<div class="wp-block-group ff-pattern-section-head">
			<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"constrained","contentSize":"34rem"}} -->
			<div class="wp-block-group">
				<!-- wp:heading -->
				<h2 class="wp-block-heading">{$heading_esc}</h2>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"muted"} -->
				<p class="has-muted-color has-text-color">{$lead_esc}</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"ff-card-slider__actions","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right","verticalAlignment":"center"}} -->
			<div class="wp-block-group ff-card-slider__actions">
				<!-- wp:buttons {"metadata":{"name":"Slider-Pfeile"},"className":"ff-card-slider__nav","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-buttons ff-card-slider__nav">
					<!-- wp:button {"metadata":{"name":"Zurück"},"className":"ff-card-slider__btn ff-card-slider__btn--prev is-style-outline"} -->
					<div class="wp-block-button ff-card-slider__btn ff-card-slider__btn--prev is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-cards-prev">←</a></div>
					<!-- /wp:button -->

					<!-- wp:button {"metadata":{"name":"Weiter"},"className":"ff-card-slider__btn ff-card-slider__btn--next is-style-outline"} -->
					<div class="wp-block-button ff-card-slider__btn ff-card-slider__btn--next is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-cards-next">→</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

{$more_html}
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ff-collections-grid is-collapsed","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ff-collections-grid is-collapsed">
{$cards}
		</div>
		<!-- /wp:group -->

		<!-- wp:buttons {"className":"ff-collections-expand","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
		<div class="wp-block-buttons ff-collections-expand" hidden>
			<!-- wp:button {"className":"ff-collections-expand__btn is-style-outline"} -->
			<div class="wp-block-button ff-collections-expand__btn is-style-outline"><a class="wp-block-button__link wp-element-button" href="#ff-collections-more" data-ff-label-more="{$expand_more_attr}" data-ff-label-less="{$expand_less_attr}">{$expand_more_esc}</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
HTML;
}

/**
 * Full pattern markup for the category overview page (= collections layout, no shop CTA).
 *
 * @since 0.9.99
 * @since 0.9.142 Same shell/grid as homepage collections, without “Alle Produkte”.
 *
 * @return string
 */
function fairflock_get_category_overview_markup() {
	return fairflock_get_collections_section_markup(
		array(
			'show_more_link' => false,
			'heading'        => __( 'Kollektionen', 'fairflock' ),
			'lead'           => __( 'Alle Welten auf einen Blick — von Neuheiten bis Klassikern.', 'fairflock' ),
			'anchor'         => 'kollektionen',
			'extra_class'    => 'ff-pattern-collections--page',
		)
	);
}

/**
 * Page body for legacy seeds (prefer template page-kategorien.html).
 *
 * @since 0.9.99
 *
 * @return string
 */
function fairflock_get_category_overview_page_content() {
	return fairflock_get_category_overview_markup();
}

/**
 * One-time: publish /kategorien/ without overwriting an existing page.
 *
 * @since 0.9.99
 *
 * @return void
 */
function fairflock_maybe_seed_category_overview_page() {
	if ( get_option( 'fairflock_category_overview_page' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$existing = get_page_by_path( 'kategorien' );
	if ( $existing instanceof WP_Post && $existing->post_status === 'publish' ) {
		update_option( 'fairflock_category_overview_page', '1' );
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => __( 'Kategorien', 'fairflock' ),
			'post_name'    => 'kategorien',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => fairflock_get_category_overview_page_content(),
		),
		true
	);

	if ( ! is_wp_error( $page_id ) && (int) $page_id > 0 ) {
		update_option( 'fairflock_category_overview_page', '1' );
	}
}
add_action( 'init', 'fairflock_maybe_seed_category_overview_page', 35 );
