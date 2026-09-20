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

	var DESKTOP_MQ = "(min-width: 961px)";
	var COLLECTIONS_ROWS = 3;

	function collectionsVisibleCount(grid) {
		var raw = window.getComputedStyle(grid).getPropertyValue("--ff-collections-cols");
		var cols = parseInt(raw, 10);
		if (!cols || cols < 2) {
			cols = 3;
		}
		if (cols > 4) {
			cols = 4;
		}
		return cols * COLLECTIONS_ROWS;
	}

	function initCollectionsExpand(section) {
		var grid = section.querySelector(".ff-collections-grid");
		var expand = section.querySelector(".ff-collections-expand");
		if (!grid || !expand) {
			return;
		}
		if (bound.has(expand)) {
			return;
		}

		var wrapMore = expand.querySelector(".ff-collections-expand__btn--more");
		var wrapLess = expand.querySelector(".ff-collections-expand__btn--less");
		var btnMore = wrapMore
			? wrapMore.querySelector(".wp-block-button__link, a, button")
			: null;
		var btnLess = wrapLess
			? wrapLess.querySelector(".wp-block-button__link, a, button")
			: null;
		var btnLegacy = expand.querySelector(
			".ff-collections-expand__btn:not(.ff-collections-expand__btn--more):not(.ff-collections-expand__btn--less) .wp-block-button__link, .ff-collections-expand__btn:not(.ff-collections-expand__btn--more):not(.ff-collections-expand__btn--less) a"
		);

		if (!btnMore && !btnLegacy) {
			return;
		}
		bound.add(expand);

		var labelMore =
			(btnMore && (btnMore.textContent || "").trim()) ||
			(btnLegacy && (btnLegacy.getAttribute("data-ff-label-more") || btnLegacy.textContent || "").trim()) ||
			"Mehr anzeigen";
		var labelLess =
			(btnLess && (btnLess.textContent || "").trim()) ||
			(btnLegacy && (btnLegacy.getAttribute("data-ff-label-less") || "").trim()) ||
			"Weniger anzeigen";

		function cardCount() {
			return grid.querySelectorAll(".ff-collection-card").length;
		}

		function isDesktop() {
			return window.matchMedia(DESKTOP_MQ).matches;
		}

		function setButtonState(expanded) {
			if (wrapMore) {
				wrapMore.hidden = !!expanded;
				wrapMore.setAttribute("aria-hidden", expanded ? "true" : "false");
			}
			if (wrapLess) {
				wrapLess.hidden = !expanded;
				wrapLess.setAttribute("aria-hidden", expanded ? "false" : "true");
			}
			if (btnMore) {
				btnMore.setAttribute("aria-expanded", expanded ? "true" : "false");
			}
			if (btnLess) {
				btnLess.setAttribute("aria-expanded", expanded ? "true" : "false");
			}
			if (btnLegacy && !btnMore) {
				btnLegacy.textContent = expanded ? labelLess : labelMore;
				btnLegacy.setAttribute("aria-expanded", expanded ? "true" : "false");
			}
		}

		function setExpanded(expanded) {
			grid.classList.toggle("is-collapsed", !expanded);
			grid.classList.toggle("is-expanded", expanded);
			setButtonState(expanded);
		}

		function syncVisibility() {
			var visible = collectionsVisibleCount(grid);
			var needsToggle = isDesktop() && cardCount() > visible;
			expand.hidden = !needsToggle;
			expand.classList.toggle("is-visible", needsToggle);
			if (!needsToggle) {
				grid.classList.remove("is-collapsed", "is-expanded");
				setButtonState(false);
				return;
			}
			if (!grid.classList.contains("is-expanded")) {
				setExpanded(false);
			} else {
				setButtonState(true);
			}
		}

		function onToggle(event) {
			event.preventDefault();
			if (!isDesktop() || cardCount() <= collectionsVisibleCount(grid)) {
				return;
			}
			setExpanded(!grid.classList.contains("is-expanded"));
		}

		if (btnMore) {
			btnMore.setAttribute("role", "button");
			btnMore.addEventListener("click", onToggle);
		}
		if (btnLess) {
			btnLess.setAttribute("role", "button");
			btnLess.addEventListener("click", onToggle);
		}
		if (btnLegacy && !btnMore) {
			btnLegacy.setAttribute("role", "button");
			btnLegacy.addEventListener("click", onToggle);
		}

		setButtonState(false);
		window.addEventListener("resize", syncVisibility);
		syncVisibility();
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
			initCollectionsExpand(section);
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
