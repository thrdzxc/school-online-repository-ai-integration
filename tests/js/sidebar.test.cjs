const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

function setup({ desktop = true, saved = '0' } = {}) {
  const classes = new Set();
  const listeners = {};
  const storage = new Map([['schoolRepository.sidebarCollapsed', saved]]);
  const element = () => ({
    attrs: {}, events: {}, focused: false, inert: false,
    setAttribute(key, value) { this.attrs[key] = value; },
    addEventListener(key, fn) { this.events[key] = fn; },
    focus() { this.focused = true; },
  });
  const arrow = element();
  const menu = element();
  const backdrop = element();
  const link = element();
  const sidebar = element();
  sidebar.querySelector = () => link;
  const main = element();
  const media = {
    matches: desktop,
    addEventListener(_, fn) { this.onChange = fn; },
  };
  const document = {
    body: { classList: {
      contains: key => classes.has(key),
      remove: key => classes.delete(key),
      toggle(key, enabled) { if (enabled) classes.add(key); else classes.delete(key); },
    } },
    querySelectorAll: selector => selector === '[data-sidebar-toggle]' ? [arrow, menu]
      : selector === '[data-nav-link]' ? [link] : [],
    querySelector: selector => ({ '[data-sidebar-close]': backdrop, '.main': main, '.topbar-menu': menu })[selector],
    getElementById: id => id === 'sidebar' ? sidebar : null,
    addEventListener: (name, fn) => { listeners[name] = fn; },
  };
  vm.runInNewContext(readFileSync('public/js/app.js', 'utf8'), {
    document, window: { matchMedia: () => media },
    localStorage: { getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value) },
  });
  return { arrow, menu, backdrop, link, sidebar, main, media, storage, classes, listeners };
}

test('desktop arrow restores, expands and persists the sidebar with accurate accessible labels', () => {
  const ui = setup({ saved: '1' });
  assert.equal(ui.arrow.attrs['aria-expanded'], 'false');
  assert.equal(ui.arrow.attrs['aria-label'], 'Maximize sidebar');
  ui.arrow.events.click();
  assert.equal(ui.classes.has('sidebar-collapsed'), false);
  assert.equal(ui.arrow.attrs['aria-expanded'], 'true');
  assert.equal(ui.storage.get('schoolRepository.sidebarCollapsed'), '0');
  ui.arrow.events.click();
  assert.equal(ui.storage.get('schoolRepository.sidebarCollapsed'), '1');
});

test('mobile navigation keeps hidden links unfocusable and Escape restores focus', () => {
  const ui = setup({ desktop: false, saved: '1' });
  assert.equal(ui.sidebar.inert, true);
  ui.menu.events.click();
  assert.equal(ui.sidebar.inert, false);
  assert.equal(ui.main.inert, true);
  assert.equal(ui.link.focused, true);
  assert.equal(ui.menu.attrs['aria-expanded'], 'true');
  ui.menu.focused = false;
  ui.listeners.keydown({ key: 'Escape' });
  assert.equal(ui.sidebar.inert, true);
  assert.equal(ui.main.inert, false);
  assert.equal(ui.menu.focused, true);
  assert.equal(ui.storage.get('schoolRepository.sidebarCollapsed'), '1');
});

test('crossing the breakpoint clears the mobile overlay and restores desktop preferences', () => {
  const ui = setup({ desktop: false, saved: '1' });
  ui.menu.events.click();
  ui.media.matches = true;
  ui.media.onChange();
  assert.equal(ui.classes.has('sidebar-mobile-open'), false);
  assert.equal(ui.classes.has('sidebar-collapsed'), true);
  assert.equal(ui.main.inert, false);
  assert.equal(ui.sidebar.inert, false);
});

test('backdrop and navigation links close the mobile drawer', () => {
  const ui = setup({ desktop: false });
  for (const target of [ui.backdrop, ui.link]) {
    ui.menu.events.click();
    target.events.click();
    assert.equal(ui.classes.has('sidebar-mobile-open'), false);
    assert.equal(ui.main.inert, false);
  }
});
