<?php
/**
 * FairFlock legal page content (company data as used on Amateurhandball.de).
 *
 * @package FairFlock
 * @since 0.9.158
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FairFlock operator identity for legal pages.
 *
 * @since 0.9.158
 *
 * @return array{name:string,owner:string,street:string,zip:string,city:string,country:string,phone:string,email:string,vat:string}
 */
function fairflock_legal_company() {
	return array(
		'name'    => 'FairFlock',
		'owner'   => 'Julian Brandt',
		'street'  => 'Dünner Str. 12-14',
		'zip'     => '32257',
		'city'    => 'Bünde',
		'country' => 'Deutschland',
		'phone'   => '05223 – 18 31 964',
		'email'   => 'info@fairflock.de',
		'vat'     => 'DE289771815',
	);
}

/**
 * Escaped address block HTML (with <br>).
 *
 * @since 0.9.158
 *
 * @return string
 */
function fairflock_legal_address_html() {
	$c = fairflock_legal_company();
	return esc_html( $c['name'] ) . '<br>'
		. esc_html( $c['owner'] ) . '<br>'
		. esc_html( $c['street'] ) . '<br>'
		. esc_html( $c['zip'] . ' ' . $c['city'] ) . '<br>'
		. esc_html( $c['country'] );
}

/**
 * Paragraph block.
 *
 * @since 0.9.158
 *
 * @param string $html Inner HTML (already escaped where needed).
 * @return string
 */
function fairflock_legal_block_p( $html ) {
	return '<!-- wp:paragraph --><p>' . $html . '</p><!-- /wp:paragraph -->';
}

/**
 * Heading block.
 *
 * @since 0.9.158
 *
 * @param string $text  Plain text.
 * @param int    $level 2|3.
 * @return string
 */
function fairflock_legal_block_h( $text, $level = 2 ) {
	$level = ( 3 === (int) $level ) ? 3 : 2;
	$tag   = 'h' . $level;
	return '<!-- wp:heading {"level":' . $level . '} --><' . $tag . ' class="wp-block-heading">' . esc_html( $text ) . '</' . $tag . '><!-- /wp:heading -->';
}

/**
 * List block from plain items.
 *
 * @since 0.9.158
 *
 * @param array<int, string> $items Plain text items.
 * @return string
 */
function fairflock_legal_block_list( array $items ) {
	$lis = '';
	foreach ( $items as $item ) {
		$lis .= '<!-- wp:list-item --><li>' . esc_html( $item ) . '</li><!-- /wp:list-item -->';
	}
	return '<!-- wp:list --><ul class="wp-block-list">' . $lis . '</ul><!-- /wp:list -->';
}

/**
 * Shop intro line for legal pages.
 *
 * @since 0.9.158
 *
 * @return string
 */
function fairflock_legal_shop_intro() {
	$shop = get_bloginfo( 'name' );
	if ( ! is_string( $shop ) || '' === trim( $shop ) ) {
		$shop = 'dieser Online-Shop';
	}
	return sprintf(
		/* translators: %s: shop name */
		esc_html__( 'Dies ist der %s – powered by FairFlock.', 'fairflock' ),
		esc_html( $shop )
	);
}

/**
 * Legal page blueprints with FairFlock company content.
 *
 * @since 0.9.158
 *
 * @return array<string, array{title: string, content: string}>
 */
function fairflock_get_legal_page_blueprints() {
	$c     = fairflock_legal_company();
	$intro = fairflock_legal_shop_intro();
	$addr  = fairflock_legal_address_html();

	return array(
		'impressum'       => array(
			'title'   => __( 'Impressum', 'fairflock' ),
			'content' => fairflock_legal_content_impressum( $intro, $addr, $c ),
		),
		'datenschutz'     => array(
			'title'   => __( 'Datenschutz', 'fairflock' ),
			'content' => fairflock_legal_content_datenschutz( $intro, $addr, $c ),
		),
		'agb'             => array(
			'title'   => __( 'AGB', 'fairflock' ),
			'content' => fairflock_legal_content_agb( $intro, $addr, $c ),
		),
		'widerruf'        => array(
			'title'   => __( 'Widerrufsrecht', 'fairflock' ),
			'content' => fairflock_legal_content_widerruf( $intro, $c ),
		),
		'versand-zahlung' => array(
			'title'   => __( 'Versand & Zahlung', 'fairflock' ),
			'content' => fairflock_legal_content_versand( $intro, $c ),
		),
	);
}

/**
 * Impressum content.
 *
 * @since 0.9.158
 *
 * @param string               $intro Intro.
 * @param string               $addr  Address HTML.
 * @param array<string,string> $c     Company.
 * @return string
 */
function fairflock_legal_content_impressum( $intro, $addr, $c ) {
	$parts   = array();
	$parts[] = fairflock_legal_block_p( $intro );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Betreiber der Seite und inhaltlich verantwortlich:', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( $c['name'], 2 );
	$parts[] = fairflock_legal_block_p(
		esc_html__( 'Inhaber:', 'fairflock' ) . ' ' . esc_html( $c['owner'] ) . '<br>'
		. $addr . '<br><br>'
		. esc_html__( 'Telefonnummer:', 'fairflock' ) . ' ' . esc_html( $c['phone'] ) . '<br>'
		. esc_html__( 'E-Mail-Adresse:', 'fairflock' ) . ' ' . esc_html( $c['email'] ) . '<br>'
		. esc_html__( 'USt-IDNr:', 'fairflock' ) . ' ' . esc_html( $c['vat'] )
	);
	$parts[] = fairflock_legal_block_h( __( 'Urheberrecht', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Haftung für Inhalte', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen. Als Diensteanbieter sind wir gemäß § 7 Abs. 1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p(
		esc_html__( 'Alle Neuigkeiten findet ihr unter', 'fairflock' ) . ' <a href="https://www.fairflock.de/news/">https://www.fairflock.de/news/</a>'
	);
	$parts[] = fairflock_legal_block_h( __( 'Hinweis zur Online-Streitbeilegung', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p(
		esc_html__( 'Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:', 'fairflock' ) . ' <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener noreferrer">https://ec.europa.eu/consumers/odr/</a>'
	);
	return implode( "\n\n", $parts );
}

/**
 * Datenschutz content (FairFlock company + core notices).
 *
 * @since 0.9.158
 *
 * @param string               $intro Intro.
 * @param string               $addr  Address HTML.
 * @param array<string,string> $c     Company.
 * @return string
 */
function fairflock_legal_content_datenschutz( $intro, $addr, $c ) {
	$parts   = array();
	$parts[] = fairflock_legal_block_p( $intro );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Wir nehmen den Schutz Ihrer persönlichen Daten sehr ernst und halten uns an die einschlägigen gesetzlichen Vorschriften. Die nachfolgende Erklärung informiert Sie darüber, welche Art von Daten zu welchem Zweck erhoben werden und in welchem Umfang diese Daten Dritten zugänglich gemacht werden.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Hinweis zur verantwortlichen Stelle', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p(
		esc_html__( 'Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist:', 'fairflock' ) . '<br><br>'
		. $addr . '<br><br>'
		. esc_html__( 'Telefon:', 'fairflock' ) . ' ' . esc_html( $c['phone'] ) . '<br>'
		. esc_html__( 'E-Mail:', 'fairflock' ) . ' ' . esc_html( $c['email'] )
	);
	$parts[] = fairflock_legal_block_p( esc_html__( 'Verantwortliche Stelle ist die natürliche oder juristische Person, die allein oder gemeinsam mit anderen über die Zwecke und Mittel der Verarbeitung von personenbezogenen Daten (z. B. Namen, E-Mail-Adressen o. Ä.) entscheidet.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Datenerfassung auf unserer Website', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Ihre Daten werden zum einen dadurch erhoben, dass Sie uns diese mitteilen (z. B. in Formularen oder bei einer Bestellung). Andere Daten werden automatisch beim Besuch der Website durch unsere IT-Systeme erfasst — vor allem technische Daten (z. B. Internetbrowser, Betriebssystem oder Uhrzeit des Seitenaufrufs).', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Ihre Rechte', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Sie haben jederzeit das Recht, unentgeltlich Auskunft über Herkunft, Empfänger und Zweck Ihrer gespeicherten personenbezogenen Daten zu erhalten. Sie haben außerdem ein Recht auf Berichtigung, Sperrung oder Löschung dieser Daten sowie auf Datenübertragbarkeit. Hierzu sowie zu weiteren Fragen zum Thema Datenschutz können Sie sich jederzeit unter der im Impressum angegebenen Adresse an uns wenden. Des Weiteren steht Ihnen ein Beschwerderecht bei der zuständigen Aufsichtsbehörde zu.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Widerruf Ihrer Einwilligung', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Viele Datenverarbeitungsvorgänge sind nur mit Ihrer ausdrücklichen Einwilligung möglich. Sie können eine bereits erteilte Einwilligung jederzeit widerrufen. Dazu reicht eine formlose Mitteilung per E-Mail an uns. Die Rechtmäßigkeit der bis zum Widerruf erfolgten Datenverarbeitung bleibt vom Widerruf unberührt.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'SSL- bzw. TLS-Verschlüsselung', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Diese Seite nutzt aus Sicherheitsgründen und zum Schutz der Übertragung vertraulicher Inhalte eine SSL- bzw. TLS-Verschlüsselung. Eine verschlüsselte Verbindung erkennen Sie daran, dass die Adresszeile des Browsers von „http://“ auf „https://“ wechselt und an dem Schloss-Symbol in Ihrer Browserzeile.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Cookies & Einwilligung', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Diese Website verwendet Cookies und ggf. ähnliche Technologien. Technisch notwendige Cookies sind für den Betrieb des Shops erforderlich (z. B. Warenkorb, Login). Weitere Cookies (z. B. Statistik oder Marketing) setzen wir nur mit Ihrer Einwilligung — verwaltet über unser Cookie-/Consent-Tool (Complianz).', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Bestellungen & Zahlungen', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Zur Abwicklung von Bestellungen verarbeiten wir die dafür erforderlichen Daten (z. B. Name, Adresse, E-Mail, Zahlungsinformationen). Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO. Zahlungsdienstleister (z. B. PayPal) erhalten nur die für die Zahlung notwendigen Daten.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Widerspruch gegen Werbe-Mails', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Der Nutzung von im Rahmen der Impressumspflicht veröffentlichten Kontaktdaten zur Übersendung von nicht ausdrücklich angeforderter Werbung und Informationsmaterialien wird hiermit widersprochen.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p(
		esc_html__( 'Datenschutz-Anfragen bitte an', 'fairflock' ) . ' ' . esc_html( $c['email'] ) . ' ' . esc_html__( 'mit dem Betreff „Datenschutz“.', 'fairflock' )
	);
	return implode( "\n\n", $parts );
}

/**
 * AGB content.
 *
 * @since 0.9.158
 *
 * @param string               $intro Intro.
 * @param string               $addr  Address HTML.
 * @param array<string,string> $c     Company.
 * @return string
 */
function fairflock_legal_content_agb( $intro, $addr, $c ) {
	$parts   = array();
	$parts[] = fairflock_legal_block_p( $intro );
	$parts[] = fairflock_legal_block_h( __( '§1 Geltung gegenüber Unternehmern und Begriffsdefinitionen', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( '(1) Die nachfolgenden Allgemeinen Geschäftsbedingungen gelten für alle Lieferungen zwischen uns und einem Verbraucher in ihrer zum Zeitpunkt der Bestellung gültigen Fassung.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p( esc_html__( '(2) „Verbraucher“ im Sinne dieser Geschäftsbedingungen ist jede natürliche Person, die ein Rechtsgeschäft zu einem Zwecke abschließt, der weder ihrer gewerblichen noch ihrer selbständigen beruflichen Tätigkeit zugerechnet werden kann.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( '§2 Zustandekommen eines Vertrages, Speicherung des Vertragstextes', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( '(1) Die folgenden Regelungen über den Vertragsabschluss gelten für Bestellungen über unseren Internetshop.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p(
		esc_html__( '(2) Im Falle des Vertragsschlusses kommt der Vertrag mit', 'fairflock' ) . '<br><br>' . $addr . '<br><br>' . esc_html__( 'zustande.', 'fairflock' )
	);
	$parts[] = fairflock_legal_block_p( esc_html__( '(3) Die Präsentation der Waren in unserem Internetshop stellt kein rechtlich bindendes Vertragsangebot unsererseits dar, sondern ist nur eine unverbindliche Aufforderung an den Verbraucher, Waren zu bestellen. Mit der Bestellung der gewünschten Ware gibt der Verbraucher ein für ihn verbindliches Angebot auf Abschluss eines Kaufvertrages ab.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p( esc_html__( '(4) Der Verbraucher gibt ein bindendes Vertragsangebot ab, indem er die vorgesehene Bestellprozedur erfolgreich durchläuft. Wir bestätigen den Eingang der Bestellung unmittelbar durch eine automatisch generierte E-Mail („Auftragsbestätigung“). Mit dieser nehmen wir Ihr Angebot an.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_list(
		array(
			__( 'Auswahl der gewünschten Ware', 'fairflock' ),
			__( 'Warenkorb prüfen', 'fairflock' ),
			__( 'Zur Kasse gehen', 'fairflock' ),
			__( 'Angaben prüfen bzw. korrigieren', 'fairflock' ),
			__( 'Bestellung verbindlich absenden', 'fairflock' ),
		)
	);
	$parts[] = fairflock_legal_block_p( esc_html__( '(5) Wir speichern den Vertragstext und senden Ihnen die Bestelldaten und unsere AGB per E-Mail zu. Vergangene Bestellungen können Sie in Ihrem Kundenbereich einsehen.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( '§3 Preise, Versandkosten, Zahlung, Fälligkeit', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( '(1) Die angegebenen Preise enthalten die gesetzliche Umsatzsteuer und sonstige Preisbestandteile. Hinzu kommen etwaige Versandkosten.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p( esc_html__( '(2) Der Verbraucher hat die Möglichkeit der Zahlung per Vorkasse und PayPal.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p( esc_html__( '(3) Hat der Verbraucher die Zahlung per Vorkasse gewählt, so verpflichtet er sich, den Kaufpreis unverzüglich nach Vertragsschluss zu zahlen.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( '§4 Lieferung', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( '(1) Sofern wir dies in der Produktbeschreibung nicht deutlich anders angegeben haben, sind alle von uns angebotenen Artikel versandfertig bzw. werden nach Zahlungseingang produziert/versendet.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p( esc_html__( '(2) Hat der Verbraucher die Zahlung per Vorkasse gewählt, so versenden wir die Ware nicht vor Zahlungseingang.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( '§5 Eigentumsvorbehalt', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Wir behalten uns das Eigentum an der Ware bis zur vollständigen Bezahlung des Kaufpreises vor.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( '§6 Widerrufsrecht', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p(
		sprintf(
			/* translators: %s: email */
			esc_html__( 'Du hast das Recht, binnen vierzehn Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen. Um Dein Widerrufsrecht auszuüben, musst Du uns (%1$s, E-Mail: %2$s) mittels einer eindeutigen Erklärung informieren. Details findest Du auf der Seite Widerrufsrecht.', 'fairflock' ),
			esc_html( $c['name'] ),
			esc_html( $c['email'] )
		)
	);
	$parts[] = fairflock_legal_block_h( __( '§7 Rücksendekosten bei Widerruf', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Sollten Sie von Ihrem Widerrufsrecht Gebrauch machen, so gilt im Einklang mit § 357 Abs. 2 BGB, dass Sie die regelmäßigen Kosten der Rücksendung zu tragen haben, wenn die gelieferte Ware der bestellten entspricht und wenn der Preis der zurückzusendenden Sache einen Betrag von 40 Euro nicht übersteigt oder wenn Sie bei einem höheren Preis der Sache zum Zeitpunkt des Widerrufs noch nicht die Gegenleistung oder eine vertraglich vereinbarte Teilzahlung erbracht haben. Anderenfalls ist die Rücksendung für Sie kostenfrei.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( '§8 Vertragssprache', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Als Vertragssprache steht ausschließlich Deutsch zur Verfügung.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( '§9 Gestaltung durch den Kunden', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( '(1) Bei jeder Gestaltung durch den Kunden muss der Kunde im Besitz aller erforderlichen Urheber-, Marken- oder sonstigen Rechte an von ihm verwendeten Bildern, Grafiken, Logos usw. sein. Für die Inhalte der selbst erstellten Produkte — auch für die Texte — ist der Auftraggeber verantwortlich.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p( esc_html__( '(2) Die angezeigten Farben, Größen und Ausrichtungen können aus darstellungs- oder produktionstechnischen Gründen vom späteren Ergebnis abweichen. Wir produzieren jedes Teil als Einzelstück, welches kleinere Abweichungen enthalten kann.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( '§10 Streitschlichtung', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p(
		esc_html__( 'Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:', 'fairflock' ) . ' <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener noreferrer">https://ec.europa.eu/consumers/odr/</a>. ' . esc_html__( 'Wir sind bereit, an einem außergerichtlichen Schlichtungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.', 'fairflock' )
	);
	return implode( "\n\n", $parts );
}

/**
 * Widerrufsrecht content.
 *
 * @since 0.9.158
 *
 * @param string               $intro Intro.
 * @param array<string,string> $c     Company.
 * @return string
 */
function fairflock_legal_content_widerruf( $intro, $c ) {
	$parts   = array();
	$parts[] = fairflock_legal_block_p( $intro );
	$parts[] = fairflock_legal_block_p(
		sprintf(
			/* translators: 1: company, 2: email */
			esc_html__( 'Du hast das Recht, binnen vierzehn Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen. Die Widerrufsfrist beträgt vierzehn Tage ab dem Tag, an dem Du oder ein von Dir benannter Dritter, der nicht der Beförderer ist, die Waren in Besitz genommen hat. Um Dein Widerrufsrecht auszuüben, musst Du uns (%1$s, E-Mail: %2$s) mittels einer eindeutigen Erklärung (z. B. Brief oder E-Mail) über Deinen Entschluss informieren. Zur Wahrung der Widerrufsfrist reicht es aus, dass Du die Mitteilung vor Ablauf der Widerrufsfrist absendest.', 'fairflock' ),
			esc_html( $c['name'] ),
			esc_html( $c['email'] )
		)
	);
	$parts[] = fairflock_legal_block_h( __( 'Folgen des Widerrufs', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Wenn Du diesen Vertrag widerrufst, haben wir Dir alle Zahlungen, die wir von Dir erhalten haben, einschließlich der Lieferkosten (mit Ausnahme der zusätzlichen Kosten, die sich daraus ergeben, dass Du eine andere Art der Lieferung als die von uns angebotene, günstige Standardlieferung gewählt hast), unverzüglich und spätestens binnen vierzehn Tagen ab dem Tag zurückzuzahlen, an dem die Mitteilung über Deinen Widerruf bei uns eingegangen ist. Für diese Rückzahlung verwenden wir dasselbe Zahlungsmittel, das Du bei der ursprünglichen Transaktion eingesetzt hast, es sei denn, mit Dir wurde ausdrücklich etwas anderes vereinbart; in keinem Fall werden Dir wegen dieser Rückzahlung Entgelte berechnet. Wir können die Rückzahlung verweigern, bis wir die Waren wieder zurückerhalten haben oder bis Du den Nachweis erbracht hast, dass Du die Waren zurückgesandt hast, je nachdem, welches der frühere Zeitpunkt ist. Du hast die Waren unverzüglich und in jedem Fall spätestens binnen vierzehn Tagen an uns zurückzusenden oder zu übergeben. Du trägst die unmittelbaren Kosten der Rücksendung der Waren.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Ausschluss bzw. vorzeitiges Erlöschen', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Das Widerrufsrecht besteht nicht bei Verträgen zur Lieferung von Waren, die nicht vorgefertigt sind und für deren Herstellung eine individuelle Auswahl oder Bestimmung durch den Verbraucher maßgeblich ist oder die eindeutig auf die persönlichen Bedürfnisse des Verbrauchers zugeschnitten sind.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Muster-Widerrufsformular', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p(
		esc_html__( 'Wenn Du den Vertrag widerrufen willst, dann teile uns Folgendes mit:', 'fairflock' ) . '<br><br>'
		. esc_html__( 'An', 'fairflock' ) . ' ' . esc_html( $c['name'] ) . ' / ' . esc_html( $c['email'] ) . '<br>'
		. esc_html__( 'Hiermit widerrufe(n) ich/wir (*) den von mir/uns (*) abgeschlossenen Vertrag über den Kauf der folgenden Waren (*)/die Erbringung der folgenden Dienstleistung (*)', 'fairflock' ) . '<br>'
		. esc_html__( 'Bestellt am (*)/erhalten am (*)', 'fairflock' ) . '<br>'
		. esc_html__( 'Name des/der Verbraucher(s)', 'fairflock' ) . '<br>'
		. esc_html__( 'Anschrift des/der Verbraucher(s)', 'fairflock' ) . '<br>'
		. esc_html__( 'Unterschrift des/der Verbraucher(s) (nur bei Mitteilung auf Papier)', 'fairflock' ) . '<br>'
		. esc_html__( 'Datum', 'fairflock' ) . '<br><br>'
		. esc_html__( '(*) Unzutreffendes streichen.', 'fairflock' )
	);
	return implode( "\n\n", $parts );
}

/**
 * Versand & Zahlung content.
 *
 * @since 0.9.158
 *
 * @param string               $intro Intro.
 * @param array<string,string> $c     Company.
 * @return string
 */
function fairflock_legal_content_versand( $intro, $c ) {
	$parts   = array();
	$parts[] = fairflock_legal_block_p( $intro );
	$parts[] = fairflock_legal_block_h( __( 'Wann geht deine Bestellung raus?', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Wir fangen direkt nach Zahlungseingang an, deine Bestellung zu bearbeiten. Je nach Lagerstand, Auftragsaufkommen und Verfügbarkeit braucht deine Bestellung einige Tage, um unser Haus zu verlassen. So schnell es geht bringen wir dein Produkt auf den Weg zu dir.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Versand nach Deutschland und Österreich', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Wir beschränken einige Angebote auf das Bundesgebiet und Österreich. Innerhalb Deutschlands berechnen wir eine Versandkostenpauschale von 5,10 EUR. Alle Pakete gehen mit DHL raus.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Versand nach Österreich: Versandkostenpauschale 12,90 EUR.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Zahlungsmittel', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p( esc_html__( 'Du kannst bei uns per Vorkasse oder PayPal bezahlen.', 'fairflock' ) );
	$parts[] = fairflock_legal_block_h( __( 'Andere Länder / Sonderwünsche', 'fairflock' ), 2 );
	$parts[] = fairflock_legal_block_p(
		esc_html__( 'Du möchtest eine Lieferung in andere Länder oder individuelle Sonderanfertigungen? Nimm Kontakt mit uns auf:', 'fairflock' ) . ' '
		. esc_html( $c['email'] ) . ' · ' . esc_html( $c['phone'] )
	);
	$parts[] = fairflock_legal_block_p( esc_html__( 'Größere Mengen: Melde dich einfach — wir können individuelle Angebote und Mengenstaffeln anbieten.', 'fairflock' ) );
	return implode( "\n\n", $parts );
}

/**
 * Creates or updates legal pages.
 *
 * @since 0.9.158
 *
 * @param bool $overwrite Update existing published pages.
 * @return array{created:int,updated:int}
 */
function fairflock_ensure_legal_pages( $overwrite = false ) {
	$pages   = fairflock_get_legal_page_blueprints();
	$created = 0;
	$updated = 0;

	foreach ( $pages as $slug => $page ) {
		$existing = get_page_by_path( $slug );
		if ( $existing instanceof WP_Post && 'publish' === $existing->post_status ) {
			if ( ! $overwrite ) {
				continue;
			}
			$result = wp_update_post(
				array(
					'ID'           => (int) $existing->ID,
					'post_title'   => $page['title'],
					'post_content' => $page['content'],
				),
				true
			);
			if ( ! is_wp_error( $result ) ) {
				++$updated;
			}
			continue;
		}

		$result = wp_insert_post(
			array(
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $page['content'],
			),
			true
		);
		if ( ! is_wp_error( $result ) && (int) $result > 0 ) {
			++$created;
		}
	}

	return array(
		'created' => $created,
		'updated' => $updated,
	);
}

/**
 * Adds body classes for legal / account pages.
 *
 * @since 0.9.158
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function fairflock_legal_body_classes( $classes ) {
	if ( is_page( array( 'impressum', 'datenschutz', 'agb', 'widerruf', 'versand-zahlung' ) ) ) {
		$classes[] = 'ff-legal-page';
	}
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		$classes[] = 'ff-account-page';
	}
	if ( is_page( 'ueber-uns' ) ) {
		$classes[] = 'ff-about-page';
	}
	return $classes;
}
add_filter( 'body_class', 'fairflock_legal_body_classes' );
