// Base URL backend Laravel. Bisa dioverride saat build:
//   VITE_API_BASE=https://api-contoh.up.railway.app npm run build
export const API_BASE = (
  import.meta.env.VITE_API_BASE || 'https://kuis-genbi-production.up.railway.app'
).replace(/\/$/, '');

async function req(path, { method = 'GET', token, body } = {}) {
  const headers = { 'Content-Type': 'application/json' };
  if (token) headers.Authorization = `Bearer ${token}`;
  const res = await fetch(`${API_BASE}${path}`, {
    method,
    headers,
    body: body === undefined ? undefined : JSON.stringify(body),
  });
  let data = null;
  try {
    data = await res.json();
  } catch {
    data = null;
  }
  if (!res.ok) {
    const msg = data?.message || `HTTP ${res.status}`;
    const detail = data?.errors ? ` ${JSON.stringify(data.errors)}` : '';
    throw new Error(`${msg}${detail}`);
  }
  return data;
}

export const fetchQuestions = () => req('/api/questions');
export const login = (email, password) =>
  req('/api/login', { method: 'POST', body: { email, password } });
export const me = (token) => req('/api/me', { token });
export const logout = (token) => req('/api/logout', { method: 'POST', token });
export const createQuestion = (token, payload) =>
  req('/api/questions', { method: 'POST', token, body: payload });
export const updateQuestion = (token, id, payload) =>
  req(`/api/questions/${id}`, { method: 'PUT', token, body: payload });
export const deleteQuestion = (token, id) =>
  req(`/api/questions/${id}`, { method: 'DELETE', token });
