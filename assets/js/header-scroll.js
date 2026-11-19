/**
 * Live clearance under fixed header (no compact-on-scroll).
 *
 * Sets --ff-header-clearance / --ff-menu-top from the measured header so
 * page content and the mobile menu sit flush under the real nav bar.
 */
(function () {
	var AIR = 32;

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function syncClearance() {
		var header = document.querySelector(".ff-header");
		if (!header) {
			return;
		}

		var root = document.documentElement;
		var rect = header.getBoundingClientRect();
		var top = Math.max(0, rect.top);
		var height = Math.max(0, rect.height);
		/* Prefer bar bottom when floating shell has extra chrome */
		var bar = header.querySelector(".ff-header__bar");
		var bottom = bar ? bar.getBoundingClientRect().bottom : rect.bottom;
		bottom = Math.ceil(Math.max(bottom, rect.bottom));

		var clearance = Math.max(80, Math.ceil(top + height + AIR));
		var stickyTop = Math.max(48, Math.ceil(bottom + 8));

		root.style.setProperty("--ff-header-top", Math.round(top) + "px");
		root.style.setProperty("--ff-header-height", Math.round(height) + "px");
		root.style.setProperty("--ff-header-clearance", clearance + "px");
		root.style.setProperty("--ff-header-sticky-top", stickyTop + "px");
		/* Mobile menu: exactly flush under the nav (no gap, no overlap) */
		root.style.setProperty("--ff-menu-top", bottom + "px");
	}

	window.fairflockSyncHeaderClearance = syncClearance;

	ready(function () {
		var header = document.querySelector(".ff-header");
		if (!header) {
			return;
		}
		syncClearance();
		window.addEventListener("resize", syncClearance);
		window.addEventListener("orientationchange", syncClearance);
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(syncClearance);
		}
		if (typeof ResizeObserver !== "undefined") {
			new ResizeObserver(syncClearance).observe(header);
			var bar = header.querySelector(".ff-header__bar");
			if (bar) {
				new ResizeObserver(syncClearance).observe(bar);
			}
		}
		window.setTimeout(syncClearance, 100);
		window.setTimeout(syncClearance, 400);
	});
})();
