<?php
/**
 * Countdown pop-up (FairFlock → Pop-up).
 *
 * @package FairFlock
 * @since 0.9.507
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default (placeholder) pop-up settings.
 *
 * @since 0.9.507
 *
 * @return array<string, string>
 */
function fairflock_popup_defaults() {
	return array(
		'enabled'   => '',
		'title'     => __( 'Vorfreude: Viel Spaß beim Spiel und unserer Mannschaft viel Erfolg!', 'fairflock' ),
		'text'      => __( 'Nur noch so lange, bis der Ball wieder fliegt – dann heißt es Anwurf! Trikot an, Stimme ölen und gemeinsam die Daumen drücken.', 'fairflock' ),
		'end'       => '',
		'done_text' => __( 'Anwurf! Jetzt geht’s los – wir drücken die Daumen.', 'fairflock' ),
		'cta_label' => __( 'Zum Fan-Shop', 'fairflock' ),
		'cta_url'   => '',
		'delay'     => '0',
		'frequency' => 'day',
		'after_end' => 'hide',
	);
}

/**
 * Display frequency choices.
 *
 * @since 0.9.507
 *
 * @return array<string, string>
 */
function fairflock_popup_frequency_choices() {
	return array(
		'session' => __( 'Einmal pro Browser-Sitzung', 'fairflock' ),
		'day'     => __( 'Höchstens einmal pro Tag', 'fairflock' ),
		'week'    => __( 'Höchstens einmal pro Woche', 'fairflock' ),
		'always'  => __( 'Bei jedem Seitenaufruf (nur zum Testen)', 'fairflock' ),
	);
}

/**
 * Behaviour once the countdown has ended.
 *
 * @since 0.9.507
 *
 * @return array<string, string>
 */
function fairflock_popup_after_end_choices() {
	return array(
		'hide' => __( 'Pop-up nicht mehr anzeigen', 'fairflock' ),
		'show' => __( 'Weiter anzeigen mit Abschluss-Text', 'fairflock' ),
	);
}

/**
 * Saved settings merged over defaults.
 *
 * @since 0.9.507
 *
 * @return array<string, string>
 */
function fairflock_popup_settings() {
	$saved = get_option( 'fairflock_popup', array() );
	$saved = is_array( $saved ) ? $saved : array();
	return array_merge( fairflock_popup_defaults(), array_map( 'strval', $saved ) );
}

/**
 * Countdown end as Unix timestamp (site timezone), 0 if unset/invalid.
 *
 * @since 0.9.507
 *
 * @param string $local Local datetime "Y-m-d\TH:i".
 * @return int
 */
function fairflock_popup_end_timestamp( $local ) {
	if ( '' === $local ) {
		return 0;
	}
	$date = date_create_immutable_from_format( 'Y-m-d\TH:i', $local, wp_timezone() );
	return $date ? $date->getTimestamp() : 0;
}

/**
 * Whether an admin preview was requested (?ff_popup_preview=1).
 *
 * @since 0.9.507
 *
 * @return bool
 */
function fairflock_popup_is_preview() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only preview flag.
	return ! empty( $_GET['ff_popup_preview'] ) && current_user_can( 'manage_options' );
}

/**
 * Whether the pop-up should render on this request.
 *
 * @since 0.9.507
 *
 * @return bool
 */
function fairflock_popup_should_render() {
	if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() ) {
		return false;
	}
	if ( fairflock_popup_is_preview() ) {
		return true;
	}

	$s = fairflock_popup_settings();
	if ( '1' !== $s['enabled'] ) {
		return false;
	}
	$end = fairflock_popup_end_timestamp( $s['end'] );
	if ( $end <= 0 ) {
		return false;
	}
	if ( $end <= time() && 'hide' === $s['after_end'] ) {
		return false;
	}
	if ( function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() || is_account_page() ) ) {
		return false;
	}

	/**
	 * Filters whether the countdown pop-up renders on the current page.
	 *
	 * @since 0.9.507
	 *
	 * @param bool $render Render.
	 */
	return (bool) apply_filters( 'fairflock_popup_should_render', true );
}

/**
 * Enqueues pop-up assets when it renders.
 *
 * @since 0.9.507
 *
 * @return void
 */
function fairflock_popup_enqueue() {
	if ( ! fairflock_popup_should_render() ) {
		return;
	}

	wp_enqueue_style(
		'fairflock-popup',
		FAIRFLOCK_URI . '/assets/css/popup.css',
		array(),
		FAIRFLOCK_VERSION
	);
	wp_enqueue_script(
		'fairflock-popup',
		FAIRFLOCK_URI . '/assets/js/popup.js',
		array(),
		FAIRFLOCK_VERSION,
		true
	);

	$s   = fairflock_popup_settings();
	$end = fairflock_popup_end_timestamp( $s['end'] );
	if ( $end <= 0 ) {
		$end = time() + WEEK_IN_SECONDS;
	}

	$config = array(
		'end'       => $end * 1000,
		'now'       => time() * 1000,
		'delay'     => fairflock_popup_is_preview() ? 0 : max( 0, (int) $s['delay'] ) * 1000,
		'frequency' => fairflock_popup_is_preview() ? 'always' : $s['frequency'],
		'afterEnd'  => $s['after_end'],
		'key'       => 'ffPopup_' . substr( md5( $s['title'] . '|' . $s['text'] . '|' . $s['end'] ), 0, 10 ),
	);
	wp_add_inline_script( 'fairflock-popup', 'window.fairflockPopup = ' . wp_json_encode( $config ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'fairflock_popup_enqueue', 30 );

/**
 * Rotating handball for the pop-up background.
 *
 * Meridian seams scale on X like a globe turning around its vertical axis;
 * equator seam + shading gradient sell the sphere.
 *
 * @since 0.9.508
 *
 * @return string SVG markup.
 */
function fairflock_popup_ball_svg() {
	$meridians = '';
	foreach ( array( 0, 1, 2 ) as $i ) {
		$meridians .= '<ellipse class="ff-ball__meridian" rx="48" ry="48" style="--ff-ball-i:' . $i . '"/>';
	}

	return '<svg class="ff-ball" viewBox="-50 -50 100 100" focusable="false">'
		. '<defs>'
		. '<radialGradient id="ffBallShade" cx="34%" cy="28%" r="78%">'
		. '<stop offset="0" stop-color="#fff" stop-opacity="0.5"/>'
		. '<stop offset="0.42" stop-color="#fff" stop-opacity="0.08"/>'
		. '<stop offset="1" stop-color="#000" stop-opacity="0.6"/>'
		. '</radialGradient>'
		. '<clipPath id="ffBallClip"><circle r="48"/></clipPath>'
		. '</defs>'
		. '<g class="ff-ball__float"><g transform="rotate(-22)">'
		. '<circle class="ff-ball__body" r="48"/>'
		. '<g class="ff-ball__seams" clip-path="url(#ffBallClip)">'
		. '<ellipse class="ff-ball__equator" rx="48" ry="13"/>'
		. $meridians
		. '</g>'
		. '<circle r="48" fill="url(#ffBallShade)"/>'
		. '<circle class="ff-ball__rim" r="47.4"/>'
		. '</g></g>'
		. '</svg>';
}

/**
 * Prints the pop-up markup in the footer.
 *
 * @since 0.9.507
 *
 * @return void
 */
function fairflock_popup_render() {
	if ( ! fairflock_popup_should_render() ) {
		return;
	}

	$s     = fairflock_popup_settings();
	$units = array(
		'days'    => __( 'Tage', 'fairflock' ),
		'hours'   => __( 'Std.', 'fairflock' ),
		'minutes' => __( 'Min.', 'fairflock' ),
		'seconds' => __( 'Sek.', 'fairflock' ),
	);
	?>
	<div class="ff-popup" id="ffPopup" hidden>
		<div class="ff-popup__backdrop" data-ff-popup-close></div>
		<div class="ff-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="ffPopupTitle" aria-describedby="ffPopupText" tabindex="-1">
			<div class="ff-popup__bg" aria-hidden="true"><?php echo fairflock_popup_ball_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></div>
			<button type="button" class="ff-popup__close" data-ff-popup-close aria-label="<?php esc_attr_e( 'Schließen', 'fairflock' ); ?>">
				<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
			<div class="ff-popup__body">
				<h2 class="ff-popup__title" id="ffPopupTitle"><?php echo esc_html( $s['title'] ); ?></h2>
				<p class="ff-popup__text" id="ffPopupText"><?php echo nl2br( esc_html( $s['text'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></p>
				<div class="ff-popup__countdown" role="timer" aria-live="off">
					<?php foreach ( $units as $unit => $label ) : ?>
						<div class="ff-popup__unit">
							<span class="ff-popup__num" data-ff-unit="<?php echo esc_attr( $unit ); ?>">00</span>
							<span class="ff-popup__label"><?php echo esc_html( $label ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
				<p class="ff-popup__done" hidden><?php echo esc_html( $s['done_text'] ); ?></p>
				<?php if ( '' !== trim( $s['cta_label'] ) && '' !== trim( $s['cta_url'] ) ) : ?>
					<a class="ff-popup__cta" href="<?php echo esc_url( $s['cta_url'] ); ?>"><?php echo esc_html( $s['cta_label'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'fairflock_popup_render', 5 );

/**
 * Registers the admin page.
 *
 * @since 0.9.507
 *
 * @return void
 */
function fairflock_popup_register_admin_page() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Pop-up', 'fairflock' ),
		__( 'Pop-up', 'fairflock' ),
		'manage_options',
		'fairflock-popup',
		'fairflock_popup_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_popup_register_admin_page', 30 );

/**
 * Saves pop-up settings.
 *
 * @since 0.9.507
 *
 * @return void
 */
function fairflock_popup_handle_admin_save() {
	if ( ! isset( $_POST['fairflock_popup_save'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'fairflock_popup_settings' );

	$in  = isset( $_POST['fairflock_popup'] ) && is_array( $_POST['fairflock_popup'] ) ? wp_unslash( $_POST['fairflock_popup'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
	$def = fairflock_popup_defaults();

	$end = isset( $in['end'] ) ? sanitize_text_field( (string) $in['end'] ) : '';
	if ( '' !== $end && ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $end ) ) {
		$end = '';
	}

	$frequency = isset( $in['frequency'] ) ? sanitize_key( (string) $in['frequency'] ) : $def['frequency'];
	if ( ! array_key_exists( $frequency, fairflock_popup_frequency_choices() ) ) {
		$frequency = $def['frequency'];
	}
	$after_end = isset( $in['after_end'] ) ? sanitize_key( (string) $in['after_end'] ) : $def['after_end'];
	if ( ! array_key_exists( $after_end, fairflock_popup_after_end_choices() ) ) {
		$after_end = $def['after_end'];
	}

	$settings = array(
		'enabled'   => ! empty( $in['enabled'] ) ? '1' : '',
		'title'     => isset( $in['title'] ) ? sanitize_text_field( (string) $in['title'] ) : $def['title'],
		'text'      => isset( $in['text'] ) ? sanitize_textarea_field( (string) $in['text'] ) : $def['text'],
		'end'       => $end,
		'done_text' => isset( $in['done_text'] ) ? sanitize_text_field( (string) $in['done_text'] ) : $def['done_text'],
		'cta_label' => isset( $in['cta_label'] ) ? sanitize_text_field( (string) $in['cta_label'] ) : '',
		'cta_url'   => isset( $in['cta_url'] ) ? esc_url_raw( (string) $in['cta_url'] ) : '',
		'delay'     => (string) min( 60, max( 0, isset( $in['delay'] ) ? (int) $in['delay'] : 0 ) ),
		'frequency' => $frequency,
		'after_end' => $after_end,
	);
	update_option( 'fairflock_popup', $settings, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'fairflock-popup',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'fairflock_popup_handle_admin_save' );

/**
 * Renders the admin page.
 *
 * @since 0.9.507
 *
 * @return void
 */
function fairflock_popup_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s       = fairflock_popup_settings();
	$end     = fairflock_popup_end_timestamp( $s['end'] );
	$preview = add_query_arg( 'ff_popup_preview', '1', home_url( '/' ) );
	$name    = static function ( $key ) {
		return 'fairflock_popup[' . $key . ']';
	};
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Pop-up mit Countdown', 'fairflock' ); ?></h1>
		<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Einstellungen gespeichert.', 'fairflock' ); ?></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Zeigt Besuchern ein Pop-up mit Countdown bis zum eingestellten Zeitpunkt (z. B. dem nächsten Anwurf). Warenkorb, Kasse und Kundenkonto bleiben frei.', 'fairflock' ); ?></p>
		<p>
			<a class="button" href="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Vorschau öffnen', 'fairflock' ); ?></a>
			<span class="description"><?php esc_html_e( 'Nur für Admins sichtbar — zeigt das Pop-up sofort, auch wenn es deaktiviert ist.', 'fairflock' ); ?></span>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'fairflock_popup_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Aktiv', 'fairflock' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $name( 'enabled' ) ); ?>" value="1" <?php checked( '1', $s['enabled'] ); ?> />
							<?php esc_html_e( 'Pop-up auf der Website anzeigen', 'fairflock' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ffPopupEnd"><?php esc_html_e( 'Countdown endet am', 'fairflock' ); ?></label></th>
					<td>
						<input type="datetime-local" id="ffPopupEnd" name="<?php echo esc_attr( $name( 'end' ) ); ?>" value="<?php echo esc_attr( $s['end'] ); ?>" />
						<p class="description">
							<?php
							printf(
								/* translators: %s: timezone name */
								esc_html__( 'Zeitzone der Website: %s. Ohne Datum wird das Pop-up nicht angezeigt.', 'fairflock' ),
								esc_html( wp_timezone_string() )
							);
							if ( $end > 0 && $end <= time() ) {
								echo ' <strong>' . esc_html__( 'Dieser Zeitpunkt liegt in der Vergangenheit.', 'fairflock' ) . '</strong>';
							}
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ffPopupTitle"><?php esc_html_e( 'Titel', 'fairflock' ); ?></label></th>
					<td><input type="text" class="large-text" id="ffPopupTitle" name="<?php echo esc_attr( $name( 'title' ) ); ?>" value="<?php echo esc_attr( $s['title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="ffPopupText"><?php esc_html_e( 'Text', 'fairflock' ); ?></label></th>
					<td>
						<textarea class="large-text" rows="3" id="ffPopupText" name="<?php echo esc_attr( $name( 'text' ) ); ?>"><?php echo esc_textarea( $s['text'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Steht über dem Countdown.', 'fairflock' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ffPopupDone"><?php esc_html_e( 'Text nach Ablauf', 'fairflock' ); ?></label></th>
					<td>
						<input type="text" class="large-text" id="ffPopupDone" name="<?php echo esc_attr( $name( 'done_text' ) ); ?>" value="<?php echo esc_attr( $s['done_text'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Ersetzt den Countdown, sobald er bei 0 ist.', 'fairflock' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Nach Ablauf', 'fairflock' ); ?></th>
					<td>
						<?php foreach ( fairflock_popup_after_end_choices() as $key => $label ) : ?>
							<label style="display:block;margin-bottom:6px;">
								<input type="radio" name="<?php echo esc_attr( $name( 'after_end' ) ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['after_end'], $key ); ?> />
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Button (optional)', 'fairflock' ); ?></th>
					<td>
						<input type="text" class="regular-text" name="<?php echo esc_attr( $name( 'cta_label' ) ); ?>" value="<?php echo esc_attr( $s['cta_label'] ); ?>" placeholder="<?php esc_attr_e( 'Beschriftung', 'fairflock' ); ?>" />
						<input type="url" class="regular-text" name="<?php echo esc_attr( $name( 'cta_url' ) ); ?>" value="<?php echo esc_attr( $s['cta_url'] ); ?>" placeholder="https://" />
						<p class="description"><?php esc_html_e( 'Nur sichtbar, wenn Beschriftung und Link gesetzt sind.', 'fairflock' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ffPopupDelay"><?php esc_html_e( 'Verzögerung', 'fairflock' ); ?></label></th>
					<td>
						<input type="number" min="0" max="60" class="small-text" id="ffPopupDelay" name="<?php echo esc_attr( $name( 'delay' ) ); ?>" value="<?php echo esc_attr( $s['delay'] ); ?>" />
						<?php esc_html_e( 'Sekunden nach dem Laden der Seite', 'fairflock' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Häufigkeit', 'fairflock' ); ?></th>
					<td>
						<?php foreach ( fairflock_popup_frequency_choices() as $key => $label ) : ?>
							<label style="display:block;margin-bottom:6px;">
								<input type="radio" name="<?php echo esc_attr( $name( 'frequency' ) ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['frequency'], $key ); ?> />
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
						<p class="description"><?php esc_html_e( 'Ändern sich Titel, Text oder Datum, sehen alle Besucher das Pop-up erneut.', 'fairflock' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Speichern', 'fairflock' ), 'primary', 'fairflock_popup_save' ); ?>
		</form>
	</div>
	<?php
}
