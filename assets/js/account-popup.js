/**
 * Header My Account popup — docks under the account icon, right-aligned.
 */
(function () {
	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	ready(function () {
		var layer = document.getElementById("ffAccountLayer");
		var modal = document.getElementById("ffAccountModal");
		var backdrop = document.getElementById("ffAccountBackdrop");
		var closeBtn = document.getElementById("ffAccountClose");
		var lastTrigger = null;
		var GAP = 8;

		if (!layer || !modal) {
			return;
		}

		if (layer.parentElement !== document.body) {
			document.body.appendChild(layer);
		}

		function accountUrlNeedle(href) {
			if (!href) return false;
			var h = String(href).toLowerCase();
			return h.indexOf("my-account") !== -1 || h.indexOf("mein-konto") !== -1;
		}

		function findTrigger(el) {
			if (!el || !el.closest) return null;
			if (el.closest(".ff-account-layer")) return null;

			var icon = el.closest(
				".ff-account a, .ff-account .wp-block-button__link, a[href='#ff-open-account'], .ff-header__icons .wp-block-woocommerce-customer-account a, .wc-block-customer-account__account-icon"
			);
			if (icon) return icon.closest("a") || icon;

			var link = el.closest("a[href]");
			if (!link) return null;

			if (link.getAttribute("data-ff-search-account") === "1") {
				return link;
			}

			var inHeader = el.closest(".ff-header");
			var inSearch = el.closest(".ff-search-layer, #ffSearchLayer");
			if ((inHeader || inSearch) && accountUrlNeedle(link.getAttribute("href"))) {
				return link;
			}
			return null;
		}

		function headerAccountTrigger() {
			return (
				document.querySelector(".ff-header .ff-account .wp-block-button__link") ||
				document.querySelector(".ff-header .ff-account a") ||
				document.querySelector(".ff-header .wp-block-woocommerce-customer-account a") ||
				document.querySelector("a[href='#ff-open-account']")
			);
		}

		function positionModal(anchor) {
			var trigger = anchor || lastTrigger || headerAccountTrigger();
			if (!trigger || !modal) {
				return;
			}

			var rect = trigger.getBoundingClientRect();
			var vw = window.innerWidth || document.documentElement.clientWidth || 0;
			var vh = window.innerHeight || document.documentElement.clientHeight || 0;
			var gap = GAP;
			var top = Math.round(rect.bottom + gap);
			var right = Math.max(8, Math.round(vw - rect.right));

			/* Prefer measured width; fall back to CSS min(320px, …). */
			var modalW = modal.offsetWidth || Math.min(320, vw - 16);
			var maxRight = Math.max(8, vw - modalW - 8);
			if (right > maxRight) {
				right = maxRight;
			}

			var maxH = Math.max(180, vh - top - 12);
			modal.style.setProperty("top", top + "px", "important");
			modal.style.setProperty("right", right + "px", "important");
			modal.style.setProperty("left", "auto", "important");
			modal.style.setProperty("bottom", "auto", "important");
			modal.style.setProperty("translate", "none", "important");
			modal.style.setProperty("max-height", maxH + "px", "important");
		}

		function clearPosition() {
			if (!modal) return;
			["top", "right", "left", "bottom", "translate", "max-height"].forEach(function (prop) {
				modal.style.removeProperty(prop);
			});
		}

		function closeOthers() {
			var search = document.getElementById("ffSearchLayer");
			if (search && search.classList.contains("is-open")) {
				search.classList.remove("is-open");
				search.setAttribute("aria-hidden", "true");
				document.body.classList.remove("is-ff-search-open");
			}
			if (typeof window.fairflockCloseMenu === "function") {
				window.fairflockCloseMenu();
			} else {
				var menu = document.getElementById("ffMenuLayer");
				if (menu && menu.classList.contains("is-open")) {
					menu.classList.remove("is-open");
					menu.setAttribute("aria-hidden", "true");
					document.body.classList.remove("is-ff-menu-open");
				}
			}
			if (typeof window.fairflockCloseMega === "function") {
				window.fairflockCloseMega();
			}
			var cart = document.getElementById("ffCartLayer");
			if (cart && cart.classList.contains("is-open")) {
				cart.classList.remove("is-open");
				cart.setAttribute("aria-hidden", "true");
				document.body.classList.remove("is-ff-cart-open");
			}
		}

		function setExpanded(open) {
			document
				.querySelectorAll(
					".ff-account a, .ff-account .wp-block-button__link, a[href='#ff-open-account'], .ff-header__icons .wp-block-woocommerce-customer-account a"
				)
				.forEach(function (a) {
					a.setAttribute("aria-expanded", open ? "true" : "false");
					a.setAttribute("aria-controls", "ffAccountLayer");
				});
		}

		function isOpen() {
			return layer.classList.contains("is-open");
		}

		function openAccount(e, trigger) {
			if (e) {
				e.preventDefault();
				e.stopPropagation();
			}
			lastTrigger = trigger || lastTrigger || headerAccountTrigger();
			closeOthers();
			positionModal(lastTrigger);
			layer.classList.add("is-open");
			layer.setAttribute("aria-hidden", "false");
			document.body.classList.add("is-ff-account-open");
			setExpanded(true);
			/* Re-measure after paint (login form height etc.). */
			window.requestAnimationFrame(function () {
				positionModal(lastTrigger);
			});
			window.setTimeout(function () {
				positionModal(lastTrigger);
				if (closeBtn) closeBtn.focus();
			}, 40);
		}

		function closeAccount() {
			layer.classList.remove("is-open");
			layer.setAttribute("aria-hidden", "true");
			document.body.classList.remove("is-ff-account-open");
			setExpanded(false);
			clearPosition();
			lastTrigger = null;
		}

		document.addEventListener(
			"click",
			function (e) {
				var trigger = findTrigger(e.target);
				if (!trigger) return;
				if (isOpen()) {
					closeAccount();
					e.preventDefault();
					e.stopPropagation();
					return;
				}
				openAccount(e, trigger);
			},
			true
		);

		if (closeBtn) closeBtn.addEventListener("click", closeAccount);
		if (backdrop) backdrop.addEventListener("click", closeAccount);
		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && isOpen()) {
				closeAccount();
			}
		});

		window.addEventListener(
			"resize",
			function () {
				if (isOpen()) positionModal(lastTrigger);
			},
			{ passive: true }
		);
		window.addEventListener(
			"scroll",
			function () {
				if (isOpen()) positionModal(lastTrigger);
			},
			{ passive: true }
		);

		window.fairflockOpenAccount = function () {
			openAccount(null, headerAccountTrigger());
		};
		window.fairflockCloseAccount = closeAccount;
	});
})();
