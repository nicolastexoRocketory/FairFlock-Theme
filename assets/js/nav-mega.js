/**
 * Opt-in Mega menu: opens editable template-part panel under the header.
 *
 * Desktop: Navigation item with block style “Mega” (is-style-ff-mega) on hover.
 * Mobile/Tablet (≤960px): Burger opens the same panel as the menu (no drill menu).
 * Content: Template part “Mega-Menü” (.ff-mega-panel-slot) — moved to <body>
 * so fixed positioning is viewport-relative.
 * Motion: slide + fade open/close.
 */
(function () {
	var MQ = "(min-width: 961px)";
	var CLOSE_MS = 180;
	var ANIM_MS = 280;
	var closeTimer = null;
	var animTimer = null;
	var openItem = null;
	/** 'hover' = desktop nav trigger; 'panel' = burger / programmatic */
	var openMode = null;

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function isDesktop() {
		return window.matchMedia(MQ).matches;
	}

	function headerRoot() {
		return document.querySelector(".ff-header");
	}

	function headerBar() {
		var header = headerRoot();
		return (header && header.querySelector(".ff-header__bar")) || header;
	}

	function panelSlot() {
		return document.querySelector(".ff-mega-panel-slot");
	}

	function megaItems() {
		return Array.prototype.slice.call(
			document.querySelectorAll(
				".ff-header__nav .wp-block-navigation-submenu.is-style-ff-mega, .ff-header__nav .wp-block-navigation-item.is-style-ff-mega"
			)
		);
	}

	function clearCloseTimer() {
		if (closeTimer) {
			window.clearTimeout(closeTimer);
			closeTimer = null;
		}
	}

	function clearAnimTimer() {
		if (animTimer) {
			window.clearTimeout(animTimer);
			animTimer = null;
		}
	}

	function clearInlineBox(slot) {
		if (!slot) {
			return;
		}
		["left", "right", "top", "width", "max-width", "transform"].forEach(function (prop) {
			slot.style.removeProperty(prop);
		});
	}

	function setPanelBodyClass(on) {
		document.body.classList.toggle("is-ff-menu-open", !!on);
	}

	/**
	 * Escape header transform containing-block (same pattern as cart drawer).
	 */
	function ensureSlotOnBody(slot) {
		if (!slot || slot.parentElement === document.body) {
			return slot;
		}
		document.body.appendChild(slot);
		return slot;
	}

	function paintMegaSlotBackground(slot) {
		if (!slot) {
			return;
		}
		var fullBleed =
			openMode === "panel" ||
			!isDesktop() ||
			document.body.classList.contains("ff-nav-compact");
		if (!fullBleed) {
			slot.style.backgroundColor = "";
			return;
		}
		var panel = slot.querySelector(".ff-mega-panel");
		if (!panel) {
			return;
		}
		/* Match the Site-Editor panel color so overscroll never shows the page behind. */
		slot.style.backgroundColor = window.getComputedStyle(panel).backgroundColor;
	}

	function positionSlot(slot) {
		var bar = headerBar();
		if (!slot || !bar) {
			return;
		}

		var rect = bar.getBoundingClientRect();
		/* -1px overlap kills hairline gaps under the bar */
		var top = Math.max(0, Math.round(rect.bottom) - 1);
		slot.style.setProperty("top", top + "px", "important");

		/* Burger / compact desktop: full-bleed panel like ≤960px */
		var fullBleed =
			openMode === "panel" ||
			!isDesktop() ||
			document.body.classList.contains("ff-nav-compact");
		if (fullBleed) {
			slot.style.setProperty("left", "0", "important");
			slot.style.setProperty("right", "0", "important");
			slot.style.setProperty("width", "100%", "important");
			slot.style.setProperty("max-width", "none", "important");
			slot.style.setProperty(
				"max-height",
				"calc(100dvh - " + top + "px)",
				"important"
			);
			paintMegaSlotBackground(slot);
		} else {
			slot.style.removeProperty("left");
			slot.style.removeProperty("right");
			slot.style.removeProperty("width");
			slot.style.removeProperty("max-width");
			slot.style.removeProperty("max-height");
			slot.style.backgroundColor = "";
		}
		/* transform is owned by CSS animation — do not clear while open */
	}

	function isMegaOpen() {
		var slot = panelSlot();
		return !!(
			slot &&
			(slot.classList.contains("is-open") || slot.classList.contains("is-closing"))
		);
	}

	/**
	 * @param {Element|null} item
	 * @param {{ force?: boolean }} [opts] force=true opens on mobile (burger)
	 */
	function openMega(item, opts) {
		opts = opts || {};
		var force = opts.force === true;
		clearCloseTimer();
		clearAnimTimer();
		var slot = ensureSlotOnBody(panelSlot());
		if (!slot) {
			return;
		}
		if (!force && !isDesktop()) {
			return;
		}

		/* Suche weicht dem Mega-Menü — Nav bleibt hoverbar während die Suche offen ist. */
		if (typeof window.fairflockCloseSearch === "function") {
			window.fairflockCloseSearch();
		}

		openMode = force ? "panel" : "hover";
		openItem = item || openItem;
		slot.classList.remove("is-closing");
		document.documentElement.classList.add("is-ff-mega-open");
		if (openMode === "panel") {
			setPanelBodyClass(true);
		}
		positionSlot(slot);

		/* Force reflow so re-open still animates after a fast close */
		if (!slot.classList.contains("is-open")) {
			void slot.offsetWidth;
			slot.classList.add("is-open");
		}

		slot.setAttribute("aria-hidden", "false");
		if (openItem) {
			megaItems().forEach(function (el) {
				if (el !== openItem) {
					el.classList.remove("is-ff-mega-active");
					el.setAttribute("aria-expanded", "false");
				}
			});
			openItem.classList.add("is-ff-mega-active");
			openItem.setAttribute("aria-expanded", "true");
		}
	}

	function finishClose(slot) {
		if (!slot) {
			return;
		}
		slot.classList.remove("is-closing");
		slot.classList.remove("is-open");
		slot.setAttribute("aria-hidden", "true");
		clearInlineBox(slot);
	}

	function syncMegaDrillClass() {
		var open = !isDesktop() && !!document.querySelector(".ff-mega-list--parents .ff-mega-list__branch.is-ff-open");
		document.documentElement.classList.toggle("is-ff-mega-drill", open);
	}

	function paintCategoryKidsPanel(kids) {
		if (!kids) {
			return;
		}
		var panel = kids.closest(".ff-mega-panel");
		if (!panel) {
			kids.style.backgroundColor = "";
			kids.style.color = "";
			kids.style.padding = "";
			return;
		}
		var styles = window.getComputedStyle(panel);
		kids.style.backgroundColor = styles.backgroundColor;
		kids.style.color = styles.color;
		kids.style.padding = styles.padding;
	}

	function resetMegaCategoryBranches() {
		var roots = document.querySelectorAll(".ff-mega-list--parents");
		Array.prototype.forEach.call(roots, function (root) {
			Array.prototype.forEach.call(root.querySelectorAll(".ff-mega-list__branch"), function (item) {
				item.classList.remove("is-ff-open");
			});
			Array.prototype.forEach.call(root.querySelectorAll(".ff-mega-list__toggle"), function (button) {
				button.setAttribute("aria-expanded", "false");
			});
			Array.prototype.forEach.call(root.querySelectorAll(".ff-mega-list__kids"), function (kids) {
				kids.style.backgroundColor = "";
				kids.style.color = "";
				kids.style.padding = "";
			});
		});
		syncMegaDrillClass();
	}

	function setBranchOpen(item, open) {
		item.classList.toggle("is-ff-open", open);
		var button = item.querySelector(":scope > .ff-mega-list__row > .ff-mega-list__toggle");
		if (button) {
			button.setAttribute("aria-expanded", open ? "true" : "false");
		}
		var kids = item.querySelector(":scope > .ff-mega-list__kids");
		if (open && !isDesktop()) {
			paintCategoryKidsPanel(kids);
		} else if (kids) {
			kids.style.backgroundColor = "";
			kids.style.color = "";
			kids.style.padding = "";
		}
		syncMegaDrillClass();
	}

	function categoryTopItem(list, node) {
		var item = node && node.closest ? node.closest("li") : null;
		while (item && item.parentElement !== list) {
			item = item.parentElement ? item.parentElement.closest("li") : null;
		}
		return item && list.contains(item) ? item : null;
	}

	/* Desktop: open stays until another top-level category is chosen. */
	function stickCategoryFlyout(list, item) {
		if (!list || !isDesktop() || !item) {
			return;
		}
		if (item.classList.contains("ff-mega-list__branch")) {
			if (item.classList.contains("is-ff-open")) {
				return;
			}
			Array.prototype.forEach.call(list.children, function (sibling) {
				if (sibling !== item && sibling.classList && sibling.classList.contains("ff-mega-list__branch")) {
					setBranchOpen(sibling, false);
				}
			});
			setBranchOpen(item, true);
			return;
		}
		if (item.classList.contains("ff-mega-list__leaf")) {
			Array.prototype.forEach.call(list.children, function (sibling) {
				if (sibling.classList && sibling.classList.contains("ff-mega-list__branch")) {
					setBranchOpen(sibling, false);
				}
			});
		}
	}

	function bindMegaCategoryBranches() {
		document.addEventListener("click", function (event) {
			var back = event.target.closest(".ff-mega-list--parents .ff-mega-list__back-btn");
			if (back) {
				event.preventDefault();
				var backItem = back.closest(".ff-mega-list__branch");
				if (backItem) {
					setBranchOpen(backItem, false);
				}
				return;
			}

			var row = event.target.closest(".ff-mega-list--parents .ff-mega-list__row");
			if (!row) {
				return;
			}
			var item = row.closest(".ff-mega-list__branch");
			if (!item) {
				return;
			}
			/* Desktop: hover opens the flyout; the parent link navigates. */
			if (isDesktop()) {
				return;
			}
			event.preventDefault();
			var list = item.parentElement;
			if (list) {
				Array.prototype.forEach.call(list.children, function (sibling) {
					if (sibling !== item && sibling.classList && sibling.classList.contains("ff-mega-list__branch")) {
						setBranchOpen(sibling, false);
					}
				});
			}
			setBranchOpen(item, true);
		});

		document.addEventListener("mouseover", function (event) {
			if (!isDesktop()) {
				return;
			}
			var list = event.target.closest(".ff-mega-list--categories .ff-mega-list__tree");
			if (!list) {
				return;
			}
			stickCategoryFlyout(list, categoryTopItem(list, event.target));
		});

		document.addEventListener("focusin", function (event) {
			if (!isDesktop()) {
				return;
			}
			var list = event.target.closest(".ff-mega-list--categories .ff-mega-list__tree");
			if (!list) {
				return;
			}
			stickCategoryFlyout(list, categoryTopItem(list, event.target));
		});
	}

	function closeMega() {
		clearCloseTimer();
		clearAnimTimer();
		resetMegaCategoryBranches();
		var slot = panelSlot();
		document.documentElement.classList.remove("is-ff-mega-open");
		document.documentElement.classList.remove("is-ff-mega-drill");
		setPanelBodyClass(false);
		openMode = null;
		if (slot) {
			slot.style.backgroundColor = "";
		}

		megaItems().forEach(function (item) {
			item.classList.remove("is-ff-mega-active");
			item.setAttribute("aria-expanded", "false");
		});
		openItem = null;

		if (!slot || (!slot.classList.contains("is-open") && !slot.classList.contains("is-closing"))) {
			return;
		}

		slot.classList.add("is-closing");
		slot.classList.remove("is-open");
		slot.setAttribute("aria-hidden", "true");

		animTimer = window.setTimeout(function () {
			finishClose(slot);
			animTimer = null;
		}, ANIM_MS);
	}

	function toggleMega() {
		if (isMegaOpen()) {
			closeMega();
		} else {
			openMega(null, { force: true });
		}
	}

	function scheduleClose() {
		if (openMode === "panel") {
			return;
		}
		clearCloseTimer();
		closeTimer = window.setTimeout(function () {
			var slot = panelSlot();
			var overItem = megaItems().some(function (item) {
				return item.matches(":hover, :focus-within");
			});
			var overSlot = slot && slot.matches(":hover, :focus-within");
			if (!overItem && !overSlot) {
				closeMega();
			}
		}, CLOSE_MS);
	}

	function bindItem(item) {
		if (item.dataset.ffMegaBound === "1") {
			return;
		}
		item.dataset.ffMegaBound = "1";
		item.setAttribute("aria-expanded", "false");
		item.setAttribute("aria-haspopup", "true");

		item.addEventListener("mouseenter", function () {
			openMega(item);
		});
		item.addEventListener("focusin", function () {
			openMega(item);
		});
		item.addEventListener("mouseleave", scheduleClose);
		item.addEventListener("focusout", scheduleClose);

		var toggle = item.querySelector(":scope > .wp-block-navigation-submenu__toggle, :scope > button");
		if (toggle) {
			toggle.addEventListener("click", function (event) {
				if (!isDesktop()) {
					return;
				}
				event.preventDefault();
				if (item.classList.contains("is-ff-mega-active")) {
					closeMega();
				} else {
					openMega(item);
				}
			});
		}
	}

	function bindSlot(slot) {
		if (!slot || slot.dataset.ffMegaBound === "1") {
			return;
		}
		slot.dataset.ffMegaBound = "1";
		slot.setAttribute("aria-hidden", "true");
		slot.addEventListener("mouseenter", function () {
			if (openMode === "panel") {
				return;
			}
			clearCloseTimer();
			openMega(openItem);
		});
		slot.addEventListener("focusin", function () {
			if (openMode === "panel") {
				return;
			}
			clearCloseTimer();
			openMega(openItem);
		});
		slot.addEventListener("mouseleave", scheduleClose);
		slot.addEventListener("focusout", scheduleClose);
	}

	function boot() {
		var items = megaItems();
		var slot = panelSlot();
		if (!slot) {
			return;
		}

		ensureSlotOnBody(slot);
		if (items.length) {
			items.forEach(bindItem);
			bindSlot(slot);
		} else {
			slot.setAttribute("aria-hidden", "true");
		}

		window.addEventListener(
			"resize",
			function () {
				if (!slot) {
					return;
				}
				if (slot.classList.contains("is-open") || slot.classList.contains("is-closing")) {
					if (openMode === "hover" && !isDesktop()) {
						closeMega();
						return;
					}
					positionSlot(slot);
				}
			},
			{ passive: true }
		);

		window.addEventListener(
			"scroll",
			function () {
				if (
					slot &&
					(slot.classList.contains("is-open") || slot.classList.contains("is-closing"))
				) {
					positionSlot(slot);
				}
			},
			{ passive: true }
		);

		document.addEventListener("keydown", function (event) {
			if (event.key !== "Escape" || !slot) {
				return;
			}
			var drill = slot.querySelector(".ff-mega-list__branch.is-ff-open");
			if (drill && !isDesktop()) {
				setBranchOpen(drill, false);
				return;
			}
			if (slot.classList.contains("is-open") || slot.classList.contains("is-closing")) {
				closeMega();
			}
		});

		window.addEventListener(
			"resize",
			function () {
				if (isDesktop()) {
					document.documentElement.classList.remove("is-ff-mega-drill");
				} else {
					syncMegaDrillClass();
				}
			},
			{ passive: true }
		);
	}

	bindMegaCategoryBranches();

	window.fairflockOpenMega = function () {
		openMega(null, { force: true });
	};
	window.fairflockCloseMega = closeMega;
	window.fairflockToggleMega = toggleMega;
	window.fairflockIsMegaOpen = isMegaOpen;

	ready(boot);
})();
