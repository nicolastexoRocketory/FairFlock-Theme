/**
 * Header My Account popup — intercept account icon / nav links.
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

		if (!layer || !modal) {
			return;
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
				".ff-account a, .ff-header__icons .wp-block-woocommerce-customer-account a, .wc-block-customer-account__account-icon"
			);
			if (icon) return icon.closest("a") || icon;

			var inHeader = el.closest(".ff-header");
			if (!inHeader) return null;

			var link = el.closest("a[href]");
			if (link && accountUrlNeedle(link.getAttribute("href"))) {
				return link;
			}
			return null;
		}

		function closeOthers() {
			var search = document.getElementById("ffSearchLayer");
			if (search && search.classList.contains("is-open")) {
				search.classList.remove("is-open");
				search.setAttribute("aria-hidden", "true");
				document.body.classList.remove("is-ff-search-open");
			}
			var cart = document.getElementById("ffCartLayer");
			if (cart && cart.classList.contains("is-open")) {
				cart.classList.remove("is-open");
				cart.setAttribute("aria-hidden", "true");
				document.body.classList.remove("is-ff-cart-open");
			}
		}

		function setExpanded(open) {
			document.querySelectorAll(".ff-account a, .ff-header__icons .wp-block-woocommerce-customer-account a").forEach(function (a) {
				a.setAttribute("aria-expanded", open ? "true" : "false");
				a.setAttribute("aria-controls", "ffAccountLayer");
			});
		}

		function openAccount(e) {
			if (e) {
				e.preventDefault();
				e.stopPropagation();
			}
			closeOthers();
			layer.classList.add("is-open");
			layer.setAttribute("aria-hidden", "false");
			document.body.classList.add("is-ff-account-open");
			setExpanded(true);
			window.setTimeout(function () {
				if (closeBtn) closeBtn.focus();
			}, 40);
		}

		function closeAccount() {
			layer.classList.remove("is-open");
			layer.setAttribute("aria-hidden", "true");
			document.body.classList.remove("is-ff-account-open");
			setExpanded(false);
		}

		document.addEventListener(
			"click",
			function (e) {
				var trigger = findTrigger(e.target);
				if (!trigger) return;
				openAccount(e);
			},
			true
		);

		if (closeBtn) closeBtn.addEventListener("click", closeAccount);
		if (backdrop) backdrop.addEventListener("click", closeAccount);
		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && layer.classList.contains("is-open")) {
				closeAccount();
			}
		});
	});
})();
