document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
  if (!window.confirm(form.dataset.confirm)) event.preventDefault();
}));
const filter = document.getElementById('status-filter');
if (filter) filter.addEventListener('change', () => {
  let visible = 0;
  document.querySelectorAll('[data-status]').forEach(card => {
    card.hidden = filter.value !== 'all' && filter.value !== card.dataset.status;
    if (!card.hidden) visible++;
  });
  document.getElementById('filter-empty').hidden = visible > 0;
});
if (document.querySelector('[data-live]')) {
  let version = JSON.parse(document.getElementById('initial-version').textContent);
  async function checkUpdates() {
    try {
      const response = await fetch('index.php?page=updates', {cache: 'no-store'});
      if (!response.ok) return;
      const data = await response.json();
      const next = data.version + ':' + data.unread;
      if (version && version !== next) document.getElementById('live-update').hidden = false;
      if (!version) version = next;
    } catch (_) { /* A later poll will retry when the local server returns. */ }
  }
  checkUpdates();
  setInterval(checkUpdates, 15000);
}
