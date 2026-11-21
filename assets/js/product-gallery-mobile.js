/**
 * Mobile product-page gallery: 1.5-image peek slider, edge-to-edge, arrows only.
 */
(function () {
	"use strict";

	var MQ = "(max-width: 960px)";
	var chevronLeft =
		'<svg class="ff-chevron ff-chevron--left ff-gal-chevron" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	var chevronRight =
		'<svg class="ff-chevron ff-chevron--right ff-gal-chevron" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	/**
	 * One gallery host per product column (avoid double-init on nested
	 * .wp-block-woocommerce-product-image-gallery + .woocommerce-product-gallery).
	 */
	function findGalleries() {
		var out = [];
		var seen = [];

		function add(el) {
			if (!el || seen.indexOf(el) !== -1) return;
			seen.push(el);
			out.push(el);
		}

		document
			.querySelectorAll(
				".ff-product__gallery .woocommerce-product-gallery, .ff-product__hero > .ff-product__gallery .woocommerce-product-gallery, .ff-product__hero .woocommerce-product-gallery"
			)
			.forEach(add);

		document
			.querySelectorAll(
				".ff-product__gallery .wp-block-woocommerce-product-image-gallery, .ff-product__hero .wp-block-woocommerce-product-image-gallery"
			)
			.forEach(function (block) {
				if (block.querySelector(".woocommerce-product-gallery")) return;
				if (block.querySelector("[data-ff-peek-root]")) return;
				add(block);
			});

		return out;
	}

	function collectSources(gallery) {
		var out = [];
		var seen = {};
		gallery
			.querySelectorAll(
				".woocommerce-product-gallery__image img, .wp-block-woocommerce-product-image-gallery img, .wc-block-woocommerce-product-gallery__image img, figure img"
			)
			.forEach(function (img) {
				if (img.closest && img.closest("[data-ff-peek-root]")) return;
				var src =
					img.getAttribute("data-large_image") ||
					img.getAttribute("data-src") ||
					img.currentSrc ||
					img.getAttribute("src") ||
					"";
				if (!src || seen[src]) return;
				seen[src] = true;
				out.push({
					src: src,
					alt: img.getAttribute("alt") || "",
					ki:
						img.getAttribute("data-ff-ki-bild") === "1" ||
						(img.classList && img.classList.contains("ff-ki-bild-img"))
				});
			});
		return out;
	}

	function build(gallery) {
		if (gallery.dataset.ffPeekReady === "1") return;
		if (gallery.closest && gallery.closest("[data-ff-peek-root]")) return;

		var images = collectSources(gallery);
		if (!images.length) return;

		gallery.dataset.ffPeekReady = "1";
		gallery.classList.add("ff-product-peek");

		var index = 0;
		var peek = images.length > 1 ? 1.5 : 1;
		var root = document.createElement("div");
		root.className = "ff-product-peek__root";
		root.setAttribute("data-ff-peek-root", "");
		root.style.setProperty("--ff-gal-n", String(images.length));
		root.style.setProperty("--ff-gal-peek", String(peek));

		var track = document.createElement("div");
		track.className = "ff-product-peek__track";
		track.setAttribute("data-ff-peek-track", "");

		images.forEach(function (img, i) {
			var fig = document.createElement("figure");
			fig.className =
				"ff-product-peek__slide" +
				(i === 0 ? " is-active" : "") +
				(img.ki ? " ff-has-ki-bild" : "");
			fig.setAttribute("data-ff-peek-slide", String(i));
			if (img.ki) {
				if (typeof window.fairflockEnsureKiBildTag === "function") {
					window.fairflockEnsureKiBildTag(fig);
				} else {
					fig.insertAdjacentHTML(
						"afterbegin",
						'<span class="ff-ki-bild-tag">KI-Bild</span>'
					);
				}
			}
			var el = document.createElement("img");
			el.src = img.src;
			el.alt = img.alt;
			el.loading = i === 0 ? "eager" : "lazy";
			el.decoding = "async";
			if (img.ki) {
				el.setAttribute("data-ff-ki-bild", "1");
				el.className = "ff-ki-bild-img";
			}
			fig.appendChild(el);
			track.appendChild(fig);
		});

		root.appendChild(track);

		if (images.length > 1) {
			var nav = document.createElement("div");
			nav.className = "ff-product-peek__nav";
			nav.innerHTML =
				'<button type="button" class="ff-product-peek__btn is-prev" data-ff-peek="prev" aria-label="Vorheriges Bild">' +
				chevronLeft +
				"</button>" +
				'<button type="button" class="ff-product-peek__btn is-next" data-ff-peek="next" aria-label="Nächstes Bild">' +
				chevronRight +
				"</button>";
			root.appendChild(nav);
		}

		var zoomHintHtml =
			typeof window.fairflockZoomHintHtml === "function"
				? window.fairflockZoomHintHtml()
				: '<span class="ff-zoom-hint-tag" aria-hidden="true">Vergrößern<svg class="ff-zoom-hint-tag__icon" viewBox="0 0 24 24" width="11" height="11" aria-hidden="true" fill="none"><circle cx="10.5" cy="10.5" r="6" stroke="currentColor" stroke-width="1.75"/><path d="M15.2 15.2L20 20" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg></span>';
		root.insertAdjacentHTML("afterbegin", zoomHintHtml);

		gallery.insertBefore(root, gallery.firstChild);

		/**
		 * Einheitliche Slider-Höhe = max. Bildhöhe bei Slide-Breite.
		 * Slot-Breite aus Root (nicht Slide), damit Layout nicht von
		 * ungeladenen Bildern / height:auto abhängt.
		 */
		function syncHeight() {
			var rootW = root.clientWidth || root.offsetWidth || 0;
			if (!rootW) {
				return;
			}
			var slotW = rootW / peek;
			var maxH = 0;
			root.querySelectorAll(".ff-product-peek__slide img").forEach(function (img) {
				if (!img.naturalWidth || !img.naturalHeight) {
					return;
				}
				var h = (img.naturalHeight / img.naturalWidth) * slotW;
				if (h > maxH) {
					maxH = h;
				}
			});
			if (!maxH) {
				maxH = slotW;
			}
			maxH = Math.min(Math.round(maxH), Math.round(window.innerHeight * 0.7));
			root.style.setProperty("--ff-gal-h", maxH + "px");
		}

		/**
		 * Slide by index, but clamp so the last image’s right edge
		 * stays flush with the viewport (no empty peek after the end).
		 */
		function setIndex(next) {
			var total = images.length;
			if (!total) return;
			index = ((next % total) + total) % total;
			var step = 100 / total;
			var maxShift = Math.max(0, (1 - peek / total) * 100);
			var shift = Math.min(index * step, maxShift);
			track.style.transform = "translate3d(" + -shift + "%,0,0)";
			root.querySelectorAll(".ff-product-peek__slide").forEach(function (slide) {
				slide.classList.toggle(
					"is-active",
					Number(slide.getAttribute("data-ff-peek-slide")) === index
				);
				slide.classList.remove("is-ff-zooming");
				slide.style.removeProperty("--ff-zoom-x");
				slide.style.removeProperty("--ff-zoom-y");
			});
			root.classList.remove("is-ff-zoom-active");
		}

		function isZoomed() {
			return (
				root.classList.contains("is-ff-zoom-active") ||
				!!root.querySelector(".ff-product-peek__slide.is-ff-zooming")
			);
		}

		root.addEventListener("click", function (e) {
			var btn = e.target.closest && e.target.closest("[data-ff-peek]");
			if (!btn || !root.contains(btn)) return;
			e.preventDefault();
			if (isZoomed()) return;
			var dir = btn.getAttribute("data-ff-peek");
			setIndex(index + (dir === "next" ? 1 : -1));
		});

		var startX = 0;
		var deltaX = 0;
		var swipeArmed = false;
		track.addEventListener(
			"touchstart",
			function (e) {
				if (!e.touches || e.touches.length !== 1) return;
				if (isZoomed()) {
					swipeArmed = false;
					deltaX = 0;
					return;
				}
				swipeArmed = true;
				startX = e.touches[0].clientX;
				deltaX = 0;
			},
			{ passive: true }
		);
		track.addEventListener(
			"touchmove",
			function (e) {
				if (!swipeArmed || isZoomed() || !e.touches || e.touches.length !== 1) {
					deltaX = 0;
					return;
				}
				deltaX = e.touches[0].clientX - startX;
			},
			{ passive: true }
		);
		track.addEventListener("touchend", function () {
			if (!swipeArmed || isZoomed()) {
				swipeArmed = false;
				deltaX = 0;
				return;
			}
			swipeArmed = false;
			if (Math.abs(deltaX) < 40) return;
			setIndex(index + (deltaX < 0 ? 1 : -1));
			deltaX = 0;
		});

		track.querySelectorAll("img").forEach(function (img) {
			if (img.complete) {
				syncHeight();
			} else {
				img.addEventListener("load", syncHeight, { once: true });
			}
		});
		window.addEventListener("resize", syncHeight);
		window.setTimeout(syncHeight, 50);
		window.setTimeout(syncHeight, 300);

		setIndex(0);
	}

	function destroy(gallery) {
		var root = gallery.querySelector("[data-ff-peek-root]");
		if (root) root.remove();
		gallery.classList.remove("ff-product-peek");
		delete gallery.dataset.ffPeekReady;
	}

	function sync() {
		var galleries = findGalleries();
		var mobile = window.matchMedia(MQ).matches;
		galleries.forEach(function (gallery) {
			if (mobile) build(gallery);
			else destroy(gallery);
		});
	}

	ready(function () {
		sync();
		if (window.matchMedia) {
			var mql = window.matchMedia(MQ);
			if (mql.addEventListener) mql.addEventListener("change", sync);
			else if (mql.addListener) mql.addListener(sync);
		}
		window.setTimeout(sync, 400);
	});
})();
