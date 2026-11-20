const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

function setup() {
  const errors = {};
  const fields = ['name', 'email', 'password', 'password_confirmation'].map(name => {
    errors[name] = { id: `create-${name}-error`, textContent: '' };
    return {
      name, value: '', required: true, type: name === 'email' ? 'email' : 'text',
      dataset: name === 'name' ? { personName: '' } : name === 'password' ? { strongPassword: '' } : {},
      validity: { valid: true, typeMismatch: false }, attrs: {}, events: {}, focused: false,
      addEventListener(event, callback) { this.events[event] = callback; },
      setAttribute(key, value) { this.attrs[key] = value; },
      getAttribute(key) { return this.attrs[key]; },
      focus() { this.focused = true; },
    };
  });
  const form = {
    events: {}, elements: Object.fromEntries(fields.map(field => [field.name, field])),
    querySelectorAll: () => fields,
    querySelector(selector) { return errors[selector.match(/="([^"]+)"/)[1]]; },
    addEventListener(event, callback) { this.events[event] = callback; },
  };
  vm.runInNewContext(readFileSync('public/js/form-validation.js', 'utf8'), {
    document: { querySelectorAll: () => [form] },
  });
  const submit = () => {
    const event = { prevented: false, preventDefault() { this.prevented = true; } };
    form.events.submit(event);
    return event;
  };
  return { fields: form.elements, errors, submit };
}

test('required fields show inline errors and focus the first invalid textbox', () => {
  const { fields, errors, submit } = setup();
  assert.equal(submit().prevented, true);
  assert.equal(fields.name.focused, true);
  assert.match(errors.name.textContent, /fill in/);
  assert.equal(fields.name.attrs['aria-describedby'], 'create-name-error');
});

test('numeric names are blocked on typing and paste, and rejected on submit', () => {
  const { fields, errors, submit } = setup();
  for (const mode of ['beforeinput', 'paste']) {
    const event = { data: '123', clipboardData: { getData: () => 'Student123' }, prevented: false, preventDefault() { this.prevented = true; } };
    fields.name.events[mode](event);
    assert.equal(event.prevented, true);
    assert.match(errors.name.textContent, /numbers/);
  }
  fields.name.value = 'Student123';
  assert.equal(submit().prevented, true);
  assert.equal(fields.name.attrs['aria-invalid'], 'true');
});

test('incomplete email is rejected and valid Unicode names and complete emails pass', () => {
  const { fields, errors, submit } = setup();
  fields.name.value = 'María Dela Cruz';
  fields.password.value = fields.password_confirmation.value = 'SecurePass!1';
  for (const email of ['student', 'student@', 'student@gmail', '@gmail.com']) {
    fields.email.value = email;
    assert.equal(submit().prevented, true);
    assert.match(errors.email.textContent, /complete email/);
  }
  fields.email.value = 'student@gmail.com';
  fields.email.events.input();
  assert.equal(errors.email.textContent, '');
  assert.equal(submit().prevented, false);
});

test('password confirmation and strength errors stay beside their controls', () => {
  const { fields, errors, submit } = setup();
  fields.name.value = 'Student'; fields.email.value = 'student@gmail.com';
  fields.password.value = 'weak'; fields.password_confirmation.value = 'different';
  assert.equal(submit().prevented, true);
  assert.match(errors.password.textContent, /uppercase/);
  assert.match(errors.password_confirmation.textContent, /do not match/);
});
