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
			'version' => '0.9.553',
			'items'   => array(
				__( 'Shop: WooCommerce „Coming Soon“ bleibt dauerhaft aus — Katalog/Kategorien wieder sichtbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.552',
			'items'   => array(
				__( 'Logo bleibt über dem Mega-Menü: Header-z-index nicht mehr auf Panel-Niveau absenken.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.551',
			'items'   => array(
				__( 'Suche: Ergebnis-Panel hält „Alle Ergebnisse“ im Glas; Hintergrund scrollt nicht mit.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.550',
			'items'   => array(
				__( 'Shop: Standardsortierung „Neueste zuerst“ (Nach Aktualität / date desc).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.549',
			'items'   => array(
				__( 'Shop-Filter mobil: PopUp wird nach body portiert — liegt über Navbar/Logo mit Backdrop.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.548',
			'items'   => array(
				__( 'Shop-Filter mobil: öffnet als zentriertes PopUp mit Backdrop (nicht mehr als Drawer unter der Navbar).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.547',
			'items'   => array(
				__( 'Logo bleibt über dem Mega-Menü (Header über Panel-Stacking).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.546',
			'items'   => array(
				__( 'Nur Countdown-PopUp: Schließen erst nach 3 Sekunden; Produkt-Quickview wieder sofort schließbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.545',
			'items'   => array(
				__( 'Nav: Burger früher bei Logo-mittig — Menüpunkte nicht mehr hinter dem Logo.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.544',
			'items'   => array(
				__( 'Suche: Logo bleibt oben (kein Abschneiden); Nav hoverbar — Mega-Menü schließt die Suche.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.543',
			'items'   => array(
				__( 'Produkt-PopUp: Schließen erst nach 3 Sekunden (Backdrop/✕/Escape; kein Sofort-Wegklicken).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.542',
			'items'   => array(
				__( 'Shop/Listen: Content hängt nicht mehr unter der Navbar (Clearance transform-sicher; kein Filter auf wp-site-blocks).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.541',
			'items'   => array(
				__( 'Suche mobil: Header slided beim Schließen von oben wieder ein.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.540',
			'items'   => array(
				__( 'Suche mobil: Header ausgeblendet; Ergebnis-Panel oben rund + 1em Abstand zur Suchleiste.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.539',
			'items'   => array(
				__( 'Produkt-PopUp: Schließen (✕, Escape, Backdrop) erst nach 2 Sekunden möglich.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.538',
			'items'   => array(
				__( 'Suchvorschläge: Produktkarten-Schrift in Contrast/Muted wie Shop — nicht Glass-Ink.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.537',
			'items'   => array(
				__( 'Suche: Navbar bleibt scharf und sichtbar; Blur/Overlay nur unter der Header-Leiste.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.536',
			'items'   => array(
				__( 'Suche: bei Formen „Eckig“ ist die Suchleiste (inkl. Morph/Close) ohne Rundung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.535',
			'items'   => array(
				__( 'Suchvorschläge: Produktkarten wie Shop/Slideshow (Bild, Preis, MwSt-Hinweis, Lieferzeile).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.534',
			'items'   => array(
				__( 'Suche: Overlay blendet beim Schließen sofort per Opacity (100→0) aus, ohne späten Ruckler.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.533',
			'items'   => array(
				__( 'Suchergebnisse: oben eckig (ohne Rundung), unten rund — schließt optisch an die Navbar an.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.532',
			'items'   => array(
				__( 'Suche: kein Ghost-Kreis mehr nach dem Schließen (Glass-Pill/Close-Hintergrund löst sich mit auf).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.531',
			'items'   => array(
				__( 'Suche: kein Ghost-✕ mehr nach dem Schließen (Close nur bei offener Suche sichtbar).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.530',
			'items'   => array(
				__( 'Header-Nav: sobald die Menüpunkte in eine zweite Zeile umbrechen würden, erscheint der Burger statt der Text-Nav.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.529',
			'items'   => array(
				__( 'Suche: Morph aus der Lupe in der Navbar; bei „Randlos“ bleibt Header scharf, Leistenbreite stoppt vor Logo/Nav.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.528',
			'items'   => array(
				__( 'Suche bei Nav „Randlos“: Panel unter dem Header, Logo/Nav bleiben frei; Overlay unter der Navbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.527',
			'items'   => array(
				__( 'Suche: kein Box-Shadow an der Leiste; kein Ghost-✕ beim Schließen; Nav „Randlos“: Header bleibt sichtbar (Overlay unter der Navbar).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.526',
			'items'   => array(
				__( 'Suche Desktop: Lupe morph’t zur Suchleiste (Expand nach links) und kollabiert wieder in die Lupe.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.525',
			'items'   => array(
				__( 'Hero-Slider: Aktiver Dot immer weiß (nicht Theme-Surface) — bleibt auf dunklen Hero-Bildern erkennbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.524',
			'items'   => array(
				__( 'Suche Desktop: Schließen-X sitzt pixelgenau auf der Lupe; Collapse-Animation endet bündig am Such-Icon.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.523',
			'items'   => array(
				__( 'Suche: Dunkles Glassmorphism wie Untermenüs; Suchleiste vertikal auf Icon-Höhe (Desktop & Mobil).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.522',
			'items'   => array(
				__( 'Nav-Icons: Bei Form „Abgerundet“ sind Hover-Flächen von Suche/Warenkorb kreisrund.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.521',
			'items'   => array(
				__( 'Nav-Icons: Desktop-Hover; Suchleiste auf Icon-Höhe; Lupe dreht/skaliert nach dem Schließen der Suche wieder ein.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.520',
			'items'   => array(
				__( '404: zentriertes Layout mit Suche und CTAs; Passwort zurücksetzen: Karten-Layout, lesbare Felder und Brand-Button (auch auf dunklem Shop).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.519',
			'items'   => array(
				__( 'Nav-Icons: Suche/Warenkorb größer; mehr Abstand zwischen den Icons, besonders Warenkorb → Profil — gilt für alle Nav-Layouts.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.518',
			'items'   => array(
				__( 'Darstellung: Glassmorphism Hell/Dunkel für Suchleiste, Vorschläge und Overlay umschaltbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.517',
			'items'   => array(
				__( 'Suche: Schließen-X fade/scale am Animationsende auch bei Hover (kein abruptes Verschwinden mehr).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.516',
			'items'   => array(
				__( 'Mega-Menü: Innenabstand und Spalten-Abstand aus dem Site-Editor greifen im Frontend (kein !important-Override mehr).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.515',
			'items'   => array(
				__( 'Suche: Ausblende-Animation fade/scale am Ende für den Rest der Leiste inkl. Schließen-X.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.514',
			'items'   => array(
				__( 'Suche-Vorschau: Produktnamen mit Anführungszeichen und Sonderzeichen werden korrekt angezeigt (HTML-Entities dekodiert).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.513',
			'items'   => array(
				__( 'Suche: Overlay in Theme-Hintergrundfarbe (60 %) plus weichgezeichneter Seitenhintergrund; moderne Pill-Suchleiste; Vorschläge erst nach Eingabe; Ein-/Ausklappen vom Such-Icon.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.512',
			'items'   => array(
				__( 'Pop-up: Design unter FairFlock → Pop-up einstellbar (Hintergrund, Akzent, Textfarbe) mit Live-Vorschau; Standard wieder dunkel mit heller Schrift.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.511',
			'items'   => array(
				__( 'Trust-Banner/Marquee: Farben aus FairFlock → Darstellung greifen zuverlässig; Markenfarben und Block-Verläufe überschreiben den Banner nicht mehr.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.510',
			'items'   => array(
				__( 'Pop-up: Enddatum wird zuverlässig gespeichert (Browser-Werte mit Sekunden); Statusanzeige im Backend, warum es sichtbar/unsichtbar ist.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.509',
			'items'   => array(
				__( 'Pop-up: erscheint sofort (Standard-Verzögerung 0 Sekunden); Admin-Vorschau ebenfalls ohne Wartezeit.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.508',
			'items'   => array(
				__( 'Pop-up: drehender Handball im Hintergrund (SVG mit 3D-Eindruck: rotierende Nähte, Lichtreflex, sanftes Schweben; steht still bei „Bewegung reduzieren“).', 'fairflock' ),
				__( 'Pop-up: Einblenden auch in Hintergrund-Tabs zuverlässig; Countdown-Kacheln deckender für bessere Lesbarkeit.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.507',
			'items'   => array(
				__( 'Neu: Pop-up mit Countdown unter FairFlock → Pop-up — Enddatum, Titel, Text, Abschluss-Text, optionaler Button, Verzögerung und Häufigkeit einstellbar; Admin-Vorschau.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.506',
			'items'   => array(
				__( 'Header „Logo mittig“: Suche und Warenkorb verschwanden — lose Icons aus gespeicherten Headern werden in die Icon-Gruppe übernommen statt übereinander zu liegen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.505',
			'items'   => array(
				__( 'Impressum generierbar: neues Paket „Impressum generieren“ unter FairFlock → Demo Content (FairFlock GmbH, USt-IdNr., Handelsregister, Geschäftsführer, News, Streitbeilegung, Karriere).', 'fairflock' ),
				__( 'Suche (Desktop): Panel öffnet rechtsbündig direkt in der Navigation und überdeckt das Such-Icon.', 'fairflock' ),
				__( 'Suche: dezente Border um das Eingabefeld, beim Fokus etwas kräftiger.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.504',
			'items'   => array(
				__( 'Header: Profil-Icon übernimmt im Editor gesetzte Text-, Hintergrund- und Rahmenfarben auch im Frontend.', 'fairflock' ),
				__( 'Header: Profil-Icon ohne eigene Farbe ist dunkel auf hellem Kreis — auch bei dunkler Navbar sichtbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.503',
			'items'   => array(
				__( 'Rechtsseiten und Konto: doppelter Abstand unter der Navbar entfernt (Body erbte Header-Abstand und Verlauf).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.502',
			'items'   => array(
				__( 'Leerer Warenkorb neu: Hinweis mit „Weiter einkaufen“ und volle Produktreihe „Neu im Shop“ statt gequetschter Karte.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.501',
			'items'   => array(
				__( 'Darstellung: Checkout zwischen Hell und Dunkel umschaltbar.', 'fairflock' ),
				__( 'Checkout: Kopfbereich „Vollende deine Bestellung“ mit leicht abgesetztem Hintergrund.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.500',
			'items'   => array(
				__( 'Checkout mit Germanized: nur noch ein „Jetzt kaufen“-Button, Rechtstext-Checkboxen direkt darüber in der rechten Spalte.', 'fairflock' ),
				__( 'Checkout: Germanized-AGB-Checkbox und Fehlermeldung auf Deutsch.', 'fairflock' ),
				__( 'Bestellübersicht: Produktbild oben mittig im 1:1-Rahmen, Mengen-Badge lesbar, keine Linie und keine graue Fläche mehr.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.499',
			'items'   => array(
				__( 'Checkout komplett neu aufgebaut: eigenes, theme-unabhängiges Design, 8-Punkt-Raster, keine Trennlinien.', 'fairflock' ),
				__( 'Checkout: Formular links, Kaufen-Spalte rechts (Übersicht, Gutschein, Summe, AGB, „Jetzt kaufen“, Vertrauenspunkte).', 'fairflock' ),
				__( 'Bestellbestätigung: Styles in eigene Datei order-confirmation.css ausgelagert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.498',
			'items'   => array(
				__( 'Checkout: doppelte Bestellübersicht entfernt, nur die volle Übersicht bleibt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.497',
			'items'   => array(
				__( 'Checkout: Abschnittskarten, Trennlinien und Summary-Rahmen entfernt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.496',
			'items'   => array(
				__( 'Checkout: Abstand zwischen Radio, Versandname und Preis.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.495',
			'items'   => array(
				__( 'Checkout: leere Rahmen über den Kontaktinformationen entfernt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.494',
			'items'   => array(
				__( 'Suche: keine Extra-Rahmen an Eingabe, Produktvorschlägen und Fußzeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.493',
			'items'   => array(
				__( 'Suche Desktop: Beliebt und vorgeschlagene Produkte untereinander, nicht nebeneinander.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.492',
			'items'   => array(
				__( 'Checkout: Versand- und Zahlungszeilen ohne Extra-Rahmen, Gesamtsumme in der Summary; leerer Warenkorb als Karte.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.491',
			'items'   => array(
				__( 'Bestellbestätigung: Inhalt in einer Karte, Meta-Zeile, Tabelle und Adressen mit einheitlichen Abständen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.490',
			'items'   => array(
				__( 'Checkout: feste Content-Maxbreite; Lieferung wieder im Karten-Layout; Zahlungsoptionen entschachtelt; PayPal-Button volle Breite.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.489',
			'items'   => array(
				__( 'Account-Pop-up (Gast): Anmelden-Button gegen Theme-/Brand-Styles abgesichert, Layout mit „Angemeldet bleiben“ korrigiert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.488',
			'items'   => array(
				__( 'Mobil: Bild-Zoom im Produkt-Pop-up und auf der Produktseite wieder aktiv (Tap zum Zoomen).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.487',
			'items'   => array(
				__( 'Checkout mobil: AGB-Checkbox direkt über „Jetzt kaufen“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.486',
			'items'   => array(
				__( 'Account-Pop-up (Gast): neutrale Trust-Farben, klareres Login-Layout, keine Theme-/Brand-Farbüberschreibungen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.485',
			'items'   => array(
				__( 'Footer mobil: 1em Randabstand; engere Link-Zeilen, kein Uppercase.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.484',
			'items'   => array(
				__( 'Checkout: Intro „Vollende deine Bestellung“ als dezenter Seiten-Header mit leicht anderem Hintergrundton.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.483',
			'items'   => array(
				__( 'Such-Pop-up: Vorgeschlagene Produkte Desktop 2 pro Zeile (wie Mobil).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.482',
			'items'   => array(
				__( 'Checkout: Abstand zwischen „Vollende deine Bestellung“ und Kontaktinformationen verringert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.480',
			'items'   => array(
				__( 'FairFlock → Navigation: individuelle Menüpunkte mit Titel und Link anlegen, sortieren und bearbeiten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.479',
			'items'   => array(
				__( 'Rechtsseiten: Abstand unter der Nav; helle Header-Leiste mit Custom-Hintergrund bekommt dunkle Menüfarbe.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.478',
			'items'   => array(
				__( 'Bestell-E-Mails: Produktbilder im 1:1-Rahmen oben mittig (wie Shop-Thumbnails).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.477',
			'items'   => array(
				__( 'Größentabelle: Info unter der Tabelle; Rich-Text-Editor (Überschriften, Fettung, Listen) in der Vorlage.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.476',
			'items'   => array(
				__( 'Checkout-Summary: doppelte Borders/Padding entfernt, einheitliche Innenabstände, schlankere Trenner.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.475',
			'items'   => array(
				__( 'Mobile Checkout: klarere Reihenfolge (Jetzt kaufen zuletzt), weniger Überladen, Text bleibt in den Cards.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.474',
			'items'   => array(
				__( 'Bestellnummern: #Anhängsel zuverlässig (Store-API/Blocks, Filter spät, Basis = Order-ID).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.473',
			'items'   => array(
				__( 'Darstellung: Produktkarten-Hover — nur Zoom oder Zoom + Galerie-Bildwechsel.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.472',
			'items'   => array(
				__( 'Desktop-Header: Suche + Warenkorb wieder sichtbar links neben dem Profil (Glyph-Farbe folgt der Leiste, feste Reihenfolge).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.471',
			'items'   => array(
				__( 'Bestellnummern: Anhängsel mit „#“ (z. B. 12345#AH001).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.470',
			'items'   => array(
				__( 'Checkout AGB: sichtbare Checkbox, korrekte Abstände, deutsche Fehlermeldung (Germanized/Woo).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.469',
			'items'   => array(
				__( 'Checkout: keine Shop-Akzentfarbe; Lieferung Versand/Abholung lesbar; Rot nur bei Fehlern.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.468',
			'items'   => array(
				__( 'Kollektionen: weniger Blur am Hintergrundbild; mobil immer 2 Karten im Slider.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.467',
			'items'   => array(
				__( 'Desktop-Header: Suche, Warenkorb und Konto wieder rechts in einer Zeile (Logo-mittig-Layout).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.466',
			'items'   => array(
				__( 'Kollektionen: Schalter „Weichgezeichnetes Hintergrundbild“ immer sichtbar unter „Nur Titel“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.465',
			'items'   => array(
				__( 'Produktkarten: weniger Innenabstand unten unter der Lieferzeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.464',
			'items'   => array(
				__( 'Header: Abstand zwischen Suche und Warenkorb enger.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.463',
			'items'   => array(
				__( 'Kollektionen „Nur Titel“: ohne sichtbaren Link, Hover-Effekt, Option weichgezeichnetes Kategoriebild.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.462',
			'items'   => array(
				__( 'Produktkarten: „Ansehen“ ist klickbar; Hover-Lift (nach oben) entfernt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.461',
			'items'   => array(
				__( 'Suche Desktop: Panel dockt rechtsbündig unter der Lupe (wie Account-Popup).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.460',
			'items'   => array(
				__( 'Header: Abstand zwischen Lupe und Warenkorb verringert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.459',
			'items'   => array(
				__( 'Cover verlinkbar per Toolbar-Link + Zoom-In-Hover (ffMegaLink).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.458',
			'items'   => array(
				__( 'Mega-Menü Cover: Link-Feld in der Block-Seitenleiste (WP 7 hat kein Cover-Link-Icon).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.457',
			'items'   => array(
				__( 'Mobile Header: Burger näher am linken Rand, mehr Abstand zwischen Burger und Logo.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.456',
			'items'   => array(
				__( 'Header-Icons: Abstand zwischen Suche, Warenkorb und Konto enger (4px) für einheitlicheres Bild.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.455',
			'items'   => array(
				__( 'Account-Popup: Trennstrich über „Konto-Seite öffnen“ greift zuverlässig (volle Breite, 1em Extra-Abstand).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.454',
			'items'   => array(
				__( 'Mega-Menü: Bilder/Cover verlinkbar; bei Link Zoom-In-Hover auf dem Bild.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.453',
			'items'   => array(
				__( 'Account-Popup: Trennstrich über „Konto-Seite öffnen“ inkl. 1em Extra-Abstand darüber.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.452',
			'items'   => array(
				__( 'Header-Icons: schwarze Quadrate behoben (mask-Shorthand hat mask-image überschrieben).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.451',
			'items'   => array(
				__( 'Warenkorb-Drawer mobil/Tablet: öffnet auf 90% der Bildschirmbreite.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.450',
			'items'   => array(
				__( 'Header: Burger wird links neben das Logo gesetzt (auch bei gespeichertem Site-Editor-Header).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.449',
			'items'   => array(
				__( 'Header-Icons: Theme-Button-Grün überschreibt Suche/Warenkorb/Konto nicht mehr; Glyphs wieder sichtbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.448',
			'items'   => array(
				__( 'Account-Popup: auf allen Geräten rechtsbündig unter dem Profil-Icon angedockt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.447',
			'items'   => array(
				__( 'Header: Profil-Icon als eigener Button (hellgrauer Kreis) ganz rechts — immer sichtbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.446',
			'items'   => array(
				__( 'Header mobil: Burger-Icon links neben dem Logo.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.445',
			'items'   => array(
				__( 'Header: Profil-Icon als hellgrauer Kreis ganz rechts; Warenkorb-Icon-Farbe mobil wie Suche/Burger.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.444',
			'items'   => array(
				__( 'FairFlock → Bestellnummern: Prefix + laufende Zahl (Schema AH001) für alle neuen Bestellungen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.443',
			'items'   => array(
				__( 'Produkt-Popup: Close-Icon fest oben im Viewport (safe-area), rutscht nicht mehr aus dem Bildschirm.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.442',
			'items'   => array(
				__( 'Touch-Zoom: Pan-Richtung umgekehrt zur Fingerbewegung (wie Karten-Ziehen).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.441',
			'items'   => array(
				__( 'Mobil/Tablet: Burger öffnet das Mega-Menü (kein separates Menü-Overlay); Account- und Warenkorb-Icons in der Header-Leiste.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.440',
			'items'   => array(
				__( 'Produkt-Popup mobil: Galerie scrollt mit; Touch-Drag verschiebt die Slideshow frei (Snap beim Loslassen).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.439',
			'items'   => array(
				__( 'Checkout-Trust: Schloss-, Store- und Paket-Icons bei allen drei Punkten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.438',
			'items'   => array(
				__( 'Checkout-Trust: „Versand per DHL“ mit Paket-Icon statt „Klarer Preis inkl. MwSt.“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.437',
			'items'   => array(
				__( 'Checkout: Titel und Formular/Summary teilen dieselbe Inhaltsbreite (--ff-shell).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.436',
			'items'   => array(
				__( 'Formen eckig: Produkt-Popup und mobile Preiscard ohne obere Rundung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.435',
			'items'   => array(
				__( 'Größentabelle-Vorlagen: Beschreibungsfeld im Backend; Speichern löscht keine anderen Vorlagen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.434',
			'items'   => array(
				__( '„Ansehen“: Typo wie Header-Nav (Caps, 0.8125rem/500, muted, Brand-Hover).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.433',
			'items'   => array(
				__( 'Produkt-Popup: „Zum Produkt“ statt „Zur Seite“; PDP-Galerie: gleiche Slide-Höhe (Cover, Hero-CSS-Konflikt behoben).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.432',
			'items'   => array(
				__( 'Mobiles Produkt-Popup: Close-Icon sitzt oben über dem Sheet (nicht auf dem Bild).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.431',
			'items'   => array(
				__( 'Produktkarten „Ansehen“: Design wie Nav-Menüpunkt (Typo, Trennlinie, Chevron, Hover).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.430',
			'items'   => array(
				__( 'Mobil-Galerie: Letztes Bild bleibt rechts angedockt (kein Leerraum rechts).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.429',
			'items'   => array(
				__( 'Produktkarten: „Ansehen“ vollbreit wie Menüpunkt, mit ausfadendem Card-Schatten über zzgl./Lieferung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.428',
			'items'   => array(
				__( 'Mobil: Bei aktivem Bild-Zoom kein versehentliches Weiterwischen der Slideshow.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.427',
			'items'   => array(
				__( 'Mobil: Produkt-Slideshow mit einheitlicher Höhe — alle Bilder füllen den Slider (kein Graustreifen).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.426',
			'items'   => array(
				__( 'Checkout: Titel „Vollende deine Bestellung“, ausgerichtet auf Formularbreite.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.425',
			'items'   => array(
				__( 'Checkout: AGB-Checkbox, Liefermethode/Felder/Hinweis in Theme-Grün, englische Infos aus, keine Card-Schatten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.424',
			'items'   => array(
				__( 'Mobil: Produktbilder im Pop-up und auf der Produktseite per Tippen zoomen; „Vergrößern“-Hinweis oben rechts.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.423',
			'items'   => array(
				__( 'Mobil: Produktbilder in Pop-up und auf der Produktseite in Originalhöhe (kein gestauchter Graukasten).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.422',
			'items'   => array(
				__( 'Produktkarten: „Ansehen“ erscheint bei Hover unten in der Card, nicht über dem Bild.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.421',
			'items'   => array(
				__( 'Produktbilder: Hover-Zoom folgt dem Mauszeiger (Karten, PDP, Modal) — Lupe-Effekt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.420',
			'items'   => array(
				__( 'Produktkarten standardmäßig 1:1 (oben mittig); geöffnete Produkte Originalformat; Widget-Option 2:3 pro Sammlung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.419',
			'items'   => array(
				__( 'Mobile-Menü immer bündig unter der Nav (gemessene Unterkante, kein Überlappen dahinter).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.418',
			'items'   => array(
				__( 'Fullbleed-Sektionen: „Hintergrund fixiert“ greift wieder im Frontend (overflow + Cover-Parallax).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.417',
			'items'   => array(
				__( 'Produkt-Slider mobil: Navigation 1em unter den Cards; „Alle …“ links, Pfeile rechts.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.416',
			'items'   => array(
				__( 'Hero-Slideshow mobil/Tablet: eingestellte Bilder korrekt als Cover (kein background-attachment:fixed).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.415',
			'items'   => array(
				__( 'Produktkarten: Hover-Lift schneidet die obere Rundung nicht mehr ab.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.414',
			'items'   => array(
				__( 'Suche-, Account- und Checkout-UI folgen Theme-Base/Contrast (kein festes Weiß mehr).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.413',
			'items'   => array(
				__( 'Shop mobil: kein Horizontal-Overflow; Filter-Drawer ohne Overflow; „Schließen“ über „Anwenden“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.412',
			'items'   => array(
				__( 'Navbar: kein Schrumpfen beim Scrollen; immer deckend (kein Glas/Blur); Hinweis unter Darstellung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.411',
			'items'   => array(
				__( 'Mobil-Menü: Chevron und „Zurück“ mit 1rem Abstand zum rechten Bildschirmrand.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.410',
			'items'   => array(
				__( 'Shop: Abstand Navbar→Breadcrumbs max. ~2em; „Produkte filtern“→Produkte 1em.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.409',
			'items'   => array(
				__( 'Mobil-Menü: Touch-Hitbox und Trenner vollbreit bis zum Bildschirmrand.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.408',
			'items'   => array(
				__( 'Shop mobil: Innenabstand wirklich 1em (kein doppeltes Root-/Shell-Padding; Filter-Drawer ebenfalls).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.407',
			'items'   => array(
				__( 'Mobil-Menü: Untermenü slide-in/out; Zurück-Pfeil links; Überpunkt und Menüpunkte größer.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.406',
			'items'   => array(
				__( 'Shop: Standard 2 Spalten; mobil Ansicht/Sortierung nur in „Produkte filtern“; Innenabstand 1em.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.405',
			'items'   => array(
				__( 'Mobil-Menü: kein Trennstrich über dem ersten Punkt; Menüpunkte größer (1.125rem).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.404',
			'items'   => array(
				__( 'Mobil-Untermenü: ohne Trennlinien, dichter, Schriftgewicht 600.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.403',
			'items'   => array(
				__( 'FAQ: Plus-Indicator optisch höher ausgerichtet.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.402',
			'items'   => array(
				__( 'Mobil-Menü: ohne Caps; Account/Warenkorb unten; Suche- und Burger-Icons größer.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.401',
			'items'   => array(
				__( 'Mobil-Menü: Überpunkt links, „Zurück“ rechts; Mega-Panel 1:1 auf der Unterseite.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.400',
			'items'   => array(
				__( 'Suche: mehr Abstand über „Vorgeschlagene Kategorie“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.399',
			'items'   => array(
				__( 'Slider: Navigation zuverlässig unter den Cards (DOM nach Track, CSS-Grid, Legacy-Relocate).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.398',
			'items'   => array(
				__( 'Mobil-Menü: Vollbreite unter dem Header; Burger wird zum Close; Suche bleibt; Untermenüs als Seite mit „Zurück“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.397',
			'items'   => array(
				__( 'Touch/Mobil: Slider-Navigation (Pfeile + „Alle Artikel“) unter die Produktkarten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.396',
			'items'   => array(
				__( 'Mobil-Menü: Rocketory-Drawer mit Akkordeon-Untermenüs; Breakpoint und Mega-Navigation korrigiert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.395',
			'items'   => array(
				__( 'Suche: Label „Vorschläge“ → „Vorgeschlagene Kategorie: T-Shirt\'s“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.394',
			'items'   => array(
				__( 'Mobil: Footer- und Hero-Inhalt mit 1em Abstand links/rechts (wie übrige Mobile-Ränder).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.393',
			'items'   => array(
				__( 'Mobil: Suche (Leiste + Vorschläge) vom Menü getrennt; Menü als Vollbild-Nav unter dem Header (Rocketory/Grün-Layout).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.392',
			'items'   => array(
				__( 'Footer: engere Abstände zwischen Link-Zeilen innerhalb der Spalten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.391',
			'items'   => array(
				__( 'Produktseite mobil: Abstand zwischen Beschreibung/Akkordeon und Related-Sektion wiederhergestellt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.390',
			'items'   => array(
				__( 'Produktseite mobil: Galerie bündig unter dem Header (kein Clearance-/BlockGap-Luftpolster).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.389',
			'items'   => array(
				__( 'Produktseite mobil: Titel und Text denselben linken Rand (Pad in rem statt em).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.388',
			'items'   => array(
				__( 'Produkt-Popup mobil: Galeriehöhe begrenzt, damit Titel/Preis/Aktionen unter den Bildern sichtbar bleiben.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.387',
			'items'   => array(
				__( 'Produkt-Popup mobil: Galerie wieder horizontal (Peek-Slider statt gestapelter Bilder).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.386',
			'items'   => array(
				__( 'KI-Bild-Tag transparenter (Hintergrund von 32 % auf 16 %).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.385',
			'items'   => array(
				__( 'Produktseite: Galerie-/Produktbilder folgen Formen „Abgerundet“ (Radius-Token; bei Eckig 0).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.384',
			'items'   => array(
				__( 'Shop: Breadcrumbs immer Caps; Filter-Akkordeon 4px Abstand; Ergebnisanzahl/Ansicht/Sortierung in der linken Filter-Spalte.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.383',
			'items'   => array(
				__( 'Kollektionen-Widget: Option „Nur Titel (ohne Bilder)“ — zentrierte H2-Titel statt Kategoriebilder.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.382',
			'items'   => array(
				__( 'Text-Links siteweit wie Navbar-Menüpunkte: Caps, Schriftgewicht 500, animierter Underline (u. a. Alle Produkte, Suche, Account-Popup/-Seite, Footer).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.381',
			'items'   => array(
				__( 'Lade-Overlay: keine Endlosschleife mehr bei Shop-Ansicht/Spalten; Overlay nur bei echten Filter-/Seitenwechseln.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.380',
			'items'   => array(
				__( 'Chevron-SVG auch in gespeicherten Template-Parts/Startseite; Runtime-Upgrade für übrige ←/→-Pfeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.379',
			'items'   => array(
				__( 'Suche-, Account- und Checkout-UI monochrom (theme-unabhängig); Rot/Grün nur für Fehler/Erfolg; Checkout-Produktbilder 2:3.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.378',
			'items'   => array(
				__( 'Ladekreis bei Produkt-Popup, Shop-Seitenwechsel/Filtern, Suche und Warenkorb-Updates.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.377',
			'items'   => array(
				__( 'Such-Popup: Beliebt-/Fußlinks wie Navbar-Menüpunkte; mehr Abstand unter der Suchleiste.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.376',
			'items'   => array(
				__( 'Einheitlicher Stroke-Chevron (Größentabelle) für Galerie-, Hero-, Slider-, Filter- und Tabellen-Pfeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.375',
			'items'   => array(
				__( 'Darstellung: Trust-Banner-Verlauf mit Live-Vorschau und drei Farbfeldern bearbeitbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.374',
			'items'   => array(
				__( 'Trust-Banner: Layout und Brand-Verlauf wie Natürlich von Grün Marquee (Vollbreite, Glanz, ·-Trenner).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.373',
			'items'   => array(
				__( 'Nav: Icon-Radien folgen Formen; Navbar nur bei Abstand zum Rand rund; Account-Icon skaliert beim Scrollen mit.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.372',
			'items'   => array(
				__( 'Desktop-Nav: aktiver Menüpunkt ohne Underline, nur 60% Opacity.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.371',
			'items'   => array(
				__( 'Desktop-Nav: Menüpunkte etwas kleiner (0.8125rem).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.370',
			'items'   => array(
				__( 'Desktop-Nav: Trennstriche zu Punkten; mehr Abstand zwischen Menüpunkten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.369',
			'items'   => array(
				__( 'Desktop-Nav: animierter Hover (Underline scaleX + Brand-Farbe).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.368',
			'items'   => array(
				__( 'Desktop-Nav: Menüpunkte in der Leiste in Großbuchstaben (Untermenüs unverändert).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.367',
			'items'   => array(
				__( 'Shop-Filter: Hover-Effekt auf Akkordeon-Items (Rahmen, Hintergrund, Brand-Text).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.366',
			'items'   => array(
				__( 'Desktop-Nav: Menüpunkte ein Gewicht dicker (font-weight 500).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.365',
			'items'   => array(
				__( 'Desktop-Nav: Menüpunkte leichter (font-weight 400); Trennstriche kürzer, enger und dezenter.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.364',
			'items'   => array(
				__( 'KI-Bild-Tag etwas transparenter.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.363',
			'items'   => array(
				__( 'Shop: engere Filter-Akkordeons; schlankere Navbar; Sticky-Filter bleibt unter der Nav (kein Überdecken).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.362',
			'items'   => array(
				__( 'Produkt-Popup: Close-Icon in der Ecke; Titel läuft nicht mehr darunter.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.361',
			'items'   => array(
				__( 'Shop-Filter: Abstände zwischen Akkordeon-Items verringert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.360',
			'items'   => array(
				__( 'Produktbilder: „KI-Bild“ im Backend pro Bild aktivierbar; minimalistischer Tag auf dem Bild im Shop/Popup.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.359',
			'items'   => array(
				__( 'Warenkorb: Mengen-Zahl editierbar (kein Verdoppeln mehr beim Klick); +/- weiterhin verfügbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.358',
			'items'   => array(
				__( 'Mobile Produktgalerie: kleinere Pfeile; letztes Bild bündig am rechten Screenrand (kein Leerraum danach).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.357',
			'items'   => array(
				__( 'Größentabelle-Popup mobil wie Desktop (kompaktes Overlay); Tabelle horizontal scrollbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.356',
			'items'   => array(
				__( 'Mobile Produktseite: kein Abstand Navbar–Bild; Text/Titel mit 1em Randabstand.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.355',
			'items'   => array(
				__( 'Produktseite: Related-/Kategorie-Bereich unten wieder erzwingen; Titel immer = Produktkategorie.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.354',
			'items'   => array(
				__( 'Produktseite: Größentabelle und „Weitere Informationen“ in der mittleren Amazon-Spalte.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.353',
			'items'   => array(
				__( 'Shop Spalten-Dropdown: gewählte Spalte mit lesbarem Kontrast (Brand-Hintergrund, weiße Schrift).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.352',
			'items'   => array(
				__( 'Shop-Filter: Kategorie-Chips eckig bei Design „Eckig“, Schrift weniger fett; mehr Abstand über „+ X mehr“; Akkordeons mit leichter Umrandung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.351',
			'items'   => array(
				__( 'Warenkorb-Drawer: Klick auf Produktname/Bild führt zuverlässig zur Produktseite.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.350',
			'items'   => array(
				__( 'Mobile Produktseite: Titel ohne linken Einschub.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.349',
			'items'   => array(
				__( 'Mobile Produktgalerie: randlos an Navbar/Screen; doppelte Slideshow behoben; Original-Woo ausgeblendet.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.348',
			'items'   => array(
				__( 'Dein Design: Upload auf ein Feld reduziert — Dokument-Icon + „{Begriff} wählen“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.347',
			'items'   => array(
				__( '„Mehr von dieser Art“: Titel = Produktkategorie; Desktop 5 / Tablet 4 / Mobile 1,5 Spalten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.346',
			'items'   => array(
				__( 'Warenkorb-Drawer: Menge per −/+ ändern, Produkt per Mülleimer entfernen, Produktname verlinkt; „Menge X“ entfernt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.345',
			'items'   => array(
				__( 'Amazon-Buy-Sidebar: Größe-/Design-Cards heben sich deutlich weniger vom Hintergrund ab.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.344',
			'items'   => array(
				__( 'Akkordeon-Chevron (Größentabelle / Weitere Informationen): Abstand oben, unten und rechts einheitlich; optisch zentriertes Icon.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.343',
			'items'   => array(
				__( 'Mobile Pop-Up/Produktseite: Galerie zeigt 1,5 Bilder randlos (ohne Thumbnails), moderne Chevron-Pfeile; Produktseite-Galerie bündig an Navbar und Screenränder.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.342',
			'items'   => array(
				__( 'Pop-Up/Produktseite: Größentabelle und „Weitere Informationen“ als gleiche Akkordeon-Zeilen (4px Abstand); Größentabelle oben; Mobile-Buy wie Pop-Up.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.341',
			'items'   => array(
				__( 'Produktkarten: statt Plugin-MwSt-/Versand-Fußnoten einheitlich „zzgl. Versandkosten | inkl. MwSt“ unter dem Preis.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.340',
			'items'   => array(
				__( 'Produktkarten: Erstbild füllt den 2:3-Rahmen per object-fit cover (wie Hover-Bild).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.339',
			'items'   => array(
				__( 'Produktseite: Kurzbeschreibung nur noch im Akkordeon „Weitere Informationen“, keine Doppelung in der Info-Spalte.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.338',
			'items'   => array(
				__( 'Produktmedien 2:3; Shop standardmäßig 4 Spalten; moderne Pagination; Filter als eingeklappte Akkordeons.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.337',
			'items'   => array(
				__( 'Produktseite: Amazon-Kauf-Sidebar auch bei angepassten 2-Spalten-Templates (render_block + Desktop-Grid).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.336',
			'items'   => array(
				__( 'Produktkarten: Hover wechselt zum zweiten Galeriebild.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.335',
			'items'   => array(
				__( 'Produkt-Popup mobil: Galerie zeigt Hauptbild und Thumbnails vollständig (kein Abschneiden).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.334',
			'items'   => array(
				__( 'Suche: engere Abstände über den Treffern und zum Link „Alle Ergebnisse anzeigen“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.333',
			'items'   => array(
				__( 'Verwandte Produkte: Titel „Mehr von dieser Art“, 1-Spalten-Slideshow mit Pfeilen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.332',
			'items'   => array(
				__( 'Akkordeon-Titel: „Weitere Informationen“ statt „Kurzbeschreibung“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.331',
			'items'   => array(
				__( 'Mobil: Produkttitel steht über dem Bildbereich.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.330',
			'items'   => array(
				__( 'Mobil: Abstand über „Konfigurieren“ entfernt (eingeklappte Config nimmt keinen Platz mehr).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.329',
			'items'   => array(
				__( 'Lieferzeit-Tooltip in der Kauf-Sidebar bleibt im Rand (unter der Zeile, volle Breite).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.328',
			'items'   => array(
				__( 'Fix: „Konfigurieren“-Button wieder sichtbar (außerhalb des Woo-Varianten-Warenkorb-Blocks).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.327',
			'items'   => array(
				__( 'Konfigurieren: Größe klappt erst mit Design/Warenkorb aus (Popup und Produktseite).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.326',
			'items'   => array(
				__( 'Kurzbeschreibung-Akkordeon: größerer Innenabstand und Ausklapp-Animation.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.325',
			'items'   => array(
				__( 'Produktseite mobil: gesamter Bestellbereich inkl. Preis unten fixiert; Konfigurieren wie im Produkt-Popup.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.324',
			'items'   => array(
				__( 'Produktkurzbeschreibung: immer als Akkordeon, standardmäßig eingeklappt (Popup und Produktseite).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.323',
			'items'   => array(
				__( 'Produktseite mobil: bei Personalisierung „Konfigurieren“; Größenauswahl immer über dem Hinzufügen-/Warenkorb-Button.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.322',
			'items'   => array(
				__( 'Kauf-Sidebar: Mengensteuerung und Warenkorb-Button kompakter, Button-Text in einer Zeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.321',
			'items'   => array(
				__( 'Dein Design: nur noch eine konfigurierbare Info-Zeile; doppeltes Feldlabel entfernt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.320',
			'items'   => array(
				__( 'Kauf-Sidebar: „Größe“ und „Dein Design“ jeweils als eigene innere Card.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.319',
			'items'   => array(
				__( 'Mobil: „Hinzufügen“/„Konfigurieren“ im Popup und Warenkorb-CTA auf der Produktseite unten fixiert.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.318',
			'items'   => array(
				__( 'Upload: erlaubte Dateiformate (PNG/JPG/PDF) pro Produkt im Backend wählbar; Menge modern mit −/+ links neben „In den Warenkorb“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.317',
			'items'   => array(
				__( 'Kauf-Sidebar: engere Abstände über Größe und Dein Design.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.316',
			'items'   => array(
				__( 'Kauf-Sidebar: Abschnitte Preis/Lager, Größe, Design und Warenkorb mit Trennlinien.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.315',
			'items'   => array(
				__( 'Produktseite: Kurzbeschreibung als eigene Card unter der Produktsektion.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.314',
			'items'   => array(
				__( 'Produktseite: Amazon-ähnliches Layout mit sticky Kauf-Sidebar rechts (Preis + Konfiguration), Infos in der Mitte.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.313',
			'items'   => array(
				__( 'Produktkarten überall wie Startseite: Lieferzeile linksbündig in einer Reihe, ohne Warenkorb-Button in Related.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.312',
			'items'   => array(
				__( 'Produktseite: Überschrift unter dem Produkt „Produkte aus dieser Kategorie“ statt „Ähnliche Produkte“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.311',
			'items'   => array(
				__( 'Produktseite: Größe, Design und Warenkorb als eine zusammenhängende Card wie im Popup.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.310',
			'items'   => array(
				__( 'Größentabelle-Button: schmaler, linksbündig und kleiner.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.309',
			'items'   => array(
				__( 'Produktseite/Popup: etwas mehr Abstand zwischen Preis/Lieferzeile und Beschreibung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.308',
			'items'   => array(
				__( 'Produktseite/Popup: engere Abstände zwischen Titel, Preis, Lagerzeile und Beschreibung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.307',
			'items'   => array(
				__( 'Produktseite: Galerie wieder als normales Hauptbild mit Thumbnails (Base-Farbe), ohne kaputtes Stapeln.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.306',
			'items'   => array(
				__( 'Produkt-Popup: Größe und „Konfigurieren“ wieder im unteren Bereich; neues Inhalts-Layout darüber bleibt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.305',
			'items'   => array(
				__( 'Produkt-Popup und Produktseite: gemeinsames Kauf-Layout (Titel, Preis+MwSt, Lager/Lieferzeit, Beschreibung, Kurzbeschreibung, Größentabelle, Produktinfos, Aktionen).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.304',
			'items'   => array(
				__( 'Produktseite: Slideshow-/Thumbnail-Hintergrund nutzt die eingestellte Base-Farbe statt festem Weiß.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.303',
			'items'   => array(
				__( 'Produkt-Popup: Produktinfos unter der Beschreibung, „Dein Design“ über Personalisierung, Datei-Link in Textfarbe, flachere Aktions-Buttons.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.302',
			'items'   => array(
				__( 'Kasse: Vorteile und „Jetzt kaufen“ in positivem Grün; Button und AGB-Text auf Deutsch.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.301',
			'items'   => array(
				__( 'Produkt-Popup: Art.-Nr., Tags und Kategorie unter „Produktinfos (i)“ als Hover. „Auf Lager“ fest grün, größerer Titel.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.300',
			'items'   => array(
				__( 'Produkt-Popup: Lieferzeit-Tooltip nutzt Kontrast/Base, bleibt in der Spalte und wird nicht links abgeschnitten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.299',
			'items'   => array(
				__( 'Produkttabelle: Button „Größentabelle öffnen“. Auf Desktop ein kompaktes Popup um die Tabelle herum.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.298',
			'items'   => array(
				__( 'Produktseite: Die Personalisierungs-Card nutzt die eingestellte Surface-Farbe statt einer festen Weiß-Mischung.', 'fairflock' ),
				__( 'Produkt-Popup: Der Bildbereich ohne Bild nutzt die eingestellte Base-Farbe statt festem Grau.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.297',
			'items'   => array(
				__( 'Produkt-Popup: Das Größen-Dropdown zeigt nur Größen, die als Variante angelegt sind, und übergibt die Auswahl an den Warenkorb.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.296',
			'items'   => array(
				__( 'Produkt-Popup: Größen stehen in einem Dropdown und bleiben sichtbar, auch wenn die Personalisierung eingeklappt ist.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.295',
			'items'   => array(
				__( 'FairFlock → Lieferzeiten: globale Lieferklassen, optional mit Info-Icon. Am Produkt unter Versand als Lieferdauerklasse wählbar.', 'fairflock' ),
				__( 'Neue Produktflächen folgen der online eingestellten Palette (Brand, Base, Kontrast, Weiß, Surface).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.294',
			'items'   => array(
				__( 'Produkttabelle: das Vollbild auf dem Desktop bleibt auf der Inhaltsbreite der Produktseite.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.292',
			'items'   => array(
				__( 'Produktkarten: LKW-Icon und „Lieferung in X Tagen“ unter dem Preis, wenn das Produkt eine Lieferzeit nennt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.291',
			'items'   => array(
				__( 'Produkttabelle: Vollbild mit Bild-Slideshow, zentrierter Tabelle, Scroll-Pfeilen und Akzentfarbe am Schließen-Button.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.290',
			'items'   => array(
				__( 'Produkt-Popup: „Konfigurieren“ fährt animiert aus und legt einen Schleier über die Beschreibung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.289',
			'items'   => array(
				__( 'Produkttabelle: Button „Tabelle öffnen“ zeigt die Tabelle im Vollbild.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.288',
			'items'   => array(
				__( 'Produkt-Popup: kräftigerer Schatten unter der Konfiguration, Lieferzeit auch bei „Lieferzeit:“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.287',
			'items'   => array(
				__( 'Produkt-Popup: Name, Preis und Lieferdetails bleiben über „Konfigurieren“ sichtbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.286',
			'items'   => array(
				__( 'Produkttabelle: Umlaute wie „Größe“ bleiben auf der Produktseite erhalten.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.285',
			'items'   => array(
				__( 'FairFlock → Produkte: Tabellenvorlagen, die man am Produkt auswählen kann.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.284',
			'items'   => array(
				__( 'Produkttabelle: bis zu 16 Spalten, breite Tabellen lassen sich seitlich scrollen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.283',
			'items'   => array(
				__( 'Produkt-Popup: „Konfigurieren“ nur, wenn die Personalisierung am Produkt aktiv ist.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.282',
			'items'   => array(
				__( 'Produkttabelle im Backend: kompakte Tabelle statt großer Einzelfelder.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.281',
			'items'   => array(
				__( 'Produkt-Popup: Konfiguration startet eingeklappt, Lager und Lieferzeit stehen als Textzeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.280',
			'items'   => array(
				__( 'Druckmotiv: neben der angehängten Datei steht „ändern“.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.279',
			'items'   => array(
				__( 'Produkt-Popup: Name, Preis und Lagerstatus stehen über der Personalisierung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.278',
			'items'   => array(
				__( 'Produkte: Tabelle im Produktdaten-Bereich, Anzeige direkt unter der Beschreibung.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.277',
			'items'   => array(
				__( 'Bestellübersicht: angehängte Datei steht beim zugehörigen Produkt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.276',
			'items'   => array(
				__( 'Produktbeschreibung: Leerzeilen aus dem Editor bleiben als Abstand sichtbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.275',
			'items'   => array(
				__( 'Produkt-Popup: Druckmotiv-Upload legt das Produkt in den Warenkorb, statt einen kritischen Fehler zu zeigen.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.274',
			'items'   => array(
				__( 'Produkt-Popup: Bilder links untereinander scrollbar, Slideshow nur auf Mobil.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.273',
			'items'   => array(
				__( 'Produkt-Popup: Hinzufügen mit Warenkorb-Icon und Zur Seite in einer Zeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.272',
			'items'   => array(
				__( 'Produkt-Popup: Größen direkt wählbar, Fenster bis 90 Prozent der Bildschirmhöhe.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.271',
			'items'   => array(
				__( 'Produkt-Popup: Titel, Preis und Lagerstatus in einer Karte, Beschreibung ohne Überschrift, weitere Angaben darunter.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.270',
			'items'   => array(
				__( 'Shop: Ansicht ist ein Button, die Spaltenzahl öffnet sich als Menü.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.269',
			'items'   => array(
				__( 'Shop: Ansicht neben der Sortierung, Produkte pro Zeile wählbar (2–5).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.268',
			'items'   => array(
				__( 'Formular im Backend: übersichtliche Karten statt der überladenen Feldtabelle.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.267',
			'items'   => array(
				__( 'Produkt-Popup: Bild immer vollständig sichtbar (nicht beschnitten).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.266',
			'items'   => array(
				__( 'Backend: WordPress-Dashboard entfällt, FairFlock ist die Startseite und der erste Menüpunkt.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.265',
			'items'   => array(
				__( 'Ähnliche Produkte: Überschrift im H2-Sektionsstil, Abstand unter dem Warenkorb-Button.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.264',
			'items'   => array(
				__( 'Produkt-Brotkrumen: Text „Zurückgelegter Weg“, deutlich blasser und transparenter.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.263',
			'items'   => array(
				__( 'Produktbeschreibung: gesamter Inhalt in einer Karte, leere Zwischenblöcke ausgeblendet.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.262',
			'items'   => array(
				__( 'Produktseite: Inhaltsbreite 1350px, keine Kurzbeschreibung neben dem Bild, Bild ohne Rahmen, Tags ausgeblendet, SKU in einer Zeile.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.261',
			'items'   => array(
				__( 'Hero: Hintergründe auf iPhone/Safari korrekt (kein Ken-Burns-Transform, stabile Cover-Fläche).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.260',
			'items'   => array(
				__( 'Kontaktformular: Gutenberg-Block mit Admin, SMTP, Turnstile und FairFlock-Tokens.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.259',
			'items'   => array(
				__( 'Darstellung: Desktop-Nav-Layouts — Logo mittig, Suchleiste mittig (zwei Varianten).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.258',
			'items'   => array(
				__( 'Mobile Nav: Burger statt Lupe; Account mit Icon und Label im Menü.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.257',
			'items'   => array(
				__( 'Produkt-Slider: Farben für den Hover-Button „Ansehen“ im Site Editor einstellbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.256',
			'items'   => array(
				__( 'Hero-Slideshow: Button-Farben aus dem Editor werden nicht mehr überschrieben.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.255',
			'items'   => array(
				__( 'Kollektionen: „Mehr/Weniger anzeigen“ als zwei gestaltbare Buttons im Site Editor.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.254',
			'items'   => array(
				__( 'Mobile Nav: nur Suche + Burger; Konto und Warenkorb im Burger-Menü.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.253',
			'items'   => array(
				__( 'Mobiles Suchpanel: Mega-Menü-Punkt öffnet das Mega-Menü als eigene Unterseite.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.252',
			'items'   => array(
				__( 'Demo Content Kollektionen: Überschreiben + Bestseller-Produkt-Slider unter dem Widget.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.251',
			'items'   => array(
				__( 'Kategorien-Seite: gleiches Kollektionen-Widget wie Startseite (Template-Part, Slider funktioniert).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.250',
			'items'   => array(
				__( 'Kollektionen-Widget: Kategorien im Editor ausschließen (live auf der Website).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.249',
			'items'   => array(
				__( 'Responsive Innenabstände: Viewport-Breakpoints + Nav-Padding überschreibt Block-Werte nicht mehr.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.248',
			'items'   => array(
				__( 'Mega-Menü Promo-Card: Cover-Höhe im Editor wieder per Resize einstellbar.', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.247',
			'items'   => array(
				__( 'Kollektionen im Site Editor: alle Cards sichtbar (kein „Mehr anzeigen“-Verstecken).', 'fairflock' ),
			),
		),
		array(
			'version' => '0.9.246',
			'items'   => array(
				__( 'Kollektions-Cards: kein „Zur Kollektion“-Text — Hover zoont Bild und verstärkt Overlay leicht.', 'fairflock' ),
			),
		),
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
