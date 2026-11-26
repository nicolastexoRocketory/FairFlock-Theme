/**
 * Bridge Darstellung options into the Site Editor canvas (iframe body + header mod).
 * Frontend uses body_class + render_block; the canvas needs the same classes.
 * Also mirrors mobile header chrome (Burger+Suche | Logo | Cart+Account) so
 * the Mobil-Vorschau matches the frontend.
 */
(function (wp, config) {
	if (!wp || !config) {
		return;
	}

	var bodyClasses = config.bodyClasses || [];
	var headerMod = config.headerMod || "";
	var navLayout = config.navLayout || "";
	var MOBILE_MQ = "(max-width: 960px)";

	function paintHeader(el) {
		if (!el || !el.classList || el.className.match(/(^|\s)ff-header__/)) {
			return;
		}
		if (headerMod) {
			el.classList.add(headerMod);
		}
		if (navLayout) {
			el.classList.add(navLayout);
		}
	}

	function deviceTypeFromStore() {
		try {
			if (!wp.data || !wp.data.select) {
				return null;
			}
			var editor = wp.data.select("core/editor");
			if (editor && typeof editor.getDeviceType === "function") {
				return editor.getDeviceType();
			}
			var site = wp.data.select("core/edit-site");
			if (site && typeof site.__experimentalGetPreviewDeviceType === "function") {
				return site.__experimentalGetPreviewDeviceType();
			}
		} catch (e) {
			/* ignore */
		}
		return null;
	}

	function isMobileDoc(doc) {
		var dt = deviceTypeFromStore();
		if (dt === "Mobile" || dt === "Tablet") {
			return true;
		}
		if (!doc || !doc.defaultView) {
			return false;
		}
		try {
			if (doc.defaultView.matchMedia(MOBILE_MQ).matches) {
				return true;
			}
		} catch (e) {
			if (doc.documentElement && doc.documentElement.clientWidth <= 960) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Same 3-column chrome as assets/js/header-search.js layoutHeaderChrome.
	 * Editor has no frontend scripts — without this, Mobil shows logo left + Lupe right.
	 */
	function layoutMobileHeader(doc) {
		if (!doc) {
			return;
		}
		var bar = doc.querySelector(".ff-header__bar");
		var brand = doc.querySelector(".ff-header__brand");
		var icons = doc.querySelector(".ff-header__icons, .ff-header__actions");
		var burgerEl = doc.querySelector(".ff-header .ff-nav-burger");
		var searchEl = doc.querySelector(".ff-header .ff-search-toggle");
		var logoEl =
			(brand && brand.querySelector(".wp-block-site-logo")) ||
			(bar && bar.querySelector(":scope > .wp-block-site-logo, :scope > .ff-header__logo"));
		var mobile = isMobileDoc(doc);

		if (doc.body) {
			doc.body.classList.toggle("is-ff-editor-mobile", mobile);
		}

		if (!bar || !brand || !burgerEl) {
			return;
		}

		var burgerNode =
			burgerEl.closest(".ff-header__menu-btns") ||
			burgerEl.closest(".wp-block-buttons") ||
			burgerEl;
		var searchNode = searchEl
			? searchEl.closest(".wp-block-buttons") || searchEl
			: null;

		if (brand.firstElementChild !== burgerNode) {
			brand.insertBefore(burgerNode, brand.firstChild);
		}

		if (mobile) {
			if (searchNode && burgerNode.nextSibling !== searchNode) {
				brand.insertBefore(searchNode, burgerNode.nextSibling);
			}
			if (logoEl && logoEl.parentElement !== bar) {
				logoEl.classList.add("ff-header__logo");
				var beforeIcons = icons && icons.parentElement === bar ? icons : null;
				bar.insertBefore(logoEl, beforeIcons);
			}
		} else {
			if (searchNode && icons && searchEl && !icons.contains(searchEl)) {
				icons.insertBefore(searchNode, icons.firstChild);
			}
			if (logoEl && brand && logoEl.parentElement !== brand) {
				logoEl.classList.remove("ff-header__logo");
				brand.appendChild(logoEl);
			}
		}
	}

	function paintDoc(doc) {
		if (!doc || !doc.body) {
			return;
		}
		bodyClasses.forEach(function (c) {
			if (c) {
				doc.body.classList.add(c);
			}
		});
		doc.querySelectorAll(".ff-header").forEach(paintHeader);
		layoutMobileHeader(doc);
	}

	function getCanvasIframe() {
		return (
			document.querySelector('iframe[name="editor-canvas"]') ||
			document.querySelector(".edit-site-visual-editor__editor-canvas iframe") ||
			document.querySelector(".block-editor-block-preview__content iframe")
		);
	}

	function paint() {
		var iframe = getCanvasIframe();
		if (iframe && iframe.contentDocument) {
			paintDoc(iframe.contentDocument);
		}
		var wrap = document.querySelector(".editor-styles-wrapper");
		if (wrap) {
			bodyClasses.forEach(function (c) {
				if (c) {
					wrap.classList.add(c);
				}
			});
			wrap.querySelectorAll(".ff-header").forEach(paintHeader);
		}
	}

	function bindIframeResize(iframe) {
		if (!iframe || iframe._ffMobileBound) {
			return;
		}
		iframe._ffMobileBound = true;
		var onResize = function () {
			paint();
		};
		try {
			if (iframe.contentWindow) {
				iframe.contentWindow.addEventListener("resize", onResize);
			}
		} catch (e) {
			/* cross-origin — ignore */
		}
		if (typeof ResizeObserver === "function") {
			var ro = new ResizeObserver(onResize);
			ro.observe(iframe);
		}
	}

	function start() {
		paint();
		var iframe = getCanvasIframe();
		if (iframe) {
			bindIframeResize(iframe);
			iframe.addEventListener("load", function () {
				paint();
				bindIframeResize(iframe);
			});
		}

		var n = 0;
		var id = setInterval(function () {
			paint();
			var frame = getCanvasIframe();
			if (frame) {
				bindIframeResize(frame);
			}
			n += 1;
			if (n > 50) {
				clearInterval(id);
			}
		}, 400);

		if (typeof MutationObserver === "function") {
			var obs = new MutationObserver(function () {
				paint();
				var frame = getCanvasIframe();
				if (frame) {
					bindIframeResize(frame);
				}
			});
			obs.observe(document.body, { childList: true, subtree: true });
		}

		/* Device preview / canvas width changes in the editor store */
		if (wp.data && wp.data.subscribe) {
			var lastW = null;
			var lastDt = null;
			wp.data.subscribe(function () {
				try {
					var frame = getCanvasIframe();
					var w = frame ? frame.offsetWidth : null;
					var dt = deviceTypeFromStore();
					if (w !== lastW || dt !== lastDt) {
						lastW = w;
						lastDt = dt;
						paint();
					}
				} catch (e) {
					/* ignore */
				}
			});
		}
	}

	if (wp.domReady) {
		wp.domReady(start);
	} else {
		start();
	}
})(window.wp, window.fairflockDarstellung || {});
