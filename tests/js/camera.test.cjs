const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function setup(getMedia) {
  const nodes = {};
  for (const id of ['open-camera', 'photo-camera', 'camera-video', 'camera-preview', 'capture-photo', 'retake-photo', 'use-photo', 'camera-error', 'camera-status', 'profile_photo', 'cancel-camera']) {
    nodes[id] = {
      events: {}, hidden: false, textContent: '',
      addEventListener(name, fn) { this.events[name] = fn; },
      dispatchEvent(event) { this.events[event.type]?.(event); },
      removeAttribute(key) { delete this[key]; },
      focus() {},
    };
  }
  const dialog = nodes['photo-camera'];
  dialog.showModal = () => { dialog.open = true; };
  dialog.close = () => { dialog.open = false; dialog.events.close(); };
  const video = nodes['camera-video'];
  video.play = async () => {};
  video.getBoundingClientRect = () => ({ width: 600, height: 400 });
  video.videoWidth = 1280;
  video.videoHeight = 960;
  let stops = 0;
  const stream = { getTracks: () => [{ stop() { stops++; } }] };
  nodes.profile_photo.form = { querySelector: () => null };
  const urls = new Set();
  vm.runInNewContext(fs.readFileSync('public/js/camera.js', 'utf8'), {
    document: {
      getElementById: id => nodes[id],
      createElement: () => ({ getContext: () => ({ drawImage() {} }), toBlob: fn => fn({ type: 'image/jpeg' }) }),
    },
    navigator: { mediaDevices: { getUserMedia: getMedia || (async () => stream) } },
    window: { addEventListener() {} },
    URL: { createObjectURL() { urls.add('blob:photo'); return 'blob:photo'; }, revokeObjectURL(url) { urls.delete(url); } },
    DataTransfer: class { constructor() { this.files = []; this.items = { add: file => this.files.push(file) }; } },
    File: class { constructor(parts, name, opts) { this.name = name; this.type = opts.type; } },
    Event: class { constructor(type) { this.type = type; } },
  });
  return { nodes, stream, urls, stops: () => stops, click: id => nodes[id].events.click() };
}
const settle = () => new Promise(resolve => setImmediate(resolve));

test('camera requires confirmation, supports retake, and attaches a JPEG only after Use photo', async () => {
  const app = setup();
  app.click('open-camera');
  await settle();
  assert.equal(app.nodes['capture-photo'].disabled, false);
  app.click('capture-photo');
  assert.equal(app.stops(), 1);
  assert.equal(app.nodes.profile_photo.files, undefined);
  assert.equal(app.nodes['camera-preview'].hidden, false);
  await app.click('retake-photo');
  assert.equal(app.urls.size, 0);
  assert.equal(app.nodes['camera-preview'].hidden, true);
  app.click('capture-photo');
  app.click('use-photo');
  assert.equal(app.nodes.profile_photo.files[0].name, 'camera-photo.jpg');
  assert.equal(app.nodes.profile_photo.files[0].type, 'image/jpeg');
  assert.equal(app.nodes['photo-camera'].open, false);
  assert.equal(app.urls.size, 0);
});

test('cancel preserves an existing upload and stops the camera', async () => {
  const app = setup();
  const original = [{ name: 'existing.png' }];
  app.nodes.profile_photo.files = original;
  app.click('open-camera');
  await settle();
  app.click('cancel-camera');
  assert.equal(app.stops(), 1);
  assert.equal(app.nodes.profile_photo.files, original);
});

test('permission denied presents a recoverable error without attaching a photo', async () => {
  const app = setup(async () => { throw { name: 'NotAllowedError' }; });
  app.click('open-camera');
  await settle();
  assert.match(app.nodes['camera-error'].textContent, /permission was denied/);
  assert.equal(app.nodes.profile_photo.files, undefined);
  assert.equal(app.nodes['retake-photo'].hidden, false);
});

test('a late permission response after cancellation stops the returned stream', async () => {
  let resolve;
  const app = setup(() => new Promise(done => { resolve = done; }));
  app.click('open-camera');
  app.click('cancel-camera');
  resolve(app.stream);
  await settle();
  assert.equal(app.stops(), 1);
  assert.equal(app.nodes['camera-video'].srcObject, null);
});
