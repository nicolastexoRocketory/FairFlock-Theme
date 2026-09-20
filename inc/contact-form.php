<?php
/**
 * FairFlock contact form — bootstrap, options, entries CPT.
 *
 * @package FairFlock
 * @since 0.9.260
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FAIRFLOCK_FORM_VERSION', '1.0.0' );
define( 'FAIRFLOCK_FORM_OPT', 'fairflock_form_opts' );
define( 'FAIRFLOCK_FORM_MAIL_ERR_OPT', 'fairflock_form_last_mail_error' );
define( 'FAIRFLOCK_FORM_SMTP_DEBUG_OPT', 'fairflock_form_smtp_debug' );
define( 'FAIRFLOCK_FORM_MAIL_SUCCESS_OPT', 'fairflock_form_last_mail_success' );
define( 'FAIRFLOCK_FORM_ENTRY_CPT', 'ff_form_entry' );

/* ============================================================
   Defaults
   ============================================================ */

function fairflock_form_defaults() {
	return array(
		'form_title'       => 'Kontakt',
		'form_intro'       => 'Schreib uns – wir melden uns so schnell wie möglich. Für schnelle Antworten erreichst du uns auch telefonisch oder per WhatsApp.',
		'submit_text'      => 'Nachricht senden',
		'success_message'  => 'Vielen Dank! Deine Nachricht wurde erfolgreich übermittelt.',
		'error_message'    => 'Leider ist ein Fehler aufgetreten. Bitte versuche es erneut oder kontaktiere uns direkt per E-Mail.',
		'recipient_email'  => get_option( 'admin_email' ),
		'email_subject'    => 'Neue Kontaktanfrage',
		'reply_to_field'   => 'email',
		'confirm_enabled'  => '1',
		'confirm_subject'  => 'Wir haben deine Nachricht erhalten',
		'confirm_intro'    => "danke für deine Nachricht. Wir haben sie erhalten und melden uns so schnell wie möglich bei dir.\n\nFür schnelle Antworten erreichst du uns auch telefonisch oder per WhatsApp.",
		'fields'           => array(
			array(
				'label'       => 'Name',
				'name'        => 'name',
				'type'        => 'text',
				'required'    => '1',
				'placeholder' => 'Dein Name',
				'options'     => '',
				'width'       => 'half',
			),
			array(
				'label'       => 'E-Mail',
				'name'        => 'email',
				'type'        => 'email',
				'required'    => '1',
				'placeholder' => 'name@beispiel.de',
				'options'     => '',
				'width'       => 'half',
			),
			array(
				'label'       => 'Betreff',
				'name'        => 'betreff',
				'type'        => 'text',
				'required'    => '1',
				'placeholder' => 'Worum geht es?',
				'options'     => '',
				'width'       => 'full',
			),
			array(
				'label'       => 'Nachricht',
				'name'        => 'nachricht',
				'type'        => 'textarea',
				'required'    => '1',
				'placeholder' => 'Deine Nachricht …',
				'options'     => '',
				'width'       => 'full',
			),
		),
		'smtp_enabled'     => '0',
		'smtp_host'        => '',
		'smtp_port'        => '587',
		'smtp_encryption'  => 'tls',
		'smtp_auth'        => '1',
		'smtp_user'        => '',
		'smtp_pass'        => '',
		'smtp_from_email'  => '',
		'smtp_from_name'   => '',
		'privacy_enabled'  => '1',
		'privacy_text'     => 'Ich habe die Datenschutzerklärung gelesen und stimme der Verarbeitung meiner Angaben zum Zweck der Kontaktaufnahme zu.',
		'privacy_url'      => '/datenschutz/',
		'rate_limit_count' => '5',
		'rate_limit_minutes' => '60',
		'cf_turnstile_enabled'   => '0',
		'cf_turnstile_site_key'  => '',
		'cf_turnstile_secret_key'=> '',
	);
}

function fairflock_form_get_opts() {
	$saved = get_option( FAIRFLOCK_FORM_OPT );
	if ( ! is_array( $saved ) ) {
		return fairflock_form_defaults();
	}
	return wp_parse_args( $saved, fairflock_form_defaults() );
}

function fairflock_form_sanitize_field_row( $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}
	$label = isset( $row['label'] ) ? sanitize_text_field( wp_unslash( $row['label'] ) ) : '';
	$name  = isset( $row['name'] ) ? sanitize_key( wp_unslash( $row['name'] ) ) : '';
	if ( '' === $label || '' === $name ) {
		return null;
	}
	$type = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : 'text';
	$allowed_types = array( 'text', 'email', 'tel', 'textarea', 'select', 'checkbox' );
	if ( ! in_array( $type, $allowed_types, true ) ) {
		$type = 'text';
	}
	$width = isset( $row['width'] ) && 'half' === $row['width'] ? 'half' : 'full';
	return array(
		'label'       => $label,
		'name'        => $name,
		'type'        => $type,
		'required'    => ( isset( $row['required'] ) && '1' === (string) $row['required'] ) ? '1' : '0',
		'placeholder' => isset( $row['placeholder'] ) ? sanitize_text_field( wp_unslash( $row['placeholder'] ) ) : '',
		'options'     => isset( $row['options'] ) ? sanitize_textarea_field( wp_unslash( $row['options'] ) ) : '',
		'width'       => $width,
	);
}

function fairflock_form_normalize_fields_input( $fields ) {
	if ( ! is_array( $fields ) ) {
		return array();
	}
	// Spaltenformat vom Repeater: label[], name[], type[], …
	if ( isset( $fields['label'] ) && is_array( $fields['label'] ) ) {
		$out   = array();
		$count = count( $fields['label'] );
		for ( $i = 0; $i < $count; $i++ ) {
			$row = fairflock_form_sanitize_field_row(
				array(
					'label'       => $fields['label'][ $i ] ?? '',
					'name'        => $fields['name'][ $i ] ?? '',
					'type'        => $fields['type'][ $i ] ?? 'text',
					'required'    => $fields['required'][ $i ] ?? '',
					'placeholder' => $fields['placeholder'][ $i ] ?? '',
					'options'     => $fields['options'][ $i ] ?? '',
					'width'       => $fields['width'][ $i ] ?? 'full',
				)
			);
			if ( $row ) {
				$out[] = $row;
			}
		}
		return $out;
	}
	$out = array();
	foreach ( $fields as $row ) {
		$row = fairflock_form_sanitize_field_row( $row );
		if ( $row ) {
			$out[] = $row;
		}
	}
	return $out;
}

/* ============================================================
   Einträge (Custom Post Type)
   ============================================================ */

add_action( 'init', 'fairflock_form_register_entry_cpt' );
function fairflock_form_register_entry_cpt() {
	register_post_type(
		FAIRFLOCK_FORM_ENTRY_CPT,
		array(
			'labels'              => array(
				'name'          => 'Formular-Einträge',
				'singular_name' => 'Formular-Eintrag',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'supports'            => array( 'title' ),
		)
	);
}

function fairflock_form_build_entry_title( $submitted, $by_name ) {
	$parts = array();
	if ( ! empty( $by_name['name'] ) ) {
		$parts[] = $by_name['name'];
	}
	if ( ! empty( $by_name['betreff'] ) ) {
		$parts[] = $by_name['betreff'];
	}
	if ( empty( $parts ) && ! empty( $submitted ) ) {
		$parts[] = reset( $submitted );
	}
	$title = ! empty( $parts ) ? implode( ' – ', $parts ) : 'Kontaktanfrage';
	return wp_trim_words( sanitize_text_field( $title ), 12, '…' );
}

function fairflock_form_save_entry( $submitted, $by_name ) {
	$post_id = wp_insert_post(
		array(
			'post_type'   => FAIRFLOCK_FORM_ENTRY_CPT,
			'post_status' => 'publish',
			'post_title'  => fairflock_form_build_entry_title( $submitted, $by_name ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	update_post_meta( $post_id, '_ff_entry_data', $submitted );
	update_post_meta( $post_id, '_ff_entry_by_name', $by_name );
	update_post_meta( $post_id, '_ff_entry_ip', fairflock_form_client_ip() );
	update_post_meta( $post_id, '_ff_entry_mail_sent', '0' );

	return $post_id;
}

function fairflock_form_get_entries( $args = array() ) {
	$defaults = array(
		'post_type'      => FAIRFLOCK_FORM_ENTRY_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	return get_posts( wp_parse_args( $args, $defaults ) );
}

function fairflock_form_count_unmailed_entries() {
	$q = new WP_Query(
		array(
			'post_type'      => FAIRFLOCK_FORM_ENTRY_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_ff_entry_mail_sent',
					'value' => '0',
				),
			),
		)
	);
	return (int) $q->found_posts;
}

require_once FAIRFLOCK_DIR . '/inc/contact-form-mail.php';
require_once FAIRFLOCK_DIR . '/inc/contact-form-submit.php';
require_once FAIRFLOCK_DIR . '/inc/contact-form-render.php';

/**
 * Enqueues frontend form CSS/JS (and Turnstile when enabled).
 *
 * @since 0.9.260
 *
 * @return void
 */
function fairflock_form_enqueue_assets() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$opts = fairflock_form_get_opts();
	$ver  = defined( 'FAIRFLOCK_VERSION' ) ? FAIRFLOCK_VERSION : FAIRFLOCK_FORM_VERSION;

	$css = FAIRFLOCK_DIR . '/assets/css/contact-form.css';
	$js  = FAIRFLOCK_DIR . '/assets/js/contact-form.js';

	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'fairflock-contact-form',
			FAIRFLOCK_URI . '/assets/css/contact-form.css',
			array(),
			$ver
		);
	}

	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'fairflock-contact-form',
			FAIRFLOCK_URI . '/assets/js/contact-form.js',
			array(),
			$ver,
			true
		);
		wp_localize_script(
			'fairflock-contact-form',
			'fairflockForm',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'errorMessage' => $opts['error_message'],
			)
		);
	}

	if ( fairflock_form_turnstile_enabled( $opts ) ) {
		wp_enqueue_script(
			'cf-turnstile',
			'https://challenges.cloudflare.com/turnstile/v0/api.js',
			array(),
			null,
			true
		);
	}
}
