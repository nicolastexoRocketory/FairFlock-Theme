<?php
/**
 * FairFlock contact form — AJAX submit.
 *
 * @package FairFlock
 * @since 0.9.260
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   Formular-Verarbeitung (AJAX)
   ============================================================ */

add_action( 'wp_ajax_fairflock_form_submit', 'fairflock_form_handle_submit' );
add_action( 'wp_ajax_nopriv_fairflock_form_submit', 'fairflock_form_handle_submit' );
add_action( 'wp_ajax_fairflock_form_refresh_nonce', 'fairflock_form_refresh_nonce' );
add_action( 'wp_ajax_nopriv_fairflock_form_refresh_nonce', 'fairflock_form_refresh_nonce' );

function fairflock_form_refresh_nonce() {
	wp_send_json_success(
		array(
			'nonce' => wp_create_nonce( 'fairflock_form_submit' ),
		)
	);
}

function fairflock_form_handle_submit() {
	$opts = fairflock_form_get_opts();

	if ( ! isset( $_POST['fairflock_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fairflock_form_nonce'] ) ), 'fairflock_form_submit' ) ) {
		wp_send_json_error( array( 'message' => 'Sicherheitsprüfung fehlgeschlagen. Bitte Seite neu laden und erneut versuchen.' ) );
	}

	// Honeypot
	if ( ! empty( $_POST['ff_form_website'] ) ) {
		wp_send_json_error( array( 'message' => $opts['error_message'] ) );
	}

	$rate_wait = fairflock_form_is_rate_limited( $opts );
	if ( false !== $rate_wait ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
					'Zu viele Anfragen. Bitte warte %d Minuten und versuche es erneut.',
					(int) $rate_wait
				),
			)
		);
	}

	if ( ! empty( $opts['privacy_enabled'] ) && '1' === $opts['privacy_enabled'] && empty( $_POST['ff_form_privacy_consent'] ) ) {
		wp_send_json_error( array( 'message' => 'Bitte bestätige die Datenschutzerklärung.' ) );
	}

	$turnstile_token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
	if ( ! fairflock_form_verify_turnstile( $opts, $turnstile_token ) ) {
		wp_send_json_error( array( 'message' => 'Spam-Schutz nicht bestanden. Bitte Seite neu laden und erneut versuchen.' ) );
	}

	$submitted = array();
	$by_name   = array();
	$errors    = array();

	foreach ( $opts['fields'] as $field ) {
		$key   = $field['name'];
		$label = $field['label'];
		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

		if ( 'checkbox' === $field['type'] ) {
			$checked = ! empty( $raw );
			if ( '1' === $field['required'] && ! $checked ) {
				$errors[] = sprintf( '„%s“ muss bestätigt werden.', $label );
				continue;
			}
			$value = $checked ? __( 'Ja', 'fairflock' ) : __( 'Nein', 'fairflock' );
		} elseif ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} elseif ( 'email' === $field['type'] ) {
			$value = sanitize_email( $raw );
			if ( '1' === $field['required'] && ! is_email( $value ) ) {
				$errors[] = sprintf( 'Bitte gib eine gültige E-Mail für „%s“ an.', $label );
				continue;
			}
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( '1' === $field['required'] && '' === trim( (string) $value ) ) {
			$errors[] = sprintf( '„%s“ ist ein Pflichtfeld.', $label );
			continue;
		}

		if ( 'select' === $field['type'] && '' !== $value ) {
			$allowed = fairflock_form_parse_options( $field['options'] );
			if ( ! empty( $allowed ) && ! in_array( $value, $allowed, true ) ) {
				$errors[] = sprintf( 'Ungültige Auswahl für „%s“.', $label );
				continue;
			}
		}

		$submitted[ $label ] = $value;
		$by_name[ $key ]     = $value;
	}

	if ( ! empty( $errors ) ) {
		wp_send_json_error( array( 'message' => implode( ' ', $errors ) ) );
	}

	$entry_id = fairflock_form_save_entry( $submitted, $by_name );
	if ( is_wp_error( $entry_id ) ) {
		wp_send_json_error( array( 'message' => $opts['error_message'] ) );
	}

	$result = fairflock_form_send_mail( $submitted, $opts, $by_name );
	if ( is_wp_error( $result ) ) {
		update_post_meta( $entry_id, '_ff_entry_mail_sent', '0' );
		update_post_meta( $entry_id, '_ff_entry_mail_error', $result->get_error_message() );
	} else {
		update_post_meta( $entry_id, '_ff_entry_mail_sent', '1' );
		delete_post_meta( $entry_id, '_ff_entry_mail_error' );
	}

	$confirm = fairflock_form_send_confirmation( $submitted, $opts, $by_name );
	if ( is_wp_error( $confirm ) ) {
		update_post_meta( $entry_id, '_ff_entry_confirm_sent', '0' );
		update_post_meta( $entry_id, '_ff_entry_confirm_error', $confirm->get_error_message() );
	} else {
		update_post_meta( $entry_id, '_ff_entry_confirm_sent', '1' );
		delete_post_meta( $entry_id, '_ff_entry_confirm_error' );
	}

	fairflock_form_bump_rate_limit( $opts );
	wp_send_json_success( array( 'message' => $opts['success_message'] ) );
}

function fairflock_form_parse_options( $options_str ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $options_str );
	$out   = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$out[] = $line;
		}
	}
	return $out;
}
