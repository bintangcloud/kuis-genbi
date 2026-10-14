import { useEffect, useState } from 'react';
import { createQuestion, deleteQuestion, fetchQuestions, login, logout, me, updateQuestion } from '../api';

const TOKEN_KEY = 'genbi_token';
const emptyForm = { id: '', q: '', a: '', b: '', ans: '0', ex: '' };

export default function Admin() {
  const [token, setToken] = useState(() => localStorage.getItem(TOKEN_KEY));
  const [user, setUser] = useState(null);
  const [list, setList] = useState([]);
  const [form, setForm] = useState(emptyForm);
  const [loginForm, setLoginForm] = useState({ email: '', password: '' });
  const [msg, setMsg] = useState({ login: '', dash: '', form: '' });
  const [booting, setBooting] = useState(true);

  const say = (k, text, ok = false) => setMsg((m) => ({ ...m, [k]: text ? { text, ok } : '' }));

  async function load(t) {
    const data = await fetchQuestions();
    setList(Array.isArray(data) ? data : []);
  }

  useEffect(() => {
    (async () => {
      if (!token) {
        setBooting(false);
        return;
      }
      try {
        const u = await me(token);
        setUser(u);
        await load(token);
      } catch {
        localStorage.removeItem(TOKEN_KEY);
        setToken(null);
      } finally {
        setBooting(false);
      }
    })();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  async function doLogin(e) {
    e.preventDefault();
    say('login', '');
    try {
      const data = await login(loginForm.email, loginForm.password);
      localStorage.setItem(TOKEN_KEY, data.token);
      setToken(data.token);
      setUser(data.user);
      setLoginForm({ email: '', password: '' });
      await load(data.token);
    } catch (err) {
      say('login', `❌ ${err.message}`);
    }
  }

  async function doLogout() {
    try {
      if (token) await logout(token);
    } catch {
      /* abaikan */
    }
    localStorage.removeItem(TOKEN_KEY);
    setToken(null);
    setUser(null);
    setList([]);
  }

  async function save(e) {
    e.preventDefault();
    say('form', '');
    const payload = { q: form.q, a: form.a, b: form.b, ans: Number(form.ans), ex: form.ex || null };
    try {
      if (form.id) await updateQuestion(token, form.id, payload);
      else await createQuestion(token, payload);
      say('form', '✅ Tersimpan.', true);
      setForm(emptyForm);
      await load(token);
    } catch (err) {
      say('form', `❌ ${err.message}`);
    }
  }

  async function remove(id) {
    if (!confirm(`Hapus soal #${id}?`)) return;
    try {
      await deleteQuestion(token, id);
      say('dash', '✅ Soal dihapus.', true);
      await load(token);
    } catch (err) {
      say('dash', `❌ ${err.message}`);
    }
  }

  const Msg = ({ v }) =>
    v ? (
      <div className={`mt-2 rounded-xl border p-2 text-sm font-bold ${v.ok ? 'border-green-600 bg-green-50 text-daun' : 'border-red-500 bg-red-50 text-red-700'}`}>
        {v.text}
      </div>
    ) : null;

  const input =
    'w-full rounded-xl border border-green-200 bg-white px-3 py-2 font-semibold focus:outline-none focus:ring-2 focus:ring-daun';

  if (booting) return <p className="p-8 text-center font-bold text-daun">Menyiapkan halaman admin... ⏳</p>;

  if (!token) {
    return (
      <div className="mx-auto w-full max-w-md rounded-3xl border border-green-100 bg-white/95 p-6 shadow-xl">
        <h2 className="text-2xl font-extrabold text-daun">🔒 Masuk Admin</h2>
        <p className="font-semibold text-stone-500">Kelola soal kuis GenBI.</p>
        <form onSubmit={doLogin} className="mt-3 flex flex-col gap-3">
          <label className="font-bold">Email
            <input type="email" required value={loginForm.email} onChange={(e) => setLoginForm({ ...loginForm, email: e.target.value })} className={input} placeholder="admin@genbi.id" />
          </label>
          <label className="font-bold">Password
            <input type="password" required value={loginForm.password} onChange={(e) => setLoginForm({ ...loginForm, password: e.target.value })} className={input} placeholder="••••••••" />
          </label>
          <button className="rounded-xl bg-daun px-5 py-2.5 font-extrabold text-white transition hover:scale-[1.02]">Masuk 🚀</button>
          <Msg v={msg.login} />
        </form>
      </div>
    );
  }

  return (
    <div className="mx-auto flex w-full max-w-4xl flex-col gap-4">
      <div className="flex items-center justify-between rounded-3xl border border-green-100 bg-white/95 p-4 shadow">
        <p className="font-bold text-daun">Halo, {user?.name} 👋</p>
        <button onClick={doLogout} className="rounded-xl bg-stone-100 px-4 py-2 font-extrabold text-daun transition hover:bg-stone-200">
          Keluar
        </button>
      </div>
      <Msg v={msg.dash} />

      <div className="overflow-x-auto rounded-3xl border border-green-100 bg-white/95 p-4 shadow">
        <h3 className="text-lg font-extrabold text-daun">📝 Daftar Soal ({list.length})</h3>
        <table className="mt-2 w-full text-left">
          <thead>
            <tr className="text-daun">
              <th className="p-2">#</th><th className="p-2">Soal</th><th className="p-2">Benar</th><th className="p-2">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {list.map((q, i) => (
              <tr key={q.id} className="border-t border-green-50 font-semibold">
                <td className="p-2">{i + 1}</td>
                <td className="p-2">
                  <b>{q.q}</b>
                  <br /><small className="text-stone-500">A. {q.a} &nbsp; B. {q.b}</small>
                </td>
                <td className="p-2"><span className="rounded-lg bg-daun px-2 py-0.5 font-extrabold text-white">{q.ans == 0 ? 'A' : 'B'}</span></td>
                <td className="p-2">
                  <div className="flex gap-2">
                    <button onClick={() => { setForm({ id: q.id, q: q.q, a: q.a, b: q.b, ans: String(q.ans), ex: q.ex || '' }); window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }); }} className="rounded-lg bg-stone-100 px-3 py-1 font-extrabold text-daun">Ubah</button>
                    <button onClick={() => remove(q.id)} className="rounded-lg bg-red-500 px-3 py-1 font-extrabold text-white">Hapus</button>
                  </div>
                </td>
              </tr>
            ))}
            {list.length === 0 && <tr><td colSpan="4" className="p-4 text-center text-stone-500">Belum ada soal. Tambah di bawah ya! 👇</td></tr>}
          </tbody>
        </table>
      </div>

      <form onSubmit={save} className="rounded-3xl border border-green-100 bg-white/95 p-4 shadow">
        <h3 className="text-lg font-extrabold text-daun">{form.id ? `✏️ Ubah Soal #${form.id}` : '➕ Tambah Soal'}</h3>
        <label className="mt-2 block font-bold">Pertanyaan
          <textarea required value={form.q} onChange={(e) => setForm({ ...form, q: e.target.value })} className={input} rows="2" />
        </label>
        <div className="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
          <label className="font-bold">Pilihan A<input required value={form.a} onChange={(e) => setForm({ ...form, a: e.target.value })} className={input} /></label>
          <label className="font-bold">Pilihan B<input required value={form.b} onChange={(e) => setForm({ ...form, b: e.target.value })} className={input} /></label>
          <label className="font-bold">Jawaban benar
            <select value={form.ans} onChange={(e) => setForm({ ...form, ans: e.target.value })} className={input}>
              <option value="0">A</option><option value="1">B</option>
            </select>
          </label>
          <label className="font-bold">Penjelasan (opsional)<input value={form.ex} onChange={(e) => setForm({ ...form, ex: e.target.value })} className={input} /></label>
        </div>
        <div className="mt-3 flex gap-2">
          <button className="rounded-xl bg-daun px-5 py-2 font-extrabold text-white">Simpan 💾</button>
          <button type="button" onClick={() => setForm(emptyForm)} className="rounded-xl bg-stone-100 px-5 py-2 font-extrabold text-daun">Batal / Baru</button>
        </div>
        <Msg v={msg.form} />
      </form>
    </div>
  );
}
