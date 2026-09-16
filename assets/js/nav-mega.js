/**
 * Opt-in Mega menu: opens editable template-part panel under the header.
 *
 * Trigger: Navigation item with block style “Mega” (is-style-ff-mega).
 * Content: Template part “Mega-Menü” (.ff-mega-panel-slot).
 * Position: flush under header bar, full viewport width (no edge/nav gap).
 */
(function () {
	var MQ = "(min-width: 961px)";
	var CLOSE_MS = 220;
	var closeTimer = null;
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

	function clearInlineBox(slot) {
		if (!slot) {
			return;
		}
		["left", "right", "top", "width", "max-width", "transform"].forEach(function (prop) {
			slot.style.removeProperty(prop);
		});
	}

	function positionSlot(slot) {
		var bar = headerBar();
		if (!slot || !bar || !isDesktop()) {
			clearInlineBox(slot);
			return;
		}

		var rect = bar.getBoundingClientRect();
		/* Bündig unter der Header-Leiste, randlos über die volle Viewport-Breite (CSS). */
		slot.style.setProperty("top", Math.round(rect.bottom) + "px", "important");
		slot.style.removeProperty("left");
		slot.style.removeProperty("right");
		slot.style.removeProperty("width");
		slot.style.removeProperty("max-width");
		slot.style.removeProperty("transform");
	}

	function openMega(item) {
		clearCloseTimer();
		var slot = panelSlot();
		if (!slot || !isDesktop()) {
			return;
		}
		openItem = item || openItem;
		document.documentElement.classList.add("is-ff-mega-open");
		slot.classList.add("is-open");
		slot.setAttribute("aria-hidden", "false");
		positionSlot(slot);
		if (openItem) {
			openItem.classList.add("is-ff-mega-active");
			openItem.setAttribute("aria-expanded", "true");
		}
	}

	function closeMega() {
		clearCloseTimer();
		var slot = panelSlot();
		document.documentElement.classList.remove("is-ff-mega-open");
		if (slot) {
			slot.classList.remove("is-open");
			slot.setAttribute("aria-hidden", "true");
			clearInlineBox(slot);
		}
		megaItems().forEach(function (item) {
			item.classList.remove("is-ff-mega-active");
			item.setAttribute("aria-expanded", "false");
		});
		openItem = null;
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

		items.forEach(bindItem);
		bindSlot(slot);

		window.addEventListener(
			"resize",
			function () {
				if (!isDesktop()) {
					closeMega();
					return;
				}
				if (slot.classList.contains("is-open")) {
					positionSlot(slot);
				}
			},
			{ passive: true }
		);

		window.addEventListener(
			"scroll",
			function () {
				if (slot.classList.contains("is-open") && isDesktop()) {
					positionSlot(slot);
				}
			},
			{ passive: true }
		);

		document.addEventListener("keydown", function (event) {
			if (event.key === "Escape" && slot.classList.contains("is-open")) {
				closeMega();
			}
		});
	}

	ready(boot);
})();
