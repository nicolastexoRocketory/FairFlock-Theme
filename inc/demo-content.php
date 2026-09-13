<?php
/**
 * Opt-in Demo Content / one-click layouts (admin only).
 *
 * Never runs automatically on theme updates — editors choose packages explicitly.
 *
 * @package FairFlock
 * @since 0.9.154
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers FairFlock admin parent + Demo Content screen.
 *
 * @since 0.9.154
 *
 * @return void
 */
function fairflock_demo_content_register_admin() {
	add_menu_page(
		__( 'FairFlock', 'fairflock' ),
		__( 'FairFlock', 'fairflock' ),
		'edit_theme_options',
		'fairflock-demo-content',
		'fairflock_demo_content_render_admin_page',
		'dashicons-store',
		58
	);

	add_submenu_page(
		'fairflock-demo-content',
		__( 'Demo Content', 'fairflock' ),
		__( 'Demo Content', 'fairflock' ),
		'edit_theme_options',
		'fairflock-demo-content',
		'fairflock_demo_content_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_demo_content_register_admin' );

/**
 * Available demo packages.
 *
 * @since 0.9.154
 *
 * @return array<string, array{label:string,description:string,risk:string}>
 */
function fairflock_demo_content_packages() {
	return array(
		'front_page'    => array(
			'label'       => __( 'Volle Startseite', 'fairflock' ),
			'description' => __( 'Legt die Startseite an bzw. befüllt sie mit dem kompletten FairFlock-Layout: Produkte, Kollektionen, Bento, Brand-Band, Media-Split, Steps, Zitat, Reviews, FAQ und CTA — wie in der lokalen Referenzinstallation (Hero + Trust kommen aus dem Template).', 'fairflock' ),
			'risk'        => __( 'Überschreibt den Inhalt der Startseite, wenn vorhanden.', 'fairflock' ),
		),
		'legal_pages'   => array(
			'label'       => __( 'Rechtliche Seiten', 'fairflock' ),
			'description' => __( 'Erstellt fehlende Platzhalter: Impressum, Datenschutz, AGB, Widerruf, Versand & Zahlung. Vorhandene Seiten bleiben unverändert.', 'fairflock' ),
			'risk'        => __( 'Nur fehlende Seiten — kein Überschreiben.', 'fairflock' ),
		),
		'category_page' => array(
			'label'       => __( 'Seite „Kategorien“', 'fairflock' ),
			'description' => __( 'Stellt sicher, dass die Seite /kategorien/ existiert und die Theme-Vorlage für die Kategorie-Übersicht nutzt.', 'fairflock' ),
			'risk'        => __( 'Legt die Seite nur an, wenn sie fehlt; Inhalt wird für die Live-Vorlage geleert.', 'fairflock' ),
		),
	);
}

/**
 * Handles Demo Content form submit.
 *
 * @since 0.9.154
 *
 * @return void
 */
function fairflock_demo_content_handle_submit() {
	if ( ! isset( $_POST['fairflock_demo_content_run'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	check_admin_referer( 'fairflock_demo_content' );

	$packages = fairflock_demo_content_packages();
	$selected = isset( $_POST['fairflock_demo_packages'] ) && is_array( $_POST['fairflock_demo_packages'] )
		? array_map( 'sanitize_key', wp_unslash( $_POST['fairflock_demo_packages'] ) )
		: array();

	$selected = array_values( array_intersect( array_keys( $packages ), $selected ) );
	if ( empty( $selected ) ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'fairflock-demo-content',
					'ff_demo' => 'empty',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	$overwrite = ! empty( $_POST['fairflock_demo_overwrite_front'] );
	$results   = array();

	foreach ( $selected as $key ) {
		$results[ $key ] = fairflock_demo_content_install_package( $key, $overwrite );
	}

	set_transient(
		'fairflock_demo_content_result_' . get_current_user_id(),
		$results,
		5 * MINUTE_IN_SECONDS
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'fairflock-demo-content',
				'ff_demo' => 'done',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'fairflock_demo_content_handle_submit' );

/**
 * Installs one demo package.
 *
 * @since 0.9.154
 *
 * @param string $package  Package key.
 * @param bool   $overwrite Allow overwriting front page content.
 * @return array{ok:bool,message:string}
 */
function fairflock_demo_content_install_package( $package, $overwrite = false ) {
	switch ( $package ) {
		case 'front_page':
			return fairflock_demo_content_install_front_page( $overwrite );
		case 'legal_pages':
			fairflock_ensure_legal_pages();
			return array(
				'ok'      => true,
				'message' => __( 'Rechtliche Platzhalter-Seiten geprüft/angelegt.', 'fairflock' ),
			);
		case 'category_page':
			return fairflock_demo_content_install_category_page();
		default:
			return array(
				'ok'      => false,
				'message' => __( 'Unbekanntes Paket.', 'fairflock' ),
			);
	}
}

/**
 * Creates or fills the homepage with the full FairFlock section stack.
 *
 * @since 0.9.154
 *
 * @param bool $overwrite Overwrite existing content.
 * @return array{ok:bool,message:string}
 */
function fairflock_demo_content_install_front_page( $overwrite ) {
	if ( ! function_exists( 'fairflock_get_default_front_page_content' ) ) {
		return array(
			'ok'      => false,
			'message' => __( 'Startseiten-Inhalt nicht verfügbar.', 'fairflock' ),
		);
	}

	$page_id = (int) get_option( 'page_on_front' );
	$post    = $page_id > 0 ? get_post( $page_id ) : null;

	if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
		$page_id = fairflock_ensure_front_page();
		$post    = $page_id > 0 ? get_post( $page_id ) : null;
	}

	if ( ! $post instanceof WP_Post ) {
		return array(
			'ok'      => false,
			'message' => __( 'Startseite konnte nicht angelegt werden.', 'fairflock' ),
		);
	}

	$has_content = is_string( $post->post_content ) && trim( $post->post_content ) !== '';
	if ( $has_content && ! $overwrite ) {
		return array(
			'ok'      => false,
			'message' => __( 'Startseite hat bereits Inhalt. Bitte „Überschreiben“ bestätigen.', 'fairflock' ),
		);
	}

	$content = fairflock_get_default_front_page_content();
	$result  = wp_update_post(
		array(
			'ID'           => (int) $post->ID,
			'post_content' => $content,
			'post_status'  => 'publish',
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		return array(
			'ok'      => false,
			'message' => $result->get_error_message(),
		);
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $post->ID );
	update_option( 'fairflock_patterns_front_seeded', '1', false );

	$edit = get_edit_post_link( (int) $post->ID, 'raw' );

	return array(
		'ok'      => true,
		'message' => sprintf(
			/* translators: %s: edit URL */
			__( 'Volle Startseite angelegt. %s', 'fairflock' ),
			$edit ? '<a href="' . esc_url( $edit ) . '">' . esc_html__( 'Seite bearbeiten', 'fairflock' ) . '</a>' : ''
		),
	);
}

/**
 * Ensures the categories overview page exists.
 *
 * @since 0.9.154
 *
 * @return array{ok:bool,message:string}
 */
function fairflock_demo_content_install_category_page() {
	$existing = get_page_by_path( 'kategorien' );
	if ( $existing instanceof WP_Post && 'publish' === $existing->post_status ) {
		// Prefer empty body — template page-kategorien.html owns the layout.
		if ( is_string( $existing->post_content ) && $existing->post_content !== '' ) {
			wp_update_post(
				array(
					'ID'           => (int) $existing->ID,
					'post_content' => '',
				),
				true
			);
		}
		return array(
			'ok'      => true,
			'message' => __( 'Seite „Kategorien“ war bereits vorhanden (Vorlage aktiv).', 'fairflock' ),
		);
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => __( 'Kategorien', 'fairflock' ),
			'post_name'    => 'kategorien',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		return array(
			'ok'      => false,
			'message' => $page_id->get_error_message(),
		);
	}

	return array(
		'ok'      => true,
		'message' => __( 'Seite „Kategorien“ angelegt.', 'fairflock' ),
	);
}

/**
 * Renders Demo Content admin UI.
 *
 * @since 0.9.154
 *
 * @return void
 */
function fairflock_demo_content_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$packages = fairflock_demo_content_packages();
	$status   = isset( $_GET['ff_demo'] ) ? sanitize_key( wp_unslash( $_GET['ff_demo'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$results  = get_transient( 'fairflock_demo_content_result_' . get_current_user_id() );
	if ( is_array( $results ) ) {
		delete_transient( 'fairflock_demo_content_result_' . get_current_user_id() );
	} else {
		$results = array();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Demo Content', 'fairflock' ); ?></h1>
		<p><?php esc_html_e( 'Ein-Klick-Layouts für neue Shops. Es wird nichts automatisch bei Theme-Updates überschrieben — nur was du hier auswählst.', 'fairflock' ); ?></p>

		<?php if ( 'empty' === $status ) : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'Bitte mindestens ein Paket auswählen.', 'fairflock' ); ?></p></div>
		<?php endif; ?>

		<?php if ( 'done' === $status && $results ) : ?>
			<div class="notice notice-info">
				<ul style="margin:0.5em 0 0.5em 1.2em;list-style:disc;">
					<?php foreach ( $results as $key => $result ) : ?>
						<?php
						$pkg = $packages[ $key ]['label'] ?? $key;
						$ok  = ! empty( $result['ok'] );
						$msg = isset( $result['message'] ) ? (string) $result['message'] : '';
						?>
						<li>
							<strong><?php echo esc_html( $pkg ); ?>:</strong>
							<?php
							echo $ok ? '✓ ' : '✗ ';
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- message may contain a safe edit link built above.
							echo $msg;
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'fairflock_demo_content' ); ?>

			<table class="widefat striped" style="max-width:920px;margin-top:16px;">
				<thead>
					<tr>
						<td class="check-column" style="padding:12px;"><span class="screen-reader-text"><?php esc_html_e( 'Auswählen', 'fairflock' ); ?></span></td>
						<th scope="col"><?php esc_html_e( 'Paket', 'fairflock' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Hinweis', 'fairflock' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $packages as $key => $pkg ) : ?>
						<tr>
							<th scope="row" class="check-column" style="padding:14px 12px;">
								<input type="checkbox" name="fairflock_demo_packages[]" value="<?php echo esc_attr( $key ); ?>" id="ff-demo-<?php echo esc_attr( $key ); ?>" <?php checked( 'front_page' === $key ); ?> />
							</th>
							<td style="padding:14px 12px;">
								<label for="ff-demo-<?php echo esc_attr( $key ); ?>">
									<strong><?php echo esc_html( $pkg['label'] ); ?></strong>
								</label>
								<p style="margin:6px 0 0;color:#50575e;"><?php echo esc_html( $pkg['description'] ); ?></p>
							</td>
							<td style="padding:14px 12px;color:#646970;"><?php echo esc_html( $pkg['risk'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p style="margin-top:16px;">
				<label>
					<input type="checkbox" name="fairflock_demo_overwrite_front" value="1" />
					<?php esc_html_e( 'Bestehende Startseite überschreiben (nur für „Volle Startseite“)', 'fairflock' ); ?>
				</label>
			</p>

			<?php submit_button( __( 'Ausgewählte Layouts anlegen', 'fairflock' ), 'primary', 'fairflock_demo_content_run' ); ?>
		</form>
	</div>
	<?php
}
