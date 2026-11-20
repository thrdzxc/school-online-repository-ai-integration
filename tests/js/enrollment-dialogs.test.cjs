const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

function setup({ modalSupport = true } = {}) {
  const dialog = {
    open: true, events: {}, modal: false,
    removeAttribute() { this.open = false; },
    addEventListener(name, callback) { this.events[name] = callback; },
  };
  if (modalSupport) dialog.showModal = function () { this.modal = true; this.open = true; };
  const input = { focused: false, focus() { this.focused = true; } };
  const button = {
    disabled: false, innerHTML: 'Confirm Enrollment <svg></svg>', attrs: {},
    setAttribute(key, value) { this.attrs[key] = value; },
    removeAttribute(key) { delete this.attrs[key]; },
  };
  const form = {
    dataset: { pendingLabel: 'Confirming enrollment…' }, events: {},
    querySelector: () => button,
    addEventListener(name, callback) { this.events[name] = callback; },
  };
  const events = {};
  vm.runInNewContext(readFileSync('public/js/enrollment-dialogs.js', 'utf8'), {
    document: {
      querySelectorAll: selector => selector === '[data-enrollment-dialog]' ? [dialog] : [form],
      getElementById: () => input,
    },
    window: { addEventListener(name, callback) { events[name] = callback; } },
  });
  return { dialog, input, button, form, events };
}

test('review opens as a modal and dismissal returns focus without submitting', () => {
  const { dialog, input, form } = setup();
  assert.equal(dialog.modal, true);
  assert.equal(form.dataset.submitting, undefined);
  dialog.events.close();
  assert.equal(input.focused, true);
  assert.equal(form.dataset.submitting, undefined);
});

test('confirmation remains visible when modal enhancement is unavailable', () => {
  const { dialog } = setup({ modalSupport: false });
  assert.equal(dialog.open, true);
  assert.equal(dialog.modal, false);
});

test('submission disables repeated clicks and browser Back restores the button', () => {
  const { form, button, events } = setup();
  form.events.submit({ defaultPrevented: false });
  assert.equal(button.disabled, true);
  assert.equal(button.textContent, 'Confirming enrollment…');
  let prevented = false;
  form.events.submit({ preventDefault() { prevented = true; } });
  assert.equal(prevented, true);
  events.pageshow();
  assert.equal(button.disabled, false);
  assert.equal(form.dataset.submitting, undefined);
  assert.equal(button.attrs['aria-busy'], undefined);
  assert.match(button.innerHTML, /<svg>/);
});

test('a cancelled submission does not leave the form disabled', () => {
  const { form, button } = setup();
  form.events.submit({ defaultPrevented: true });
  assert.equal(button.disabled, false);
});
