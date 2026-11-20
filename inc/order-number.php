<?php
/**
 * Order number appendage (e.g. 12345#AH001).
 *
 * WooCommerce-Bestellnummer + „#“ + Prefix/Sequenz.
 *
 * @package FairFlock
 * @since 0.9.444
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FAIRFLOCK_ORDER_NUMBER_PREFIX_OPTION = 'fairflock_order_number_prefix';
const FAIRFLOCK_ORDER_NUMBER_PAD_OPTION    = 'fairflock_order_number_pad';
const FAIRFLOCK_ORDER_NUMBER_NEXT_OPTION   = 'fairflock_order_number_next';
const FAIRFLOCK_META_ORDER_SEQ             = '_fairflock_order_seq';
const FAIRFLOCK_META_ORDER_NUMBER          = '_fairflock_order_number';

/**
 * Configured prefix (uppercase alphanumeric), or empty when disabled.
 *
 * @since 0.9.444
 *
 * @return string
 */
function fairflock_order_number_prefix() {
	$raw = (string) get_option( FAIRFLOCK_ORDER_NUMBER_PREFIX_OPTION, '' );
	$raw = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $raw ) );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $raw, 0, 12 );
	}
	return substr( $raw, 0, 12 );
}

/**
 * Zero-pad width for the numeric part (default 3 → 001).
 *
 * @since 0.9.444
 *
 * @return int
 */
function fairflock_order_number_pad() {
	$pad = (int) get_option( FAIRFLOCK_ORDER_NUMBER_PAD_OPTION, 3 );
	return max( 2, min( 8, $pad ) );
}

/**
 * Next sequential number that will be assigned.
 *
 * @since 0.9.444
 *
 * @return int
 */
function fairflock_order_number_next() {
	$next = (int) get_option( FAIRFLOCK_ORDER_NUMBER_NEXT_OPTION, 1 );
	return max( 1, $next );
}

/**
 * Formats the appendage only: Prefix + padded sequence (e.g. AH001).
 *
 * @since 0.9.444
 *
 * @param string $prefix Prefix.
 * @param int    $seq    Sequence.
 * @param int    $pad    Pad width.
 * @return string
 */
function fairflock_order_number_format( $prefix, $seq, $pad = 0 ) {
	$prefix = (string) $prefix;
	$seq    = max( 1, (int) $seq );
	$pad    = $pad > 0 ? (int) $pad : fairflock_order_number_pad();
	return $prefix . str_pad( (string) $seq, $pad, '0', STR_PAD_LEFT );
}

/**
 * Full display number: {Woo-Bestellnummer}#{Anhängsel}.
 *
 * @since 0.9.471
 *
 * @param string $base_order_number WooCommerce order number / id.
 * @param string $suffix            Appendage without leading #.
 * @return string
 */
function fairflock_order_number_with_suffix( $base_order_number, $suffix ) {
	$base   = preg_replace( '/#[A-Za-z0-9]+$/', '', (string) $base_order_number );
	$suffix = ltrim( (string) $suffix, '#' );
	if ( '' === $suffix ) {
		return $base;
	}
	return $base . '#' . $suffix;
}

/**
 * Claims the next sequence number (increments the stored counter).
 *
 * @since 0.9.444
 *
 * @return int
 */
function fairflock_order_number_claim_next() {
	$next = fairflock_order_number_next();
	update_option( FAIRFLOCK_ORDER_NUMBER_NEXT_OPTION, $next + 1, false );
	return $next;
}

/**
 * Assigns a stable sequence + display number on a new order.
 *
 * @since 0.9.444
 *
 * @param int|WC_Order $order Order id or object.
 * @return void
 */
function fairflock_order_number_assign( $order ) {
	$prefix = fairflock_order_number_prefix();
	if ( '' === $prefix ) {
		return;
	}

	if ( is_numeric( $order ) && function_exists( 'wc_get_order' ) ) {
		$order = wc_get_order( (int) $order );
	}
	if ( ! $order instanceof WC_Order ) {
		return;
	}

	$existing = $order->get_meta( FAIRFLOCK_META_ORDER_SEQ );
	if ( '' !== $existing && null !== $existing && false !== $existing ) {
		return;
	}

	$seq      = fairflock_order_number_claim_next();
	$display  = fairflock_order_number_format( $prefix, $seq );
	$order->update_meta_data( FAIRFLOCK_META_ORDER_SEQ, $seq );
	$order->update_meta_data( FAIRFLOCK_META_ORDER_NUMBER, $display );
	$order->save();
}
add_action( 'woocommerce_new_order', 'fairflock_order_number_assign', 20 );
add_action( 'woocommerce_checkout_order_processed', 'fairflock_order_number_assign', 20 );

/**
 * Displays order numbers as {Woo-Nr}#{Prefix}{Sequenz} in admin, emails, account, etc.
 *
 * @since 0.9.444
 * @since 0.9.471 Anhängsel mit führendem # an die Woo-Bestellnummer.
 *
 * @param string   $order_number Default order number.
 * @param WC_Order $order        Order.
 * @return string
 */
function fairflock_filter_order_number( $order_number, $order ) {
	$prefix = fairflock_order_number_prefix();
	if ( '' === $prefix || ! $order instanceof WC_Order ) {
		return $order_number;
	}

	$suffix = (string) $order->get_meta( FAIRFLOCK_META_ORDER_NUMBER );
	if ( '' === $suffix ) {
		$seq = $order->get_meta( FAIRFLOCK_META_ORDER_SEQ );
		if ( '' === $seq || null === $seq || false === $seq ) {
			/* Legacy orders without a claimed sequence: pad WooCommerce id. */
			$seq = $order->get_id();
		}
		$suffix = fairflock_order_number_format( $prefix, (int) $seq );
	}

	/* Meta may already contain a full "123#AH001" from older logic — normalize. */
	if ( false !== strpos( $suffix, '#' ) ) {
		$parts  = explode( '#', $suffix );
		$suffix = (string) end( $parts );
	}

	return fairflock_order_number_with_suffix( $order_number, $suffix );
}
add_filter( 'woocommerce_order_number', 'fairflock_filter_order_number', 10, 2 );

/**
 * Lets shop order search find the custom number (classic CPT storage).
 *
 * @since 0.9.444
 *
 * @param array $fields Meta keys.
 * @return array
 */
function fairflock_order_number_search_fields( $fields ) {
	$fields[] = FAIRFLOCK_META_ORDER_NUMBER;
	$fields[] = FAIRFLOCK_META_ORDER_SEQ;
	return $fields;
}
add_filter( 'woocommerce_shop_order_search_fields', 'fairflock_order_number_search_fields' );

/**
 * HPOS: include custom order number in admin search.
 *
 * @since 0.9.444
 *
 * @param array $meta_keys Meta keys searched.
 * @return array
 */
function fairflock_order_number_hpos_search_meta( $meta_keys ) {
	$meta_keys[] = FAIRFLOCK_META_ORDER_NUMBER;
	return $meta_keys;
}
add_filter( 'woocommerce_order_table_search_query_meta_keys', 'fairflock_order_number_hpos_search_meta' );

/**
 * Registers FairFlock → Bestellnummern.
 *
 * @since 0.9.444
 *
 * @return void
 */
function fairflock_order_number_register_admin_page() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Bestellnummern', 'fairflock' ),
		__( 'Bestellnummern', 'fairflock' ),
		'manage_woocommerce',
		'fairflock-order-numbers',
		'fairflock_order_number_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_order_number_register_admin_page', 28 );

/**
 * Saves Bestellnummern settings.
 *
 * @since 0.9.444
 *
 * @return void
 */
function fairflock_order_number_handle_admin_save() {
	if ( ! isset( $_POST['fairflock_order_number_save'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'fairflock_order_number_settings' );

	$prefix = isset( $_POST['fairflock_order_number_prefix'] )
		? strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_POST['fairflock_order_number_prefix'] ) ) )
		: '';
	if ( function_exists( 'mb_substr' ) ) {
		$prefix = mb_substr( $prefix, 0, 12 );
	} else {
		$prefix = substr( $prefix, 0, 12 );
	}

	$pad = isset( $_POST['fairflock_order_number_pad'] )
		? (int) wp_unslash( $_POST['fairflock_order_number_pad'] )
		: 3;
	$pad = max( 2, min( 8, $pad ) );

	$next = isset( $_POST['fairflock_order_number_next'] )
		? (int) wp_unslash( $_POST['fairflock_order_number_next'] )
		: 1;
	$next = max( 1, $next );

	update_option( FAIRFLOCK_ORDER_NUMBER_PREFIX_OPTION, $prefix, false );
	update_option( FAIRFLOCK_ORDER_NUMBER_PAD_OPTION, $pad, false );
	update_option( FAIRFLOCK_ORDER_NUMBER_NEXT_OPTION, $next, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'fairflock-order-numbers',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'fairflock_order_number_handle_admin_save' );

/**
 * Renders FairFlock → Bestellnummern.
 *
 * @since 0.9.444
 *
 * @return void
 */
function fairflock_order_number_render_admin_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$prefix  = fairflock_order_number_prefix();
	$pad     = fairflock_order_number_pad();
	$next    = fairflock_order_number_next();
	$suffix  = '' !== $prefix ? fairflock_order_number_format( $prefix, $next, $pad ) : '';
	$preview = '' !== $suffix
		? fairflock_order_number_with_suffix( '{Bestellnummer}', $suffix )
		: __( '— (kein Prefix = WooCommerce-Standard)', 'fairflock' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Bestellnummern', 'fairflock' ); ?></h1>
		<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Einstellungen gespeichert.', 'fairflock' ); ?></p></div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'Das Anhängsel wird mit „#“ an die WooCommerce-Bestellnummer gehängt, z. B. 12345#AH001. Ohne Prefix bleibt die normale WooCommerce-Bestellnummer.', 'fairflock' ); ?>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'fairflock_order_number_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="fairflock_order_number_prefix"><?php esc_html_e( 'Prefix', 'fairflock' ); ?></label></th>
					<td>
						<input
							type="text"
							class="regular-text"
							id="fairflock_order_number_prefix"
							name="fairflock_order_number_prefix"
							value="<?php echo esc_attr( $prefix ); ?>"
							maxlength="12"
							pattern="[A-Za-z0-9]*"
							placeholder="AH"
							autocomplete="off"
						/>
						<p class="description">
							<?php esc_html_e( 'Nur Buchstaben und Zahlen, z. B. AH. Leer lassen deaktiviert die Funktion.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fairflock_order_number_pad"><?php esc_html_e( 'Stellen (Zahl)', 'fairflock' ); ?></label></th>
					<td>
						<input
							type="number"
							id="fairflock_order_number_pad"
							name="fairflock_order_number_pad"
							value="<?php echo esc_attr( (string) $pad ); ?>"
							min="2"
							max="8"
							step="1"
							class="small-text"
						/>
						<p class="description">
							<?php esc_html_e( 'Mindestbreite der Zahl mit führenden Nullen. 3 → 001, 002, …', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fairflock_order_number_next"><?php esc_html_e( 'Nächste Nummer', 'fairflock' ); ?></label></th>
					<td>
						<input
							type="number"
							id="fairflock_order_number_next"
							name="fairflock_order_number_next"
							value="<?php echo esc_attr( (string) $next ); ?>"
							min="1"
							step="1"
							class="small-text"
						/>
						<p class="description">
							<?php esc_html_e( 'Laufende Zahl für die nächste neue Bestellung. Bereits vergebene Nummern ändern sich nicht.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Vorschau', 'fairflock' ); ?></th>
					<td>
						<code style="font-size:1.1em"><?php echo esc_html( $preview ); ?></code>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Speichern', 'fairflock' ), 'primary', 'fairflock_order_number_save' ); ?>
		</form>
	</div>
	<?php
}
