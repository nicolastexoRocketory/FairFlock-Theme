<?php
/**
 * One-time site defaults applied when FairFlock is activated.
 *
 * Content width lives in theme.json. Permalinks and Reading settings
 * are set once so shops start with a sensible baseline; site owners
 * can still change them afterwards.
 *
 * @package FairFlock
 * @since 0.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option key for applied defaults version.
 *
 * @since 0.2.1
 */
define( 'FAIRFLOCK_SITE_DEFAULTS_VERSION', '3' );

/**
 * Applies site defaults after the theme is switched on.
 *
 * @since 0.2.1
 *
 * @return void
 */
function fairflock_on_theme_switch() {
	fairflock_apply_site_defaults( true );
}
add_action( 'after_switch_theme', 'fairflock_on_theme_switch' );

/**
 * Applies defaults once if the theme is already active (e.g. Playground mount).
 *
 * @since 0.2.1
 *
 * @return void
 */
function fairflock_maybe_apply_site_defaults() {
	if ( get_option( 'fairflock_site_defaults_version' ) === FAIRFLOCK_SITE_DEFAULTS_VERSION ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	fairflock_apply_site_defaults( false );
}
add_action( 'init', 'fairflock_maybe_apply_site_defaults', 20 );

/**
 * Sets permalinks, creates the front page, and configures Reading settings.
 *
 * @since 0.2.1
 *
 * @param bool $force Re-apply even if the version option is already set.
 * @return void
 */
function fairflock_apply_site_defaults( $force = false ) {
	if ( ! $force && get_option( 'fairflock_site_defaults_version' ) === FAIRFLOCK_SITE_DEFAULTS_VERSION ) {
		return;
	}

	// Permalinks: /beitragsname/
	if ( get_option( 'permalink_structure' ) !== '/%postname%/' ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules( false );
	}

	$front_page_id = fairflock_ensure_front_page();

	if ( $front_page_id > 0 ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_page_id );
		fairflock_maybe_seed_pattern_front_page( $front_page_id );
	}

	fairflock_defaults_ensure_legal_pages();

	update_option( 'fairflock_site_defaults_version', FAIRFLOCK_SITE_DEFAULTS_VERSION );
}

/**
 * Seeds the front page once with the Phase-5 pattern stack.
 *
 * Runs only when `fairflock_patterns_front_seeded` is unset. After that,
 * editors keep their own page content through theme updates.
 *
 * @since 0.6.0
 *
 * @param int $page_id Front page ID.
 * @return void
 */
function fairflock_maybe_seed_pattern_front_page( $page_id ) {
	$page_id = (int) $page_id;

	if ( $page_id <= 0 ) {
		return;
	}

	if ( get_option( 'fairflock_patterns_front_seeded' ) === '1' ) {
		return;
	}

	$result = wp_update_post(
		array(
			'ID'           => $page_id,
			'post_content' => fairflock_get_default_front_page_content(),
		),
		true
	);

	if ( ! is_wp_error( $result ) ) {
		update_option( 'fairflock_patterns_front_seeded', '1' );
	}
}

/**
 * Ensures a published "Startseite" exists and returns its ID.
 *
 * Layout lives in the page content (editable in the page editor),
 * not only in a theme template file.
 *
 * @since 0.2.1
 *
 * @return int
 */
function fairflock_ensure_front_page() {
	$existing_id = (int) get_option( 'page_on_front' );

	if ( $existing_id > 0 && get_post_status( $existing_id ) === 'publish' ) {
		return $existing_id;
	}

	$found = get_page_by_path( 'startseite' );
	if ( $found instanceof WP_Post && $found->post_status === 'publish' ) {
		return (int) $found->ID;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => __( 'Startseite', 'fairflock' ),
			'post_name'    => 'startseite',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => fairflock_get_default_front_page_content(),
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		return 0;
	}

	return (int) $page_id;
}

/**
 * Ensures placeholder legal pages exist for footer links.
 *
 * Does not overwrite existing published pages (editors keep their content)
 * unless called with overwrite from Demo Content.
 *
 * @since 0.9.11
 *
 * @return void
 */
function fairflock_defaults_ensure_legal_pages() {
	if ( function_exists( 'fairflock_ensure_legal_pages' ) ) {
		fairflock_ensure_legal_pages( false );
	}
}

/**
 * Placeholder content for legal / info pages — see inc/legal-pages.php.
 *
 * Kept as thin wrapper for older call sites.
 *
 * @since 0.9.11
 * @deprecated 0.9.158 Use fairflock_get_legal_page_blueprints() in legal-pages.php.
 *
 * @return array<string, array{title: string, content: string}>
 */
function fairflock_defaults_legal_page_blueprints() {
	return function_exists( 'fairflock_get_legal_page_blueprints' )
		? fairflock_get_legal_page_blueprints()
		: array();
}

/**
 * Ordered homepage section pattern slugs (excluding live template-parts).
 *
 * Stack is assembled in fairflock_get_default_front_page_content().
 *
 * @since 0.2.1
 * @since 0.9.171 Explicit order: products → media → fullbleed → collections → fullbleed 2 → …
 * @since 0.9.172 Fullbleed 3 before steps.
 *
 * @return array<int, string>
 */
function fairflock_get_front_page_section_slugs() {
	return array(
		class_exists( 'WooCommerce' ) ? 'fairflock/product-collection' : 'fairflock/products-teaser',
		'fairflock/media-split',
		'fairflock/quote',
		// collections template-part inserted between quote and quote-alt
		'fairflock/quote-alt',
		'fairflock/categories-bento',
		'fairflock/brand-band',
		'fairflock/quote-3',
		'fairflock/steps',
		'fairflock/faq',
		'fairflock/cta-end',
	);
}

/**
 * Default Startseite content: products, text/image, fullbleeds, collections, rest.
 *
 * @since 0.2.1
 * @since 0.9.133 Collections as template-part so product_cat stays the single source.
 * @since 0.9.171 Text/Bild + Vollbild under products; second Vollbild under Kategorien.
 * @since 0.9.172 Third Vollbild before steps.
 *
 * @return string
 */
function fairflock_get_default_front_page_content() {
	$slugs = fairflock_get_front_page_section_slugs();
	$parts = array();

	foreach ( $slugs as $slug ) {
		if ( 'fairflock/quote-alt' === $slug ) {
			// Kategorien (live) sitzt vor dem zweiten Vollbild.
			$parts[] = '<!-- wp:template-part {"slug":"collections","theme":"fairflock","area":"uncategorized"} /-->';
		}

		$content = fairflock_get_registered_pattern_content( $slug );
		if ( $content !== '' ) {
			$parts[] = $content;
		}
	}

	$parts[] = '<!-- wp:template-part {"slug":"reviews","theme":"fairflock","area":"uncategorized"} /-->';

	return implode( "\n\n", $parts );
}

/**
 * Returns registered pattern markup for a slug.
 *
 * @since 0.9.31
 *
 * @param string $slug Pattern slug.
 * @return string
 */
function fairflock_get_registered_pattern_content( $slug ) {
	if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return '';
	}

	$registry = WP_Block_Patterns_Registry::get_instance();
	if ( ! $registry->is_registered( $slug ) ) {
		return '';
	}

	$pattern = $registry->get_registered( $slug );
	if ( empty( $pattern['content'] ) || ! is_string( $pattern['content'] ) ) {
		return '';
	}

	return trim( $pattern['content'] );
}

/**
 * Replaces wp:pattern references with concrete block markup.
 *
 * @since 0.9.31
 *
 * @param string $content Post content.
 * @return string
 */
function fairflock_expand_pattern_blocks( $content ) {
	if ( ! is_string( $content ) || $content === '' || ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return is_string( $content ) ? $content : '';
	}

	$skip = array(
		'fairflock/collections',
		'fairflock/category-overview',
		'fairflock/reviews',
	);

	return (string) preg_replace_callback(
		'/<!--\s*wp:pattern\s+(\{.*?\})\s*\/-->/',
		static function ( $matches ) use ( $skip ) {
			$attrs = json_decode( $matches[1], true );
			if ( ! is_array( $attrs ) || empty( $attrs['slug'] ) ) {
				return '';
			}

			$slug = (string) $attrs['slug'];
			// Keep dynamic category-driven patterns live (single source of truth).
			if ( in_array( $slug, $skip, true ) ) {
				return $matches[0];
			}

			return fairflock_get_registered_pattern_content( $slug );
		},
		$content
	);
}

/**
 * One-time: expand leftover `wp:pattern` refs into concrete block markup.
 *
 * Never rebuilds the front page from theme defaults. Page content belongs to
 * the customer after the initial seed — theme updates must not wipe Site Editor
 * / page customizations. Ship layout and styling fixes via CSS, JS, template
 * parts, and patterns (for new inserts only).
 *
 * @since 0.9.31
 * @since 0.9.48 Stop versioned full-page rebuilds; preserve customer content.
 *
 * @return void
 */
function fairflock_maybe_expand_front_page_patterns() {
	$done = 'locked';

	if ( get_option( 'fairflock_front_patterns_expanded' ) === $done ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( $page_id <= 0 ) {
		update_option( 'fairflock_front_patterns_expanded', $done );
		return;
	}

	$content = get_post_field( 'post_content', $page_id );
	if ( ! is_string( $content ) || $content === '' ) {
		update_option( 'fairflock_front_patterns_expanded', $done );
		return;
	}

	// Expand pattern placeholders in place only — do not replace the page.
	if ( str_contains( $content, 'wp:pattern' ) ) {
		$expanded = trim( (string) fairflock_expand_pattern_blocks( $content ) );
		if ( $expanded !== '' && $expanded !== $content ) {
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => $expanded,
				),
				true
			);
		}
	}

	update_option( 'fairflock_front_patterns_expanded', $done );
}
add_action( 'init', 'fairflock_maybe_expand_front_page_patterns', 30 );

/**
 * One-time: convert footer „Sold by“ Custom HTML into editable Group/Paragraph blocks.
 *
 * Customized footer template parts in the DB otherwise keep the old HTML block
 * even though parts/footer.html already uses normal blocks.
 *
 * @since 0.9.88
 *
 * @return void
 */
function fairflock_maybe_migrate_footer_sold_by_blocks() {
	if ( get_option( 'fairflock_footer_soldby_blocks' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$replacement = '<!-- wp:group {"className":"ff-footer__sold-by","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group ff-footer__sold-by">
				<!-- wp:paragraph {"className":"ff-footer__sold-by-mark"} -->
				<p class="ff-footer__sold-by-mark" aria-hidden="true">FF</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph -->
				<p>Verkauf und Abwicklung durch FairFlock GmbH</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->';

	$parts = get_posts(
		array(
			'post_type'      => 'wp_template_part',
			'name'           => 'footer',
			'post_status'    => array( 'publish', 'draft', 'auto-draft' ),
			'posts_per_page' => 20,
			'no_found_rows'  => true,
		)
	);

	foreach ( $parts as $part ) {
		if ( ! $part instanceof WP_Post || ! is_string( $part->post_content ) ) {
			continue;
		}

		if ( ! str_contains( $part->post_content, 'ff-footer__sold-by' ) || ! str_contains( $part->post_content, 'wp:html' ) ) {
			continue;
		}

		$updated = preg_replace(
			'/<!--\s*wp:html\s*-->\s*<div class="ff-footer__sold-by">.*?<\/div>\s*<!--\s*\/wp:html\s*-->/s',
			$replacement,
			$part->post_content,
			1
		);

		if ( ! is_string( $updated ) || $updated === $part->post_content ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => (int) $part->ID,
				'post_content' => $updated,
			),
			true
		);
	}

	update_option( 'fairflock_footer_soldby_blocks', '1' );
}
add_action( 'init', 'fairflock_maybe_migrate_footer_sold_by_blocks', 31 );

/**
 * One-time: switch customized footers from dark (contrast/white) to light (base/contrast).
 *
 * Theme file parts/footer.html already uses base + contrast; DB customizations
 * from the old styleguide kept contrast background + white text.
 *
 * @since 0.9.166
 *
 * @return void
 */
function fairflock_maybe_migrate_footer_light_defaults() {
	if ( get_option( 'fairflock_footer_light_defaults' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$parts = get_posts(
		array(
			'post_type'      => 'wp_template_part',
			'name'           => 'footer',
			'post_status'    => array( 'publish', 'draft', 'auto-draft' ),
			'posts_per_page' => 20,
			'no_found_rows'  => true,
		)
	);

	foreach ( $parts as $part ) {
		if ( ! $part instanceof WP_Post || ! is_string( $part->post_content ) || '' === $part->post_content ) {
			continue;
		}

		$content = $part->post_content;
		if ( ! str_contains( $content, 'ff-footer' ) ) {
			continue;
		}

		// Only migrate the classic dark styleguide combo.
		$is_dark = str_contains( $content, '"backgroundColor":"contrast"' )
			|| str_contains( $content, 'has-contrast-background-color' );
		if ( ! $is_dark ) {
			continue;
		}

		$updated = $content;

		// Block JSON attrs on root footer group.
		$updated = str_replace( '"backgroundColor":"contrast"', '"backgroundColor":"base"', $updated );
		$updated = str_replace( '"textColor":"white"', '"textColor":"contrast"', $updated );

		// Rendered HTML classes on root footer.
		$updated = preg_replace(
			'/(class="[^"]*\bff-footer\b[^"]*)\bhas-contrast-background-color\b/',
			'$1has-base-background-color',
			$updated,
			1
		);
		$updated = preg_replace(
			'/(class="[^"]*\bff-footer\b[^"]*)\bhas-white-color\b/',
			'$1has-contrast-color',
			$updated,
			1
		);

		// Social icons: white → contrast for light surface.
		$updated = str_replace( '"iconColor":"white"', '"iconColor":"contrast"', $updated );
		$updated = str_replace( '"iconColorValue":"#ffffff"', '"iconColorValue":"#0e1114"', $updated );
		$updated = str_replace( '"iconColorValue":"#fff"', '"iconColorValue":"#0e1114"', $updated );

		// Headings / copy that were forced white.
		$updated = str_replace(
			'"className":"ff-footer__heading","textColor":"white"',
			'"className":"ff-footer__heading","textColor":"contrast"',
			$updated
		);
		$updated = str_replace(
			'ff-footer__heading has-white-color',
			'ff-footer__heading has-contrast-color',
			$updated
		);

		if ( ! is_string( $updated ) || $updated === $content ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => (int) $part->ID,
				'post_content' => $updated,
			),
			true
		);
	}

	update_option( 'fairflock_footer_light_defaults', '1' );
}
add_action( 'init', 'fairflock_maybe_migrate_footer_light_defaults', 32 );

/**
 * Reviews: Score-Card entfernen, Titel „4,9 bei Google“ setzen.
 *
 * @return void
 */
function fairflock_maybe_migrate_reviews_google_heading() {
	if ( get_option( 'fairflock_reviews_google_heading' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( $page_id <= 0 ) {
		update_option( 'fairflock_reviews_google_heading', '1' );
		return;
	}

	$post = get_post( $page_id );
	if ( ! $post instanceof WP_Post || ! is_string( $post->post_content ) ) {
		update_option( 'fairflock_reviews_google_heading', '1' );
		return;
	}

	$content = $post->post_content;
	$updated = $content;

	// Score-Card-Block entfernen (inkl. verschachtelter Absätze).
	$updated = preg_replace(
		'/<!--\s*wp:group\s*\{[^}]*"className"\s*:\s*"ff-reviews-score"[^}]*\}\s*-->.*?<!--\s*\/wp:group\s*-->\s*/s',
		'',
		$updated,
		1
	);

	// Fallback: HTML ohne vollständigen Block-Comment.
	$updated = preg_replace(
		'/<div class="[^"]*\bff-reviews-score\b[^"]*"[\s\S]*?<\/div>\s*(?=<!--\s*wp:group[^>]*ff-reviews-slider|<!--\s*\/wp:group|<div class="[^"]*\bff-reviews-slider)/',
		'',
		$updated,
		1
	);

	$updated = str_replace( 'Das sagen Kunden', '4,9 bei Google', $updated );

	// Heading-Klasse setzen, falls noch fehlend.
	if ( str_contains( $updated, '4,9 bei Google' ) && ! str_contains( $updated, 'ff-reviews-heading' ) ) {
		$updated = preg_replace(
			'/(<!--\s*wp:heading)(\s*\{[^}]*\})?(\s*-->\s*<h2 class="wp-block-heading)(">)(\s*4,9 bei Google\s*<\/h2>)/',
			'$1 {"className":"ff-reviews-heading"}$3 ff-reviews-heading$4$5',
			$updated,
			1
		);
		if ( ! is_string( $updated ) ) {
			$updated = $content;
		}
	}

	if ( is_string( $updated ) && $updated !== $content ) {
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $updated,
			),
			true
		);
	}

	update_option( 'fairflock_reviews_google_heading', '1' );
}
add_action( 'init', 'fairflock_maybe_migrate_reviews_google_heading', 32 );

/**
 * Recursively drop review score-card groups (invalid leftovers in the editor).
 *
 * @since 0.9.139
 *
 * @param array<int, array<string, mixed>> $blocks Parsed blocks.
 * @return array{0: array<int, array<string, mixed>>, 1: bool}
 */
function fairflock_strip_reviews_score_blocks( array $blocks ) {
	$out     = array();
	$changed = false;

	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		$name  = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
		$class = '';
		if ( isset( $block['attrs']['className'] ) && is_string( $block['attrs']['className'] ) ) {
			$class = $block['attrs']['className'];
		}
		$html = isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ? $block['innerHTML'] : '';

		if ( 'core/group' === $name && str_contains( $class, 'ff-reviews-score' ) ) {
			$changed = true;
			continue;
		}

		// Classic/freeform crumbs left after a partial score-card removal.
		if ( ( '' === $name || 'core/freeform' === $name || 'core/html' === $name ) && str_contains( $html, 'ff-reviews-score' ) ) {
			$changed = true;
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			list( $inner, $inner_changed ) = fairflock_strip_reviews_score_blocks( $block['innerBlocks'] );
			if ( $inner_changed ) {
				$changed              = true;
				$block['innerBlocks'] = $inner;

				$serialized = '';
				foreach ( $inner as $inner_block ) {
					$serialized .= serialize_block( $inner_block );
				}

				$open  = '';
				$close = '';
				if ( preg_match( '/^(<div\b[^>]*>)/i', $html, $m_open ) ) {
					$open = $m_open[1];
				}
				if ( preg_match( '/(<\/div>\s*)$/i', $html, $m_close ) ) {
					$close = $m_close[1];
				}

				$block['innerHTML']    = $open . $serialized . $close;
				$block['innerContent'] = array( $open );
				foreach ( $inner as $_ignored ) {
					$block['innerContent'][] = null;
				}
				$block['innerContent'][] = $close;
			}
		}

		$out[] = $block;
	}

	return array( $out, $changed );
}

/**
 * One-time: remove leftover ff-reviews-score blocks from the front page.
 *
 * @since 0.9.139
 *
 * @return void
 */
function fairflock_maybe_purge_reviews_score_blocks() {
	if ( get_option( 'fairflock_reviews_score_purged' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( $page_id <= 0 ) {
		update_option( 'fairflock_reviews_score_purged', '1' );
		return;
	}

	$post = get_post( $page_id );
	if ( ! $post instanceof WP_Post || ! is_string( $post->post_content ) || $post->post_content === '' ) {
		update_option( 'fairflock_reviews_score_purged', '1' );
		return;
	}

	if ( ! str_contains( $post->post_content, 'ff-reviews-score' ) ) {
		update_option( 'fairflock_reviews_score_purged', '1' );
		return;
	}

	$blocks = parse_blocks( $post->post_content );
	list( $next, $changed ) = fairflock_strip_reviews_score_blocks( $blocks );

	if ( $changed ) {
		$serialized = serialize_blocks( $next );
		if ( is_string( $serialized ) && $serialized !== '' ) {
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => $serialized,
				),
				true
			);
		}
	}

	update_option( 'fairflock_reviews_score_purged', '1' );
}
add_action( 'init', 'fairflock_maybe_purge_reviews_score_blocks', 34 );

/**
 * One-time: reset customized collections template-part if it still has invalid group styles.
 *
 * Cards use a live pattern; DB forks with `--ff-cat-img` on core/group fail validation.
 *
 * @since 0.9.139
 *
 * @return void
 */
function fairflock_maybe_reset_collections_part_invalid_styles() {
	if ( get_option( 'fairflock_collections_part_bg_fix' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$parts = get_posts(
		array(
			'post_type'      => 'wp_template_part',
			'post_status'    => array( 'publish', 'draft', 'auto-draft' ),
			'posts_per_page' => 20,
			'name'           => 'collections',
		)
	);

	$fresh = '<!-- wp:pattern {"slug":"fairflock/collections"} /-->';

	foreach ( $parts as $part ) {
		if ( ! $part instanceof WP_Post || ! is_string( $part->post_content ) ) {
			continue;
		}
		if ( ! str_contains( $part->post_content, '--ff-cat-img' ) && ! str_contains( $part->post_content, 'ff-collection-card' ) ) {
			continue;
		}
		// Only rewrite forks that still embed cards (or the invalid CSS var), not unrelated edits.
		if (
			str_contains( $part->post_content, '--ff-cat-img' )
			|| ( str_contains( $part->post_content, 'ff-collection-card' ) && ! str_contains( $part->post_content, 'fairflock/collections' ) )
		) {
			wp_update_post(
				array(
					'ID'           => (int) $part->ID,
					'post_content' => $fresh,
				),
				true
			);
		}
	}

	update_option( 'fairflock_collections_part_bg_fix', '1' );
}
add_action( 'init', 'fairflock_maybe_reset_collections_part_invalid_styles', 37 );

/**
 * Produkte-Sektion: „Alle Artikel“ wieder als Textlink (wie Kollektionen/Bento).
 *
 * @return void
 */
function fairflock_maybe_migrate_products_more_link() {
	if ( get_option( 'fairflock_products_more_link' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( $page_id <= 0 ) {
		update_option( 'fairflock_products_more_link', '1' );
		return;
	}

	$post = get_post( $page_id );
	if ( ! $post instanceof WP_Post || ! is_string( $post->post_content ) ) {
		update_option( 'fairflock_products_more_link', '1' );
		return;
	}

	$content = $post->post_content;
	$updated = $content;

	// Buttons-Block → Textlink wie bei Kollektionen.
	$updated = preg_replace(
		'/<!--\s*wp:buttons\s*\{[^}]*"className"\s*:\s*"ff-pattern-products__more"[^}]*\}\s*-->\s*<div class="wp-block-buttons ff-pattern-products__more">\s*<!--\s*wp:button[^>]*-->\s*<div class="[^"]*ff-pattern-products__more-btn[^"]*"><a class="[^"]*" href="([^"]*)">([^<]*)<\/a><\/div>\s*<!--\s*\/wp:button\s*-->\s*<\/div>\s*<!--\s*\/wp:buttons\s*-->/s',
		'<!-- wp:paragraph {"className":"ff-pattern-link-more","style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->' . "\n" .
		'<p class="ff-pattern-link-more" style="font-style:normal;font-weight:700"><a href="$1">$2</a></p>' . "\n" .
		'<!-- /wp:paragraph -->',
		$updated,
		1
	);

	// Bereits Absatz mit alter Klasse.
	$updated = str_replace(
		array(
			'className":"ff-pattern-products__more"',
			'class="ff-pattern-products__more"',
			'class="ff-pattern-products__more wp-block-paragraph"',
			'Alle Artikel</a>',
		),
		array(
			'className":"ff-pattern-link-more"',
			'class="ff-pattern-link-more"',
			'class="ff-pattern-link-more wp-block-paragraph"',
			'Alle Artikel →</a>',
		),
		$updated
	);

	if ( is_string( $updated ) && $updated !== $content ) {
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $updated,
			),
			true
		);
	}

	update_option( 'fairflock_products_more_link', '1' );
}
add_action( 'init', 'fairflock_maybe_migrate_products_more_link', 33 );

/**
 * Whether a block is the frozen homepage collections section.
 *
 * @since 0.9.133
 *
 * @param array $block Parsed block.
 * @return bool
 */
function fairflock_block_is_collections_section( $block ) {
	if ( ! is_array( $block ) || ( $block['blockName'] ?? '' ) !== 'core/group' ) {
		return false;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	if ( ( $attrs['anchor'] ?? '' ) === 'kollektionen' ) {
		return true;
	}

	$class = (string) ( $attrs['className'] ?? '' );
	return str_contains( $class, 'ff-pattern-collections' );
}

/**
 * Replace frozen collections groups with a live template-part reference.
 *
 * @since 0.9.133
 *
 * @param array $blocks Parsed blocks.
 * @return array{0: array, 1: bool} Blocks and whether a replacement happened.
 */
function fairflock_replace_collections_blocks_with_template_part( $blocks ) {
	$out      = array();
	$replaced = false;
	$part     = array(
		'blockName'    => 'core/template-part',
		'attrs'        => array(
			'slug'  => 'collections',
			'theme' => 'fairflock',
			'area'  => 'uncategorized',
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);

	foreach ( (array) $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		if ( fairflock_block_is_collections_section( $block ) ) {
			if ( ! $replaced ) {
				$out[]    = $part;
				$replaced = true;
			}
			continue;
		}

		if (
			( $block['blockName'] ?? '' ) === 'core/template-part'
			&& ( $block['attrs']['slug'] ?? '' ) === 'collections'
		) {
			$out[]    = $block;
			$replaced = true;
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			list( $inner, $inner_replaced ) = fairflock_replace_collections_blocks_with_template_part( $block['innerBlocks'] );
			$block['innerBlocks']           = $inner;
			if ( $inner_replaced ) {
				$replaced = true;
			}
		}

		$out[] = $block;
	}

	return array( $out, $replaced );
}

/**
 * One-time: Startseite + /kategorien/ auf eine live Kollektionen-Quelle umstellen.
 *
 * - Startseite: eingefrorene Collections-Sektion → Template-Part `collections`
 * - /kategorien/: Inhalt leeren, Vorlage `page-kategorien.html` liefert live Grid
 *
 * Karten kommen aus WooCommerce-Produktkategorien (eine Pflege-Stelle).
 *
 * @since 0.9.133
 *
 * @return void
 */
function fairflock_maybe_migrate_collections_template_part() {
	if ( get_option( 'fairflock_collections_template_part' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( $page_id > 0 ) {
		$post = get_post( $page_id );
		if ( $post instanceof WP_Post && is_string( $post->post_content ) && $post->post_content !== '' ) {
			$blocks = parse_blocks( $post->post_content );
			list( $next, $replaced ) = fairflock_replace_collections_blocks_with_template_part( $blocks );
			if ( $replaced ) {
				$serialized = serialize_blocks( $next );
				if ( is_string( $serialized ) && $serialized !== $post->post_content ) {
					wp_update_post(
						array(
							'ID'           => $page_id,
							'post_content' => $serialized,
						),
						true
					);
				}
			}
		}
	}

	$cats = get_page_by_path( 'kategorien' );
	if ( $cats instanceof WP_Post ) {
		// Template page-kategorien.html owns title + live pattern; avoid duplicate frozen grid.
		if ( is_string( $cats->post_content ) && $cats->post_content !== '' ) {
			wp_update_post(
				array(
					'ID'           => (int) $cats->ID,
					'post_content' => '',
				),
				true
			);
		}
	}

	update_option( 'fairflock_collections_template_part', '1' );
}
add_action( 'init', 'fairflock_maybe_migrate_collections_template_part', 36 );

/**
 * Whether a block is the frozen homepage reviews section.
 *
 * @since 0.9.139
 *
 * @param array<string, mixed> $block Parsed block.
 * @return bool
 */
function fairflock_block_is_reviews_section( $block ) {
	if ( ! is_array( $block ) ) {
		return false;
	}
	if ( ( $block['blockName'] ?? '' ) !== 'core/group' ) {
		return false;
	}
	$class = '';
	if ( isset( $block['attrs']['className'] ) && is_string( $block['attrs']['className'] ) ) {
		$class = $block['attrs']['className'];
	}
	return str_contains( $class, 'ff-pattern-reviews' );
}

/**
 * Replace frozen reviews groups with a live template-part reference.
 *
 * @since 0.9.139
 *
 * @param array<int, array<string, mixed>> $blocks Parsed blocks.
 * @return array{0: array<int, array<string, mixed>>, 1: bool}
 */
function fairflock_replace_reviews_blocks_with_template_part( $blocks ) {
	$out      = array();
	$replaced = false;
	$ref      = array(
		'blockName'    => 'core/template-part',
		'attrs'        => array(
			'slug'  => 'reviews',
			'theme' => 'fairflock',
			'area'  => 'uncategorized',
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);

	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		if ( fairflock_block_is_reviews_section( $block ) ) {
			$out[]    = $ref;
			$replaced = true;
			continue;
		}

		if (
			( $block['blockName'] ?? '' ) === 'core/template-part'
			&& ( $block['attrs']['slug'] ?? '' ) === 'reviews'
		) {
			$out[] = $block;
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			list( $inner, $inner_replaced ) = fairflock_replace_reviews_blocks_with_template_part( $block['innerBlocks'] );
			if ( $inner_replaced ) {
				$replaced             = true;
				$block['innerBlocks'] = $inner;
				$serialized           = '';
				foreach ( $inner as $inner_block ) {
					$serialized .= serialize_block( $inner_block );
				}
				$html  = isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ? $block['innerHTML'] : '';
				$open  = preg_match( '/^(<div\b[^>]*>)/i', $html, $m_open ) ? $m_open[1] : '';
				$close = preg_match( '/(<\/div>\s*)$/i', $html, $m_close ) ? $m_close[1] : '';
				$block['innerHTML']    = $open . $serialized . $close;
				$block['innerContent'] = array( $open );
				foreach ( $inner as $_ignored ) {
					$block['innerContent'][] = null;
				}
				$block['innerContent'][] = $close;
			}
		}

		$out[] = $block;
	}

	return array( $out, $replaced );
}

/**
 * One-time: Startseite Bewertungen → live Template-Part (fixes editor validation).
 *
 * Frozen constrained shell markup lacked layout classes expected by current core/group.
 *
 * @since 0.9.139
 *
 * @return void
 */
function fairflock_maybe_migrate_reviews_template_part() {
	if ( get_option( 'fairflock_reviews_template_part' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( $page_id > 0 ) {
		$post = get_post( $page_id );
		if ( $post instanceof WP_Post && is_string( $post->post_content ) && $post->post_content !== '' ) {
			$blocks = parse_blocks( $post->post_content );
			list( $next, $replaced ) = fairflock_replace_reviews_blocks_with_template_part( $blocks );
			if ( $replaced ) {
				$serialized = serialize_blocks( $next );
				if ( is_string( $serialized ) && $serialized !== $post->post_content ) {
					wp_update_post(
						array(
							'ID'           => $page_id,
							'post_content' => $serialized,
						),
						true
					);
				}
			}
		}
	}

	update_option( 'fairflock_reviews_template_part', '1' );
}
add_action( 'init', 'fairflock_maybe_migrate_reviews_template_part', 38 );
