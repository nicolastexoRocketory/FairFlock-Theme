/**
 * Checkout: buy panel in the right column, German copy fallbacks.
 *
 * Moves the native terms + place-order blocks (last children of the form,
 * nothing mounts after them) into a panel below the order summary, so the
 * whole purchase decision sits in one column. React keeps managing the nodes.
 *
 * @package FairFlock
 * @since 0.9.499
 */
(function () {
	"use strict";

	var cfg = window.fairflockCheckout || {};
	var cta = cfg.cta || "Jetzt kaufen";
	var termsHtml = cfg.terms || "";
	var termsError = cfg.termsError || "";
	var trust = Array.isArray(cfg.trust) ? cfg.trust : [];

	var ICONS = {
		lock: '<path d="M8 10V7.5a4 4 0 0 1 8 0V10"/><rect x="5.5" y="10" width="13" height="10" rx="2"/>',
		store: '<path d="M4 20V9.5L12 4l8 5.5V20"/><path d="M9.5 20v-6h5v6"/>',
		box: '<path d="M3.5 8 12 4l8.5 4v8L12 20l-8.5-4V8z"/><path d="M12 12.2 20.5 8M12 12.2V20M12 12.2 3.5 8"/>',
		return: '<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>',
	};

	var EN_DESC =
		/\b(enter your|select a|choose a|add a note|shipping options|contact details|billing address|payment options|we'll use this|we will use this|for order updates|with your order)\b/i;
	var EN_TERMS =
		/with your order|terms and conditions|cancellation policy|you must accept our|privacy policy|mit deiner bestellung bestätigst du/i;
	var EN_TERMS_ERROR =
		/to complete the order you have to accept|please read and accept the terms/i;

	function normalize(text) {
		return String(text || "").replace(/\s+/g, " ").trim();
	}

	function escapeHtml(text) {
		var div = document.createElement("div");
		div.textContent = text;
		return div.innerHTML;
	}

	function trustMarkup() {
		if (!trust.length) {
			return "";
		}
		var items = trust
			.map(function (item) {
				var icon = ICONS[item.icon] || ICONS.lock;
				return (
					'<li class="ff-checkout-buy__trust-item">' +
					'<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">' +
					icon +
					"</svg>" +
					escapeHtml(item.label || "") +
					"</li>"
				);
			})
			.join("");
		return '<ul class="ff-checkout-buy__trust">' + items + "</ul>";
	}

	function ensurePanel(sidebar) {
		var panel = sidebar.querySelector(":scope > .ff-checkout-buy");
		if (panel) {
			return panel;
		}
		panel = document.createElement("div");
		panel.className = "ff-checkout-buy";
		panel.innerHTML = '<div class="ff-checkout-buy__actions"></div>' + trustMarkup();
		sidebar.appendChild(panel);
		return panel;
	}

	function placeBuyArea(root) {
		var sidebar = root.querySelector(".wc-block-checkout__sidebar");
		var actions = root.querySelector(".wc-block-checkout__actions");
		if (!sidebar || !actions) {
			return;
		}
		var slot = ensurePanel(sidebar).querySelector(".ff-checkout-buy__actions");
		var terms = root.querySelector(".wc-block-checkout__terms");

		if (terms && terms.parentNode !== slot) {
			slot.insertBefore(terms, slot.firstChild);
		}
		if (actions.parentNode !== slot) {
			slot.appendChild(actions);
		}
	}

	function translateButton(root) {
		root.querySelectorAll(".wc-block-components-checkout-place-order-button").forEach(function (btn) {
			var walker = document.createTreeWalker(btn, NodeFilter.SHOW_TEXT, null);
			var node;
			while ((node = walker.nextNode())) {
				if (/^(buy now|place order)$/i.test(normalize(node.nodeValue))) {
					node.nodeValue = cta;
				}
			}
		});
	}

	function translateTerms(root) {
		if (!termsHtml) {
			return;
		}
		root.querySelectorAll(".wc-block-checkout__terms .wc-block-components-checkbox__label").forEach(function (label) {
			var text = normalize(label.textContent);
			if (!text || EN_TERMS.test(text)) {
				label.innerHTML = termsHtml;
			}
		});
	}

	function translateErrors(root) {
		if (!termsError) {
			return;
		}
		root.querySelectorAll(
			".wc-block-components-validation-error, .wc-block-components-notice-banner__content"
		).forEach(function (el) {
			if (EN_TERMS_ERROR.test(normalize(el.textContent))) {
				el.textContent = termsError;
			}
		});
	}

	function hideEnglishDescriptions(root) {
		root.querySelectorAll(".wc-block-components-checkout-step__description").forEach(function (el) {
			if (!el.hidden && EN_DESC.test(normalize(el.textContent))) {
				el.hidden = true;
			}
		});
	}

	function scan() {
		var root = document.querySelector(".ff-checkout .wc-block-checkout");
		if (!root) {
			return;
		}
		placeBuyArea(root);
		translateButton(root);
		translateTerms(root);
		translateErrors(root);
		hideEnglishDescriptions(root);
	}

	function boot() {
		var page = document.querySelector(".ff-checkout");
		if (!page) {
			return;
		}
		scan();

		var queued = false;
		new MutationObserver(function () {
			if (queued) {
				return;
			}
			queued = true;
			requestAnimationFrame(function () {
				queued = false;
				scan();
			});
		}).observe(page, { childList: true, subtree: true, characterData: true });
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
