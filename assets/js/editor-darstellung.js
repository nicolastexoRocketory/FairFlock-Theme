/**
 * Bridge Darstellung options into the Site Editor canvas (iframe body + header mod).
 * Frontend uses body_class + render_block; the canvas needs the same classes.
 */
(function (wp, config) {
	if (!wp || !config) {
		return;
	}

	var bodyClasses = config.bodyClasses || [];
	var headerMod = config.headerMod || "";
	var navLayout = config.navLayout || "";

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
	}

	function paint() {
		var iframe =
			document.querySelector('iframe[name="editor-canvas"]') ||
			document.querySelector(".edit-site-visual-editor__editor-canvas iframe") ||
			document.querySelector(".block-editor-block-preview__content iframe");
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

	function start() {
		paint();
		var n = 0;
		var id = setInterval(function () {
			paint();
			n += 1;
			if (n > 40) {
				clearInterval(id);
			}
		}, 400);
		if (typeof MutationObserver === "function") {
			var obs = new MutationObserver(function () {
				paint();
			});
			obs.observe(document.body, { childList: true, subtree: true });
		}
	}

	if (wp.domReady) {
		wp.domReady(start);
	} else {
		start();
	}
})(window.wp, window.fairflockDarstellung || {});
