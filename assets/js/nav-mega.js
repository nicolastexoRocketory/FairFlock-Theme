/**
 * Positions Mega submenu panels under the header bar (shell width).
 */
(function () {
	var MQ = "(min-width: 961px)";

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

	function headerBar(nav) {
		var header = nav.closest(".ff-header");
		return (header && header.querySelector(".ff-header__bar")) || header || nav;
	}

	function panel(item) {
		return item.querySelector(":scope > .wp-block-navigation__submenu-container");
	}

	function positionPanel(item) {
		var el = panel(item);
		var nav = item.closest(".ff-header__nav");
		if (!el || !nav || !isDesktop()) {
			if (el) {
				el.style.removeProperty("left");
				el.style.removeProperty("top");
				el.style.removeProperty("width");
				el.style.removeProperty("max-width");
				el.style.removeProperty("transform");
			}
			return;
		}

		var bar = headerBar(nav);
		var rect = bar.getBoundingClientRect();
		var gutter = 16;
		var width = Math.min(rect.width, window.innerWidth - gutter * 2);
		var left = Math.max(gutter, Math.min(rect.left, window.innerWidth - width - gutter));

		el.style.setProperty("left", left + "px", "important");
		el.style.setProperty("top", rect.bottom + 8 + "px", "important");
		el.style.setProperty("width", width + "px", "important");
		el.style.setProperty("max-width", "calc(100vw - " + gutter * 2 + "px)", "important");
		el.style.setProperty("transform", "none", "important");
		el.style.setProperty("right", "auto", "important");
	}

	function clearPanel(item) {
		var el = panel(item);
		if (!el) {
			return;
		}
		el.style.removeProperty("left");
		el.style.removeProperty("top");
		el.style.removeProperty("width");
		el.style.removeProperty("max-width");
		el.style.removeProperty("transform");
		el.style.removeProperty("right");
	}

	function megaItems(root) {
		return Array.prototype.slice.call(
			(root || document).querySelectorAll(
				".ff-header__nav .wp-block-navigation-submenu.is-style-ff-mega, .ff-header__nav .wp-block-navigation-item.is-style-ff-mega"
			)
		);
	}

	function bindItem(item) {
		if (item.dataset.ffMegaBound === "1") {
			return;
		}
		item.dataset.ffMegaBound = "1";

		function open() {
			document.documentElement.classList.add("is-ff-mega-open");
			positionPanel(item);
		}

		function close() {
			if (!document.querySelector(".ff-header__nav .is-style-ff-mega:focus-within, .ff-header__nav .is-style-ff-mega.is-menu-open")) {
				document.documentElement.classList.remove("is-ff-mega-open");
			}
			if (!isDesktop()) {
				clearPanel(item);
			}
		}

		item.addEventListener("mouseenter", open);
		item.addEventListener("focusin", open);
		item.addEventListener("mouseleave", close);
		item.addEventListener("focusout", function () {
			window.setTimeout(close, 0);
		});

		var toggle = item.querySelector(":scope > .wp-block-navigation-submenu__toggle, :scope > button");
		if (toggle) {
			toggle.addEventListener("click", function () {
				window.setTimeout(function () {
					if (item.classList.contains("is-menu-open") || item.matches(":focus-within")) {
						open();
					} else {
						close();
					}
				}, 0);
			});
		}
	}

	function boot() {
		var items = megaItems();
		if (!items.length) {
			return;
		}
		items.forEach(bindItem);

		window.addEventListener(
			"resize",
			function () {
				items.forEach(function (item) {
					if (item.matches(":hover, :focus-within") || item.classList.contains("is-menu-open")) {
						positionPanel(item);
					} else if (!isDesktop()) {
						clearPanel(item);
					}
				});
			},
			{ passive: true }
		);

		window.addEventListener(
			"scroll",
			function () {
				items.forEach(function (item) {
					if (item.matches(":hover, :focus-within") || item.classList.contains("is-menu-open")) {
						positionPanel(item);
					}
				});
			},
			{ passive: true }
		);
	}

	ready(boot);
})();
