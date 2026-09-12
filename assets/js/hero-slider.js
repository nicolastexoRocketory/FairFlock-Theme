(function () {
  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  ready(function () {
    if (document.body.classList.contains("block-editor-iframe__body")) {
      return;
    }

    var root = document.querySelector(".fs-slider");
    if (!root) return;

    var slides = Array.prototype.slice.call(root.querySelectorAll(".fs-slide"));
    var prev = root.querySelector("[data-fs-prev]");
    var next = root.querySelector("[data-fs-next]");
    var dotsWrap = root.querySelector("[data-fs-dots]");
    if (!slides.length || !dotsWrap) return;

    var index = 0;
    var timer = null;
    var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    slides.forEach(function (_, i) {
      var dot = document.createElement("button");
      dot.type = "button";
      dot.className = "fs-dot" + (i === 0 ? " is-on" : "");
      dot.setAttribute("aria-label", "Slide " + (i + 1));
      dot.addEventListener("click", function () {
        go(i);
      });
      dotsWrap.appendChild(dot);
    });

    function go(i) {
      index = (i + slides.length) % slides.length;
      slides.forEach(function (slide, n) {
        var on = n === index;
        slide.classList.toggle("is-active", on);
        // Nur CSS steuert Sichtbarkeit — kein hidden-Attribut (Editor braucht alle Slides)
        slide.removeAttribute("hidden");
        slide.setAttribute("aria-hidden", on ? "false" : "true");
      });
      Array.prototype.forEach.call(dotsWrap.children, function (dot, n) {
        dot.classList.toggle("is-on", n === index);
      });
      restart();
    }

    function restart() {
      if (timer) window.clearInterval(timer);
      if (reduceMotion || slides.length < 2) return;
      timer = window.setInterval(function () {
        go(index + 1);
      }, 7000);
    }

    if (prev) {
      prev.addEventListener("click", function () {
        go(index - 1);
      });
    }
    if (next) {
      next.addEventListener("click", function () {
        go(index + 1);
      });
    }

    root.addEventListener("mouseenter", function () {
      if (timer) window.clearInterval(timer);
    });
    root.addEventListener("mouseleave", restart);

    document.addEventListener("keydown", function (e) {
      if (!root.matches(":hover") && document.activeElement && !root.contains(document.activeElement)) {
        return;
      }
      if (e.key === "ArrowLeft") go(index - 1);
      if (e.key === "ArrowRight") go(index + 1);
    });

    restart();
  });
})();
