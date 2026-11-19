/**
 * Live clearance under fixed header (no compact-on-scroll).
 *
 * Sets --ff-header-clearance from the measured header so page
 * content clears logos of varying height.
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
		var top = parseFloat(window.getComputedStyle(header).top) || 0;
		var height = header.offsetHeight;
		var clearance = Math.max(80, Math.ceil(top + height + AIR));
		var stickyTop = Math.max(48, Math.ceil(top + height + 8));
		root.style.setProperty("--ff-header-clearance", clearance + "px");
		root.style.setProperty("--ff-header-sticky-top", stickyTop + "px");
	}

	ready(function () {
		if (!document.querySelector(".ff-header")) {
			return;
		}
		syncClearance();
		window.addEventListener("resize", syncClearance);
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(syncClearance);
		}
		window.setTimeout(syncClearance, 100);
		window.setTimeout(syncClearance, 400);
	});
})();
