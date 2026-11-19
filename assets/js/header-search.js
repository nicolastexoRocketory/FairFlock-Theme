/**
 * Header mega-search + mobile menu (separate layers).
 * Search: bar + Beliebt + product hits.
 * Menu: full-width under header; burger→close; submenu drill-down with „Zurück“.
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
    var menuLayer = document.getElementById("ffMenuLayer") || document.querySelector(".ff-menu-layer");
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

    var menuPanel = document.getElementById("ffMenuPanel") || (menuLayer && menuLayer.querySelector(".ff-menu-panel"));
    var menuNavList = document.getElementById("ffMenuNavList");
    var menuSubPage = document.getElementById("ffMenuSubPage");
    var menuFoot = document.getElementById("ffMenuFoot");
    var menuDrill = document.getElementById("ffMenuDrill");
    var menuBar = document.getElementById("ffMenuBar");
    var menuBackBtn = document.getElementById("ffMenuBack");
    var menuTitleEl = document.getElementById("ffMenuTitle");
    var menuSubIndex = {};
    var menuSubCloseTimer = null;

    if (layer && layer.parentElement !== document.body) {
      document.body.appendChild(layer);
    }
    if (menuLayer && menuLayer.parentElement !== document.body) {
      document.body.appendChild(menuLayer);
    }

    var form = layer && (layer.querySelector("form") || document.getElementById("ffSearchForm"));
    var input =
      (layer &&
        (document.getElementById("ffSearchInput") ||
          (form && form.querySelector('input[type="search"], input[type="text"], input[name="s"]')) ||
          layer.querySelector("input[type='search'], input[name='s'], .ff-search-field input"))) ||
      null;
    var shell = layer && layer.querySelector(".ff-search-shell");

    var hasSearch = !!(layer && mega && hitsWrap && input);
    var hasMenu = !!(menuLayer && menuNavList);

    if (!hasSearch && !hasMenu) {
      return;
    }

    if (emptyEl) {
      emptyEl.hidden = true;
    }

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
      burger.setAttribute("aria-controls", "ffMenuLayer");
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
      if (!hitsWrap) return;
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

    function renderHitsLoading() {
      if (!hitsWrap) return;
      hitsWrap.innerHTML =
        typeof window.fairflockLoading !== "undefined" && window.fairflockLoading.markup
          ? window.fairflockLoading.markup(32)
          : '<div class="ff-loading-inline" role="status" aria-busy="true"><span class="ff-spinner" style="--ff-spinner-size:32px" aria-hidden="true"></span></div>';
      if (emptyEl) {
        emptyEl.hidden = true;
        emptyEl.setAttribute("aria-hidden", "true");
      }
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

      renderHitsLoading();

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

    function accountMenuHref() {
      var fromIcon =
        document.querySelector(".ff-account a[href], .ff-header__icons .wp-block-woocommerce-customer-account a[href]");
      if (fromIcon && fromIcon.getAttribute("href")) {
        return fromIcon.getAttribute("href");
      }
      return home.replace(/\/?$/, "/") + "my-account/";
    }

    function chevronRightSvg() {
      return '<span class="ff-menu-nav__chev" aria-hidden="true"><svg class="ff-chevron ff-chevron--right" viewBox="0 0 24 24" width="14" height="14" focusable="false"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
    }

    function collectChildLinks(item) {
      var links = [];
      var seen = {};
      var submenu = item.querySelector(
        ":scope > .wp-block-navigation__submenu-container, :scope > .wp-block-navigation-submenu__container, :scope > ul"
      );
      if (submenu) {
        Array.prototype.forEach.call(submenu.querySelectorAll("a[href]"), function (a) {
          var href = a.getAttribute("href") || "";
          var label = (a.textContent || "").replace(/\s+/g, " ").trim();
          if (!href || !label || seen[href + "|" + label]) return;
          seen[href + "|" + label] = true;
          links.push({ href: href, label: label });
        });
      }
      return links;
    }

    function renderSubLinks(links) {
      return links
        .map(function (item) {
          return (
            '<a href="' +
            String(item.href).replace(/"/g, "&quot;") +
            '">' +
            escapeHtml(item.label) +
            "</a>"
          );
        })
        .join("");
    }

    function renderMegaSubPage() {
      var panel = sourceMegaPanel();
      if (!panel || !menuSubPage) return false;

      var wrap = document.createElement("div");
      wrap.className = "ff-menu-sub__mega";
      var clone = panel.cloneNode(true);
      Array.prototype.forEach.call(clone.querySelectorAll("[id]"), function (el) {
        el.removeAttribute("id");
      });
      wrap.appendChild(clone);
      menuSubPage.innerHTML = "";
      menuSubPage.appendChild(wrap);
      menuSubPage.classList.add("is-mega");
      return true;
    }

    function isMenuSubOpen() {
      return !!(menuLayer && menuLayer.classList.contains("is-sub"));
    }

    function finishCloseMenuSub() {
      if (menuSubCloseTimer) {
        window.clearTimeout(menuSubCloseTimer);
        menuSubCloseTimer = null;
      }
      if (menuLayer) {
        menuLayer.classList.remove("is-sub", "is-sub-closing");
      }
      if (menuDrill) {
        menuDrill.hidden = true;
        menuDrill.classList.remove("is-open", "is-closing");
      }
      if (menuSubPage) {
        menuSubPage.innerHTML = "";
        menuSubPage.classList.remove("is-mega");
      }
      if (menuTitleEl) {
        menuTitleEl.textContent = "";
      }
    }

    function closeMenuSub(immediate) {
      if (!menuLayer || !isMenuSubOpen()) {
        finishCloseMenuSub();
        return;
      }

      if (immediate) {
        finishCloseMenuSub();
        return;
      }

      menuLayer.classList.add("is-sub-closing");
      menuLayer.classList.remove("is-sub");
      if (menuDrill) {
        menuDrill.classList.add("is-closing");
        menuDrill.classList.remove("is-open");
      }

      if (menuSubCloseTimer) {
        window.clearTimeout(menuSubCloseTimer);
      }
      menuSubCloseTimer = window.setTimeout(finishCloseMenuSub, 320);
    }

    function openMenuSub(id, label) {
      var entry = menuSubIndex[id];
      if (!entry || !menuSubPage || !menuLayer || !menuDrill) return;

      if (menuSubCloseTimer) {
        window.clearTimeout(menuSubCloseTimer);
        menuSubCloseTimer = null;
      }
      finishCloseMenuSub();

      if (entry.mega) {
        if (!renderMegaSubPage()) return;
      } else {
        menuSubPage.classList.remove("is-mega");
        menuSubPage.innerHTML = renderSubLinks(entry.links || []);
      }

      if (menuTitleEl) {
        menuTitleEl.textContent = label || entry.label || "";
      }

      menuDrill.hidden = false;
      menuDrill.classList.remove("is-closing");
      menuLayer.classList.remove("is-sub-closing");
      menuLayer.classList.add("is-sub");
      menuSubPage.scrollTop = 0;

      /* Nächster Frame: Slide-In starten */
      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () {
          menuDrill.classList.add("is-open");
        });
      });

      if (menuBackBtn) {
        menuBackBtn.focus({ preventScroll: true });
      }
    }

    function syncMenuNav() {
      if (!menuNavList) return;
      var source = document.querySelector(
        ".ff-header__nav .wp-block-navigation__container, .ff-header__nav .wp-block-navigation__responsive-container-content > ul"
      );
      if (!source) return;

      var items = source.querySelectorAll(":scope > .wp-block-navigation-item");
      if (!items.length) return;

      menuSubIndex = {};
      var html = "";
      var drillSeq = 0;
      var hasMegaPanel = !!sourceMegaPanel();

      Array.prototype.forEach.call(items, function (item) {
        var link = item.querySelector(":scope > a.wp-block-navigation-item__content, :scope > button.wp-block-navigation-item__content, :scope > a, :scope > button");
        var labelNode = item.querySelector(":scope > .wp-block-navigation-item__content, :scope > a, :scope > button");
        var label = ((labelNode && labelNode.textContent) || "").replace(/\s+/g, " ").trim();
        if (!label) return;

        var href = (link && link.getAttribute && link.getAttribute("href")) || "#";
        var isMega = item.classList.contains("is-style-ff-mega");

        /* Mega-Item: immer das Template-Part-Panel, nie Nav-Kinder oder ein anderes Menü */
        if (isMega && hasMegaPanel) {
          var megaId = "sub-" + drillSeq++;
          menuSubIndex[megaId] = { label: label, mega: true, links: [] };
          html +=
            '<button type="button" class="ff-menu-nav__drill" data-ff-menu-sub="' +
            megaId +
            '">' +
            escapeHtml(label) +
            chevronRightSvg() +
            "</button>";
          return;
        }

        var childLinks = collectChildLinks(item);
        if (childLinks.length) {
          var id = "sub-" + drillSeq++;
          menuSubIndex[id] = { label: label, mega: false, links: childLinks };
          html +=
            '<button type="button" class="ff-menu-nav__drill" data-ff-menu-sub="' +
            id +
            '">' +
            escapeHtml(label) +
            chevronRightSvg() +
            "</button>";
          return;
        }

        html +=
          '<a href="' +
          String(href).replace(/"/g, "&quot;") +
          '">' +
          escapeHtml(label) +
          "</a>";
      });

      menuNavList.innerHTML = html;

      if (menuFoot) {
        menuFoot.innerHTML =
          '<a class="ff-menu-nav__account" href="' +
          escapeHtml(accountMenuHref()).replace(/"/g, "&quot;") +
          '" data-ff-menu-account="1"><span class="ff-menu-nav__icon ff-menu-nav__icon--account" aria-hidden="true"></span>Account</a>' +
          '<a class="ff-menu-nav__cart" href="#ff-open-cart" data-ff-menu-cart="1"><span class="ff-menu-nav__icon ff-menu-nav__icon--cart" aria-hidden="true"></span>Warenkorb</a>';
      }

      closeMenuSub(true);

      if (shop) {
        Array.prototype.forEach.call(menuNavList.querySelectorAll("a"), function (a) {
          var text = (a.textContent || "").trim().toLowerCase();
          if (text.indexOf("alle produkte") !== -1) {
            a.setAttribute("href", shop);
          }
        });
      }
    }

    function setBurgerExpanded(open) {
      if (burger) {
        burger.setAttribute("aria-expanded", open ? "true" : "false");
        burger.setAttribute("aria-label", open ? "Menü schließen" : "Menü");
      }
    }

    function setSearchExpanded(open) {
      if (toggle) {
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
      }
    }

    function isMenuOpen() {
      return !!(menuLayer && menuLayer.classList.contains("is-open"));
    }

    function closeMenu() {
      if (!menuLayer) return;
      closeMenuSub(true);
      menuLayer.classList.remove("is-open");
      menuLayer.setAttribute("aria-hidden", "true");
      document.body.classList.remove("is-ff-menu-open");
      setBurgerExpanded(false);
    }

    function openMenu() {
      if (!menuLayer) return;
      closeSearch();
      syncMenuNav();
      /* Nav-Unterkante frisch messen → Menü bündig darunter, nicht hinter der Bar */
      if (typeof window.fairflockSyncHeaderClearance === "function") {
        window.fairflockSyncHeaderClearance();
      }
      menuLayer.classList.add("is-open");
      menuLayer.setAttribute("aria-hidden", "false");
      document.body.classList.add("is-ff-menu-open");
      setBurgerExpanded(true);
    }

    function toggleMenu() {
      if (isMenuOpen()) {
        closeMenu();
      } else {
        openMenu();
      }
    }

    function wireShopLinks() {
      if (!shop || !mega) return;
      var links = mega.querySelectorAll(".ff-search-mega-foot a");
      Array.prototype.forEach.call(links, function (a) {
        if (a.id === "ffSearchFootLink" || (a.textContent || "").trim().toLowerCase().indexOf("alle produkte") !== -1) {
          if (!(input && (input.value || "").trim())) {
            a.setAttribute("href", shop);
          }
        }
      });
    }

    function goToResults() {
      if (!input) return;
      var q = (input.value || "").trim();
      if (q) {
        window.location.href = productSearchUrl(q);
      }
    }

    function syncMega() {
      if (!hasSearch) return;
      var raw = (input.value || "").trim();
      var querying = raw.length > 0;
      mega.classList.toggle("is-querying", querying);
      if (titleEl) titleEl.textContent = querying ? "Suchergebnisse" : "Vorgeschlagene Kategorie: T-Shirt's";
      if (footLink) {
        footLink.href = querying ? productSearchUrl(raw) : shop;
        footLink.textContent = querying ? "Alle Ergebnisse anzeigen" : "Alle Produkte anzeigen";
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

      renderHits(filterLocalCatalog(raw));

      if (debounceTimer) window.clearTimeout(debounceTimer);
      debounceTimer = window.setTimeout(function () {
        fetchStoreProducts(raw);
      }, debounceMs);
    }

    function openSearch(opts) {
      if (!hasSearch) return;
      var focusInput = !opts || opts.focusInput !== false;
      closeMenu();
      layer.classList.add("is-open");
      layer.setAttribute("aria-hidden", "false");
      document.body.classList.add("is-ff-search-open");
      setSearchExpanded(true);
      syncMega();
      if (focusInput) {
        window.setTimeout(function () {
          input.focus({ preventScroll: true });
        }, 40);
      }
    }

    function closeSearch() {
      if (!layer) return;
      layer.classList.remove("is-open");
      layer.setAttribute("aria-hidden", "true");
      document.body.classList.remove("is-ff-search-open");
      setSearchExpanded(false);
    }

    window.fairflockCloseMenu = closeMenu;
    window.fairflockCloseSearch = closeSearch;

    if (toggle) {
      toggle.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (layer && layer.classList.contains("is-open")) {
          closeSearch();
        } else {
          openSearch({ focusInput: true });
        }
      });
    }

    if (burger && hasMenu) {
      burger.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleMenu();
      });
    } else if (burger && hasSearch) {
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
        if (toggle && typeof toggle.focus === "function") {
          toggle.focus();
        }
      });
    }
    if (backdrop) {
      backdrop.addEventListener("click", closeSearch);
    } else if (layer) {
      layer.addEventListener("click", function (e) {
        if (e.target === layer) closeSearch();
      });
    }

    if (menuBackBtn) {
      menuBackBtn.addEventListener("click", function (e) {
        e.preventDefault();
        closeMenuSub();
      });
    }

    if (menuNavList) {
      menuNavList.addEventListener("click", function (e) {
        var drill = e.target.closest("[data-ff-menu-sub]");
        if (drill && menuNavList.contains(drill)) {
          e.preventDefault();
          e.stopPropagation();
          var id = drill.getAttribute("data-ff-menu-sub");
          var label = (drill.textContent || "").replace(/\s+/g, " ").trim();
          openMenuSub(id, label);
        }
      });
    }

    if (menuFoot) {
      menuFoot.addEventListener("click", function (e) {
        var cartLink = e.target.closest("a[data-ff-menu-cart]");
        if (cartLink && menuFoot.contains(cartLink)) {
          e.preventDefault();
          e.stopPropagation();
          closeMenu();
          if (typeof window.fairflockOpenCart === "function") {
            window.fairflockOpenCart();
          }
        }
      });
    }

    var inlineInput = document.querySelector(".ff-header-inline-search__input");
    var inlineForm = document.querySelector("form.ff-header-inline-search");

    if (inlineInput && hasSearch) {
      inlineInput.addEventListener("focus", function () {
        input.value = inlineInput.value || "";
        openSearch({ focusInput: true });
      });
      inlineInput.addEventListener("click", function () {
        input.value = inlineInput.value || "";
        openSearch({ focusInput: true });
      });
    }

    if (inlineForm && hasSearch) {
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
      if (inlineInput && input) {
        inlineInput.value = input.value || "";
      }
    }

    if (hasSearch) {
      input.id = "ffSearchInput";
      input.removeAttribute("required");
      input.setAttribute("autocomplete", "off");
      input.setAttribute("autocorrect", "off");
      input.setAttribute("autocapitalize", "off");
      input.setAttribute("spellcheck", "false");

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

      wireShopLinks();
      syncMega();
    }

    document.addEventListener("keydown", function (e) {
      if (e.key !== "Escape") return;

      if (isMenuOpen()) {
        e.preventDefault();
        if (isMenuSubOpen()) {
          closeMenuSub();
          return;
        }
        closeMenu();
        if (burger && typeof burger.focus === "function") {
          burger.focus();
        }
        return;
      }

      if (layer && layer.classList.contains("is-open")) {
        e.preventDefault();
        closeSearch();
        if (toggle && typeof toggle.focus === "function") {
          toggle.focus();
        }
      }
    });

    if (hasMenu) {
      syncMenuNav();
    }
  });
})();
