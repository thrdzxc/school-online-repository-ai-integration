const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

test('live classroom updates replace text and optional details without replacing cards or selections', async () => {
  const fields = ['name', 'section', 'physical_room', 'schedule'].map(field => ({ dataset: { roomField: field }, textContent: 'Old', hidden: false }));
  const optional = { dataset: { roomOptional: 'schedule' }, hidden: false };
  const card = { dataset: { liveRoom: '7' }, querySelectorAll: key => key === '[data-room-field]' ? fields : [optional] };
  const label = { dataset: { roomLabel: '7' }, textContent: 'Old room', selected: true, value: '7' };
  const timers = [];
  let payload = { id: 7, name: 'EM', section: '3-C', physical_room: 'EM19', schedule: 'Friday' };
  vm.runInNewContext(fs.readFileSync('public/js/room-details.js', 'utf8'), {
    document: { hidden: false, querySelector: () => ({ dataset: { roomDetailsUrl: '/workspaces/student/classroom-details' } }), querySelectorAll: key => key === '[data-live-room]' ? [card] : [label], addEventListener() {} },
    window: { location: { href: 'https://school.test/workspaces/student/student/rooms' }, addEventListener() {} },
    URL, AbortController,
    setTimeout(fn, delay) { if (delay === 5000) timers.push(fn); return 1; }, clearTimeout() {},
    fetch: async url => {
      assert.equal(url.pathname, '/workspaces/student/classroom-details');
      assert.equal(url.searchParams.get('ids[]'), '7');
      return { ok: true, status: 200, redirected: false, json: async () => ({ rooms: [payload] }) };
    },
  });
  await new Promise(resolve => setImmediate(resolve));
  assert.deepEqual(fields.map(node => node.textContent), ['EM', '3-C', 'EM19', 'Friday']);
  assert.equal(label.value, '7');
  assert.equal(label.selected, true);
  payload = { ...payload, section: null, physical_room: null, schedule: null };
  await timers.shift()();
  assert.equal(fields[1].textContent, 'No section');
  assert.equal(fields[2].hidden, true);
  assert.equal(optional.hidden, true);
  assert.equal(label.textContent, 'EM');
});
