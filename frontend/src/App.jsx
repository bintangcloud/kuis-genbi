import { useEffect, useState } from 'react';
import Decor from './components/Decor';
import Game from './components/Game';
import Admin from './components/Admin';

function viewFromHash() {
  return window.location.hash === '#/admin' ? 'admin' : 'game';
}

export default function App() {
  const [view, setView] = useState(viewFromHash);

  useEffect(() => {
    const onHash = () => setView(viewFromHash());
    window.addEventListener('hashchange', onHash);
    return () => window.removeEventListener('hashchange', onHash);
  }, []);

  const go = (v) => {
    window.location.hash = v === 'admin' ? '#/admin' : '#/';
  };

  return (
    <div className="relative min-h-screen bg-gradient-to-br from-green-50 via-amber-50 to-emerald-100 font-kids text-tinta">
      <Decor />

      <div className="relative z-10 flex min-h-screen flex-col">
        {/* Navigasi atas */}
        <nav className="mx-auto flex w-full max-w-5xl items-center justify-between px-4 pt-4">
          <div className="flex gap-2 rounded-full bg-white/70 p-1 shadow backdrop-blur">
            <button
              onClick={() => go('game')}
              className={`rounded-full px-4 py-1.5 text-sm font-extrabold transition ${view === 'game' ? 'bg-daun text-white shadow' : 'text-daun hover:bg-white'}`}
            >
              🎮 Main
            </button>
            <button
              onClick={() => go('admin')}
              className={`rounded-full px-4 py-1.5 text-sm font-extrabold transition ${view === 'admin' ? 'bg-daun text-white shadow' : 'text-daun hover:bg-white'}`}
            >
              ⚙️ Admin
            </button>
          </div>
          <span className="hidden rounded-full bg-white/70 px-3 py-1 text-xs font-bold text-stone-500 shadow backdrop-blur sm:block">
            ✨ GenBI Undiksha ✨
          </span>
        </nav>

        {/* Konten boleh di-scroll — tidak dikunci setinggi layar */}
        <main className="flex flex-1 flex-col py-6">
          {view === 'game' ? <Game /> : <Admin />}
        </main>

        <footer className="border-t border-white/60 bg-white/50 px-4 py-4 text-center text-sm font-bold text-tinta backdrop-blur">
          ✨ GenBI Undiksha Edukasi Interaktif ✨
          <br />
          {view === 'game' ? (
            <button onClick={() => go('admin')} className="mt-1 inline-block rounded-full bg-white/80 px-4 py-1 font-extrabold text-daun shadow transition hover:bg-daun hover:text-white">
              🔒 Login Admin
            </button>
          ) : (
            <button onClick={() => go('game')} className="mt-1 inline-block rounded-full bg-white/80 px-4 py-1 font-extrabold text-daun shadow transition hover:bg-daun hover:text-white">
              🎮 Kembali ke Game
            </button>
          )}
        </footer>
      </div>
    </div>
  );
}
