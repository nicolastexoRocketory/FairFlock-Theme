<?php
/**
 * Site-wide and page password protection — modern logo card.
 *
 * @package FairFlock
 * @since 0.9.154
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie name for the site-wide gate.
 *
 * @since 0.9.154
 *
 * @return string
 */
function fairflock_site_password_cookie_name() {
	return 'wp-fairflock_sitepass_' . COOKIEHASH;
}

/**
 * Whether the site-wide password gate is enabled and configured.
 *
 * @since 0.9.154
 *
 * @return bool
 */
function fairflock_site_password_is_enabled() {
	if ( '1' !== (string) get_option( 'fairflock_site_password_enabled', '' ) ) {
		return false;
	}
	$hash = (string) get_option( 'fairflock_site_password_hash', '' );
	return '' !== $hash;
}

/**
 * Expected auth token for the current password hash.
 *
 * @since 0.9.154
 *
 * @return string
 */
function fairflock_site_password_token() {
	$hash = (string) get_option( 'fairflock_site_password_hash', '' );
	if ( '' === $hash ) {
		return '';
	}
	return hash_hmac( 'sha256', 'fairflock-site-ok', $hash . wp_salt( 'auth' ) );
}

/**
 * Whether the visitor may pass the site gate.
 *
 * @since 0.9.154
 *
 * @return bool
 */
function fairflock_site_password_visitor_ok() {
	if ( ! fairflock_site_password_is_enabled() ) {
		return true;
	}

	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return true;
	}

	$expected = fairflock_site_password_token();
	if ( '' === $expected ) {
		return true;
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$cookie = isset( $_COOKIE[ fairflock_site_password_cookie_name() ] ) ? (string) wp_unslash( $_COOKIE[ fairflock_site_password_cookie_name() ] ) : '';
	return '' !== $cookie && hash_equals( $expected, $cookie );
}

/**
 * Requests that must never be blocked by the site gate.
 *
 * @since 0.9.154
 *
 * @return bool
 */
function fairflock_site_password_should_bypass_request() {
	if ( is_admin() ) {
		return true;
	}

	if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return true;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	global $pagenow;
	if ( isset( $pagenow ) && in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ), true ) ) {
		return true;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( $request_uri && preg_match( '#/(wp-login\.php|wp-admin|wp-json|xmlrpc\.php)#', $request_uri ) ) {
		return true;
	}

	return false;
}

/**
 * Handles site-password form POST and sets the cookie.
 *
 * @since 0.9.154
 *
 * @return void
 */
function fairflock_site_password_handle_submit() {
	if ( ! fairflock_site_password_is_enabled() ) {
		return;
	}

	if ( empty( $_POST['fairflock_site_password_submit'] ) ) {
		return;
	}

	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'fairflock_site_password' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- compared via wp_check_password.
	$plain = isset( $_POST['fairflock_site_password'] ) ? (string) wp_unslash( $_POST['fairflock_site_password'] ) : '';
	$hash  = (string) get_option( 'fairflock_site_password_hash', '' );

	if ( '' === $plain || '' === $hash || ! wp_check_password( $plain, $hash ) ) {
		$GLOBALS['fairflock_site_password_error'] = true;
		return;
	}

	$token   = fairflock_site_password_token();
	$expire  = time() + 14 * DAY_IN_SECONDS;
	$secure  = is_ssl();
	$httponly = true;

	setcookie( fairflock_site_password_cookie_name(), $token, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, $httponly );
	if ( COOKIEPATH !== SITECOOKIEPATH ) {
		setcookie( fairflock_site_password_cookie_name(), $token, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure, $httponly );
	}

	$redirect = wp_validate_redirect(
		isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '',
		home_url( '/' )
	);
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'init', 'fairflock_site_password_handle_submit', 1 );

/**
 * Blocks the front end behind the site password card when enabled.
 *
 * @since 0.9.154
 *
 * @return void
 */
function fairflock_site_password_gate() {
	if ( fairflock_site_password_should_bypass_request() ) {
		return;
	}

	if ( fairflock_site_password_visitor_ok() ) {
		return;
	}

	status_header( 401 );
	nocache_headers();

	$error = ! empty( $GLOBALS['fairflock_site_password_error'] );
	$redirect = home_url( add_query_arg( array() ) );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- full document; helpers escape inside.
	echo fairflock_get_password_gate_markup(
		array(
			'title'       => __( 'Geschützter Bereich', 'fairflock' ),
			'intro'       => __( 'Dieser Shop ist nur für autorisierte Personen zugänglich. Bitte Passwort eingeben.', 'fairflock' ),
			'action'      => home_url( '/' ),
			'field_name'  => 'fairflock_site_password',
			'submit_name' => 'fairflock_site_password_submit',
			'nonce_action'=> 'fairflock_site_password',
			'redirect_to' => $redirect,
			'error'       => $error,
			'sitewide'    => true,
		)
	);
	exit;
}
add_action( 'template_redirect', 'fairflock_site_password_gate', 1 );

/**
 * Site logo URL for password cards (custom logo → theme logo fallback).
 *
 * @since 0.9.154
 *
 * @return string
 */
function fairflock_password_logo_url() {
	$custom_id = (int) get_theme_mod( 'custom_logo' );
	if ( $custom_id > 0 ) {
		$url = wp_get_attachment_image_url( $custom_id, 'medium' );
		if ( is_string( $url ) && $url !== '' ) {
			return $url;
		}
	}

	$theme_logo = FAIRFLOCK_URI . '/assets/images/logo.png';
	return $theme_logo;
}

/**
 * Renders the shared password card document / form shell.
 *
 * @since 0.9.154
 *
 * @param array<string, mixed> $args Form args.
 * @return string
 */
function fairflock_get_password_gate_markup( array $args ) {
	$defaults = array(
		'title'        => __( 'Passwort erforderlich', 'fairflock' ),
		'intro'        => __( 'Dieser Inhalt ist passwortgeschützt.', 'fairflock' ),
		'action'       => home_url( '/' ),
		'field_name'   => 'post_password',
		'submit_name'  => 'Submit',
		'nonce_action' => '',
		'redirect_to'  => '',
		'error'        => false,
		'sitewide'     => false,
		'form_extra'   => '',
	);
	$args     = wp_parse_args( $args, $defaults );

	$logo_url  = fairflock_password_logo_url();
	$site_name = get_bloginfo( 'name' );
	$css_url   = FAIRFLOCK_URI . '/assets/css/components.css?ver=' . rawurlencode( (string) FAIRFLOCK_VERSION );
	$style_url = get_stylesheet_uri() . '?ver=' . rawurlencode( (string) FAIRFLOCK_VERSION );

	ob_start();

	if ( ! empty( $args['sitewide'] ) ) {
		?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex,nofollow" />
	<title><?php echo esc_html( $args['title'] . ' — ' . $site_name ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( $style_url ); ?>" />
	<link rel="stylesheet" href="<?php echo esc_url( $css_url ); ?>" />
</head>
<body class="ff-password-body">
<?php
	}

	?>
	<div class="ff-password">
		<div class="ff-password__card" role="dialog" aria-labelledby="ff-password-title" aria-modal="true">
			<div class="ff-password__brand">
				<img class="ff-password__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" width="160" height="64" decoding="async" />
				<?php if ( $site_name ) : ?>
					<p class="ff-password__site"><?php echo esc_html( $site_name ); ?></p>
				<?php endif; ?>
			</div>

			<h1 id="ff-password-title" class="ff-password__title"><?php echo esc_html( (string) $args['title'] ); ?></h1>
			<p class="ff-password__intro"><?php echo esc_html( (string) $args['intro'] ); ?></p>

			<?php if ( ! empty( $args['error'] ) ) : ?>
				<p class="ff-password__error" role="alert"><?php esc_html_e( 'Das Passwort ist nicht korrekt. Bitte erneut versuchen.', 'fairflock' ); ?></p>
			<?php endif; ?>

			<form class="ff-password__form" action="<?php echo esc_url( (string) $args['action'] ); ?>" method="post">
				<label class="ff-password__label" for="ff-password-input"><?php esc_html_e( 'Passwort', 'fairflock' ); ?></label>
				<input
					class="ff-password__input"
					type="password"
					name="<?php echo esc_attr( (string) $args['field_name'] ); ?>"
					id="ff-password-input"
					autocomplete="current-password"
					required
					autofocus
				/>
				<?php if ( ! empty( $args['nonce_action'] ) ) : ?>
					<?php wp_nonce_field( (string) $args['nonce_action'] ); ?>
				<?php endif; ?>
				<?php if ( ! empty( $args['redirect_to'] ) ) : ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( (string) $args['redirect_to'] ); ?>" />
				<?php endif; ?>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- optional trusted extras from callers.
				echo (string) $args['form_extra'];
				?>
				<button class="ff-password__submit" type="submit" name="<?php echo esc_attr( (string) $args['submit_name'] ); ?>" value="1">
					<?php esc_html_e( 'Zugang freischalten', 'fairflock' ); ?>
				</button>
			</form>
		</div>
	</div>
	<?php

	if ( ! empty( $args['sitewide'] ) ) {
		?>
</body>
</html>
		<?php
	}

	return (string) ob_get_clean();
}

/**
 * Replaces the default WordPress post/page password form.
 *
 * @since 0.9.154
 *
 * @param string $output Default form HTML.
 * @param string $post   Unused (WP passes post object in newer versions as 2nd sometimes).
 * @return string
 */
function fairflock_filter_the_password_form( $output ) {
	$post = get_post();
	$action = esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) );

	$form = fairflock_get_password_gate_markup(
		array(
			'title'       => __( 'Passwort erforderlich', 'fairflock' ),
			'intro'       => __( 'Dieser Inhalt ist nur mit Passwort sichtbar.', 'fairflock' ),
			'action'      => $action,
			'field_name'  => 'post_password',
			'submit_name' => 'Submit',
			'sitewide'    => false,
		)
	);

	// Keep a single card for page passwords (no full HTML document).
	return $form;
}
add_filter( 'the_password_form', 'fairflock_filter_the_password_form', 10, 1 );

/**
 * Registers the Zugang settings page under FairFlock.
 *
 * @since 0.9.154
 *
 * @return void
 */
function fairflock_password_register_admin_page() {
	add_submenu_page(
		'fairflock-demo-content',
		__( 'Passwortschutz', 'fairflock' ),
		__( 'Passwortschutz', 'fairflock' ),
		'manage_options',
		'fairflock-password',
		'fairflock_password_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_password_register_admin_page', 20 );

/**
 * Saves Zugang settings.
 *
 * @since 0.9.154
 *
 * @return void
 */
function fairflock_password_handle_admin_save() {
	if ( ! isset( $_POST['fairflock_password_save'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'fairflock_password_settings' );

	$enabled = ! empty( $_POST['fairflock_site_password_enabled'] ) ? '1' : '';

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$new = isset( $_POST['fairflock_site_password_new'] ) ? (string) wp_unslash( $_POST['fairflock_site_password_new'] ) : '';
	if ( '' !== $new ) {
		update_option( 'fairflock_site_password_hash', wp_hash_password( $new ), false );
	}

	if ( ! empty( $_POST['fairflock_site_password_clear'] ) ) {
		delete_option( 'fairflock_site_password_hash' );
		$enabled = '';
	}

	$hash = (string) get_option( 'fairflock_site_password_hash', '' );
	if ( '1' === $enabled && '' === $hash ) {
		$enabled = '';
	}

	update_option( 'fairflock_site_password_enabled', $enabled, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'fairflock-password',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'fairflock_password_handle_admin_save' );

/**
 * Renders the Passwortschutz admin screen.
 *
 * @since 0.9.154
 *
 * @return void
 */
function fairflock_password_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$enabled = '1' === (string) get_option( 'fairflock_site_password_enabled', '' );
	$has_pw  = '' !== (string) get_option( 'fairflock_site_password_hash', '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Passwortschutz', 'fairflock' ); ?></h1>
		<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Einstellungen gespeichert.', 'fairflock' ); ?></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Optionaler Zugangsschutz für den gesamten Shop — z. B. nur für Mitarbeitende. Admins (eingeloggt) sind ausgenommen.', 'fairflock' ); ?></p>

		<form method="post" action="">
			<?php wp_nonce_field( 'fairflock_password_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Shop schützen', 'fairflock' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fairflock_site_password_enabled" value="1" <?php checked( $enabled ); ?> />
							<?php esc_html_e( 'Frontend nur mit Passwort zugänglich', 'fairflock' ); ?>
						</label>
						<?php if ( ! $has_pw ) : ?>
							<p class="description"><?php esc_html_e( 'Zuerst ein Passwort setzen (Feld darunter), dann speichern.', 'fairflock' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fairflock_site_password_new"><?php esc_html_e( 'Neues Passwort', 'fairflock' ); ?></label></th>
					<td>
						<input type="password" class="regular-text" id="fairflock_site_password_new" name="fairflock_site_password_new" autocomplete="new-password" />
						<p class="description">
							<?php
							echo $has_pw
								? esc_html__( 'Leer lassen, um das aktuelle Passwort zu behalten.', 'fairflock' )
								: esc_html__( 'Passwort festlegen (wird gehasht gespeichert).', 'fairflock' );
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Zurücksetzen', 'fairflock' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fairflock_site_password_clear" value="1" />
							<?php esc_html_e( 'Passwort löschen und Schutz deaktivieren', 'fairflock' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Speichern', 'fairflock' ), 'primary', 'fairflock_password_save' ); ?>
		</form>

		<hr />
		<p class="description">
			<?php esc_html_e( 'Einzelne Seiten können weiterhin über „Sichtbarkeit → Passwortgeschützt“ in WordPress geschützt werden — dieselbe Card-Optik gilt dort automatisch.', 'fairflock' ); ?>
		</p>
	</div>
	<?php
}
