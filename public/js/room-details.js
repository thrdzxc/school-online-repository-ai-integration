(() => {
  const script = document.querySelector('[data-room-details-url]');
  const cards = [...document.querySelectorAll('[data-live-room]')];
  const labels = [...document.querySelectorAll('[data-room-label]')];
  const ids = [...new Set([...cards.map(node => node.dataset.liveRoom), ...labels.map(node => node.dataset.roomLabel)])];
  if (!script || !ids.length) return;
  let timer, busy = false, stopped = false;
  function update(room) {
    cards.filter(card => card.dataset.liveRoom === String(room.id)).forEach(card => {
      card.querySelectorAll('[data-room-field]').forEach(node => {
        const field = node.dataset.roomField;
        const value = room[field] ?? '';
        node.textContent = field === 'section' ? (value || 'No section') : value;
        if (field === 'physical_room') node.hidden = !value;
      });
      card.querySelectorAll('[data-room-optional]').forEach(node => {
        node.hidden = !room[node.dataset.roomOptional];
      });
    });
    labels.filter(node => node.dataset.roomLabel === String(room.id)).forEach(node => {
      node.textContent = room.name + (room.section ? ` · ${room.section}` : '');
    });
  }
  async function refresh() {
    clearTimeout(timer);
    if (busy || stopped || document.hidden) return;
    busy = true;
    try {
      for (let offset = 0; offset < ids.length; offset += 100) {
        const url = new URL(script.dataset.roomDetailsUrl, window.location.href);
        ids.slice(offset, offset + 100).forEach(id => url.searchParams.append('ids[]', id));
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 10000);
        try {
          const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store', signal: controller.signal });
          if ([401, 403, 419].includes(response.status) || response.redirected) { stopped = true; break; }
          if (!response.ok) break;
          const data = await response.json();
          data.rooms.forEach(update);
        } finally { clearTimeout(timeout); }
      }
    } catch (_) {
      // Keep the last confirmed details visible and retry after a connection failure.
    } finally {
      busy = false;
      if (!stopped && !document.hidden) timer = setTimeout(refresh, 5000);
    }
  }
  document.addEventListener('visibilitychange', () => document.hidden ? clearTimeout(timer) : refresh());
  window.addEventListener('focus', refresh);
  window.addEventListener('online', refresh);
  window.addEventListener('pagehide', () => clearTimeout(timer));
  window.addEventListener('pageshow', refresh);
  refresh();
})();
