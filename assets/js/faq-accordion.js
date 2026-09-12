/**
 * FAQ accordion — height expand/collapse for .ff-faq-item details.
 * details stays open after init so UA display:none cannot break the transition.
 */
(function () {
	var DURATION_MS = 360;
	var EASING = "cubic-bezier(0.16, 1, 0.3, 1)";

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function prefersReducedMotion() {
		return (
			window.matchMedia &&
			window.matchMedia("(prefers-reduced-motion: reduce)").matches
		);
	}

	function ensurePanel(details) {
		var panel = details.querySelector(":scope > .ff-faq-item__panel");
		if (!panel) {
			panel = document.createElement("div");
			panel.className = "ff-faq-item__panel";

			var move = [];
			var child = details.firstChild;
			while (child) {
				var next = child.nextSibling;
				if (
					!(
						child.nodeType === 1 &&
						String(child.tagName).toLowerCase() === "summary"
					)
				) {
					move.push(child);
				}
				child = next;
			}
			move.forEach(function (node) {
				panel.appendChild(node);
			});
			details.appendChild(panel);
		}

		var inner = panel.querySelector(":scope > .ff-faq-item__inner");
		if (!inner) {
			inner = document.createElement("div");
			inner.className = "ff-faq-item__inner";
			while (panel.firstChild) {
				inner.appendChild(panel.firstChild);
			}
			panel.appendChild(inner);
		}

		return panel;
	}

	function setExpanded(details, expanded) {
		details.classList.toggle("is-expanded", expanded);
		var summary = details.querySelector(":scope > summary");
		if (summary) {
			summary.setAttribute("aria-expanded", expanded ? "true" : "false");
		}
		var panel = details.querySelector(":scope > .ff-faq-item__panel");
		if (panel) {
			panel.setAttribute("aria-hidden", expanded ? "false" : "true");
			if (expanded) {
				panel.removeAttribute("inert");
			} else {
				panel.setAttribute("inert", "");
			}
		}
	}

	function clearInlineHeight(panel) {
		panel.style.height = "";
	}

	function animateOpen(details, panel) {
		if (prefersReducedMotion()) {
			details.open = true;
			setExpanded(details, true);
			clearInlineHeight(panel);
			return;
		}

		details.classList.add("is-animating");
		details.open = true;
		panel.style.height = "0px";
		void panel.offsetHeight;
		setExpanded(details, true);
		panel.style.height = panel.scrollHeight + "px";

		function onEnd(event) {
			if (event && event.target !== panel) {
				return;
			}
			if (event && event.propertyName && event.propertyName !== "height") {
				return;
			}
			panel.removeEventListener("transitionend", onEnd);
			clearInlineHeight(panel);
			details.classList.remove("is-animating");
		}

		panel.addEventListener("transitionend", onEnd);
		window.setTimeout(function () {
			if (details.classList.contains("is-animating")) {
				onEnd({ target: panel, propertyName: "height" });
			}
		}, DURATION_MS + 80);
	}

	function animateClose(details, panel) {
		if (prefersReducedMotion()) {
			setExpanded(details, false);
			panel.style.height = "0px";
			return;
		}

		details.classList.add("is-animating");
		panel.style.height = panel.scrollHeight + "px";
		void panel.offsetHeight;
		setExpanded(details, false);
		panel.style.height = "0px";

		function onEnd(event) {
			if (event && event.target !== panel) {
				return;
			}
			if (event && event.propertyName && event.propertyName !== "height") {
				return;
			}
			panel.removeEventListener("transitionend", onEnd);
			panel.style.height = "0px";
			details.classList.remove("is-animating");
		}

		panel.addEventListener("transitionend", onEnd);
		window.setTimeout(function () {
			if (details.classList.contains("is-animating")) {
				onEnd({ target: panel, propertyName: "height" });
			}
		}, DURATION_MS + 80);
	}

	function bindItem(details) {
		if (details.dataset.ffFaqBound === "1") {
			return;
		}
		details.dataset.ffFaqBound = "1";

		var summary = details.querySelector(":scope > summary");
		if (!summary) {
			return;
		}

		var panel = ensurePanel(details);
		panel.style.transition = "height " + DURATION_MS + "ms " + EASING;

		var startOpen = !!details.open;
		// Always open for animation; visual state is is-expanded + height.
		details.open = true;
		setExpanded(details, startOpen);
		if (!startOpen) {
			panel.style.height = "0px";
		}

		summary.addEventListener("click", function (event) {
			event.preventDefault();
			event.stopPropagation();
			if (details.classList.contains("is-animating")) {
				return;
			}
			if (details.classList.contains("is-expanded")) {
				animateClose(details, panel);
			} else {
				animateOpen(details, panel);
			}
		});
	}

	function init(root) {
		var scope = root && root.querySelectorAll ? root : document;
		var items = scope.querySelectorAll(
			".ff-faq-list .ff-faq-item, .ff-faq-list details.wp-block-details"
		);
		items.forEach(bindItem);
	}

	ready(function () {
		init(document);
	});
})();
