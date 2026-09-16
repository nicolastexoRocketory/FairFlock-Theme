/**
 * Subtle header compact state on scroll.
 *
 * Toggles html.is-ff-header-compact when scrolled past a small threshold.
 */
(function () {
	var THRESHOLD = 24;
	var ticking = false;

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function update() {
		ticking = false;
		var compact = window.scrollY > THRESHOLD;
		document.documentElement.classList.toggle("is-ff-header-compact", compact);
	}

	function onScroll() {
		if (ticking) {
			return;
		}
		ticking = true;
		window.requestAnimationFrame(update);
	}

	ready(function () {
		if (!document.querySelector(".ff-header")) {
			return;
		}
		update();
		window.addEventListener("scroll", onScroll, { passive: true });
	});
})();
