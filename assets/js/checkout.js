/**
 * Checkout: German CTA / terms, hide English step blurbs, keep checkbox visible.
 *
 * @package FairFlock
 * @since 0.9.302
 */
(function () {
	"use strict";

	var cfg = window.fairflockCheckout || {};
	var cta = typeof cfg.cta === "string" && cfg.cta ? cfg.cta : "Jetzt kaufen";
	var termsHtml = typeof cfg.terms === "string" ? cfg.terms : "";
	var EN_DESC =
		/\b(enter your|select a|choose a|add a note|shipping options|contact details|billing address|payment options|we'll use this|we will use this|for order updates|with your order|terms and conditions)\b/i;

	function isEnglishCta(text) {
		var n = String(text || "")
			.replace(/\s+/g, " ")
			.trim()
			.toLowerCase();
		return n === "buy now" || n === "place order";
	}

	function looksEnglishTerms(text) {
		var n = String(text || "").toLowerCase();
		return (
			n.indexOf("with your order") !== -1 ||
			n.indexOf("terms and conditions") !== -1 ||
			n.indexOf("cancellation policy") !== -1 ||
			n.indexOf("you must accept our") !== -1 ||
			n.indexOf("privacy policy") !== -1
		);
	}

	function rewriteButton(btn) {
		if (!btn || btn.getAttribute("data-ff-cta") === "1") {
			return;
		}
		var walker = document.createTreeWalker(btn, NodeFilter.SHOW_TEXT, null);
		var node;
		var changed = false;
		while ((node = walker.nextNode())) {
			if (isEnglishCta(node.nodeValue)) {
				node.nodeValue = cta;
				changed = true;
			}
		}
		if (changed) {
			btn.setAttribute("data-ff-cta", "1");
		}
	}

	function rewriteTerms(root) {
		if (!termsHtml || !root) {
			return;
		}
		var label = root.querySelector(".wc-block-components-checkbox__label, .wc-block-checkout__terms");
		var target = label || root;
		if (target.getAttribute("data-ff-terms") === "1") {
			return;
		}
		var plain = target.textContent || "";
		if (!looksEnglishTerms(plain) && plain.trim() !== "") {
			return;
		}
		if (label) {
			label.innerHTML = termsHtml;
			label.setAttribute("data-ff-terms", "1");
		} else {
			target.innerHTML = termsHtml;
			target.setAttribute("data-ff-terms", "1");
		}
	}

	function hideEnglishDescriptions(scope) {
		var root = scope && scope.querySelectorAll ? scope : document;
		root.querySelectorAll(".wc-block-components-checkout-step__description").forEach(function (el) {
			var text = (el.textContent || "").replace(/\s+/g, " ").trim();
			if (!text) {
				el.setAttribute("data-ff-hide-en", "1");
				el.hidden = true;
				return;
			}
			if (EN_DESC.test(text)) {
				el.setAttribute("data-ff-hide-en", "1");
				el.hidden = true;
				el.style.display = "none";
			}
		});
	}

	function scan(scope) {
		var root = scope && scope.querySelectorAll ? scope : document;
		root.querySelectorAll(
			".wc-block-components-checkout-place-order-button, button.wc-block-components-checkout-place-order-button"
		).forEach(rewriteButton);
		root.querySelectorAll(
			".wc-block-checkout__terms, .wp-block-woocommerce-checkout-terms-block, .wc-block-components-checkbox"
		).forEach(rewriteTerms);
		hideEnglishDescriptions(root);
	}

	function boot() {
		scan(document);
		if (typeof MutationObserver === "undefined") {
			return;
		}
		var obs = new MutationObserver(function (records) {
			for (var i = 0; i < records.length; i++) {
				var nodes = records[i].addedNodes;
				for (var j = 0; j < nodes.length; j++) {
					if (nodes[j].nodeType === 1) {
						scan(nodes[j]);
					}
				}
			}
			document
				.querySelectorAll(".wc-block-components-checkout-place-order-button")
				.forEach(function (btn) {
					if (btn.getAttribute("data-ff-cta") !== "1") {
						rewriteButton(btn);
					}
				});
			hideEnglishDescriptions(document);
		});
		obs.observe(document.body, { childList: true, subtree: true, characterData: true });
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
