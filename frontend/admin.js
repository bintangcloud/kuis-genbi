const API_BASE = (window.API_BASE || 'http://localhost:8000').replace(/\/$/, '');
const TOKEN_KEY = 'genbi_token';
const $ = (id) => document.getElementById(id);

const getToken = () => localStorage.getItem(TOKEN_KEY);
const authHeaders = () => ({ 'Content-Type': 'application/json', Authorization: `Bearer ${getToken()}` });

function msg(el, text, ok = false) {
  el.innerHTML = text ? `<div class="${ok ? 'ok' : 'err'}">${text}</div>` : '';
}

// ---------- LOGIN ----------
$('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  msg($('loginMsg'), '');
  try {
    const res = await fetch(`${API_BASE}/api/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: $('email').value, password: $('password').value }),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || `HTTP ${res.status}`);
    localStorage.setItem(TOKEN_KEY, data.token);
    $('password').value = '';
    await boot();
  } catch (err) {
    msg($('loginMsg'), `❌ ${err.message}`);
  }
});

// ---------- BOOT / ME ----------
async function boot() {
  if (!getToken()) return showLogin();
  try {
    const res = await fetch(`${API_BASE}/api/me`, { headers: authHeaders() });
    if (res.status === 401) { localStorage.removeItem(TOKEN_KEY); return showLogin(); }
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const user = await res.json();
    $('hello').textContent = `Halo, ${user.name} (${user.email})`;
    showDash();
    await loadQuestions();
  } catch (err) {
    msg($('loginMsg'), `❌ Tidak bisa menghubungi API: ${err.message}`);
    showLogin();
  }
}

function showLogin() { $('loginCard').classList.remove('hidden'); $('dash').classList.add('hidden'); }
function showDash() { $('loginCard').classList.add('hidden'); $('dash').classList.remove('hidden'); }

$('logoutBtn').addEventListener('click', async () => {
  try { await fetch(`${API_BASE}/api/logout`, { method: 'POST', headers: authHeaders() }); } catch (_) {}
  localStorage.removeItem(TOKEN_KEY);
  showLogin();
});

// ---------- CRUD ----------
async function loadQuestions() {
  const res = await fetch(`${API_BASE}/api/questions`, { headers: authHeaders() });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const list = await res.json();
  $('qTable').innerHTML = list.map((q, i) => `
    <tr>
      <td>${i + 1}</td>
      <td><strong>${escapeHtml(q.q)}</strong><br><small>A. ${escapeHtml(q.a)} &nbsp; B. ${escapeHtml(q.b)}</small></td>
      <td><span class="badge">${q.ans == 0 ? 'A' : 'B'}</span></td>
      <td><div class="actions">
        <button class="btn btn-ghost" data-edit="${q.id}">Ubah</button>
        <button class="btn btn-danger" data-del="${q.id}">Hapus</button>
      </div></td>
    </tr>`).join('') || '<tr><td colspan="4">Belum ada soal.</td></tr>';

  $('qTable').querySelectorAll('[data-edit]').forEach((b) =>
    b.addEventListener('click', () => fillForm(list.find((q) => q.id == b.dataset.edit))));
  $('qTable').querySelectorAll('[data-del]').forEach((b) =>
    b.addEventListener('click', () => delQuestion(b.dataset.del)));
}

function fillForm(q) {
  $('qId').value = q.id; $('fq').value = q.q; $('fa').value = q.a;
  $('fb').value = q.b; $('fans').value = String(q.ans); $('fex').value = q.ex || '';
  $('formTitle').textContent = `✏️ Ubah Soal #${q.id}`;
  window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}

function resetForm() {
  $('qForm').reset(); $('qId').value = '';
  $('formTitle').textContent = '➕ Tambah Soal';
}
$('resetBtn').addEventListener('click', resetForm);

$('qForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  msg($('formMsg'), '');
  const payload = {
    q: $('fq').value, a: $('fa').value, b: $('fb').value,
    ans: Number($('fans').value), ex: $('fex').value || null,
  };
  const id = $('qId').value;
  try {
    const res = await fetch(`${API_BASE}/api/questions${id ? `/${id}` : ''}`, {
      method: id ? 'PUT' : 'POST',
      headers: authHeaders(),
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || JSON.stringify(data.errors) || `HTTP ${res.status}`);
    msg($('formMsg'), '✅ Tersimpan.', true);
    resetForm();
    await loadQuestions();
  } catch (err) {
    msg($('formMsg'), `❌ ${err.message}`);
  }
});

async function delQuestion(id) {
  if (!confirm(`Hapus soal #${id}?`)) return;
  const res = await fetch(`${API_BASE}/api/questions/${id}`, { method: 'DELETE', headers: authHeaders() });
  if (!res.ok) { msg($('dashMsg'), '❌ Gagal menghapus.'); return; }
  msg($('dashMsg'), '✅ Soal dihapus.', true);
  await loadQuestions();
}

function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

boot();
