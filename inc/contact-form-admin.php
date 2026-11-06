<?php
/**
 * FairFlock contact form — admin Formular + Einträge.
 *
 * @package FairFlock
 * @since 0.9.260
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   Backend
   ============================================================ */

add_action( 'admin_menu', 'fairflock_form_admin_menu', 30 );
function fairflock_form_admin_menu() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Formular', 'fairflock' ),
		__( 'Formular', 'fairflock' ),
		'manage_options',
		'fairflock-formular',
		'fairflock_form_admin_page'
	);

	$unmailed = fairflock_form_count_unmailed_entries();
	$label    = __( 'Einträge', 'fairflock' );
	if ( $unmailed > 0 ) {
		$label .= sprintf(
			' <span class="awaiting-mod count-%1$d"><span class="pending-count">%1$d</span></span>',
			min( 99, $unmailed )
		);
	}

	add_submenu_page(
		'fairflock-setup',
		__( 'Formular-Einträge', 'fairflock' ),
		$label,
		'manage_options',
		'fairflock-form-eintraege',
		'fairflock_form_entries_page'
	);
}

add_action( 'admin_enqueue_scripts', 'fairflock_form_admin_assets' );
function fairflock_form_admin_assets( $hook ) {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( ! in_array( $page, array( 'fairflock-formular', 'fairflock-form-eintraege' ), true ) ) {
		return;
	}
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	add_action( 'admin_head', 'fairflock_form_admin_css' );
	add_action( 'admin_footer', 'fairflock_form_admin_js' );
}

function fairflock_form_admin_css() {
	?>
	<style>
	.ff-form-admin, .ff-form-admin * { box-sizing: border-box; }
	.ff-form-admin { max-width: 880px; }
	.ff-form-lead { margin: 6px 0 0; max-width: 62ch; color: #50575e; font-size: 14px; line-height: 1.55; }
	.ff-form-overview { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 18px 0 20px; }
	.ff-form-overview article { background: #fff; border: 1px solid #dcdcde; border-top: 3px solid #c3c4c7; padding: 16px 18px; min-width: 0; }
	.ff-form-overview article.is-ok { border-top-color: #377530; }
	.ff-form-overview article.is-warn { border-top-color: #dba617; }
	.ff-form-kicker { margin: 0 0 6px; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #646970; }
	.ff-form-overview h2 { margin: 0 0 6px; font-size: 16px; font-weight: 600; line-height: 1.35; color: #1d2327; overflow-wrap: anywhere; }
	.ff-form-overview p { margin: 0; color: #50575e; font-size: 13px; line-height: 1.5; }
	.ff-form-overview a { display: inline-block; margin-top: 8px; }
	.ff-form-acc { background: #fff; border: 1px solid #dcdcde; margin-bottom: 12px; }
	.ff-form-acc-summary { cursor: pointer; list-style: none; padding: 14px 18px; background: #f6f7f7; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
	.ff-form-acc-summary::-webkit-details-marker { display: none; }
	.ff-form-acc-summary::after { content: "▾"; color: #787c82; flex: none; }
	.ff-form-acc[open] .ff-form-acc-summary::after { transform: rotate(180deg); }
	.ff-form-acc-title { display: block; font-size: 15px; font-weight: 600; color: #1d2327; }
	.ff-form-acc-lead { display: block; margin-top: 2px; font-size: 13px; font-weight: 400; color: #646970; line-height: 1.4; }
	.ff-form-acc-body { padding: 18px 20px 8px; border-top: 1px solid #dcdcde; }
	.ff-form-field { margin-bottom: 16px; }
	.ff-form-field > label, .ff-form-field > .ff-form-label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px; color: #1d2327; }
	.ff-form-field input[type=text], .ff-form-field input[type=email], .ff-form-field input[type=number], .ff-form-field input[type=password], .ff-form-field select, .ff-form-field textarea { width: 100%; max-width: 100%; padding: 8px 10px; border: 1px solid #8c8f94; border-radius: 2px; font-size: 14px; line-height: 1.4; background: #fff; }
	.ff-form-field textarea { min-height: 72px; resize: vertical; }
	.ff-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 16px; }
	.ff-form-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
	.ff-form-hint { font-weight: 400; color: #646970; font-size: 12px; }
	.ff-form-note { margin: 0 0 16px; padding: 10px 12px; background: #f6f7f7; border-left: 3px solid #377530; color: #1d2327; font-size: 13px; line-height: 1.5; }
	.ff-form-check { display: flex; align-items: flex-start; gap: 8px; font-weight: 600; font-size: 14px; line-height: 1.4; }
	.ff-form-field > label.ff-form-check { display: flex; margin-bottom: 0; }
	.ff-form-check input { margin: 3px 0 0; }
	.ff-form-save-bar { position: sticky; bottom: 10px; z-index: 10; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; background: #fff; padding: 12px 16px; border: 1px solid #dcdcde; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
	.ff-form-fields { border: 1px solid #dcdcde; background: #fff; }
	.ff-form-field-row { display: grid; grid-template-columns: 32px minmax(0, 1fr) 36px; gap: 8px; align-items: start; padding: 14px 12px; border-bottom: 1px solid #e2e4e7; }
	.ff-form-field-row:last-child { border-bottom: none; }
	.ff-form-field-card { display: grid; gap: 12px; min-width: 0; }
	.ff-form-field-line { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
	.ff-form-field-line--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
	.ff-form-field-card label { display: flex; flex-direction: column; gap: 4px; min-width: 0; font-size: 12px; font-weight: 600; color: #1d2327; }
	.ff-form-field-card input, .ff-form-field-card select, .ff-form-field-card textarea { width: 100%; max-width: none; padding: 8px 10px; border: 1px solid #8c8f94; border-radius: 2px; font-size: 14px; font-weight: 400; background: #fff; }
	.ff-form-field-card textarea { min-height: 72px; resize: vertical; }
	.ff-form-field-card label.ff-form-field-options { display: none; }
	.ff-form-field-row.is-choice .ff-form-field-card label.ff-form-field-options { display: flex; }
	.ff-form-field-key { max-width: 280px; }
	.ff-form-drag { cursor: grab; color: #8c8f94; text-align: center; padding-top: 22px; font-size: 16px; line-height: 1; }
	.ff-form-remove { background: none; border: none; color: #b32d2e; cursor: pointer; font-size: 18px; line-height: 1; padding: 22px 4px 0; }
	.ff-form-add-field { margin-top: 12px; }
	@media (max-width: 782px) {
		.ff-form-overview, .ff-form-grid, .ff-form-grid--3, .ff-form-field-line, .ff-form-field-line--3 { grid-template-columns: 1fr; }
		.ff-form-field-row { grid-template-columns: 28px minmax(0, 1fr); }
		.ff-form-remove { grid-column: 2; padding-top: 0; text-align: left; }
	}
	.ff-form-entries-table { width:100%; border-collapse:collapse; background:#fff; border:1px solid #c3c4c7; }
	.ff-form-entries-table th, .ff-form-entries-table td { padding:10px 12px; text-align:left; border-bottom:1px solid #e2e4e7; font-size:13px; vertical-align:top; }
	.ff-form-entries-table th { background:#f0f0f1; font-weight:700; }
	.ff-form-entries-table tr:last-child td { border-bottom:none; }
	.ff-form-badge { display:inline-block; padding:2px 8px; font-size:11px; font-weight:700; border-radius:2px; }
	.ff-form-badge--ok { background:#d4edda; color:#155724; }
	.ff-form-badge--fail { background:#f8d7da; color:#721c24; }
	.ff-form-entry-detail { background:#fff; border:1px solid #dcdcde; padding:16px 20px; margin-top:16px; }
	.ff-form-entry-detail dl { margin:0; display:grid; grid-template-columns:160px 1fr; gap:8px 16px; }
	.ff-form-entry-detail dt { font-weight:700; color:#50575e; margin:0; }
	.ff-form-entry-detail dd { margin:0; color:#1d2327; white-space:pre-wrap; word-break:break-word; }
	.ff-form-entry-actions { display:flex; gap:8px; flex-wrap:wrap; }
	.ff-form-debug-log { background:#1d2327; color:#f0f0f1; padding:12px 14px; font-family:Menlo,Consolas,monospace; font-size:11.5px; line-height:1.5; max-height:280px; overflow:auto; white-space:pre-wrap; word-break:break-word; border:1px solid #3c434a; margin-top:10px; }
	</style>
	<?php
}

function fairflock_form_admin_js() {
	?>
	<script>
	jQuery(function($){
		function syncChoice(row){
			var type = row.find('select[name*="[type]"]').val();
			row.toggleClass('is-choice', type === 'select');
		}
		function initSortable(){
			$('#ff-form-fields-list').sortable({
				handle: '.ff-form-drag',
				placeholder: 'ff-form-field-row ui-sortable-placeholder',
				forcePlaceholderSize: true
			});
		}
		initSortable();
		$('#ff-form-fields-list .ff-form-field-row').each(function(){
			syncChoice($(this));
		});
		$(document).on('change', 'select[name*="[type]"]', function(){
			syncChoice($(this).closest('.ff-form-field-row'));
		});
		$(document).on('click', '.ff-form-add-field', function(){
			var tpl = document.getElementById('ff-form-field-template');
			if (!tpl) return;
			$('#ff-form-fields-list').append(tpl.content.cloneNode(true));
			initSortable();
		});
		$(document).on('click', '.ff-form-remove', function(){
			$(this).closest('.ff-form-field-row').remove();
		});
	});
	</script>
	<?php
}

function fairflock_form_admin_field_row( $row = array() ) {
	$row = wp_parse_args(
		$row,
		array(
			'label'       => '',
			'name'        => '',
			'type'        => 'text',
			'required'    => '0',
			'placeholder' => '',
			'options'     => '',
			'width'       => 'full',
		)
	);
	$choice = 'select' === $row['type'] ? ' is-choice' : '';
	$types  = array(
		'text'     => 'Text',
		'email'    => 'E-Mail',
		'tel'      => 'Telefon',
		'textarea' => 'Mehrzeilig',
		'select'   => 'Auswahl',
		'checkbox' => 'Checkbox',
	);
	?>
	<div class="ff-form-field-row<?php echo esc_attr( $choice ); ?>">
		<span class="ff-form-drag" title="Reihenfolge ändern">⠿</span>
		<div class="ff-form-field-card">
			<div class="ff-form-field-line">
				<label>Bezeichnung
					<input type="text" name="fairflock_form[fields][label][]" value="<?php echo esc_attr( $row['label'] ); ?>" placeholder="z. B. Name">
				</label>
				<label>Platzhalter
					<input type="text" name="fairflock_form[fields][placeholder][]" value="<?php echo esc_attr( $row['placeholder'] ); ?>" placeholder="z. B. Dein Name">
				</label>
			</div>
			<div class="ff-form-field-line ff-form-field-line--3">
				<label>Art
					<select name="fairflock_form[fields][type][]">
						<?php foreach ( $types as $val => $lbl ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $row['type'], $val ); ?>><?php echo esc_html( $lbl ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>Pflichtfeld
					<select name="fairflock_form[fields][required][]">
						<option value="0" <?php selected( $row['required'], '0' ); ?>>Nein</option>
						<option value="1" <?php selected( $row['required'], '1' ); ?>>Ja</option>
					</select>
				</label>
				<label>Breite
					<select name="fairflock_form[fields][width][]">
						<option value="full" <?php selected( $row['width'], 'full' ); ?>>Ganze Zeile</option>
						<option value="half" <?php selected( $row['width'], 'half' ); ?>>Halbe Zeile</option>
					</select>
				</label>
			</div>
			<label class="ff-form-field-options">Auswahlmöglichkeiten <span class="ff-form-hint">eine pro Zeile</span>
				<textarea name="fairflock_form[fields][options][]" rows="3" placeholder="Option 1&#10;Option 2"><?php echo esc_textarea( $row['options'] ); ?></textarea>
			</label>
			<label class="ff-form-field-key">Interner Name <span class="ff-form-hint">nur Kleinbuchstaben, z. B. email</span>
				<input type="text" name="fairflock_form[fields][name][]" value="<?php echo esc_attr( $row['name'] ); ?>" placeholder="feldname">
			</label>
		</div>
		<button type="button" class="ff-form-remove" title="Feld entfernen">✕</button>
	</div>
	<?php
}

function fairflock_form_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$opts = fairflock_form_get_opts();

	if ( isset( $_POST['fairflock_form_save'] ) && check_admin_referer( 'fairflock_form_save', 'fairflock_form_nonce' ) ) {
		$in = isset( $_POST['fairflock_form'] ) ? wp_unslash( $_POST['fairflock_form'] ) : array();

		$new = array(
			'form_title'      => sanitize_text_field( $in['form_title'] ?? '' ),
			'form_intro'      => sanitize_textarea_field( $in['form_intro'] ?? '' ),
			'submit_text'     => sanitize_text_field( $in['submit_text'] ?? '' ),
			'success_message' => sanitize_textarea_field( $in['success_message'] ?? '' ),
			'error_message'   => sanitize_textarea_field( $in['error_message'] ?? '' ),
			'recipient_email' => implode( ', ', fairflock_form_parse_recipients( $in['recipient_email'] ?? '' ) ),
			'email_subject'   => sanitize_text_field( $in['email_subject'] ?? '' ),
			'reply_to_field'  => sanitize_key( $in['reply_to_field'] ?? 'email' ),
			'confirm_enabled' => ! empty( $in['confirm_enabled'] ) ? '1' : '0',
			'confirm_subject' => sanitize_text_field( $in['confirm_subject'] ?? '' ),
			'confirm_intro'   => sanitize_textarea_field( $in['confirm_intro'] ?? '' ),
			'fields'          => fairflock_form_normalize_fields_input( $in['fields'] ?? array() ),
			'smtp_enabled'    => ! empty( $in['smtp_enabled'] ) ? '1' : '0',
			'smtp_host'       => sanitize_text_field( $in['smtp_host'] ?? '' ),
			'smtp_port'       => absint( $in['smtp_port'] ?? 587 ),
			'smtp_encryption' => in_array( $in['smtp_encryption'] ?? '', array( 'tls', 'ssl', 'none' ), true ) ? $in['smtp_encryption'] : 'tls',
			'smtp_auth'       => ! empty( $in['smtp_auth'] ) ? '1' : '0',
			'smtp_user'       => sanitize_text_field( $in['smtp_user'] ?? '' ),
			'smtp_from_email' => sanitize_email( $in['smtp_from_email'] ?? '' ),
			'smtp_from_name'  => sanitize_text_field( $in['smtp_from_name'] ?? '' ),
			'privacy_enabled' => ! empty( $in['privacy_enabled'] ) ? '1' : '0',
			'privacy_text'    => sanitize_textarea_field( $in['privacy_text'] ?? '' ),
			'privacy_url'     => sanitize_text_field( $in['privacy_url'] ?? '' ),
			'rate_limit_count' => max( 1, absint( $in['rate_limit_count'] ?? 5 ) ),
			'rate_limit_minutes' => max( 1, absint( $in['rate_limit_minutes'] ?? 60 ) ),
			'cf_turnstile_enabled'  => ! empty( $in['cf_turnstile_enabled'] ) ? '1' : '0',
			'cf_turnstile_site_key' => sanitize_text_field( $in['cf_turnstile_site_key'] ?? '' ),
		);

		if ( ! empty( $in['smtp_pass'] ) ) {
			$new['smtp_pass'] = $in['smtp_pass'];
		} else {
			$new['smtp_pass'] = $opts['smtp_pass'];
		}

		if ( ! empty( $in['cf_turnstile_secret_key'] ) ) {
			$new['cf_turnstile_secret_key'] = sanitize_text_field( $in['cf_turnstile_secret_key'] );
		} else {
			$new['cf_turnstile_secret_key'] = $opts['cf_turnstile_secret_key'] ?? '';
		}

		if ( empty( $new['fields'] ) ) {
			$new['fields'] = fairflock_form_defaults()['fields'];
		}

		update_option( FAIRFLOCK_FORM_OPT, $new );
		$opts = $new;
		echo '<div class="notice notice-success is-dismissible"><p>Einstellungen gespeichert.</p></div>';
	}

	if ( isset( $_POST['fairflock_form_test_mail'] ) && check_admin_referer( 'fairflock_form_save', 'fairflock_form_nonce' ) ) {
		$result = fairflock_form_run_smtp_test( $opts );
		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>SMTP-Test fehlgeschlagen: ' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			$profile = $result['profile'];
			echo '<div class="notice notice-success is-dismissible"><p>Test erfolgreich an ' . esc_html( implode( ', ', $result['to'] ) ) . ' gesendet. Verbindung: ' . esc_html( $profile['label'] . ' – Port ' . $profile['port'] . ' / ' . strtoupper( $profile['encryption'] ) ) . '.</p></div>';
		}
	}
	$last_mail_err     = get_option( FAIRFLOCK_FORM_MAIL_ERR_OPT );
	$last_mail_success = get_option( FAIRFLOCK_FORM_MAIL_SUCCESS_OPT );
	$smtp_debug        = get_option( FAIRFLOCK_FORM_SMTP_DEBUG_OPT );
	$smtp_on           = ! empty( $opts['smtp_enabled'] ) && '1' === $opts['smtp_enabled'] && ! empty( $opts['smtp_host'] );
	$entries_url       = admin_url( 'admin.php?page=fairflock-form-eintraege' );
	$field_count       = is_array( $opts['fields'] ) ? count( $opts['fields'] ) : 0;
	$recipients        = fairflock_form_parse_recipients( $opts['recipient_email'] ?? '' );
	$unmailed          = fairflock_form_count_unmailed_entries();
	?>
	<div class="wrap ff-form-admin">
		<h1>Formular</h1>
		<p class="ff-form-lead">Hier stellst du das Kontaktformular ein: was Besucher sehen, welche Felder es gibt und wohin die Nachricht geht. Jede Einsendung bleibt unter Eingegangene Nachrichten gespeichert.</p>
		<div class="ff-form-overview">
			<article class="<?php echo $smtp_on ? 'is-ok' : 'is-warn'; ?>">
				<p class="ff-form-kicker">Versand</p>
				<?php if ( $smtp_on ) : ?>
					<h2>E-Mails gehen raus</h2>
					<p>Über <?php echo esc_html( $opts['smtp_host'] ); ?>. Absender: <?php echo esc_html( $opts['smtp_from_email'] ?: get_option( 'admin_email' ) ); ?>.</p>
				<?php else : ?>
					<h2>E-Mails gehen noch nicht raus</h2>
					<p>Nachrichten werden gespeichert. Für den Versand unten „E-Mail-Versand“ ausfüllen und eine Test-Mail schicken.</p>
				<?php endif; ?>
			</article>
			<article class="<?php echo $recipients ? 'is-ok' : 'is-warn'; ?>">
				<p class="ff-form-kicker">Empfänger</p>
				<?php if ( $recipients ) : ?>
					<h2><?php echo esc_html( implode( ', ', $recipients ) ); ?></h2>
					<p>An diese Adressen geht jede neue Nachricht.</p>
				<?php else : ?>
					<h2>Noch keine Adresse</h2>
					<p>Trag unten ein, wer die Nachrichten bekommen soll.</p>
				<?php endif; ?>
			</article>
			<article class="is-ok">
				<p class="ff-form-kicker">Nachrichten</p>
				<h2><?php echo esc_html( sprintf( _n( '%d Feld im Formular', '%d Felder im Formular', $field_count, 'fairflock' ), $field_count ) ); ?></h2>
				<p><?php echo $unmailed > 0 ? esc_html( sprintf( _n( '%d Nachricht noch nicht per E-Mail rausgegangen.', '%d Nachrichten noch nicht per E-Mail rausgegangen.', $unmailed, 'fairflock' ), $unmailed ) ) : 'Alle gespeicherten Nachrichten wurden verschickt oder es gibt noch keine.'; ?></p>
				<a href="<?php echo esc_url( $entries_url ); ?>">Eingegangene Nachrichten ansehen</a>
			</article>
		</div>
		<?php if ( is_array( $last_mail_success ) && ! empty( $last_mail_success['time'] ) ) : ?>
			<div class="notice notice-success"><p><strong>Letzter erfolgreicher Versand</strong> (<?php echo esc_html( wp_date( 'd.m.Y H:i', $last_mail_success['time'] ) ); ?>): an <?php echo esc_html( $last_mail_success['to'] ?? '' ); ?> via <?php echo esc_html( $last_mail_success['method'] ?? '' ); ?>.</p></div>
		<?php endif; ?>
		<?php if ( is_array( $last_mail_err ) && ! empty( $last_mail_err['message'] ) ) : ?>
			<div class="notice notice-warning"><p><strong>Letzter Mail-Fehler</strong> (<?php echo esc_html( wp_date( 'd.m.Y H:i', $last_mail_err['time'] ?? time() ) ); ?>): <?php echo esc_html( $last_mail_err['message'] ); ?></p></div>
		<?php endif; ?>
		<?php if ( is_array( $smtp_debug ) && ! empty( $smtp_debug['log'] ) ) : ?>
			<details class="ff-form-acc" style="margin-bottom:16px;" open>
				<summary class="ff-form-acc-summary">SMTP-Debug vom letzten Test (<?php echo esc_html( wp_date( 'd.m.Y H:i', $smtp_debug['time'] ?? time() ) ); ?>)</summary>
				<div class="ff-form-acc-body">
					<pre class="ff-form-debug-log"><?php echo esc_html( $smtp_debug['log'] ); ?></pre>
				</div>
			</details>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'fairflock_form_save', 'fairflock_form_nonce' ); ?>
			<div class="ff-form-save-bar">
				<button type="submit" name="fairflock_form_save" value="1" class="button button-primary">Änderungen speichern</button>
				<button type="submit" name="fairflock_form_test_mail" value="1" class="button">Test-E-Mail senden</button>
			</div>

			<details class="ff-form-acc" open>
				<summary class="ff-form-acc-summary">
					<span>
						<span class="ff-form-acc-title">Texte auf der Seite</span>
						<span class="ff-form-acc-lead">Überschrift, Einleitung und was nach dem Absenden steht.</span>
					</span>
				</summary>
				<div class="ff-form-acc-body">
					<div class="ff-form-grid">
						<div class="ff-form-field"><label>Überschrift</label><input type="text" name="fairflock_form[form_title]" value="<?php echo esc_attr( $opts['form_title'] ); ?>"></div>
						<div class="ff-form-field"><label>Text auf dem Button</label><input type="text" name="fairflock_form[submit_text]" value="<?php echo esc_attr( $opts['submit_text'] ); ?>"></div>
					</div>
					<div class="ff-form-field"><label>Einleitung</label><textarea name="fairflock_form[form_intro]" rows="3"><?php echo esc_textarea( $opts['form_intro'] ); ?></textarea></div>
					<div class="ff-form-grid">
						<div class="ff-form-field"><label>Meldung bei Erfolg</label><textarea name="fairflock_form[success_message]" rows="3"><?php echo esc_textarea( $opts['success_message'] ); ?></textarea></div>
						<div class="ff-form-field"><label>Meldung bei einem Fehler</label><textarea name="fairflock_form[error_message]" rows="3"><?php echo esc_textarea( $opts['error_message'] ); ?></textarea></div>
					</div>
				</div>
			</details>

			<details class="ff-form-acc" open>
				<summary class="ff-form-acc-summary">
					<span>
						<span class="ff-form-acc-title">Felder</span>
						<span class="ff-form-acc-lead">Am Griff links ziehen, um die Reihenfolge zu ändern. Auswahlmöglichkeiten erscheinen nur bei der Art „Auswahl“.</span>
					</span>
				</summary>
				<div class="ff-form-acc-body">
					<div class="ff-form-fields" id="ff-form-fields-list">
						<?php foreach ( $opts['fields'] as $field ) : ?>
							<?php fairflock_form_admin_field_row( $field ); ?>
						<?php endforeach; ?>
					</div>
					<template id="ff-form-field-template">
						<?php fairflock_form_admin_field_row(); ?>
					</template>
					<p><button type="button" class="button ff-form-add-field">Feld hinzufügen</button></p>
				</div>
			</details>

			<details class="ff-form-acc">
				<summary class="ff-form-acc-summary">
					<span>
						<span class="ff-form-acc-title">Datenschutz</span>
						<span class="ff-form-acc-lead">Pflicht-Haken und wie oft jemand das Formular absenden darf.</span>
					</span>
				</summary>
				<div class="ff-form-acc-body">
					<div class="ff-form-field">
						<label class="ff-form-check"><input type="checkbox" name="fairflock_form[privacy_enabled]" value="1" <?php checked( $opts['privacy_enabled'] ?? '1', '1' ); ?>> Besucher müssen den Datenschutz bestätigen</label>
					</div>
					<div class="ff-form-field"><label>Text neben dem Haken</label><textarea name="fairflock_form[privacy_text]" rows="2"><?php echo esc_textarea( $opts['privacy_text'] ?? '' ); ?></textarea></div>
					<div class="ff-form-field"><label>Link zur Datenschutzerklärung <span class="ff-form-hint">z. B. /datenschutz</span></label><input type="text" name="fairflock_form[privacy_url]" value="<?php echo esc_attr( $opts['privacy_url'] ?? '' ); ?>"></div>
					<div class="ff-form-grid">
						<div class="ff-form-field"><label>Höchstens so viele Absendungen pro Besucher</label><input type="number" min="1" name="fairflock_form[rate_limit_count]" value="<?php echo esc_attr( $opts['rate_limit_count'] ?? 5 ); ?>"></div>
						<div class="ff-form-field"><label>In diesem Zeitraum (Minuten)</label><input type="number" min="1" name="fairflock_form[rate_limit_minutes]" value="<?php echo esc_attr( $opts['rate_limit_minutes'] ?? 60 ); ?>"></div>
					</div>
				</div>
			</details>

			<details class="ff-form-acc">
				<summary class="ff-form-acc-summary">
					<span>
						<span class="ff-form-acc-title">Extra Spam-Schutz</span>
						<span class="ff-form-acc-lead">Optional. Nur nötig, wenn ihr Cloudflare Turnstile nutzt.</span>
					</span>
				</summary>
				<div class="ff-form-acc-body">
					<p class="ff-form-note">Die Schlüssel legt ihr in der <a href="https://dash.cloudflare.com/" target="_blank" rel="noopener noreferrer">Cloudflare-Konsole</a> unter Turnstile an. Bis die Domain online ist, kann der Schutz aus bleiben.</p>
					<div class="ff-form-field">
						<label class="ff-form-check"><input type="checkbox" name="fairflock_form[cf_turnstile_enabled]" value="1" <?php checked( $opts['cf_turnstile_enabled'] ?? '0', '1' ); ?>> Spam-Schutz aktivieren</label>
					</div>
					<div class="ff-form-field"><label>Öffentlicher Schlüssel (Site Key)</label><input type="text" name="fairflock_form[cf_turnstile_site_key]" value="<?php echo esc_attr( $opts['cf_turnstile_site_key'] ?? '' ); ?>" placeholder="0x4AAAAAAA…"></div>
					<div class="ff-form-field"><label>Geheimer Schlüssel <span class="ff-form-hint">leer lassen, wenn er schon gespeichert ist</span></label><input type="password" name="fairflock_form[cf_turnstile_secret_key]" value="" autocomplete="new-password" placeholder="<?php echo ! empty( $opts['cf_turnstile_secret_key'] ) ? '••••••••' : ''; ?>"></div>
				</div>
			</details>

			<details class="ff-form-acc">
				<summary class="ff-form-acc-summary">
					<span>
						<span class="ff-form-acc-title">Wohin die Nachricht geht</span>
						<span class="ff-form-acc-lead">Eure Adresse und die Bestätigung, die der Absender bekommt.</span>
					</span>
				</summary>
				<div class="ff-form-acc-body">
					<div class="ff-form-field"><label>Eure E-Mail-Adressen <span class="ff-form-hint">mehrere mit Komma trennen</span></label><input type="text" name="fairflock_form[recipient_email]" value="<?php echo esc_attr( $opts['recipient_email'] ); ?>"></div>
					<div class="ff-form-grid">
						<div class="ff-form-field"><label>Betreff der Mail an euch</label><input type="text" name="fairflock_form[email_subject]" value="<?php echo esc_attr( $opts['email_subject'] ); ?>"></div>
						<div class="ff-form-field"><label>Feld mit der Antwort-Adresse <span class="ff-form-hint">meist email</span></label><input type="text" name="fairflock_form[reply_to_field]" value="<?php echo esc_attr( $opts['reply_to_field'] ); ?>"></div>
					</div>
					<div class="ff-form-field">
						<label class="ff-form-check"><input type="checkbox" name="fairflock_form[confirm_enabled]" value="1" <?php checked( $opts['confirm_enabled'] ?? '1', '1' ); ?>> Dem Absender eine Bestätigung schicken</label>
						<p class="ff-form-hint" style="margin:6px 0 0;">Geht an die E-Mail aus dem Formular und fasst die Angaben kurz zusammen.</p>
					</div>
					<div class="ff-form-field"><label>Betreff der Bestätigung</label><input type="text" name="fairflock_form[confirm_subject]" value="<?php echo esc_attr( $opts['confirm_subject'] ?? '' ); ?>"></div>
					<div class="ff-form-field"><label>Text der Bestätigung <span class="ff-form-hint">steht nach „Hallo …,“</span></label><textarea name="fairflock_form[confirm_intro]" rows="4"><?php echo esc_textarea( $opts['confirm_intro'] ?? '' ); ?></textarea></div>
				</div>
			</details>

			<details class="ff-form-acc" <?php echo $smtp_on ? '' : 'open'; ?>>
				<summary class="ff-form-acc-summary">
					<span>
						<span class="ff-form-acc-title">E-Mail-Versand</span>
						<span class="ff-form-acc-lead">Ohne diese Angaben kommen die Mails auf den meisten Servern nicht an.</span>
					</span>
				</summary>
				<div class="ff-form-acc-body">
					<p class="ff-form-note">Bei Mittwald / mStudio meist: Server <strong>mail.agenturserver.de</strong>, Port <strong>465</strong>, Verschlüsselung <strong>SSL</strong>. Benutzername ist die ganze E-Mail-Adresse, Passwort das vom Postfach.</p>
					<div class="ff-form-field">
						<label class="ff-form-check"><input type="checkbox" name="fairflock_form[smtp_enabled]" value="1" <?php checked( $opts['smtp_enabled'], '1' ); ?>> Versand über den Mailserver aktivieren</label>
					</div>
					<div class="ff-form-grid ff-form-grid--3">
						<div class="ff-form-field"><label>Server</label><input type="text" name="fairflock_form[smtp_host]" value="<?php echo esc_attr( $opts['smtp_host'] ); ?>" placeholder="mail.agenturserver.de"></div>
						<div class="ff-form-field"><label>Port</label><input type="number" name="fairflock_form[smtp_port]" value="<?php echo esc_attr( $opts['smtp_port'] ); ?>" placeholder="465"></div>
						<div class="ff-form-field">
							<label>Verschlüsselung</label>
							<select name="fairflock_form[smtp_encryption]">
								<option value="tls" <?php selected( $opts['smtp_encryption'], 'tls' ); ?>>TLS (meist Port 587)</option>
								<option value="ssl" <?php selected( $opts['smtp_encryption'], 'ssl' ); ?>>SSL (meist Port 465)</option>
								<option value="none" <?php selected( $opts['smtp_encryption'], 'none' ); ?>>Keine</option>
							</select>
						</div>
					</div>
					<div class="ff-form-field">
						<label class="ff-form-check"><input type="checkbox" name="fairflock_form[smtp_auth]" value="1" <?php checked( $opts['smtp_auth'], '1' ); ?>> Mit Benutzername und Passwort anmelden</label>
					</div>
					<div class="ff-form-grid">
						<div class="ff-form-field"><label>Benutzername <span class="ff-form-hint">meist die ganze E-Mail-Adresse</span></label><input type="text" name="fairflock_form[smtp_user]" value="<?php echo esc_attr( $opts['smtp_user'] ); ?>" autocomplete="off"></div>
						<div class="ff-form-field"><label>Passwort <span class="ff-form-hint">leer lassen, wenn es schon gespeichert ist</span></label><input type="password" name="fairflock_form[smtp_pass]" value="" autocomplete="new-password"></div>
						<div class="ff-form-field"><label>Absender-Adresse <span class="ff-form-hint">muss zum Postfach passen</span></label><input type="email" name="fairflock_form[smtp_from_email]" value="<?php echo esc_attr( $opts['smtp_from_email'] ); ?>"></div>
						<div class="ff-form-field"><label>Absender-Name</label><input type="text" name="fairflock_form[smtp_from_name]" value="<?php echo esc_attr( $opts['smtp_from_name'] ); ?>"></div>
					</div>
				</div>
			</details>

			<div class="ff-form-save-bar">
				<button type="submit" name="fairflock_form_save" value="1" class="button button-primary">Änderungen speichern</button>
				<button type="submit" name="fairflock_form_test_mail" value="1" class="button">Test-E-Mail senden</button>
			</div>
		</form>
	</div>
	<?php
}

function fairflock_form_entries_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$view_id = isset( $_GET['entry'] ) ? absint( $_GET['entry'] ) : 0;

	if ( isset( $_GET['action'], $_GET['entry'], $_GET['_wpnonce'] ) && 'delete' === $_GET['action'] ) {
		$del_id = absint( $_GET['entry'] );
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'fairflock_delete_entry_' . $del_id ) ) {
			wp_delete_post( $del_id, true );
			echo '<div class="notice notice-success is-dismissible"><p>Eintrag gelöscht.</p></div>';
			if ( $view_id === $del_id ) {
				$view_id = 0;
			}
		}
	}

	if ( isset( $_GET['action'], $_GET['entry'], $_GET['_wpnonce'] ) && 'resend' === $_GET['action'] ) {
		$resend_id = absint( $_GET['entry'] );
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'fairflock_resend_entry_' . $resend_id ) ) {
			$resend_post = get_post( $resend_id );
			if ( $resend_post && FAIRFLOCK_FORM_ENTRY_CPT === $resend_post->post_type ) {
				$resend_data    = get_post_meta( $resend_id, '_ff_entry_data', true );
				$resend_by_name = get_post_meta( $resend_id, '_ff_entry_by_name', true );
				$resend_opts    = fairflock_form_get_opts();
				if ( is_array( $resend_data ) ) {
					$by = is_array( $resend_by_name ) ? $resend_by_name : array();
					$resend_result = fairflock_form_send_mail( $resend_data, $resend_opts, $by );
					if ( is_wp_error( $resend_result ) ) {
						update_post_meta( $resend_id, '_ff_entry_mail_sent', '0' );
						update_post_meta( $resend_id, '_ff_entry_mail_error', $resend_result->get_error_message() );
						echo '<div class="notice notice-error is-dismissible"><p>E-Mail an euch erneut fehlgeschlagen: ' . esc_html( $resend_result->get_error_message() ) . '</p></div>';
					} else {
						update_post_meta( $resend_id, '_ff_entry_mail_sent', '1' );
						delete_post_meta( $resend_id, '_ff_entry_mail_error' );
						echo '<div class="notice notice-success is-dismissible"><p>E-Mail an euch wurde erneut gesendet.</p></div>';
					}
					$confirm_result = fairflock_form_send_confirmation( $resend_data, $resend_opts, $by );
					if ( is_wp_error( $confirm_result ) ) {
						update_post_meta( $resend_id, '_ff_entry_confirm_sent', '0' );
						update_post_meta( $resend_id, '_ff_entry_confirm_error', $confirm_result->get_error_message() );
						echo '<div class="notice notice-warning is-dismissible"><p>Bestätigung an Kunden fehlgeschlagen: ' . esc_html( $confirm_result->get_error_message() ) . '</p></div>';
					} else {
						update_post_meta( $resend_id, '_ff_entry_confirm_sent', '1' );
						delete_post_meta( $resend_id, '_ff_entry_confirm_error' );
						echo '<div class="notice notice-success is-dismissible"><p>Bestätigung an den Kunden wurde erneut gesendet.</p></div>';
					}
					$view_id = $resend_id;
				}
			}
		}
	}

	if ( $view_id ) {
		$post = get_post( $view_id );
		if ( ! $post || FAIRFLOCK_FORM_ENTRY_CPT !== $post->post_type ) {
			echo '<div class="notice notice-error"><p>Eintrag nicht gefunden.</p></div>';
			$view_id = 0;
		}
	}

	$entries = fairflock_form_get_entries();
	?>
	<div class="wrap ff-form-admin">
		<h1>Einträge</h1>
		<p class="ff-form-admin-intro">Alle Formular-Einsendungen werden hier gespeichert – auch wenn der E-Mail-Versand fehlschlägt. Prüfe regelmäßig Einträge mit dem Status <strong>E-Mail fehlgeschlagen</strong>.</p>

		<?php if ( $view_id && ! empty( $post ) ) : ?>
			<?php
			$data       = get_post_meta( $view_id, '_ff_entry_data', true );
			$ip         = get_post_meta( $view_id, '_ff_entry_ip', true );
			$mail_sent  = get_post_meta( $view_id, '_ff_entry_mail_sent', true );
			$mail_error = get_post_meta( $view_id, '_ff_entry_mail_error', true );
			$confirm_sent  = get_post_meta( $view_id, '_ff_entry_confirm_sent', true );
			$confirm_error = get_post_meta( $view_id, '_ff_entry_confirm_error', true );
			?>
			<p class="ff-form-entry-actions">
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=fairflock-form-eintraege' ) ); ?>">&larr; Zurück zur Liste</a>
				<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=fairflock-form-eintraege&action=resend&entry=' . $view_id ), 'fairflock_resend_entry_' . $view_id ) ); ?>">E-Mails erneut senden</a>
				<a class="button button-link-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=fairflock-form-eintraege&action=delete&entry=' . $view_id ), 'fairflock_delete_entry_' . $view_id ) ); ?>" onclick="return confirm('Eintrag wirklich löschen?');">Löschen</a>
			</p>
			<div class="ff-form-entry-detail">
				<p style="margin:0 0 12px;">
					<strong><?php echo esc_html( get_the_date( 'd.m.Y, H:i', $post ) ); ?></strong>
					<?php if ( '1' === $mail_sent ) : ?>
						<span class="ff-form-badge ff-form-badge--ok">An euch gesendet</span>
					<?php else : ?>
						<span class="ff-form-badge ff-form-badge--fail">An euch fehlgeschlagen</span>
					<?php endif; ?>
					<?php if ( '1' === $confirm_sent ) : ?>
						<span class="ff-form-badge ff-form-badge--ok">Bestätigung an Kunden</span>
					<?php elseif ( '' !== $confirm_sent ) : ?>
						<span class="ff-form-badge ff-form-badge--fail">Bestätigung fehlgeschlagen</span>
					<?php endif; ?>
				</p>
				<?php if ( $mail_error ) : ?>
					<p style="margin:0 0 8px;color:#b32d2e;font-size:13px;"><strong>Mail-Fehler (an euch):</strong> <?php echo esc_html( $mail_error ); ?></p>
				<?php endif; ?>
				<?php if ( $confirm_error ) : ?>
					<p style="margin:0 0 16px;color:#b32d2e;font-size:13px;"><strong>Bestätigung (Kunde):</strong> <?php echo esc_html( $confirm_error ); ?></p>
				<?php endif; ?>
				<dl>
					<?php if ( is_array( $data ) ) : ?>
						<?php foreach ( $data as $label => $value ) : ?>
							<dt><?php echo esc_html( $label ); ?></dt>
							<dd><?php echo esc_html( $value ); ?></dd>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( $ip ) : ?>
						<dt>IP-Adresse</dt>
						<dd><?php echo esc_html( $ip ); ?></dd>
					<?php endif; ?>
				</dl>
			</div>
		<?php else : ?>
			<?php if ( empty( $entries ) ) : ?>
				<p>Noch keine Einträge vorhanden.</p>
			<?php else : ?>
				<table class="ff-form-entries-table">
					<thead>
						<tr>
							<th>Datum</th>
							<th>Eintrag</th>
							<th>E-Mail</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $entries as $entry ) : ?>
							<?php
							$data      = get_post_meta( $entry->ID, '_ff_entry_data', true );
							$mail_sent = get_post_meta( $entry->ID, '_ff_entry_mail_sent', true );
							$preview   = '';
							if ( is_array( $data ) ) {
								$preview = wp_trim_words( implode( ' · ', array_map( 'strval', $data ) ), 14, '…' );
							}
							?>
							<tr>
								<td><?php echo esc_html( get_the_date( 'd.m.Y, H:i', $entry ) ); ?></td>
								<td>
									<strong><?php echo esc_html( $entry->post_title ); ?></strong>
									<?php if ( $preview ) : ?>
										<br><span style="color:#646970;font-size:12px;"><?php echo esc_html( $preview ); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<?php if ( '1' === $mail_sent ) : ?>
										<span class="ff-form-badge ff-form-badge--ok">Gesendet</span>
									<?php else : ?>
										<span class="ff-form-badge ff-form-badge--fail">Fehlgeschlagen</span>
									<?php endif; ?>
								</td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=fairflock-form-eintraege&entry=' . $entry->ID ) ); ?>">Ansehen</a>
									|
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=fairflock-form-eintraege&action=delete&entry=' . $entry->ID ), 'fairflock_delete_entry_' . $entry->ID ) ); ?>" style="color:#b32d2e;" onclick="return confirm('Eintrag wirklich löschen?');">Löschen</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
