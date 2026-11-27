<?php
/**
 * KI-Bild flag for product gallery attachments.
 *
 * Backend: product side panel + media attachment field.
 * Frontend: minimalist “KI-Bild” tag on flagged images.
 *
 * @package FairFlock
 * @since 0.9.360
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FAIRFLOCK_META_KI_BILD = '_fairflock_ki_bild';

/**
 * Whether an attachment is flagged as KI-Bild.
 *
 * @since 0.9.360
 *
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function fairflock_attachment_is_ki_bild( $attachment_id ) {
	$id = absint( $attachment_id );
	if ( ! $id ) {
		return false;
	}
	return '1' === (string) get_post_meta( $id, FAIRFLOCK_META_KI_BILD, true );
}

/**
 * Sets / clears the KI-Bild flag on an attachment.
 *
 * @since 0.9.360
 *
 * @param int  $attachment_id Attachment ID.
 * @param bool $enabled       Enabled.
 * @return void
 */
function fairflock_attachment_set_ki_bild( $attachment_id, $enabled ) {
	$id = absint( $attachment_id );
	if ( ! $id || 'attachment' !== get_post_type( $id ) ) {
		return;
	}
	if ( $enabled ) {
		update_post_meta( $id, FAIRFLOCK_META_KI_BILD, '1' );
	} else {
		delete_post_meta( $id, FAIRFLOCK_META_KI_BILD );
	}
}

/**
 * Minimalist tag markup.
 *
 * @since 0.9.360
 *
 * @return string
 */
function fairflock_ki_bild_tag_html() {
	return '<span class="ff-ki-bild-tag">' . esc_html__( 'KI-Bild', 'fairflock' ) . '</span>';
}

/**
 * Resolve a media-library attachment from a URL (including resized -WxH files).
 *
 * @since 0.9.566
 *
 * @param string $url Image URL.
 * @return int
 */
function fairflock_ki_bild_id_from_url( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' || ! function_exists( 'attachment_url_to_postid' ) ) {
		return 0;
	}
	$url = html_entity_decode( $url, ENT_QUOTES | ENT_HTML5 );
	$id = (int) attachment_url_to_postid( $url );
	if ( $id ) {
		return $id;
	}
	$stripped = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+(?:\?.*)?$)/i', '', $url );
	if ( is_string( $stripped ) && $stripped !== $url ) {
		return (int) attachment_url_to_postid( $stripped );
	}
	return 0;
}

/**
 * Attachment id from rendered markup (wp-image-N or background-image url).
 *
 * @since 0.9.569
 *
 * @param string $html Rendered block HTML.
 * @return int
 */
function fairflock_ki_bild_id_from_html( $html ) {
	if ( ! is_string( $html ) || $html === '' ) {
		return 0;
	}
	if ( preg_match( '/\bwp-image-(\d+)\b/', $html, $m ) ) {
		return absint( $m[1] );
	}
	$decoded = html_entity_decode( $html, ENT_QUOTES | ENT_HTML5 );
	if ( preg_match( '/background-image\s*:\s*url\(\s*[\'"]?([^\'")]+)/i', $decoded, $m ) ) {
		return fairflock_ki_bild_id_from_url( $m[1] );
	}
	return 0;
}

/**
 * Class list contains a whole token (fs-slide, not fs-slide-inner).
 *
 * @since 0.9.569
 *
 * @param string $class Class attribute.
 * @param string $token Token.
 * @return bool
 */
function fairflock_ki_bild_has_class_token( $class, $token ) {
	if ( $class === '' || $token === '' ) {
		return false;
	}
	return (bool) preg_match( '/(?:^|\s)' . preg_quote( $token, '/' ) . '(?:\s|$)/', $class );
}

/**
 * Cover block background attachment (media id, featured image, or url).
 *
 * @since 0.9.566
 *
 * @param array<string, mixed> $block Parsed block.
 * @return int
 */
function fairflock_ki_bild_cover_attachment_id( $block ) {
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	if ( ! empty( $attrs['id'] ) ) {
		return absint( $attrs['id'] );
	}
	if ( ! empty( $attrs['useFeaturedImage'] ) ) {
		$thumb = (int) get_post_thumbnail_id();
		if ( $thumb ) {
			return $thumb;
		}
	}
	if ( ! empty( $attrs['url'] ) ) {
		return fairflock_ki_bild_id_from_url( (string) $attrs['url'] );
	}
	return fairflock_ki_bild_background_attachment_id( $block );
}

/**
 * Group/section background image from block style.background.backgroundImage.
 *
 * @since 0.9.566
 *
 * @param array<string, mixed> $block Parsed block.
 * @return int
 */
function fairflock_ki_bild_background_attachment_id( $block ) {
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$bg    = $attrs['style']['background']['backgroundImage'] ?? null;
	if ( ! is_array( $bg ) ) {
		return 0;
	}
	if ( ! empty( $bg['id'] ) ) {
		return absint( $bg['id'] );
	}
	if ( ! empty( $bg['url'] ) ) {
		return fairflock_ki_bild_id_from_url( (string) $bg['url'] );
	}
	return 0;
}

/**
 * Adds the KI-Bild class + tag to the root element of a rendered block.
 *
 * @since 0.9.566
 *
 * @param string $html Block HTML.
 * @return string
 */
function fairflock_ki_bild_mark_root_html( $html ) {
	if ( ! is_string( $html ) || $html === '' || str_contains( $html, 'ff-ki-bild-tag' ) ) {
		return $html;
	}
	$tag = fairflock_ki_bild_tag_html();
	if ( ! preg_match( '/^(\s*)(<\w+\b[^>]*>)/', $html, $m ) ) {
		return $html;
	}
	$open = $m[2];
	if ( ! preg_match( '/\bff-has-ki-bild\b/', $open ) ) {
		if ( preg_match( '/\bclass=(["\'])(.*?)\1/i', $open ) ) {
			$open = preg_replace(
				'/\bclass=(["\'])(.*?)\1/i',
				'class=$1$2 ff-has-ki-bild$1',
				$open,
				1
			);
		} else {
			$open = preg_replace( '/^<(\w+)/', '<$1 class="ff-has-ki-bild"', $open, 1 );
		}
	}
	return $m[1] . $open . $tag . substr( $html, strlen( $m[1] ) + strlen( $m[2] ) );
}

/**
 * KI-Bild tag on hero slides, covers, fullbleed, collection cards, bento cards, mega-menu images and the brand-band photo.
 *
 * The band copy column stays untagged. The photo column (ff-band__media) is tagged when the image is flagged.
 *
 * @since 0.9.566
 * @since 0.9.569 Hero, Kollektionen, Bento.
 * @since 0.9.572 Mega-Menü-Bilder wieder mit Hinweis.
 * @since 0.9.574 Brand-Band: Hinweis auf dem Bild rechts, nicht auf dem Text.
 *
 * @param string               $content Block HTML.
 * @param array<string, mixed> $block   Parsed block.
 * @return string
 */
function fairflock_ki_bild_render_background_block( $content, $block ) {
	if ( ! is_string( $content ) || $content === '' || ! is_array( $block ) ) {
		return $content;
	}
	$name  = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';

	if (
		fairflock_ki_bild_has_class_token( $class, 'ff-band' )
		|| fairflock_ki_bild_has_class_token( $class, 'ff-band__copy' )
	) {
		return $content;
	}

	$is_cover = ( $name === 'core/cover' );
	$is_slide = fairflock_ki_bild_has_class_token( $class, 'fs-slide' );
	$is_bleed = str_contains( $class, 'ff-fullbleed' );
	$is_card  = fairflock_ki_bild_has_class_token( $class, 'ff-collection-card' )
		|| fairflock_ki_bild_has_class_token( $class, 'ff-bento-card' );
	if ( ! $is_cover && ! $is_slide && ! $is_bleed && ! $is_card ) {
		return $content;
	}

	$id = $is_cover
		? fairflock_ki_bild_cover_attachment_id( $block )
		: fairflock_ki_bild_background_attachment_id( $block );
	if ( ! $id ) {
		$id = fairflock_ki_bild_id_from_html( $content );
	}
	if ( ! fairflock_attachment_is_ki_bild( $id ) ) {
		return $content;
	}
	return fairflock_ki_bild_mark_root_html( $content );
}
add_filter( 'render_block', 'fairflock_ki_bild_render_background_block', 12, 2 );

/**
 * Featured + gallery attachment IDs for a product.
 *
 * @since 0.9.360
 *
 * @param int|WC_Product $product Product.
 * @return int[]
 */
function fairflock_product_image_attachment_ids( $product ) {
	if ( ! is_object( $product ) ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product ) : null;
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return array();
	}
	$ids = array();
	$featured = (int) $product->get_image_id();
	if ( $featured ) {
		$ids[] = $featured;
	}
	foreach ( $product->get_gallery_image_ids() as $gid ) {
		$gid = (int) $gid;
		if ( $gid && ! in_array( $gid, $ids, true ) ) {
			$ids[] = $gid;
		}
	}
	return $ids;
}

/**
 * Attachment IDs of a product that are flagged as KI-Bild.
 *
 * @since 0.9.360
 *
 * @param int|WC_Product $product Product.
 * @return int[]
 */
function fairflock_product_ki_bild_ids( $product ) {
	$out = array();
	foreach ( fairflock_product_image_attachment_ids( $product ) as $id ) {
		if ( fairflock_attachment_is_ki_bild( $id ) ) {
			$out[] = $id;
		}
	}
	return $out;
}

/**
 * Product edit metabox: toggle KI-Bild per gallery image.
 *
 * @since 0.9.360
 *
 * @return void
 */
function fairflock_ki_bild_add_meta_box() {
	add_meta_box(
		'fairflock_ki_bild',
		__( 'KI-Bilder', 'fairflock' ),
		'fairflock_ki_bild_render_meta_box',
		'product',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'fairflock_ki_bild_add_meta_box' );

/**
 * Renders the product KI-Bilder metabox.
 *
 * @since 0.9.360
 *
 * @param WP_Post $post Post.
 * @return void
 */
function fairflock_ki_bild_render_meta_box( $post ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return;
	}
	$product = wc_get_product( $post->ID );
	if ( ! $product ) {
		return;
	}

	wp_nonce_field( 'fairflock_ki_bild_save', 'fairflock_ki_bild_nonce' );

	$ids = fairflock_product_image_attachment_ids( $product );
	if ( ! $ids ) {
		echo '<p class="description">' . esc_html__( 'Noch keine Produktbilder. Bitte zuerst Beitragsbild oder Galerie setzen.', 'fairflock' ) . '</p>';
		return;
	}

	echo '<p class="description" style="margin-top:0">' . esc_html__( 'Aktiviere „KI-Bild“ für einzelne Bilder — der Tag erscheint auf dem Bild im Shop.', 'fairflock' ) . '</p>';
	echo '<ul class="ff-ki-bild-admin-list">';
	foreach ( $ids as $id ) {
		$checked = fairflock_attachment_is_ki_bild( $id );
		$thumb   = wp_get_attachment_image( $id, array( 56, 56 ), false, array( 'style' => 'display:block;width:56px;height:56px;object-fit:cover;border-radius:6px;' ) );
		if ( ! $thumb ) {
			continue;
		}
		printf(
			'<li class="ff-ki-bild-admin-item"><label><span class="ff-ki-bild-admin-thumb">%s</span><span class="ff-ki-bild-admin-check"><input type="checkbox" name="fairflock_ki_bild_ids[]" value="%d"%s /> %s</span></label></li>',
			$thumb, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image
			(int) $id,
			$checked ? ' checked="checked"' : '',
			esc_html__( 'KI-Bild', 'fairflock' )
		);
	}
	echo '</ul>';
	echo '<style>
		.ff-ki-bild-admin-list{list-style:none;margin:8px 0 0;padding:0;display:flex;flex-direction:column;gap:8px}
		.ff-ki-bild-admin-item label{display:flex;align-items:center;gap:10px;cursor:pointer}
		.ff-ki-bild-admin-check{font-size:12px;line-height:1.3}
	</style>';
}

/**
 * Saves KI-Bild flags from the product metabox.
 *
 * @since 0.9.360
 *
 * @param int $post_id Product ID.
 * @return void
 */
function fairflock_ki_bild_save_product( $post_id ) {
	if ( ! isset( $_POST['fairflock_ki_bild_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fairflock_ki_bild_nonce'] ) ), 'fairflock_ki_bild_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! function_exists( 'wc_get_product' ) ) {
		return;
	}
	$product = wc_get_product( $post_id );
	if ( ! $product ) {
		return;
	}

	$allowed = fairflock_product_image_attachment_ids( $product );
	$raw     = isset( $_POST['fairflock_ki_bild_ids'] ) ? wp_unslash( $_POST['fairflock_ki_bild_ids'] ) : array();
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}
	$selected = array_map( 'absint', $raw );

	foreach ( $allowed as $id ) {
		fairflock_attachment_set_ki_bild( $id, in_array( $id, $selected, true ) );
	}
}
add_action( 'woocommerce_process_product_meta', 'fairflock_ki_bild_save_product' );

/**
 * Media modal / attachment edit: KI-Bild checkbox.
 *
 * @since 0.9.360
 *
 * @param array   $fields Fields.
 * @param WP_Post $post   Attachment.
 * @return array
 */
function fairflock_ki_bild_attachment_fields( $fields, $post ) {
	if ( ! $post || 'attachment' !== $post->post_type ) {
		return $fields;
	}
	if ( strpos( (string) $post->post_mime_type, 'image/' ) !== 0 ) {
		return $fields;
	}
	$checked = fairflock_attachment_is_ki_bild( $post->ID );
	$fields['fairflock_ki_bild'] = array(
		'label' => __( 'KI-Bild', 'fairflock' ),
		'input' => 'html',
		'html'  => sprintf(
			'<label style="display:inline-flex;align-items:center;gap:6px"><input type="checkbox" name="attachments[%1$d][fairflock_ki_bild]" value="1"%2$s /> %3$s</label>',
			(int) $post->ID,
			$checked ? ' checked="checked"' : '',
			esc_html__( 'Als KI-Bild markieren', 'fairflock' )
		),
		'helps' => __( 'Zeigt einen kleinen „KI-Bild“-Tag auf dem Produktbild.', 'fairflock' ),
	);
	return $fields;
}
add_filter( 'attachment_fields_to_edit', 'fairflock_ki_bild_attachment_fields', 10, 2 );

/**
 * Saves KI-Bild from media modal / attachment fields.
 *
 * @since 0.9.360
 *
 * @param array $post       Post data.
 * @param array $attachment Attachment fields.
 * @return array
 */
function fairflock_ki_bild_attachment_fields_save( $post, $attachment ) {
	$id = isset( $post['ID'] ) ? absint( $post['ID'] ) : 0;
	if ( ! $id ) {
		return $post;
	}
	$enabled = ! empty( $attachment['fairflock_ki_bild'] );
	fairflock_attachment_set_ki_bild( $id, $enabled );
	return $post;
}
add_filter( 'attachment_fields_to_save', 'fairflock_ki_bild_attachment_fields_save', 10, 2 );

/**
 * Adds data-ff-ki-bild on attachment images.
 *
 * @since 0.9.360
 *
 * @param array $attr       Attributes.
 * @param WP_Post $attachment Attachment.
 * @return array
 */
function fairflock_ki_bild_image_attributes( $attr, $attachment ) {
	$id = is_object( $attachment ) ? (int) $attachment->ID : absint( $attachment );
	if ( $id && fairflock_attachment_is_ki_bild( $id ) ) {
		$attr['data-ff-ki-bild'] = '1';
		$class = isset( $attr['class'] ) ? (string) $attr['class'] : '';
		if ( false === strpos( ' ' . $class . ' ', ' ff-ki-bild-img ' ) ) {
			$attr['class'] = trim( $class . ' ff-ki-bild-img' );
		}
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'fairflock_ki_bild_image_attributes', 10, 2 );

/**
 * Injects the KI-Bild tag into classic Woo gallery HTML.
 *
 * @since 0.9.360
 *
 * @param string $html          HTML.
 * @param int    $attachment_id Attachment ID.
 * @return string
 */
function fairflock_ki_bild_gallery_thumbnail_html( $html, $attachment_id ) {
	if ( ! fairflock_attachment_is_ki_bild( $attachment_id ) || ! is_string( $html ) || '' === $html ) {
		return $html;
	}
	if ( false !== strpos( $html, 'ff-ki-bild-tag' ) ) {
		return $html;
	}
	$tag = fairflock_ki_bild_tag_html();
	if ( preg_match( '/^(<\w+\b[^>]*>)/', $html, $m ) ) {
		$open = $m[1];
		if ( false === stripos( $open, 'ff-has-ki-bild' ) ) {
			if ( preg_match( '/\bclass=(["\'])(.*?)\1/i', $open, $cm ) ) {
				$open = preg_replace(
					'/\bclass=(["\'])(.*?)\1/i',
					'class=$1$2 ff-has-ki-bild$1',
					$open,
					1
				);
			} else {
				$open = preg_replace( '/^<(\w+)/', '<$1 class="ff-has-ki-bild"', $open, 1 );
			}
		}
		return $open . $tag . substr( $html, strlen( $m[1] ) );
	}
	return $tag . $html;
}
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'fairflock_ki_bild_gallery_thumbnail_html', 20, 2 );

/**
 * Store API: KI image IDs for the product modal.
 *
 * @since 0.9.360
 *
 * @return void
 */
function fairflock_ki_bild_register_store_api_safe() {
	if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
		return;
	}
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	$endpoint = 'product';
	if ( class_exists( '\Automattic\WooCommerce\StoreApi\Schemas\V1\ProductSchema' ) ) {
		$endpoint = \Automattic\WooCommerce\StoreApi\Schemas\V1\ProductSchema::IDENTIFIER;
	}
	woocommerce_store_api_register_endpoint_data(
		array(
			'endpoint'        => $endpoint,
			'namespace'       => 'fairflockKi',
			'data_callback'   => 'fairflock_ki_bild_store_api_data',
			'schema_callback' => 'fairflock_ki_bild_store_api_schema',
		)
	);
}
add_action( 'woocommerce_init', 'fairflock_ki_bild_register_store_api_safe', 25 );

/**
 * @since 0.9.360
 *
 * @param WC_Product $product Product.
 * @return array{image_ids:int[]}
 */
function fairflock_ki_bild_store_api_data( $product ) {
	return array(
		'image_ids' => array_values( fairflock_product_ki_bild_ids( $product ) ),
	);
}

/**
 * @since 0.9.360
 *
 * @return array
 */
function fairflock_ki_bild_store_api_schema() {
	return array(
		'image_ids' => array(
			'description' => __( 'Attachment IDs marked as KI-Bild', 'fairflock' ),
			'type'        => 'array',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
			'items'       => array(
				'type' => 'integer',
			),
		),
	);
}

/**
 * Frontend: inject badges into gallery nodes that only have data-ff-ki-bild.
 *
 * @since 0.9.360
 *
 * @return void
 */
function fairflock_ki_bild_enqueue_script() {
	if ( is_admin() ) {
		return;
	}
	wp_enqueue_script(
		'fairflock-ki-bild',
		FAIRFLOCK_URI . '/assets/js/product-ai-image.js',
		array(),
		FAIRFLOCK_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'fairflock_ki_bild_enqueue_script', 35 );
