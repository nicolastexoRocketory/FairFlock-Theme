/**
 * Product image zoom — cursor-follow on desktop; tap-to-zoom on mobile galleries.
 * Mobile: Pop-up + Produktseiten-Slideshow; „Vergrößern“-Hinweis wie KI-Tag.
 */
(function () {
	"use strict";

	var FINE_MQ = "(hover: hover) and (pointer: fine)";
	var CARD_SEL =
		".ff-prod-card__media, .wc-block-components-product-image, .ff-shop__collection .wc-block-components-product-image";
	var GALLERY_SEL =
		".ff-product__hero .woocommerce-product-gallery__image, .ff-product__hero .wc-block-woocommerce-product-gallery__image, .ff-product-modal__slide, .ff-product-peek__slide";
	/* Touch/Tap-Zoom: Modal, Peek und Fallback Woo-Galerie auf der PDP */
	var TOUCH_ZOOM_SEL =
		".ff-product-modal__slide, .ff-product-peek__slide, .ff-product__hero .woocommerce-product-gallery__image, .ff-product__hero .wc-block-woocommerce-product-gallery__image";
	var HINT_ROOT_SEL =
		".ff-product-modal__stage, .ff-product-peek__root, [data-ff-peek-root]";
	var HOST_SEL = CARD_SEL + ", " + GALLERY_SEL;
	var SKIP_SEL =
		".ff-product__hero .flex-control-thumbs, .ff-product-modal__thumbs, .ff-product-modal__thumb";
	/* Nur echte Controls — keine Galerie-<a> um das Bild (sonst kein Tap-Zoom). */
	var CTRL_SEL =
		"button, .ff-product-modal__gallery-btn, .ff-product-peek__btn, .ff-product-modal__close, .ff-zoom-hint-tag";

	var LOUPE_SVG =
		'<svg class="ff-zoom-hint-tag__icon" viewBox="0 0 24 24" width="11" height="11" aria-hidden="true" fill="none">' +
		'<circle cx="10.5" cy="10.5" r="6" stroke="currentColor" stroke-width="1.75"/>' +
		'<path d="M15.2 15.2L20 20" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>' +
		"</svg>";

	var HINT_HTML =
		'<span class="ff-zoom-hint-tag" aria-hidden="true">Vergrößern' + LOUPE_SVG + "</span>";

	window.fairflockZoomHintHtml = function () {
		return HINT_HTML;
	};

	var touchState = null;
	var suppressClickUntil = 0;

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function isFinePointer() {
		return window.matchMedia && window.matchMedia(FINE_MQ).matches;
	}

	function isHost(el) {
		if (!el || !el.closest) {
			return null;
		}
		if (el.closest(SKIP_SEL)) {
			return null;
		}
		return el.closest(HOST_SEL);
	}

	function isTouchZoomHost(el) {
		if (!el || !el.closest) {
			return null;
		}
		if (el.closest(CTRL_SEL)) {
			return null;
		}
		var host = el.closest(TOUCH_ZOOM_SEL);
		if (!host) {
			return null;
		}
		/* Woo wrappt Galeriebilder oft in <a> — Zoom erlauben, Navigation nicht. */
		return host;
	}

	function clampPct(n) {
		return Math.max(0, Math.min(100, n));
	}

	function setOrigin(host, clientX, clientY) {
		var rect = host.getBoundingClientRect();
		if (!rect.width || !rect.height) {
			return;
		}
		var x = clampPct(((clientX - rect.left) / rect.width) * 100);
		var y = clampPct(((clientY - rect.top) / rect.height) * 100);
		host.style.setProperty("--ff-zoom-x", x.toFixed(2) + "%");
		host.style.setProperty("--ff-zoom-y", y.toFixed(2) + "%");
	}

	function readOriginPct(host) {
		var x = parseFloat(host.style.getPropertyValue("--ff-zoom-x"));
		var y = parseFloat(host.style.getPropertyValue("--ff-zoom-y"));
		return {
			x: Number.isFinite(x) ? x : 50,
			y: Number.isFinite(y) ? y : 50
		};
	}

	/**
	 * Touch-Pan: Fokus wandert umgekehrt zur Fingerbewegung
	 * (Finger nach rechts → Bildinhalt nach rechts / Zoom-Ursprung nach links).
	 */
	function panOriginInverted(host, clientX, clientY, lastX, lastY) {
		var rect = host.getBoundingClientRect();
		if (!rect.width || !rect.height) {
			return;
		}
		var origin = readOriginPct(host);
		var x = clampPct(origin.x - ((clientX - lastX) / rect.width) * 100);
		var y = clampPct(origin.y - ((clientY - lastY) / rect.height) * 100);
		host.style.setProperty("--ff-zoom-x", x.toFixed(2) + "%");
		host.style.setProperty("--ff-zoom-y", y.toFixed(2) + "%");
	}

	function clearOrigin(host) {
		if (!host) {
			return;
		}
		host.classList.remove("is-ff-zooming");
		host.style.removeProperty("--ff-zoom-x");
		host.style.removeProperty("--ff-zoom-y");
		var root = host.closest(
			".ff-product-modal__stage, .ff-product-peek__root, [data-ff-peek-root], .ff-product-modal__media, .ff-product__hero .woocommerce-product-gallery, .ff-product__gallery"
		);
		if (root) {
			root.classList.remove("is-ff-zoom-active");
		}
	}

	function activateZoom(host, clientX, clientY) {
		document.querySelectorAll(TOUCH_ZOOM_SEL + ".is-ff-zooming").forEach(function (el) {
			if (el !== host) {
				clearOrigin(el);
			}
		});
		setOrigin(host, clientX, clientY);
		host.classList.add("is-ff-zooming");
		var root = host.closest(
			".ff-product-modal__stage, .ff-product-peek__root, [data-ff-peek-root], .ff-product-modal__media, .ff-product__hero .woocommerce-product-gallery, .ff-product__gallery"
		);
		if (root) {
			root.classList.add("is-ff-zoom-active");
		}
	}

	function toggleZoom(host, clientX, clientY) {
		if (!host) {
			return;
		}
		if (host.classList.contains("is-ff-zooming")) {
			clearOrigin(host);
		} else {
			activateZoom(host, clientX, clientY);
		}
	}

	function ensureZoomHints() {
		document.querySelectorAll(HINT_ROOT_SEL).forEach(function (root) {
			if (root.querySelector(".ff-zoom-hint-tag")) {
				return;
			}
			root.insertAdjacentHTML("afterbegin", HINT_HTML);
		});
	}

	function onMove(event) {
		if (!isFinePointer()) {
			return;
		}
		/* Echte Touch-Pointer nicht als Hover-Zoom behandeln */
		if (event.pointerType === "touch") {
			return;
		}
		var host = isHost(event.target);
		if (!host) {
			return;
		}
		host.classList.add("is-ff-zooming");
		setOrigin(host, event.clientX, event.clientY);
	}

	function onLeave(event) {
		if (!isFinePointer()) {
			return;
		}
		if (event.pointerType === "touch") {
			return;
		}
		var host = isHost(event.target);
		if (!host) {
			return;
		}
		var related = event.relatedTarget;
		if (related && host.contains(related)) {
			return;
		}
		clearOrigin(host);
	}

	function onTouchStart(event) {
		/* Kein isFinePointer-Gate: Touch-Events = Touch-Gerät (auch Hybrid/DevTools). */
		if (!event.touches || event.touches.length !== 1) {
			touchState = null;
			return;
		}
		var host = isTouchZoomHost(event.target);
		if (!host) {
			touchState = null;
			return;
		}
		var t = event.touches[0];
		touchState = {
			host: host,
			startX: t.clientX,
			startY: t.clientY,
			lastX: t.clientX,
			lastY: t.clientY,
			moved: false,
			zooming: host.classList.contains("is-ff-zooming")
		};
	}

	function onTouchMove(event) {
		/* Pinch (2+ Finger) nie blockieren — Browser-/OS-Zoom erlauben. */
		if (!event.touches || event.touches.length !== 1) {
			touchState = null;
			return;
		}
		if (!touchState) {
			return;
		}
		var t = event.touches[0];
		var dx = t.clientX - touchState.startX;
		var dy = t.clientY - touchState.startY;
		if (Math.abs(dx) > 10 || Math.abs(dy) > 10) {
			touchState.moved = true;
		}
		if (touchState.zooming && touchState.host) {
			panOriginInverted(
				touchState.host,
				t.clientX,
				t.clientY,
				touchState.lastX,
				touchState.lastY
			);
			touchState.lastX = t.clientX;
			touchState.lastY = t.clientY;
			if (event.cancelable) {
				event.preventDefault();
			}
			/* Kein Gallery-Swipe, solange gezoomt wird. */
			event.stopPropagation();
		}
	}

	function onTouchEnd(event) {
		if (!touchState) {
			return;
		}
		var state = touchState;
		touchState = null;

		if (state.zooming) {
			/* Pan/Zoom beendet — Slide-Wisch nicht auslösen. */
			event.stopPropagation();
			if (state.moved) {
				suppressClickUntil = Date.now() + 400;
				return;
			}
		}
		if (state.moved) {
			return;
		}
		var touch = event.changedTouches && event.changedTouches[0];
		if (!touch || !state.host) {
			return;
		}
		toggleZoom(state.host, touch.clientX, touch.clientY);
		suppressClickUntil = Date.now() + 400;
		event.stopPropagation();
	}

	/**
	 * Klick-Fallback (manche WebViews liefern kein zuverlässiges touchend-Toggle).
	 */
	function onClickCapture(event) {
		if (Date.now() < suppressClickUntil) {
			var hostBusy = isTouchZoomHost(event.target);
			if (hostBusy) {
				event.preventDefault();
				event.stopPropagation();
			}
			return;
		}
		/* Desktop-Hover-Geräte: Klick nicht für Zoom missbrauchen. */
		if (isFinePointer() && !(event.sourceCapabilities && event.sourceCapabilities.firesTouchEvents)) {
			return;
		}
		var host = isTouchZoomHost(event.target);
		if (!host) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		toggleZoom(host, event.clientX, event.clientY);
	}

	ready(function () {
		ensureZoomHints();

		document.addEventListener("pointermove", onMove, { passive: true });
		document.addEventListener("pointerleave", onLeave, true);
		document.addEventListener(
			"pointerout",
			function (event) {
				if (!isFinePointer() || event.pointerType === "touch") {
					return;
				}
				var host = isHost(event.target);
				if (!host) {
					return;
				}
				var related = event.relatedTarget;
				if (related && host.contains(related)) {
					return;
				}
				clearOrigin(host);
			},
			true
		);

		document.addEventListener("touchstart", onTouchStart, { passive: true, capture: true });
		document.addEventListener("touchmove", onTouchMove, { passive: false, capture: true });
		document.addEventListener("touchend", onTouchEnd, { passive: true, capture: true });
		document.addEventListener(
			"touchcancel",
			function () {
				touchState = null;
			},
			{ passive: true, capture: true }
		);
		document.addEventListener("click", onClickCapture, true);

		var obs =
			typeof MutationObserver !== "undefined"
				? new MutationObserver(function () {
						ensureZoomHints();
				  })
				: null;
		if (obs) {
			obs.observe(document.body, { childList: true, subtree: true });
		}

		window.setTimeout(ensureZoomHints, 400);
		window.setTimeout(ensureZoomHints, 1200);
	});
})();
