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
 * Registers Demo Content under FairFlock.
 *
 * @since 0.9.154
 * @since 0.9.168 Parent menu is Einrichtung (fairflock-setup).
 *
 * @return void
 */
function fairflock_demo_content_register_admin() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Demo Content', 'fairflock' ),
		__( 'Demo Content', 'fairflock' ),
		'edit_theme_options',
		'fairflock-demo-content',
		'fairflock_demo_content_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_demo_content_register_admin', 10 );

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
			'description' => __( 'Legt die Startseite an bzw. befüllt sie mit dem FairFlock-Layout: Produkte, Text/Bild, Vollbild, Kategorien, zweites Vollbild, Bento, Brand-Band, Steps, FAQ, Reviews und CTA (Hero + Trust kommen aus dem Template).', 'fairflock' ),
			'risk'        => __( 'Ohne Haken bleibt bestehender Inhalt unberührt.', 'fairflock' ),
			'overwrite'   => array(
				'name'  => 'fairflock_demo_overwrite_front',
				'label' => __( 'Bestehende Startseite überschreiben', 'fairflock' ),
			),
		),
		'legal_pages'   => array(
			'label'       => __( 'Rechtliche Seiten (FairFlock-Daten)', 'fairflock' ),
			'description' => __( 'Impressum, Datenschutz, AGB, Widerrufsrecht, Versand & Zahlung mit den FairFlock-Angaben (wie Amateurhandball.de).', 'fairflock' ),
			'risk'        => __( 'Ohne Haken werden nur fehlende Seiten angelegt.', 'fairflock' ),
			'overwrite'   => array(
				'name'  => 'fairflock_demo_overwrite_legal',
				'label' => __( 'Bestehende Rechtstexte ersetzen', 'fairflock' ),
			),
		),
		'category_page' => array(
			'label'       => __( 'Seite „Kategorien“', 'fairflock' ),
			'description' => __( 'Stellt sicher, dass die Seite /kategorien/ existiert und die Theme-Vorlage für die Kategorie-Übersicht nutzt.', 'fairflock' ),
			'risk'        => __( 'Legt die Seite nur an, wenn sie fehlt; Inhalt wird für die Live-Vorlage geleert.', 'fairflock' ),
			'overwrite'   => null,
		),
		'about_page'    => array(
			'label'       => __( 'Seite „Über uns“', 'fairflock' ),
			'description' => __( 'Legt /ueber-uns/ an bzw. befüllt sie mit Hero, Story, Werten, Moment-Band, Text-Bild, CTA und FAQ.', 'fairflock' ),
			'risk'        => __( 'Ohne Haken bleibt bestehender Inhalt unberührt.', 'fairflock' ),
			'overwrite'   => array(
				'name'  => 'fairflock_demo_overwrite_about',
				'label' => __( 'Bestehende „Über uns“-Seite überschreiben', 'fairflock' ),
			),
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

	$overwrite       = ! empty( $_POST['fairflock_demo_overwrite_front'] );
	$overwrite_legal = ! empty( $_POST['fairflock_demo_overwrite_legal'] );
	$overwrite_about = ! empty( $_POST['fairflock_demo_overwrite_about'] );
	$results         = array();

	foreach ( $selected as $key ) {
		if ( 'legal_pages' === $key ) {
			$results[ $key ] = fairflock_demo_content_install_package( $key, $overwrite_legal );
		} elseif ( 'about_page' === $key ) {
			$results[ $key ] = fairflock_demo_content_install_package( $key, $overwrite_about );
		} else {
			$results[ $key ] = fairflock_demo_content_install_package( $key, $overwrite );
		}
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
			$stats = fairflock_ensure_legal_pages( (bool) $overwrite );
			return array(
				'ok'      => true,
				'message' => sprintf(
					/* translators: 1: created count, 2: updated count */
					__( 'Rechtliche Seiten: %1$d neu, %2$d aktualisiert.', 'fairflock' ),
					(int) $stats['created'],
					(int) $stats['updated']
				),
			);
		case 'category_page':
			return fairflock_demo_content_install_category_page();
		case 'about_page':
			return fairflock_demo_content_install_about_page( (bool) $overwrite );
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
 * Creates or fills the About page with the FairFlock about pattern stack.
 *
 * @since 0.9.161
 *
 * @param bool $overwrite Overwrite existing content.
 * @return array{ok:bool,message:string}
 */
function fairflock_demo_content_install_about_page( $overwrite = false ) {
	$content = '';
	if ( function_exists( 'fairflock_get_registered_pattern_content' ) ) {
		$content = trim( (string) fairflock_get_registered_pattern_content( 'fairflock/about' ) );
	}
	if ( '' === $content ) {
		return array(
			'ok'      => false,
			'message' => __( 'Über-uns-Layout nicht verfügbar (Pattern fehlt).', 'fairflock' ),
		);
	}

	$existing = get_page_by_path( 'ueber-uns' );
	if ( $existing instanceof WP_Post ) {
		$has_content = is_string( $existing->post_content ) && '' !== trim( $existing->post_content );
		if ( $has_content && ! $overwrite ) {
			$edit = get_edit_post_link( $existing->ID, 'raw' );
			return array(
				'ok'      => true,
				'message' => sprintf(
					/* translators: %s: edit link */
					__( 'Seite „Über uns“ existiert bereits mit Inhalt — Überschreiben nicht aktiviert. %s', 'fairflock' ),
					$edit ? '<a href="' . esc_url( $edit ) . '">' . esc_html__( 'Seite bearbeiten', 'fairflock' ) . '</a>' : ''
				),
			);
		}

		$updated = wp_update_post(
			array(
				'ID'           => (int) $existing->ID,
				'post_title'   => __( 'Über uns', 'fairflock' ),
				'post_status'  => 'publish',
				'post_content' => $content,
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			return array(
				'ok'      => false,
				'message' => $updated->get_error_message(),
			);
		}

		$edit = get_edit_post_link( (int) $existing->ID, 'raw' );
		return array(
			'ok'      => true,
			'message' => sprintf(
				/* translators: %s: edit link */
				__( 'Seite „Über uns“ aktualisiert. %s', 'fairflock' ),
				$edit ? '<a href="' . esc_url( $edit ) . '">' . esc_html__( 'Seite bearbeiten', 'fairflock' ) . '</a>' : ''
			),
		);
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => __( 'Über uns', 'fairflock' ),
			'post_name'    => 'ueber-uns',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => $content,
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		return array(
			'ok'      => false,
			'message' => $page_id->get_error_message(),
		);
	}

	$edit = get_edit_post_link( (int) $page_id, 'raw' );
	return array(
		'ok'      => true,
		'message' => sprintf(
			/* translators: %s: edit link */
			__( 'Seite „Über uns“ angelegt. %s', 'fairflock' ),
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
 * Recommended plugins before shop setup.
 *
 * @since 0.9.155
 *
 * @return array<int, array{slug:string,name:string,required:bool,note:string}>
 */
function fairflock_demo_content_recommended_plugins() {
	return array(
		array(
			'slug'     => 'woocommerce',
			'name'     => 'WooCommerce',
			'required' => true,
			'note'     => __( 'Shop-Grundlage — Produkte, Warenkorb, Kasse.', 'fairflock' ),
		),
		array(
			'slug'     => 'complianz-gdpr',
			'name'     => 'Complianz',
			'required' => true,
			'note'     => __( 'Cookie-Hinweis & Datenschutz-Einwilligung.', 'fairflock' ),
		),
		array(
			'slug'     => 'wordpress-seo',
			'name'     => 'Yoast SEO',
			'required' => false,
			'note'     => __( 'Optional, aber sinnvoll — Suchmaschinen & Snippets.', 'fairflock' ),
		),
	);
}

/**
 * Whether a plugin slug is installed and active.
 *
 * @since 0.9.155
 *
 * @param string $slug Plugin directory slug.
 * @return array{installed:bool,active:bool}
 */
function fairflock_demo_content_plugin_status( $slug ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$slug   = sanitize_key( $slug );
	$found  = '';
	$plugins = get_plugins();

	foreach ( array_keys( $plugins ) as $file ) {
		if ( str_starts_with( $file, $slug . '/' ) || $file === $slug . '.php' ) {
			$found = $file;
			break;
		}
	}

	if ( '' === $found ) {
		return array(
			'installed' => false,
			'active'    => false,
		);
	}

	return array(
		'installed' => true,
		'active'    => is_plugin_active( $found ),
	);
}

/**
 * Renders Demo Content admin UI.
 *
 * @since 0.9.154
 * @since 0.9.155 Plugins checklist before layouts.
 *
 * @return void
 */
function fairflock_demo_content_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$packages = fairflock_demo_content_packages();
	$plugins  = fairflock_demo_content_recommended_plugins();
	$status   = isset( $_GET['ff_demo'] ) ? sanitize_key( wp_unslash( $_GET['ff_demo'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$results  = get_transient( 'fairflock_demo_content_result_' . get_current_user_id() );
	if ( is_array( $results ) ) {
		delete_transient( 'fairflock_demo_content_result_' . get_current_user_id() );
	} else {
		$results = array();
	}

	$can_install = current_user_can( 'install_plugins' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Demo Content', 'fairflock' ); ?></h1>
		<p><?php esc_html_e( 'Ein-Klick-Layouts für neue Shops. Es wird nichts automatisch bei Theme-Updates überschrieben — nur was du hier auswählst.', 'fairflock' ); ?></p>

		<h2 style="margin-top:28px;"><?php esc_html_e( 'Plugins', 'fairflock' ); ?></h2>
		<p><?php esc_html_e( 'Bitte diese Plugins installieren und aktivieren, bevor du Layouts anlegst oder den Shop einrichtest.', 'fairflock' ); ?></p>

		<table class="widefat striped" style="max-width:920px;">
			<thead>
				<tr>
					<th scope="col" style="padding:12px;"><?php esc_html_e( 'Plugin', 'fairflock' ); ?></th>
					<th scope="col" style="padding:12px;"><?php esc_html_e( 'Status', 'fairflock' ); ?></th>
					<th scope="col" style="padding:12px;"><?php esc_html_e( 'Hinweis', 'fairflock' ); ?></th>
					<th scope="col" style="padding:12px;"><?php esc_html_e( 'Aktion', 'fairflock' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $plugins as $plugin ) : ?>
					<?php
					$st = fairflock_demo_content_plugin_status( $plugin['slug'] );
					if ( $st['active'] ) {
						$badge = '<span style="color:#1e7a34;font-weight:600;">' . esc_html__( 'Aktiv', 'fairflock' ) . '</span>';
					} elseif ( $st['installed'] ) {
						$badge = '<span style="color:#996800;font-weight:600;">' . esc_html__( 'Installiert, nicht aktiv', 'fairflock' ) . '</span>';
					} else {
						$badge = '<span style="color:#b32d2e;font-weight:600;">' . esc_html__( 'Fehlt', 'fairflock' ) . '</span>';
					}

					$install_url = $can_install
						? admin_url( 'plugin-install.php?s=' . rawurlencode( $plugin['name'] ) . '&tab=search&type=term' )
						: '';
					$plugins_url = admin_url( 'plugins.php' );
					?>
					<tr>
						<td style="padding:14px 12px;">
							<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
							<?php if ( ! empty( $plugin['required'] ) ) : ?>
								— <span style="color:#b32d2e;"><?php esc_html_e( 'erforderlich', 'fairflock' ); ?></span>
							<?php else : ?>
								— <span style="color:#646970;"><?php esc_html_e( 'optional, aber sinnvoll', 'fairflock' ); ?></span>
							<?php endif; ?>
						</td>
						<td style="padding:14px 12px;"><?php echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<td style="padding:14px 12px;color:#50575e;"><?php echo esc_html( $plugin['note'] ); ?></td>
						<td style="padding:14px 12px;">
							<?php if ( $st['active'] ) : ?>
								—
							<?php elseif ( $st['installed'] ) : ?>
								<a href="<?php echo esc_url( $plugins_url ); ?>"><?php esc_html_e( 'Unter Plugins aktivieren', 'fairflock' ); ?></a>
							<?php elseif ( $install_url ) : ?>
								<a class="button button-secondary" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Installieren', 'fairflock' ); ?></a>
							<?php else : ?>
								<span class="description"><?php esc_html_e( 'Keine Installationsrechte', 'fairflock' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2 style="margin-top:32px;"><?php esc_html_e( 'Layouts anlegen', 'fairflock' ); ?></h2>

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
							<th scope="row" class="check-column" style="padding:14px 12px;vertical-align:top;">
								<input type="checkbox" name="fairflock_demo_packages[]" value="<?php echo esc_attr( $key ); ?>" id="ff-demo-<?php echo esc_attr( $key ); ?>" <?php checked( 'front_page' === $key ); ?> />
							</th>
							<td style="padding:14px 12px;">
								<label for="ff-demo-<?php echo esc_attr( $key ); ?>">
									<strong><?php echo esc_html( $pkg['label'] ); ?></strong>
								</label>
								<p style="margin:6px 0 0;color:#50575e;"><?php echo esc_html( $pkg['description'] ); ?></p>
								<?php if ( ! empty( $pkg['overwrite']['name'] ) && ! empty( $pkg['overwrite']['label'] ) ) : ?>
									<p style="margin:12px 0 0;">
										<label style="display:inline-flex;align-items:flex-start;gap:8px;color:#1d2327;">
											<input type="checkbox" name="<?php echo esc_attr( $pkg['overwrite']['name'] ); ?>" value="1" style="margin-top:2px;" />
											<span><?php echo esc_html( $pkg['overwrite']['label'] ); ?></span>
										</label>
									</p>
								<?php endif; ?>
							</td>
							<td style="padding:14px 12px;color:#646970;vertical-align:top;"><?php echo esc_html( $pkg['risk'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php submit_button( __( 'Ausgewählte Layouts anlegen', 'fairflock' ), 'primary', 'fairflock_demo_content_run' ); ?>
		</form>
	</div>
	<?php
}
