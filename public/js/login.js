(() => {
  const form = document.querySelector('[data-login-form]');
  if (!form) return;
  const email = form.elements.email;
  const password = form.elements.password;
  const remember = form.elements.remember;
  const key = `schoolRepository.login.${form.dataset.portal}`;
  const help = document.getElementById('remember-login-help');
  const read = () => {
    try { return localStorage.getItem(key) || ''; } catch (_) { return ''; }
  };
  const forget = () => {
    try { localStorage.removeItem(key); } catch (_) { /* Storage may be disabled. */ }
  };
  function mode() {
    form.autocomplete = remember.checked ? 'on' : 'off';
    email.autocomplete = remember.checked ? 'username' : 'off';
    password.autocomplete = remember.checked ? 'current-password' : 'off';
    help.textContent = remember.checked
      ? 'This portal will remember your email and keep you signed in. Accept your browser’s Save password prompt to fill your password next time.'
      : 'Manual login: this portal’s remembered email is cleared. Your browser may still offer passwords saved in its password manager.';
  }
  const savedEmail = read();
  remember.checked = Boolean(savedEmail);
  if (savedEmail && !email.value) email.value = savedEmail;
  mode();
  remember.addEventListener('change', () => {
    if (!remember.checked) {
      forget();
      email.value = '';
      password.value = '';
      email.focus();
    }
    mode();
  });
  form.addEventListener('submit', event => {
    if (event.defaultPrevented || !form.checkValidity()) return;
    if (remember.checked) {
      try { localStorage.setItem(key, email.value.trim()); } catch (_) {
        help.textContent = 'Browser storage is unavailable. Your email cannot be remembered on this device.';
      }
    } else {
      forget();
    }
  });
  // Never persist passwords in application storage. The browser password manager
  // handles password saving and filling through the standard login fields.
})();
