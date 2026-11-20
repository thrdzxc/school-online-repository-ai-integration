(() => {
  const body = document.body;
  const toggles = document.querySelectorAll('[data-sidebar-toggle]');
  const closeButton = document.querySelector('[data-sidebar-close]');
  const desktopQuery = window.matchMedia('(min-width: 821px)');
  const storageKey = 'schoolRepository.sidebarCollapsed';
  const sidebar = document.getElementById('sidebar');
  const main = document.querySelector('.main');

  const syncSidebar = () => {
    const expanded = desktopQuery.matches
      ? !body.classList.contains('sidebar-collapsed')
      : body.classList.contains('sidebar-mobile-open');
    toggles.forEach((button) => {
      const label = desktopQuery.matches
        ? (expanded ? 'Minimize sidebar' : 'Maximize sidebar')
        : (expanded ? 'Close navigation' : 'Open navigation');
      button.setAttribute('aria-expanded', String(expanded));
      button.setAttribute('aria-controls', 'sidebar');
      button.setAttribute('aria-label', label);
      button.title = label;
    });
    if (sidebar) sidebar.inert = !desktopQuery.matches && !expanded;
    if (main) main.inert = !desktopQuery.matches && expanded;
  };

  const setCollapsed = (collapsed) => {
    body.classList.toggle('sidebar-collapsed', collapsed);
    syncSidebar();
    if (desktopQuery.matches) {
      try { localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (_) {}
    }
  };

  const setMobileOpen = (open) => {
    body.classList.toggle('sidebar-mobile-open', open);
    syncSidebar();
    if (!desktopQuery.matches) {
      if (open) sidebar?.querySelector('[data-nav-link]')?.focus();
      else document.querySelector('.topbar-menu')?.focus();
    }
  };

  const restoreSidebar = () => {
    if (!desktopQuery.matches) {
      body.classList.remove('sidebar-collapsed');
      setMobileOpen(false);
      return;
    }
    let collapsed = false;
    try { collapsed = localStorage.getItem(storageKey) === '1'; } catch (_) {}
    body.classList.remove('sidebar-mobile-open');
    setCollapsed(collapsed);
  };

  toggles.forEach((button) => {
    button.addEventListener('click', () => {
      if (desktopQuery.matches) {
        setCollapsed(!body.classList.contains('sidebar-collapsed'));
      } else {
        setMobileOpen(!body.classList.contains('sidebar-mobile-open'));
      }
    });
  });

  closeButton?.addEventListener('click', () => setMobileOpen(false));

  document.querySelectorAll('[data-nav-link]').forEach((link) => {
    link.addEventListener('click', () => {
      // Never intercept navigation. Only close the mobile drawer visually.
      if (!desktopQuery.matches) setMobileOpen(false);
    });
  });

  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-password-toggle');
      const input = document.getElementById(targetId);
      if (!input) return;
      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
      button.classList.toggle('showing', !showing);
    });
  });

  document.querySelectorAll('[data-copy-enrollment-key]').forEach((button) => {
    button.addEventListener('click', async () => {
      const input = document.getElementById(button.dataset.copyEnrollmentKey);
      const status = button.closest('.card')?.querySelector('[data-copy-key-status]');
      if (!input || !status) return;
      try {
        await navigator.clipboard.writeText(input.value);
        status.textContent = 'Enrollment key copied. Share it with your students.';
      } catch (_) {
        input.focus();
        input.select();
        status.textContent = 'Key selected. Use your device’s Copy command to copy it.';
      }
    });
  });

  const profileInput = document.querySelector('[data-profile-input]');
  if (profileInput) {
    profileInput.addEventListener('change', () => {
      const file = profileInput.files && profileInput.files[0];
      const previewBox = document.getElementById('profilePreview');
      if (!file || !previewBox || !file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = (event) => {
        previewBox.innerHTML = '';
        const img = document.createElement('img');
        img.src = event.target.result;
        img.alt = 'Selected profile photo preview';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        previewBox.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  }

  desktopQuery.addEventListener('change', restoreSidebar);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setMobileOpen(false);
  });

  restoreSidebar();
})();
