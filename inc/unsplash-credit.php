<?php
/**
 * Unsplash credit toggle — footer + Impressum Bildnachweis.
 *
 * @package FairFlock
 * @since 0.9.180
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Unsplash credit is enabled.
 *
 * @since 0.9.180
 *
 * @return bool
 */
function fairflock_unsplash_credit_enabled() {
	return '1' === (string) get_option( 'fairflock_unsplash_credit_enabled', '' );
}

/**
 * Canonical Unsplash referral URL (utm as recommended by Unsplash).
 *
 * @since 0.9.180
 *
 * @return string
 */
function fairflock_unsplash_credit_url() {
	return 'https://unsplash.com/?utm_source=fairflock&utm_medium=referral';
}

/**
 * Short credit line (footer).
 *
 * @since 0.9.180
 *
 * @return string HTML
 */
function fairflock_unsplash_credit_footer_html() {
	$url = esc_url( fairflock_unsplash_credit_url() );

	return sprintf(
		'<p class="ff-footer__unsplash">%s <a href="%s" target="_blank" rel="noopener noreferrer">Unsplash</a>.</p>',
		esc_html__( 'Bildnachweis: Fotos von', 'fairflock' ),
		$url
	);
}

/**
 * Longer credit block for Impressum (Bildnachweis).
 *
 * @since 0.9.180
 *
 * @return string HTML
 */
function fairflock_unsplash_credit_impressum_html() {
	$url = esc_url( fairflock_unsplash_credit_url() );

	$heading = esc_html__( 'Bildnachweis', 'fairflock' );
	$body    = sprintf(
		/* translators: %s: Unsplash link HTML */
		esc_html__( 'Auf dieser Website werden Fotografien von %s verwendet. Unsplash stellt Bilder unter der Unsplash License bereit; die Nennung dient der transparenten Quellenangabe.', 'fairflock' ),
		'<a href="' . $url . '" target="_blank" rel="noopener noreferrer">Unsplash</a>'
	);

	return '<div class="ff-unsplash-credit-impressum">'
		. '<h2>' . $heading . '</h2>'
		. '<p>' . $body . '</p>'
		. '</div>';
}

/**
 * Injects credit into the footer bottom bar.
 *
 * @since 0.9.180
 *
 * @param string               $block_content Rendered HTML.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function fairflock_unsplash_credit_render_footer( $block_content, $block ) {
	if ( ! fairflock_unsplash_credit_enabled() ) {
		return $block_content;
	}

	if ( empty( $block['blockName'] ) || 'core/group' !== $block['blockName'] ) {
		return $block_content;
	}

	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( ! str_contains( $class, 'ff-footer__bottom' ) ) {
		return $block_content;
	}

	if ( str_contains( $block_content, 'ff-footer__unsplash' ) ) {
		$GLOBALS['fairflock_unsplash_credit_rendered'] = true;
		return $block_content;
	}

	$credit = fairflock_unsplash_credit_footer_html();
	$pos    = strrpos( $block_content, '</div>' );
	if ( false === $pos ) {
		return $block_content . $credit;
	}

	$GLOBALS['fairflock_unsplash_credit_rendered'] = true;

	return substr( $block_content, 0, $pos ) . $credit . substr( $block_content, $pos );
}
add_filter( 'render_block_core/group', 'fairflock_unsplash_credit_render_footer', 20, 2 );

/**
 * Fallback if the footer template part has no .ff-footer__bottom.
 *
 * @since 0.9.180
 *
 * @return void
 */
function fairflock_unsplash_credit_footer_fallback() {
	if ( ! fairflock_unsplash_credit_enabled() ) {
		return;
	}

	if ( ! empty( $GLOBALS['fairflock_unsplash_credit_rendered'] ) ) {
		return;
	}

	if ( is_admin() ) {
		return;
	}

	echo '<div class="ff-footer__unsplash--fallback">' . fairflock_unsplash_credit_footer_html() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_*.
}
add_action( 'wp_footer', 'fairflock_unsplash_credit_footer_fallback', 25 );

/**
 * Appends Bildnachweis on the Impressum page.
 *
 * @since 0.9.180
 *
 * @param string $content Post content.
 * @return string
 */
function fairflock_unsplash_credit_filter_impressum_content( $content ) {
	if ( ! fairflock_unsplash_credit_enabled() || ! is_page( 'impressum' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( str_contains( $content, 'ff-unsplash-credit-impressum' ) ) {
		return $content;
	}

	return $content . "\n\n" . fairflock_unsplash_credit_impressum_html();
}
add_filter( 'the_content', 'fairflock_unsplash_credit_filter_impressum_content', 25 );

/**
 * Registers admin submenu.
 *
 * @since 0.9.180
 *
 * @return void
 */
function fairflock_unsplash_credit_register_admin_page() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Unsplash Credit', 'fairflock' ),
		__( 'Unsplash Credit', 'fairflock' ),
		'manage_options',
		'fairflock-unsplash-credit',
		'fairflock_unsplash_credit_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_unsplash_credit_register_admin_page', 30 );

/**
 * Saves Unsplash credit setting.
 *
 * @since 0.9.180
 *
 * @return void
 */
function fairflock_unsplash_credit_handle_admin_save() {
	if ( ! isset( $_POST['fairflock_unsplash_credit_save'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'fairflock_unsplash_credit_settings' );

	$enabled = ! empty( $_POST['fairflock_unsplash_credit_enabled'] ) ? '1' : '';
	update_option( 'fairflock_unsplash_credit_enabled', $enabled, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'fairflock-unsplash-credit',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'fairflock_unsplash_credit_handle_admin_save' );

/**
 * Renders Unsplash Credit admin screen.
 *
 * @since 0.9.180
 *
 * @return void
 */
function fairflock_unsplash_credit_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$enabled = fairflock_unsplash_credit_enabled();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Unsplash Credit', 'fairflock' ); ?></h1>
		<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Einstellungen gespeichert.', 'fairflock' ); ?></p></div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'Wenn der Shop Unsplash-Fotos nutzt, kannst du hier den Bildnachweis aktivieren. Er erscheint im Footer und zusätzlich im Impressum unter „Bildnachweis“.', 'fairflock' ); ?>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'fairflock_unsplash_credit_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Bildnachweis', 'fairflock' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fairflock_unsplash_credit_enabled" value="1" <?php checked( $enabled ); ?> />
							<?php esc_html_e( 'Unsplash-Credit anzeigen (Footer + Impressum)', 'fairflock' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Empfohlen, sobald Unsplash-Bilder auf der Website eingesetzt werden. Link führt zu Unsplash mit FairFlock-Referrer.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Speichern', 'fairflock' ), 'primary', 'fairflock_unsplash_credit_save' ); ?>
		</form>
	</div>
	<?php
}
