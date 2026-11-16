/**
 * STORE.JS — shared cart / wishlist behaviour for the storefront.
 *
 * Progressively enhances the plain POST forms rendered by
 * includes/product-card.php, product.php, cart.php and wishlist.php.
 * Without JavaScript those forms still work via cart-action.php.
 */
(function () {
  "use strict";

  var toastTimer = null;

  function getToast() {
    var toast = document.getElementById("store-toast");
    if (toast) return toast;

    toast = document.createElement("div");
    toast.id = "store-toast";
    toast.className = "store-toast";
    toast.setAttribute("role", "status");
    toast.setAttribute("aria-live", "polite");
    document.body.appendChild(toast);
    return toast;
  }

  function showToast(message, ok) {
    var toast = getToast();
    toast.textContent = message;
    toast.className = "store-toast is-visible" + (ok ? "" : " is-error");

    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(function () {
      toast.className = "store-toast";
    }, 3200);
  }

  function setCount(selector, count) {
    document.querySelectorAll(selector).forEach(function (el) {
      var value = String(count);
      el.textContent = value;
      el.setAttribute("data-count", value);
      var host = el.closest("[data-count-host]");
      if (host) host.classList.toggle("has-items", count > 0);
    });
  }

  function setWishButton(form, active) {
    var button = form.querySelector("button[type='submit']");
    if (!button) return;

    button.classList.toggle("is-active", !!active);
    button.setAttribute("aria-pressed", active ? "true" : "false");
    var label = active ? "Remove from wishlist" : "Save to wishlist";
    button.setAttribute("aria-label", label);
    button.setAttribute("title", label);
  }

  function submitForm(form) {
    if (form.dataset.busy === "1") return;

    var button = form.querySelector("button[type='submit']");
    var previous = button ? button.innerHTML : "";

    form.dataset.busy = "1";
    if (button) {
      button.disabled = true;
      button.classList.add("is-loading");
    }

    fetch(form.getAttribute("action") || "cart-action.php", {
      method: "POST",
      body: new FormData(form),
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json"
      },
      credentials: "same-origin"
    })
      .then(function (response) {
        if (!response.ok) throw new Error("Request failed");
        return response.json();
      })
      .then(function (data) {
        showToast(data.message || "Done.", !!data.ok);

        if (typeof data.cartCount === "number") {
          setCount(".js-cart-count", data.cartCount);
        }
        if (typeof data.wishlistCount === "number") {
          setCount(".js-wishlist-count", data.wishlistCount);
        }
        if (typeof data.active === "boolean") {
          setWishButton(form, data.active);
        }

        if (form.hasAttribute("data-reload-on-success") && data.ok) {
          window.setTimeout(function () {
            window.location.reload();
          }, 500);
        }
      })
      .catch(function () {
        // Network/JSON failure: fall back to the normal form submit.
        form.dataset.busy = "";
        if (button) {
          button.innerHTML = previous;
          button.classList.remove("is-loading");
          button.disabled = false;
        }
        form.submit();
      })
      .then(function () {
        form.dataset.busy = "";
        if (button) {
          button.classList.remove("is-loading");
          button.innerHTML = previous;
          button.disabled = false;
        }
      });
  }

  document.querySelectorAll("[data-cart-form], [data-wishlist-form]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      if (!window.fetch || !window.FormData) return; // let the browser submit
      event.preventDefault();
      submitForm(form);
    });
  });

  /* ── Product gallery thumbnails ── */
  var mainImage = document.querySelector("[data-gallery-main]");
  if (mainImage) {
    document.querySelectorAll("[data-gallery-thumb]").forEach(function (thumb) {
      thumb.addEventListener("click", function () {
        var src = thumb.getAttribute("data-gallery-thumb");
        if (!src) return;
        mainImage.setAttribute("src", src);
        if (thumb.getAttribute("data-gallery-alt")) {
          mainImage.setAttribute("alt", thumb.getAttribute("data-gallery-alt"));
        }
        document.querySelectorAll("[data-gallery-thumb]").forEach(function (other) {
          other.classList.toggle("is-active", other === thumb);
        });
      });
    });
  }

  /* ── Quantity steppers on the cart / product page ── */
  document.querySelectorAll("[data-qty-step]").forEach(function (button) {
    button.addEventListener("click", function () {
      var input = document.getElementById(button.getAttribute("data-qty-step"));
      if (!input) return;

      var min = parseInt(input.getAttribute("min") || "1", 10);
      var max = parseInt(input.getAttribute("max") || "999", 10);
      var next = (parseInt(input.value, 10) || min) + (button.getAttribute("data-step") === "up" ? 1 : -1);

      input.value = Math.min(max, Math.max(min, next));
    });
  });
})();
