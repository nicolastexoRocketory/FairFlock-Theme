/**
 * Opt-in Mega menu: opens editable template-part panel under the header.
 *
 * Trigger: Navigation item with block style “Mega” (is-style-ff-mega).
 * Content: Template part “Mega-Menü” (.ff-mega-panel-slot) — moved to <body>
 * so fixed positioning is viewport-relative (header translate would otherwise
 * create a containing block and leave a gap).
 * Motion: slide + fade open/close.
 */
(function () {
	var MQ = "(min-width: 961px)";
	var CLOSE_MS = 180;
	var ANIM_MS = 280;
	var closeTimer = null;
	var animTimer = null;
	var openItem = null;

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

	function positionSlot(slot) {
		var bar = headerBar();
		if (!slot || !bar || !isDesktop()) {
			return;
		}

		var rect = bar.getBoundingClientRect();
		/* -1px overlap kills hairline gaps under the bar */
		var top = Math.max(0, Math.round(rect.bottom) - 1);
		slot.style.setProperty("top", top + "px", "important");
		slot.style.removeProperty("left");
		slot.style.removeProperty("right");
		slot.style.removeProperty("width");
		slot.style.removeProperty("max-width");
		/* transform is owned by CSS animation — do not clear while open */
	}

	function openMega(item) {
		clearCloseTimer();
		clearAnimTimer();
		var slot = ensureSlotOnBody(panelSlot());
		if (!slot || !isDesktop()) {
			return;
		}

		openItem = item || openItem;
		slot.classList.remove("is-closing");
		document.documentElement.classList.add("is-ff-mega-open");
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

	function closeMega() {
		clearCloseTimer();
		clearAnimTimer();
		var slot = panelSlot();
		document.documentElement.classList.remove("is-ff-mega-open");

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

	function scheduleClose() {
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
			clearCloseTimer();
			openMega(openItem);
		});
		slot.addEventListener("focusin", function () {
			clearCloseTimer();
			openMega(openItem);
		});
		slot.addEventListener("mouseleave", scheduleClose);
		slot.addEventListener("focusout", scheduleClose);
	}

	function boot() {
		var items = megaItems();
		var slot = panelSlot();
		if (!items.length || !slot) {
			return;
		}

		ensureSlotOnBody(slot);
		items.forEach(bindItem);
		bindSlot(slot);

		window.addEventListener(
			"resize",
			function () {
				if (!isDesktop()) {
					closeMega();
					return;
				}
				if (slot.classList.contains("is-open") || slot.classList.contains("is-closing")) {
					positionSlot(slot);
				}
			},
			{ passive: true }
		);

		window.addEventListener(
			"scroll",
			function () {
				if (
					isDesktop() &&
					(slot.classList.contains("is-open") || slot.classList.contains("is-closing"))
				) {
					positionSlot(slot);
				}
			},
			{ passive: true }
		);

		document.addEventListener("keydown", function (event) {
			if (
				event.key === "Escape" &&
				(slot.classList.contains("is-open") || slot.classList.contains("is-closing"))
			) {
				closeMega();
			}
		});
	}

	ready(boot);
})();
