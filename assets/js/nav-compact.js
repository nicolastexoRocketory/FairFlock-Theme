/**
 * Collapse desktop text-nav to the burger as soon as items would wrap,
 * overflow the nav slot, or collide with the (often oversized) center logo.
 *
 * Body class: ff-nav-compact — CSS mirrors the ≤960px burger/nav rules.
 */
(function () {
	var MQ = "(min-width: 961px)";
	var HYSTERESIS = 36;
	var LOGO_GAP = 16;
	var cachedNeed = 0;
	var raf = 0;
	var ro = null;

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

	function isLogoCenter() {
		return (
			document.body.classList.contains("ff-nav-layout-logo-center") ||
			!!document.querySelector(".ff-header.ff-header--nav-logo-center")
		);
	}

	function navEl() {
		return document.querySelector(".ff-header__nav");
	}

	function brandEl() {
		return document.querySelector(".ff-header__brand");
	}

	function logoEl() {
		var brand = brandEl();
		if (!brand) return null;
		return (
			brand.querySelector(".wp-block-site-logo") ||
			brand.querySelector("img") ||
			brand
		);
	}

	function navContainer(nav) {
		if (!nav) return null;
		return (
			nav.querySelector(".wp-block-navigation__container") ||
			nav.querySelector(
				".wp-block-navigation__responsive-container-content > ul"
			) ||
			nav.querySelector(".wp-block-navigation__responsive-container-content")
		);
	}

	function topLevelItems(container) {
		if (!container) return [];
		return Array.prototype.slice.call(
			container.querySelectorAll(
				":scope > .wp-block-navigation-item, :scope > .wp-block-navigation-submenu, :scope > li"
			)
		);
	}

	/** Full inline span of top-level items (includes gaps). */
	function measureNeededWidth(container) {
		var items = topLevelItems(container);
		if (!items.length) return 0;
		var first = items[0].getBoundingClientRect();
		var last = items[items.length - 1].getBoundingClientRect();
		return Math.ceil(Math.max(0, last.right - first.left));
	}

	function itemsWrap(container) {
		var items = topLevelItems(container);
		if (items.length < 2) return false;
		var top0 = items[0].getBoundingClientRect().top;
		for (var i = 1; i < items.length; i++) {
			if (Math.abs(items[i].getBoundingClientRect().top - top0) > 6) {
				return true;
			}
		}
		return container.scrollWidth > container.clientWidth + 1;
	}

	/**
	 * True when any top-level item overlaps the logo/brand box
	 * (logo-center: oversized mark often covers the last menu labels).
	 */
	function overlapsLogo(container) {
		var logo = logoEl();
		if (!logo) return false;
		var logoRect = logo.getBoundingClientRect();
		if (!logoRect.width || !logoRect.height) return false;
		var items = topLevelItems(container);
		var i;
		for (i = 0; i < items.length; i++) {
			var r = items[i].getBoundingClientRect();
			if (!r.width) continue;
			if (
				r.right > logoRect.left - LOGO_GAP &&
				r.left < logoRect.right + LOGO_GAP
			) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Free width the text-nav may use. Logo-center: left edge → logo left
	 * (not bar − brand − icons — the large logo protrudes into the nav column).
	 */
	function availableNavWidth() {
		var bar = document.querySelector(".ff-header__bar");
		var brand = brandEl();
		var logo = logoEl();
		var icons = document.querySelector(
			".ff-header__icons, .ff-header__actions"
		);
		var search = document.querySelector(".ff-header__search");
		if (!bar) return 0;

		if (isLogoCenter() && logo) {
			var barRect = bar.getBoundingClientRect();
			var logoLeft = logo.getBoundingClientRect().left;
			var pad = 16;
			var style = window.getComputedStyle(bar);
			var pl = parseFloat(style.paddingLeft) || 0;
			return Math.max(0, Math.floor(logoLeft - barRect.left - pl - LOGO_GAP - pad));
		}

		var w = bar.getBoundingClientRect().width;
		if (brand) w -= brand.getBoundingClientRect().width;
		if (icons) w -= icons.getBoundingClientRect().width;
		if (search && getComputedStyle(search).display !== "none") {
			w -= search.getBoundingClientRect().width;
		}
		return Math.max(0, w - 48);
	}

	function updateCompact() {
		raf = 0;
		if (!isDesktop()) {
			document.body.classList.remove("ff-nav-compact");
			return;
		}

		var nav = navEl();
		var container = navContainer(nav);
		if (!nav || !container) {
			document.body.classList.remove("ff-nav-compact");
			return;
		}

		var compact = document.body.classList.contains("ff-nav-compact");

		if (compact) {
			/* Nav is hidden — compare cached need vs free space (+ hysteresis). */
			var avail = availableNavWidth();
			if (cachedNeed > 0 && avail >= cachedNeed + HYSTERESIS) {
				document.body.classList.remove("ff-nav-compact");
				/* Re-check next frame with nav visible again. */
				schedule();
			}
			return;
		}

		cachedNeed = measureNeededWidth(container) || cachedNeed;
		var shouldCompact =
			itemsWrap(container) ||
			overlapsLogo(container) ||
			(cachedNeed > 0 && cachedNeed > availableNavWidth());

		if (shouldCompact) {
			document.body.classList.add("ff-nav-compact");
		}
	}

	function schedule() {
		if (raf) return;
		raf = window.requestAnimationFrame(updateCompact);
	}

	ready(function () {
		updateCompact();
		window.addEventListener("resize", schedule, { passive: true });
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(schedule).catch(function () {});
		}
		var bar = document.querySelector(".ff-header__bar");
		var nav = navEl();
		var brand = brandEl();
		var logo = logoEl();
		if (typeof ResizeObserver === "function") {
			ro = new ResizeObserver(schedule);
			if (bar) ro.observe(bar);
			if (nav) ro.observe(nav);
			if (brand) ro.observe(brand);
			if (logo && logo !== brand) ro.observe(logo);
		}
	});
})();
