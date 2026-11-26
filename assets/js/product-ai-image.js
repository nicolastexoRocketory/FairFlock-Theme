/**
 * Inject minimalist “KI-Bild” tags on flagged product images.
 */
(function () {
	"use strict";

	var TAG_CLASS = "ff-ki-bild-tag";
	var HOST_CLASS = "ff-has-ki-bild";

	function tagHtml() {
		return '<span class="' + TAG_CLASS + '">KI-Bild</span>';
	}

	function ensureTag(host) {
		if (!host || host.querySelector("." + TAG_CLASS)) return;
		host.classList.add(HOST_CLASS);
		host.insertAdjacentHTML("afterbegin", tagHtml());
	}

	function hostForImg(img) {
		if (!img) return null;
		if (
			img.closest(
				".ff-mega-panel, .ff-mega-panel-slot, .ff-menu-sub__mega, .ff-mega-cover-link, .ff-band"
			)
		) {
			return null;
		}
		return (
			img.closest(
				".woocommerce-product-gallery__image, .wc-block-woocommerce-product-gallery__image, .wp-block-woocommerce-product-image, .ff-product-peek__slide, .ff-product-modal__slide, .wc-block-components-product-image, figure"
			) || img.parentElement
		);
	}

	function isKiImg(img) {
		if (!img) return false;
		if (img.getAttribute("data-ff-ki-bild") === "1") return true;
		if (img.classList && img.classList.contains("ff-ki-bild-img")) return true;
		return false;
	}

	function scan(root) {
		var scope = root && root.querySelectorAll ? root : document;
		scope.querySelectorAll("img[data-ff-ki-bild='1'], img.ff-ki-bild-img").forEach(function (img) {
			ensureTag(hostForImg(img));
		});
	}

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	ready(function () {
		scan(document);
		window.setTimeout(function () {
			scan(document);
		}, 500);

		if (typeof MutationObserver !== "undefined") {
			var obs = new MutationObserver(function (mutations) {
				mutations.forEach(function (m) {
					m.addedNodes.forEach(function (node) {
						if (!node || node.nodeType !== 1) return;
						if (isKiImg(node)) ensureTag(hostForImg(node));
						else if (node.querySelectorAll) scan(node);
					});
				});
			});
			obs.observe(document.body, { childList: true, subtree: true });
		}
	});

	window.fairflockEnsureKiBildTag = ensureTag;
	window.fairflockKiBildTagHtml = tagHtml;
})();
