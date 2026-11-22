/**
 * Collapse desktop text-nav to the burger as soon as items would wrap
 * onto a second line (or overflow the nav slot).
 *
 * Body class: ff-nav-compact — CSS mirrors the ≤960px burger/nav rules.
 */
(function () {
	var MQ = "(min-width: 961px)";
	var HYSTERESIS = 28;
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

	function navEl() {
		return document.querySelector(".ff-header__nav");
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

	function measureNeededWidth(container) {
		var items = topLevelItems(container);
		if (!items.length) return 0;
		var sum = 0;
		items.forEach(function (el) {
			sum += el.getBoundingClientRect().width;
		});
		return Math.ceil(sum);
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

	function availableNavWidth() {
		var bar = document.querySelector(".ff-header__bar");
		var brand = document.querySelector(".ff-header__brand");
		var icons = document.querySelector(
			".ff-header__icons, .ff-header__actions"
		);
		var search = document.querySelector(".ff-header__search");
		if (!bar) return 0;
		var w = bar.getBoundingClientRect().width;
		if (brand) w -= brand.getBoundingClientRect().width;
		if (icons) w -= icons.getBoundingClientRect().width;
		if (search && getComputedStyle(search).display !== "none") {
			w -= search.getBoundingClientRect().width;
		}
		/* gaps / padding slack */
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
		var shouldCompact = itemsWrap(container);
		if (!shouldCompact && cachedNeed > 0) {
			shouldCompact = cachedNeed > availableNavWidth();
		}

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
		if (typeof ResizeObserver === "function" && (bar || nav)) {
			ro = new ResizeObserver(schedule);
			if (bar) ro.observe(bar);
			if (nav) ro.observe(nav);
		}
	});
})();
