<?php
/**
 * Header / navigation style switch (floating pill vs edge bar).
 *
 * @package FairFlock
 * @since 0.9.173
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed header style keys.
 *
 * @since 0.9.173
 *
 * @return array<string, string> style => label
 */
function fairflock_header_style_choices() {
	return array(
		'floating' => __( 'Schwebend (abgerundet)', 'fairflock' ),
		'edge'     => __( 'Randlos (bis zum Rand, ohne Rundungen)', 'fairflock' ),
	);
}

/**
 * Current header style.
 *
 * @since 0.9.173
 *
 * @return string floating|edge
 */
function fairflock_get_header_style() {
	$style = (string) get_option( 'fairflock_header_style', 'floating' );
	$ok    = array_keys( fairflock_header_style_choices() );

	return in_array( $style, $ok, true ) ? $style : 'floating';
}

/**
 * Adds body class for CSS clearance / layout.
 *
 * @since 0.9.173
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fairflock_header_style_body_class( $classes ) {
	$classes[] = 'ff-header-style-' . fairflock_get_header_style();

	return $classes;
}
add_filter( 'body_class', 'fairflock_header_style_body_class' );

/**
 * Adds style modifier on the root .ff-header group (frontend + editor render).
 *
 * @since 0.9.173
 *
 * @param string               $block_content Rendered HTML.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function fairflock_header_style_render_block( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'core/group' !== $block['blockName'] ) {
		return $block_content;
	}

	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( ! str_contains( $class, 'ff-header' ) || str_contains( $class, 'ff-header__' ) ) {
		return $block_content;
	}

	// Only the root shell (class "ff-header"), not nested ff-header__*.
	if ( ! preg_match( '/(^|\s)ff-header(\s|$)/', $class ) ) {
		return $block_content;
	}

	$style = fairflock_get_header_style();
	$mod   = 'ff-header--' . $style;

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( $processor->next_tag( array( 'class_name' => 'ff-header' ) ) ) {
		$processor->add_class( $mod );
		return $processor->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block', 'fairflock_header_style_render_block', 10, 2 );

/**
 * Registers admin submenu.
 *
 * @since 0.9.173
 *
 * @return void
 */
function fairflock_header_style_register_admin_page() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Navigation', 'fairflock' ),
		__( 'Navigation', 'fairflock' ),
		'manage_options',
		'fairflock-header-style',
		'fairflock_header_style_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_header_style_register_admin_page', 25 );

/**
 * Saves header style setting.
 *
 * @since 0.9.173
 *
 * @return void
 */
function fairflock_header_style_handle_admin_save() {
	if ( ! isset( $_POST['fairflock_header_style_save'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'fairflock_header_style_settings' );

	$raw   = isset( $_POST['fairflock_header_style'] ) ? sanitize_key( wp_unslash( (string) $_POST['fairflock_header_style'] ) ) : 'floating';
	$ok    = array_keys( fairflock_header_style_choices() );
	$style = in_array( $raw, $ok, true ) ? $raw : 'floating';

	update_option( 'fairflock_header_style', $style, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'fairflock-header-style',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'fairflock_header_style_handle_admin_save' );

/**
 * Renders Navigation style admin screen.
 *
 * @since 0.9.173
 *
 * @return void
 */
function fairflock_header_style_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$current = fairflock_get_header_style();
	$choices = fairflock_header_style_choices();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Navigation', 'fairflock' ); ?></h1>
		<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Einstellungen gespeichert.', 'fairflock' ); ?></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Gleiches Layout (Logo, Menü, Suche, Konto, Warenkorb) — zwei Optiken zur Auswahl.', 'fairflock' ); ?></p>

		<form method="post" action="">
			<?php wp_nonce_field( 'fairflock_header_style_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Nav-Design', 'fairflock' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $choices as $key => $label ) : ?>
								<label style="display:block;margin:0 0 10px;">
									<input type="radio" name="fairflock_header_style" value="<?php echo esc_attr( $key ); ?>" <?php checked( $current, $key ); ?> />
									<strong><?php echo esc_html( $label ); ?></strong>
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Schwebend: abgerundete Leiste mit Abstand zum Rand (Standard). Randlos: volle Breite, oben bündig, ohne Rundungen.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Speichern', 'fairflock' ), 'primary', 'fairflock_header_style_save' ); ?>
		</form>
	</div>
	<?php
}
