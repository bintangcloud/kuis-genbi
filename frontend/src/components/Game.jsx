import { useEffect, useRef, useState } from 'react';
import { FilesetResolver, HandLandmarker } from '@mediapipe/tasks-vision';
import { fetchQuestions } from '../api';

const ANSWER_SECONDS = 30;
const HOVER_MAX = 40;
const FALLBACK = [
  {
    q: 'Belum ada soal! Kakak GenBI harus menambahkan soal dulu.',
    a: 'Siap',
    b: 'Oke',
    ans: 0,
    ex: 'Silakan login ke menu admin.',
  },
];

export default function Game() {
  const [phase, setPhase] = useState('home'); // home | quiz | result
  const [questions, setQuestions] = useState(null); // null = masih loading
  const [loadError, setLoadError] = useState(false);
  const [idx, setIdx] = useState(0);
  const [score, setScore] = useState(0);
  const [timeLeft, setTimeLeft] = useState(ANSWER_SECONDS);
  const [answer, setAnswer] = useState(null); // {picked, correct, timedOut}
  const [fx, setFx] = useState(null); // {text, kind}
  const [hoverZone, setHoverZone] = useState(null);
  const [camState, setCamState] = useState('off'); // off | loading | on | failed
  const [camMsg, setCamMsg] = useState('');

  const videoRef = useRef(null);
  const canvasRef = useRef(null);
  const barARef = useRef(null);
  const barBRef = useRef(null);
  const audioRef = useRef({ benar: null, salah: null });

  // State mutable untuk loop kamera & timer (hindari stale closure).
  const S = useRef({
    idx: 0,
    score: 0,
    answered: false,
    time: ANSWER_SECONDS,
    timerId: null,
    hoverTarget: null,
    hoverProgress: 0,
    cameraOn: false,
    hand: null,
    stream: null,
    raf: null,
    lastVideo: -1,
    questions: [],
  });

  useEffect(() => {
    let alive = true;
    fetchQuestions()
      .then((data) => {
        if (!alive) return;
        const list = Array.isArray(data) && data.length > 0 ? data : FALLBACK;
        if (!Array.isArray(data) || data.length === 0) setLoadError(true);
        S.current.questions = list;
        setQuestions(list);
      })
      .catch(() => {
        if (!alive) return;
        setLoadError(true);
        S.current.questions = FALLBACK;
        setQuestions(FALLBACK);
      });
    return () => {
      alive = false;
      stopAll();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  function clearTimer() {
    if (S.current.timerId) clearInterval(S.current.timerId);
    S.current.timerId = null;
  }

  function stopAll() {
    const s = S.current;
    clearTimer();
    s.cameraOn = false;
    if (s.raf) cancelAnimationFrame(s.raf);
    s.raf = null;
    if (s.stream) {
      s.stream.getTracks().forEach((t) => t.stop());
      s.stream = null;
    }
    if (s.hand) {
      try {
        s.hand.close();
      } catch {
        /* abaikan */
      }
      s.hand = null;
    }
  }

  function setBar(which, pct) {
    const el = which === 0 ? barARef.current : barBRef.current;
    if (el) el.style.width = `${pct}%`;
  }

  function startTimer() {
    clearTimer();
    S.current.time = ANSWER_SECONDS;
    setTimeLeft(ANSWER_SECONDS);
    S.current.timerId = setInterval(() => {
      const s = S.current;
      if (s.answered) {
        clearTimer();
        return;
      }
      s.time -= 1;
      setTimeLeft(s.time);
      if (s.time <= 0) {
        clearTimer();
        if (!s.answered) processAnswer(-1);
      }
    }, 1000);
  }

  function resetLoop() {
    const s = S.current;
    s.answered = false;
    s.hoverTarget = null;
    s.hoverProgress = 0;
    setBar(0, 0);
    setBar(1, 0);
    setHoverZone(null);
    setAnswer(null);
    setFx(null);
    startTimer();
  }

  function startGame() {
    // Audio dibuat saat gesture user agar tidak diblokir browser.
    if (!audioRef.current.benar) {
      audioRef.current.benar = new Audio('benar.mp3');
      audioRef.current.salah = new Audio('salah.mp3');
    }
    S.current.idx = 0;
    S.current.score = 0;
    setIdx(0);
    setScore(0);
    setPhase('quiz');
    if (!S.current.cameraOn) enableCamera();
    else resetLoop();
  }

  function processAnswer(picked) {
    const s = S.current;
    if (s.answered) return;
    s.answered = true;
    clearTimer();
    const q = s.questions[s.idx];
    const correct = picked == q.ans;

    let newScore = s.score;
    if (correct) {
      newScore += 100;
      s.score = newScore;
      setScore(newScore);
    }
    setAnswer({ picked, correct });

    if (picked < 0) {
      audioRef.current.salah?.play().catch(() => {});
      setFx({ text: '⏰ WAKTU HABIS!', kind: 'salah' });
    } else if (correct) {
      audioRef.current.benar?.play().catch(() => {});
      setFx({ text: '✨ BENAR! ✨', kind: 'benar' });
    } else {
      audioRef.current.salah?.play().catch(() => {});
      setFx({ text: '💦 SALAH 💦', kind: 'salah' });
    }
  }

  function nextQuestion() {
    const s = S.current;
    if (s.idx < s.questions.length - 1) {
      s.idx += 1;
      setIdx(s.idx);
      resetLoop();
    } else {
      finishGame();
    }
  }

  function finishGame() {
    clearTimer();
    stopAll();
    setPhase('result');
  }

  async function enableCamera() {
    setCamState('loading');
    setCamMsg('');
    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: 640, height: 480 },
        audio: false,
      });
      S.current.stream = stream;
      if (videoRef.current) videoRef.current.srcObject = stream;

      const vision = await FilesetResolver.forVisionTasks(
        'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/wasm',
      );
      const hand = await HandLandmarker.createFromOptions(vision, {
        baseOptions: {
          modelAssetPath:
            'https://storage.googleapis.com/mediapipe-models/hand_landmarker/hand_landmarker/float16/1/hand_landmarker.task',
          delegate: 'GPU',
        },
        runningMode: 'VIDEO',
        numHands: 1,
      });
      S.current.hand = hand;
      S.current.cameraOn = true;
      setCamState('on');
      resetLoop();
      detectLoop();
    } catch (err) {
      console.error('Kamera gagal:', err);
      setCamState('failed');
      setCamMsg('Kamera gagal diakses. Main pakai tombol manual di bawah ya!');
      setTimeout(() => {
        resetLoop();
      }, 1500);
    }
  }

  function detectLoop() {
    const s = S.current;
    if (!s.cameraOn || !s.hand) return;
    const video = videoRef.current;
    const canvas = canvasRef.current;
    if (!video || !canvas) {
      s.raf = requestAnimationFrame(detectLoop);
      return;
    }
    const ctx = canvas.getContext('2d');

    if (video.readyState >= 2 && video.currentTime !== s.lastVideo) {
      s.lastVideo = video.currentTime;
      canvas.width = video.videoWidth || 640;
      canvas.height = video.videoHeight || 480;
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      const result = s.hand.detectForVideo(video, performance.now());

      if (result.landmarks && result.landmarks.length > 0 && !s.answered) {
        const finger = result.landmarks[0][8];
        const cursorX = (1 - finger.x) * canvas.width;
        const cursorY = finger.y * canvas.height;

        ctx.beginPath();
        ctx.arc(cursorX, cursorY, 15, 0, 2 * Math.PI);
        ctx.fillStyle = '#fff';
        ctx.fill();
        ctx.lineWidth = 4;
        ctx.strokeStyle = '#4CAF50';
        ctx.stroke();

        let zone = null;
        if (cursorX < canvas.width * 0.45) zone = 0;
        else if (cursorX > canvas.width * 0.55) zone = 1;

        if (zone !== null) {
          if (s.hoverTarget === zone) {
            s.hoverProgress += 1;
            const pct = Math.min(100, (s.hoverProgress / HOVER_MAX) * 100);
            setBar(zone, pct);
            setBar(zone === 0 ? 1 : 0, 0);
            setHoverZone(zone);
            if (s.hoverProgress >= HOVER_MAX) processAnswer(zone);
          } else {
            s.hoverTarget = zone;
            s.hoverProgress = 0;
          }
        } else {
          s.hoverTarget = null;
          s.hoverProgress = 0;
          setBar(0, 0);
          setBar(1, 0);
          setHoverZone(null);
        }
      }
    }
    s.raf = requestAnimationFrame(detectLoop);
  }

  const q = questions?.[idx];
  const isLast = questions ? idx === questions.length - 1 : false;

  const optClass = (side) => {
    let cls =
      'w-[38%] rounded-2xl border-4 bg-white/90 p-3 text-center transition-all duration-300 flex flex-col gap-1 ';
    if (answer) {
      const correctSide = q.ans == 0 ? 0 : 1;
      if (side === correctSide) cls += 'border-green-600 bg-green-100 scale-105 ';
      else if (side === answer.picked) cls += 'border-red-500 bg-red-100 ';
      else cls += 'border-daun ';
    } else if (hoverZone === side) {
      cls += 'border-orange-500 scale-105 shadow-[0_0_30px_rgba(255,152,0,0.8)] ';
    } else {
      cls += 'border-daun ';
    }
    return cls;
  };

  return (
    <div className="mx-auto flex w-full max-w-5xl flex-col gap-4 px-4 pb-10">
      {/* Skor */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="animate-wiggle grid h-12 w-12 place-items-center rounded-2xl bg-daun text-2xl text-white shadow-lg">
            👆
          </div>
          <div>
            <h1 className="text-xl font-extrabold text-daun sm:text-2xl">Pahlawan Rupiah Cilik!</h1>
            <p className="text-sm font-semibold text-stone-500">Kuis Klik Udara GenBI 🪄</p>
          </div>
        </div>
        <div className="rounded-full border border-green-200 bg-white px-4 py-1.5 text-sm font-extrabold text-daun shadow">
          ⭐ {score} Poin
        </div>
      </div>

      {phase === 'home' && (
        <section className="animate-pop-in rounded-3xl border border-green-100 bg-white/95 p-8 text-center shadow-xl">
          <div className="text-6xl">🪄</div>
          <h2 className="mt-2 text-3xl font-extrabold text-daun">Kuis Ajaib GenBI!</h2>
          <p className="mx-auto mt-2 max-w-xl font-semibold text-stone-600">
            Arahkan jari telunjukmu ke layar seperti pakai tongkat sihir! Tahan jarimu di atas jawaban
            yang benar sampai lingkarannya penuh ya. 🎈
          </p>
          {loadError && (
            <div className="mx-auto mt-3 max-w-xl rounded-xl border border-red-400 bg-red-50 p-2 text-sm font-bold text-red-700">
              ⚠️ Tidak bisa mengambil soal dari server, memakai soal cadangan.
            </div>
          )}
          <button
            onClick={startGame}
            disabled={!questions}
            className="mt-5 rounded-2xl bg-daun px-8 py-3 text-lg font-extrabold text-white shadow-lg transition hover:scale-105 hover:bg-daun2 disabled:opacity-50"
          >
            {questions ? 'Mulai Kamera & Main! 🚀' : 'Menyiapkan soal... ⏳'}
          </button>
          <p className="mt-3 text-sm font-semibold text-stone-400">
            Pastikan klik "Allow/Izinkan" saat browser meminta akses kamera. 📷
          </p>
        </section>
      )}

      {phase === 'quiz' && q && (
        <section className="flex flex-col gap-3">
          <div className="text-center">
            <span className="inline-block rounded-full border border-green-200 bg-white px-4 py-1 text-sm font-extrabold text-daun shadow">
              Soal {idx + 1} / {questions.length} 📝
            </span>
            <h2 className="mt-2 text-2xl font-black text-daun sm:text-3xl">{q.q}</h2>
            <div className="mx-auto mt-2 h-3 max-w-md overflow-hidden rounded-full bg-stone-200">
              <div
                className="h-full rounded-full transition-all duration-1000"
                style={{
                  width: `${Math.max(0, (timeLeft / ANSWER_SECONDS) * 100)}%`,
                  background: timeLeft <= 10 ? '#e16a54' : '#ffd66b',
                }}
              />
            </div>
            <div className="mt-1 font-extrabold text-daun">⏱️ {timeLeft} Detik</div>
            {answer && (
              <div
                className={`mt-1 min-h-6 text-base font-bold ${answer.correct ? 'text-daun' : 'text-red-700'}`}
              >
                {answer.timedOut || answer.picked < 0 ? (
                  <>⏰ Waktu habis! Jawaban: <b>{q.ans == 0 ? 'A' : 'B'}</b>. {q.ex}</>
                ) : answer.correct ? (
                  <>🎉 <b>BENAR!</b> +100 poin. {q.ex}</>
                ) : (
                  <>💪 <b>UPS!</b> Jawaban tepat: <b>{q.ans == 0 ? 'A' : 'B'}</b>. {q.ex}</>
                )}
              </div>
            )}
          </div>

          {/* Kamera BESAR */}
          <div className="relative mx-auto aspect-video w-full overflow-hidden rounded-[2rem] border-[6px] border-white bg-black shadow-2xl">
            <div className="pointer-events-none absolute left-1/2 top-3 z-10 -translate-x-1/2 rounded-full border border-white/30 bg-daun/85 px-4 py-1 text-xs font-extrabold text-white shadow sm:text-sm">
              ✨ Arahkan Jari Telunjukmu ke Opsi A / B ✨
            </div>

            <video ref={videoRef} autoPlay playsInline muted className="mirror absolute inset-0 h-full w-full object-cover" />
            <canvas ref={canvasRef} className="mirror pointer-events-none absolute inset-0 z-[2] h-full w-full" />

            <div className="absolute inset-0 z-[3] flex items-center justify-between p-4 sm:p-6">
              <div className={optClass(0)}>
                <div className="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-daun text-xl font-black text-white">A</div>
                <div className="text-base font-extrabold sm:text-lg">{q.a}</div>
                <div className="mt-1 h-3 w-full overflow-hidden rounded-full bg-stone-300">
                  <div ref={barARef} className="h-full bg-orange-500" style={{ width: '0%' }} />
                </div>
              </div>
              <div className={optClass(1)}>
                <div className="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-daun text-xl font-black text-white">B</div>
                <div className="text-base font-extrabold sm:text-lg">{q.b}</div>
                <div className="mt-1 h-3 w-full overflow-hidden rounded-full bg-stone-300">
                  <div ref={barBRef} className="h-full bg-orange-500" style={{ width: '0%' }} />
                </div>
              </div>
            </div>

            {!answer && (
              <div className="absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 gap-3">
                <button
                  onClick={() => processAnswer(0)}
                  className="rounded-full border-2 border-daun bg-white/85 px-5 py-1.5 font-extrabold text-daun backdrop-blur transition hover:bg-daun hover:text-white"
                >
                  A (Kiri) 👈
                </button>
                <button
                  onClick={() => processAnswer(1)}
                  className="rounded-full border-2 border-daun bg-white/85 px-5 py-1.5 font-extrabold text-daun backdrop-blur transition hover:bg-daun hover:text-white"
                >
                  B (Kanan) 👉
                </button>
              </div>
            )}

            {answer && (
              <div className="animate-pop-in absolute right-4 top-14 z-30">
                <button
                  onClick={nextQuestion}
                  className="rounded-2xl bg-daun px-6 py-3 text-lg font-extrabold text-white shadow-xl transition hover:scale-105"
                >
                  {isLast ? 'Lihat Nilai 🏆' : 'Lanjut Soal ➡️'}
                </button>
              </div>
            )}

            {fx && (
              <div
                key={`${idx}-${fx.text}`}
                className={`animate-fx-float pointer-events-none absolute inset-0 z-10 flex items-center justify-center text-center text-4xl font-black sm:text-6xl ${fx.kind === 'benar' ? 'text-green-400' : 'text-red-400'}`}
                style={{ textShadow: '0 5px 20px rgba(0,0,0,0.6)' }}
              >
                {fx.text}
              </div>
            )}

            {camState === 'loading' && (
              <div className="absolute inset-0 z-50 flex items-center justify-center bg-black/70 text-xl font-bold text-white">
                Menyiapkan Kamera... ⏳
              </div>
            )}
            {camState === 'failed' && (
              <div className="absolute inset-x-0 top-12 z-40 mx-auto w-fit rounded-full bg-black/70 px-4 py-1 text-sm font-bold text-white">
                📷 {camMsg}
              </div>
            )}
          </div>
          <p className="text-center text-sm font-semibold text-stone-500">
            💡 Tips: halaman ini bisa di-scroll — tombol admin ada di bawah! 👇
          </p>
        </section>
      )}

      {phase === 'result' && (
        <section className="animate-pop-in rounded-3xl border border-green-100 bg-white/95 p-10 text-center shadow-xl">
          <div className="text-7xl">{score / 100 >= 4 ? '🦸🏅' : score / 100 >= 2 ? '🌟' : '🌱'}</div>
          <h2 className="mt-2 text-3xl font-extrabold text-daun">Kamu Hebat!</h2>
          <div className="my-2 text-6xl font-black text-daun">
            {score}/{questions.length * 100}
          </div>
          <p className="text-lg font-semibold text-stone-600">
            {score / 100 >= 4
              ? 'Wah, kamu Super Pahlawan Rupiah!'
              : score / 100 >= 2
                ? 'Hebat! Semangat belajarnya ya.'
                : 'Belajar lagi sama kakak GenBI biar makin jago!'}
          </p>
          <button
            onClick={() => setPhase('home')}
            className="mt-5 rounded-2xl bg-daun px-8 py-3 text-lg font-extrabold text-white shadow-lg transition hover:scale-105"
          >
            Main Lagi Yuk! 🎮
          </button>
        </section>
      )}
    </div>
  );
}
