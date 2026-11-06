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

/** Meta: optional hint under upload field. */
const FAIRFLOCK_META_UPLOAD_HINT = '_fairflock_upload_hint';

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
 * Customer-facing labels for upload / text (with defaults).
 *
 * @since 0.9.156
 *
 * @param int|\WC_Product $product Product.
 * @return array{upload_label:string,upload_hint:string,text_label:string,text_hint:string}
 */
function fairflock_personalization_labels( $product ) {
	$id = fairflock_personalization_product_id( $product );

	$upload_label = $id ? trim( (string) get_post_meta( $id, FAIRFLOCK_META_UPLOAD_LABEL, true ) ) : '';
	$upload_hint  = $id ? trim( (string) get_post_meta( $id, FAIRFLOCK_META_UPLOAD_HINT, true ) ) : '';
	$text_label   = $id ? trim( (string) get_post_meta( $id, FAIRFLOCK_META_TEXT_LABEL, true ) ) : '';
	$text_hint    = $id ? trim( (string) get_post_meta( $id, FAIRFLOCK_META_TEXT_HINT, true ) ) : '';

	if ( '' === $upload_label ) {
		$upload_label = __( 'Logo hochladen', 'fairflock' );
	}
	if ( '' === $upload_hint ) {
		$upload_hint = __( 'PNG, JPG oder PDF, max. 5 MB.', 'fairflock' );
	}
	if ( '' === $text_label ) {
		$text_label = __( 'Persönlicher Text', 'fairflock' );
	}

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
 * Allowed upload extensions and MIME types.
 *
 * @since 0.9.151
 *
 * @return array<string, string> extension => mime
 */
function fairflock_personalization_allowed_mimes() {
	// SVG absichtlich nicht: XSS-Risiko ohne Sanitizer (Audit 0.9.224).
	return array(
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'pdf'  => 'application/pdf',
	);
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
			'label'       => __( 'Titel Bild-Upload', 'fairflock' ),
			'placeholder' => __( 'z. B. Vereinslogo / Druckmotiv', 'fairflock' ),
			'description' => __( 'Wird dem Kunden als Feldtitel angezeigt.', 'fairflock' ),
			'desc_tip'    => true,
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => FAIRFLOCK_META_UPLOAD_HINT,
			'label'       => __( 'Hinweis Bild-Upload', 'fairflock' ),
			'placeholder' => __( 'z. B. PNG oder PDF, max. 5 MB', 'fairflock' ),
			'description' => __( 'Optionaler Hinweis unter dem Upload-Feld.', 'fairflock' ),
			'desc_tip'    => true,
		)
	);

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
			'label'       => __( 'Titel Freitext', 'fairflock' ),
			'placeholder' => __( 'z. B. Name auf dem Trikot', 'fairflock' ),
			'description' => __( 'Wird dem Kunden als Feldtitel angezeigt.', 'fairflock' ),
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
		FAIRFLOCK_META_UPLOAD_HINT,
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
	} elseif ( $enable_upload ) {
		echo '<p class="ff-personalization__hint">' . esc_html( $need_upload ) . '</p>';
	} else {
		echo '<p class="ff-personalization__hint">' . esc_html( $need_text ) . '</p>';
	}

	if ( $enable_upload ) {
		echo '<div class="ff-personalization__panel ff-personalization__panel--upload"' . ( $both ? ' data-ff-panel="upload"' : '' ) . '>';
		echo '<p class="ff-personalization__field ff-personalization__field--upload">';
		echo '<label for="' . esc_attr( $uid_file ) . '">' . esc_html( $labels['upload_label'] ) . '</label>';
		echo '<span class="ff-personalization__file">';
		echo '<input type="file" class="ff-personalization__file-input" id="' . esc_attr( $uid_file ) . '" name="fairflock_logo_upload" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" />';
		echo '<span class="ff-personalization__file-name" data-ff-file-name data-empty="' . esc_attr__( 'Keine Datei ausgewählt', 'fairflock' ) . '">' . esc_html__( 'Keine Datei ausgewählt', 'fairflock' ) . '</span>';
		echo '<button type="button" class="ff-personalization__file-change" data-ff-file-pick>' . esc_html__( 'Datei auswählen', 'fairflock' ) . '</button>';
		echo '</span>';
		if ( '' !== $labels['upload_hint'] ) {
			echo '<span class="ff-personalization__note">' . esc_html( $labels['upload_hint'] ) . '</span>';
		}
		echo '</p>';
		echo '</div>';
	}

	if ( $enable_text ) {
		echo '<div class="ff-personalization__panel ff-personalization__panel--text"' . ( $both ? ' data-ff-panel="text" hidden' : '' ) . '>';
		echo '<p class="ff-personalization__field ff-personalization__field--text">';
		echo '<label for="' . esc_attr( $uid_text ) . '">' . esc_html( $labels['text_label'] ) . '</label>';
		printf(
			'<input type="text" id="%s" name="fairflock_personal_text" maxlength="%d" autocomplete="off" />',
			esc_attr( $uid_text ),
			(int) FAIRFLOCK_TEXT_MAX_LENGTH
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

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped inside helper.
	echo fairflock_get_personalization_markup( $product );
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
    if(fileBtn){fileBtn.textContent=chosen?"ändern":"Datei auswählen";}
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

	wp_register_script(
		'fairflock-personalization',
		false,
		array(),
		FAIRFLOCK_VERSION,
		true
	);
	wp_enqueue_script( 'fairflock-personalization' );
	wp_add_inline_script( 'fairflock-personalization', $js_init );
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
	$upload = fairflock_product_has_logo_upload( $product );
	$text   = fairflock_product_has_personal_text( $product );
	return array(
		'logo_upload'    => $upload,
		'personal_text'  => $text,
		'enabled'        => ( $upload || $text ),
		'markup'         => ( $upload || $text ) ? fairflock_get_personalization_markup( $product, '_modal' ) : '',
		'table_html'     => function_exists( 'fairflock_product_table_markup' ) ? fairflock_product_table_markup( $product ) : '',
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
			$check = fairflock_personalization_validate_uploaded_file( $_FILES['fairflock_logo_upload'] );
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
 *
 * @param array $file $_FILES element.
 * @return true|\WP_Error
 */
function fairflock_personalization_validate_uploaded_file( $file ) {
	if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return new WP_Error( 'ff_upload', __( 'Ungültiger Upload.', 'fairflock' ) );
	}

	if ( ! empty( $file['size'] ) && (int) $file['size'] > FAIRFLOCK_UPLOAD_MAX_BYTES ) {
		return new WP_Error( 'ff_upload_size', __( 'Die Datei ist zu groß (max. 5 MB).', 'fairflock' ) );
	}

	$filename = isset( $file['name'] ) ? $file['name'] : '';
	$ext      = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	$allowed  = fairflock_personalization_allowed_mimes();

	if ( ! isset( $allowed[ $ext ] ) ) {
		return new WP_Error( 'ff_upload_type', __( 'Erlaubt sind PNG, JPG oder PDF.', 'fairflock' ) );
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
 *
 * @param array $file $_FILES element.
 * @return array|\WP_Error { file, url, type, name }
 */
function fairflock_personalization_handle_upload( $file ) {
	$valid = fairflock_personalization_validate_uploaded_file( $file );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	fairflock_personalization_ensure_upload_dir();

	add_filter( 'upload_dir', 'fairflock_personalization_filter_upload_dir' );
	add_filter( 'upload_mimes', 'fairflock_personalization_filter_upload_mimes' );

	$overrides = array(
		'test_form' => false,
		'mimes'     => fairflock_personalization_allowed_mimes(),
	);

	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$movefile = wp_handle_upload( $file, $overrides );

	remove_filter( 'upload_dir', 'fairflock_personalization_filter_upload_dir' );
	remove_filter( 'upload_mimes', 'fairflock_personalization_filter_upload_mimes' );

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
	return fairflock_personalization_allowed_mimes();
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
		$uploaded = fairflock_personalization_handle_upload( $_FILES['fairflock_logo_upload'] );
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
