<?php
/**
 * FairFlock Setup Wizard — dashboard timeline for shop onboarding.
 *
 * @package FairFlock
 * @since 0.9.168
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers FairFlock parent menu + Einrichtung screen.
 *
 * @since 0.9.168
 *
 * @return void
 */
function fairflock_setup_wizard_register_admin() {
	add_menu_page(
		__( 'FairFlock', 'fairflock' ),
		__( 'FairFlock', 'fairflock' ),
		'edit_theme_options',
		'fairflock-setup',
		'fairflock_setup_wizard_render_admin_page',
		'dashicons-store',
		58
	);

	add_submenu_page(
		'fairflock-setup',
		__( 'Einrichtung', 'fairflock' ),
		__( 'Einrichtung', 'fairflock' ),
		'edit_theme_options',
		'fairflock-setup',
		'fairflock_setup_wizard_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_setup_wizard_register_admin', 9 );

/**
 * Handles dismiss / restore of optional wizard steps.
 *
 * @since 0.9.168
 *
 * @return void
 */
function fairflock_setup_wizard_handle_actions() {
	if ( ! isset( $_GET['page'] ) || 'fairflock-setup' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$action = isset( $_GET['ff_wizard'] ) ? sanitize_key( wp_unslash( $_GET['ff_wizard'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$step   = isset( $_GET['ff_step'] ) ? sanitize_key( wp_unslash( $_GET['ff_step'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( '' === $action || '' === $step ) {
		return;
	}

	check_admin_referer( 'fairflock_wizard_' . $action . '_' . $step );

	$dismissed = get_option( 'fairflock_wizard_dismissed', array() );
	if ( ! is_array( $dismissed ) ) {
		$dismissed = array();
	}

	if ( 'dismiss' === $action ) {
		$dismissed[ $step ] = 1;
	} elseif ( 'restore' === $action ) {
		unset( $dismissed[ $step ] );
	}

	update_option( 'fairflock_wizard_dismissed', $dismissed, false );

	wp_safe_redirect( admin_url( 'admin.php?page=fairflock-setup' ) );
	exit;
}
add_action( 'admin_init', 'fairflock_setup_wizard_handle_actions' );

/**
 * Setup wizard steps definition.
 *
 * @since 0.9.168
 *
 * @return array<string, array<string, mixed>>
 */
function fairflock_setup_wizard_steps() {
	$demo_url     = admin_url( 'admin.php?page=fairflock-demo-content' );
	$password_url = admin_url( 'admin.php?page=fairflock-password' );
	$styles_url   = admin_url( 'site-editor.php?path=/wp_global_styles' );
	$site_url     = admin_url( 'site-editor.php' );
	$plugins_url  = admin_url( 'plugins.php' );
	$products_url = admin_url( 'edit.php?post_type=product' );
	$cats_url     = admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' );
	$pages_url    = admin_url( 'edit.php?post_type=page' );
	$wc_url       = admin_url( 'admin.php?page=wc-admin' );
	$wc_pay_url   = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
	$wc_ship_url  = admin_url( 'admin.php?page=wc-settings&tab=shipping' );
	$menus_url    = admin_url( 'site-editor.php?p=%2Fnavigation' );
	$header_url   = admin_url( 'admin.php?page=fairflock-header-style' );
	$unsplash_url = admin_url( 'admin.php?page=fairflock-unsplash-credit' );
	$permalinks   = admin_url( 'options-permalink.php' );

	return array(
		'plugins'      => array(
			'title'       => __( 'Plugins aktivieren', 'fairflock' ),
			'description' => __( 'WooCommerce, Germanized und Complianz sind die Grundlage. Yoast SEO ist optional, aber sinnvoll.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Zu den Plugins', 'fairflock' ),
					'url'   => $plugins_url,
				),
				array(
					'label' => __( 'Checkliste öffnen', 'fairflock' ),
					'url'   => $demo_url,
				),
			),
		),
		'branding'     => array(
			'title'       => __( 'Marke festlegen', 'fairflock' ),
			'description' => __( 'Logo und Farben im Site Editor setzen — einmal einstellen, gilt für den ganzen Shop.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Global Styles', 'fairflock' ),
					'url'   => $styles_url,
				),
				array(
					'label' => __( 'Site Editor', 'fairflock' ),
					'url'   => $site_url,
				),
			),
		),
		'layouts'      => array(
			'title'       => __( 'Demo-Layouts anlegen', 'fairflock' ),
			'description' => __( 'Startseite, Rechtstexte, Kategorien und Über uns per Klick befüllen.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Demo Content', 'fairflock' ),
					'url'   => $demo_url,
				),
			),
		),
		'woocommerce'  => array(
			'title'       => __( 'WooCommerce einrichten', 'fairflock' ),
			'description' => __( 'Shop-Seiten, Währung und Basiseinstellungen prüfen.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'WooCommerce Start', 'fairflock' ),
					'url'   => $wc_url,
				),
				array(
					'label' => __( 'Permalinks', 'fairflock' ),
					'url'   => $permalinks,
				),
			),
		),
		'products'     => array(
			'title'       => __( 'Produkte anlegen', 'fairflock' ),
			'description' => __( 'Mindestens ein Produkt — mit Bild, Preis und ggf. Personalisierung.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Produkte', 'fairflock' ),
					'url'   => $products_url,
				),
				array(
					'label' => __( 'Neues Produkt', 'fairflock' ),
					'url'   => admin_url( 'post-new.php?post_type=product' ),
				),
			),
		),
		'categories'   => array(
			'title'       => __( 'Kategorien pflegen', 'fairflock' ),
			'description' => __( 'Produktkategorien anlegen — die Seite „Kategorien“ zeigt sie automatisch.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Produktkategorien', 'fairflock' ),
					'url'   => $cats_url,
				),
				array(
					'label' => __( 'Seite Kategorien', 'fairflock' ),
					'url'   => $demo_url,
				),
			),
		),
		'legal'        => array(
			'title'       => __( 'Rechtliche Seiten', 'fairflock' ),
			'description' => __( 'Impressum, Datenschutz, AGB, Widerruf, Versand & Zahlung — mit FairFlock-Daten vorbelegt. Germanized verlinkt AGB/Widerruf dorthin.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Demo Content (Rechtliches)', 'fairflock' ),
					'url'   => $demo_url,
				),
				array(
					'label' => __( 'Alle Seiten', 'fairflock' ),
					'url'   => $pages_url,
				),
			),
		),
		'about'        => array(
			'title'       => __( 'Über-uns-Seite', 'fairflock' ),
			'description' => __( 'Story, Werte und FAQ — Layout per Demo Content, Texte und Bilder im Editor anpassen.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Demo Content', 'fairflock' ),
					'url'   => $demo_url,
				),
				array(
					'label' => __( 'Seite bearbeiten', 'fairflock' ),
					'url'   => fairflock_setup_wizard_page_edit_url( 'ueber-uns' ),
				),
			),
		),
		'navigation'   => array(
			'title'       => __( 'Navigation prüfen', 'fairflock' ),
			'description' => __( 'Hauptmenü setzen. Menüpunkt mit Stil „Mega“ öffnet den Template-Teil „Mega-Menü“ (dort Spalten/Blöcke bauen). Unter Darstellung: Nav-Design, Inhaltsbreite und Formen.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Navigation im Site Editor', 'fairflock' ),
					'url'   => $menus_url,
				),
				array(
					'label' => __( 'Darstellung wählen', 'fairflock' ),
					'url'   => $header_url,
				),
			),
			'optional'    => true,
		),
		'payments'     => array(
			'title'       => __( 'Zahlung & Versand', 'fairflock' ),
			'description' => __( 'Zahlungsmethoden und Versandzonen in WooCommerce hinterlegen.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Zahlungen', 'fairflock' ),
					'url'   => $wc_pay_url,
				),
				array(
					'label' => __( 'Versand', 'fairflock' ),
					'url'   => $wc_ship_url,
				),
			),
		),
		'password'     => array(
			'title'       => __( 'Passwortschutz (optional)', 'fairflock' ),
			'description' => __( 'Während der Einrichtung die Website sperren — vor dem Go-Live wieder öffnen.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Passwortschutz', 'fairflock' ),
					'url'   => $password_url,
				),
			),
			'optional'    => true,
		),
		'unsplash'     => array(
			'title'       => __( 'Unsplash Credit (optional)', 'fairflock' ),
			'description' => __( 'Wenn Unsplash-Fotos genutzt werden: Bildnachweis im Footer und Impressum aktivieren.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Unsplash Credit', 'fairflock' ),
					'url'   => $unsplash_url,
				),
			),
			'optional'    => true,
		),
		'golive'       => array(
			'title'       => __( 'Go-Live Check', 'fairflock' ),
			'description' => __( 'Passwortschutz aus, Rechtstexte und Preise geprüft, Testbestellung gemacht — dann live.', 'fairflock' ),
			'links'       => array(
				array(
					'label' => __( 'Website ansehen', 'fairflock' ),
					'url'   => home_url( '/' ),
				),
				array(
					'label' => __( 'Passwortschutz', 'fairflock' ),
					'url'   => $password_url,
				),
			),
			'optional'    => true,
		),
	);
}

/**
 * Edit URL for a page slug, or pages list as fallback.
 *
 * @since 0.9.168
 *
 * @param string $slug Page slug.
 * @return string
 */
function fairflock_setup_wizard_page_edit_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page instanceof WP_Post ) {
		$url = get_edit_post_link( $page->ID, 'raw' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}
	return admin_url( 'edit.php?post_type=page' );
}

/**
 * Whether a published page with content exists for the slug.
 *
 * @since 0.9.168
 *
 * @param string $slug Page slug.
 * @return bool
 */
function fairflock_setup_wizard_page_ready( $slug ) {
	$page = get_page_by_path( $slug );
	if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
		return false;
	}
	return is_string( $page->post_content ) && '' !== trim( $page->post_content );
}

/**
 * Evaluates completion status for one step.
 *
 * @since 0.9.168
 *
 * @param string               $key   Step key.
 * @param array<string, mixed> $step  Step config.
 * @return array{done:bool,note:string}
 */
function fairflock_setup_wizard_step_status( $key, $step ) {
	$dismissed = get_option( 'fairflock_wizard_dismissed', array() );
	if ( ! is_array( $dismissed ) ) {
		$dismissed = array();
	}

	if ( ! empty( $step['optional'] ) && ! empty( $dismissed[ $key ] ) ) {
		return array(
			'done' => true,
			'note' => __( 'Als erledigt markiert.', 'fairflock' ),
		);
	}

	switch ( $key ) {
		case 'plugins':
			$woo = class_exists( 'WooCommerce' );
			$gzd = function_exists( 'fairflock_demo_content_plugin_status' )
				? fairflock_demo_content_plugin_status( 'woocommerce-germanized' )
				: array( 'active' => false );
			$cmp = function_exists( 'fairflock_demo_content_plugin_status' )
				? fairflock_demo_content_plugin_status( 'complianz-gdpr' )
				: array( 'active' => false );
			if ( $woo && ! empty( $gzd['active'] ) && ! empty( $cmp['active'] ) ) {
				return array(
					'done' => true,
					'note' => __( 'WooCommerce, Germanized und Complianz sind aktiv.', 'fairflock' ),
				);
			}
			$missing = array();
			if ( ! $woo ) {
				$missing[] = 'WooCommerce';
			}
			if ( empty( $gzd['active'] ) ) {
				$missing[] = 'Germanized';
			}
			if ( empty( $cmp['active'] ) ) {
				$missing[] = 'Complianz';
			}
			return array(
				'done' => false,
				'note' => sprintf(
					/* translators: %s: plugin names */
					__( 'Noch offen: %s', 'fairflock' ),
					implode( ', ', $missing )
				),
			);

		case 'branding':
			$logo_id = (int) get_theme_mod( 'custom_logo' );
			if ( $logo_id <= 0 && function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
				$logo_id = 1;
			}
			// Block themes often use site_logo option.
			if ( $logo_id <= 0 ) {
				$logo_id = (int) get_option( 'site_logo' );
			}
			if ( $logo_id > 0 ) {
				return array(
					'done' => true,
					'note' => __( 'Logo ist gesetzt. Farben ggf. noch in Global Styles prüfen.', 'fairflock' ),
				);
			}
			return array(
				'done' => false,
				'note' => __( 'Noch kein Site-Logo hinterlegt.', 'fairflock' ),
			);

		case 'layouts':
			$front_id = (int) get_option( 'page_on_front' );
			$front    = $front_id > 0 ? get_post( $front_id ) : null;
			$has_front = $front instanceof WP_Post
				&& is_string( $front->post_content )
				&& str_contains( $front->post_content, 'ff-pattern' );
			if ( $has_front ) {
				return array(
					'done' => true,
					'note' => __( 'Startseite enthält FairFlock-Layout-Abschnitte.', 'fairflock' ),
				);
			}
			return array(
				'done' => false,
				'note' => __( 'Demo-Layouts noch nicht eingespielt (oder Startseite leer).', 'fairflock' ),
			);

		case 'woocommerce':
			if ( ! class_exists( 'WooCommerce' ) ) {
				return array(
					'done' => false,
					'note' => __( 'WooCommerce ist nicht aktiv.', 'fairflock' ),
				);
			}
			$shop_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
			if ( $shop_id > 0 ) {
				return array(
					'done' => true,
					'note' => __( 'Shop-Seite ist vorhanden.', 'fairflock' ),
				);
			}
			return array(
				'done' => false,
				'note' => __( 'WooCommerce-Shop-Seite fehlt noch.', 'fairflock' ),
			);

		case 'products':
			if ( ! class_exists( 'WooCommerce' ) ) {
				return array(
					'done' => false,
					'note' => __( 'WooCommerce ist nicht aktiv.', 'fairflock' ),
				);
			}
			$count = (int) wp_count_posts( 'product' )->publish;
			if ( $count > 0 ) {
				return array(
					'done' => true,
					'note' => sprintf(
						/* translators: %d: product count */
						_n( '%d Produkt veröffentlicht.', '%d Produkte veröffentlicht.', $count, 'fairflock' ),
						$count
					),
				);
			}
			return array(
				'done' => false,
				'note' => __( 'Noch keine veröffentlichten Produkte.', 'fairflock' ),
			);

		case 'categories':
			$count = wp_count_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
				)
			);
			$count = is_wp_error( $count ) ? 0 : (int) $count;
			// Woo always has "Uncategorized" — need more than default.
			if ( $count > 1 ) {
				return array(
					'done' => true,
					'note' => sprintf(
						/* translators: %d: category count */
						__( '%d Produktkategorien vorhanden.', 'fairflock' ),
						$count
					),
				);
			}
			return array(
				'done' => false,
				'note' => __( 'Lege eigene Produktkategorien an (nicht nur „Uncategorized“).', 'fairflock' ),
			);

		case 'legal':
			$slugs = array( 'impressum', 'datenschutz', 'agb', 'widerruf', 'versand-zahlung' );
			$ready = 0;
			foreach ( $slugs as $slug ) {
				if ( fairflock_setup_wizard_page_ready( $slug ) ) {
					++$ready;
				}
			}
			if ( $ready >= 5 ) {
				return array(
					'done' => true,
					'note' => __( 'Alle fünf Rechtstexte sind angelegt.', 'fairflock' ),
				);
			}
			return array(
				'done' => false,
				'note' => sprintf(
					/* translators: 1: ready, 2: total */
					__( '%1$d von %2$d Rechtstexten bereit.', 'fairflock' ),
					$ready,
					5
				),
			);

		case 'about':
			if ( fairflock_setup_wizard_page_ready( 'ueber-uns' ) ) {
				return array(
					'done' => true,
					'note' => __( 'Seite „Über uns“ ist vorhanden.', 'fairflock' ),
				);
			}
			return array(
				'done' => false,
				'note' => __( 'Seite „Über uns“ fehlt noch oder ist leer.', 'fairflock' ),
			);

		case 'navigation':
			return array(
				'done' => ! empty( $dismissed[ $key ] ),
				'note' => __( 'Kurz im Site Editor prüfen — dann als erledigt markieren.', 'fairflock' ),
			);

		case 'payments':
			if ( ! class_exists( 'WooCommerce' ) ) {
				return array(
					'done' => false,
					'note' => __( 'WooCommerce ist nicht aktiv.', 'fairflock' ),
				);
			}
			$gateways = WC()->payment_gateways();
			if ( ! $gateways ) {
				return array(
					'done' => false,
					'note' => __( 'Zahlungsmethoden konnten nicht gelesen werden.', 'fairflock' ),
				);
			}
			$enabled = 0;
			foreach ( $gateways->payment_gateways() as $gateway ) {
				if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
					++$enabled;
				}
			}
			if ( $enabled > 0 ) {
				return array(
					'done' => true,
					'note' => sprintf(
						/* translators: %d: enabled gateways */
						_n( '%d Zahlungsart aktiv.', '%d Zahlungsarten aktiv.', $enabled, 'fairflock' ),
						$enabled
					),
				);
			}
			return array(
				'done' => false,
				'note' => __( 'Noch keine Zahlungsart aktiviert.', 'fairflock' ),
			);

		case 'password':
			$enabled = (string) get_option( 'fairflock_site_password_enabled', '' ) === '1';
			if ( $enabled ) {
				return array(
					'done' => true,
					'note' => __( 'Site-Passwortschutz ist aktiv (für die Einrichtung ok).', 'fairflock' ),
				);
			}
			return array(
				'done' => ! empty( $dismissed[ $key ] ),
				'note' => __( 'Optional — aktivieren, wenn die Seite noch nicht öffentlich sein soll.', 'fairflock' ),
			);

		case 'unsplash':
			if ( fairflock_unsplash_credit_enabled() ) {
				return array(
					'done' => true,
					'note' => __( 'Unsplash-Bildnachweis ist aktiv (Footer + Impressum).', 'fairflock' ),
				);
			}
			return array(
				'done' => ! empty( $dismissed[ $key ] ),
				'note' => __( 'Optional — nur nötig, wenn Unsplash-Fotos eingesetzt werden.', 'fairflock' ),
			);

		case 'golive':
			$pw_on = (string) get_option( 'fairflock_site_password_enabled', '' ) === '1';
			if ( $pw_on ) {
				return array(
					'done' => false,
					'note' => __( 'Passwortschutz ist noch an — vor Go-Live ausschalten.', 'fairflock' ),
				);
			}
			return array(
				'done' => ! empty( $dismissed[ $key ] ),
				'note' => __( 'Wenn alles stimmt: als erledigt markieren und live gehen.', 'fairflock' ),
			);

		default:
			return array(
				'done' => false,
				'note' => '',
			);
	}
}

/**
 * Progress stats for the wizard.
 *
 * @since 0.9.168
 *
 * @return array{total:int,done:int,percent:int}
 */
function fairflock_setup_wizard_progress() {
	$steps = fairflock_setup_wizard_steps();
	$total = count( $steps );
	$done  = 0;
	foreach ( $steps as $key => $step ) {
		$status = fairflock_setup_wizard_step_status( $key, $step );
		if ( ! empty( $status['done'] ) ) {
			++$done;
		}
	}
	$percent = $total > 0 ? (int) round( ( $done / $total ) * 100 ) : 0;
	return array(
		'total'   => $total,
		'done'    => $done,
		'percent' => $percent,
	);
}

/**
 * Enqueues admin CSS on the wizard screen.
 *
 * @since 0.9.168
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function fairflock_setup_wizard_admin_assets( $hook ) {
	if ( 'toplevel_page_fairflock-setup' !== $hook ) {
		return;
	}
	wp_enqueue_style(
		'fairflock-setup-wizard',
		FAIRFLOCK_URI . '/assets/css/admin-setup-wizard.css',
		array(),
		FAIRFLOCK_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'fairflock_setup_wizard_admin_assets' );

/**
 * Renders the Setup Wizard admin page.
 *
 * @since 0.9.168
 *
 * @return void
 */
function fairflock_setup_wizard_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$steps    = fairflock_setup_wizard_steps();
	$progress = fairflock_setup_wizard_progress();
	$dismissed = get_option( 'fairflock_wizard_dismissed', array() );
	if ( ! is_array( $dismissed ) ) {
		$dismissed = array();
	}
	?>
	<div class="wrap ff-wizard">
		<div class="ff-wizard__hero">
			<p class="ff-wizard__eyebrow"><?php esc_html_e( 'FairFlock', 'fairflock' ); ?></p>
			<h1><?php esc_html_e( 'Einrichtung', 'fairflock' ); ?></h1>
			<p class="ff-wizard__lead">
				<?php esc_html_e( 'Schritt für Schritt zum fertigen Shop — mit direkten Links zu den richtigen Stellen im System.', 'fairflock' ); ?>
			</p>
			<div class="ff-wizard__progress" role="status" aria-live="polite">
				<div class="ff-wizard__progress-bar" aria-hidden="true">
					<span style="width:<?php echo esc_attr( (string) $progress['percent'] ); ?>%;"></span>
				</div>
				<p class="ff-wizard__progress-label">
					<?php
					printf(
						/* translators: 1: done count, 2: total, 3: percent */
						esc_html__( '%1$d von %2$d erledigt · %3$d%%', 'fairflock' ),
						(int) $progress['done'],
						(int) $progress['total'],
						(int) $progress['percent']
					);
					?>
				</p>
			</div>
		</div>

		<ol class="ff-wizard__timeline">
			<?php
			$index = 0;
			foreach ( $steps as $key => $step ) :
				++$index;
				$status   = fairflock_setup_wizard_step_status( $key, $step );
				$done     = ! empty( $status['done'] );
				$optional = ! empty( $step['optional'] );
				$classes  = 'ff-wizard__step';
				$classes .= $done ? ' is-done' : ' is-open';
				if ( $optional ) {
					$classes .= ' is-optional';
				}
				?>
				<li class="<?php echo esc_attr( $classes ); ?>">
					<div class="ff-wizard__marker" aria-hidden="true">
						<?php if ( $done ) : ?>
							<span class="ff-wizard__check">✓</span>
						<?php else : ?>
							<span class="ff-wizard__num"><?php echo esc_html( (string) $index ); ?></span>
						<?php endif; ?>
					</div>
					<div class="ff-wizard__card">
						<div class="ff-wizard__card-head">
							<h2>
								<?php echo esc_html( $step['title'] ); ?>
								<?php if ( $optional ) : ?>
									<span class="ff-wizard__badge"><?php esc_html_e( 'optional', 'fairflock' ); ?></span>
								<?php endif; ?>
							</h2>
							<span class="ff-wizard__state <?php echo $done ? 'is-done' : 'is-todo'; ?>">
								<?php echo $done ? esc_html__( 'Erledigt', 'fairflock' ) : esc_html__( 'Offen', 'fairflock' ); ?>
							</span>
						</div>
						<p class="ff-wizard__desc"><?php echo esc_html( $step['description'] ); ?></p>
						<?php if ( ! empty( $status['note'] ) ) : ?>
							<p class="ff-wizard__note"><?php echo esc_html( $status['note'] ); ?></p>
						<?php endif; ?>
						<div class="ff-wizard__actions">
							<?php if ( ! empty( $step['links'] ) && is_array( $step['links'] ) ) : ?>
								<?php foreach ( $step['links'] as $i => $link ) : ?>
									<?php
									if ( empty( $link['url'] ) || empty( $link['label'] ) ) {
										continue;
									}
									$btn = 0 === $i && ! $done ? 'button button-primary' : 'button button-secondary';
									?>
									<a class="<?php echo esc_attr( $btn ); ?>" href="<?php echo esc_url( $link['url'] ); ?>">
										<?php echo esc_html( $link['label'] ); ?>
									</a>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if ( $optional ) : ?>
								<?php
								$is_dismissed = ! empty( $dismissed[ $key ] );
								$action       = $is_dismissed ? 'restore' : 'dismiss';
								$url          = wp_nonce_url(
									add_query_arg(
										array(
											'page'      => 'fairflock-setup',
											'ff_wizard' => $action,
											'ff_step'   => $key,
										),
										admin_url( 'admin.php' )
									),
									'fairflock_wizard_' . $action . '_' . $key
								);
								?>
								<a class="button button-link" href="<?php echo esc_url( $url ); ?>">
									<?php
									echo $is_dismissed
										? esc_html__( 'Wieder öffnen', 'fairflock' )
										: esc_html__( 'Als erledigt markieren', 'fairflock' );
									?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php
}

/**
 * Adds a compact Setup widget to the WP dashboard.
 *
 * @since 0.9.168
 *
 * @return void
 */
function fairflock_setup_wizard_register_dashboard_widget() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	wp_add_dashboard_widget(
		'fairflock_setup_wizard',
		__( 'FairFlock Einrichtung', 'fairflock' ),
		'fairflock_setup_wizard_render_dashboard_widget'
	);
}
add_action( 'wp_dashboard_setup', 'fairflock_setup_wizard_register_dashboard_widget' );

/**
 * Renders dashboard widget summary.
 *
 * @since 0.9.168
 *
 * @return void
 */
function fairflock_setup_wizard_render_dashboard_widget() {
	$progress = fairflock_setup_wizard_progress();
	$steps    = fairflock_setup_wizard_steps();
	$next     = null;
	foreach ( $steps as $key => $step ) {
		$status = fairflock_setup_wizard_step_status( $key, $step );
		if ( empty( $status['done'] ) ) {
			$next = array(
				'key'  => $key,
				'step' => $step,
			);
			break;
		}
	}
	?>
	<p>
		<strong>
			<?php
			printf(
				/* translators: 1: done, 2: total, 3: percent */
				esc_html__( '%1$d / %2$d Schritte · %3$d%%', 'fairflock' ),
				(int) $progress['done'],
				(int) $progress['total'],
				(int) $progress['percent']
			);
			?>
		</strong>
	</p>
	<?php if ( $next ) : ?>
		<p>
			<?php esc_html_e( 'Als Nächstes:', 'fairflock' ); ?>
			<strong><?php echo esc_html( $next['step']['title'] ); ?></strong>
		</p>
	<?php else : ?>
		<p><?php esc_html_e( 'Alles erledigt — stark!', 'fairflock' ); ?></p>
	<?php endif; ?>
	<p>
		<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=fairflock-setup' ) ); ?>">
			<?php esc_html_e( 'Zur Einrichtung', 'fairflock' ); ?>
		</a>
	</p>
	<?php
}
