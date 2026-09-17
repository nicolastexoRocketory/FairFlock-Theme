/**
 * Product quick-view popup — intercepts product links, Store API powered.
 * Gallery slideshow + extended product details.
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
		var layer = document.getElementById("ffProductLayer");
		var modal = document.getElementById("ffProductModal");
		var backdrop = document.getElementById("ffProductBackdrop");
		var closeBtn = document.getElementById("ffProductClose");
		var bodyEl = document.getElementById("ffProductBody");

		if (!layer || !modal || !bodyEl) {
			return;
		}

		if (layer.parentElement !== document.body) {
			document.body.appendChild(layer);
		}

		var productsApi = window.fairflockSearchApi || "/wp-json/wc/store/v1/products";
		var cartApi = window.fairflockCartApi || "/wp-json/wc/store/v1/cart";
		var addItemApi = cartApi.replace(/\/?$/, "/") + "add-item";
		var personalizedAddApi =
			window.fairflockPersonalizedAddApi || "/wp-json/fairflock/v1/cart/add-personalized";
		var requestSeq = 0;
		var storeNonce = "";
		var cartToken = "";
		var galleryIndex = 0;
		var galleryImages = [];
		var currentProduct = null;

		function escapeHtml(s) {
			return String(s).replace(/[&<>"']/g, function (c) {
				return ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c];
			});
		}

		function decodeHtmlEntities(s) {
			if (!s) {
				return "";
			}
			var t = document.createElement("textarea");
			t.innerHTML = String(s);
			return t.value;
		}

		function safeText(s) {
			return escapeHtml(decodeHtmlEntities(s));
		}

		function formatPrice(prices, key) {
			if (!prices) return "";
			var minor = typeof prices.currency_minor_unit === "number" ? prices.currency_minor_unit : 2;
			var raw = Number(prices[key || "price"]);
			if (!isFinite(raw)) return "";
			var value = raw / Math.pow(10, minor);
			var formatted = value.toLocaleString("de-DE", {
				minimumFractionDigits: minor,
				maximumFractionDigits: minor
			});
			var symbol = prices.currency_symbol || "€";
			var prefix = prices.currency_prefix || "";
			var suffix = prices.currency_suffix || "";
			if (prefix || suffix) {
				return String(prefix) + formatted + String(suffix);
			}
			return formatted + "\u00a0" + symbol;
		}

		function productSlugFromUrl(href) {
			try {
				var u = new URL(href, window.location.origin);
				if (u.origin !== window.location.origin) return "";
				var parts = u.pathname.replace(/\/+$/, "").split("/").filter(Boolean);
				if (!parts.length) return "";

				var bases = window.fairflockProductBases;
				if (!Array.isArray(bases) || !bases.length) {
					bases = ["product", "produkt"];
				}

				var i;
				for (i = 0; i < bases.length; i++) {
					var base = String(bases[i] || "")
						.replace(/^\/+|\/+$/g, "")
						.split("/")
						.filter(Boolean)
						.pop();
					if (!base) continue;
					var idx = parts.indexOf(base);
					// Need a slug after the product base; ignore category roots like /produktkategorie/
					if (idx !== -1 && idx < parts.length - 1) {
						return decodeURIComponent(parts[parts.length - 1]);
					}
				}
				return "";
			} catch (e) {
				return "";
			}
		}

		function isProductLink(a) {
			if (!a || !a.getAttribute) return false;
			if (a.closest(".ff-product-layer, .ff-cart-layer, .ff-search-layer .ff-search-close")) {
				return false;
			}
			if (a.hasAttribute("download") || a.getAttribute("target") === "_blank") return false;
			if (a.classList.contains("ff-product-modal__full")) return false;
			var href = a.getAttribute("href") || "";
			if (!href || href.charAt(0) === "#") return false;
			return !!productSlugFromUrl(href);
		}

		function ensureCartAuth() {
			return fetch(cartApi, {
				credentials: "same-origin",
				headers: { Accept: "application/json" }
			}).then(function (res) {
				var n = res.headers.get("Nonce") || res.headers.get("nonce");
				var t = res.headers.get("Cart-Token") || res.headers.get("cart-token");
				if (n) storeNonce = n;
				if (t) cartToken = t;
				return res.json().catch(function () {
					return null;
				});
			});
		}

		function renderLoading() {
			bodyEl.innerHTML = '<p class="ff-product-modal__loading">Produkt wird geladen…</p>';
		}

		function renderError(msg) {
			bodyEl.innerHTML =
				'<p class="ff-product-modal__error">' +
				escapeHtml(msg || "Produkt konnte nicht geladen werden.") +
				"</p>";
		}

		function collectImages(p) {
			var list = Array.isArray(p.images) ? p.images : [];
			var out = [];
			list.forEach(function (img) {
				var src = (img && (img.src || img.thumbnail)) || "";
				if (!src) return;
				out.push({
					src: src,
					alt: (img && (img.alt || img.name)) || p.name || ""
				});
			});
			return out;
		}

		function renderGallery(images) {
			galleryImages = images;
			galleryIndex = 0;

			if (!images.length) {
				return '<div class="ff-product-modal__media ff-product-modal__media--empty" aria-hidden="true"></div>';
			}

			var slides = images
				.map(function (img, i) {
					return (
						'<figure class="ff-product-modal__slide' +
						(i === 0 ? " is-active" : "") +
						'" data-ff-slide="' +
						i +
						'">' +
						'<img src="' +
						escapeHtml(img.src) +
						'" alt="' +
						escapeHtml(img.alt) +
						'" loading="' +
						(i === 0 ? "eager" : "lazy") +
						'" />' +
						"</figure>"
					);
				})
				.join("");

			var nav =
				images.length > 1
					? '<div class="ff-product-modal__gallery-nav">' +
					  '<button type="button" class="ff-product-modal__gallery-btn" data-ff-gallery="prev" aria-label="Vorheriges Bild">←</button>' +
					  '<button type="button" class="ff-product-modal__gallery-btn" data-ff-gallery="next" aria-label="Nächstes Bild">→</button>' +
					  "</div>"
					: "";

			var thumbs =
				images.length > 1
					? '<div class="ff-product-modal__thumbs" role="tablist" aria-label="Alle Produktbilder">' +
					  images
							.map(function (img, i) {
								return (
									'<button type="button" class="ff-product-modal__thumb' +
									(i === 0 ? " is-active" : "") +
									'" data-ff-thumb="' +
									i +
									'" role="tab" aria-selected="' +
									(i === 0 ? "true" : "false") +
									'" aria-label="Bild ' +
									(i + 1) +
									'">' +
									'<img src="' +
									escapeHtml(img.src) +
									'" alt="" loading="lazy" />' +
									"</button>"
								);
							})
							.join("") +
					  "</div>"
					: "";

			return (
				'<div class="ff-product-modal__media" data-ff-gallery-root>' +
				'<div class="ff-product-modal__stage">' +
				'<div class="ff-product-modal__slides">' +
				slides +
				"</div>" +
				nav +
				"</div>" +
				thumbs +
				"</div>"
			);
		}

		function setGalleryIndex(next) {
			if (!galleryImages.length) return;
			var total = galleryImages.length;
			galleryIndex = ((next % total) + total) % total;

			bodyEl.querySelectorAll(".ff-product-modal__slide").forEach(function (slide) {
				var i = Number(slide.getAttribute("data-ff-slide"));
				slide.classList.toggle("is-active", i === galleryIndex);
			});
			bodyEl.querySelectorAll(".ff-product-modal__thumb").forEach(function (thumb) {
				var i = Number(thumb.getAttribute("data-ff-thumb"));
				var on = i === galleryIndex;
				thumb.classList.toggle("is-active", on);
				thumb.setAttribute("aria-selected", on ? "true" : "false");
				if (on && thumb.scrollIntoView) {
					thumb.scrollIntoView({
						block: "nearest",
						inline: "nearest",
						behavior: "smooth"
					});
				}
			});
		}

		function isMeaningfulMeta(value) {
			if (value == null) return false;
			var text = String(value)
				.replace(/<[^>]+>/g, "")
				.replace(/\u00a0/g, " ")
				.trim()
				.toLowerCase();
			return !!text && text !== "n. v." && text !== "n/a" && text !== "-";
		}

		function renderMetaRow(label, valueHtml) {
			if (!isMeaningfulMeta(valueHtml)) return "";
			return (
				'<div class="ff-product-modal__meta-row">' +
				"<dt>" +
				escapeHtml(label) +
				"</dt>" +
				"<dd>" +
				valueHtml +
				"</dd>" +
				"</div>"
			);
		}

		function renderAttributes(attrs) {
			if (!Array.isArray(attrs) || !attrs.length) return "";
			var rows = attrs
				.map(function (attr) {
					var name = attr.name || attr.taxonomy || "";
					var terms = Array.isArray(attr.terms)
						? attr.terms
								.map(function (t) {
									return t.name || t.label || "";
								})
								.filter(Boolean)
								.join(", ")
						: attr.options
						? (Array.isArray(attr.options) ? attr.options : []).join(", ")
						: "";
					if (!name || !terms) return "";
					return renderMetaRow(name, escapeHtml(terms));
				})
				.filter(Boolean)
				.join("");
			return rows
				? '<dl class="ff-product-modal__meta ff-product-modal__meta--attrs">' + rows + "</dl>"
				: "";
		}

		function renderCategories(cats) {
			if (!Array.isArray(cats) || !cats.length) return "";
			var html = cats
				.map(function (c) {
					var name = c.name || "";
					if (!name) return "";
					if (c.permalink || c.link) {
						return (
							'<a href="' +
							escapeHtml(c.permalink || c.link) +
							'">' +
							escapeHtml(name) +
							"</a>"
						);
					}
					return escapeHtml(name);
				})
				.filter(Boolean)
				.join(", ");
			return renderMetaRow("Kategorie", html);
		}

		function renderTags(tags) {
			if (!Array.isArray(tags) || !tags.length) return "";
			var html = tags
				.map(function (t) {
					return t.name ? escapeHtml(t.name) : "";
				})
				.filter(Boolean)
				.join(", ");
			return renderMetaRow("Schlagwörter", html);
		}

		function renderPriceBlock(p) {
			var prices = p.prices || {};
			var current = formatPrice(prices, "price");
			if (!current && p.price_html) {
				return '<div class="ff-product-modal__price">' + p.price_html + "</div>";
			}
			if (!current) return "";

			var regular = formatPrice(prices, "regular_price");
			var onSale =
				p.on_sale ||
				(regular && prices.regular_price && prices.price && prices.regular_price !== prices.price);

			if (onSale && regular && regular !== current) {
				return (
					'<p class="ff-product-modal__price">' +
					'<span class="ff-product-modal__price-sale">' +
					escapeHtml(current) +
					"</span> " +
					'<s class="ff-product-modal__price-regular">' +
					escapeHtml(regular) +
					"</s>" +
					"</p>"
				);
			}

			return '<p class="ff-product-modal__price">' + escapeHtml(current) + "</p>";
		}

		function renderStock(p) {
			var text =
				(p.stock_availability && (p.stock_availability.text || p.stock_availability)) ||
				"";
			if (typeof text !== "string") text = "";
			if (!text) {
				if (p.is_in_stock === false) text = "Nicht vorrätig";
				else if (p.is_on_backorder) text = "Nachlieferung";
				else if (p.is_in_stock) text = "Auf Lager";
			}
			if (!text) return "";
			var cls =
				p.is_in_stock === false
					? " is-out"
					: p.is_on_backorder
					? " is-backorder"
					: " is-in";
			return (
				'<p class="ff-product-modal__availability' +
				cls +
				'">' +
				escapeHtml(text) +
				"</p>"
			);
		}

		function renderRating(p) {
			var rating = Number(p.average_rating);
			var count = Number(p.review_count) || 0;
			if (!isFinite(rating) || rating <= 0) return "";
			return (
				'<p class="ff-product-modal__rating">' +
				'<span aria-hidden="true">★</span> ' +
				escapeHtml(rating.toFixed(1).replace(".", ",")) +
				(count
					? ' <span class="ff-product-modal__rating-count">(' +
					  count +
					  " Bewertung" +
					  (count === 1 ? "" : "en") +
					  ")</span>"
					: "") +
				"</p>"
			);
		}

		function getPersonalization(p) {
			var ext = (p && p.extensions && p.extensions.fairflock) || {};
			return {
				enabled: !!ext.enabled,
				markup: typeof ext.markup === "string" ? ext.markup : ""
			};
		}

		function renderProduct(p) {
			currentProduct = p;
			var images = collectImages(p);
			var shortDesc = p.short_description || "";
			var longDesc = p.description || "";
			var showLong =
				longDesc &&
				longDesc.replace(/\s+/g, "") !== String(shortDesc).replace(/\s+/g, "");
			var btnText =
				(p.add_to_cart && (p.add_to_cart.single_text || p.add_to_cart.text)) ||
				"In den Warenkorb";
			var purchasable = p.is_purchasable !== false && p.is_in_stock !== false;
			var hasOptions = !!p.has_options || (Array.isArray(p.variations) && p.variations.length > 0);
			var personalization = getPersonalization(p);
			var personalizationHtml =
				!hasOptions && personalization.enabled && personalization.markup
					? personalization.markup
					: "";

			var factsRows =
				renderCategories(p.categories) +
				(p.formatted_weight ? renderMetaRow("Gewicht", escapeHtml(p.formatted_weight)) : "") +
				(p.formatted_dimensions
					? renderMetaRow("Maße", escapeHtml(p.formatted_dimensions))
					: "");
			var facts = factsRows
				? '<dl class="ff-product-modal__meta">' + factsRows + "</dl>"
				: "";

			var topMetaRows =
				(p.sku ? renderMetaRow("Art.-Nr.", escapeHtml(p.sku)) : "") + renderTags(p.tags);
			var topMeta = topMetaRows
				? '<dl class="ff-product-modal__meta ff-product-modal__meta--top">' +
				  topMetaRows +
				  "</dl>"
				: "";

			var optionsNote = hasOptions
				? '<p class="ff-product-modal__note">Varianten und Optionen wählst du auf der Produktseite.</p>'
				: "";

			bodyEl.innerHTML =
				'<div class="ff-product-modal__grid">' +
				renderGallery(images) +
				'<div class="ff-product-modal__copy">' +
				'<div class="ff-product-modal__scroll">' +
				'<h2 id="ffProductTitle" class="ff-product-modal__title">' +
				safeText(p.name || "") +
				"</h2>" +
				renderRating(p) +
				renderPriceBlock(p) +
				renderStock(p) +
				topMeta +
				renderAttributes(p.attributes) +
				(shortDesc ? '<div class="ff-product-modal__excerpt">' + shortDesc + "</div>" : "") +
				(showLong
					? '<div class="ff-product-modal__description"><h3 class="ff-product-modal__section-title">Beschreibung</h3>' +
					  longDesc +
					  "</div>"
					: "") +
				facts +
				optionsNote +
				"</div>" +
				'<div class="ff-product-modal__actions">' +
				(personalizationHtml
					? '<div class="ff-product-modal__personalization">' + personalizationHtml + "</div>"
					: "") +
				(purchasable && !hasOptions
					? '<button type="button" class="ff-product-modal__cart" data-ff-add-id="' +
					  String(p.id) +
					  '"' +
					  (personalization.enabled ? ' data-ff-personalized="1"' : "") +
					  ">" +
					  escapeHtml(btnText) +
					  "</button>"
					: hasOptions
					? ""
					: '<p class="ff-product-modal__stock">Derzeit nicht verfügbar</p>') +
				'<a class="ff-product-modal__full" href="' +
				escapeHtml(p.permalink || "#") +
				'">' +
				(hasOptions ? "Optionen wählen →" : "Zur Produktseite →") +
				"</a>" +
				'<p class="ff-product-modal__status" id="ffProductStatus" hidden></p>' +
				"</div>" +
				"</div></div>";

			var persRoot = bodyEl.querySelector("[data-ff-personalization]");
			if (persRoot && typeof window.fairflockInitPersonalization === "function") {
				window.fairflockInitPersonalization(persRoot);
			}
		}

		function openModal() {
			layer.classList.add("is-open");
			layer.setAttribute("aria-hidden", "false");
			document.body.classList.add("is-ff-product-open");
			window.setTimeout(function () {
				if (closeBtn) closeBtn.focus();
			}, 40);
		}

		function closeModal() {
			layer.classList.remove("is-open");
			layer.setAttribute("aria-hidden", "true");
			document.body.classList.remove("is-ff-product-open");
			galleryImages = [];
			galleryIndex = 0;
			currentProduct = null;
		}

		function loadProduct(slug) {
			var seq = ++requestSeq;
			renderLoading();
			openModal();

			var url =
				productsApi +
				(productsApi.indexOf("?") === -1 ? "?" : "&") +
				"slug=" +
				encodeURIComponent(slug);

			fetch(url, {
				credentials: "same-origin",
				headers: { Accept: "application/json" }
			})
				.then(function (res) {
					if (!res.ok) throw new Error("load failed");
					return res.json();
				})
				.then(function (data) {
					if (seq !== requestSeq) return;
					var list = Array.isArray(data) ? data : data ? [data] : [];
					if (!list.length) {
						renderError("Produkt nicht gefunden.");
						return;
					}
					renderProduct(list[0]);
				})
				.catch(function () {
					if (seq !== requestSeq) return;
					renderError("Produkt konnte nicht geladen werden.");
				});
		}

		function setStatus(msg, isError) {
			var el = document.getElementById("ffProductStatus");
			if (!el) return;
			el.hidden = !msg;
			el.textContent = msg || "";
			el.classList.toggle("is-error", !!isError);
		}

		function finishAddSuccess() {
			setStatus("Zum Warenkorb hinzugefügt.");
			document.body.dispatchEvent(new CustomEvent("wc-blocks_added_to_cart"));
			var cartOpen =
				document.querySelector(".ff-cart .wp-block-button__link") ||
				document.querySelector(".ff-cart a");
			closeModal();
			if (cartOpen) {
				window.setTimeout(function () {
					cartOpen.click();
				}, 180);
			}
		}

		function validatePersonalizationClient() {
			var root = bodyEl.querySelector("[data-ff-personalization]");
			if (!root) return "";

			var both = root.getAttribute("data-ff-both");
			var mode = "upload";
			if (both) {
				var checked = root.querySelector('input[name="fairflock_personalization_mode"]:checked');
				mode = checked ? checked.value : "upload";
			} else if (root.querySelector('input[name="fairflock_personal_text"]') && !root.querySelector('input[name="fairflock_logo_upload"]')) {
				mode = "text";
			}

			if (mode === "upload") {
				var file = root.querySelector('input[name="fairflock_logo_upload"]');
				if (!file || !file.files || !file.files.length) {
					return root.getAttribute("data-ff-msg-upload") || "Bitte Datei hochladen.";
				}
			} else {
				var text = root.querySelector('input[name="fairflock_personal_text"]');
				if (!text || !String(text.value || "").trim()) {
					return root.getAttribute("data-ff-msg-text") || "Bitte Text eingeben.";
				}
			}
			return "";
		}

		function addPersonalizedToCart(productId, btn) {
			var err = validatePersonalizationClient();
			if (err) {
				setStatus(err, true);
				return;
			}

			var root = bodyEl.querySelector("[data-ff-personalization]");
			var fd = new FormData();
			fd.append("id", String(productId));
			fd.append("quantity", "1");

			if (root) {
				var modeInput = root.querySelector('input[name="fairflock_personalization_mode"]:checked');
				if (modeInput) {
					fd.append("fairflock_personalization_mode", modeInput.value);
				}
				var textInput = root.querySelector('input[name="fairflock_personal_text"]');
				if (textInput && !textInput.disabled) {
					fd.append("fairflock_personal_text", textInput.value || "");
				}
				var fileInput = root.querySelector('input[name="fairflock_logo_upload"]');
				if (fileInput && !fileInput.disabled && fileInput.files && fileInput.files[0]) {
					fd.append("fairflock_logo_upload", fileInput.files[0]);
				}
			}

			if (btn) {
				btn.disabled = true;
				btn.setAttribute("aria-busy", "true");
			}
			setStatus("");

			fetch(personalizedAddApi, {
				method: "POST",
				credentials: "same-origin",
				headers: {
					Accept: "application/json",
					"X-WP-Nonce": window.fairflockRestNonce || ""
				},
				body: fd
			})
				.then(function (res) {
					return res.json().then(function (data) {
						return { ok: res.ok, data: data };
					});
				})
				.then(function (result) {
					if (!result.ok) {
						var msg =
							(result.data && (result.data.message || (result.data.data && result.data.data.message))) ||
							"Leider nicht hinzugefügt. Bitte erneut versuchen.";
						throw new Error(msg);
					}
					finishAddSuccess();
				})
				.catch(function (e) {
					setStatus(
						(e && e.message) || "Leider nicht hinzugefügt. Bitte erneut versuchen.",
						true
					);
				})
				.finally(function () {
					if (btn) {
						btn.disabled = false;
						btn.removeAttribute("aria-busy");
					}
				});
		}

		function addToCart(productId, btn) {
			setStatus("");
			if (btn && btn.getAttribute("data-ff-personalized") === "1") {
				addPersonalizedToCart(productId, btn);
				return;
			}

			if (btn) {
				btn.disabled = true;
				btn.setAttribute("aria-busy", "true");
			}

			ensureCartAuth()
				.then(function () {
					var headers = {
						Accept: "application/json",
						"Content-Type": "application/json"
					};
					if (storeNonce) headers.Nonce = storeNonce;
					if (cartToken) headers["Cart-Token"] = cartToken;

					return fetch(addItemApi, {
						method: "POST",
						credentials: "same-origin",
						headers: headers,
						body: JSON.stringify({ id: Number(productId), quantity: 1 })
					});
				})
				.then(function (res) {
					var n = res.headers.get("Nonce") || res.headers.get("nonce");
					var t = res.headers.get("Cart-Token") || res.headers.get("cart-token");
					if (n) storeNonce = n;
					if (t) cartToken = t;
					if (!res.ok) throw new Error("add failed");
					return res.json();
				})
				.then(function () {
					finishAddSuccess();
				})
				.catch(function () {
					setStatus("Leider nicht hinzugefügt. Bitte erneut versuchen.", true);
				})
				.finally(function () {
					if (btn) {
						btn.disabled = false;
						btn.removeAttribute("aria-busy");
					}
				});
		}

		document.addEventListener(
			"click",
			function (e) {
				var galleryBtn = e.target.closest && e.target.closest("[data-ff-gallery]");
				if (galleryBtn && layer.contains(galleryBtn)) {
					e.preventDefault();
					var dir = galleryBtn.getAttribute("data-ff-gallery");
					setGalleryIndex(galleryIndex + (dir === "next" ? 1 : -1));
					return;
				}

				var thumb = e.target.closest && e.target.closest("[data-ff-thumb]");
				if (thumb && layer.contains(thumb)) {
					e.preventDefault();
					setGalleryIndex(Number(thumb.getAttribute("data-ff-thumb")));
					return;
				}

				var addBtn = e.target.closest && e.target.closest("[data-ff-add-id]");
				if (addBtn && layer.contains(addBtn)) {
					e.preventDefault();
					addToCart(addBtn.getAttribute("data-ff-add-id"), addBtn);
					return;
				}

				var a = e.target.closest && e.target.closest("a");
				if (!isProductLink(a)) return;
				if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

				var slug = productSlugFromUrl(a.href);
				if (!slug) return;

				e.preventDefault();
				loadProduct(slug);
			},
			true
		);

		if (closeBtn) closeBtn.addEventListener("click", closeModal);
		if (backdrop) backdrop.addEventListener("click", closeModal);
		document.addEventListener("keydown", function (e) {
			if (!layer.classList.contains("is-open")) return;
			if (e.key === "Escape") {
				closeModal();
				return;
			}
			if (galleryImages.length > 1 && (e.key === "ArrowLeft" || e.key === "ArrowRight")) {
				e.preventDefault();
				setGalleryIndex(galleryIndex + (e.key === "ArrowRight" ? 1 : -1));
			}
		});

		ensureCartAuth().catch(function () {
			/* optional preload */
		});
	});
})();
