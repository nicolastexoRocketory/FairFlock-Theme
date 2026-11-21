/**
 * Checkout: German CTA / terms checkbox, hide English blurbs.
 *
 * @package FairFlock
 * @since 0.9.302
 */
(function () {
	"use strict";

	var cfg = window.fairflockCheckout || {};
	var cta = typeof cfg.cta === "string" && cfg.cta ? cfg.cta : "Jetzt kaufen";
	var termsHtml = typeof cfg.terms === "string" ? cfg.terms : "";
	var termsError =
		typeof cfg.termsError === "string" && cfg.termsError
			? cfg.termsError
			: "Bitte bestätige AGB und Widerrufsbelehrung, um die Bestellung abzuschließen.";
	var EN_DESC =
		/\b(enter your|select a|choose a|add a note|shipping options|contact details|billing address|payment options|we'll use this|we will use this|for order updates|with your order|terms and conditions)\b/i;

	function isEnglishCta(text) {
		var n = String(text || "")
			.replace(/\s+/g, " ")
			.trim()
			.toLowerCase();
		return n === "buy now" || n === "place order";
	}

	function needsTermsRewrite(text) {
		var n = String(text || "").toLowerCase();
		if (!n.trim()) {
			return true;
		}
		return (
			n.indexOf("with your order") !== -1 ||
			n.indexOf("terms and conditions") !== -1 ||
			n.indexOf("cancellation policy") !== -1 ||
			n.indexOf("you must accept our") !== -1 ||
			n.indexOf("to complete the order") !== -1 ||
			n.indexOf("privacy policy") !== -1 ||
			/* Alte Button-Lösung ohne Checkbox — Text ersetzen */
			n.indexOf("mit deiner bestellung bestätigst du") !== -1
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

	function ensureTermsCheckbox(termsRoot) {
		if (!termsRoot || termsRoot.querySelector(".wc-block-components-checkbox__input")) {
			return termsRoot.querySelector(".wc-block-components-checkbox__input");
		}
		if (termsRoot.getAttribute("data-ff-terms-box") === "1") {
			return termsRoot.querySelector(".ff-checkout-terms-input");
		}

		var wrap = document.createElement("label");
		wrap.className = "wc-block-components-checkbox ff-checkout-terms-box";
		wrap.setAttribute("data-ff-terms-box", "1");

		var input = document.createElement("input");
		input.type = "checkbox";
		input.className =
			"wc-block-components-checkbox__input ff-checkout-terms-input";
		input.id = "ff-checkout-terms-accept";
		input.name = "ff-checkout-terms-accept";
		input.setAttribute("aria-required", "true");

		var text = document.createElement("span");
		text.className = "wc-block-components-checkbox__label";
		text.innerHTML = termsHtml || termsRoot.innerHTML;

		/* Bestehenden Hinweistext in die Checkbox-Label-Zeile legen */
		termsRoot.innerHTML = "";
		wrap.appendChild(input);
		wrap.appendChild(text);
		termsRoot.appendChild(wrap);
		termsRoot.setAttribute("data-ff-terms-box", "1");

		input.addEventListener("change", function () {
			syncTermsAcceptance(input.checked);
		});

		return input;
	}

	function syncTermsAcceptance(checked) {
		/* Germanized / klassische Felder mitziehen */
		document
			.querySelectorAll(
				'input[name="legal"], input#legal, input[name="terms"], input#terms, input.woocommerce-form__input-checkbox[name="terms"]'
			)
			.forEach(function (el) {
				if (el === document.activeElement) {
					return;
				}
				el.checked = !!checked;
				el.dispatchEvent(new Event("change", { bubbles: true }));
			});

		try {
			var data = window.wc && window.wc.wcBlocksData;
			var wpData = window.wp && window.wp.data;
			if (!data || !wpData || !wpData.dispatch) {
				return;
			}
			var checkoutStore = data.checkoutStore || "wc/store/checkout";
			var validationStore = data.validationStore || "wc/store/validation";
			var dispatchCheckout = wpData.dispatch(checkoutStore);
			var dispatchValidation = wpData.dispatch(validationStore);

			if (dispatchCheckout) {
				if (typeof dispatchCheckout.__internalSetTermsCheckbox === "function") {
					dispatchCheckout.__internalSetTermsCheckbox(!!checked);
				} else if (typeof dispatchCheckout.setTermsAccepted === "function") {
					dispatchCheckout.setTermsAccepted(!!checked);
				}
			}

			if (dispatchValidation) {
				var ids = [
					"terms-and-conditions",
					"terms",
					"legal",
					"woocommerce-gzd-checkbox-terms",
				];
				if (checked && typeof dispatchValidation.clearValidationError === "function") {
					ids.forEach(function (id) {
						dispatchValidation.clearValidationError(id);
					});
				} else if (
					!checked &&
					typeof dispatchValidation.setValidationErrors === "function"
				) {
					dispatchValidation.setValidationErrors({
						"terms-and-conditions": {
							message: termsError,
							hidden: true,
						},
					});
				}
			}
		} catch (e) {
			/* Store API optional */
		}
	}

	function rewriteTerms(root) {
		if (!root) {
			return;
		}

		var termsRoot =
			root.closest(".wc-block-checkout__terms, .wp-block-woocommerce-checkout-terms-block") ||
			(root.matches &&
			root.matches(".wc-block-checkout__terms, .wp-block-woocommerce-checkout-terms-block")
				? root
				: null) ||
			root.querySelector(
				".wc-block-checkout__terms, .wp-block-woocommerce-checkout-terms-block"
			);

		if (!termsRoot) {
			if (root.classList && root.classList.contains("wc-block-components-checkbox")) {
				var labelOnly = root.querySelector(".wc-block-components-checkbox__label");
				if (labelOnly && termsHtml && needsTermsRewrite(labelOnly.textContent || "")) {
					labelOnly.innerHTML = termsHtml;
					labelOnly.setAttribute("data-ff-terms", "1");
				}
			}
			return;
		}

		var input = ensureTermsCheckbox(termsRoot);
		var label = termsRoot.querySelector(".wc-block-components-checkbox__label");
		if (label && termsHtml && label.getAttribute("data-ff-terms") !== "1") {
			if (needsTermsRewrite(label.textContent || "") || !label.textContent.trim()) {
				label.innerHTML = termsHtml;
			}
			label.setAttribute("data-ff-terms", "1");
		}

		if (input && input.getAttribute("data-ff-terms-bound") !== "1") {
			input.setAttribute("data-ff-terms-bound", "1");
			input.addEventListener("change", function () {
				syncTermsAcceptance(input.checked);
			});
			syncTermsAcceptance(input.checked);
		}
	}

	function rewriteGermanizedErrors(scope) {
		var root = scope && scope.querySelectorAll ? scope : document;
		root.querySelectorAll(
			".wc-block-components-validation-error, .wc-block-components-notice-banner__content, .woocommerce-error, .wc-block-components-notice-banner"
		).forEach(function (el) {
			var text = (el.textContent || "").replace(/\s+/g, " ").trim();
			if (!text) {
				return;
			}
			if (
				/to complete the order you have to accept/i.test(text) ||
				(/accept/i.test(text) && /terms/i.test(text) && /cancellation|widerruf/i.test(text))
			) {
				el.innerHTML = termsError;
			}
		});
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

	/**
	 * AGB direkt über „Jetzt kaufen“: Terms in den Actions-Block vor den CTA ziehen.
	 * CSS-order allein reicht nicht zuverlässig (Panel/Sidebar/display:contents).
	 */
	function placeTermsAboveBuy() {
		var page = document.querySelector(".ff-checkout-page");
		if (!page) {
			return;
		}

		var actions = page.querySelector(
			".wp-block-woocommerce-checkout-actions-block, .wc-block-checkout__actions"
		);
		var terms = page.querySelector(
			".wp-block-woocommerce-checkout-terms-block, .wc-block-checkout__terms"
		);
		var btn = page.querySelector(
			".wc-block-components-checkout-place-order-button, button.wc-block-components-checkout-place-order-button"
		);

		if (!actions || !terms || !btn) {
			return;
		}

		/* Anker: Button selbst oder sein Wrapper direkt unter Actions. */
		var anchor = btn;
		while (anchor.parentElement && anchor.parentElement !== actions) {
			anchor = anchor.parentElement;
		}
		if (anchor.parentElement !== actions) {
			anchor = actions.firstElementChild;
		}

		/* Schon korrekt: Terms ist Kind von Actions und steht vor dem CTA. */
		if (terms.parentElement === actions) {
			var next = terms.nextElementSibling;
			while (next && next !== anchor && !(anchor && next.contains && next.contains(btn))) {
				next = next.nextElementSibling;
			}
			if (next === anchor || (next && next.contains && next.contains(btn))) {
				terms.setAttribute("data-ff-terms-placed", "1");
				return;
			}
		}

		try {
			actions.insertBefore(terms, anchor || null);
			terms.setAttribute("data-ff-terms-placed", "1");
		} catch (e) {
			/* DOM kann während React-Render kurz inkonsistent sein */
		}
	}

	function scan(scope) {
		var root = scope && scope.querySelectorAll ? scope : document;
		root.querySelectorAll(
			".wc-block-components-checkout-place-order-button, button.wc-block-components-checkout-place-order-button"
		).forEach(rewriteButton);
		root.querySelectorAll(
			".wc-block-checkout__terms, .wp-block-woocommerce-checkout-terms-block, .wc-block-components-checkbox"
		).forEach(rewriteTerms);
		placeTermsAboveBuy();
		rewriteGermanizedErrors(root);
		hideEnglishDescriptions(root);
	}

	function boot() {
		scan(document);
		if (typeof MutationObserver === "undefined") {
			return;
		}
		var scheduled = false;
		function schedule() {
			if (scheduled) {
				return;
			}
			scheduled = true;
			requestAnimationFrame(function () {
				scheduled = false;
				scan(document);
			});
		}
		var obs = new MutationObserver(function (records) {
			var needs = false;
			for (var i = 0; i < records.length; i++) {
				var nodes = records[i].addedNodes;
				for (var j = 0; j < nodes.length; j++) {
					if (nodes[j].nodeType === 1) {
						needs = true;
						break;
					}
				}
				if (needs) {
					break;
				}
				if (records[i].type === "characterData") {
					needs = true;
					break;
				}
			}
			if (needs) {
				schedule();
			}
		});
		obs.observe(document.body, { childList: true, subtree: true, characterData: true });
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
