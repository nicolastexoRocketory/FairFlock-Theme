<?php
/**
 * Product personalization: optional logo upload and/or free text per product.
 *
 * @package FairFlock
 * @since 0.9.151
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Meta: enable logo/file upload on product. */
const FAIRFLOCK_META_ENABLE_UPLOAD = '_fairflock_enable_logo_upload';

/** Meta: enable personal free text on product. */
const FAIRFLOCK_META_ENABLE_TEXT = '_fairflock_enable_personal_text';

/** Meta: customer-facing title for upload field. */
const FAIRFLOCK_META_UPLOAD_LABEL = '_fairflock_upload_label';

/** Meta: optional hint under upload field (legacy; formats are preferred). */
const FAIRFLOCK_META_UPLOAD_HINT = '_fairflock_upload_hint';

/** Meta: allowed upload formats (comma-separated: jpg,png,pdf). */
const FAIRFLOCK_META_UPLOAD_FORMATS = '_fairflock_upload_formats';

/** Meta: customer-facing title for text field. */
const FAIRFLOCK_META_TEXT_LABEL = '_fairflock_text_label';

/** Meta: optional hint under text field. */
const FAIRFLOCK_META_TEXT_HINT = '_fairflock_text_hint';

/** Cart / order meta keys. */
const FAIRFLOCK_CART_UPLOAD_PATH  = 'fairflock_logo_path';
const FAIRFLOCK_CART_UPLOAD_NAME  = 'fairflock_logo_name';
const FAIRFLOCK_CART_UPLOAD_URL   = 'fairflock_logo_url';
const FAIRFLOCK_CART_UPLOAD_LABEL = 'fairflock_upload_label';
const FAIRFLOCK_CART_TEXT         = 'fairflock_personal_text';
const FAIRFLOCK_CART_TEXT_LABEL   = 'fairflock_text_label';
const FAIRFLOCK_ORDER_UPLOAD_URL  = '_fairflock_logo_url';
const FAIRFLOCK_ORDER_UPLOAD_NAME = '_fairflock_logo_name';
const FAIRFLOCK_ORDER_TEXT        = '_fairflock_personal_text';

/** Max upload size in bytes (5 MB). */
const FAIRFLOCK_UPLOAD_MAX_BYTES = 5242880;

/** Max personal text length. */
const FAIRFLOCK_TEXT_MAX_LENGTH = 80;

/**
 * Whether logo upload is enabled for a product.
 *
 * @since 0.9.151
 *
 * @param int|\WC_Product $product Product ID or object.
 * @return bool
 */
function fairflock_product_has_logo_upload( $product ) {
	$id = fairflock_personalization_product_id( $product );
	if ( ! $id ) {
		return false;
	}
	return 'yes' === get_post_meta( $id, FAIRFLOCK_META_ENABLE_UPLOAD, true );
}

/**
 * Whether personal text is enabled for a product.
 *
 * @since 0.9.151
 *
 * @param int|\WC_Product $product Product ID or object.
 * @return bool
 */
function fairflock_product_has_personal_text( $product ) {
	$id = fairflock_personalization_product_id( $product );
	if ( ! $id ) {
		return false;
	}
	return 'yes' === get_post_meta( $id, FAIRFLOCK_META_ENABLE_TEXT, true );
}

/**
 * Catalog of selectable upload formats for personalization.
 *
 * @since 0.9.318
 *
 * @return array<string, array{label:string,exts:string[],mimes:string[]}>
 */
function fairflock_personalization_format_catalog() {
	return array(
		'png' => array(
			'label' => 'PNG',
			'exts'  => array( 'png' ),
			'mimes' => array( 'image/png' ),
		),
		'jpg' => array(
			'label' => 'JPG',
			'exts'  => array( 'jpg', 'jpeg' ),
			'mimes' => array( 'image/jpeg' ),
		),
		'pdf' => array(
			'label' => 'PDF',
			'exts'  => array( 'pdf' ),
			'mimes' => array( 'application/pdf' ),
		),
	);
}

/**
 * Selected upload format keys for a product (png, jpg, pdf).
 *
 * @since 0.9.318
 *
 * @param int|\WC_Product $product Product.
 * @return string[]
 */
function fairflock_personalization_selected_formats( $product ) {
	$id      = fairflock_personalization_product_id( $product );
	$catalog = fairflock_personalization_format_catalog();
	$keys    = array_keys( $catalog );

	if ( ! $id ) {
		return $keys;
	}

	$raw = trim( (string) get_post_meta( $id, FAIRFLOCK_META_UPLOAD_FORMATS, true ) );
	if ( '' === $raw ) {
		// Legacy products without meta: keep previous default (all formats).
		return $keys;
	}

	$selected = array_values(
		array_filter(
			array_map( 'sanitize_key', array_map( 'trim', explode( ',', $raw ) ) ),
			static function ( $key ) use ( $catalog ) {
				return isset( $catalog[ $key ] );
			}
		)
	);

	return $selected ? $selected : $keys;
}

/**
 * Human-readable list of allowed formats, e.g. "PNG, JPG".
 *
 * @since 0.9.318
 *
 * @param int|\WC_Product $product Product.
 * @return string
 */
function fairflock_personalization_formats_label( $product ) {
	$catalog  = fairflock_personalization_format_catalog();
	$selected = fairflock_personalization_selected_formats( $product );
	$labels   = array();
	foreach ( $selected as $key ) {
		if ( isset( $catalog[ $key ]['label'] ) ) {
			$labels[] = $catalog[ $key ]['label'];
		}
	}
	return implode( ', ', $labels );
}

/**
 * accept= attribute for the file input from product formats.
 *
 * @since 0.9.318
 *
 * @param int|\WC_Product $product Product.
 * @return string
 */
function fairflock_personalization_accept_attr( $product ) {
	$catalog  = fairflock_personalization_format_catalog();
	$selected = fairflock_personalization_selected_formats( $product );
	$parts    = array();
	foreach ( $selected as $key ) {
		if ( empty( $catalog[ $key ] ) ) {
			continue;
		}
		foreach ( $catalog[ $key ]['exts'] as $ext ) {
			$parts[] = '.' . $ext;
		}
		foreach ( $catalog[ $key ]['mimes'] as $mime ) {
			$parts[] = $mime;
		}
	}
	return implode( ',', array_unique( $parts ) );
}

/**
 * Customer-facing labels for upload / text (with defaults).
 *
 * @since 0.9.156
 * @since 0.9.318 Upload hint is derived from selected formats.
 *
 * @param int|\WC_Product $product Product.
 * @return array{upload_label:string,upload_hint:string,text_label:string,text_hint:string}
 */
function fairflock_personalization_labels( $product ) {
	$id = fairflock_personalization_product_id( $product );

	$upload_label = $id ? trim( (string) get_post_meta( $id, FAIRFLOCK_META_UPLOAD_LABEL, true ) ) : '';
	$text_label   = $id ? trim( (string) get_post_meta( $id, FAIRFLOCK_META_TEXT_LABEL, true ) ) : '';
	$text_hint    = $id ? trim( (string) get_post_meta( $id, FAIRFLOCK_META_TEXT_HINT, true ) ) : '';

	if ( '' === $upload_label ) {
		$upload_label = __( 'Logo hochladen', 'fairflock' );
	}
	if ( '' === $text_label ) {
		$text_label = __( 'Persönlicher Text', 'fairflock' );
	}

	$formats = fairflock_personalization_formats_label( $product );
	$upload_hint = $formats
		? sprintf(
			/* translators: %s: comma-separated formats, e.g. PNG, PDF */
			__( '%s, max. 5 MB.', 'fairflock' ),
			$formats
		)
		: __( 'Max. 5 MB.', 'fairflock' );

	return array(
		'upload_label' => $upload_label,
		'upload_hint'  => $upload_hint,
		'text_label'   => $text_label,
		'text_hint'    => $text_hint,
	);
}

/**
 * Resolve parent product ID (variable products use parent meta).
 *
 * @since 0.9.151
 *
 * @param int|\WC_Product|null $product Product.
 * @return int
 */
function fairflock_personalization_product_id( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product instanceof WC_Product ) {
		return 0;
	}
	$parent = $product->get_parent_id();
	return $parent ? (int) $parent : (int) $product->get_id();
}

/**
 * Allowed upload extensions and MIME types (optionally scoped to a product).
 *
 * @since 0.9.151
 * @since 0.9.318 Honours per-product format selection.
 *
 * @param int|\WC_Product|null $product Optional product for scoped formats.
 * @return array<string, string> extension => mime
 */
function fairflock_personalization_allowed_mimes( $product = null ) {
	// SVG absichtlich nicht: XSS-Risiko ohne Sanitizer (Audit 0.9.224).
	$all = array(
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'pdf'  => 'application/pdf',
	);

	if ( null === $product ) {
		return $all;
	}

	$catalog  = fairflock_personalization_format_catalog();
	$selected = fairflock_personalization_selected_formats( $product );
	$allowed  = array();
	foreach ( $selected as $key ) {
		if ( empty( $catalog[ $key ]['exts'] ) ) {
			continue;
		}
		foreach ( $catalog[ $key ]['exts'] as $ext ) {
			if ( isset( $all[ $ext ] ) ) {
				$allowed[ $ext ] = $all[ $ext ];
			}
		}
	}

	return $allowed ? $allowed : $all;
}

/* -------------------------------------------------------------------------
 * Admin: product settings
 * ---------------------------------------------------------------------- */

/**
 * Adds personalization checkboxes to the product General tab.
 *
 * @since 0.9.151
 *
 * @return void
 */
function fairflock_personalization_admin_fields() {
	echo '<div class="options_group fairflock-personalization-options">';
	echo '<p class="form-field"><strong>' . esc_html__( 'Personalisierung (FairFlock)', 'fairflock' ) . '</strong></p>';

	woocommerce_wp_checkbox(
		array(
			'id'          => FAIRFLOCK_META_ENABLE_UPLOAD,
			'label'       => __( 'Bild-Upload', 'fairflock' ),
			'description' => __( 'Kunden können eine Datei hochladen (Logo, Motiv, Druckvorlage …).', 'fairflock' ),
			'desc_tip'    => true,
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => FAIRFLOCK_META_UPLOAD_LABEL,
			'label'       => __( 'Info unter „Dein Design“ (Upload)', 'fairflock' ),
			'placeholder' => __( 'z. B. Vereinslogo / Druckmotiv', 'fairflock' ),
			'description' => __( 'Einzige Info-Zeile unter der Überschrift „Dein Design“ beim Bild-Upload.', 'fairflock' ),
			'desc_tip'    => true,
		)
	);

	global $post;
	$product_id       = ( $post instanceof WP_Post ) ? (int) $post->ID : 0;
	$selected_formats = fairflock_personalization_selected_formats( $product_id );
	$catalog          = fairflock_personalization_format_catalog();
	echo '<p class="form-field fairflock-upload-formats">';
	echo '<label>' . esc_html__( 'Erlaubte Dateiformate', 'fairflock' ) . '</label>';
	echo '<span class="wrap" style="display:inline-flex;flex-wrap:wrap;gap:12px 18px;padding-top:4px;">';
	foreach ( $catalog as $key => $item ) {
		printf(
			'<label style="margin:0;float:none;"><input type="checkbox" name="%1$s[]" value="%2$s"%3$s /> %4$s</label>',
			esc_attr( FAIRFLOCK_META_UPLOAD_FORMATS ),
			esc_attr( $key ),
			checked( in_array( $key, $selected_formats, true ), true, false ),
			esc_html( $item['label'] )
		);
	}
	echo '</span>';
	echo '<span class="description">' . esc_html__( 'Wird unter dem Upload angezeigt und beim Hochladen geprüft. Mindestens eines wählen.', 'fairflock' ) . '</span>';
	echo '</p>';

	woocommerce_wp_checkbox(
		array(
			'id'          => FAIRFLOCK_META_ENABLE_TEXT,
			'label'       => __( 'Freitext', 'fairflock' ),
			'description' => __( 'Kunden können einen kurzen Text angeben (Name, Nummer …).', 'fairflock' ),
			'desc_tip'    => true,
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => FAIRFLOCK_META_TEXT_LABEL,
			'label'       => __( 'Info unter „Dein Design“ (Freitext)', 'fairflock' ),
			'placeholder' => __( 'z. B. Name auf dem Trikot', 'fairflock' ),
			'description' => __( 'Einzige Info-Zeile unter der Überschrift „Dein Design“ beim Freitext.', 'fairflock' ),
			'desc_tip'    => true,
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => FAIRFLOCK_META_TEXT_HINT,
			'label'       => __( 'Hinweis Freitext', 'fairflock' ),
			'placeholder' => __( 'z. B. Max. 12 Zeichen', 'fairflock' ),
			'description' => __( 'Optionaler Hinweis unter dem Textfeld.', 'fairflock' ),
			'desc_tip'    => true,
		)
	);

	echo '<p class="form-field">' . esc_html__( 'Sind beide Optionen aktiv, wählt der Kunde eine Variante (Upload oder Text). Leere Titel nutzen die Standardtexte.', 'fairflock' ) . '</p>';
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'fairflock_personalization_admin_fields' );

/**
 * Saves personalization product meta.
 *
 * @since 0.9.151
 * @since 0.9.156 Custom titles and hints.
 *
 * @param int $post_id Product ID.
 * @return void
 */
function fairflock_personalization_save_admin_fields( $post_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the save nonce.
	$upload = isset( $_POST[ FAIRFLOCK_META_ENABLE_UPLOAD ] ) ? 'yes' : 'no';
	$text   = isset( $_POST[ FAIRFLOCK_META_ENABLE_TEXT ] ) ? 'yes' : 'no';

	update_post_meta( $post_id, FAIRFLOCK_META_ENABLE_UPLOAD, $upload );
	update_post_meta( $post_id, FAIRFLOCK_META_ENABLE_TEXT, $text );

	$fields = array(
		FAIRFLOCK_META_UPLOAD_LABEL,
		FAIRFLOCK_META_TEXT_LABEL,
		FAIRFLOCK_META_TEXT_HINT,
	);

	foreach ( $fields as $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$val = sanitize_text_field( is_string( $raw ) ? $raw : '' );
		if ( '' === $val ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $val );
		}
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$formats_raw = isset( $_POST[ FAIRFLOCK_META_UPLOAD_FORMATS ] ) ? (array) wp_unslash( $_POST[ FAIRFLOCK_META_UPLOAD_FORMATS ] ) : array();
	$catalog     = fairflock_personalization_format_catalog();
	$formats     = array();
	foreach ( $formats_raw as $key ) {
		$key = sanitize_key( (string) $key );
		if ( isset( $catalog[ $key ] ) ) {
			$formats[] = $key;
		}
	}
	$formats = array_values( array_unique( $formats ) );
	if ( ! $formats ) {
		$formats = array_keys( $catalog );
	}
	update_post_meta( $post_id, FAIRFLOCK_META_UPLOAD_FORMATS, implode( ',', $formats ) );

	// Stop using free-text format hints; formats are selected above.
	delete_post_meta( $post_id, FAIRFLOCK_META_UPLOAD_HINT );
}
add_action( 'woocommerce_process_product_meta', 'fairflock_personalization_save_admin_fields' );

/* -------------------------------------------------------------------------
 * Frontend: fields
 * ---------------------------------------------------------------------- */

/**
 * Builds personalization card HTML for a product.
 *
 * @since 0.9.153
 *
 * @param WC_Product $product   Product.
 * @param string     $id_suffix Optional suffix for input ids (e.g. "_modal").
 * @return string Empty when personalization is off.
 */
function fairflock_get_personalization_markup( $product, $id_suffix = '' ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$enable_upload = fairflock_product_has_logo_upload( $product );
	$enable_text   = fairflock_product_has_personal_text( $product );

	if ( ! $enable_upload && ! $enable_text ) {
		return '';
	}

	$labels    = fairflock_personalization_labels( $product );
	$both      = $enable_upload && $enable_text;
	$id_suffix = preg_replace( '/[^a-z0-9_-]/i', '', (string) $id_suffix );
	$uid_file  = 'fairflock_logo_upload' . $id_suffix;
	$uid_text  = 'fairflock_personal_text' . $id_suffix;

	$need_upload = sprintf(
		/* translators: %s: field title */
		__( 'Bitte „%s“ angeben.', 'fairflock' ),
		$labels['upload_label']
	);
	$need_text = sprintf(
		/* translators: %s: field title */
		__( 'Bitte „%s“ angeben.', 'fairflock' ),
		$labels['text_label']
	);

	ob_start();
	echo '<div class="ff-personalization" data-ff-personalization="1"'
		. ( $both ? ' data-ff-both="1"' : '' )
		. ' data-ff-msg-upload="' . esc_attr( $need_upload ) . '"'
		. ' data-ff-msg-text="' . esc_attr( $need_text ) . '"'
		. '>';

	if ( $both ) {
		echo '<fieldset class="ff-personalization__mode">';
		echo '<legend class="ff-personalization__question">' . esc_html__( 'Wie möchtest du dein Produkt individualisieren?', 'fairflock' ) . '</legend>';
		echo '<div class="ff-personalization__switch" role="radiogroup" aria-label="' . esc_attr__( 'Individualisierungsart', 'fairflock' ) . '">';
		echo '<label class="ff-personalization__switch-option is-active">';
		echo '<input type="radio" name="fairflock_personalization_mode" value="upload" checked="checked" />';
		echo '<span>' . esc_html( $labels['upload_label'] ) . '</span>';
		echo '</label>';
		echo '<label class="ff-personalization__switch-option">';
		echo '<input type="radio" name="fairflock_personalization_mode" value="text" />';
		echo '<span>' . esc_html( $labels['text_label'] ) . '</span>';
		echo '</label>';
		echo '</div>';
		echo '</fieldset>';
	} elseif ( $enable_text ) {
		echo '<p class="ff-personalization__hint">' . esc_html( $labels['text_label'] ) . '</p>';
	}

	if ( $enable_upload ) {
		$accept     = fairflock_personalization_accept_attr( $product );
		$pick_label = sprintf(
			/* translators: %s: configured upload field title, e.g. Druckmotiv */
			__( '%s wählen', 'fairflock' ),
			$labels['upload_label']
		);
		$doc_icon = '<svg class="ff-personalization__file-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><path d="M14 3v5h5M9 13h6M9 17h6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>';
		echo '<div class="ff-personalization__panel ff-personalization__panel--upload"' . ( $both ? ' data-ff-panel="upload"' : '' ) . '>';
		echo '<p class="ff-personalization__field ff-personalization__field--upload">';
		echo '<label class="screen-reader-text" for="' . esc_attr( $uid_file ) . '">' . esc_html( $labels['upload_label'] ) . '</label>';
		echo '<span class="ff-personalization__file">';
		echo '<input type="file" class="ff-personalization__file-input" id="' . esc_attr( $uid_file ) . '" name="fairflock_logo_upload" accept="' . esc_attr( $accept ) . '" />';
		echo '<button type="button" class="ff-personalization__file-pick" data-ff-file-pick>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
		echo $doc_icon;
		echo '<span class="ff-personalization__file-name" data-ff-file-name data-empty="' . esc_attr( $pick_label ) . '">' . esc_html( $pick_label ) . '</span>';
		echo '</button>';
		echo '</span>';
		echo '</p>';
		echo '</div>';
	}

	if ( $enable_text ) {
		echo '<div class="ff-personalization__panel ff-personalization__panel--text"' . ( $both ? ' data-ff-panel="text" hidden' : '' ) . '>';
		echo '<p class="ff-personalization__field ff-personalization__field--text">';
		$text_label_class = $both ? '' : ' class="screen-reader-text"';
		echo '<label' . $text_label_class . ' for="' . esc_attr( $uid_text ) . '">' . esc_html( $labels['text_label'] ) . '</label>';
		printf(
			'<input type="text" id="%s" name="fairflock_personal_text" maxlength="%d" autocomplete="off"%s />',
			esc_attr( $uid_text ),
			(int) FAIRFLOCK_TEXT_MAX_LENGTH,
			$both ? '' : ' placeholder="' . esc_attr( $labels['text_label'] ) . '"'
		);
		if ( '' !== $labels['text_hint'] ) {
			echo '<span class="ff-personalization__note">' . esc_html( $labels['text_hint'] ) . '</span>';
		}
		echo '</p>';
		echo '</div>';
	}

	echo '</div>';
	return (string) ob_get_clean();
}

/**
 * Renders personalization fields before the add-to-cart button.
 *
 * @since 0.9.151
 * @since 0.9.152 Toggle when both options are enabled.
 * @since 0.9.153 Shared markup helper.
 *
 * @return void
 */
function fairflock_personalization_render_fields() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$markup = fairflock_get_personalization_markup( $product );
	if ( '' === trim( (string) $markup ) ) {
		return;
	}

	echo '<div class="ff-product-buy__design">';
	echo '<p class="ff-product-modal__sizes-label ff-product-buy__design-label">' . esc_html__( 'Dein Design', 'fairflock' ) . '</p>';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped inside helper.
	echo $markup;
	echo '</div>';
}
add_action( 'woocommerce_before_add_to_cart_button', 'fairflock_personalization_render_fields', 15 );

/**
 * Enqueues multipart form fix and shared personalization UI script.
 *
 * @since 0.9.151
 * @since 0.9.152 Toggle script when both options are enabled.
 * @since 0.9.153 Always available for product modal.
 *
 * @return void
 */
function fairflock_personalization_enqueue_form_script() {
	if ( is_admin() ) {
		return;
	}

	$js_init = <<<'JS'
window.fairflockInitPersonalization=function(root){
  if(!root){return;}
  var form=root.closest("form")||document.querySelector("form.cart, .wp-block-add-to-cart-form form, .wp-block-woocommerce-add-to-cart-form form");
  if(form&&root.querySelector('input[type="file"]')){
    form.setAttribute("enctype","multipart/form-data");
    form.setAttribute("encoding","multipart/form-data");
  }
  var fileInput=root.querySelector('input[name="fairflock_logo_upload"]');
  var fileWrap=fileInput?fileInput.closest(".ff-personalization__file"):null;
  var fileName=fileWrap?fileWrap.querySelector("[data-ff-file-name]"):null;
  var fileBtn=fileWrap?fileWrap.querySelector("[data-ff-file-pick]"):null;
  function syncFile(){
    if(!fileInput||!fileWrap){return;}
    var file=fileInput.files&&fileInput.files[0];
    var chosen=!!(file&&file.name);
    var empty=fileName?fileName.getAttribute("data-empty"):"";
    fileWrap.classList.toggle("is-chosen",chosen);
    if(fileName){fileName.textContent=chosen?file.name:empty;}
  }
  if(fileInput&&fileBtn&&!fileInput.getAttribute("data-ff-file-bound")){
    fileInput.setAttribute("data-ff-file-bound","1");
    fileBtn.addEventListener("click",function(){fileInput.click();});
    fileInput.addEventListener("change",syncFile);
  }
  syncFile();
  if(!root.getAttribute("data-ff-both")){return;}
  var radios=root.querySelectorAll('input[name="fairflock_personalization_mode"]');
  var panelUpload=root.querySelector('[data-ff-panel="upload"]');
  var panelText=root.querySelector('[data-ff-panel="text"]');
  var textInput=root.querySelector('input[name="fairflock_personal_text"]');
  function setMode(mode){
    radios.forEach(function(r){
      var lab=r.closest(".ff-personalization__switch-option");
      if(lab){lab.classList.toggle("is-active",r.checked);}
    });
    if(panelUpload){panelUpload.hidden=mode!=="upload";}
    if(panelText){panelText.hidden=mode!=="text";}
    if(mode==="upload"){
      if(textInput){textInput.value="";textInput.disabled=true;}
      if(fileInput){fileInput.disabled=false;}
    }else{
      if(fileInput){fileInput.value="";fileInput.disabled=true;syncFile();}
      if(textInput){textInput.disabled=false;}
    }
  }
  radios.forEach(function(r){
    r.addEventListener("change",function(){setMode(r.value);});
  });
  var checked=root.querySelector('input[name="fairflock_personalization_mode"]:checked');
  setMode(checked?checked.value:"upload");
};
document.addEventListener("DOMContentLoaded",function(){
  document.querySelectorAll("[data-ff-personalization]").forEach(function(el){
    window.fairflockInitPersonalization(el);
  });
});
JS;

	$js_excerpt = <<<'JS'
(function(){
  document.addEventListener("click",function(e){
    var summary=e.target.closest&&e.target.closest(".ff-product-excerpt-acc__summary");
    if(!summary){return;}
    var details=summary.closest(".ff-product-excerpt-acc");
    if(!details||!details.open||details.classList.contains("is-closing")){return;}
    e.preventDefault();
    var clip=details.querySelector(".ff-product-excerpt-acc__clip");
    details.classList.add("is-closing");
    var done=false;
    function finish(){
      if(done){return;}
      done=true;
      details.classList.remove("is-closing");
      details.open=false;
      if(clip){clip.removeEventListener("transitionend",onEnd);}
    }
    function onEnd(ev){
      if(ev.target!==clip){return;}
      finish();
    }
    if(clip){clip.addEventListener("transitionend",onEnd);}
    window.setTimeout(finish,420);
  },true);
})();
JS;

	$js_buy = <<<'JS'
(function(){
  function bindActions(panel){
    if(!panel||panel.getAttribute("data-ff-buy-bound")){return;}
    panel.setAttribute("data-ff-buy-bound","1");
    var collapse=panel.querySelector("[data-ff-collapse]");
    var mq=window.matchMedia("(max-width: 960px)");
    function setCollapsed(on){
      if(!mq.matches){on=false;}
      panel.classList.toggle("is-collapsed",!!on);
      if(collapse){collapse.setAttribute("aria-expanded",on?"false":"true");}
    }
    function syncViewport(){
      setCollapsed(mq.matches);
    }
    syncViewport();
    if(mq.addEventListener){mq.addEventListener("change",syncViewport);}
    else if(mq.addListener){mq.addListener(syncViewport);}
    panel.addEventListener("click",function(e){
      var configureBtn=e.target.closest&&e.target.closest("[data-ff-configure]");
      if(configureBtn&&panel.contains(configureBtn)){
        e.preventDefault();
        setCollapsed(false);
        return;
      }
      var collapseBtn=e.target.closest&&e.target.closest("[data-ff-collapse]");
      if(collapseBtn&&panel.contains(collapseBtn)){
        e.preventDefault();
        setCollapsed(true);
      }
    });
  }
  function init(){
    document.querySelectorAll(".ff-product__buy [data-ff-buy-config]").forEach(bindActions);
  }
  if(document.readyState==="loading"){
    document.addEventListener("DOMContentLoaded",init);
  }else{init();}
})();
JS;

	wp_register_script(
		'fairflock-personalization',
		false,
		array(),
		FAIRFLOCK_VERSION,
		true
	);
	wp_enqueue_script( 'fairflock-personalization' );
	wp_add_inline_script( 'fairflock-personalization', $js_init );
	wp_add_inline_script( 'fairflock-personalization', $js_excerpt );
	wp_add_inline_script( 'fairflock-personalization', $js_buy );
}
add_action( 'wp_enqueue_scripts', 'fairflock_personalization_enqueue_form_script', 15 );

/**
 * Registers Store API product extension + personalized cart endpoint.
 *
 * @since 0.9.153
 * @since 0.9.224 REST requires wp_rest nonce; no public unauthenticated upload.
 *
 * @return void
 */
function fairflock_personalization_register_rest_routes() {
	register_rest_route(
		'fairflock/v1',
		'/cart/add-personalized',
		array(
			'methods'             => 'POST',
			'callback'            => 'fairflock_rest_add_personalized_to_cart',
			'permission_callback' => 'fairflock_rest_personalized_cart_permission',
		)
	);
}
add_action( 'rest_api_init', 'fairflock_personalization_register_rest_routes' );
add_action( 'woocommerce_init', 'fairflock_personalization_register_store_api_extension', 20 );

/**
 * Permission: valid wp_rest nonce + light rate limit (guests ok via cookie nonce).
 *
 * @since 0.9.224
 *
 * @param WP_REST_Request $request Request.
 * @return true|WP_Error
 */
function fairflock_rest_personalized_cart_permission( $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! is_string( $nonce ) || '' === $nonce ) {
		$nonce = (string) $request->get_param( '_wpnonce' );
	}

	if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error(
			'ff_rest_forbidden',
			__( 'Ungültige oder abgelaufene Anfrage. Bitte Seite neu laden.', 'fairflock' ),
			array( 'status' => 403 )
		);
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
	$key = 'ff_pers_rl_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 40 ) {
		return new WP_Error(
			'ff_rest_rate',
			__( 'Zu viele Anfragen. Bitte kurz warten.', 'fairflock' ),
			array( 'status' => 429 )
		);
	}
	set_transient( $key, $n + 1, MINUTE_IN_SECONDS );

	return true;
}

/**
 * Registers Store API extension on woocommerce_init (preferred timing).
 *
 * @since 0.9.153
 *
 * @return void
 */
function fairflock_personalization_register_store_api_extension() {
	if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
		return;
	}
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	woocommerce_store_api_register_endpoint_data(
		array(
			'endpoint'        => 'product',
			'namespace'       => 'fairflock',
			'data_callback'   => 'fairflock_store_api_product_personalization_data',
			'schema_callback' => 'fairflock_store_api_product_personalization_schema',
		)
	);
}

/**
 * Store API product personalization flags.
 *
 * @since 0.9.153
 *
 * @param WC_Product $product Product.
 * @return array{logo_upload:bool,personal_text:bool,markup:string}
 */
function fairflock_store_api_product_personalization_data( $product ) {
	$upload   = fairflock_product_has_logo_upload( $product );
	$text     = fairflock_product_has_personal_text( $product );
	$delivery = function_exists( 'fairflock_product_delivery_view' ) ? fairflock_product_delivery_view( $product ) : null;
	return array(
		'logo_upload'      => $upload,
		'personal_text'    => $text,
		'enabled'          => ( $upload || $text ),
		'markup'           => ( $upload || $text ) ? fairflock_get_personalization_markup( $product, '_modal' ) : '',
		'table_html'       => function_exists( 'fairflock_product_table_markup' ) ? fairflock_product_table_markup( $product ) : '',
		'delivery_label'   => is_array( $delivery ) ? $delivery['label'] : '',
		'delivery_tooltip' => is_array( $delivery ) ? $delivery['tooltip'] : '',
	);
}

/**
 * Store API schema for personalization.
 *
 * @since 0.9.153
 *
 * @return array
 */
function fairflock_store_api_product_personalization_schema() {
	return array(
		'logo_upload'   => array(
			'description' => __( 'Logo upload enabled', 'fairflock' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		),
		'personal_text' => array(
			'description' => __( 'Personal text enabled', 'fairflock' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		),
		'enabled'       => array(
			'description' => __( 'Any personalization enabled', 'fairflock' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		),
		'markup'        => array(
			'description' => __( 'Personalization card HTML', 'fairflock' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		),
		'table_html'    => array(
			'description' => __( 'Product table HTML shown under the description', 'fairflock' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		),
		'delivery_label' => array(
			'description' => __( 'Delivery line shown with the product', 'fairflock' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		),
		'delivery_tooltip' => array(
			'description' => __( 'Optional delivery hint for the info icon', 'fairflock' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		),
	);
}

/**
 * Variation id posted with a personalized add, only if it belongs to the product.
 *
 * @since 0.9.272
 *
 * @param WP_REST_Request $request   Request.
 * @param int             $parent_id Parent product id.
 * @return int
 */
function fairflock_personalization_variation_id( $request, $parent_id ) {
	$variation_id = absint( $request->get_param( 'variation_id' ) );
	if ( ! $variation_id || ! function_exists( 'wc_get_product' ) ) {
		return 0;
	}

	$variation = wc_get_product( $variation_id );
	if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
		return 0;
	}

	if ( (int) $variation->get_parent_id() !== (int) $parent_id ) {
		return 0;
	}

	return $variation_id;
}

/**
 * Attribute map Woo expects when a variation is added to the cart.
 *
 * @since 0.9.272
 *
 * @param WP_REST_Request $request   Request.
 * @param int             $parent_id Parent product id.
 * @return array<string, string>
 */
function fairflock_personalization_variation_attrs( $request, $parent_id ) {
	$variation_id = fairflock_personalization_variation_id( $request, $parent_id );
	if ( ! $variation_id ) {
		return array();
	}

	$variation = wc_get_product( $variation_id );
	if ( ! $variation || ! method_exists( $variation, 'get_variation_attributes' ) ) {
		return array();
	}

	$attrs = $variation->get_variation_attributes();
	return is_array( $attrs ) ? $attrs : array();
}

/**
 * REST: add personalized product to cart (supports file upload).
 *
 * @since 0.9.153
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function fairflock_rest_add_personalized_to_cart( $request ) {
	if ( ! function_exists( 'WC' ) ) {
		return new WP_Error( 'ff_no_cart', __( 'Warenkorb nicht verfügbar.', 'fairflock' ), array( 'status' => 400 ) );
	}

	if ( null === WC()->cart && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}

	if ( ! WC()->cart ) {
		return new WP_Error( 'ff_no_cart', __( 'Warenkorb nicht verfügbar.', 'fairflock' ), array( 'status' => 400 ) );
	}

	$product_id = absint( $request->get_param( 'id' ) );
	$quantity   = max( 1, absint( $request->get_param( 'quantity' ) ) );
	if ( ! $product_id ) {
		return new WP_Error( 'ff_no_product', __( 'Produkt fehlt.', 'fairflock' ), array( 'status' => 400 ) );
	}

	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_purchasable() ) {
		return new WP_Error( 'ff_bad_product', __( 'Produkt nicht bestellbar.', 'fairflock' ), array( 'status' => 400 ) );
	}

	$upload_on = fairflock_product_has_logo_upload( $product );
	$text_on   = fairflock_product_has_personal_text( $product );
	if ( ! $upload_on && ! $text_on ) {
		return new WP_Error( 'ff_no_personalization', __( 'Personalisierung ist für dieses Produkt nicht aktiv.', 'fairflock' ), array( 'status' => 400 ) );
	}

	// Mirror $_POST / $_FILES for existing cart helpers.
	$mode = sanitize_key( (string) $request->get_param( 'fairflock_personalization_mode' ) );
	$text = sanitize_text_field( (string) $request->get_param( 'fairflock_personal_text' ) );
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$_POST['fairflock_personalization_mode'] = $mode;
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$_POST['fairflock_personal_text']        = $text;

	$files = $request->get_file_params();
	if ( ! empty( $files['fairflock_logo_upload'] ) ) {
		$_FILES['fairflock_logo_upload'] = $files['fairflock_logo_upload'];
	}

	$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
	if ( ! $passed ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();
		$msg = __( 'Angaben prüfen und erneut versuchen.', 'fairflock' );
		if ( ! empty( $notices[0]['notice'] ) ) {
			$msg = wp_strip_all_tags( $notices[0]['notice'] );
		}
		return new WP_Error( 'ff_validation', $msg, array( 'status' => 400 ) );
	}

	$cart_item_data = array();

	if ( WC()->cart && ! did_action( 'woocommerce_load_cart_from_session' ) ) {
		WC()->cart->get_cart();
	}

	$key = WC()->cart->add_to_cart( $product_id, $quantity, fairflock_personalization_variation_id( $request, $product_id ), fairflock_personalization_variation_attrs( $request, $product_id ), $cart_item_data );
	if ( ! $key ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();
		$msg = __( 'Konnte nicht in den Warenkorb gelegt werden.', 'fairflock' );
		if ( ! empty( $notices[0]['notice'] ) ) {
			$msg = wp_strip_all_tags( $notices[0]['notice'] );
		}
		return new WP_Error( 'ff_add_failed', $msg, array( 'status' => 400 ) );
	}

	wc_clear_notices();

	return rest_ensure_response(
		array(
			'success'       => true,
			'cart_item_key' => $key,
			'cart_count'    => WC()->cart->get_cart_contents_count(),
		)
	);
}

/**
 * Validates personalization on add to cart.
 *
 * @since 0.9.151
 *
 * @param bool $passed     Validation state.
 * @param int  $product_id Product ID.
 * @return bool
 */
function fairflock_personalization_validate_add_to_cart( $passed, $product_id ) {
	$enable_upload = fairflock_product_has_logo_upload( $product_id );
	$enable_text   = fairflock_product_has_personal_text( $product_id );

	if ( ! $enable_upload && ! $enable_text ) {
		return $passed;
	}

	$labels  = fairflock_personalization_labels( $product_id );
	$file_ok = false;
	$text_ok = false;

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$mode = isset( $_POST['fairflock_personalization_mode'] ) ? sanitize_key( wp_unslash( $_POST['fairflock_personalization_mode'] ) ) : '';

	if ( $enable_upload && $enable_text && in_array( $mode, array( 'upload', 'text' ), true ) ) {
		// Customer chose one path via the frontend switch.
		$enable_upload = ( 'upload' === $mode );
		$enable_text   = ( 'text' === $mode );
	}

	if ( $enable_upload ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- add-to-cart request.
		if ( ! empty( $_FILES['fairflock_logo_upload']['name'] ) && empty( $_FILES['fairflock_logo_upload']['error'] ) ) {
			$check = fairflock_personalization_validate_uploaded_file( $_FILES['fairflock_logo_upload'], $product_id );
			if ( is_wp_error( $check ) ) {
				wc_add_notice( $check->get_error_message(), 'error' );
				return false;
			}
			$file_ok = true;
		} elseif ( ! empty( $_FILES['fairflock_logo_upload']['error'] ) && UPLOAD_ERR_NO_FILE !== (int) $_FILES['fairflock_logo_upload']['error'] ) {
			wc_add_notice( __( 'Der Upload ist fehlgeschlagen. Bitte versuche es erneut.', 'fairflock' ), 'error' );
			return false;
		}
	}

	if ( $enable_text ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$raw = isset( $_POST['fairflock_personal_text'] ) ? wp_unslash( $_POST['fairflock_personal_text'] ) : '';
		$text = sanitize_text_field( $raw );
		if ( '' !== $text ) {
			$text_len = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
			if ( $text_len > FAIRFLOCK_TEXT_MAX_LENGTH ) {
				wc_add_notice(
					sprintf(
						/* translators: %d: max characters */
						__( 'Der persönliche Text darf höchstens %d Zeichen haben.', 'fairflock' ),
						FAIRFLOCK_TEXT_MAX_LENGTH
					),
					'error'
				);
				return false;
			}
			$text_ok = true;
		}
	}

	$satisfied = false;
	if ( $enable_upload && $enable_text ) {
		$satisfied = $file_ok || $text_ok;
		$msg       = sprintf(
			/* translators: 1: upload title, 2: text title */
			__( 'Bitte „%1$s“ oder „%2$s“ angeben (eines reicht).', 'fairflock' ),
			$labels['upload_label'],
			$labels['text_label']
		);
	} elseif ( $enable_upload ) {
		$satisfied = $file_ok;
		$msg       = sprintf(
			/* translators: %s: field title */
			__( 'Bitte „%s“ angeben.', 'fairflock' ),
			$labels['upload_label']
		);
	} else {
		$satisfied = $text_ok;
		$msg       = sprintf(
			/* translators: %s: field title */
			__( 'Bitte „%s“ angeben.', 'fairflock' ),
			$labels['text_label']
		);
	}

	if ( ! $satisfied ) {
		wc_add_notice( $msg, 'error' );
		return false;
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'fairflock_personalization_validate_add_to_cart', 10, 2 );

/**
 * Validates an uploaded personalization file.
 *
 * @since 0.9.151
 * @since 0.9.318 Product-scoped formats.
 *
 * @param array                $file    $_FILES element.
 * @param int|\WC_Product|null $product Optional product for allowed formats.
 * @return true|\WP_Error
 */
function fairflock_personalization_validate_uploaded_file( $file, $product = null ) {
	if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return new WP_Error( 'ff_upload', __( 'Ungültiger Upload.', 'fairflock' ) );
	}

	if ( ! empty( $file['size'] ) && (int) $file['size'] > FAIRFLOCK_UPLOAD_MAX_BYTES ) {
		return new WP_Error( 'ff_upload_size', __( 'Die Datei ist zu groß (max. 5 MB).', 'fairflock' ) );
	}

	$filename = isset( $file['name'] ) ? $file['name'] : '';
	$ext      = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	$allowed  = fairflock_personalization_allowed_mimes( $product );
	$formats  = fairflock_personalization_formats_label( $product );

	if ( ! isset( $allowed[ $ext ] ) ) {
		return new WP_Error(
			'ff_upload_type',
			$formats
				? sprintf(
					/* translators: %s: allowed formats */
					__( 'Erlaubt sind: %s.', 'fairflock' ),
					$formats
				)
				: __( 'Dateityp nicht erlaubt.', 'fairflock' )
		);
	}

	$ft       = wp_check_filetype_and_ext( $file['tmp_name'], $filename, $allowed );
	$filetype = isset( $ft['type'] ) ? $ft['type'] : '';

	if ( ! $filetype || ! in_array( $filetype, $allowed, true ) ) {
		return new WP_Error( 'ff_upload_mime', __( 'Dateityp nicht erlaubt.', 'fairflock' ) );
	}

	return true;
}

/**
 * Uploads personalization file into a dedicated uploads subdir.
 *
 * @since 0.9.151
 * @since 0.9.318 Product-scoped formats.
 *
 * @param array                $file    $_FILES element.
 * @param int|\WC_Product|null $product Optional product for allowed formats.
 * @return array|\WP_Error { file, url, type, name }
 */
function fairflock_personalization_handle_upload( $file, $product = null ) {
	$valid = fairflock_personalization_validate_uploaded_file( $file, $product );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	$GLOBALS['fairflock_personalization_upload_product'] = fairflock_personalization_product_id( $product );

	fairflock_personalization_ensure_upload_dir();

	add_filter( 'upload_dir', 'fairflock_personalization_filter_upload_dir' );
	add_filter( 'upload_mimes', 'fairflock_personalization_filter_upload_mimes' );

	$overrides = array(
		'test_form' => false,
		'mimes'     => fairflock_personalization_allowed_mimes( $product ),
	);

	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$movefile = wp_handle_upload( $file, $overrides );

	remove_filter( 'upload_dir', 'fairflock_personalization_filter_upload_dir' );
	remove_filter( 'upload_mimes', 'fairflock_personalization_filter_upload_mimes' );
	$GLOBALS['fairflock_personalization_upload_product'] = 0;

	if ( isset( $movefile['error'] ) ) {
		return new WP_Error( 'ff_upload_move', $movefile['error'] );
	}

	return array(
		'file' => $movefile['file'],
		'url'  => $movefile['url'],
		'type' => $movefile['type'],
		'name' => basename( $movefile['file'] ),
	);
}

/**
 * Restricts upload_dir to fairflock-personalization.
 *
 * @since 0.9.151
 *
 * @param array $dirs Upload dirs.
 * @return array
 */
function fairflock_personalization_filter_upload_dir( $dirs ) {
	$subdir = '/fairflock-personalization';
	$dirs['subdir'] = $subdir;
	$dirs['path']   = $dirs['basedir'] . $subdir;
	$dirs['url']    = $dirs['baseurl'] . $subdir;
	return $dirs;
}

/**
 * Restricts allowed mimes during personalization upload.
 *
 * @since 0.9.151
 *
 * @param array $mimes Mime map.
 * @return array
 */
function fairflock_personalization_filter_upload_mimes( $mimes ) {
	$product = ! empty( $GLOBALS['fairflock_personalization_upload_product'] )
		? (int) $GLOBALS['fairflock_personalization_upload_product']
		: null;
	return fairflock_personalization_allowed_mimes( $product );
}

/**
 * Creates upload directory with index protection.
 *
 * @since 0.9.151
 *
 * @return void
 */
function fairflock_personalization_ensure_upload_dir() {
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		return;
	}

	$dir = trailingslashit( $upload['basedir'] ) . 'fairflock-personalization';
	if ( ! wp_mkdir_p( $dir ) ) {
		return;
	}

	$index = trailingslashit( $dir ) . 'index.php';
	if ( ! file_exists( $index ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $index, "<?php\n// Silence is golden.\n" );
	}

	$htaccess = trailingslashit( $dir ) . '.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $htaccess, "Options -Indexes\n" );
	}
}

/**
 * Attaches personalization data to the cart item.
 *
 * @since 0.9.151
 *
 * @param array $cart_item_data Cart item data.
 * @param int   $product_id    Product ID.
 * @return array
 */
function fairflock_personalization_add_cart_item_data( $cart_item_data, $product_id ) {
	$enable_upload = fairflock_product_has_logo_upload( $product_id );
	$enable_text   = fairflock_product_has_personal_text( $product_id );

	if ( ! $enable_upload && ! $enable_text ) {
		return $cart_item_data;
	}

	$labels = fairflock_personalization_labels( $product_id );

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$mode = isset( $_POST['fairflock_personalization_mode'] ) ? sanitize_key( wp_unslash( $_POST['fairflock_personalization_mode'] ) ) : '';
	if ( $enable_upload && $enable_text && in_array( $mode, array( 'upload', 'text' ), true ) ) {
		$enable_upload = ( 'upload' === $mode );
		$enable_text   = ( 'text' === $mode );
	}

	if ( $enable_upload && ! empty( $_FILES['fairflock_logo_upload']['name'] ) ) {
		$uploaded = fairflock_personalization_handle_upload( $_FILES['fairflock_logo_upload'], $product_id );
		if ( is_wp_error( $uploaded ) ) {
			wc_add_notice( $uploaded->get_error_message(), 'error' );
			return $cart_item_data;
		}
		$original_name = isset( $_FILES['fairflock_logo_upload']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['fairflock_logo_upload']['name'] ) ) : '';
		$cart_item_data[ FAIRFLOCK_CART_UPLOAD_PATH ]  = $uploaded['file'];
		$cart_item_data[ FAIRFLOCK_CART_UPLOAD_URL ]   = $uploaded['url'];
		$cart_item_data[ FAIRFLOCK_CART_UPLOAD_NAME ]  = $original_name ? $original_name : $uploaded['name'];
		$cart_item_data[ FAIRFLOCK_CART_UPLOAD_LABEL ] = $labels['upload_label'];
		$cart_item_data['unique_key']                  = md5( microtime() . $uploaded['file'] );
	}

	if ( $enable_text ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$raw  = isset( $_POST['fairflock_personal_text'] ) ? wp_unslash( $_POST['fairflock_personal_text'] ) : '';
		$text = sanitize_text_field( $raw );
		if ( '' !== $text ) {
			$cart_item_data[ FAIRFLOCK_CART_TEXT ]       = function_exists( 'mb_substr' )
				? mb_substr( $text, 0, FAIRFLOCK_TEXT_MAX_LENGTH )
				: substr( $text, 0, FAIRFLOCK_TEXT_MAX_LENGTH );
			$cart_item_data[ FAIRFLOCK_CART_TEXT_LABEL ] = $labels['text_label'];
			if ( empty( $cart_item_data['unique_key'] ) ) {
				$cart_item_data['unique_key'] = md5( microtime() . $text );
			}
		}
	}

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'fairflock_personalization_add_cart_item_data', 10, 2 );

/**
 * Displays personalization in cart and checkout.
 *
 * @since 0.9.151
 * @since 0.9.156 Uses custom field titles.
 *
 * @param array $item_data Item data rows.
 * @param array $cart_item Cart item.
 * @return array
 */
function fairflock_personalization_get_item_data( $item_data, $cart_item ) {
	if ( ! empty( $cart_item[ FAIRFLOCK_CART_UPLOAD_NAME ] ) ) {
		$label = ! empty( $cart_item[ FAIRFLOCK_CART_UPLOAD_LABEL ] )
			? (string) $cart_item[ FAIRFLOCK_CART_UPLOAD_LABEL ]
			: __( 'Logo', 'fairflock' );
		$item_data[] = array(
			'key'   => $label,
			'value' => esc_html( $cart_item[ FAIRFLOCK_CART_UPLOAD_NAME ] ),
		);
	}
	if ( ! empty( $cart_item[ FAIRFLOCK_CART_TEXT ] ) ) {
		$label = ! empty( $cart_item[ FAIRFLOCK_CART_TEXT_LABEL ] )
			? (string) $cart_item[ FAIRFLOCK_CART_TEXT_LABEL ]
			: __( 'Persönlicher Text', 'fairflock' );
		$item_data[] = array(
			'key'   => $label,
			'value' => esc_html( $cart_item[ FAIRFLOCK_CART_TEXT ] ),
		);
	}
	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'fairflock_personalization_get_item_data', 10, 2 );

/**
 * Persists personalization on the order line item.
 *
 * @since 0.9.151
 *
 * @param WC_Order_Item_Product $item          Order item.
 * @param string                $cart_item_key Cart key.
 * @param array                 $values        Cart item values.
 * @return void
 */
function fairflock_personalization_checkout_create_order_line_item( $item, $cart_item_key, $values ) {
	if ( ! empty( $values[ FAIRFLOCK_CART_UPLOAD_URL ] ) ) {
		$item->add_meta_data( FAIRFLOCK_ORDER_UPLOAD_URL, esc_url_raw( $values[ FAIRFLOCK_CART_UPLOAD_URL ] ), true );
		$name = ! empty( $values[ FAIRFLOCK_CART_UPLOAD_NAME ] ) ? $values[ FAIRFLOCK_CART_UPLOAD_NAME ] : __( 'Datei', 'fairflock' );
		$item->add_meta_data( FAIRFLOCK_ORDER_UPLOAD_NAME, sanitize_file_name( $name ), true );
		$upload_label = ! empty( $values[ FAIRFLOCK_CART_UPLOAD_LABEL ] )
			? sanitize_text_field( $values[ FAIRFLOCK_CART_UPLOAD_LABEL ] )
			: __( 'Logo', 'fairflock' );
		$item->add_meta_data(
			$upload_label,
			esc_url_raw( $values[ FAIRFLOCK_CART_UPLOAD_URL ] ),
			true
		);
	}
	if ( ! empty( $values[ FAIRFLOCK_CART_TEXT ] ) ) {
		$text = sanitize_text_field( $values[ FAIRFLOCK_CART_TEXT ] );
		$item->add_meta_data( FAIRFLOCK_ORDER_TEXT, $text, true );
		$text_label = ! empty( $values[ FAIRFLOCK_CART_TEXT_LABEL ] )
			? sanitize_text_field( $values[ FAIRFLOCK_CART_TEXT_LABEL ] )
			: __( 'Persönlicher Text', 'fairflock' );
		$item->add_meta_data( $text_label, $text, true );
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'fairflock_personalization_checkout_create_order_line_item', 10, 3 );

/**
 * Formats order item meta display for logo URL as a link.
 *
 * @since 0.9.151
 *
 * @param string       $display_value Display value.
 * @param WC_Meta_Data $meta          Meta object.
 * @param WC_Order_Item $item         Order item.
 * @return string
 */
function fairflock_personalization_order_item_display_meta( $display_value, $meta, $item ) {
	if ( ! $meta instanceof WC_Meta_Data ) {
		return $display_value;
	}
	$data = $meta->get_data();
	$key  = isset( $data['key'] ) ? $data['key'] : '';
	$val  = isset( $data['value'] ) ? $data['value'] : '';

	if ( FAIRFLOCK_ORDER_UPLOAD_URL === $key || __( 'Logo', 'fairflock' ) === $key || ( $item instanceof WC_Order_Item && (string) $item->get_meta( FAIRFLOCK_ORDER_UPLOAD_URL, true ) === (string) $val && $val ) ) {
		$url = esc_url( $val );
		if ( $url ) {
			$label = '';
			if ( $item instanceof WC_Order_Item ) {
				$label = $item->get_meta( FAIRFLOCK_ORDER_UPLOAD_NAME, true );
			}
			if ( ! $label ) {
				$label = __( 'Datei herunterladen', 'fairflock' );
			}
			return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a>';
		}
	}

	// Hide internal meta keys from customer-facing lists when Woo shows all meta.
	if ( in_array( $key, array( FAIRFLOCK_ORDER_UPLOAD_NAME, FAIRFLOCK_ORDER_TEXT ), true ) ) {
		return $display_value;
	}

	return $display_value;
}
add_filter( 'woocommerce_order_item_display_meta_value', 'fairflock_personalization_order_item_display_meta', 10, 3 );

/**
 * Hides internal meta keys from order item meta display.
 *
 * @since 0.9.151
 *
 * @param array $hidden Hidden keys.
 * @return array
 */
function fairflock_personalization_hidden_order_item_meta( $hidden ) {
	$hidden[] = FAIRFLOCK_ORDER_UPLOAD_URL;
	$hidden[] = FAIRFLOCK_ORDER_UPLOAD_NAME;
	$hidden[] = FAIRFLOCK_ORDER_TEXT;
	return $hidden;
}
add_filter( 'woocommerce_hidden_order_itemmeta', 'fairflock_personalization_hidden_order_item_meta' );

/**
 * Whether the current product needs personalization (upload and/or text).
 *
 * @since 0.9.323
 *
 * @param WC_Product|null $product Product.
 * @return bool
 */
function fairflock_product_needs_buy_config( $product = null ) {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}
	if ( ! $product instanceof WC_Product ) {
		return false;
	}
	return fairflock_product_has_logo_upload( $product ) || fairflock_product_has_personal_text( $product );
}

/**
 * Opens the shared modal-style actions panel (Konfigurieren klappt Größe + Design aus).
 *
 * @since 0.9.325
 *
 * @return void
 */
function fairflock_product_buy_actions_open() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	static $opened = false;
	if ( $opened ) {
		return;
	}
	$opened = true;

	global $product;
	$needs = fairflock_product_needs_buy_config( $product instanceof WC_Product ? $product : null );

	echo '<div class="ff-product-modal__actions ff-product-buy__actions"'
		. ( $needs ? ' data-ff-buy-config="1"' : '' )
		. '>';

	if ( $needs ) {
		echo '<button type="button" class="ff-product-modal__collapse" data-ff-collapse aria-expanded="false" aria-label="' . esc_attr__( 'Konfiguration einklappen', 'fairflock' ) . '">';
		echo '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		echo '</button>';
	}
}
add_action( 'woocommerce_before_variations_form', 'fairflock_product_buy_actions_open', 1 );
add_action( 'woocommerce_before_add_to_cart_button', 'fairflock_product_buy_actions_open', 1 );

/**
 * Opens the collapsible config clip (Größe + Design + CTA) — same as product modal.
 *
 * @since 0.9.325
 * @since 0.9.327 Größe liegt im Clip und erscheint erst nach „Konfigurieren“.
 *
 * @return void
 */
function fairflock_product_buy_config_open() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	global $product;
	if ( ! fairflock_product_needs_buy_config( $product instanceof WC_Product ? $product : null ) ) {
		return;
	}
	static $opened = false;
	if ( $opened ) {
		return;
	}
	$opened = true;
	echo '<div class="ff-product-modal__config"><div class="ff-product-modal__config-clip">';
}
/* Vor der Größentabelle (variable) bzw. vor Design (simple). */
add_action( 'woocommerce_before_variations_form', 'fairflock_product_buy_config_open', 2 );
add_action( 'woocommerce_before_add_to_cart_button', 'fairflock_product_buy_config_open', 5 );

/**
 * Opens the CTA wrap (qty + add to cart).
 *
 * @since 0.9.319
 *
 * @return void
 */
function fairflock_product_buy_cta_open() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	echo '<div class="ff-product-buy__cta">';
}
add_action( 'woocommerce_before_add_to_cart_quantity', 'fairflock_product_buy_cta_open', 5 );

/**
 * Closes the CTA wrap only.
 *
 * @since 0.9.319
 * @since 0.9.328 Config/Konfigurieren schließen außerhalb von variation-add-to-cart.
 *
 * @return void
 */
function fairflock_product_buy_cta_close() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	echo '</div>'; // .ff-product-buy__cta
}
add_action( 'woocommerce_after_add_to_cart_button', 'fairflock_product_buy_cta_close', 50 );

/**
 * Closes the config clip after Größe/Design/CTA (not inside Woo’s variation button box).
 *
 * @since 0.9.328
 *
 * @return void
 */
function fairflock_product_buy_config_close() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	global $product;
	if ( ! fairflock_product_needs_buy_config( $product instanceof WC_Product ? $product : null ) ) {
		return;
	}
	/* Variable: after_add_to_cart_button feuert zu früh innerhalb von .variations_button. */
	if ( doing_action( 'woocommerce_after_add_to_cart_button' ) && $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
		return;
	}
	static $closed = false;
	if ( $closed ) {
		return;
	}
	$closed = true;
	echo '</div></div>'; // .ff-product-modal__config-clip + .ff-product-modal__config
}
add_action( 'woocommerce_after_variations_form', 'fairflock_product_buy_config_close', 40 );
add_action( 'woocommerce_after_add_to_cart_button', 'fairflock_product_buy_config_close', 55 );

/**
 * Closes the actions panel and outputs Konfigurieren as a sibling of the config clip.
 *
 * @since 0.9.325
 * @since 0.9.328 Konfigurieren außerhalb von .woocommerce-variation-add-to-cart.
 *
 * @return void
 */
function fairflock_product_buy_actions_close() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	static $closed = false;
	if ( $closed ) {
		return;
	}
	/* Variable: after_add_to_cart_button ist zu früh — erst nach dem Formular schließen. */
	global $product;
	if ( doing_action( 'woocommerce_after_add_to_cart_button' ) && $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
		return;
	}
	$closed = true;

	if ( fairflock_product_needs_buy_config( $product instanceof WC_Product ? $product : null ) ) {
		echo '<button type="button" class="ff-product-modal__configure" data-ff-configure>' . esc_html__( 'Konfigurieren', 'fairflock' ) . '</button>';
	}
	echo '</div>'; // .ff-product-buy__actions
}
add_action( 'woocommerce_after_variations_form', 'fairflock_product_buy_actions_close', 50 );
add_action( 'woocommerce_after_add_to_cart_button', 'fairflock_product_buy_actions_close', 60 );

/**
 * Minus button before the quantity input.
 *
 * @since 0.9.318
 *
 * @return void
 */
function fairflock_qty_button_minus() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	echo '<button type="button" class="ff-qty__btn" data-ff-qty="-1" aria-label="' . esc_attr__( 'Menge verringern', 'fairflock' ) . '">−</button>';
}
add_action( 'woocommerce_before_quantity_input_field', 'fairflock_qty_button_minus', 10 );

/**
 * Plus button after the quantity input.
 *
 * @since 0.9.318
 *
 * @return void
 */
function fairflock_qty_button_plus() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	echo '<button type="button" class="ff-qty__btn" data-ff-qty="1" aria-label="' . esc_attr__( 'Menge erhöhen', 'fairflock' ) . '">+</button>';
}
add_action( 'woocommerce_after_quantity_input_field', 'fairflock_qty_button_plus', 10 );

/**
 * Quantity stepper script on single product pages.
 *
 * @since 0.9.318
 *
 * @return void
 */
function fairflock_qty_stepper_script() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$js = <<<'JS'
(function(){
  function step(btn){
    var wrap=btn.closest(".quantity");
    if(!wrap){return;}
    var input=wrap.querySelector("input.qty,input[type=number]");
    if(!input||input.disabled){return;}
    var delta=parseInt(btn.getAttribute("data-ff-qty")||"0",10);
    if(!delta){return;}
    var min=input.min!==""?parseFloat(input.min):1;
    var max=input.max!==""?parseFloat(input.max):NaN;
    var step=input.step!==""?parseFloat(input.step):1;
    if(!step||step<=0){step=1;}
    var val=parseFloat(input.value);
    if(isNaN(val)){val=min;}
    val=val+delta*step;
    if(val<min){val=min;}
    if(!isNaN(max)&&val>max){val=max;}
    input.value=String(val);
    input.dispatchEvent(new Event("change",{bubbles:true}));
  }
  document.addEventListener("click",function(e){
    var btn=e.target.closest("[data-ff-qty]");
    if(!btn){return;}
    e.preventDefault();
    step(btn);
  });
})();
JS;

	wp_register_script( 'fairflock-qty-stepper', false, array(), FAIRFLOCK_VERSION, true );
	wp_enqueue_script( 'fairflock-qty-stepper' );
	wp_add_inline_script( 'fairflock-qty-stepper', $js );
}
add_action( 'wp_enqueue_scripts', 'fairflock_qty_stepper_script', 25 );
