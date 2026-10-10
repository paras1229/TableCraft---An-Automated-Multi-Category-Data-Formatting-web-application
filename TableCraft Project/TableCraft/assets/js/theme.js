/* =====================================================================
   TableCraft — Theme Engine (Dark / Bright)
   Default theme = "bright" as required by the project spec.
   ===================================================================== */
(function () {
  const STORAGE_KEY = 'tc_theme';

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.querySelectorAll('.theme-switch i').forEach((icon) => {
      icon.className = theme === 'dark' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
    });
    document.querySelectorAll('.theme-label').forEach((el) => {
      el.textContent = theme === 'dark' ? 'Dark mode' : 'Bright mode';
    });
  }

  function getStoredTheme() {
    return localStorage.getItem(STORAGE_KEY) || 'bright';
  }

  // Apply immediately (this file is loaded early in <head> to avoid a flash of wrong theme)
  applyTheme(getStoredTheme());

  window.TC_Theme = {
    toggle: function () {
      const current = document.documentElement.getAttribute('data-theme') || 'bright';
      const next = current === 'dark' ? 'bright' : 'dark';
      localStorage.setItem(STORAGE_KEY, next);
      applyTheme(next);

      // Persist to server too, so the preference follows the user across devices (best effort)
      fetch('/tablecraft/api/update_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'theme', theme: next })
      }).catch(() => {});
    },
    get: getStoredTheme
  };

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
      btn.addEventListener('click', window.TC_Theme.toggle);
    });
  });
})();
