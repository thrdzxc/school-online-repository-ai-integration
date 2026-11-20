(() => {
  const namePattern = /^[\p{L}\p{M}]+(?:[ '\u2019-][\p{L}\p{M}]+)*$/u;
  const emailPattern = /^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+$/;
  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.noValidate = true;
    const fields = [...form.querySelectorAll('input:not([type=hidden]), select, textarea')];
    const messageFor = (field) => {
      const value = field.value.trim();
      if (field.required && !value) return 'Please fill in this field.';
      if (!value) return '';
      if (field.dataset.personName !== undefined && !namePattern.test(value)) return 'Use letters, spaces, hyphens, or apostrophes only. Numbers are not allowed.';
      if (field.type === 'email' && (!emailPattern.test(value) || field.validity.typeMismatch)) return 'Enter a complete email address, such as student@gmail.com.';
      if (field.dataset.strongPassword !== undefined && (field.value.length < 8 || !/[A-Z]/.test(field.value) || !/[^A-Za-z0-9]/.test(field.value))) return 'Use 8 or more characters, an uppercase letter, and a special character.';
      if (field.name === 'password_confirmation' && field.value !== form.elements.password.value) return 'The passwords do not match.';
      if (!field.validity.valid) return field.validationMessage;
      return '';
    };
    const show = (field, message) => {
      const error = form.querySelector(`[data-field-error="${field.name}"]`);
      if (error) {
        error.textContent = message;
        field.setAttribute('aria-describedby', error.id);
      }
      field.setAttribute('aria-invalid', String(Boolean(message)));
    };
    fields.forEach((field) => {
      const error = form.querySelector(`[data-field-error="${field.name}"]`);
      if (error?.textContent.trim()) show(field, error.textContent);
      field.addEventListener('input', () => show(field, messageFor(field)));
      field.addEventListener('blur', () => show(field, messageFor(field)));
      // Reject digits and other disallowed characters on typing and paste.
      if (field.dataset.personName !== undefined) {
        field.addEventListener('beforeinput', (event) => {
          if (event.data && /[^\p{L}\p{M} '\u2019-]/u.test(event.data)) {
            event.preventDefault();
            show(field, 'Names cannot contain numbers or other special characters.');
          }
        });
        field.addEventListener('paste', (event) => {
          if (/[^\p{L}\p{M} '\u2019-]/u.test(event.clipboardData.getData('text'))) {
            event.preventDefault();
            show(field, 'Names cannot contain numbers or other special characters.');
          }
        });
      }
    });
    form.addEventListener('submit', (event) => {
      let firstInvalid;
      fields.forEach((field) => {
        const message = messageFor(field);
        show(field, message);
        if (message && !firstInvalid) firstInvalid = field;
      });
      if (firstInvalid) {
        event.preventDefault();
        firstInvalid.focus();
      }
    });
    const invalid = fields.find(field => field.getAttribute('aria-invalid') === 'true');
    if (invalid) {
      const details = form.closest('details');
      if (details) details.open = true;
      invalid.focus();
    }
  });
})();
