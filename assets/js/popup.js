/**
 * Countdown pop-up (FairFlock → Pop-up).
 */
(function () {
  var cfg = window.fairflockPopup || {};
  var root = document.getElementById("ffPopup");
  if (!root || !cfg.end) return;

  var dialog = root.querySelector(".ff-popup__dialog");
  var countdown = root.querySelector(".ff-popup__countdown");
  var done = root.querySelector(".ff-popup__done");
  var nums = {};
  Array.prototype.forEach.call(root.querySelectorAll("[data-ff-unit]"), function (el) {
    nums[el.getAttribute("data-ff-unit")] = el;
  });

  /* Server clock is the reference — visitor clocks may be off. */
  var skew = cfg.now ? cfg.now - Date.now() : 0;
  var timer = null;
  var lastFocus = null;
  var FREQ_MS = { day: 864e5, week: 6048e5 };

  function store(kind) {
    try {
      return kind === "session" ? window.sessionStorage : window.localStorage;
    } catch (e) {
      return null;
    }
  }

  function alreadySeen() {
    var f = cfg.frequency;
    if (f === "always") return false;
    var s = store(f);
    if (!s) return false;
    var at = parseInt(s.getItem(cfg.key) || "0", 10);
    if (!at) return false;
    if (f === "session") return true;
    return Date.now() - at < (FREQ_MS[f] || FREQ_MS.day);
  }

  function markSeen() {
    if (cfg.frequency === "always") return;
    var s = store(cfg.frequency);
    if (s) {
      try {
        s.setItem(cfg.key, String(Date.now()));
      } catch (e) {}
    }
  }

  function pad(n) {
    return n < 10 ? "0" + n : String(n);
  }

  function tick() {
    var left = Math.max(0, cfg.end - (Date.now() + skew));
    var sec = Math.floor(left / 1000);
    var parts = {
      days: Math.floor(sec / 86400),
      hours: Math.floor((sec % 86400) / 3600),
      minutes: Math.floor((sec % 3600) / 60),
      seconds: sec % 60
    };
    Object.keys(parts).forEach(function (k) {
      if (nums[k]) nums[k].textContent = k === "days" ? String(parts[k]) : pad(parts[k]);
    });
    if (left <= 0) {
      if (timer) window.clearInterval(timer);
      timer = null;
      if (countdown) countdown.hidden = true;
      if (done) done.hidden = false;
    }
    return left;
  }

  function onKey(e) {
    if (e.key === "Escape") {
      close();
      return;
    }
    if (e.key !== "Tab") return;
    var focusables = dialog.querySelectorAll("a[href], button:not([disabled])");
    if (!focusables.length) return;
    var first = focusables[0];
    var last = focusables[focusables.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  function open() {
    lastFocus = document.activeElement;
    root.hidden = false;
    document.documentElement.classList.add("is-ff-popup-open");
    void root.offsetWidth;
    root.classList.add("is-open");
    dialog.focus({ preventScroll: true });
    document.addEventListener("keydown", onKey);
    markSeen();
    tick();
    if (!timer) timer = window.setInterval(tick, 1000);
  }

  function close() {
    root.classList.remove("is-open");
    document.documentElement.classList.remove("is-ff-popup-open");
    document.removeEventListener("keydown", onKey);
    if (timer) window.clearInterval(timer);
    timer = null;
    window.setTimeout(function () {
      root.hidden = true;
    }, 300);
    if (lastFocus && typeof lastFocus.focus === "function") {
      lastFocus.focus({ preventScroll: true });
    }
  }

  root.addEventListener("click", function (e) {
    if (e.target.closest("[data-ff-popup-close]")) {
      e.preventDefault();
      close();
    }
  });

  if (tick() <= 0 && cfg.afterEnd === "hide") return;
  if (alreadySeen()) return;

  window.setTimeout(open, cfg.delay || 0);
})();
