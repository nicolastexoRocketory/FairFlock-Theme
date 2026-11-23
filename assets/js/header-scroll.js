/**
 * Live clearance under fixed header (no compact-on-scroll).
 *
 * Sets --ff-header-clearance / --ff-menu-top from the measured header so
 * page content and the mobile menu sit flush under the real nav bar.
 *
 * Uses offsetHeight + computed `top` (not getBoundingClientRect) so CSS
 * translate/opacity during search open/close cannot collapse clearance.
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
		var bar = header.querySelector(".ff-header__bar");
		var measureEl = bar || header;

		/* Layout size — ignores translate/filter on ancestors or self */
		var height = Math.max(0, measureEl.offsetHeight || 0);
		if (!height) {
			height = Math.max(0, header.offsetHeight || 0);
		}

		var topCss = window.getComputedStyle(header).top;
		var top = parseFloat(topCss);
		if (!isFinite(top) || top < 0) {
			top = 0;
		}

		/*
		 * Visual bottom for menu flush: prefer untransformed layout math.
		 * Fall back to rect only when header is not mid search-hide.
		 */
		var searching =
			document.body.classList.contains("is-ff-search-open") ||
			document.body.classList.contains("is-ff-search-closing");
		var bottom = Math.ceil(top + height);
		if (!searching) {
			var rect = (bar || header).getBoundingClientRect();
			if (rect && rect.height > 0) {
				bottom = Math.ceil(Math.max(bottom, rect.bottom));
				/* Keep CSS top in sync with real inset when not transforming */
				if (rect.top >= 0 && rect.top < 200) {
					top = Math.round(rect.top);
				}
				height = Math.max(height, Math.round(rect.height));
			}
		}

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
		window.addEventListener("resize", syncClearance, { passive: true });
		window.addEventListener("orientationchange", syncClearance);
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(syncClearance).catch(function () {});
		}
		if (typeof ResizeObserver !== "undefined") {
			new ResizeObserver(function () {
				/* Skip noisy observations while the header is translated away */
				if (
					document.body.classList.contains("is-ff-search-open") ||
					document.body.classList.contains("is-ff-search-closing")
				) {
					return;
				}
				syncClearance();
			}).observe(header);
			var bar = header.querySelector(".ff-header__bar");
			if (bar) {
				new ResizeObserver(function () {
					if (
						document.body.classList.contains("is-ff-search-open") ||
						document.body.classList.contains("is-ff-search-closing")
					) {
						return;
					}
					syncClearance();
				}).observe(bar);
			}
		}
		window.setTimeout(syncClearance, 100);
		window.setTimeout(syncClearance, 400);
	});
})();
