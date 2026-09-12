<?php
/**
 * Title: FAQ
 * Slug: fairflock/faq
 * Categories: fairflock
 * Description: FAQ-Accordion 1:1 Styleguide (zentriert, Plus-Icon).
 * Viewport Width: 1240
 */
?>
<!-- wp:group {"anchor":"faq","metadata":{"name":"FAQ"},"align":"full","className":"ff-pattern-faq","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
<div id="faq" class="wp-block-group alignfull ff-pattern-faq" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0">
	<!-- wp:group {"className":"ff-pattern-faq__shell","style":{"spacing":{"padding":{"left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group ff-pattern-faq__shell" style="padding-right:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
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
				<summary>Wie lange dauert die Lieferung?</summary>
				<!-- wp:paragraph -->
				<p>In der Regel 3–10 Werktage. Personalisierte Artikel können etwas länger brauchen — die Angabe steht am Produkt.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->

			<!-- wp:details {"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item">
				<summary>Kann ich personalisieren lassen?</summary>
				<!-- wp:paragraph -->
				<p>Ja, bei ausgewählten Produkten. Logo oder Schriftzug wählst du im Bestellprozess.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->

			<!-- wp:details {"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item">
				<summary>Wer ist der Verkäufer?</summary>
				<!-- wp:paragraph -->
				<p>Verkauf und Abwicklung laufen über FairFlock GmbH. Impressum und Rechnung kommen von FairFlock.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->

			<!-- wp:details {"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item">
				<summary>Sind die Preise inkl. MwSt.?</summary>
				<!-- wp:paragraph -->
				<p>Ja. Alle angezeigten Preise enthalten die gesetzliche Mehrwertsteuer.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->

			<!-- wp:details {"metadata":{"name":"FAQ-Eintrag"},"className":"ff-faq-item"} -->
			<details class="wp-block-details ff-faq-item">
				<summary>Kann ich abholen statt liefern lassen?</summary>
				<!-- wp:paragraph -->
				<p>Abholung nach Termin ist möglich. Melde dich kurz über die Kontaktdaten im Footer.</p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
