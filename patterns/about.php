<?php
/**
 * Title: Über uns
 * Slug: fairflock/about
 * Categories: fairflock, featured
 * Description: Über-uns-Seite — Hero, Story, Werte, Moment-Band und Abschluss-CTA.
 * Viewport Width: 1240
 */

$shop_name = get_bloginfo( 'name' );
if ( ! is_string( $shop_name ) || '' === trim( $shop_name ) ) {
	$shop_name = 'FairFlock';
}
$shop_name = esc_html( $shop_name );
$shop_url  = esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
$contact   = esc_url( 'mailto:info@fairflock.de' );
?>
<!-- wp:group {"metadata":{"name":"Über uns · Hero"},"align":"full","className":"ff-about-hero","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-about-hero" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">
	<!-- wp:cover {"dimRatio":55,"overlayColor":"contrast","isUserOverlayColor":true,"minHeight":88,"minHeightUnit":"vh","contentPosition":"center center","isDark":true,"className":"ff-about-hero__cover","layout":{"type":"default"}} -->
	<div class="wp-block-cover is-dark has-custom-content-position is-position-center-center ff-about-hero__cover" style="min-height:88vh">
		<span aria-hidden="true" class="wp-block-cover__background has-contrast-background-color has-background-dim"></span>
		<div class="wp-block-cover__inner-container">
			<!-- wp:group {"className":"ff-about-hero__copy","style":{"spacing":{"blockGap":"14px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center","flexWrap":"nowrap"}} -->
			<div class="wp-block-group ff-about-hero__copy">
				<!-- wp:heading {"textAlign":"center","level":2,"className":"ff-about-hero__brand"} -->
				<h2 class="wp-block-heading has-text-align-center ff-about-hero__brand"><?php echo $shop_name; ?></h2>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center","className":"ff-about-hero__title"} -->
				<p class="has-text-align-center ff-about-hero__title">Dein Druckpartner — mit Haltung.</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"align":"center","className":"ff-about-hero__lead"} -->
				<p class="has-text-align-center ff-about-hero__lead">Von der Idee bis zum fertigen Stück: Druck, Stick und Gestaltung für Vereine, Teams und alle, die Marke sichtbar machen wollen.</p>
				<!-- /wp:paragraph -->

				<!-- wp:buttons {"className":"ff-about-hero__cta","layout":{"type":"flex","justifyContent":"center"}} -->
				<div class="wp-block-buttons ff-about-hero__cta">
					<!-- wp:button {"className":"ff-about-hero__btn"} -->
					<div class="wp-block-button ff-about-hero__btn"><a class="wp-block-button__link wp-element-button" href="<?php echo $shop_url; ?>">Produkte entdecken</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->
			</div>
			<!-- /wp:group -->
		</div>
	</div>
	<!-- /wp:cover -->
</div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"Über uns · Story"},"align":"full","className":"ff-pattern-media-split ff-about-story","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}},"blockGap":"0"},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-media-split ff-about-story" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-media-split","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-media-split">
		<!-- wp:group {"className":"ff-media-split__copy","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ff-media-split__copy">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Was uns antreibt</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p>Wir haben mit Sport und Social Media angefangen — und daraus einen Partner für Vereine und Athleten gemacht. Heute bedrucken und besticken wir Sportkleidung, Workwear und vieles mehr: von Flyern und Aufklebern bis zu Bandenwerbung.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p>Layouts zur Vorstellung des Endprodukts gehören dazu. Schreib uns einfach — wir finden für (fast) alles eine Lösung.</p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"ff-media-split__cta","layout":{"type":"default"}} -->
			<div class="wp-block-group ff-media-split__cta">
				<!-- wp:paragraph -->
				<p><a class="ff-media-split__btn" href="<?php echo $contact; ?>">Nachricht schreiben →</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ff-media-split__media","layout":{"type":"default"}} -->
		<div class="wp-block-group ff-media-split__media">
			<!-- wp:group {"className":"ff-video-frame","layout":{"type":"default"}} -->
			<div class="wp-block-group ff-video-frame">
				<!-- wp:paragraph {"className":"ff-video-frame__label"} -->
				<p class="ff-video-frame__label">Werkstatt- oder Teamfoto</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"Über uns · Werte"},"align":"full","className":"ff-pattern-steps ff-about-values","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-steps ff-about-values" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-pattern-steps__shell","style":{"spacing":{"padding":{"left":"0","right":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-pattern-steps__shell" style="padding-right:0;padding-left:0">
		<!-- wp:group {"className":"ff-pattern-section-head","layout":{"type":"constrained","contentSize":"34rem","justifyContent":"left"}} -->
		<div class="wp-block-group ff-pattern-section-head">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Drei Dinge, die zählen</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"muted"} -->
			<p class="has-muted-color has-text-color">Klar, nahbar und ohne Schnickschnack — damit aus Ideen tragbare Ergebnisse werden.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:columns {"isStackedOnMobile":false,"className":"ff-steps"} -->
		<div class="wp-block-columns ff-steps">
			<!-- wp:column {"className":"ff-step"} -->
			<div class="wp-block-column ff-step">
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading">Druck &amp; Stick</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph -->
				<p>Sporttextilien, Workwear, Handtücher und mehr — sauber verarbeitet, mit dem Look, den du brauchst.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"ff-step"} -->
			<div class="wp-block-column ff-step">
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading">Idee bis Produkt</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph -->
				<p>Von der ersten Skizze über Layouts bis zur Fertigung — so viel Begleitung, wie du möchtest.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"ff-step"} -->
			<div class="wp-block-column ff-step">
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading">Partner vor Ort</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph -->
				<p>Aus Ostwestfalen für Vereine und Teams — erreichbar, ehrlich und mit Blick fürs Detail.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"Über uns · Moment"},"align":"full","className":"ff-pattern-quote ff-about-moment","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-quote ff-about-moment" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">
	<!-- wp:group {"className":"ff-fullbleed","style":{"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"48vh"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-fullbleed" style="min-height:48vh">
		<!-- wp:group {"className":"ff-fullbleed__inner","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group ff-fullbleed__inner">
			<!-- wp:heading {"textAlign":"center"} -->
			<h2 class="wp-block-heading has-text-align-center">Aus Ostwestfalen in die Welt des Amateursports.</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"align":"center"} -->
			<p class="has-text-align-center">FairFlock — Druck &amp; Stickerei aus Bünde für OWL und darüber hinaus.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"Über uns · Vor Ort"},"align":"full","className":"ff-pattern-media-split ff-about-visit","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}},"blockGap":"0"},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-media-split ff-about-visit" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-media-split ff-media-split--flip","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-media-split ff-media-split--flip">
		<!-- wp:group {"className":"ff-media-split__media","layout":{"type":"default"}} -->
		<div class="wp-block-group ff-media-split__media">
			<!-- wp:group {"className":"ff-video-frame","layout":{"type":"default"}} -->
			<div class="wp-block-group ff-video-frame">
				<!-- wp:paragraph {"className":"ff-video-frame__label"} -->
				<p class="ff-video-frame__label">Standort / Werkstatt</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ff-media-split__copy","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ff-media-split__copy">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Du möchtest vorbeikommen?</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p>Wir sitzen in Bünde — Dünner Str. 12-14. Ob Abholung, Motiv-Abstimmung oder einfach ein kurzer Blick in die Produktion: melde dich kurz, dann finden wir einen Termin.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p>Telefon: 05223 – 18 31 964<br>E-Mail: info@fairflock.de</p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"ff-media-split__cta","layout":{"type":"default"}} -->
			<div class="wp-block-group ff-media-split__cta">
				<!-- wp:paragraph -->
				<p><a class="ff-media-split__btn" href="<?php echo $contact; ?>">Termin anfragen →</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"Über uns · CTA"},"align":"full","className":"ff-pattern-cta-end ff-about-cta","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ff-pattern-cta-end ff-about-cta" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--40);padding-right:0;padding-bottom:var(--wp--preset--spacing--40);padding-left:0">
	<!-- wp:group {"className":"ff-end-box","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group ff-end-box">
		<!-- wp:heading {"textAlign":"center"} -->
		<h2 class="wp-block-heading has-text-align-center">Lust aufs nächste Projekt?</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center"} -->
		<p class="has-text-align-center">Fragen zu Mengen, Motiven oder Abholung? Melde dich — wir helfen unkompliziert weiter.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"className":"ff-end-box__cta","layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons ff-end-box__cta">
			<!-- wp:button {"className":"ff-end-box__btn ff-end-box__btn--brand"} -->
			<div class="wp-block-button ff-end-box__btn ff-end-box__btn--brand"><a class="wp-block-button__link wp-element-button" href="<?php echo $shop_url; ?>">Zum Shop →</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"ff-end-box__btn ff-end-box__btn--ghost"} -->
			<div class="wp-block-button ff-end-box__btn ff-end-box__btn--ghost"><a class="wp-block-button__link wp-element-button" href="<?php echo $contact; ?>">Kontakt</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"faq","metadata":{"name":"Über uns · FAQ"},"align":"full","className":"ff-pattern-faq ff-about-faq","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
<div id="faq" class="wp-block-group alignfull ff-pattern-faq ff-about-faq" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--60);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-pattern-faq__shell","style":{"spacing":{"padding":{"left":"0","right":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group ff-pattern-faq__shell" style="padding-right:0;padding-left:0">
		<!-- wp:group {"className":"ff-pattern-section-head ff-pattern-faq__head","layout":{"type":"constrained","contentSize":"48rem","justifyContent":"center"}} -->
		<div class="wp-block-group ff-pattern-section-head ff-pattern-faq__head">
			<!-- wp:heading {"textAlign":"center"} -->
			<h2 class="wp-block-heading has-text-align-center">Häufige Fragen</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"align":"center","textColor":"muted"} -->
			<p class="has-text-align-center has-muted-color has-text-color">Kurz und klar — bevor du anrufst.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"metadata":{"name":"FAQ-Liste"},"allowedBlocks":["core/details"],"className":"ff-faq-list","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ff-faq-list">
			<!-- wp:details {"showContent":true,"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item" open>
				<summary>Wer steckt hinter dem Shop?</summary>
				<!-- wp:paragraph -->
				<p>Produktion und Versand laufen über FairFlock in Bünde. Impressum und Rechnung kommen von FairFlock — transparent und nachvollziehbar.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->

			<!-- wp:details {"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item">
				<summary>Kann ich personalisieren lassen?</summary>
				<!-- wp:paragraph -->
				<p>Ja — Logo, Name, Nummer oder Vereinschriftzug sind bei vielen Artikeln möglich. Was fehlt, klären wir kurz per Nachricht.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->

			<!-- wp:details {"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item">
				<summary>Wie lange dauert die Lieferung?</summary>
				<!-- wp:paragraph -->
				<p>Nach Zahlungseingang geht’s an die Bearbeitung. Je nach Lager und Auftrag dauert es einige Werktage, bis das Paket rausgeht — so schnell es geht.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->

			<!-- wp:details {"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item">
				<summary>Geht Abholung statt Versand?</summary>
				<!-- wp:paragraph -->
				<p>Ja, nach Termin in Bünde. Schreib uns kurz — wir sagen dir, wann dein Auftrag bereitliegt.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->

			<!-- wp:details {"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item">
				<summary>Größere Mengen oder Sonderwünsche?</summary>
				<!-- wp:paragraph -->
				<p>Staffelpreise decken nicht alles ab. Für Vereinsaufträge und Sonderanfertigungen machen wir gerne ein individuelles Angebot.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
