<?php
/**
 * FairFlock contact form — mail / SMTP.
 *
 * @package FairFlock
 * @since 0.9.260
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   E-Mail / SMTP (direkt via PHPMailer – umgeht wp_mail-Konflikte)
   ============================================================ */

add_action( 'wp_mail_failed', 'fairflock_form_log_mail_error' );
function fairflock_form_log_mail_error( $error ) {
	if ( empty( $GLOBALS['fairflock_form_sending'] ) || ! is_wp_error( $error ) ) {
		return;
	}
	fairflock_form_log_mail_error_message( $error->get_error_message() );
}

function fairflock_form_log_mail_error_message( $message ) {
	update_option(
		FAIRFLOCK_FORM_MAIL_ERR_OPT,
		array(
			'time'    => time(),
			'message' => (string) $message,
		),
		false
	);
}

function fairflock_form_load_phpmailer() {
	if ( ! class_exists( 'PHPMailer\PHPMailer\PHPMailer', false ) ) {
		require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
		require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
		require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
	}
}

function fairflock_form_is_smtp_active( $opts ) {
	return ! empty( $opts['smtp_enabled'] ) && '1' === $opts['smtp_enabled'] && ! empty( $opts['smtp_host'] );
}

function fairflock_form_turnstile_enabled( $opts ) {
	return ! empty( $opts['cf_turnstile_enabled'] ) && '1' === $opts['cf_turnstile_enabled']
		&& ! empty( $opts['cf_turnstile_site_key'] ) && ! empty( $opts['cf_turnstile_secret_key'] );
}

function fairflock_form_verify_turnstile( $opts, $token ) {
	if ( ! fairflock_form_turnstile_enabled( $opts ) ) {
		return true;
	}

	$token = sanitize_text_field( (string) $token );
	if ( '' === $token ) {
		return false;
	}

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 15,
			'body'    => array(
				'secret'   => $opts['cf_turnstile_secret_key'],
				'response' => $token,
				'remoteip' => fairflock_form_client_ip(),
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $body['success'] );
}

function fairflock_form_get_smtp_user( $opts ) {
	if ( ! empty( $opts['smtp_user'] ) ) {
		return $opts['smtp_user'];
	}
	if ( ! empty( $opts['smtp_from_email'] ) && is_email( $opts['smtp_from_email'] ) ) {
		return $opts['smtp_from_email'];
	}
	return '';
}

function fairflock_form_log_mail_success( $to, $method ) {
	update_option(
		FAIRFLOCK_FORM_MAIL_SUCCESS_OPT,
		array(
			'time'    => time(),
			'to'      => is_array( $to ) ? implode( ', ', $to ) : (string) $to,
			'method'  => (string) $method,
		),
		false
	);
}

function fairflock_form_get_default_from_email() {
	$sitename = wp_parse_url( network_home_url(), PHP_URL_HOST );
	if ( is_string( $sitename ) ) {
		$sitename = strtolower( $sitename );
		if ( 0 === strpos( $sitename, 'www.' ) ) {
			$sitename = substr( $sitename, 4 );
		}
	} else {
		$sitename = 'localhost';
	}
	return 'wordpress@' . $sitename;
}

function fairflock_form_get_from( $opts ) {
	$name = ! empty( $opts['smtp_from_name'] ) ? $opts['smtp_from_name'] : get_bloginfo( 'name' );

	if ( ! empty( $opts['smtp_from_email'] ) && is_email( $opts['smtp_from_email'] ) ) {
		return array( $opts['smtp_from_email'], $name );
	}

	$admin = get_option( 'admin_email' );
	if ( is_email( $admin ) ) {
		return array( $admin, $name );
	}

	return array( fairflock_form_get_default_from_email(), $name );
}

function fairflock_form_add_reply_to( $mail, $opts, $by_name ) {
	$reply_field = sanitize_key( $opts['reply_to_field'] ?? '' );
	if ( ! $reply_field || ! isset( $by_name[ $reply_field ] ) ) {
		return;
	}

	$reply_email = sanitize_email( $by_name[ $reply_field ] );
	if ( ! is_email( $reply_email ) ) {
		return;
	}

	$reply_name = isset( $by_name['name'] ) ? sanitize_text_field( $by_name['name'] ) : '';
	$mail->addReplyTo( $reply_email, $reply_name );
}

function fairflock_form_configure_phpmailer_transport( $mail, $opts, $profile = array() ) {
	$port        = isset( $profile['port'] ) ? (int) $profile['port'] : (int) $opts['smtp_port'];
	$encryption  = $profile['encryption'] ?? ( $opts['smtp_encryption'] ?? 'tls' );

	if ( fairflock_form_is_smtp_active( $opts ) ) {
		$mail->isSMTP();
		$mail->Host       = $opts['smtp_host'];
		$mail->Port       = $port;
		$mail->SMTPAuth   = ! empty( $opts['smtp_auth'] ) && '1' === $opts['smtp_auth'];
		$mail->Timeout    = 25;
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer'       => true,
				'verify_peer_name'  => true,
				'allow_self_signed' => false,
			),
		);

		if ( 'ssl' === $encryption ) {
			$mail->SMTPSecure = 'ssl';
		} elseif ( 'tls' === $encryption ) {
			$mail->SMTPSecure = 'tls';
		} else {
			$mail->SMTPSecure  = '';
			$mail->SMTPAutoTLS = false;
		}

		if ( $mail->SMTPAuth ) {
			$mail->Username = fairflock_form_get_smtp_user( $opts );
			$mail->Password = $opts['smtp_pass'] ?? '';
		}
	} else {
		$mail->isMail();
	}
}

function fairflock_form_send_mail_direct( $to, $subject, $html_body, $plain_body, $opts, $by_name, $html = true, $debug = false, $profile = array(), $custom_reply_to = null ) {
	fairflock_form_load_phpmailer();

	$mail      = new PHPMailer\PHPMailer\PHPMailer( true );
	$debug_log = '';

	try {
		if ( $debug ) {
			$mail->SMTPDebug   = 2;
			$mail->Debugoutput = static function ( $str ) use ( &$debug_log ) {
				$debug_log .= $str;
			};
		}

		fairflock_form_configure_phpmailer_transport( $mail, $opts, $profile );

		list( $from_email, $from_name ) = fairflock_form_get_from( $opts );
		$mail->setFrom( $from_email, $from_name, false );
		$mail->Sender = $from_email;

		foreach ( $to as $recipient ) {
			$mail->addAddress( $recipient );
		}

		if ( is_array( $custom_reply_to ) && ! empty( $custom_reply_to['email'] ) && is_email( $custom_reply_to['email'] ) ) {
			$mail->addReplyTo( $custom_reply_to['email'], $custom_reply_to['name'] ?? '' );
		} else {
			fairflock_form_add_reply_to( $mail, $opts, $by_name );
		}

		$mail->CharSet = 'UTF-8';
		$mail->Subject = $subject;
		$mail->XMailer = 'FairFlock-Formular';

		if ( $html ) {
			$mail->isHTML( true );
			$mail->Body    = $html_body;
			$mail->AltBody = $plain_body;
		} else {
			$mail->isHTML( false );
			$mail->Body = $plain_body;
		}

		$mail->send();

		if ( $debug ) {
			update_option(
				FAIRFLOCK_FORM_SMTP_DEBUG_OPT,
				array(
					'time'    => time(),
					'success' => true,
					'log'     => $debug_log,
					'profile' => $profile,
				),
				false
			);
		}

		return true;
	} catch ( PHPMailer\PHPMailer\Exception $e ) {
		$message = $e->getMessage();
		if ( ! empty( $mail->ErrorInfo ) ) {
			$message = $mail->ErrorInfo;
		}
		fairflock_form_log_mail_error_message( $message );
		if ( $debug ) {
			update_option(
				FAIRFLOCK_FORM_SMTP_DEBUG_OPT,
				array(
					'time'    => time(),
					'success' => false,
					'log'     => trim( $debug_log . "\n" . $message ),
					'profile' => $profile,
				),
				false
			);
		}
		return new WP_Error( 'send_failed', $message );
	} catch ( Exception $e ) {
		fairflock_form_log_mail_error_message( $e->getMessage() );
		if ( $debug ) {
			update_option(
				FAIRFLOCK_FORM_SMTP_DEBUG_OPT,
				array(
					'time'    => time(),
					'success' => false,
					'log'     => trim( $debug_log . "\n" . $e->getMessage() ),
					'profile' => $profile,
				),
				false
			);
		}
		return new WP_Error( 'send_failed', $e->getMessage() );
	}
}

function fairflock_form_smtp_test_profiles( $opts ) {
	$profiles = array(
		array(
			'port'        => (int) ( $opts['smtp_port'] ?? 587 ),
			'encryption'  => $opts['smtp_encryption'] ?? 'tls',
			'label'       => 'Gespeichert',
		),
	);

	if ( fairflock_form_is_smtp_active( $opts ) ) {
		$profiles[] = array(
			'port'       => 465,
			'encryption' => 'ssl',
			'label'      => '465 / SSL',
		);
		$profiles[] = array(
			'port'       => 587,
			'encryption' => 'tls',
			'label'      => '587 / TLS',
		);
	}

	$seen   = array();
	$unique = array();
	foreach ( $profiles as $profile ) {
		$key = $profile['port'] . '|' . $profile['encryption'];
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$unique[]     = $profile;
	}

	return $unique;
}

function fairflock_form_run_smtp_test( $opts ) {
	$to = fairflock_form_parse_recipients( $opts['recipient_email'] );
	if ( empty( $to ) ) {
		return new WP_Error( 'invalid_recipient', 'Keine gültige Empfänger-E-Mail hinterlegt.' );
	}

	if ( fairflock_form_is_smtp_active( $opts ) && empty( $opts['smtp_pass'] ) ) {
		return new WP_Error( 'missing_password', 'SMTP-Passwort fehlt. Bitte speichern und erneut testen.' );
	}

	$subject    = 'SMTP-Test – FairFlock Kontaktformular';
	$test_data  = array(
		'Name'      => 'SMTP Test',
		'E-Mail'    => get_option( 'admin_email' ),
		'Betreff'   => 'Verbindungstest',
		'Nachricht' => 'Dies ist eine Test-E-Mail vom FairFlock Kontaktformular.',
	);
	$html_body  = fairflock_form_build_email_html( $test_data, $opts );
	$plain_body = fairflock_form_build_email_plain( $test_data );
	$attempts   = array();

	foreach ( fairflock_form_smtp_test_profiles( $opts ) as $profile ) {
		$result = fairflock_form_send_mail_direct(
			$to,
			$subject,
			$html_body,
			$plain_body,
			$opts,
			array( 'email' => get_option( 'admin_email' ) ),
			true,
			true,
			$profile
		);

		$attempts[] = array(
			'profile' => $profile,
			'result'  => $result,
		);

		if ( true === $result ) {
			fairflock_form_log_mail_success( $to, $profile['label'] . ' (' . $profile['port'] . '/' . $profile['encryption'] . ')' );
			return array(
				'success'  => true,
				'profile'  => $profile,
				'to'       => $to,
				'attempts' => $attempts,
			);
		}
	}

	$last = end( $attempts );
	$msg  = 'SMTP-Test fehlgeschlagen.';
	if ( $last && is_wp_error( $last['result'] ) ) {
		$msg .= ' ' . $last['result']->get_error_message();
	}

	return new WP_Error( 'smtp_test_failed', $msg, array( 'attempts' => $attempts ) );
}

function fairflock_form_build_mail_headers( $opts, $by_name, $is_html = false ) {
	$headers = array();

	if ( $is_html ) {
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
	}

	list( $from_email, $from_name ) = fairflock_form_get_from( $opts );
	if ( is_email( $from_email ) ) {
		$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
	}

	$reply_field = sanitize_key( $opts['reply_to_field'] ?? '' );
	if ( $reply_field && isset( $by_name[ $reply_field ] ) ) {
		$reply_email = sanitize_email( $by_name[ $reply_field ] );
		if ( is_email( $reply_email ) ) {
			$reply_name = isset( $by_name['name'] ) ? sanitize_text_field( $by_name['name'] ) : '';
			$headers[]  = 'Reply-To: ' . ( $reply_name ? $reply_name . ' <' . $reply_email . '>' : $reply_email );
		}
	}

	return $headers;
}

function fairflock_form_send_mail_wp_fallback( $to, $subject, $body, $opts, $by_name, $is_html = false ) {
	$headers = fairflock_form_build_mail_headers( $opts, $by_name, $is_html );

	$GLOBALS['fairflock_form_sending'] = true;
	$sent                         = wp_mail( $to, $subject, $body, $headers );
	unset( $GLOBALS['fairflock_form_sending'] );

	if ( $sent ) {
		return true;
	}

	$last = get_option( FAIRFLOCK_FORM_MAIL_ERR_OPT );
	$msg  = is_array( $last ) && ! empty( $last['message'] ) ? $last['message'] : 'wp_mail() fehlgeschlagen.';
	return new WP_Error( 'send_failed', $msg );
}

function fairflock_form_parse_recipients( $raw ) {
	$parts = preg_split( '/[\r\n,;]+/', (string) $raw );
	$valid = array();
	foreach ( $parts as $part ) {
		$email = sanitize_email( trim( $part ) );
		if ( is_email( $email ) ) {
			$valid[] = $email;
		}
	}
	return array_values( array_unique( $valid ) );
}

function fairflock_form_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return $ip ?: 'unknown';
}

function fairflock_form_rate_limit_key() {
	return 'ff_form_rl_' . md5( fairflock_form_client_ip() );
}

function fairflock_form_is_rate_limited( $opts ) {
	$limit = max( 1, (int) ( $opts['rate_limit_count'] ?? 5 ) );
	$mins  = max( 1, (int) ( $opts['rate_limit_minutes'] ?? 60 ) );
	$key   = fairflock_form_rate_limit_key();
	$count = (int) get_transient( $key );
	return $count >= $limit ? $mins : false;
}

function fairflock_form_bump_rate_limit( $opts ) {
	$mins = max( 1, (int) ( $opts['rate_limit_minutes'] ?? 60 ) );
	$key  = fairflock_form_rate_limit_key();
	$count = (int) get_transient( $key );
	set_transient( $key, $count + 1, $mins * MINUTE_IN_SECONDS );
}

/* ============================================================
   E-Mail Template
   ============================================================ */

function fairflock_form_build_email_html( $submitted, $opts ) {
	$rows_html = '';
	foreach ( $submitted as $label => $value ) {
		$value_safe = nl2br( esc_html( $value ) );
		$rows_html .= '
			<tr>
				<td style="padding:12px 16px;border-bottom:1px solid #2a2a2a;color:#a3a3a3;font-size:13px;font-weight:600;width:32%;vertical-align:top;">' . esc_html( $label ) . '</td>
				<td style="padding:12px 16px;border-bottom:1px solid #2a2a2a;color:#f5f5f5;font-size:14px;line-height:1.55;vertical-align:top;">' . $value_safe . '</td>
			</tr>';
	}

	$site     = get_bloginfo( 'name' );
	$date_str = wp_date( 'd.m.Y, H:i' );

	return '<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#111111;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#111111;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background:#0f0f0f;border:1px solid #2a2a2a;">
	<tr>
		<td style="background:#377530;padding:18px 24px;">
			<p style="margin:0;color:#ffffff;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">FairFlock</p>
			<h1 style="margin:6px 0 0;color:#ffffff;font-size:20px;font-weight:800;line-height:1.25;">Neue Kontaktanfrage</h1>
		</td>
	</tr>
	<tr>
		<td style="padding:20px 24px 8px;">
			<p style="margin:0 0 16px;color:#a3a3a3;font-size:13px;line-height:1.5;">Eine neue Nachricht ist über das Kontaktformular auf <strong style="color:#f5f5f5;">' . esc_html( $site ) . '</strong> eingegangen.</p>
			<p style="margin:0 0 20px;color:#737373;font-size:12px;">Eingegangen am ' . esc_html( $date_str ) . '</p>
			<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #2a2a2a;border-collapse:collapse;background:#141414;">
				' . $rows_html . '
			</table>
		</td>
	</tr>
	<tr>
		<td style="padding:8px 24px 24px;">
			<p style="margin:0;color:#737373;font-size:11px;line-height:1.5;">Diese E-Mail wurde automatisch vom Kontaktformular versendet.</p>
		</td>
	</tr>
</table>
</td></tr>
</table>
</body>
</html>';
}

function fairflock_form_build_email_plain( $submitted ) {
	$lines = array( 'Neue Kontaktanfrage – FairFlock', str_repeat( '-', 40 ), '' );
	foreach ( $submitted as $label => $value ) {
		$lines[] = $label . ':';
		$lines[] = $value;
		$lines[] = '';
	}
	$lines[] = 'Eingegangen am ' . wp_date( 'd.m.Y, H:i' );
	return implode( "\n", $lines );
}

function fairflock_form_get_customer_email( $opts, $by_name ) {
	$candidates = array();
	$reply_field = sanitize_key( $opts['reply_to_field'] ?? 'email' );
	if ( $reply_field ) {
		$candidates[] = $reply_field;
	}
	$candidates = array_merge( $candidates, array( 'email', 'e_mail', 'mail' ) );

	foreach ( $candidates as $key ) {
		if ( empty( $by_name[ $key ] ) ) {
			continue;
		}
		$email = sanitize_email( $by_name[ $key ] );
		if ( is_email( $email ) ) {
			return $email;
		}
	}

	return '';
}

function fairflock_form_build_confirmation_html( $submitted, $opts, $by_name = array() ) {
	$name = ! empty( $by_name['name'] ) ? sanitize_text_field( $by_name['name'] ) : '';
	$greeting = $name ? ( 'Hallo ' . $name . ',' ) : 'Hallo,';
	$intro    = ! empty( $opts['confirm_intro'] ) ? $opts['confirm_intro'] : fairflock_form_defaults()['confirm_intro'];
	$intro_html = nl2br( esc_html( $intro ) );
	$date_str = wp_date( 'd.m.Y, H:i' );

	$rows_html = '';
	foreach ( $submitted as $label => $value ) {
		$value_safe = nl2br( esc_html( $value ) );
		$rows_html .= '
			<tr>
				<td style="padding:12px 16px;border-bottom:1px solid #2a2a2a;color:#a3a3a3;font-size:13px;font-weight:600;width:32%;vertical-align:top;">' . esc_html( $label ) . '</td>
				<td style="padding:12px 16px;border-bottom:1px solid #2a2a2a;color:#f5f5f5;font-size:14px;line-height:1.55;vertical-align:top;">' . $value_safe . '</td>
			</tr>';
	}

	return '<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#111111;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#111111;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background:#0f0f0f;border:1px solid #2a2a2a;">
	<tr>
		<td style="background:#377530;padding:18px 24px;">
			<p style="margin:0;color:#ffffff;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">FairFlock</p>
			<h1 style="margin:6px 0 0;color:#ffffff;font-size:20px;font-weight:800;line-height:1.25;">Nachricht erhalten</h1>
		</td>
	</tr>
	<tr>
		<td style="padding:22px 24px 8px;">
			<p style="margin:0 0 12px;color:#f5f5f5;font-size:15px;font-weight:700;line-height:1.4;">' . esc_html( $greeting ) . '</p>
			<p style="margin:0 0 18px;color:#a3a3a3;font-size:14px;line-height:1.6;">' . $intro_html . '</p>
			<p style="margin:0 0 10px;color:#737373;font-size:12px;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;">Deine Angaben</p>
			<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #2a2a2a;border-collapse:collapse;background:#141414;margin-bottom:18px;">
				' . $rows_html . '
			</table>
			<p style="margin:0 0 8px;color:#737373;font-size:12px;">Eingegangen am ' . esc_html( $date_str ) . '</p>
		</td>
	</tr>
	<tr>
		<td style="padding:16px 24px 24px;">
			<p style="margin:0;color:#737373;font-size:11px;line-height:1.5;">Diese Bestätigung wurde automatisch vom Kontaktformular versendet. Du kannst auf diese E-Mail antworten – deine Antwort geht direkt an uns.</p>
		</td>
	</tr>
</table>
</td></tr>
</table>
</body>
</html>';
}

function fairflock_form_build_confirmation_plain( $submitted, $opts, $by_name = array() ) {
	$name = ! empty( $by_name['name'] ) ? sanitize_text_field( $by_name['name'] ) : '';
	$greeting = $name ? ( 'Hallo ' . $name . ',' ) : 'Hallo,';
	$intro    = ! empty( $opts['confirm_intro'] ) ? $opts['confirm_intro'] : fairflock_form_defaults()['confirm_intro'];

	$lines = array(
		'FairFlock – Bestätigung',
		str_repeat( '-', 40 ),
		'',
		$greeting,
		'',
		$intro,
		'',
		'Deine Angaben:',
		'',
	);
	foreach ( $submitted as $label => $value ) {
		$lines[] = $label . ':';
		$lines[] = $value;
		$lines[] = '';
	}
	$lines[] = 'Eingegangen am ' . wp_date( 'd.m.Y, H:i' );
	$lines[] = '';
	return implode( "\n", $lines );
}

/**
 * Bestätigungsmail an die vom Kunden eingetragene E-Mail (Delil-CI).
 */
function fairflock_form_send_confirmation( $submitted, $opts, $by_name = array() ) {
	if ( empty( $opts['confirm_enabled'] ) || '1' !== (string) $opts['confirm_enabled'] ) {
		return true;
	}

	$to_email = fairflock_form_get_customer_email( $opts, $by_name );
	if ( ! is_email( $to_email ) ) {
		return new WP_Error( 'no_customer_email', 'Keine gültige Kunden-E-Mail für die Bestätigung.' );
	}

	$subject    = ! empty( $opts['confirm_subject'] ) ? sanitize_text_field( $opts['confirm_subject'] ) : fairflock_form_defaults()['confirm_subject'];
	$html_body  = fairflock_form_build_confirmation_html( $submitted, $opts, $by_name );
	$plain_body = fairflock_form_build_confirmation_plain( $submitted, $opts, $by_name );

	// Reply-To = Fahrschule (nicht die Kundenadresse).
	$business = fairflock_form_parse_recipients( $opts['recipient_email'] ?? '' );
	list( $from_email, $from_name ) = fairflock_form_get_from( $opts );
	$reply_email = ! empty( $business[0] ) ? $business[0] : $from_email;
	$custom_reply = array(
		'email' => $reply_email,
		'name'  => $from_name,
	);

	$result = fairflock_form_send_mail_direct(
		array( $to_email ),
		$subject,
		$html_body,
		$plain_body,
		$opts,
		array(),
		true,
		false,
		array(),
		$custom_reply
	);

	if ( true === $result ) {
		return true;
	}

	$result = fairflock_form_send_mail_direct(
		array( $to_email ),
		$subject,
		$html_body,
		$plain_body,
		$opts,
		array(),
		false,
		false,
		array(),
		$custom_reply
	);

	if ( true === $result ) {
		return true;
	}

	// wp_mail-Fallback mit Reply-To an die Fahrschule.
	$headers = fairflock_form_build_mail_headers( $opts, array(), false );
	$headers[] = 'Reply-To: ' . $from_name . ' <' . $reply_email . '>';
	$GLOBALS['fairflock_form_sending'] = true;
	$sent = wp_mail( $to_email, $subject, $plain_body, $headers );
	unset( $GLOBALS['fairflock_form_sending'] );

	return $sent ? true : ( is_wp_error( $result ) ? $result : new WP_Error( 'confirm_failed', 'Bestätigungsmail fehlgeschlagen.' ) );
}

function fairflock_form_send_mail( $submitted, $opts, $by_name = array() ) {
	$to = fairflock_form_parse_recipients( $opts['recipient_email'] );
	if ( empty( $to ) ) {
		return new WP_Error( 'invalid_recipient', 'Ungültige Empfänger-E-Mail.' );
	}

	$subject    = sanitize_text_field( $opts['email_subject'] );
	$html_body  = fairflock_form_build_email_html( $submitted, $opts );
	$plain_body = fairflock_form_build_email_plain( $submitted );
	$last_error = null;

	// 1. Direkt via PHPMailer (HTML).
	$result = fairflock_form_send_mail_direct( $to, $subject, $html_body, $plain_body, $opts, $by_name, true );
	if ( true === $result ) {
		fairflock_form_log_mail_success( $to, 'PHPMailer HTML' );
		return true;
	}
	if ( is_wp_error( $result ) ) {
		$last_error = $result;
	}

	// 2. Direkt via PHPMailer (Plain-Text).
	$result = fairflock_form_send_mail_direct( $to, $subject, $html_body, $plain_body, $opts, $by_name, false );
	if ( true === $result ) {
		fairflock_form_log_mail_success( $to, 'PHPMailer Plain' );
		return true;
	}
	if ( is_wp_error( $result ) ) {
		$last_error = $result;
	}

	// 3. wp_mail-Fallback (Plain-Text).
	$result = fairflock_form_send_mail_wp_fallback( $to, $subject, $plain_body, $opts, $by_name, false );
	if ( true === $result ) {
		fairflock_form_log_mail_success( $to, 'wp_mail Plain' );
		return true;
	}
	if ( is_wp_error( $result ) ) {
		$last_error = $result;
	}

	$msg = 'E-Mail konnte nicht versendet werden.';
	if ( $last_error instanceof WP_Error ) {
		$msg .= ' (' . $last_error->get_error_message() . ')';
	}

	return new WP_Error( 'send_failed', $msg );
}

/* ============================================================
