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

	function paintDoc(doc) {
		if (!doc || !doc.body) {
			return;
		}
		bodyClasses.forEach(function (c) {
			if (c) {
				doc.body.classList.add(c);
			}
		});
		if (headerMod) {
			var headers = doc.querySelectorAll(".ff-header");
			headers.forEach(function (el) {
				if (el && el.classList && !el.className.match(/(^|\s)ff-header__/)) {
					el.classList.add(headerMod);
				}
			});
		}
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
			if (headerMod) {
				wrap.querySelectorAll(".ff-header").forEach(function (el) {
					if (el && !el.className.match(/(^|\s)ff-header__/)) {
						el.classList.add(headerMod);
					}
				});
			}
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
