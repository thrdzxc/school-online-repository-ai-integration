(() => {
  document.querySelectorAll('[data-enrollment-dialog]').forEach((dialog) => {
    // The open attribute provides a readable confirmation without JavaScript.
    // Native modal mode supplies keyboard focus containment and Escape dismissal.
    if (typeof dialog.showModal === 'function') {
      dialog.removeAttribute('open');
      dialog.showModal();
      dialog.addEventListener('close', () => document.getElementById('enrollment-key')?.focus());
    }
  });

  const forms = document.querySelectorAll('form[data-enrollment-submit]');
  forms.forEach((form) => {
    const button = form.querySelector('[data-enrollment-button]');
    if (!button) return;
    const label = button.innerHTML;
    form.addEventListener('submit', (event) => {
      if (event.defaultPrevented) return;
      if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
      }
      form.dataset.submitting = 'true';
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      button.textContent = form.dataset.pendingLabel;
    });
    window.addEventListener('pageshow', () => {
      delete form.dataset.submitting;
      button.disabled = false;
      button.removeAttribute('aria-busy');
      button.innerHTML = label;
    });
  });
})();
