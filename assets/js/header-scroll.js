/**
 * Subtle header compact state on scroll + live clearance under fixed header.
 *
 * Toggles html.is-ff-header-compact when scrolled past a small threshold.
 * Sets --ff-header-clearance from the measured (expanded) header so page
 * titles clear logos of varying height. Clearance stays stable on compact.
 */
(function () {
	var THRESHOLD = 24;
	var AIR = 32;
	var ticking = false;

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

		/* Measure expanded bar so padding does not jump when compacting. */
		var root = document.documentElement;
		var wasCompact = root.classList.contains("is-ff-header-compact");
		if (wasCompact) {
			root.classList.remove("is-ff-header-compact");
		}

		var top = parseFloat(window.getComputedStyle(header).top) || 0;
		var height = header.offsetHeight;
		var clearance = Math.max(96, Math.ceil(top + height + AIR));
		root.style.setProperty("--ff-header-clearance", clearance + "px");

		if (wasCompact) {
			root.classList.add("is-ff-header-compact");
		}
	}

	function updateCompact() {
		ticking = false;
		document.documentElement.classList.toggle(
			"is-ff-header-compact",
			window.scrollY > THRESHOLD
		);
	}

	function onScroll() {
		if (ticking) {
			return;
		}
		ticking = true;
		window.requestAnimationFrame(updateCompact);
	}

	ready(function () {
		if (!document.querySelector(".ff-header")) {
			return;
		}
		syncClearance();
		updateCompact();
		window.addEventListener("scroll", onScroll, { passive: true });
		window.addEventListener("resize", syncClearance);
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(syncClearance);
		}
		window.setTimeout(syncClearance, 100);
		window.setTimeout(syncClearance, 400);
	});
})();
