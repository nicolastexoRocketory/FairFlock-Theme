<?php
/**
 * Global delivery times and the product shipping class that picks one.
 *
 * @package FairFlock
 * @since 0.9.295
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FAIRFLOCK_DELIVERY_TIMES_OPTION = 'fairflock_delivery_times';
const FAIRFLOCK_META_DELIVERY_TIME    = '_fairflock_delivery_time';
const FAIRFLOCK_DELIVERY_TIMES_MAX    = 40;

/**
 * Saved delivery classes.
 *
 * @since 0.9.295
 *
 * @return array<int,array{id:string,label:string,tooltip:string}>
 */
function fairflock_delivery_times() {
	$stored = get_option( FAIRFLOCK_DELIVERY_TIMES_OPTION, array() );
	if ( ! is_array( $stored ) ) {
		return array();
	}

	$times = array();
	foreach ( $stored as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$clean = fairflock_delivery_time_clean( $item );
		if ( null === $clean ) {
			continue;
		}
		$times[] = $clean;
	}

	return $times;
}

/**
 * One class, or null.
 *
 * @since 0.9.295
 *
 * @param string $id Class id.
 * @return array{id:string,label:string,tooltip:string}|null
 */
function fairflock_delivery_time_find( $id ) {
	$id = sanitize_key( (string) $id );
	if ( '' === $id ) {
		return null;
	}
	foreach ( fairflock_delivery_times() as $time ) {
		if ( $time['id'] === $id ) {
			return $time;
		}
	}
	return null;
}

/**
 * Normalizes one posted or stored class.
 *
 * @since 0.9.295
 *
 * @param array $item Raw item.
 * @return array{id:string,label:string,tooltip:string}|null
 */
function fairflock_delivery_time_clean( $item ) {
	$id = isset( $item['id'] ) ? sanitize_key( (string) $item['id'] ) : '';
	if ( ! preg_match( '/^ffdt_[a-z0-9]+$/', $id ) ) {
		$id = 'ffdt_' . strtolower( wp_generate_password( 8, false, false ) );
	}

	$label = isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '';
	$label = function_exists( 'mb_substr' ) ? mb_substr( $label, 0, 80 ) : substr( $label, 0, 80 );
	if ( '' === $label ) {
		return null;
	}

	$tooltip = isset( $item['tooltip'] ) ? sanitize_text_field( (string) $item['tooltip'] ) : '';
	$tooltip = function_exists( 'mb_substr' ) ? mb_substr( $tooltip, 0, 240 ) : substr( $tooltip, 0, 240 );

	return array(
		'id'      => $id,
		'label'   => $label,
		'tooltip' => $tooltip,
	);
}

/**
 * Label and optional hint for a product.
 *
 * A chosen class wins. Without one, a „Lieferzeit“ in the description still counts.
 *
 * @since 0.9.295
 *
 * @param WC_Product|int $product Product.
 * @return array{label:string,tooltip:string}|null
 */
function fairflock_product_delivery_view( $product ) {
	if ( is_numeric( $product ) && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product instanceof WC_Product ) {
		return null;
	}

	$choice = fairflock_delivery_time_find( (string) $product->get_meta( FAIRFLOCK_META_DELIVERY_TIME ) );
	if ( is_array( $choice ) ) {
		return array(
			'label'   => $choice['label'],
			'tooltip' => $choice['tooltip'],
		);
	}

	$label = fairflock_product_delivery_from_text( $product );
	if ( '' === $label ) {
		return null;
	}

	return array(
		'label'   => $label,
		'tooltip' => '',
	);
}

/**
 * Reads „Lieferzeit“ or „sofort“ from the product texts.
 *
 * @since 0.9.292
 *
 * @param WC_Product $product Product.
 * @return string
 */
function fairflock_product_delivery_from_text( $product ) {
	$short = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $product->get_short_description() ) ) );
	$long  = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $product->get_description() ) ) );
	$raw   = trim( $short . ' ' . $long );

	if ( preg_match( '/Lieferzeit\s*:?\s*([0-9]+\s*[–\-]\s*[0-9]+|[0-9]+)/iu', $raw, $match ) ) {
		$span = str_replace( '-', '–', (string) preg_replace( '/\s+/u', '', $match[1] ) );
		return sprintf(
			/* translators: %s: day span such as 3–5 or 7. */
			__( 'Lieferung in %s Tagen', 'fairflock' ),
			$span
		);
	}

	if ( preg_match( '/sofort/iu', $short ) ) {
		return __( 'Lieferung in 1–2 Tagen', 'fairflock' );
	}

	return '';
}

/**
 * Info icon with a tooltip. Empty when there is no hint.
 *
 * @since 0.9.295
 *
 * @param string $tooltip Hint text.
 * @return string
 */
function fairflock_delivery_info_markup( $tooltip ) {
	$tooltip = trim( (string) $tooltip );
	if ( '' === $tooltip ) {
		return '';
	}

	$id = wp_unique_id( 'ff-delivery-tip-' );

	return '<span class="ff-delivery-info">'
		. '<button type="button" class="ff-delivery-info__btn" aria-describedby="' . esc_attr( $id ) . '" aria-label="' . esc_attr__( 'Hinweis zur Lieferzeit', 'fairflock' ) . '"><span aria-hidden="true">i</span></button>'
		. '<span class="ff-delivery-info__tip" id="' . esc_attr( $id ) . '" role="tooltip">' . esc_html( $tooltip ) . '</span>'
		. '</span>';
}

/**
 * FairFlock → Lieferzeiten.
 *
 * @since 0.9.295
 *
 * @return void
 */
function fairflock_delivery_times_menu() {
	$hook = add_submenu_page(
		'fairflock-setup',
		__( 'Lieferzeiten', 'fairflock' ),
		__( 'Lieferzeiten', 'fairflock' ),
		'edit_theme_options',
		'fairflock-lieferzeiten',
		'fairflock_delivery_times_page'
	);
	if ( is_string( $hook ) && '' !== $hook ) {
		add_action( 'load-' . $hook, 'fairflock_delivery_times_handle' );
	}
}
add_action( 'admin_menu', 'fairflock_delivery_times_menu' );

/**
 * Saves the class list before the screen renders.
 *
 * @since 0.9.295
 *
 * @return void
 */
function fairflock_delivery_times_handle() {
	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Du darfst Lieferzeiten nicht ändern.', 'fairflock' ) );
	}
	check_admin_referer( 'fairflock_delivery_times_save' );

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Rows are sanitized in fairflock_delivery_time_clean().
	$ids = isset( $_POST['ff_delivery_id'] ) ? wp_unslash( $_POST['ff_delivery_id'] ) : array();
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$labels = isset( $_POST['ff_delivery_label'] ) ? wp_unslash( $_POST['ff_delivery_label'] ) : array();
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$tips = isset( $_POST['ff_delivery_tooltip'] ) ? wp_unslash( $_POST['ff_delivery_tooltip'] ) : array();

	$ids    = is_array( $ids ) ? $ids : array();
	$labels = is_array( $labels ) ? $labels : array();
	$tips   = is_array( $tips ) ? $tips : array();

	$times = array();
	$count = max( count( $ids ), count( $labels ), count( $tips ) );
	for ( $i = 0; $i < $count; $i++ ) {
		$clean = fairflock_delivery_time_clean(
			array(
				'id'      => isset( $ids[ $i ] ) ? $ids[ $i ] : '',
				'label'   => isset( $labels[ $i ] ) ? $labels[ $i ] : '',
				'tooltip' => isset( $tips[ $i ] ) ? $tips[ $i ] : '',
			)
		);
		if ( null !== $clean ) {
			$times[] = $clean;
		}
	}

	$capped = count( $times ) > FAIRFLOCK_DELIVERY_TIMES_MAX;
	if ( $capped ) {
		$times = array_slice( $times, 0, FAIRFLOCK_DELIVERY_TIMES_MAX );
	}

	update_option( FAIRFLOCK_DELIVERY_TIMES_OPTION, $times, true );

	$args = array( 'page' => 'fairflock-lieferzeiten', 'updated' => '1' );
	if ( $capped ) {
		$args['capped'] = '1';
	}
	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	exit;
}

/**
 * Settings screen.
 *
 * @since 0.9.295
 *
 * @return void
 */
function fairflock_delivery_times_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Du darfst Lieferzeiten nicht ändern.', 'fairflock' ) );
	}

	$times = fairflock_delivery_times();
	if ( empty( $times ) ) {
		$times[] = array(
			'id'      => '',
			'label'   => '',
			'tooltip' => '',
		);
	}

	echo '<div class="wrap ff-delivery-screen">';
	echo '<h1>' . esc_html__( 'Lieferzeiten', 'fairflock' ) . '</h1>';
	echo '<p class="ff-delivery-screen__lead">' . esc_html__( 'Diese Klassen wählst du am Produkt unter Produktdaten → Versand als Lieferdauerklasse. Die Bezeichnung steht im Shop. Ein Hinweis erscheint dort als Info-Icon daneben.', 'fairflock' ) . '</p>';

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag after the save redirect.
	if ( isset( $_GET['updated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Lieferzeiten gespeichert.', 'fairflock' ) . '</p></div>';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['capped'] ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html(
			sprintf(
				/* translators: %d: maximum number of delivery classes. */
				__( 'Es werden höchstens %d Lieferzeiten gespeichert.', 'fairflock' ),
				FAIRFLOCK_DELIVERY_TIMES_MAX
			)
		) . '</p></div>';
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin.php?page=fairflock-lieferzeiten' ) ) . '">';
	wp_nonce_field( 'fairflock_delivery_times_save' );
	echo '<table class="widefat striped" id="ff-delivery-times">';
	echo '<thead><tr>';
	echo '<th>' . esc_html__( 'Bezeichnung', 'fairflock' ) . '</th>';
	echo '<th>' . esc_html__( 'Hinweis', 'fairflock' ) . '</th>';
	echo '<th></th>';
	echo '</tr></thead><tbody>';
	foreach ( $times as $time ) {
		fairflock_delivery_time_admin_row( $time );
	}
	echo '</tbody></table>';
	echo '<p><button type="button" class="button" id="ff-delivery-add">' . esc_html__( 'Lieferzeit hinzufügen', 'fairflock' ) . '</button></p>';
	submit_button( __( 'Speichern', 'fairflock' ) );
	echo '</form></div>';

	echo '<style>
		.ff-delivery-screen__lead{max-width:42rem}
		.ff-delivery-screen table.widefat{max-width:920px;margin-top:12px}
		.ff-delivery-screen td input.regular-text{width:100%}
		.ff-delivery-screen th:last-child,.ff-delivery-screen td:last-child{width:7rem;text-align:right}
	</style>';
	echo '<script>
		(function () {
			var table = document.getElementById("ff-delivery-times");
			var add = document.getElementById("ff-delivery-add");
			if (!table || !add) return;
			var body = table.querySelector("tbody");
			add.addEventListener("click", function () {
				var row = body.querySelector(".ff-delivery-row");
				if (!row) return;
				var next = row.cloneNode(true);
				next.querySelectorAll("input").forEach(function (input) { input.value = ""; });
				body.appendChild(next);
				var field = next.querySelector("[name=\'ff_delivery_label[]\']");
				if (field) field.focus();
			});
			table.addEventListener("click", function (event) {
				var button = event.target.closest("[data-ff-delivery-remove]");
				if (!button) return;
				var rows = body.querySelectorAll(".ff-delivery-row");
				if (rows.length < 2) {
					rows[0].querySelectorAll("input").forEach(function (input) { input.value = ""; });
					return;
				}
				var row = button.closest("tr");
				if (row) row.remove();
			});
		})();
	</script>';
}

/**
 * One editable row.
 *
 * @since 0.9.295
 *
 * @param array{id:string,label:string,tooltip:string} $time Class.
 * @return void
 */
function fairflock_delivery_time_admin_row( $time ) {
	echo '<tr class="ff-delivery-row">';
	echo '<td>';
	echo '<input type="hidden" name="ff_delivery_id[]" value="' . esc_attr( $time['id'] ) . '" />';
	printf(
		'<input type="text" class="regular-text" name="ff_delivery_label[]" value="%s" maxlength="80" placeholder="%s" />',
		esc_attr( $time['label'] ),
		esc_attr__( 'z. B. Lieferung in 3–5 Tagen', 'fairflock' )
	);
	echo '</td><td>';
	printf(
		'<input type="text" class="regular-text" name="ff_delivery_tooltip[]" value="%s" maxlength="240" placeholder="%s" aria-label="%s" />',
		esc_attr( $time['tooltip'] ),
		esc_attr__( 'Optional, wird zum Info-Icon', 'fairflock' ),
		esc_attr__( 'Hinweis', 'fairflock' )
	);
	echo '</td><td>';
	echo '<button type="button" class="button-link-delete" data-ff-delivery-remove>' . esc_html__( 'Entfernen', 'fairflock' ) . '</button>';
	echo '</td></tr>';
}

/**
 * Lieferdauerklasse on the product shipping tab.
 *
 * @since 0.9.295
 *
 * @return void
 */
function fairflock_delivery_time_product_field() {
	$options = array(
		'' => __( 'Keine Lieferdauer', 'fairflock' ),
	);
	foreach ( fairflock_delivery_times() as $time ) {
		$options[ $time['id'] ] = $time['label'];
	}

	woocommerce_wp_select(
		array(
			'id'          => FAIRFLOCK_META_DELIVERY_TIME,
			'label'       => __( 'Lieferdauerklasse', 'fairflock' ),
			'options'     => $options,
			'desc_tip'    => false,
			'description' => __( 'Die Klassen pflegst du unter FairFlock → Lieferzeiten.', 'fairflock' ),
		)
	);
}
add_action( 'woocommerce_product_options_shipping', 'fairflock_delivery_time_product_field' );

/**
 * Stores the chosen class.
 *
 * @since 0.9.295
 *
 * @param int $post_id Product id.
 * @return void
 */
function fairflock_delivery_time_save_product( $post_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the product save nonce.
	$raw = isset( $_POST[ FAIRFLOCK_META_DELIVERY_TIME ] ) ? wp_unslash( $_POST[ FAIRFLOCK_META_DELIVERY_TIME ] ) : '';
	$id  = sanitize_key( is_string( $raw ) ? $raw : '' );
	if ( '' === $id || ! fairflock_delivery_time_find( $id ) ) {
		delete_post_meta( $post_id, FAIRFLOCK_META_DELIVERY_TIME );
		return;
	}
	update_post_meta( $post_id, FAIRFLOCK_META_DELIVERY_TIME, $id );
}
add_action( 'woocommerce_process_product_meta', 'fairflock_delivery_time_save_product' );
