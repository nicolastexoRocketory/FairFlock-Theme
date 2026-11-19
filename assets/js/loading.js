/**
 * Shared FairFlock loading spinner (overlay + inline).
 * window.fairflockLoading.show() / hide() / wrap(promise)
 */
(function () {
	var OVERLAY_ID = "ffLoadingOverlay";
	var depth = 0;
	var hideTimer = 0;
	var shopObserver = null;
	var shopHideTimer = 0;
	var shopWatchTimer = 0;
	var shopBusy = false;

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function spinnerHtml(size) {
		var s = size || 36;
		return (
			'<span class="ff-spinner" style="--ff-spinner-size:' +
			s +
			'px" role="presentation" aria-hidden="true"></span>'
		);
	}

	function ensureOverlay() {
		var el = document.getElementById(OVERLAY_ID);
		if (el) return el;
		el = document.createElement("div");
		el.id = OVERLAY_ID;
		el.className = "ff-loading-overlay";
		el.setAttribute("aria-hidden", "true");
		el.innerHTML =
			'<div class="ff-loading-overlay__panel" role="status" aria-live="polite" aria-busy="true">' +
			spinnerHtml(40) +
			'<span class="ff-loading-overlay__label">Wird geladen…</span>' +
			"</div>";
		document.body.appendChild(el);
		return el;
	}

	function showOverlay(label) {
		var el = ensureOverlay();
		var labelEl = el.querySelector(".ff-loading-overlay__label");
		if (labelEl) {
			labelEl.textContent = label || "Wird geladen…";
		}
		el.classList.add("is-visible");
		el.setAttribute("aria-hidden", "false");
		document.body.classList.add("is-ff-loading");
	}

	function hideOverlay() {
		var el = document.getElementById(OVERLAY_ID);
		if (!el) return;
		el.classList.remove("is-visible");
		el.setAttribute("aria-hidden", "true");
		document.body.classList.remove("is-ff-loading");
	}

	function show(opts) {
		opts = opts || {};
		if (hideTimer) {
			window.clearTimeout(hideTimer);
			hideTimer = 0;
		}
		depth += 1;
		showOverlay(opts.label);
		if (opts.maxMs && opts.maxMs > 0) {
			hideTimer = window.setTimeout(function () {
				depth = 0;
				shopBusy = false;
				hideOverlay();
			}, opts.maxMs);
		}
		return depth;
	}

	function hide() {
		depth = Math.max(0, depth - 1);
		if (depth === 0) {
			if (hideTimer) {
				window.clearTimeout(hideTimer);
				hideTimer = 0;
			}
			shopBusy = false;
			hideOverlay();
		}
		return depth;
	}

	function forceHide() {
		depth = 0;
		shopBusy = false;
		if (hideTimer) {
			window.clearTimeout(hideTimer);
			hideTimer = 0;
		}
		if (shopHideTimer) {
			window.clearTimeout(shopHideTimer);
			shopHideTimer = 0;
		}
		if (shopWatchTimer) {
			window.clearTimeout(shopWatchTimer);
			shopWatchTimer = 0;
		}
		if (shopObserver) {
			shopObserver.disconnect();
			shopObserver = null;
		}
		hideOverlay();
	}

	function wrap(promise, opts) {
		show(opts);
		return Promise.resolve(promise).then(
			function (value) {
				hide();
				return value;
			},
			function (err) {
				hide();
				throw err;
			}
		);
	}

	function markup(size) {
		return (
			'<div class="ff-loading-inline" role="status" aria-live="polite" aria-busy="true">' +
			spinnerHtml(size || 36) +
			'<span class="ff-visually-hidden">Wird geladen…</span>' +
			"</div>"
		);
	}

	window.fairflockLoading = {
		show: show,
		hide: hide,
		forceHide: forceHide,
		wrap: wrap,
		markup: markup,
		spinnerHtml: spinnerHtml
	};

	function scheduleShopHide(delay) {
		if (shopHideTimer) window.clearTimeout(shopHideTimer);
		shopHideTimer = window.setTimeout(function () {
			shopHideTimer = 0;
			forceHide();
		}, typeof delay === "number" ? delay : 320);
	}

	function watchShopCollection() {
		var collection =
			document.querySelector(".ff-shop__collection .wc-block-product-template") ||
			document.querySelector(".ff-shop__collection");
		if (!collection || typeof MutationObserver === "undefined") {
			scheduleShopHide(500);
			return;
		}
		if (shopObserver) {
			shopObserver.disconnect();
			shopObserver = null;
		}
		if (shopWatchTimer) {
			window.clearTimeout(shopWatchTimer);
			shopWatchTimer = 0;
		}

		var settled = false;
		function settle() {
			if (settled) return;
			settled = true;
			if (shopObserver) {
				shopObserver.disconnect();
				shopObserver = null;
			}
			if (shopWatchTimer) {
				window.clearTimeout(shopWatchTimer);
				shopWatchTimer = 0;
			}
			scheduleShopHide(200);
		}

		shopObserver = new MutationObserver(function () {
			settle();
		});
		shopObserver.observe(collection, {
			childList: true,
			subtree: false
		});

		/* Always end — CSS-only “Ansicht” / no DOM swap must not leave the overlay up. */
		shopWatchTimer = window.setTimeout(settle, 700);
	}

	function beginShopLoad() {
		if (shopBusy) {
			/* Already visible — just refresh the settle timer, do not stack overlays. */
			watchShopCollection();
			return;
		}
		shopBusy = true;
		depth = 0;
		show({ label: "Produkte werden geladen…", maxMs: 6000 });
		watchShopCollection();
	}

	function isLocalViewControl(el) {
		if (!el || !el.closest) return false;
		/* Spalten-„Ansicht“ is CSS-only — never show the global loader. */
		if (el.closest(".ff-shop-cols")) return true;
		if (el.closest(".ff-shop__view")) return true;
		return false;
	}

	function isShopNavTrigger(el) {
		if (!el || !el.closest) return false;
		if (!el.closest(".ff-shop")) return false;
		if (isLocalViewControl(el)) return false;
		if (el.closest(".ff-shop__pagination")) return true;
		if (el.closest(".wp-block-query-pagination")) return true;
		if (el.closest(".wc-block-product-filter-chips")) return true;
		if (el.closest(".wc-block-product-filter-checkbox-list")) return true;
		if (el.closest(".wp-block-woocommerce-product-filter-price")) return true;
		if (el.closest(".wp-block-woocommerce-product-filter-attribute")) return true;
		if (el.closest(".wp-block-woocommerce-product-filter-status")) return true;
		if (el.closest(".wp-block-woocommerce-product-filter-taxonomy")) return true;
		if (el.closest(".wp-block-woocommerce-product-collection-active-filters")) return true;
		if (el.closest(".woocommerce-ordering")) return true;
		return false;
	}

	ready(function () {
		document.addEventListener(
			"click",
			function (e) {
				var t = e.target;
				if (isLocalViewControl(t)) return;
				if (!isShopNavTrigger(t)) return;
				var interactive =
					t.closest("a, button, label, input, select, [role='button'], [role='checkbox'], [role='option']") ||
					t;
				if (interactive.disabled || interactive.getAttribute("aria-disabled") === "true") return;
				if (interactive.tagName === "A") {
					var href = interactive.getAttribute("href") || "";
					if (href.charAt(0) === "#" && href.length < 2) return;
				}
				beginShopLoad();
			},
			true
		);

		document.addEventListener(
			"change",
			function (e) {
				var t = e.target;
				if (!t || !t.closest) return;
				if (isLocalViewControl(t)) return;
				if (!t.closest(".ff-shop__filters") && !t.closest(".woocommerce-ordering")) {
					return;
				}
				beginShopLoad();
			},
			true
		);

		window.addEventListener("pageshow", function () {
			forceHide();
		});
	});
})();
