(() => {
  const chat = document.querySelector('[data-chat]');
  if (!chat) return;
  const box = chat.querySelector('#chat-messages');
  const form = chat.querySelector('[data-chat-form]');
  const input = form.elements.body;
  const button = form.querySelector('[data-chat-send]');
  const buttonLabel = button.querySelector('span');
  const status = chat.querySelector('[data-chat-connection]');
  const error = chat.querySelector('#chat-error');
  const seen = new Set([...box.querySelectorAll('[data-message-id]')].map(row => Number(row.dataset.messageId)));
  let cursor = Math.max(0, ...seen);
  let refreshing = false;
  let sending = false;
  let stopped = false;
  let timer;

  const connection = (text, state) => {
    status.textContent = text;
    status.dataset.state = state;
  };
  const showError = (text = '') => {
    error.textContent = text;
    error.hidden = !text;
  };
  const append = (items, forceScroll = false) => {
    const nearBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 100;
    for (const message of items) {
      if (seen.has(message.id)) continue;
      box.querySelector('.empty')?.remove();
      const row = document.createElement('div');
      row.className = `message-row${message.mine ? ' mine' : ''}`;
      row.dataset.messageId = message.id;
      const bubble = document.createElement('div');
      bubble.className = 'message-bubble';
      const body = document.createElement('div');
      body.className = 'message-body';
      body.textContent = message.body;
      const meta = document.createElement('div');
      meta.className = 'message-meta';
      meta.textContent = `${message.sender_name} · ${message.time}`;
      bubble.append(body, meta);
      row.append(bubble);
      const next = [...box.children].find(item => Number(item.dataset.messageId) > message.id);
      box.insertBefore(row, next || null);
      seen.add(message.id);
    }
    if (nearBottom || forceScroll) box.scrollTop = box.scrollHeight;
  };

  async function request(url, options = {}) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);
    try {
      const response = await fetch(url, {
        ...options, signal: controller.signal, credentials: 'same-origin', cache: 'no-store',
        headers: { Accept: 'application/json', ...options.headers },
      });
      if (response.status === 401 || response.status === 419 || response.redirected) {
        stopped = true;
        throw new Error('Your session expired. Copy your draft, then reload and sign in again.');
      }
      if (response.status === 403 || response.status === 404) {
        stopped = true;
        throw new Error('This conversation is no longer available to your account.');
      }
      let data;
      try {
        data = await response.json();
      } catch (_) {
        throw new Error('The chat server could not confirm this request. Your draft is still here; check the conversation before trying again.');
      }
      if (!response.ok) {
        throw new Error(data.errors?.body?.[0] || 'Unable to complete the request. Please try again.');
      }
      return data;
    } finally {
      clearTimeout(timeout);
    }
  }

  async function refresh() {
    clearTimeout(timer);
    if (refreshing || stopped || document.hidden) return;
    refreshing = true;
    let hasMore = false;
    try {
      const url = new URL(chat.dataset.messagesUrl, window.location.href);
      url.searchParams.set('after', cursor);
      const data = await request(url);
      append(data.messages);
      for (const message of data.messages) cursor = Math.max(cursor, message.id);
      hasMore = data.has_more;
      connection('Connected', 'connected');
    } catch (failure) {
      connection(stopped ? 'Chat unavailable' : 'Reconnecting…', 'error');
      if (stopped) showError(failure.message);
    } finally {
      refreshing = false;
      if (!stopped && !document.hidden) timer = setTimeout(refresh, hasMore ? 100 : 4000);
    }
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (sending) return;
    if (stopped) {
      showError('Copy your draft, then reload this page to reconnect.');
      return;
    }
    if (!input.value.trim()) {
      showError('Type a message before sending.');
      input.focus({ preventScroll: true });
      return;
    }
    const draft = input.value;
    sending = true;
    form.setAttribute('aria-busy', 'true');
    button.disabled = true;
    buttonLabel.textContent = 'Sending…';
    showError();
    try {
      const data = await request(form.action, { method: 'POST', body: new FormData(form) });
      append([data.message], true);
      if (input.value === draft) input.value = '';
      // Only polling advances the cursor, so concurrent incoming messages cannot be skipped.
      refresh();
      input.focus({ preventScroll: true });
    } catch (failure) {
      showError(stopped ? failure.message :
        (failure.name === 'AbortError' || failure instanceof TypeError
          ? 'Delivery could not be confirmed. Your draft is saved here. Check the conversation before sending again.'
          : failure.message));
    } finally {
      sending = false;
      form.setAttribute('aria-busy', 'false');
      button.disabled = false;
      buttonLabel.textContent = 'Send message';
    }
  });
  input.addEventListener('keydown', event => {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
      event.preventDefault();
      form.requestSubmit();
    }
  });
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) clearTimeout(timer);
    else refresh();
  });
  window.addEventListener('online', refresh);
  window.addEventListener('pagehide', () => clearTimeout(timer));
  window.addEventListener('pageshow', refresh);
  box.scrollTop = box.scrollHeight;
  refresh();
})();
