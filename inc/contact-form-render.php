<?php
/**
 * FairFlock contact form — frontend markup.
 *
 * @package FairFlock
 * @since 0.9.260
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   Frontend Shortcode
   ============================================================ */

add_shortcode( 'fairflock_contact_form', 'fairflock_form_shortcode' );



function fairflock_form_shortcode() {
	fairflock_form_enqueue_assets();
	$opts = fairflock_form_get_opts();
	$id   = 'ff-form-' . wp_unique_id();

	ob_start();
	?>
	<div class="ff-form-wrap" id="<?php echo esc_attr( $id ); ?>">
		<?php if ( ! empty( $opts['form_title'] ) ) : ?>
			<h2 class="ff-form__title"><?php echo esc_html( $opts['form_title'] ); ?></h2>
		<?php endif; ?>
		<?php if ( ! empty( $opts['form_intro'] ) ) : ?>
			<p class="ff-form__intro"><?php echo nl2br( esc_html( $opts['form_intro'] ) ); ?></p>
		<?php endif; ?>

		<form class="ff-form" method="post" novalidate>
			<?php wp_nonce_field( 'fairflock_form_submit', 'fairflock_form_nonce', false ); ?>
			<input type="text" name="ff_form_website" class="ff-form__hp" tabindex="-1" autocomplete="off" aria-hidden="true">

			<div class="ff-form__grid">
				<?php foreach ( $opts['fields'] as $field ) : ?>
					<?php fairflock_form_render_field( $field ); ?>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $opts['privacy_enabled'] ) && '1' === $opts['privacy_enabled'] ) : ?>
				<?php fairflock_form_render_privacy_field( $opts ); ?>
			<?php endif; ?>

			<?php if ( fairflock_form_turnstile_enabled( $opts ) ) : ?>
				<div class="ff-form__field ff-form__field--full ff-form__turnstile">
					<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $opts['cf_turnstile_site_key'] ); ?>" data-theme="auto"></div>
				</div>
			<?php endif; ?>

			<div class="ff-form__actions">
				<button type="submit" class="ff-form__submit">
					<span class="ff-form__submit-text"><?php echo esc_html( $opts['submit_text'] ); ?></span>
					<span class="ff-form__submit-loading" aria-hidden="true">Wird gesendet …</span>
				</button>
			</div>

			<div class="ff-form__notice ff-form__notice--success" role="status" aria-live="polite" hidden></div>
			<div class="ff-form__notice ff-form__notice--error" role="alert" aria-live="assertive" hidden></div>
		</form>
	</div>


	<?php
	return ob_get_clean();
}

function fairflock_form_render_field( $field ) {
	$key      = esc_attr( $field['name'] );
	$label    = esc_html( $field['label'] );
	$required = '1' === $field['required'];
	$width    = 'half' === $field['width'] ? 'half' : 'full';
	$ph       = esc_attr( $field['placeholder'] );
	$req_attr = $required ? ' required aria-required="true"' : '';

	echo '<div class="ff-form__field ff-form__field--' . esc_attr( $width ) . '">';

	if ( 'checkbox' === $field['type'] ) {
		echo '<div class="ff-form__checkbox-wrap">';
		echo '<input type="checkbox" class="ff-form__checkbox" id="' . $key . '" name="' . $key . '" value="1"' . $req_attr . '>';
		echo '<label class="ff-form__checkbox-label" for="' . $key . '">' . $label;
		if ( $required ) {
			echo '<span class="ff-form__required">*</span>';
		}
		echo '</label></div>';
		echo '</div>';
		return;
	}

	echo '<label class="ff-form__label" for="' . $key . '">' . $label;
	if ( $required ) {
		echo '<span class="ff-form__required">*</span>';
	}
	echo '</label>';

	switch ( $field['type'] ) {
		case 'textarea':
			echo '<textarea class="ff-form__textarea" id="' . $key . '" name="' . $key . '" placeholder="' . $ph . '"' . $req_attr . '></textarea>';
			break;
		case 'select':
			echo '<select class="ff-form__select" id="' . $key . '" name="' . $key . '"' . $req_attr . '>';
			echo '<option value="">' . esc_html( $field['placeholder'] ?: 'Bitte wählen …' ) . '</option>';
			foreach ( fairflock_form_parse_options( $field['options'] ) as $opt ) {
				echo '<option value="' . esc_attr( $opt ) . '">' . esc_html( $opt ) . '</option>';
			}
			echo '</select>';
			break;
		case 'email':
			echo '<input type="email" class="ff-form__input" id="' . $key . '" name="' . $key . '" placeholder="' . $ph . '" autocomplete="email"' . $req_attr . '>';
			break;
		case 'tel':
			echo '<input type="tel" class="ff-form__input" id="' . $key . '" name="' . $key . '" placeholder="' . $ph . '" autocomplete="tel"' . $req_attr . '>';
			break;
		default:
			echo '<input type="text" class="ff-form__input" id="' . $key . '" name="' . $key . '" placeholder="' . $ph . '"' . $req_attr . '>';
			break;
	}

	echo '</div>';
}

function fairflock_form_render_privacy_field( $opts ) {
	$text = ! empty( $opts['privacy_text'] ) ? $opts['privacy_text'] : fairflock_form_defaults()['privacy_text'];
	$url  = ! empty( $opts['privacy_url'] ) ? $opts['privacy_url'] : '/datenschutz';

	if ( 0 !== strpos( $url, 'http' ) ) {
		$url = home_url( '/' . ltrim( $url, '/' ) );
	}

	$link = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">Datenschutzerklärung</a>';
	$label_html = esc_html( $text );
	if ( false !== stripos( $text, 'Datenschutzerklärung' ) ) {
		$label_html = wp_kses(
			preg_replace( '/Datenschutzerklärung/i', $link, esc_html( $text ), 1 ),
			array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) )
		);
	} else {
		$label_html = esc_html( $text ) . ' ' . wp_kses( $link, array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) );
	}

	echo '<div class="ff-form__field ff-form__field--full ff-form__privacy">';
	echo '<div class="ff-form__checkbox-wrap">';
	echo '<input type="checkbox" class="ff-form__checkbox" id="ff_form_privacy_consent" name="ff_form_privacy_consent" value="1" required aria-required="true">';
	echo '<label class="ff-form__checkbox-label" for="ff_form_privacy_consent">' . $label_html;
	echo '<span class="ff-form__required">*</span>';
	echo '</label></div></div>';
}

/* ============================================================
