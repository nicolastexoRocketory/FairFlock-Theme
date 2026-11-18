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
		var selectedOptions = {};

		function variationAttrs(p) {
			if (!p || !Array.isArray(p.attributes)) return [];
			return p.attributes.filter(function (attr) {
				return attr && attr.has_variations && Array.isArray(attr.terms) && attr.terms.length;
			});
		}

		function sameOption(a, b) {
			return String(a).toLowerCase() === String(b).toLowerCase();
		}

		function variationValue(variation, attrName) {
			var row = (variation.attributes || []).find(function (item) {
				return item && item.name === attrName;
			});
			return row && row.value != null ? String(row.value) : "";
		}

		function termIsOffered(p, attr, term) {
			return (p.variations || []).some(function (variation) {
				var value = variationValue(variation, attr.name);
				return !value || sameOption(value, term.slug || "") || sameOption(value, term.name || "");
			});
		}

		function renderSizePicker(p) {
			var attrs = variationAttrs(p);
			if (!attrs.length || !Array.isArray(p.variations) || !p.variations.length) return "";
			selectedOptions = {};
			return attrs
				.map(function (attr, index) {
					var labelId = "ffSizeLabel" + index;
					var selectId = "ffSizeSelect" + index;
					var terms = attr.terms.filter(function (term) {
						return termIsOffered(p, attr, term);
					});
					var options = terms
						.map(function (term) {
							var label = term.name || term.slug || "";
							var value = term.slug || label;
							if (!label) return "";
							if (term.default || terms.length === 1) {
								selectedOptions[attr.name] = value;
							}
							var on = selectedOptions[attr.name] === value;
							return (
								'<option value="' +
								escapeHtml(value) +
								'"' +
								(on ? " selected" : "") +
								">" +
								escapeHtml(label) +
								"</option>"
							);
						})
						.filter(Boolean)
						.join("");
					if (!options) return "";
					var placeholder = selectedOptions[attr.name]
						? ""
						: '<option value="">Bitte wählen</option>';
					return (
						'<div class="ff-product-modal__sizes">' +
						'<label class="ff-product-modal__sizes-label" id="' +
						labelId +
						'" for="' +
						selectId +
						'">' +
						escapeHtml(attr.name) +
						"</label>" +
						'<select class="ff-product-modal__size-select" id="' +
						selectId +
						'" data-ff-size-attr="' +
						escapeHtml(attr.name) +
						'" aria-labelledby="' +
						labelId +
						'">' +
						placeholder +
						options +
						"</select></div>"
					);
				})
				.filter(Boolean)
				.join("");
		}

		function selectedVariationId(p) {
			var attrs = variationAttrs(p);
			if (!attrs.length) return 0;
			var missing = attrs.some(function (attr) {
				return !selectedOptions[attr.name];
			});
			if (missing) return 0;
			var found = (p.variations || []).find(function (variation) {
				return attrs.every(function (attr) {
					var value = variationValue(variation, attr.name);
					return !value || sameOption(value, selectedOptions[attr.name]);
				});
			});
			return found ? Number(found.id) : 0;
		}

		function selectedVariationData(p) {
			return variationAttrs(p)
				.filter(function (attr) {
					return !!selectedOptions[attr.name];
				})
				.map(function (attr) {
					return { attribute: attr.name, value: selectedOptions[attr.name] };
				});
		}

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
			/* Warenkorb-/Such-UI: echte Navigation, kein Modal */
			if (
				a.closest(".ff-product-layer") ||
				a.closest(".ff-cart-layer") ||
				a.closest(".ff-cart-drawer") ||
				a.closest(".ff-search-layer") ||
				a.classList.contains("ff-cart-line__title") ||
				a.classList.contains("ff-cart-line__thumb") ||
				a.hasAttribute("data-ff-cart-product")
			) {
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

		var galleryChevron =
			'<svg class="ff-gal-chevron" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M8.2 2.1 17.8 12 8.2 21.9 5.6 19.4 12.8 12 5.6 4.6z"/></svg>';

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
					  '<button type="button" class="ff-product-modal__gallery-btn is-prev" data-ff-gallery="prev" aria-label="Vorheriges Bild">' +
					  galleryChevron +
					  "</button>" +
					  '<button type="button" class="ff-product-modal__gallery-btn is-next" data-ff-gallery="next" aria-label="Nächstes Bild">' +
					  galleryChevron +
					  "</button>" +
					  "</div>"
					: "";

			return (
				'<div class="ff-product-modal__media" data-ff-gallery-root style="--ff-gal-n:' +
				images.length +
				";--ff-gal-peek:" +
				(images.length > 1 ? "1.5" : "1") +
				'">' +
				'<div class="ff-product-modal__stage">' +
				'<div class="ff-product-modal__slides" data-ff-gallery-track>' +
				slides +
				"</div>" +
				nav +
				"</div>" +
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

			var track = bodyEl.querySelector("[data-ff-gallery-track]");
			if (track) {
				track.style.transform = "translate3d(" + -galleryIndex * (100 / total) + "%,0,0)";
			}
		}

		function bindGallerySwipe() {
			var track = bodyEl.querySelector("[data-ff-gallery-track]");
			if (!track || track.dataset.ffSwipeBound === "1") return;
			track.dataset.ffSwipeBound = "1";
			var startX = 0;
			var deltaX = 0;
			track.addEventListener(
				"touchstart",
				function (e) {
					if (!e.touches || !e.touches.length) return;
					startX = e.touches[0].clientX;
					deltaX = 0;
				},
				{ passive: true }
			);
			track.addEventListener(
				"touchmove",
				function (e) {
					if (!e.touches || !e.touches.length) return;
					deltaX = e.touches[0].clientX - startX;
				},
				{ passive: true }
			);
			track.addEventListener("touchend", function () {
				if (Math.abs(deltaX) < 40) return;
				setGalleryIndex(galleryIndex + (deltaX < 0 ? 1 : -1));
				deltaX = 0;
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
					if (!name || !terms || attr.has_variations) return "";
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

		function renderTaxNote() {
			return '<p class="ff-product-buy__tax">zzgl. Versandkosten | inkl. MwSt</p>';
		}

		function renderPriceBlock(p) {
			var prices = p.prices || {};
			var current = formatPrice(prices, "price");
			var priceInner = "";
			if (!current && p.price_html) {
				priceInner = '<div class="ff-product-modal__price">' + p.price_html + "</div>";
			} else if (current) {
				var regular = formatPrice(prices, "regular_price");
				var onSale =
					p.on_sale ||
					(regular &&
						prices.regular_price &&
						prices.price &&
						prices.regular_price !== prices.price);

				if (onSale && regular && regular !== current) {
					priceInner =
						'<p class="ff-product-modal__price">' +
						'<span class="ff-product-modal__price-sale">' +
						escapeHtml(current) +
						"</span> " +
						'<s class="ff-product-modal__price-regular">' +
						escapeHtml(regular) +
						"</s>" +
						"</p>";
				} else {
					priceInner = '<p class="ff-product-modal__price">' + escapeHtml(current) + "</p>";
				}
			}
			if (!priceInner) return "";
			return (
				'<div class="ff-product-buy__price-row">' + priceInner + renderTaxNote() + "</div>"
			);
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
				'<span class="ff-product-buy__fact ff-product-modal__fact' +
				cls +
				'">' +
				'<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M3.5 8 12 4l8.5 4v8L12 20l-8.5-4V8z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><path d="M12 12.2 20.5 8M12 12.2V20M12 12.2 3.5 8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/></svg>' +
				escapeHtml(text) +
				"</span>"
			);
		}

		function plainText(html) {
			return String(html || "")
				.replace(/<[^>]+>/g, " ")
				.replace(/&nbsp;/g, " ")
				.replace(/\s+/g, " ")
				.trim();
		}

		function deliveryLabel(p) {
			var shortText = plainText(p.short_description);
			var raw = (shortText + " " + plainText(p.description)).trim();
			var match = raw.match(/Lieferzeit\s*:?\s*([0-9]+\s*[–\-]\s*[0-9]+|[0-9]+)/i);
			if (match) {
				var span = match[1].replace(/\s+/g, "").replace(/-/g, "–");
				return "Lieferung in " + span + " Tagen";
			}
			if (/sofort/i.test(shortText)) {
				return "Lieferung in 1–2 Tagen";
			}
			return "";
		}

		function deliveryView(p) {
			var ext = (p && p.extensions && p.extensions.fairflock) || {};
			if (typeof ext.delivery_label === "string" && ext.delivery_label) {
				return {
					label: ext.delivery_label,
					tooltip: typeof ext.delivery_tooltip === "string" ? ext.delivery_tooltip : ""
				};
			}
			var label = deliveryLabel(p);
			return label ? { label: label, tooltip: "" } : null;
		}

		function deliveryInfo(tooltip) {
			if (!tooltip) return "";
			return (
				'<span class="ff-delivery-info">' +
				'<button type="button" class="ff-delivery-info__btn" aria-label="Hinweis zur Lieferzeit"><span aria-hidden="true">i</span></button>' +
				'<span class="ff-delivery-info__tip" role="tooltip">' +
				escapeHtml(tooltip) +
				"</span></span>"
			);
		}

		function renderFacts(p) {
			var stock = renderStock(p);
			var delivery = deliveryView(p);
			if (!stock && !delivery) return "";
			var ship = delivery
				? (stock
						? '<span class="ff-product-buy__fact-rule ff-product-modal__fact-rule" aria-hidden="true"></span>'
						: "") +
				  '<span class="ff-product-buy__fact ff-product-modal__fact">' +
				  '<svg viewBox="0 0 24 24" width="18" height="16" aria-hidden="true"><path d="M2.5 7.5h11v7.5h-11z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><path d="M13.5 10.5h4.2L21 14v1.5h-7.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><circle cx="6.5" cy="17.2" r="1.5" fill="none" stroke="currentColor" stroke-width="1.75"/><circle cx="17" cy="17.2" r="1.5" fill="none" stroke="currentColor" stroke-width="1.75"/></svg>' +
				  escapeHtml(delivery.label) +
				  deliveryInfo(delivery.tooltip) +
				  "</span>"
				: "";
			return '<p class="ff-product-buy__facts ff-product-modal__facts">' + stock + ship + "</p>";
		}

		function renderProductInfos(p) {
			var rows =
				(p.sku ? renderMetaRow("Art.-Nr.", escapeHtml(p.sku)) : "") +
				renderTags(p.tags) +
				renderCategories(p.categories) +
				(p.formatted_weight ? renderMetaRow("Gewicht", escapeHtml(p.formatted_weight)) : "") +
				(p.formatted_dimensions
					? renderMetaRow("Maße", escapeHtml(p.formatted_dimensions))
					: "");
			if (!rows) return "";
			return (
				'<div class="ff-product-infos">' +
				'<span class="ff-product-infos__label">Produktinfos</span>' +
				'<button type="button" class="ff-product-infos__btn" aria-label="Produktinformationen anzeigen"><span aria-hidden="true">i</span></button>' +
				'<span class="ff-product-infos__tip" role="tooltip">' +
				'<dl class="ff-product-infos__meta">' +
				rows +
				"</dl></span></div>"
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
				markup: typeof ext.markup === "string" ? ext.markup : "",
				tableHtml: typeof ext.table_html === "string" ? ext.table_html : ""
			};
		}

		function renderProduct(p) {
			currentProduct = p;
			var images = collectImages(p);
			var shortDesc = p.short_description || "";
			var longDesc = p.description || "";
			var longPlain = plainText(longDesc);
			var shortPlain = plainText(shortDesc);
			var shortIsDistinct =
				!!shortPlain &&
				(!longPlain || shortPlain.replace(/\s+/g, "") !== longPlain.replace(/\s+/g, ""));
			var purchasable = p.is_purchasable !== false && p.is_in_stock !== false;
			var sizePicker = renderSizePicker(p);
			var hasOptions = !!p.has_options || (Array.isArray(p.variations) && p.variations.length > 0);
			var canChooseHere = !!sizePicker;
			var personalization = getPersonalization(p);
			var personalizationHtml =
				(!hasOptions || canChooseHere) && personalization.enabled && personalization.markup
					? personalization.markup
					: "";
			var startsCollapsed = personalization.enabled && !!personalizationHtml;

			var optionsNote =
				hasOptions && !canChooseHere
					? '<p class="ff-product-modal__note">Varianten und Optionen wählst du auf der Produktseite.</p>'
					: "";

			var descriptionHtml = "";
			if (longPlain) {
				descriptionHtml =
					'<div class="ff-product-modal__description ff-product-buy__description">' +
					longDesc +
					"</div>";
			}

			/* Kurzbeschreibung immer als Akkordeon, standardmäßig eingeklappt */
			var excerptHtml = shortIsDistinct
				? '<details class="ff-product-excerpt-acc ff-product-modal__excerpt ff-product-buy__excerpt">' +
				  '<summary class="ff-product-excerpt-acc__summary">' +
				  '<span class="ff-product-excerpt-acc__title">Weitere Informationen</span>' +
				  '<span class="ff-product-excerpt-acc__icon" aria-hidden="true"></span>' +
				  "</summary>" +
				  '<div class="ff-product-excerpt-acc__clip">' +
				  '<div class="ff-product-excerpt-acc__clip-inner">' +
				  '<div class="ff-product-excerpt-acc__body">' +
				  shortDesc +
				  "</div></div></div>" +
				  "</details>"
				: "";

			var tableHtml = personalization.tableHtml || "";
			var accStack =
				tableHtml || excerptHtml
					? '<div class="ff-product-acc-stack">' + tableHtml + excerptHtml + "</div>"
					: "";
			var productInfosHtml = renderProductInfos(p);
			var attrsHtml = renderAttributes(p.attributes);

			var headHtml =
				'<div class="ff-product-modal__head">' +
				'<h2 id="ffProductTitle" class="ff-product-modal__title">' +
				safeText(p.name || "") +
				"</h2>" +
				renderPriceBlock(p) +
				renderFacts(p) +
				"</div>";

			bodyEl.innerHTML =
				'<div class="ff-product-modal__grid">' +
				renderGallery(images) +
				'<div class="ff-product-modal__copy">' +
				'<div class="ff-product-modal__scroll">' +
				headHtml +
				descriptionHtml +
				accStack +
				attrsHtml +
				optionsNote +
				productInfosHtml +
				"</div>" +
				'<div class="ff-product-modal__actions' +
				(startsCollapsed ? " is-collapsed" : "") +
				'">' +
				(startsCollapsed
					? '<button type="button" class="ff-product-modal__collapse" data-ff-collapse aria-expanded="false" aria-label="Konfiguration einklappen">' +
					  '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
					  "</button>"
					: "") +
				'<div class="ff-product-modal__config">' +
				'<div class="ff-product-modal__config-clip">' +
				sizePicker +
				(personalizationHtml
					? '<div class="ff-product-modal__personalization">' +
					  '<p class="ff-product-modal__sizes-label">Dein Design</p>' +
					  personalizationHtml +
					  "</div>"
					: "") +
				(!purchasable && !hasOptions
					? '<p class="ff-product-modal__stock">Derzeit nicht verfügbar</p>'
					: "") +
				'<div class="ff-product-modal__buttons">' +
				(purchasable && (!hasOptions || canChooseHere)
					? '<button type="button" class="ff-product-modal__cart" data-ff-add-id="' +
					  String(p.id) +
					  '"' +
					  (personalization.enabled ? ' data-ff-personalized="1"' : "") +
					  ">Hinzufügen</button>"
					: "") +
				'<a class="ff-product-modal__full" href="' +
				escapeHtml(p.permalink || "#") +
				'">Zur Seite</a>' +
				"</div>" +
				'<p class="ff-product-modal__status" id="ffProductStatus" hidden></p>' +
				"</div></div>" +
				(startsCollapsed
					? '<button type="button" class="ff-product-modal__configure" data-ff-configure>Konfigurieren</button>'
					: "") +
				"</div>" +
				"</div></div>";

			setGalleryIndex(0);
			bindGallerySwipe();

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
			selectedOptions = {};
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
			closeModal();
			window.setTimeout(function () {
				if (typeof window.fairflockOnAddedToCart === "function") {
					window.fairflockOnAddedToCart();
					return;
				}
				document.body.dispatchEvent(new CustomEvent("wc-blocks_added_to_cart"));
				if (typeof window.fairflockOpenCart === "function") {
					window.fairflockOpenCart();
				}
			}, 180);
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
			if (currentProduct && variationAttrs(currentProduct).length) {
				var variationId = selectedVariationId(currentProduct);
				if (!variationId) {
					setStatus(missingSizeMessage(currentProduct), true);
					return;
				}
				fd.append("variation_id", String(variationId));
			}

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
					var msg = (e && e.message) || "";
					msg = msg.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
					if (!msg || /kritischer Fehler/i.test(msg)) {
						msg = "Leider nicht hinzugefügt. Bitte erneut versuchen.";
					}
					setStatus(msg, true);
				})
				.finally(function () {
					if (btn) {
						btn.disabled = false;
						btn.removeAttribute("aria-busy");
					}
				});
		}

		function missingSizeMessage(p) {
			var names = variationAttrs(p)
				.filter(function (attr) {
					return !selectedOptions[attr.name];
				})
				.map(function (attr) {
					return attr.name;
				});
			if (!names.length) return "Diese Kombination ist nicht verfügbar.";
			return "Bitte " + names.join(" und ") + " wählen.";
		}

		function addToCart(productId, btn) {
			setStatus("");
			var cartId = Number(productId);
			var variationData = [];
			if (currentProduct && variationAttrs(currentProduct).length) {
				cartId = selectedVariationId(currentProduct);
				variationData = selectedVariationData(currentProduct);
				if (!cartId) {
					setStatus(missingSizeMessage(currentProduct), true);
					return;
				}
			}

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
						body: JSON.stringify(
							variationData.length
								? { id: cartId, quantity: 1, variation: variationData }
								: { id: cartId, quantity: 1 }
						)
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

		document.addEventListener("change", function (e) {
			var sizeSelect = e.target.closest && e.target.closest("select[data-ff-size-attr]");
			if (!sizeSelect || !layer.contains(sizeSelect)) return;
			var attrName = sizeSelect.getAttribute("data-ff-size-attr");
			if (!attrName) return;
			if (sizeSelect.value) selectedOptions[attrName] = sizeSelect.value;
			else delete selectedOptions[attrName];
			setStatus("");
		});

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

				var configureBtn = e.target.closest && e.target.closest("[data-ff-configure]");
				if (configureBtn && layer.contains(configureBtn)) {
					e.preventDefault();
					var openPanel = configureBtn.closest(".ff-product-modal__actions");
					if (openPanel) {
						openPanel.classList.remove("is-collapsed");
						var openToggle = openPanel.querySelector("[data-ff-collapse]");
						if (openToggle) openToggle.setAttribute("aria-expanded", "true");
					}
					return;
				}

				var collapseBtn = e.target.closest && e.target.closest("[data-ff-collapse]");
				if (collapseBtn && layer.contains(collapseBtn)) {
					e.preventDefault();
					var closePanel = collapseBtn.closest(".ff-product-modal__actions");
					if (closePanel) {
						closePanel.classList.add("is-collapsed");
						collapseBtn.setAttribute("aria-expanded", "false");
					}
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
