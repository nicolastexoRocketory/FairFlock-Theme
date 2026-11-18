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
		var storeNonce = "";
		var cartToken = "";
		var busyKey = "";

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

		function decodeEntities(s) {
			var t = document.createElement("textarea");
			t.innerHTML = String(s || "");
			return t.value;
		}

		function captureAuth(res) {
			if (!res || !res.headers) return;
			var n = res.headers.get("Nonce") || res.headers.get("nonce");
			var t = res.headers.get("Cart-Token") || res.headers.get("cart-token");
			if (n) storeNonce = n;
			if (t) cartToken = t;
		}

		function cartHeaders(extra) {
			var headers = Object.assign(
				{
					Accept: "application/json",
					"Content-Type": "application/json"
				},
				extra || {}
			);
			if (storeNonce) headers.Nonce = storeNonce;
			if (cartToken) headers["Cart-Token"] = cartToken;
			return headers;
		}

		function updateBadge(cart) {
			if (!countBadge) return;
			var n = cart && typeof cart.items_count === "number" ? cart.items_count : 0;
			countBadge.textContent = String(n);
			countBadge.hidden = n <= 0;
			countBadge.setAttribute("data-count", String(n));
		}

		function qtyIconTrash() {
			return (
				'<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none">' +
				'<path d="M9 4h6m-8 3h10m-1.5 0-.7 11.2a1.5 1.5 0 0 1-1.5 1.4h-4.6a1.5 1.5 0 0 1-1.5-1.4L6.5 7M10 10.5v6M14 10.5v6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>' +
				"</svg>"
			);
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
					var key = String(item.key || "");
					var qty = Number(item.quantity || 1);
					var limits = item.quantity_limits || {};
					var minQty = typeof limits.minimum === "number" ? limits.minimum : 1;
					var maxQty = typeof limits.maximum === "number" ? limits.maximum : 99;
					var editable = limits.editable !== false;
					var name = decodeEntities(item.name || "");
					var permalink = item.permalink || "";
					var img =
						item.images && item.images[0]
							? item.images[0].thumbnail || item.images[0].src || ""
							: "";
					var thumbInner = img
						? ' style="background-image:url(\'' + String(img).replace(/'/g, "%27") + "')\""
						: "";
					var thumb = permalink
						? '<a class="ff-cart-line__thumb" href="' +
						  escapeHtml(permalink) +
						  '"' +
						  thumbInner +
						  ' aria-hidden="true" tabindex="-1"></a>'
						: '<div class="ff-cart-line__thumb"' + thumbInner + "></div>";
					var title = permalink
						? '<a class="ff-cart-line__title" href="' +
						  escapeHtml(permalink) +
						  '">' +
						  escapeHtml(name) +
						  "</a>"
						: '<p class="ff-cart-line__title">' + escapeHtml(name) + "</p>";
					var price = formatMoney({
						total_items: item.prices ? item.prices.line_total || item.prices.price : 0,
						currency_minor_unit: item.prices ? item.prices.currency_minor_unit : 2,
						currency_symbol: item.prices ? item.prices.currency_symbol : "€"
					});
					var qtyCtrl = editable
						? '<div class="ff-cart-line__qty" role="group" aria-label="Menge">' +
						  '<button type="button" class="ff-cart-line__qty-btn" data-ff-cart-qty="-1" data-ff-cart-key="' +
						  escapeHtml(key) +
						  '" aria-label="Menge verringern"' +
						  (qty <= minQty ? " disabled" : "") +
						  ">−</button>" +
						  '<span class="ff-cart-line__qty-val" aria-live="polite">' +
						  escapeHtml(String(qty)) +
						  "</span>" +
						  '<button type="button" class="ff-cart-line__qty-btn" data-ff-cart-qty="1" data-ff-cart-key="' +
						  escapeHtml(key) +
						  '" aria-label="Menge erhöhen"' +
						  (qty >= maxQty ? " disabled" : "") +
						  ">+</button>" +
						  "</div>"
						: '<div class="ff-cart-line__qty is-static"><span class="ff-cart-line__qty-val">' +
						  escapeHtml(String(qty)) +
						  "</span></div>";

					return (
						'<article class="ff-cart-line' +
						(busyKey === key ? " is-busy" : "") +
						'" data-ff-cart-key="' +
						escapeHtml(key) +
						'" data-ff-cart-qty="' +
						escapeHtml(String(qty)) +
						'" data-ff-cart-min="' +
						escapeHtml(String(minQty)) +
						'" data-ff-cart-max="' +
						escapeHtml(String(maxQty)) +
						'">' +
						thumb +
						'<div class="ff-cart-line__meta">' +
						title +
						'<div class="ff-cart-line__actions">' +
						qtyCtrl +
						'<button type="button" class="ff-cart-line__remove" data-ff-cart-remove="' +
						escapeHtml(key) +
						'" aria-label="Produkt entfernen">' +
						qtyIconTrash() +
						"</button>" +
						"</div></div>" +
						'<div class="ff-cart-line__price">' +
						price +
						"</div></article>"
					);
				})
				.join("");
		}

		function notifyCartChange() {
			if (window.jQuery) {
				window.jQuery(document.body).trigger("wc_fragment_refresh");
				window.jQuery(document.body).trigger("updated_wc_div");
			}
		}

		function mutateCart(path, body, key) {
			busyKey = key || "";
			if (linesEl) {
				linesEl.querySelectorAll(".ff-cart-line").forEach(function (el) {
					el.classList.toggle("is-busy", el.getAttribute("data-ff-cart-key") === busyKey);
				});
			}
			return fetch(cartApi.replace(/\/?$/, "/") + path, {
				method: "POST",
				credentials: "same-origin",
				headers: cartHeaders(),
				body: JSON.stringify(body)
			})
				.then(function (res) {
					captureAuth(res);
					if (!res.ok) throw new Error("cart mutate");
					return res.json();
				})
				.then(function (cart) {
					busyKey = "";
					renderCart(cart);
					notifyCartChange();
					return cart;
				})
				.catch(function () {
					busyKey = "";
					fetchCart();
				});
		}

		function updateItemQty(key, nextQty) {
			if (!key) return;
			var qty = Math.max(0, Number(nextQty) || 0);
			if (qty <= 0) {
				return removeItem(key);
			}
			return mutateCart("update-item", { key: key, quantity: qty }, key);
		}

		function removeItem(key) {
			if (!key) return;
			return mutateCart("remove-item", { key: key }, key);
		}

		function fetchCart() {
			if (!window.fetch) return;
			fetch(cartApi, {
				credentials: "same-origin",
				headers: { Accept: "application/json" }
			})
				.then(function (r) {
					captureAuth(r);
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

		if (linesEl) {
			linesEl.addEventListener("click", function (e) {
				var removeBtn = e.target.closest && e.target.closest("[data-ff-cart-remove]");
				if (removeBtn && linesEl.contains(removeBtn)) {
					e.preventDefault();
					if (busyKey) return;
					removeItem(removeBtn.getAttribute("data-ff-cart-remove"));
					return;
				}

				var qtyBtn = e.target.closest && e.target.closest("[data-ff-cart-qty]");
				if (qtyBtn && linesEl.contains(qtyBtn)) {
					e.preventDefault();
					if (busyKey || qtyBtn.disabled) return;
					var line = qtyBtn.closest(".ff-cart-line");
					var key = qtyBtn.getAttribute("data-ff-cart-key") || (line && line.getAttribute("data-ff-cart-key"));
					var current = Number(line && line.getAttribute("data-ff-cart-qty")) || 1;
					var delta = Number(qtyBtn.getAttribute("data-ff-cart-qty")) || 0;
					updateItemQty(key, current + delta);
				}
			});
		}

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
