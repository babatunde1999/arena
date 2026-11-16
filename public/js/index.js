/**
 * INDEX.JS — Home page behaviour (carousel + login/vendor modals).
 * Pairs with: public/index.php, css/index.css
 * Shared chrome behaviour (menus, logout modal) lives in js/site.js.
 */
(function () {
  "use strict";

  /* ── Carousel: arrows + drag-to-scroll + wheel ── */
  var carousels = document.querySelectorAll(
    ".carousel, .carousel-card, .carousel-market-card"
  );

  carousels.forEach(function (el) {
    if (el.dataset.carouselReady) return;
    el.dataset.carouselReady = "1";

    // Don't let images start a native drag while moving the carousel
    el.querySelectorAll("img").forEach(function (img) {
      img.draggable = false;
    });

    // Wrap in a positioned container so arrow buttons can anchor
    var outer = document.createElement("div");
    outer.className = "carousel-outer";
    el.parentNode.insertBefore(outer, el);
    outer.appendChild(el);

    var btnLeft = document.createElement("button");
    var btnRight = document.createElement("button");
    btnLeft.type = btnRight.type = "button";
    btnLeft.className = "carousel-btn carousel-btn-left";
    btnRight.className = "carousel-btn carousel-btn-right";
    btnLeft.setAttribute("aria-label", "Scroll left");
    btnRight.setAttribute("aria-label", "Scroll right");
    btnLeft.innerHTML = "\u2039";
    btnRight.innerHTML = "\u203A";
    outer.appendChild(btnLeft);
    outer.appendChild(btnRight);

    var step = function () {
      return Math.round(el.clientWidth * 0.8);
    };

    var update = function () {
      var max = el.scrollWidth - el.clientWidth;
      btnLeft.disabled = el.scrollLeft <= 0;
      btnRight.disabled = el.scrollLeft >= max - 1;
    };

    btnLeft.addEventListener("click", function () {
      el.scrollBy({ left: -step(), behavior: "smooth" });
    });

    btnRight.addEventListener("click", function () {
      el.scrollBy({ left: step(), behavior: "smooth" });
    });

    el.addEventListener("scroll", update, { passive: true });
    window.addEventListener("resize", update);
    // Re-check once images finish loading (scroll-width changes)
    window.addEventListener("load", update);
    if (document.readyState === "complete") update();
    else requestAnimationFrame(update);

    // ── Drag to scroll (mouse) ──
    // Only begin an actual drag after the pointer moves a few pixels. Capturing
    // the pointer on pointerdown retargets pointerup to the carousel, which
    // stops real clicks/submits on card buttons and links from ever firing.
    var DRAG_THRESHOLD = 5;
    var drag = null;

    function isInteractive(target) {
      return !!(target && target.closest &&
        target.closest("a, button, input, select, textarea, label, [role='button']"));
    }

    el.addEventListener("pointerdown", function (e) {
      if (e.pointerType !== "mouse" || e.button !== 0) return;
      if (isInteractive(e.target)) return; // let card buttons/links work
      drag = {
        startX: e.clientX,
        startScroll: el.scrollLeft,
        pointerId: e.pointerId,
        active: false
      };
    });

    el.addEventListener("pointermove", function (e) {
      if (!drag) return;
      var dx = e.clientX - drag.startX;

      if (!drag.active) {
        if (Math.abs(dx) < DRAG_THRESHOLD) return;
        drag.active = true;
        outer.classList.add("dragging");
        el.style.scrollBehavior = "auto";
        if (el.setPointerCapture) {
          try { el.setPointerCapture(drag.pointerId); } catch (err) {}
        }
      }

      e.preventDefault();
      el.scrollLeft = drag.startScroll - dx;
    });

    function stopDrag(e) {
      if (!drag) return;
      if (drag.active) {
        outer.classList.remove("dragging");
        el.style.scrollBehavior = "";
        if (e && el.releasePointerCapture && drag.pointerId) {
          try { el.releasePointerCapture(drag.pointerId); } catch (err) {}
        }
      }
      drag = null;
    }
    el.addEventListener("pointerup", stopDrag);
    el.addEventListener("pointercancel", stopDrag);
    el.addEventListener("pointerleave", stopDrag);

    // ── Vertical wheel becomes horizontal scroll ──
    el.addEventListener(
      "wheel",
      function (e) {
        if (el.scrollWidth <= el.clientWidth) return; // nothing to scroll
        if (Math.abs(e.deltaY) < Math.abs(e.deltaX)) return; // native horizontal
        e.preventDefault();
        el.scrollLeft += e.deltaY;
      },
      { passive: false }
    );
  });

  /* ── Practice snippets (kept from the old inline <script> on index.php) ── */
  const add = (b, k) => {
    return b + k;
  };
  console.log(add(5, 6));

  const multiply = (a, b) => a * b;
  console.log(multiply(5, 9));

  const fruits = ["red-apple", "yelloe-banana", "green-mellon"];
  fruits.forEach((fruit) => console.log(fruit));

  /* ── Welcome modal (after login) ── */
  var welcomeModal = document.getElementById("welcome-modal");

  function closeWelcomeModal() {
    if (welcomeModal) welcomeModal.classList.remove("active");
  }

  window.closeWelcomeModal = closeWelcomeModal;

  if (welcomeModal) {
    welcomeModal.addEventListener("click", function (e) {
      if (e.target === welcomeModal) closeWelcomeModal();
    });
  }

  /* ── Become-a-vendor modal ── */
  var becomeVendorModal = document.getElementById("become-vendor-modal");

  function openBecomeVendorModal() {
    if (becomeVendorModal) becomeVendorModal.classList.add("active");
  }

  function closeBecomeVendorModal() {
    if (becomeVendorModal) becomeVendorModal.classList.remove("active");
  }

  window.openBecomeVendorModal = openBecomeVendorModal;
  window.closeBecomeVendorModal = closeBecomeVendorModal;

  if (becomeVendorModal) {
    becomeVendorModal.addEventListener("click", function (e) {
      if (e.target === becomeVendorModal) closeBecomeVendorModal();
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeWelcomeModal();
      closeBecomeVendorModal();
    }
  });
})();
