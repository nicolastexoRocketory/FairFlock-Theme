/**
 * Fullscreen product table: slideshow, centered grid, scroll arrows.
 *
 * @package FairFlock
 * @since 0.9.289
 */
(function () {
	"use strict";

	var openDialog = null;
	var lastTrigger = null;
	var homeParent = null;
	var homeNext = null;

	function escapeHtml(value) {
		return String(value || "")
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;");
	}

	function uniqueImages(nodes) {
		var images = [];
		var seen = {};
		Array.prototype.forEach.call(nodes, function (img) {
			var src = img.currentSrc || img.getAttribute("src") || "";
			if (!src || seen[src]) {
				return;
			}
			seen[src] = true;
			images.push({
				src: src,
				alt: img.getAttribute("alt") || ""
			});
		});
		return images;
	}

	function collectImages(button) {
		var modal = button.closest(".ff-product-modal");
		if (modal) {
			return uniqueImages(modal.querySelectorAll(".ff-product-modal__slide img"));
		}
		var page = document.querySelector(".ff-product") || document;
		return uniqueImages(
			page.querySelectorAll(
				".wp-block-woocommerce-product-gallery img, .woocommerce-product-gallery__image img, .wc-block-product-gallery-large-image__container img"
			)
		);
	}

	function renderGallery(dialog, images) {
		var host = dialog.querySelector("[data-ff-ptable-gallery]");
		if (!host) {
			return;
		}
		if (!images.length) {
			host.hidden = true;
			host.innerHTML = "";
			return;
		}
		var slides = images
			.map(function (img, index) {
				return (
					'<figure class="ff-product-table__slide' +
					(index === 0 ? " is-active" : "") +
					'" data-ff-ptable-slide="' +
					index +
					'"><img src="' +
					escapeHtml(img.src) +
					'" alt="' +
					escapeHtml(img.alt) +
					'" /></figure>'
				);
			})
			.join("");
		var nav =
			images.length > 1
				? '<button type="button" class="ff-product-table__gbtn is-prev" data-ff-ptable-gallery-nav="-1" aria-label="Vorheriges Bild"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M14.5 6 8.5 12l6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' +
				  '<button type="button" class="ff-product-table__gbtn is-next" data-ff-ptable-gallery-nav="1" aria-label="Nächstes Bild"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M9.5 6 15.5 12l-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>'
				: "";
		var thumbs =
			images.length > 1
				? '<div class="ff-product-table__thumbs">' +
				  images
						.map(function (img, index) {
							return (
								'<button type="button" class="ff-product-table__thumb' +
								(index === 0 ? " is-active" : "") +
								'" data-ff-ptable-thumb="' +
								index +
								'" aria-label="Bild ' +
								(index + 1) +
								'"><img src="' +
								escapeHtml(img.src) +
								'" alt="" /></button>'
							);
						})
						.join("") +
				  "</div>"
				: "";
		host.innerHTML =
			'<div class="ff-product-table__stage"><div class="ff-product-table__slides">' +
			slides +
			"</div>" +
			nav +
			"</div>" +
			thumbs;
		host.hidden = false;
		host.setAttribute("data-ff-ptable-index", "0");
	}

	function setGalleryIndex(dialog, next) {
		var host = dialog.querySelector("[data-ff-ptable-gallery]");
		if (!host) {
			return;
		}
		var slides = host.querySelectorAll("[data-ff-ptable-slide]");
		if (!slides.length) {
			return;
		}
		var total = slides.length;
		var index = ((next % total) + total) % total;
		host.setAttribute("data-ff-ptable-index", String(index));
		Array.prototype.forEach.call(slides, function (slide) {
			slide.classList.toggle("is-active", Number(slide.getAttribute("data-ff-ptable-slide")) === index);
		});
		host.querySelectorAll("[data-ff-ptable-thumb]").forEach(function (thumb) {
			thumb.classList.toggle("is-active", Number(thumb.getAttribute("data-ff-ptable-thumb")) === index);
		});
	}

	function syncArrows(dialog) {
		var wrap = dialog.querySelector(".ff-product-table__wrap");
		if (!wrap) {
			return;
		}
		var max = wrap.scrollWidth - wrap.clientWidth;
		var prev = dialog.querySelector('[data-ff-ptable-scroll="-1"]');
		var next = dialog.querySelector('[data-ff-ptable-scroll="1"]');
		var can = max > 8;
		if (prev) {
			prev.hidden = !can || wrap.scrollLeft <= 4;
		}
		if (next) {
			next.hidden = !can || wrap.scrollLeft >= max - 4;
		}
	}

	function closeDialog() {
		if (!openDialog) {
			return;
		}
		openDialog.hidden = true;
		document.body.classList.remove("is-ff-ptable-open");
		if (homeParent) {
			homeParent.insertBefore(openDialog, homeNext);
		}
		if (lastTrigger && typeof lastTrigger.focus === "function") {
			lastTrigger.focus();
		}
		openDialog = null;
		lastTrigger = null;
		homeParent = null;
		homeNext = null;
	}

	function openFrom(button) {
		var section = button.closest(".ff-product-table");
		var dialog = section && section.querySelector(".ff-product-table__dialog");
		if (!dialog) {
			return;
		}
		if (openDialog && openDialog !== dialog) {
			closeDialog();
		}
		lastTrigger = button;
		homeParent = dialog.parentNode;
		homeNext = dialog.nextSibling;
		renderGallery(dialog, collectImages(button));
		document.body.appendChild(dialog);
		dialog.hidden = false;
		openDialog = dialog;
		document.body.classList.add("is-ff-ptable-open");
		window.requestAnimationFrame(function () {
			syncArrows(dialog);
		});
		var closeBtn = dialog.querySelector("[data-ff-ptable-close]");
		if (closeBtn) {
			closeBtn.focus();
		}
	}

	document.addEventListener("click", function (event) {
		var openBtn = event.target.closest && event.target.closest("[data-ff-ptable-open]");
		if (openBtn) {
			event.preventDefault();
			openFrom(openBtn);
			return;
		}
		if (!openDialog || !openDialog.contains(event.target)) {
			return;
		}
		var galleryBtn = event.target.closest("[data-ff-ptable-gallery-nav]");
		if (galleryBtn) {
			event.preventDefault();
			var host = openDialog.querySelector("[data-ff-ptable-gallery]");
			var current = host ? Number(host.getAttribute("data-ff-ptable-index")) || 0 : 0;
			setGalleryIndex(openDialog, current + Number(galleryBtn.getAttribute("data-ff-ptable-gallery-nav")));
			return;
		}
		var thumb = event.target.closest("[data-ff-ptable-thumb]");
		if (thumb) {
			event.preventDefault();
			setGalleryIndex(openDialog, Number(thumb.getAttribute("data-ff-ptable-thumb")));
			return;
		}
		var scrollBtn = event.target.closest("[data-ff-ptable-scroll]");
		if (scrollBtn) {
			event.preventDefault();
			var wrap = openDialog.querySelector(".ff-product-table__wrap");
			if (wrap) {
				wrap.scrollBy({
					left: Number(scrollBtn.getAttribute("data-ff-ptable-scroll")) * Math.max(160, wrap.clientWidth * 0.7),
					behavior: "smooth"
				});
			}
			return;
		}
		if (event.target === openDialog || event.target.closest("[data-ff-ptable-close]")) {
			event.preventDefault();
			closeDialog();
		}
	});

	document.addEventListener(
		"scroll",
		function (event) {
			if (!openDialog) {
				return;
			}
			var wrap = openDialog.querySelector(".ff-product-table__wrap");
			if (event.target === wrap) {
				syncArrows(openDialog);
			}
		},
		true
	);

	window.addEventListener("resize", function () {
		if (openDialog) {
			syncArrows(openDialog);
		}
	});

	document.addEventListener(
		"keydown",
		function (event) {
			if (!openDialog || event.key !== "Escape") {
				return;
			}
			event.preventDefault();
			event.stopPropagation();
			closeDialog();
		},
		true
	);
})();
