/**
 * Force canonical stroke-chevron (M6 9l6 6 6-6) on nav arrows.
 * Upgrades leftover ←/→ text in customized templates / page content.
 */
(function () {
	var PATH =
		'<path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/>';

	function svg(dir, size) {
		size = size || 16;
		return (
			'<svg class="ff-chevron ff-chevron--' +
			dir +
			'" viewBox="0 0 24 24" width="' +
			size +
			'" height="' +
			size +
			'" aria-hidden="true" focusable="false">' +
			PATH +
			"</svg>"
		);
	}

	function textIsArrow(el) {
		var t = (el.textContent || "").replace(/\s+/g, "");
		return t === "←" || t === "→" || t === "‹" || t === "›" || t === "<" || t === ">";
	}

	function dirFor(el) {
		if (
			el.matches(
				".is-prev, .ff-card-slider__btn--prev, .ff-prod-slider__btn--prev, [data-fs-prev], [data-ff-peek='prev'], [data-ff-gallery='prev']"
			) ||
			el.closest(
				".is-prev, .ff-card-slider__btn--prev, .ff-prod-slider__btn--prev, [data-fs-prev]"
			)
		) {
			return "left";
		}
		if (
			el.matches(
				".is-next, .ff-card-slider__btn--next, .ff-prod-slider__btn--next, [data-fs-next], [data-ff-peek='next'], [data-ff-gallery='next']"
			) ||
			el.closest(
				".is-next, .ff-card-slider__btn--next, .ff-prod-slider__btn--next, [data-fs-next]"
			)
		) {
			return "right";
		}
		var t = (el.textContent || "").replace(/\s+/g, "");
		if (t === "←" || t === "‹" || t === "<") return "left";
		return "right";
	}

	function upgrade(el) {
		if (!el || el.querySelector(".ff-chevron")) return;
		if (!textIsArrow(el)) return;
		var dir = dirFor(el);
		var size = el.closest(".slider-btn") ? 16 : 16;
		el.innerHTML = svg(dir, size);
	}

	function run(root) {
		root = root || document;
		var nodes = root.querySelectorAll(
			[
				".slider-btn[data-fs-prev]",
				".slider-btn[data-fs-next]",
				".ff-card-slider__btn--prev .wp-block-button__link",
				".ff-card-slider__btn--next .wp-block-button__link",
				".ff-prod-slider__btn--prev .wp-block-button__link",
				".ff-prod-slider__btn--next .wp-block-button__link",
				"a[href='#ff-prod-prev']",
				"a[href='#ff-prod-next']",
				"a[href='#ff-cards-prev']",
				"a[href='#ff-cards-next']",
				"a[href='#ff-reviews-prev']",
				"a[href='#ff-reviews-next']",
				".ff-product-peek__btn",
				".ff-product-modal__gallery-btn",
				".ff-product-table__arrow",
				".ff-product-table__gbtn"
			].join(",")
		);
		Array.prototype.forEach.call(nodes, upgrade);
	}

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	ready(function () {
		run();
		if (typeof MutationObserver !== "undefined") {
			var obs = new MutationObserver(function (muts) {
				for (var i = 0; i < muts.length; i++) {
					var m = muts[i];
					if (m.type === "childList") {
						Array.prototype.forEach.call(m.addedNodes, function (n) {
							if (n.nodeType !== 1) return;
							run(n);
						});
					}
				}
			});
			obs.observe(document.body, { childList: true, subtree: true });
		}
	});

	window.fairflockUpgradeChevrons = run;
})();
