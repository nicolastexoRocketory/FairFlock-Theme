<?php
/**
 * Theme metadata for Appearance → Themes modal + FairFlock Versionshistorie.
 *
 * @package FairFlock
 * @since 0.9.182
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ordered changelog entries (newest first).
 *
 * @since 0.9.182
 *
 * @return array<int, array{version:string,date?:string,items:array<int,string>}>
 */
function fairflock_theme_changelog() {
	return array(
		array(
			'version' => '0.9.245',
			'items'   => array(
				__( 'Hero-Nav „Pfeile neben Punkten“ auf Mobil wieder mittig zentriert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.244',
			'items'   => array(
				__( 'Mobile Hero: Höhe wächst mit Inhalt — CTA wird nicht mehr abgeschnitten; Nav-Pfeile einheitlich.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.243',
			'items'   => array(
				__( 'Warenkorb-Badge bleibt rund (kein Geister-Quadrat bei Formen → Eckig); Cart-Wrapper ohne zweiten Rahmen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.242',
			'items'   => array(
				__( 'Mobile Nav (Randlos/Eckig): kein Doppelrahmen am Warenkorb, einheitliche Icon-Boxen, Gutter-Padding.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.241',
			'items'   => array(
				__( 'Hero-Nav-Pfeile nur noch unter Darstellung — unabhängig vom Stil „Inhalt mittig“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.240',
			'items'   => array(
				__( 'Mobile Header-Icons: einheitliche Größe, Radius und vertikale Ausrichtung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.239',
			'items'   => array(
				__( 'Mobile Header/Nav: fester 1em-Inset, Logo skaliert, Overlay-Hamburger bleibt sichtbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.238',
			'items'   => array(
				__( 'Full-Width-Innenabstand: Desktop 4em, Tablet 2em, Mobile 1em (--ff-gutter / Root-Padding).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.237',
			'items'   => array(
				__( 'Kategorien-Bento: Inhalt wählbar — Kollektionen, Produkte einer Kategorie oder Seiten/Beiträge (live).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.236',
			'items'   => array(
				__( 'Darstellung: Kollektionen-Spalten — 2, 3 oder 4 Spalten am Desktop einstellbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.235',
			'items'   => array(
				__( 'Darstellung: Hero-Navigation — Pfeile optional direkt neben den Punkten (mittig).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.234',
			'items'   => array(
				__( 'Hero: Stil „Inhalt mittig“ zentriert Text, Titel und Buttons zuverlässig horizontal.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.233',
			'items'   => array(
				__( 'Hero: individuelle Titel-Schriftgröße (S–XXL) greift wieder.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.232',
			'items'   => array(
				__( 'Hero: Block-Stil „Hero: zentriert“ — Inhalt horizontal und vertikal mittig.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.231',
			'items'   => array(
				__( 'Mega-Menü: bündig unter der Nav + Ein-/Ausfahr-Animation.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.230',
			'items'   => array(
				__( 'Demo Content / Wizard: Germanized for WooCommerce als erforderliches Plugin.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.229',
			'items'   => array(
				__( 'Header: Seitentitel mit gemessenem Abstand — kein Abschneiden unter der Leiste.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.228',
			'items'   => array(
				__( 'Warenkorb: Kein Erfolgs-Banner oben — Button wird „Wurde hinzugefügt“, Drawer öffnet sich.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.227',
			'items'   => array(
				__( 'Produkt-Slider: Spalten-Einstellung (2–6) steuert wieder die Kartenbreite.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.226',
			'items'   => array(
				__( 'Navigation: schlichter Hover — Label + Indicator als Einheit, ohne Pill und Underline.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.225',
			'items'   => array(
				__( 'Editor: Darstellung im Canvas, Bento-Spans, Mega-Liste/FAQ stabiler — Editor ≈ Frontend.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.224',
			'items'   => array(
				__( 'Security/Perf: Personalized-ATC mit REST-Nonce; kein SVG-Upload; Conditional Assets; Version nur aus style.css; ZIP-Allowlist.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.223',
			'items'   => array(
				__( 'Multi-Tenant: Theme-Push überschreibt keine angepassten Mega-Menü-/Collections-Inhalte in der DB.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.222',
			'items'   => array(
				__( 'Mega-Menü: Kategorien-Spalte doppelt breit, Linkliste zweispaltig.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.221',
			'items'   => array(
				__( 'Mega-Menü: Spaltenbreiten aus dem Site Editor werden wieder angewendet.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.220',
			'items'   => array(
				__( 'Editor: Mega-Liste erscheint wieder im Block-Inserter (JS-Variation für core/group).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.219',
			'items'   => array(
				__( 'Mega-Menü: Promo-Card nur so hoch wie der Inhalt; Nav-Hover-Underline ohne Doppelstrich und Abstand zu Trennstrichen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.218',
			'items'   => array(
				__( 'Mega-Menü: Listen manuell, alle Kategorien live oder Produkte einer Kategorie (Site Editor).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.217',
			'items'   => array(
				__( 'Navigation: Hover mit animiertem Underline und dezentem Hitbox-Hintergrund.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.216',
			'items'   => array(
				__( 'Darstellung: Hero-Höhe der Slideshow als vh einstellbar (alle Geräte).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.215',
			'items'   => array(
				__( 'Navigation: Indicator wieder dezent (keine Brand-Farbe), klarer Abstand zum Label, Dreh-Animation.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.214',
			'items'   => array(
				__( 'Produktseite: Variations-Auswahl und Meta-Bereich (SKU/Kategorie/Tags) sauber ausgerichtet.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.213',
			'items'   => array(
				__( 'Navigation: Dropdown-Indicator mit Hover-Farbe, mehr Abstand und Dreh-Animation.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.212',
			'items'   => array(
				__( 'Header: leichte Kompakt-Skalierung beim Scrollen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.211',
			'items'   => array(
				__( 'Mega-Menü: 4 Spalten — 3× Links + Promo-Card (Bild, Titel, Text, Button).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.210',
			'items'   => array(
				__( 'Suche: Vorschläge Desktop 3 Spalten, Mobile 2 Spalten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.209',
			'items'   => array(
				__( 'Produkt-Popup: Titel nutzen Body-Font statt Heading-Design.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.208',
			'items'   => array(
				__( 'Mega-Menü: bündig unter der Nav und randlos (kein Abstand).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.207',
			'items'   => array(
				__( 'Hero-Slider: Titel übernehmen Typografie aus Global Styles (h1/h2), nicht mehr feste Theme-Werte.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.206',
			'items'   => array(
				__( 'Navigation: Dropdown-Indicator und Abstand zu den Trennstrichen überarbeitet.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.205',
			'items'   => array(
				__( 'Mega-Menü: Breite unter FairFlock → Darstellung; folgt Nav-Design/Formen; Buttons nutzen Brand-Palette.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.204',
			'items'   => array(
				__( 'Hero-Slider: Heading-Font greift wieder zuverlässig (auch gegen Block-Font-Klassen).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.203',
			'items'   => array(
				__( 'Mega-Menü: Inhalt als Template-Teil „Mega-Menü“ (Spalten/Blöcke); Hover-Lücke geschlossen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.202',
			'items'   => array(
				__( 'Navigation: opt-in Mega-Menü per Block-Stil „Mega“ (Site Editor, Spalten über Unterpunkte).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.201',
			'items'   => array(
				__( 'Mein Konto: Adressen-Grid, Login-Karte, Dark-Mode-Felder und Body-Font für Inhaltsüberschriften.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.200',
			'items'   => array(
				__( 'Header-Navigation: Typografie aus Global Styles; Standard Body-Font ohne Uppercase.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.199',
			'items'   => array(
				__( 'Shop-Seitennavigation: gestylte Buttons (Surface, Brand-Aktivzustand).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.198',
			'items'   => array(
				__( 'Hero: Titel wieder größer (alle Slides gleich); Primär-Button bleibt hell (auch Dark Mode).', 'fairflock' ),
				__( 'Header-Logo: Site-Editor-Breite greift wieder (kein max-height-Cap mehr).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.197',
			'items'   => array(
				__( 'Warenkorb-Drawer: Produktname als Text (Body-Font), kein Heading-Tag.', 'fairflock' ),
				__( 'Hero-Slider: Überschriften nutzen wieder Global Styles (h1/h2).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.196',
			'items'   => array(
				__( 'Kollektionen: Desktop zeigt 3 Reihen, darunter „Mehr anzeigen“ für den Rest.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.195',
			'items'   => array(
				__( 'Produktkarten Hover „Ansehen“: dunkler Hintergrund, weißer Text (Dark Mode).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.194',
			'items'   => array(
				__( 'Produkt-Popup Dark Mode: Galerie-Pfeile, Thumbnail-Leiste und Hinweis-Box korrigiert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.193',
			'items'   => array(
				__( 'Hero-Slideshow: Titel einheitlich größer (alle Slides gleiche Größe).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.192',
			'items'   => array(
				__( 'Sektions- und Rechtsseiten-Titel nutzen Global Styles (h2); About-Hero-Marke als Überschrift.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.191',
			'items'   => array(
				__( 'Suche-Popup: Suchleiste volle Panelbreite, einheitliche Radien; robust gegen Volle Breite / Eckig.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.190',
			'items'   => array(
				__( 'Hero: Overlay bleibt im Dark Mode dunkel; Slide-Inhalt folgt „Volle Breite“ (--ff-shell).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.189',
			'items'   => array(
				__( 'Darstellung „Eckig“ greift auf Karten, Suche, Kasse, Shop; Dark Mode für Close-Buttons, Sortierung, CTA und Verläufe korrigiert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.188',
			'items'   => array(
				__( 'Dark Mode: Suche nutzt Surface/Text-Tokens; Brand-Farben bleiben in Light/Dark gleich.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.187',
			'items'   => array(
				__( 'Dark Mode: Vollbild-Overlays bleiben dunkel, Text darauf weiß.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.186',
			'items'   => array(
				__( 'Darstellung: Volle Breite und Formen (abgerundet/eckig) unter FairFlock → Darstellung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.185',
			'items'   => array(
				__( 'Produkt-Popup: Titel nutzt Global Styles (Überschrift) und dekodiert HTML-Entities korrekt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.184',
			'items'   => array(
				__( 'Hero-Slideshow: Überschriften nutzen Global Styles (h1–h6).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.183',
			'items'   => array(
				__( 'Global Styles: Style-Variationen Light und Dark (Site Editor → Styles).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.182',
			'items'   => array(
				__( 'Theme-Details: Herausgeber Rocketory.de, Versionshistorie in der Modal-Ansicht und unter FairFlock.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.181',
			'items'   => array(
				__( 'Header-/Footer-Logo: Editor-Breite wird übernommen (feste CSS-Höhe entfernt).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.180',
			'items'   => array(
				__( 'Unsplash Credit: Bildnachweis im Footer und Impressum (FairFlock → Unsplash Credit).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.179',
			'items'   => array(
				__( 'Account-Seite: Inhaltsbreite (--ff-shell) korrigiert, Info-Banner ohne abgeschnittene Buttons.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.178',
			'items'   => array(
				__( 'Shop „Alle Produkte“: Filter-Seitenleiste breiter (bis 300px).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.177',
			'items'   => array(
				__( 'Produktpopup: lange Attribut-Titel umbrechen statt in Werte zu rutschen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.176',
			'items'   => array(
				__( 'WooCommerce CSV-Import: Fehlermeldung „Dateityp nicht erlaubt“ behoben (MIME-Erkennung).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.175',
			'items'   => array(
				__( 'Warenkorb: Innenabstand der Summen-Spalte korrigiert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.174',
			'items'   => array(
				__( 'Warenkorb-Layout: Zwei-Spalten-Grid und eine Summen-Karte (Woo 65/35-Breiten zurückgesetzt).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.173',
			'items'   => array(
				__( 'Navigation umschaltbar: schwebend (abgerundet) oder randlos (FairFlock → Navigation).', 'fairflock' ),
				__( 'System-Präsentation aktualisiert (Nav-Designs, Assistent, Layouts).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.172',
			'items'   => array(
				__( 'Startseite: drittes Vollbild vor der Drei-Schritte-Sektion.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.171',
			'items'   => array(
				__( 'Startseite: Text/Bild + Vollbild unter Produktslider; zweites Vollbild unter Kategorien.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.170',
			'items'   => array(
				__( 'Über-uns-Hero: Hintergrundbild auch im Frontend sichtbar (Gradient nur ohne Media).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.168',
			'items'   => array(
				__( 'Einrichtungs-Assistent (FairFlock → Einrichtung) inkl. Dashboard-Widget.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.167',
			'items'   => array(
				__( 'Demo Content: Überschreiben-Optionen direkt in jeder Paket-Zeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.166',
			'items'   => array(
				__( 'Footer: Migration auf hellen Standard (heller Hintergrund / dunkler Text).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.164',
			'items'   => array(
				__( 'Produktpopup: deutsche Produkt-URLs (/produkt/) werden erkannt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.161',
			'items'   => array(
				__( 'Über-uns-Layout + Demo-Paket; rechtliche Seiten mit FairFlock-Firmendaten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.160',
			'items'   => array(
				__( 'Warenkorb-, Konto- und Rechtsseiten-Layouts (Karten / Lesespalte).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.154',
			'items'   => array(
				__( 'Passwortschutz für den gesamten Shop (FairFlock → Passwortschutz).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.150',
			'items'   => array(
				__( 'Hero-/Footer-Farben an Global Styles gekoppelt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.2.0+',
			'items'   => array(
				__( 'Block-Theme-Grundlage: FSE, Patterns, WooCommerce-Shop, Suche, Warenkorb-Drawer, Personalisierung.', 'fairflock' ),
			),
		),
	);
}

/**
 * Whether a themes.php prepared slug is this theme.
 *
 * @since 0.9.182
 *
 * @param string $slug Theme stylesheet key.
 * @return bool
 */
function fairflock_is_theme_js_slug( $slug ) {
	$slug = (string) $slug;
	if ( 'fairflock' === $slug ) {
		return true;
	}

	$theme = wp_get_theme();
	return $slug === $theme->get_stylesheet() || $slug === $theme->get_template();
}

/**
 * Rich HTML for the Themes details modal description.
 *
 * @since 0.9.182
 *
 * @return string
 */
function fairflock_theme_details_description_html() {
	$parts   = array();
	$parts[] = '<p><strong>' . esc_html__( 'Performantes Block-Theme für FairFlock Multi-Tenant-WooCommerce-Shops.', 'fairflock' ) . '</strong></p>';
	$parts[] = '<p>' . esc_html__( 'Generische Platzhalter-Inhalte; Marke (Logo, Farben, Schriften) über Global Styles. Bearbeitung über Gutenberg und Site Editor. Ein-Klick-Demo-Layouts, Einrichtungs-Assistent, Nav-Designs, Unsplash-Credit und Passwortschutz unter FairFlock im Backend.', 'fairflock' ) . '</p>';
	$parts[] = '<p>' . esc_html__( 'Herausgeber:', 'fairflock' ) . ' <a href="https://rocketory.de" target="_blank" rel="noopener noreferrer">Rocketory.de</a> '
		. esc_html__( '(im Auftrag von FairFlock GmbH).', 'fairflock' ) . '</p>';
	$parts[] = '<p>' . esc_html__( 'Aktuelle Version:', 'fairflock' ) . ' <strong>' . esc_html( FAIRFLOCK_VERSION ) . '</strong></p>';

	$parts[] = '<div class="ff-theme-changelog"><h3 style="margin:1em 0 .5em;font-size:14px;">'
		. esc_html__( 'Versionshistorie', 'fairflock' ) . '</h3>';

	foreach ( fairflock_theme_changelog() as $entry ) {
		$parts[] = '<p style="margin:.75em 0 .25em;"><strong>' . esc_html( $entry['version'] ) . '</strong></p><ul style="margin:.25em 0 .75em 1.25em;">';
		foreach ( $entry['items'] as $item ) {
			$parts[] = '<li>' . esc_html( $item ) . '</li>';
		}
		$parts[] = '</ul>';
	}

	$parts[] = '<p style="margin-top:1em;opacity:.85;">'
		. esc_html__( 'Vollständige Liste auch unter FairFlock → Versionshistorie.', 'fairflock' )
		. '</p></div>';

	return implode( '', $parts );
}

/**
 * Enriches Appearance → Themes modal data for FairFlock.
 *
 * @since 0.9.182
 *
 * @param array<string, array<string, mixed>> $prepared Prepared themes.
 * @return array<string, array<string, mixed>>
 */
function fairflock_prepare_themes_for_js( $prepared ) {
	foreach ( $prepared as $slug => &$data ) {
		if ( ! fairflock_is_theme_js_slug( (string) $slug ) ) {
			continue;
		}

		$data['authorAndUri'] = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a> <span class="ff-theme-author-note">%3$s</span>',
			esc_url( 'https://rocketory.de' ),
			esc_html__( 'Rocketory.de', 'fairflock' ),
			esc_html__( '(im Auftrag von FairFlock GmbH)', 'fairflock' )
		);

		$data['description'] = fairflock_theme_details_description_html();
		$data['version']     = FAIRFLOCK_VERSION;
	}
	unset( $data );

	return $prepared;
}
add_filter( 'wp_prepare_themes_for_js', 'fairflock_prepare_themes_for_js' );

/**
 * Styles for the Themes overlay changelog block.
 *
 * @since 0.9.182
 *
 * @param string $hook Current admin page.
 * @return void
 */
function fairflock_theme_info_admin_assets( $hook ) {
	if ( 'themes.php' !== $hook ) {
		return;
	}

	$css = '
		.theme-overlay .theme-author .ff-theme-author-note { opacity: .75; font-weight: 400; }
		.theme-overlay .theme-description .ff-theme-changelog {
			max-height: min(42vh, 360px);
			overflow: auto;
			margin-top: 12px;
			padding: 12px 14px;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			background: #f6f7f7;
			font-size: 13px;
			line-height: 1.45;
		}
		.theme-overlay .theme-description .ff-theme-changelog ul { list-style: disc; }
	';
	wp_add_inline_style( 'common', $css );
}
add_action( 'admin_enqueue_scripts', 'fairflock_theme_info_admin_assets' );

/**
 * Registers Versionshistorie under FairFlock.
 *
 * @since 0.9.182
 *
 * @return void
 */
function fairflock_theme_changelog_register_admin_page() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Versionshistorie', 'fairflock' ),
		__( 'Versionshistorie', 'fairflock' ),
		'manage_options',
		'fairflock-changelog',
		'fairflock_theme_changelog_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_theme_changelog_register_admin_page', 35 );

/**
 * Renders FairFlock → Versionshistorie.
 *
 * @since 0.9.182
 *
 * @return void
 */
function fairflock_theme_changelog_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Versionshistorie', 'fairflock' ); ?></h1>
		<p>
			<?php
			printf(
				/* translators: 1: current version, 2: publisher link */
				esc_html__( 'Aktuell installiert: %1$s · Herausgeber: %2$s (im Auftrag von FairFlock GmbH).', 'fairflock' ),
				'<strong>' . esc_html( FAIRFLOCK_VERSION ) . '</strong>',
				'<a href="https://rocketory.de" target="_blank" rel="noopener noreferrer">Rocketory.de</a>'
			);
			?>
		</p>
		<?php foreach ( fairflock_theme_changelog() as $entry ) : ?>
			<h2 style="margin-top:1.5em;"><?php echo esc_html( $entry['version'] ); ?></h2>
			<ul style="list-style:disc;margin-left:1.5em;">
				<?php foreach ( $entry['items'] as $item ) : ?>
					<li><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
	<?php
}
