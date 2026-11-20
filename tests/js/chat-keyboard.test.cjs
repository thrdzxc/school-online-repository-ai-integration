const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

test('Enter submits chat, while Shift+Enter and composition preserve text entry', () => {
  let keydown, submissions = 0;
  const input = { addEventListener: (_, fn) => { keydown = fn; } };
  const form = {
    elements: { body: input },
    querySelector: () => ({ querySelector: () => ({}) }),
    addEventListener() {},
    requestSubmit() { submissions++; },
  };
  const box = { querySelectorAll: () => [], scrollHeight: 100 };
  const chat = { querySelector: key => key === '#chat-messages' ? box : key === '[data-chat-form]' ? form : {} };
  vm.runInNewContext(readFileSync('public/js/chat.js', 'utf8'), {
    document: { querySelector: () => chat, hidden: true, addEventListener() {} },
    window: { addEventListener() {} }, clearTimeout() {},
  });
  const enter = (extra = {}) => {
    let prevented = false;
    keydown({ key: 'Enter', preventDefault() { prevented = true; }, ...extra });
    return prevented;
  };
  assert.equal(enter(), true);
  assert.equal(submissions, 1);
  assert.equal(enter({ shiftKey: true }), false);
  assert.equal(enter({ isComposing: true }), false);
  assert.equal(submissions, 1);
});
