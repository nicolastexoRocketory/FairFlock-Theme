<?php
/**
 * FAQ editor UX — add/remove Details items without custom blocks.
 *
 * @package FairFlock
 * @since 0.9.52
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers a Details variation pre-styled as a FAQ row.
 *
 * @since 0.9.52
 *
 * @return void
 */
function fairflock_register_faq_details_variation() {
	if ( ! function_exists( 'register_block_variation' ) ) {
		return;
	}

	register_block_variation(
		'core/details',
		array(
			'name'        => 'fairflock-faq-item',
			'title'       => __( 'FAQ-Eintrag', 'fairflock' ),
			'description' => __( 'Frage und Antwort für die FAQ-Liste.', 'fairflock' ),
			'category'    => 'text',
			'icon'        => 'editor-help',
			'keywords'    => array( 'faq', 'frage', 'antwort', 'accordion', 'akkordion' ),
			'attributes'  => array(
				'className'   => 'ff-faq-item',
				'showContent' => true,
				'metadata'    => array(
					'name' => __( 'FAQ-Eintrag', 'fairflock' ),
				),
			),
			'innerBlocks' => array(
				array(
					'core/paragraph',
					array(
						'placeholder' => __( 'Antwort hier eingeben…', 'fairflock' ),
					),
				),
			),
			'scope'       => array( 'inserter', 'block', 'transform' ),
			'isActive'    => array( 'className' ),
		)
	);
}
add_action( 'init', 'fairflock_register_faq_details_variation' );

/**
 * Enqueues editor helpers for the FAQ list appender.
 *
 * @since 0.9.52
 *
 * @return void
 */
function fairflock_enqueue_faq_editor_assets() {
	$path = FAIRFLOCK_DIR . '/assets/js/editor-faq.js';

	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'fairflock-editor-faq',
		FAIRFLOCK_URI . '/assets/js/editor-faq.js',
		array( 'wp-blocks', 'wp-data', 'wp-hooks', 'wp-block-editor' ),
		FAIRFLOCK_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_faq_editor_assets' );

/**
 * Recursively add allowedBlocks to FAQ list groups (content preserved).
 *
 * @since 0.9.52
 *
 * @param array<int, array<string, mixed>> $blocks Parsed blocks.
 * @return array{0: array<int, array<string, mixed>>, 1: bool}
 */
function fairflock_patch_faq_list_blocks( array $blocks ) {
	$changed = false;

	foreach ( $blocks as &$block ) {
		$class = '';
		if ( isset( $block['attrs']['className'] ) && is_string( $block['attrs']['className'] ) ) {
			$class = $block['attrs']['className'];
		}

		if (
			( $block['blockName'] ?? '' ) === 'core/group'
			&& str_contains( ' ' . $class . ' ', ' ff-faq-list ' )
		) {
			$allowed = $block['attrs']['allowedBlocks'] ?? null;
			$name    = $block['attrs']['metadata']['name'] ?? '';

			if ( $allowed !== array( 'core/details' ) ) {
				$block['attrs']['allowedBlocks'] = array( 'core/details' );
				$changed                         = true;
			}

			if ( $name !== 'FAQ-Liste' ) {
				if ( ! isset( $block['attrs']['metadata'] ) || ! is_array( $block['attrs']['metadata'] ) ) {
					$block['attrs']['metadata'] = array();
				}
				$block['attrs']['metadata']['name'] = 'FAQ-Liste';
				$changed                            = true;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$result               = fairflock_patch_faq_list_blocks( $block['innerBlocks'] );
			$block['innerBlocks'] = $result[0];
			$changed              = $changed || $result[1];
		}
	}
	unset( $block );

	return array( $blocks, $changed );
}

/**
 * Soft-patch: only adds allowedBlocks/metadata to the FAQ list group wrapper.
 *
 * @since 0.9.52
 *
 * @return void
 */
function fairflock_maybe_patch_faq_list_editor_attrs() {
	if ( get_option( 'fairflock_faq_list_editor_patched' ) === '1' ) {
		return;
	}

	if ( get_stylesheet() !== 'fairflock' ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( $page_id <= 0 ) {
		update_option( 'fairflock_faq_list_editor_patched', '1' );
		return;
	}

	$content = get_post_field( 'post_content', $page_id );
	if ( ! is_string( $content ) || $content === '' || ! str_contains( $content, 'ff-faq-list' ) ) {
		update_option( 'fairflock_faq_list_editor_patched', '1' );
		return;
	}

	$parsed           = parse_blocks( $content );
	$result           = fairflock_patch_faq_list_blocks( $parsed );
	$patched_blocks   = $result[0];
	$changed          = $result[1];

	if ( $changed ) {
		$serialized = serialize_blocks( $patched_blocks );
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

	update_option( 'fairflock_faq_list_editor_patched', '1' );
}
add_action( 'init', 'fairflock_maybe_patch_faq_list_editor_attrs', 40 );
