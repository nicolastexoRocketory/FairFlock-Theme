/**
 * Cart slide-in drawer — Store API powered, styleguide behavior.
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
		var layer = document.getElementById("ffCartLayer");
		var drawer = document.getElementById("ffCartDrawer");
		var backdrop = document.getElementById("ffCartBackdrop");
		var closeBtn = document.getElementById("ffCartClose");
		var linesEl = document.getElementById("ffCartLines");
		var emptyEl = document.getElementById("ffCartEmpty");
		var subtotalEl = document.getElementById("ffCartSubtotal");
		var checkoutBtn = document.getElementById("ffCartCheckout");
		var pageLink = document.getElementById("ffCartPageLink");
		var openBtn =
			document.querySelector(".ff-cart .wp-block-button__link") ||
			document.querySelector(".ff-cart a");
		var countBadge = document.getElementById("ffCartCount");

		if (!layer || !drawer || !openBtn) {
			return;
		}

		// Escape header transform containing-block (same as search).
		if (layer.parentElement !== document.body) {
			document.body.appendChild(layer);
		}

		var cartApi = window.fairflockCartApi || "/wp-json/wc/store/v1/cart";
		var checkoutUrl = window.fairflockCheckoutUrl || "/checkout/";
		var cartPageUrl = window.fairflockCartUrl || "/cart/";

		if (checkoutBtn) checkoutBtn.href = checkoutUrl;
		if (pageLink) pageLink.href = cartPageUrl;

		function formatMoney(totals) {
			if (!totals) return "0,00\u00a0€";
			var minor = typeof totals.currency_minor_unit === "number" ? totals.currency_minor_unit : 2;
			var raw = Number(totals.total_items || totals.total_price || 0);
			if (!isFinite(raw)) return "0,00\u00a0€";
			var value = raw / Math.pow(10, minor);
			var formatted = value.toLocaleString("de-DE", {
				minimumFractionDigits: minor,
				maximumFractionDigits: minor
			});
			var symbol = totals.currency_symbol || "€";
			return formatted + "\u00a0" + symbol;
		}

		function escapeHtml(s) {
			return String(s).replace(/[&<>"']/g, function (c) {
				return ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c];
			});
		}

		function updateBadge(cart) {
			if (!countBadge) return;
			var n = cart && typeof cart.items_count === "number" ? cart.items_count : 0;
			countBadge.textContent = String(n);
			countBadge.hidden = n <= 0;
			countBadge.setAttribute("data-count", String(n));
		}

		function renderCart(cart) {
			updateBadge(cart);
			var items = (cart && cart.items) || [];
			if (subtotalEl) subtotalEl.innerHTML = formatMoney(cart && cart.totals);
			if (checkoutBtn && cart && cart.checkout_url) {
				checkoutBtn.href = cart.checkout_url;
			}

			if (!items.length) {
				if (emptyEl) emptyEl.hidden = false;
				if (linesEl) {
					linesEl.hidden = true;
					linesEl.innerHTML = "";
				}
				return;
			}

			if (emptyEl) emptyEl.hidden = true;
			if (!linesEl) return;
			linesEl.hidden = false;
			linesEl.innerHTML = items
				.map(function (item) {
					var img =
						item.images && item.images[0]
							? item.images[0].thumbnail || item.images[0].src || ""
							: "";
					var thumb = img
						? '<div class="ff-cart-line__thumb" style="background-image:url(\'' +
						  String(img).replace(/'/g, "%27") +
						  "')\"></div>"
						: '<div class="ff-cart-line__thumb"></div>';
					var price = formatMoney({
						total_items: item.prices ? item.prices.line_total || item.prices.price : 0,
						currency_minor_unit: item.prices ? item.prices.currency_minor_unit : 2,
						currency_symbol: item.prices ? item.prices.currency_symbol : "€"
					});
					return (
						'<article class="ff-cart-line">' +
						thumb +
						'<div class="ff-cart-line__meta"><p class="ff-cart-line__title">' +
						escapeHtml(item.name || "") +
						"</p><p class=\"ff-cart-line__qty\">Menge " +
						escapeHtml(String(item.quantity || 1)) +
						"</p></div>" +
						'<div class="ff-cart-line__price">' +
						price +
						"</div></article>"
					);
				})
				.join("");
		}

		function fetchCart() {
			if (!window.fetch) return;
			fetch(cartApi, {
				credentials: "same-origin",
				headers: { Accept: "application/json" }
			})
				.then(function (r) {
					if (!r.ok) throw new Error("cart");
					return r.json();
				})
				.then(renderCart)
				.catch(function () {
					/* keep empty state */
				});
		}

		var addedResetTimer = null;
		var ATC_LABEL = "Wurde hinzugefügt";

		function findAtcButtons() {
			return Array.prototype.slice.call(
				document.querySelectorAll(
					".ff-product .single_add_to_cart_button, form.cart .single_add_to_cart_button, .ff-product button[name='add-to-cart']"
				)
			);
		}

		function markAddToCartSuccess(sourceBtn) {
			var buttons = sourceBtn ? [sourceBtn] : findAtcButtons();
			buttons.forEach(function (btn) {
				if (!btn || btn.nodeType !== 1) {
					return;
				}
				if (!btn.getAttribute("data-ff-atc-label")) {
					btn.setAttribute("data-ff-atc-label", (btn.textContent || "").trim());
				}
				btn.classList.add("is-ff-added");
				btn.setAttribute("aria-live", "polite");
				btn.innerHTML =
					'<span class="ff-atc-check" aria-hidden="true"></span><span class="ff-atc-label">' +
					ATC_LABEL +
					"</span>";
			});

			if (addedResetTimer) {
				window.clearTimeout(addedResetTimer);
			}
			addedResetTimer = window.setTimeout(function () {
				findAtcButtons().forEach(function (btn) {
					btn.classList.remove("is-ff-added");
					btn.removeAttribute("aria-live");
					var prev = btn.getAttribute("data-ff-atc-label");
					if (prev) {
						btn.textContent = prev;
					}
				});
			}, 4000);
		}

		function hideAddedNotices() {
			document
				.querySelectorAll(
					".ff-product .woocommerce-message, .ff-product .wc-block-components-notice-banner.is-success, .wp-block-woocommerce-store-notices .wc-block-components-notice-banner.is-success"
				)
				.forEach(function (el) {
					el.hidden = true;
					el.setAttribute("aria-hidden", "true");
				});
		}

		function openCart(e) {
			if (e) {
				e.preventDefault();
				e.stopPropagation();
			}
			layer.classList.add("is-open");
			layer.setAttribute("aria-hidden", "false");
			openBtn.setAttribute("aria-expanded", "true");
			document.body.classList.add("is-ff-cart-open");
			fetchCart();
			window.setTimeout(function () {
				if (closeBtn) closeBtn.focus();
			}, 40);
		}

		function closeCart() {
			layer.classList.remove("is-open");
			layer.setAttribute("aria-hidden", "true");
			openBtn.setAttribute("aria-expanded", "false");
			document.body.classList.remove("is-ff-cart-open");
			openBtn.focus();
		}

		function onAddedToCart(sourceBtn) {
			hideAddedNotices();
			markAddToCartSuccess(sourceBtn || null);
			openCart();
		}

		window.fairflockOpenCart = openCart;
		window.fairflockOnAddedToCart = onAddedToCart;

		openBtn.addEventListener("click", openCart);
		if (closeBtn) closeBtn.addEventListener("click", closeCart);
		if (backdrop) backdrop.addEventListener("click", closeCart);
		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && layer.classList.contains("is-open")) closeCart();
		});

		fetchCart();
		document.body.addEventListener("wc-blocks_added_to_cart", function () {
			onAddedToCart();
		});
		document.body.addEventListener("wc-blocks_removed_from_cart", fetchCart);

		if (window.jQuery) {
			window.jQuery(document.body).on("added_to_cart", function (_event, _fragments, _cartHash, button) {
				var el = button && button.jquery ? button.get(0) : button;
				onAddedToCart(el || null);
			});
		}

		if (window.fairflockJustAddedToCart) {
			window.fairflockJustAddedToCart = false;
			hideAddedNotices();
			markAddToCartSuccess(null);
			window.setTimeout(openCart, 120);
		}
	});
})();
