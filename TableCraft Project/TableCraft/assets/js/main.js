/* =====================================================================
   TableCraft — Core UI Interactions
   ===================================================================== */
document.addEventListener('DOMContentLoaded', function () {

  /* ---- Mobile sidebar toggle ------------------------------------- */
  const sidebar   = document.querySelector('.tc-sidebar');
  const backdrop  = document.querySelector('.sidebar-backdrop');
  const toggleBtn = document.querySelector('.sidebar-toggle-btn');

  function openSidebar() {
    sidebar && sidebar.classList.add('open');
    backdrop && backdrop.classList.add('show');
  }
  function closeSidebar() {
    sidebar && sidebar.classList.remove('open');
    backdrop && backdrop.classList.remove('show');
  }
  toggleBtn && toggleBtn.addEventListener('click', openSidebar);
  backdrop  && backdrop.addEventListener('click', closeSidebar);

  /* ---- Staggered entrance animation for cards / table rows -------- */
  document.querySelectorAll('.stagger-item').forEach((el, i) => {
    el.style.animationDelay = (i * 60) + 'ms';
  });

  /* ---- Animated counters on dashboard stat tiles ------------------- */
  document.querySelectorAll('[data-count-to]').forEach((el) => {
    const target = parseInt(el.getAttribute('data-count-to'), 10) || 0;
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 40));
    const timer = setInterval(() => {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = current.toLocaleString();
    }, 20);
  });

  /* ---- Password show/hide toggle on auth pages ---------------------- */
  document.querySelectorAll('.toggle-eye').forEach((btn) => {
    btn.addEventListener('click', function () {
      const input = this.closest('.input-icon-group').querySelector('input');
      const icon  = this.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text'; icon.className = 'bi bi-eye-slash';
      } else {
        input.type = 'password'; icon.className = 'bi bi-eye';
      }
    });
  });

  /* ---- Live password strength meter (register page) ------------------ */
  const pwField = document.getElementById('reg_password');
  const pwBar   = document.getElementById('pw_strength_bar');
  if (pwField && pwBar) {
    pwField.addEventListener('input', function () {
      const val = this.value;
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
      if (/\d/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;
      const pct = (score / 5) * 100;
      const colors = ['#E5484D', '#E5484D', '#F5A524', '#F5A524', '#17B26A', '#17B26A'];
      pwBar.style.width = pct + '%';
      pwBar.style.background = colors[score];
    });
  }

  /* ---- Auto-dismiss alerts ------------------------------------------- */
  document.querySelectorAll('.alert[data-autohide]').forEach((el) => {
    setTimeout(() => { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 4000);
  });

});

/* ---- Shared toast helper (used across dashboard + table pages) -------- */
window.tcToast = function (message, type) {
  const el = document.createElement('div');
  el.className = 'toast-tc' + (type === 'error' ? ' error' : '');
  el.innerHTML = '<i class="bi ' + (type === 'error' ? 'bi-x-circle' : 'bi-check-circle') + ' me-2"></i>' + message;
  document.body.appendChild(el);
  setTimeout(() => {
    el.style.transition = 'opacity .4s, transform .4s';
    el.style.opacity = '0';
    el.style.transform = 'translateY(10px)';
    setTimeout(() => el.remove(), 400);
  }, 2600);
};
