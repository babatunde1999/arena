/**
 * SITE.JS — Shared storefront chrome behaviour.
 * Pairs with: includes/header.php, includes/footer.php,
 *             includes/site-modals.php, css/site.css
 *
 * Handles: mobile hamburger menu, Orders/Help nav dropdowns,
 *          user dropdown menu, logout confirmation modal.
 * All lookups are null-guarded so the file is safe on any page.
 */
(function () {
  "use strict";

  /* ── Mobile hamburger menu ── */
  var hamburger = document.getElementById("hamburger-btn");
  var mobileMenu = document.getElementById("dropdown-menu");

  if (hamburger && mobileMenu) {
    hamburger.addEventListener("click", function (e) {
      e.stopPropagation();
      mobileMenu.classList.toggle("open");
    });

    mobileMenu.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }

  function closeMobileMenu() {
    if (mobileMenu) mobileMenu.classList.remove("open");
  }

  /* ── Generic dropdown toggle (Orders & Help) ── */
  function setupDropdown(triggerId, dropdownId) {
    var trigger = document.getElementById(triggerId);
    var dropdown = document.getElementById(dropdownId);
    if (!trigger || !dropdown) return;
    var chevron = trigger.querySelector(".chevron");

    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      var isOpen = dropdown.classList.contains("open");
      closeNavDropdowns();
      if (!isOpen) {
        dropdown.classList.add("open");
        if (chevron) chevron.classList.add("open");
      }
    });
  }

  function closeNavDropdowns() {
    document
      .querySelectorAll(".order-container")
      .forEach(function (d) { d.classList.remove("open"); });
    document
      .querySelectorAll(".chevron")
      .forEach(function (c) { c.classList.remove("open"); });
  }

  setupDropdown("orders-chevron", "orders-dropdown");
  setupDropdown("help-chevron", "help-dropdown");

  /* ── User dropdown menu ── */
  var userMenuBtn = document.getElementById("user-menu-btn");
  var userDropdown = document.getElementById("user-dropdown");

  function toggleUserMenu(e) {
    if (!userDropdown) return;
    if (e) e.stopPropagation();
    var isOpen = userDropdown.classList.contains("open");
    closeUserMenu();
    if (!isOpen) {
      userDropdown.classList.add("open");
      if (userMenuBtn) userMenuBtn.setAttribute("aria-expanded", "true");
    }
  }

  function closeUserMenu() {
    if (userDropdown) userDropdown.classList.remove("open");
    if (userMenuBtn) userMenuBtn.setAttribute("aria-expanded", "false");
  }

  // expose for inline onclick="" handlers in the header partial
  window.toggleUserMenu = toggleUserMenu;
  window.closeUserMenu = closeUserMenu;

  /* ── Close menus when clicking anywhere ── */
  document.addEventListener("click", function () {
    closeMobileMenu();
    closeNavDropdowns();
    closeUserMenu();
  });

  /* ── Logout confirmation modal ── */
  var logoutModal = document.getElementById("logout-modal");

  function openLogoutModal() {
    if (logoutModal) logoutModal.classList.add("active");
  }

  function closeLogoutModal() {
    if (logoutModal) logoutModal.classList.remove("active");
  }

  window.openLogoutModal = openLogoutModal;
  window.closeLogoutModal = closeLogoutModal;

  if (logoutModal) {
    logoutModal.addEventListener("click", function (e) {
      if (e.target === logoutModal) closeLogoutModal();
    });
  }

  /* Sign Out button: roll the spinner so the user knows they signed out */
  var logoutConfirm = document.getElementById("logout-confirm");
  if (logoutConfirm && logoutModal) {
    var label = logoutConfirm.querySelector(".btn-label");
    var original = label ? label.textContent : "Sign Out";

    logoutConfirm.addEventListener("click", function (e) {
      e.preventDefault();
      if (logoutConfirm.classList.contains("loading")) return;

      logoutConfirm.classList.add("loading");
      logoutConfirm.setAttribute("aria-disabled", "true");
      if (label) label.textContent = "Signing out…";

      setTimeout(function () {
        window.location.href = logoutConfirm.getAttribute("href");
      }, 500);
    });

    // Restore the button if the modal is reopened without navigating away.
    logoutModal.addEventListener("click", function (e) {
      if (e.target !== logoutModal) return;
      logoutConfirm.classList.remove("loading");
      logoutConfirm.removeAttribute("aria-disabled");
      if (label) label.textContent = original;
    });
  }

  /* ── Escape key closes shared menus/modals ── */
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeMobileMenu();
      closeNavDropdowns();
      closeUserMenu();
      closeLogoutModal();
    }
  });
})();
