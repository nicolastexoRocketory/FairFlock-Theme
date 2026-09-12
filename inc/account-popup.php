<?php
/**
 * My Account overview popup (header icon).
 *
 * @package FairFlock
 * @since 0.9.24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account page URL (WooCommerce or fallback).
 *
 * @since 0.9.24
 *
 * @return string
 */
function fairflock_get_account_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'myaccount' );
		if ( $url ) {
			return $url;
		}
	}

	return home_url( '/my-account/' );
}

/**
 * Prints the account popup shell in the footer.
 *
 * @since 0.9.24
 *
 * @return void
 */
function fairflock_print_account_popup() {
	if ( is_admin() ) {
		return;
	}

	$account_url = fairflock_get_account_url();
	$logged_in   = is_user_logged_in();
	$user        = $logged_in ? wp_get_current_user() : null;
	$display     = $user ? ( $user->display_name ? $user->display_name : $user->user_login ) : '';
	?>
	<div class="ff-account-layer" id="ffAccountLayer" aria-hidden="true">
		<div class="ff-account-backdrop" id="ffAccountBackdrop"></div>
		<div
			class="ff-account-modal"
			id="ffAccountModal"
			role="dialog"
			aria-modal="true"
			aria-labelledby="ffAccountTitle"
			tabindex="-1"
		>
			<div class="ff-account-modal__head">
				<h2 id="ffAccountTitle"><?php echo esc_html__( 'Mein Konto', 'fairflock' ); ?></h2>
				<button type="button" class="ff-account-modal__close" id="ffAccountClose" aria-label="<?php echo esc_attr__( 'Konto schließen', 'fairflock' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
				</button>
			</div>

			<div class="ff-account-modal__body">
				<?php if ( $logged_in ) : ?>
					<p class="ff-account-modal__hello">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: user display name */
								__( 'Hallo, %s', 'fairflock' ),
								$display
							)
						);
						?>
					</p>

					<?php if ( function_exists( 'wc_get_account_menu_items' ) ) : ?>
						<nav class="ff-account-modal__nav" aria-label="<?php echo esc_attr__( 'Konto-Menü', 'fairflock' ); ?>">
							<ul>
								<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
									<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
										<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>">
											<?php echo esc_html( $label ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</nav>
					<?php else : ?>
						<p class="ff-account-modal__note">
							<a href="<?php echo esc_url( $account_url ); ?>"><?php echo esc_html__( 'Zur Konto-Seite', 'fairflock' ); ?></a>
						</p>
					<?php endif; ?>

					<a class="ff-account-modal__full" href="<?php echo esc_url( $account_url ); ?>">
						<?php echo esc_html__( 'Konto-Seite öffnen →', 'fairflock' ); ?>
					</a>
				<?php else : ?>
					<p class="ff-account-modal__note">
						<?php echo esc_html__( 'Melde dich an, um Bestellungen und Daten zu verwalten.', 'fairflock' ); ?>
					</p>

					<div class="ff-account-modal__login">
						<?php
						if ( function_exists( 'woocommerce_login_form' ) ) {
							woocommerce_login_form(
								array(
									'redirect' => $account_url,
								)
							);
						} else {
							wp_login_form(
								array(
									'redirect'       => $account_url,
									'label_username' => __( 'Benutzername oder E-Mail', 'fairflock' ),
									'label_password' => __( 'Passwort', 'fairflock' ),
									'label_remember' => __( 'Angemeldet bleiben', 'fairflock' ),
									'label_log_in'   => __( 'Anmelden', 'fairflock' ),
								)
							);
						}
						?>
					</div>

					<p class="ff-account-modal__links">
						<a href="<?php echo esc_url( wp_lostpassword_url( $account_url ) ); ?>">
							<?php echo esc_html__( 'Passwort vergessen?', 'fairflock' ); ?>
						</a>
						<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
							<span aria-hidden="true">·</span>
							<a href="<?php echo esc_url( $account_url ); ?>">
								<?php echo esc_html__( 'Konto erstellen', 'fairflock' ); ?>
							</a>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'fairflock_print_account_popup', 5 );
