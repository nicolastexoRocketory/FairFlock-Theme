/**
 * Header mega-search — overlay, live product suggestions, Store API.
 * Mobile: Mega-Nav-Items open the mega panel as a sub-page inside the search shell.
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
    var layer = document.getElementById("ffSearchLayer") || document.querySelector(".ff-search-layer");
    var toggle =
      document.querySelector(".ff-search-toggle .wp-block-button__link") ||
      document.querySelector("a.ff-search-toggle") ||
      document.querySelector(".ff-search-toggle");
    var burger =
      document.querySelector(".ff-nav-burger .wp-block-button__link") ||
      document.querySelector("a.ff-nav-burger") ||
      document.querySelector(".ff-nav-burger");
    var closeBtn =
      document.querySelector(".ff-search-close .wp-block-button__link") ||
      document.querySelector(".ff-search-close") ||
      document.getElementById("ffSearchClose");
    var backdrop = document.getElementById("ffSearchBackdrop") || document.querySelector(".ff-search-backdrop");
    var mega = document.getElementById("ffSearchMega") || document.querySelector(".ff-search-mega");
    var hitsWrap = document.getElementById("ffSearchHits") || document.querySelector(".ff-search-hits");
    var emptyEl = document.getElementById("ffSearchEmpty") || document.querySelector(".ff-search-empty");
    var titleEl = document.getElementById("ffSearchProductsTitle");
    var footLink = document.getElementById("ffSearchFootLink") || document.querySelector(".ff-search-mega-foot a");
    var cartBtn =
      document.querySelector(".ff-cart .wp-block-button__link") ||
      document.querySelector(".ff-cart a");

    if (!layer || (!toggle && !burger) || !mega || !hitsWrap) {
      return;
    }

    if (emptyEl) {
      emptyEl.hidden = true;
    }

    // Header uses translate → containing block for position:fixed.
    if (layer.parentElement !== document.body) {
      document.body.appendChild(layer);
    }

    // Re-resolve after move (form/input live inside the layer).
    var form = layer.querySelector("form") || document.getElementById("ffSearchForm");
    var input =
      document.getElementById("ffSearchInput") ||
      (form && form.querySelector('input[type="search"], input[type="text"], input[name="s"]')) ||
      layer.querySelector("input[type='search'], input[name='s'], .ff-search-field input");
    var shell = layer.querySelector(".ff-search-shell");
    var searchBar = layer.querySelector(".ff-search-bar");

    if (!input) {
      return;
    }

    input.id = "ffSearchInput";
    input.removeAttribute("required");
    input.setAttribute("autocomplete", "off");
    input.setAttribute("autocorrect", "off");
    input.setAttribute("autocapitalize", "off");
    input.setAttribute("spellcheck", "false");

    if (cartBtn && !cartBtn.querySelector(".ff-cart__count")) {
      var badge = document.createElement("b");
      badge.className = "ff-cart__count";
      badge.id = "ffCartCount";
      badge.textContent = "0";
      cartBtn.appendChild(badge);
    }

    if (toggle) {
      toggle.setAttribute("aria-controls", "ffSearchLayer");
      toggle.setAttribute("aria-expanded", "false");
      if (!toggle.getAttribute("aria-label")) {
        toggle.setAttribute("aria-label", "Suche öffnen");
      }
    }
    if (burger) {
      burger.setAttribute("aria-controls", "ffSearchLayer");
      burger.setAttribute("aria-expanded", "false");
      if (!burger.getAttribute("aria-label")) {
        burger.setAttribute("aria-label", "Menü öffnen");
      }
    }

    var catalog = Array.isArray(window.fairflockSearchCatalog) ? window.fairflockSearchCatalog : [];
    var defaults = catalog.slice(0, 4);
    var home = window.fairflockSearchHome || "/";
    var shop = window.fairflockSearchShop || home;
    var api = window.fairflockSearchApi || "";
    var debounceMs = 220;
    var debounceTimer = null;
    var activeController = null;
    var requestSeq = 0;
    var megaPage = null;
    var megaBackBtn = null;
    var megaTitleEl = null;

    function productSearchUrl(q) {
      var params = new URLSearchParams();
      params.set("post_type", "product");
      if (q) params.set("s", q);
      return home.replace(/\/?$/, "/") + "?" + params.toString();
    }

    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, function (c) {
        return ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c];
      });
    }

    function stripTags(s) {
      return String(s || "")
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/gi, " ")
        .replace(/&euro;/gi, "\u20ac")
        .replace(/&#8364;/g, "\u20ac")
        .replace(/&amp;/g, "&")
        .trim();
    }

    function formatStorePrice(item) {
      if (!item || !item.prices) return "";
      var p = item.prices;
      var minor = typeof p.currency_minor_unit === "number" ? p.currency_minor_unit : 2;
      var raw = Number(p.price);
      if (!isFinite(raw)) return "";
      var value = raw / Math.pow(10, minor);
      var symbol = p.currency_symbol || "€";
      var formatted = value.toLocaleString("de-DE", {
        minimumFractionDigits: minor,
        maximumFractionDigits: minor
      });
      var prefix = p.currency_prefix || "";
      var suffix = p.currency_suffix || "";
      if (prefix || suffix) {
        return String(prefix) + formatted + String(suffix);
      }
      return formatted + "\u00a0" + symbol;
    }

    function mapStoreProduct(item) {
      var img = "";
      if (item.images && item.images.length && item.images[0]) {
        img = item.images[0].thumbnail || item.images[0].src || "";
      }
      return {
        name: item.name || "",
        price: formatStorePrice(item),
        url: item.permalink || "",
        img: img,
        q: item.name || ""
      };
    }

    function renderHits(items) {
      hitsWrap.innerHTML = (items || [])
        .map(function (item) {
          var href = item.url || productSearchUrl(item.q || item.name);
          var thumbStyle = item.img
            ? ' style="background-image:url(\'' + String(item.img).replace(/'/g, "%27") + '\')"'
            : "";
          return (
            '<a class="ff-search-hit" href="' +
            href +
            '">' +
            '<span class="ff-search-hit-thumb"' +
            thumbStyle +
            "></span>" +
            '<span class="ff-search-hit-meta"><strong>' +
            escapeHtml(item.name) +
            '</strong><span class="ff-search-hit-price">' +
            escapeHtml(stripTags(item.price || "")) +
            "</span></span></a>"
          );
        })
        .join("");
      if (emptyEl) {
        emptyEl.hidden = !!(items && items.length);
        emptyEl.setAttribute("aria-hidden", items && items.length ? "true" : "false");
      }
    }

    function filterLocalCatalog(q) {
      var needle = String(q || "").toLowerCase();
      if (!needle) return defaults;
      return catalog
        .filter(function (item) {
          var hay = (item.name + " " + (item.tags || "") + " " + (item.q || "")).toLowerCase();
          return hay.indexOf(needle) !== -1;
        })
        .slice(0, 6);
    }

    function fetchStoreProducts(q) {
      if (!api || !window.fetch) {
        renderHits(filterLocalCatalog(q));
        return;
      }

      if (activeController && typeof activeController.abort === "function") {
        activeController.abort();
      }

      var seq = ++requestSeq;
      var controller = typeof AbortController !== "undefined" ? new AbortController() : null;
      activeController = controller;

      var url =
        api + (api.indexOf("?") === -1 ? "?" : "&") + "search=" + encodeURIComponent(q) + "&per_page=6";

      fetch(url, {
        method: "GET",
        credentials: "same-origin",
        signal: controller ? controller.signal : undefined,
        headers: { Accept: "application/json" }
      })
        .then(function (res) {
          if (!res.ok) throw new Error("search failed");
          return res.json();
        })
        .then(function (data) {
          if (seq !== requestSeq) return;
          var list = Array.isArray(data) ? data : [];
          if (!list.length) {
            renderHits(filterLocalCatalog(q));
            return;
          }
          renderHits(list.map(mapStoreProduct));
        })
        .catch(function (err) {
          if (err && err.name === "AbortError") return;
          if (seq !== requestSeq) return;
          renderHits(filterLocalCatalog(q));
        });
    }

    function sourceMegaPanel() {
      return (
        document.querySelector(".ff-mega-panel-slot .ff-mega-panel") ||
        document.querySelector(".ff-mega-panel")
      );
    }

    function ensureMegaPageUi() {
      if (!shell) {
        return null;
      }

      if (!megaPage) {
        megaPage = document.createElement("div");
        megaPage.className = "ff-search-mega-page";
        megaPage.hidden = true;
        megaPage.innerHTML =
          '<div class="ff-search-mega-page__body" id="ffSearchMegaPageBody"></div>';
        shell.appendChild(megaPage);
      }

      if (searchBar && !megaBackBtn) {
        megaBackBtn = document.createElement("button");
        megaBackBtn.type = "button";
        megaBackBtn.className = "ff-search-mega-back";
        megaBackBtn.setAttribute("aria-label", "Zurück");
        megaBackBtn.innerHTML = "<span aria-hidden=\"true\">←</span>";
        megaBackBtn.addEventListener("click", function (e) {
          e.preventDefault();
          closeMegaPage();
        });
        searchBar.insertBefore(megaBackBtn, searchBar.firstChild);

        megaTitleEl = document.createElement("p");
        megaTitleEl.className = "ff-search-mega-page-title";
        megaTitleEl.setAttribute("aria-live", "polite");
        searchBar.insertBefore(megaTitleEl, megaBackBtn.nextSibling);
      }

      return megaPage;
    }

    function isMegaPageOpen() {
      return !!(shell && shell.classList.contains("is-mega-page"));
    }

    function closeMegaPage() {
      if (!shell) {
        return;
      }
      shell.classList.remove("is-mega-page");
      if (megaPage) {
        megaPage.hidden = true;
        var body = megaPage.querySelector(".ff-search-mega-page__body");
        if (body) {
          body.innerHTML = "";
        }
      }
      if (megaTitleEl) {
        megaTitleEl.textContent = "";
      }
      if (mega) {
        mega.hidden = false;
      }
    }

    function openMegaPage(label) {
      var panel = sourceMegaPanel();
      var page = ensureMegaPageUi();
      if (!page || !panel || !shell) {
        return;
      }

      var body = page.querySelector(".ff-search-mega-page__body");
      if (!body) {
        return;
      }

      var clone = panel.cloneNode(true);
      clone.querySelectorAll("[id]").forEach(function (el) {
        el.removeAttribute("id");
      });
      body.innerHTML = "";
      body.appendChild(clone);

      if (megaTitleEl) {
        megaTitleEl.textContent = label || "Menü";
      }

      page.hidden = false;
      if (mega) {
        mega.hidden = true;
      }
      shell.classList.add("is-mega-page");
      page.scrollTop = 0;
      if (megaBackBtn) {
        megaBackBtn.focus({ preventScroll: true });
      }
    }

    function accountMenuHref() {
      var fromIcon =
        document.querySelector(".ff-account a[href], .ff-header__icons .wp-block-woocommerce-customer-account a[href]");
      if (fromIcon && fromIcon.getAttribute("href")) {
        return fromIcon.getAttribute("href");
      }
      return home.replace(/\/?$/, "/") + "my-account/";
    }

    function syncSearchNav() {
      var source = document.querySelector(
        ".ff-header__nav .wp-block-navigation__container, .ff-header__nav .wp-block-navigation__responsive-container-content > ul"
      );
      var target = mega.querySelector(".ff-search-mega-menu .ff-search-menu-nav");
      if (!source || !target) {
        return;
      }
      var items = source.querySelectorAll(":scope > .wp-block-navigation-item");
      if (!items.length) {
        return;
      }
      var html = "";
      Array.prototype.forEach.call(items, function (item) {
        var link = item.querySelector(":scope > a.wp-block-navigation-item__content, :scope > a");
        if (!link) {
          return;
        }
        var href = link.getAttribute("href") || "#";
        var label = (link.textContent || "").replace(/\s+/g, " ").trim();
        if (!label) {
          return;
        }
        var isMega = item.classList.contains("is-style-ff-mega");
        html +=
          "<li" +
          (isMega ? ' class="has-ff-mega"' : "") +
          "><a href=\"" +
          href.replace(/"/g, "&quot;") +
          "\"" +
          (isMega ? ' data-ff-search-mega="1" class="ff-search-menu-mega"' : "") +
          ">" +
          escapeHtml(label) +
          (isMega ? '<span class="ff-search-menu-mega__chev" aria-hidden="true">›</span>' : "") +
          "</a></li>";
      });

      html +=
        '<li class="ff-search-menu-account"><a href="' +
        escapeHtml(accountMenuHref()).replace(/"/g, "&quot;") +
        '" data-ff-search-account="1"><span class="ff-search-menu-account__icon" aria-hidden="true"></span>Account</a></li>';
      html +=
        '<li class="ff-search-menu-cart"><a href="#ff-open-cart" data-ff-search-cart="1"><span class="ff-search-menu-cart__icon" aria-hidden="true"></span>Warenkorb</a></li>';

      if (html) {
        target.innerHTML = html;
      }
    }

    function wireShopLinks() {
      if (!shop) return;
      var links = mega.querySelectorAll(".ff-search-menu-nav a, .ff-search-mega-foot a");
      Array.prototype.forEach.call(links, function (a) {
        var label = (a.textContent || "").trim().toLowerCase();
        if (label.indexOf("alle produkte") !== -1 || a.id === "ffSearchFootLink") {
          if (!(input.value || "").trim()) {
            a.setAttribute("href", shop);
          }
        }
      });
    }

    function goToResults() {
      var q = (input.value || "").trim();
      if (q) {
        window.location.href = productSearchUrl(q);
      }
    }

    function syncMega() {
      var raw = (input.value || "").trim();
      var querying = raw.length > 0;
      mega.classList.toggle("is-querying", querying);
      if (titleEl) titleEl.textContent = querying ? "Suchergebnisse" : "Vorschläge";
      if (footLink) {
        footLink.href = querying ? productSearchUrl(raw) : shop;
        footLink.textContent = querying ? "Alle Ergebnisse anzeigen →" : "Alle Produkte anzeigen →";
      }
      wireShopLinks();

      if (!querying) {
        if (debounceTimer) window.clearTimeout(debounceTimer);
        if (activeController && typeof activeController.abort === "function") {
          activeController.abort();
        }
        renderHits(defaults);
        return;
      }

      // Instant local feedback, then refine via Store API.
      renderHits(filterLocalCatalog(raw));

      if (debounceTimer) window.clearTimeout(debounceTimer);
      debounceTimer = window.setTimeout(function () {
        fetchStoreProducts(raw);
      }, debounceMs);
    }

    function setSearchTriggersExpanded(open) {
      if (toggle) {
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
      }
      if (burger) {
        burger.setAttribute("aria-expanded", open ? "true" : "false");
      }
    }

    function openSearch(opts) {
      var focusInput = !opts || opts.focusInput !== false;
      layer.classList.add("is-open");
      layer.setAttribute("aria-hidden", "false");
      document.body.classList.add("is-ff-search-open");
      setSearchTriggersExpanded(true);
      closeMegaPage();
      ensureMegaPageUi();
      syncSearchNav();
      syncMega();
      if (focusInput) {
        window.setTimeout(function () {
          input.focus({ preventScroll: true });
        }, 40);
      }
    }

    function closeSearch() {
      closeMegaPage();
      layer.classList.remove("is-open");
      layer.setAttribute("aria-hidden", "true");
      document.body.classList.remove("is-ff-search-open");
      setSearchTriggersExpanded(false);
    }

    if (toggle) {
      toggle.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        openSearch({ focusInput: true });
      });
    }

    if (burger) {
      burger.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        openSearch({ focusInput: false });
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener("click", function (e) {
        e.preventDefault();
        closeSearch();
        var focusTarget = burger || toggle;
        if (toggle && toggle.offsetParent !== null) {
          focusTarget = toggle;
        } else if (burger) {
          focusTarget = burger;
        }
        if (focusTarget && typeof focusTarget.focus === "function") {
          focusTarget.focus();
        }
      });
    }
    if (backdrop) {
      backdrop.addEventListener("click", closeSearch);
    } else {
      layer.addEventListener("click", function (e) {
        if (e.target === layer) closeSearch();
      });
    }

    mega.addEventListener("click", function (e) {
      var cartLink = e.target.closest("a[data-ff-search-cart]");
      if (cartLink && mega.contains(cartLink)) {
        e.preventDefault();
        e.stopPropagation();
        closeSearch();
        if (typeof window.fairflockOpenCart === "function") {
          window.fairflockOpenCart();
        }
        return;
      }

      var link = e.target.closest("a[data-ff-search-mega]");
      if (!link || !mega.contains(link)) {
        return;
      }
      e.preventDefault();
      e.stopPropagation();
      var label = (link.childNodes[0] && link.childNodes[0].textContent
        ? link.childNodes[0].textContent
        : link.textContent || ""
      )
        .replace(/\s+/g, " ")
        .replace(/›/g, "")
        .trim();
      openMegaPage(label);
    });

    var inlineInput = document.querySelector(".ff-header-inline-search__input");
    var inlineForm = document.querySelector("form.ff-header-inline-search");

    if (inlineInput) {
      inlineInput.addEventListener("focus", function () {
        input.value = inlineInput.value || "";
        openSearch({ focusInput: true });
      });
      inlineInput.addEventListener("click", function () {
        input.value = inlineInput.value || "";
        openSearch({ focusInput: true });
      });
    }

    if (inlineForm) {
      inlineForm.addEventListener("submit", function (e) {
        e.preventDefault();
        var q = ((inlineInput && inlineInput.value) || "").trim();
        if (q) {
          window.location.href = productSearchUrl(q);
        } else {
          openSearch({ focusInput: true });
        }
      });
    }

    function syncInlineFromOverlay() {
      if (inlineInput) {
        inlineInput.value = input.value || "";
      }
    }

    input.addEventListener("input", function () {
      syncInlineFromOverlay();
      syncMega();
    });
    input.addEventListener("keyup", syncMega);
    input.addEventListener("search", syncMega);
    input.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        goToResults();
      }
    });

    if (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        goToResults();
      });
    }

    document.addEventListener("keydown", function (e) {
      if (e.key !== "Escape" || !layer.classList.contains("is-open")) {
        return;
      }
      if (isMegaPageOpen()) {
        e.preventDefault();
        closeMegaPage();
        return;
      }
      closeSearch();
      var focusTarget = burger || toggle;
      if (toggle && toggle.offsetParent !== null) {
        focusTarget = toggle;
      } else if (burger) {
        focusTarget = burger;
      }
      if (focusTarget && typeof focusTarget.focus === "function") {
        focusTarget.focus();
      }
    });

    ensureMegaPageUi();
    syncSearchNav();
    wireShopLinks();
    syncMega();
  });
})();
