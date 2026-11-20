const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function setup(portal, storage = new Map()) {
  const field = value => ({ value, events: {}, addEventListener(k, fn) { this.events[k] = fn; }, focus() {} });
  const email = field('');
  const password = field('');
  const remember = field('1');
  const form = { elements: { email, password, remember }, dataset: { portal }, events: {},
    addEventListener(k, fn) { this.events[k] = fn; }, checkValidity: () => true };
  vm.runInNewContext(fs.readFileSync('public/js/login.js', 'utf8'), {
    document: { querySelector: () => form, getElementById: () => ({}) },
    localStorage: { getItem: k => storage.get(k), setItem: (k, v) => storage.set(k, v), removeItem: k => storage.delete(k) },
  });
  return { form, email, password, remember };
}

test('each role remembers its own email without storing passwords', () => {
  const storage = new Map();
  for (const role of ['student', 'adviser', 'admin']) {
    const app = setup(role, storage);
    assert.equal(app.remember.checked, false);
    app.remember.checked = true;
    app.remember.events.change();
    app.email.value = `${role}@school.test`;
    app.password.value = 'private-secret';
    app.form.events.submit({ defaultPrevented: false });
    const returned = setup(role, storage);
    assert.equal(returned.email.value, `${role}@school.test`);
    assert.equal(returned.password.value, '');
    assert.equal(returned.password.autocomplete, 'current-password');
  }
  assert.equal(storage.size, 3);
  assert.equal([...storage.values()].includes('private-secret'), false);
});

test('unchecking clears fields and forgets only the selected role', () => {
  const storage = new Map([['schoolRepository.login.student', 'student@school.test'], ['schoolRepository.login.admin', 'admin@school.test']]);
  const app = setup('student', storage);
  app.password.value = 'filled-by-browser';
  app.remember.checked = false;
  app.remember.events.change();
  assert.equal(app.email.value, '');
  assert.equal(app.password.value, '');
  assert.equal(app.password.autocomplete, 'off');
  assert.equal(storage.has('schoolRepository.login.student'), false);
  assert.equal(storage.has('schoolRepository.login.admin'), true);
  assert.equal(setup('student', storage).remember.checked, false);
});

test('a cancelled submission does not save login preferences', () => {
  const storage = new Map();
  const app = setup('admin', storage);
  app.remember.checked = true;
  app.email.value = 'invalid';
  app.form.events.submit({ defaultPrevented: true });
  assert.equal(storage.size, 0);
});
