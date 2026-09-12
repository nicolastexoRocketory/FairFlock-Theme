/**
 * Homepage product + reviews + card-grid tracks — prev/next mirrors styleguide.
 * Re-binds after Woo product collections hydrate (async).
 */
(function () {
	var bound = new WeakSet();

	function navControl(section, selector) {
		var el = section.querySelector(selector);
		if (!el) {
			return null;
		}
		if (el.matches("a, button")) {
			return el;
		}
		return el.querySelector(".wp-block-button__link, a, button") || el;
	}

	function setDisabled(control, disabled) {
		if (!control) {
			return;
		}
		control.setAttribute("aria-disabled", disabled ? "true" : "false");
		if ("disabled" in control) {
			control.disabled = disabled;
		}
		var wrap = control.closest(".ff-card-slider__btn, .ff-prod-slider__btn, .wp-block-button");
		if (wrap) {
			wrap.classList.toggle("is-disabled", disabled);
		}
	}

	function equalizeHeights(track, cardSelector) {
		if (!track) {
			return;
		}
		/* Product cards use aspect-ratio images — JS minHeight caused huge cards. */
		if (
			track.classList.contains("wc-block-product-template") ||
			track.classList.contains("ff-prod-slider__track") ||
			track.closest(".ff-prod-slider__track")
		) {
			return;
		}
		var cards = Array.prototype.slice.call(track.querySelectorAll(cardSelector));
		if (!cards.length) {
			cards = Array.prototype.slice.call(track.children);
		}
		if (!cards.length) {
			return;
		}

		cards.forEach(function (card) {
			card.style.minHeight = "";
		});

		if (
			track.classList.contains("ff-bento") ||
			track.classList.contains("ff-collections-grid")
		) {
			return;
		}

		var max = 0;
		cards.forEach(function (card) {
			max = Math.max(max, card.getBoundingClientRect().height);
		});

		if (max > 0) {
			cards.forEach(function (card) {
				card.style.minHeight = Math.ceil(max) + "px";
			});
		}
	}

	function bindTrack(track, prev, next, cardSelector) {
		if (!track || bound.has(track)) {
			return;
		}
		bound.add(track);

		function step() {
			var card = track.querySelector(cardSelector) || track.firstElementChild;
			if (!card) {
				return Math.max(280, Math.floor(track.clientWidth * 0.85));
			}
			var styles = window.getComputedStyle(track);
			var gap = parseFloat(styles.columnGap || styles.gap) || 16;
			return card.getBoundingClientRect().width + gap;
		}

		function updateNav() {
			if (!prev && !next) {
				return;
			}
			var max = track.scrollWidth - track.clientWidth - 2;
			var atStart = track.scrollLeft <= 2;
			var atEnd = track.scrollLeft >= max || max <= 0;
			setDisabled(prev, atStart || max <= 0);
			setDisabled(next, atEnd || max <= 0);
		}

		function sync() {
			equalizeHeights(track, cardSelector);
			updateNav();
		}

		function onPrev(event) {
			event.preventDefault();
			event.stopPropagation();
			track.scrollBy({ left: -step(), behavior: "smooth" });
			window.setTimeout(updateNav, 350);
		}

		function onNext(event) {
			event.preventDefault();
			event.stopPropagation();
			track.scrollBy({ left: step(), behavior: "smooth" });
			window.setTimeout(updateNav, 350);
		}

		if (prev) {
			prev.addEventListener("click", onPrev);
		}
		if (next) {
			next.addEventListener("click", onNext);
		}
		track.addEventListener("scroll", updateNav, { passive: true });
		window.addEventListener("resize", sync);
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(sync);
		}
		requestAnimationFrame(sync);
		window.setTimeout(sync, 100);
		window.setTimeout(sync, 600);
	}

	function productTrack(section) {
		var root = section.querySelector(".ff-prod-slider") || section;
		return (
			root.querySelector(".ff-prod-slider__track .wc-block-product-template") ||
			root.querySelector(".ff-prod-slider__track ul.wc-block-product-template") ||
			root.querySelector(".ff-prod-slider__track ul") ||
			section.querySelector(".ff-prod-slider__track .wc-block-product-template") ||
			section.querySelector(".ff-prod-slider__track ul") ||
			root.querySelector(".ff-prod-slider__track")
		);
	}

	function initProducts(section) {
		bindTrack(
			productTrack(section),
			navControl(section, ".ff-card-slider__btn--prev, .ff-prod-slider__btn--prev, [data-ff-prod-prev]"),
			navControl(section, ".ff-card-slider__btn--next, .ff-prod-slider__btn--next, [data-ff-prod-next]"),
			".wc-block-product, .wc-block-product-template__product, li.wc-block-product"
		);
	}

	function initReviews(root) {
		bindTrack(
			root.querySelector(".ff-reviews-track"),
			navControl(root, ".ff-card-slider__btn--prev, [data-ff-reviews-prev]"),
			navControl(root, ".ff-card-slider__btn--next, [data-ff-reviews-next]"),
			".ff-review, .wp-block-group.ff-review"
		);
	}

	function initCardSection(section, trackSelector) {
		bindTrack(
			section.querySelector(trackSelector),
			navControl(section, ".ff-card-slider__btn--prev, [data-ff-cards-prev]"),
			navControl(section, ".ff-card-slider__btn--next, [data-ff-cards-next]"),
			".ff-collection-card, .ff-bento-card, .wp-block-group.ff-collection-card, .wp-block-group.ff-bento-card"
		);
	}

	function boot() {
		var productSections = document.querySelectorAll(".ff-pattern-products--woo");
		if (productSections.length) {
			productSections.forEach(initProducts);
		} else {
			document.querySelectorAll(".ff-prod-slider").forEach(function (root) {
				var section = root.closest(".ff-pattern-products--woo") || root.parentElement || root;
				initProducts(section);
			});
		}
		document.querySelectorAll(".ff-reviews-slider").forEach(initReviews);
		document.querySelectorAll(".ff-pattern-collections").forEach(function (section) {
			initCardSection(section, ".ff-collections-grid");
		});
		document.querySelectorAll(".ff-pattern-bento").forEach(function (section) {
			initCardSection(section, ".ff-bento");
		});
	}

	function observeProducts() {
		var roots = document.querySelectorAll(".ff-pattern-products--woo, .ff-prod-slider");
		if (!roots.length || typeof MutationObserver === "undefined") {
			return;
		}
		var obs = new MutationObserver(function () {
			boot();
		});
		roots.forEach(function (root) {
			obs.observe(root, { childList: true, subtree: true });
		});
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", function () {
			boot();
			observeProducts();
		});
	} else {
		boot();
		observeProducts();
	}

	window.addEventListener("load", boot);
})();
