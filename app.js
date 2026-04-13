const state = { plans: [], filtered: [], deleteId: null, step: 1 };

const qs = (s) => document.querySelector(s);
const tableBody = qs('#tableBody');
const searchInput = qs('#searchInput');
const monthFilter = qs('#monthFilter');
const skeleton = qs('#skeleton');
const form = qs('#planForm');

const toast = (msg, type = 'ok') => {
  const host = qs('#toastHost');
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.textContent = msg;
  host.appendChild(el);
  setTimeout(() => el.remove(), 2200);
};

const api = async (action, options = {}) => {
  const res = await fetch(`api.php?api=${encodeURIComponent(action)}`, options);
  const json = await res.json();
  if (!json.ok) throw new Error(json.message || 'Request failed');
  return json;
};

const esc = (v = '') => String(v).replace(/[&<>'"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));

const rowMarkup = (r) => `
<tr data-id="${r.id}">
  <td><span class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>${esc(r.week)}</td>
  <td>${esc(r.total_period)}</td>
  <td contenteditable="true" data-field="subject">${esc(r.subject)}</td>
  <td>${esc(r.lesson_name)}</td>
  <td>${esc(r.lesson_details)}</td>
  <td>${esc(r.activities)}</td>
  <td>${esc(r.smart_date || '')}</td>
  <td>${esc(r.exam_date || '')}</td>
  <td><div class="action-group"><button class="text-btn edit-btn" data-edit="${r.id}">Edit</button><button class="text-btn delete-btn" data-delete="${r.id}">Delete</button></div></td>
</tr>`;

const render = () => {
  const q = searchInput.value.trim().toLowerCase();
  state.filtered = state.plans.filter((r) => JSON.stringify(r).toLowerCase().includes(q));

  tableBody.innerHTML = state.filtered.length
    ? state.filtered.map(rowMarkup).join('')
    : '<tr><td colspan="9">No plans found.</td></tr>';

  qs('#totalPlans').textContent = String(state.filtered.length);
  qs('#activeMonth').textContent = monthFilter.value || 'All Months';
  qs('#subjectCount').textContent = String(new Set(state.filtered.map((p) => p.subject).filter(Boolean)).size);
};

const fetchPlans = async () => {
  skeleton.innerHTML = '<div class="skeleton"></div><div class="skeleton"></div><div class="skeleton"></div>';
  try {
    const query = new URLSearchParams({ month: monthFilter.value, search: searchInput.value });
    const res = await fetch(`api.php?api=list&${query.toString()}`);
    const json = await res.json();
    state.plans = json.data || [];
    render();
  } catch (e) {
    toast(e.message, 'err');
  } finally {
    skeleton.innerHTML = '';
  }
};

const setStep = (target) => {
  state.step = Math.max(1, Math.min(3, target));
  document.querySelectorAll('.step').forEach((s) => s.classList.remove('active'));
  qs(`.step[data-step="${state.step}"]`).classList.add('active');
};

const openModal = (title, item = null) => {
  qs('#modalTitle').textContent = title;
  form.reset();
  qs('#editId').value = item?.id || '';
  if (item) {
    form.month.value = item.month_name || '';
    form.ustad.value = item.ustad_name || '';
    form.week.value = item.week || '';
    form.period.value = item.total_period || '';
    form.subject.value = item.subject || '';
    form.lesson.value = item.lesson_name || '';
    form.details.value = item.lesson_details || '';
    form.activity.value = item.activities || '';
    form.smart.value = item.smart_date || '';
    form.exam.value = item.exam_date || '';
  }
  setStep(1);
  qs('#planModal').classList.add('open');
};

const closeModal = () => qs('#planModal').classList.remove('open');
const openConfirm = (id) => {
  state.deleteId = id;
  qs('#confirmModal').classList.add('open');
};
const closeConfirm = () => qs('#confirmModal').classList.remove('open');

const loadSelects = async () => {
  const [ustads, subjects] = await Promise.all([api('ustads'), api('subjects')]);
  qs('#ustadSelect').innerHTML = '<option value="">Select Ustad</option>' + ustads.data.map((u) => `<option>${esc(u.name)}</option>`).join('');
  qs('#subjectSelect').innerHTML = '<option value="">Select Subject</option>' + subjects.data.map((s) => `<option>${esc(s.name)}</option>`).join('');
};

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  try {
    const fd = new FormData(form);
    await api('save', { method: 'POST', body: fd });
    toast('Plan saved');
    closeModal();
    await fetchPlans();
  } catch (err) {
    toast(err.message, 'err');
  }
});

document.addEventListener('keydown', (e) => {
  if (e.ctrlKey && e.key === 'Enter' && qs('#planModal').classList.contains('open')) {
    form.requestSubmit();
  }
});

tableBody.addEventListener('click', (e) => {
  const edit = e.target.closest('[data-edit]');
  const del = e.target.closest('[data-delete]');
  if (edit) {
    const item = state.plans.find((r) => String(r.id) === edit.dataset.edit);
    openModal('Edit Plan', item);
  }
  if (del) openConfirm(del.dataset.delete);
});

tableBody.addEventListener('blur', async (e) => {
  const cell = e.target.closest('[data-field="subject"]');
  if (!cell) return;
  const tr = cell.closest('tr');
  const fd = new FormData();
  fd.append('id', tr.dataset.id);
  fd.append('subject', cell.textContent.trim());
  try {
    await api('save', { method: 'POST', body: fd });
    toast('Subject updated');
  } catch (err) {
    toast(err.message, 'err');
  }
}, true);

qs('#confirmDeleteBtn').addEventListener('click', async () => {
  if (!state.deleteId) return;
  try {
    const fd = new FormData();
    fd.append('id', state.deleteId);
    await api('delete', { method: 'POST', body: fd });
    toast('Plan deleted');
    closeConfirm();
    await fetchPlans();
  } catch (err) {
    toast(err.message, 'err');
  }
});

qs('[data-close-confirm]').addEventListener('click', closeConfirm);
qs('[data-close-modal]').addEventListener('click', closeModal);
qs('#newPlanBtn').addEventListener('click', () => openModal('Create Plan'));
qs('#prevStep').addEventListener('click', () => setStep(state.step - 1));
qs('#nextStep').addEventListener('click', () => setStep(state.step + 1));
searchInput.addEventListener('input', render);
monthFilter.addEventListener('change', fetchPlans);
qs('#themeToggle').addEventListener('change', (e) => {
  document.documentElement.dataset.theme = e.target.checked ? 'dark' : 'light';
});

new Sortable(tableBody, {
  handle: '.drag-handle',
  animation: 150,
  onEnd: async () => {
    const rows = [...tableBody.querySelectorAll('tr[data-id]')].map((tr) => Number(tr.dataset.id));
    try {
      await api('reorder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ rows }),
      });
      toast('Order updated');
      await fetchPlans();
    } catch (err) {
      toast(err.message, 'err');
    }
  },
});

(async () => {
  await loadSelects();
  await fetchPlans();
})();
