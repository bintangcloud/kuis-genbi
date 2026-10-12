<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pahlawan Rupiah Cilik! | Klik Udara GenBI</title>
<style>
:root {
  --green: #174c36; --green2: #246b49; --mint: #e5f4e9;
  --yellow: #ffd66b; --ink: #203329; --muted: #64746a;
}
* { box-sizing: border-box; }
body {
  margin: 0; background: linear-gradient(135deg, #eff8ee, #fff7e8); 
  font-family: system-ui, -apple-system, sans-serif; color: var(--ink);
  height: 100vh; overflow: hidden; display: flex; flex-direction: column;
}
button { font: inherit; cursor: pointer; border: none; }
button:focus-visible { outline: 3px solid #e8a900; outline-offset: 3px; }

/* Main Container - Dibuat flex agar memenuhi layar tanpa scroll */
.app { max-width: 1000px; margin: 0 auto; padding: 15px; flex-grow: 1; display: flex; flex-direction: column; width: 100%; }
.topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.brand { display: flex; align-items: center; gap: 12px; }
.logo { width: 50px; height: 50px; border-radius: 15px; background: var(--green); display: grid; place-items: center; color: white; font-size: 25px; }
.brand h1 { font-size: 1.3rem; margin: 0; color: var(--green); }
.brand p { margin: 0; font-size: .85rem; color: var(--muted); }
.pill { background: white; border: 1px solid #dce9dd; border-radius: 999px; padding: 6px 15px; font-size: .9rem; font-weight: 700; color: var(--green); }
.panel { background: rgba(255, 255, 255, .95); border: 1px solid #e1ebdf; border-radius: 24px; box-shadow: 0 14px 36px rgba(20, 65, 44, .12); }

/* Halaman Depan */
.hero { padding: 30px; text-align: center; margin: auto; }
.hero h2 { font-size: 2.2rem; margin: 10px 0; color: var(--green); }
.btn { border-radius: 16px; padding: 12px 25px; font-weight: 800; font-size: 1.1rem; transition: transform .2s; }
.btn-primary { background: var(--green); color: white; box-shadow: 0 10px 20px rgba(23, 76, 54, 0.3); }

/* Kuis & Kamera */
.hidden { display: none !important; }
.quiz-header { text-align: center; margin-bottom: 10px; }
.question { font-size: 1.5rem; line-height: 1.2; color: var(--green); font-weight: 900; margin: 10px 0; }
.timer-bar { height: 10px; background: #e9eee7; border-radius: 99px; overflow: hidden; max-width: 400px; margin: 0 auto; }
.timer-fill { height: 100%; background: var(--yellow); width: 100%; transition: width 1s linear; }

/* Layout Kamera Dipercantik */
.camera-wrap { 
  position: relative; 
  width: 100%; 
  max-width: 800px; 
  margin: 0 auto; 
  aspect-ratio: 16/9; 
  background: #000; 
  border-radius: 28px; 
  overflow: hidden; 
  /* Efek Bingkai Estetik */
  border: 6px solid #fff; 
  box-shadow: 0 20px 50px rgba(23, 76, 54, 0.25), inset 0 0 20px rgba(0,0,0,0.5); 
}

/* Pita Hiasan di Atas Kamera */
.camera-badge {
  position: absolute;
  top: 12px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(23, 76, 54, 0.85);
  backdrop-filter: blur(8px);
  color: white;
  padding: 5px 18px;
  border-radius: 99px;
  font-size: 0.85rem;
  font-weight: 800;
  letter-spacing: 0.5px;
  z-index: 15;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  border: 1.5px solid rgba(255,255,255,0.3);
  pointer-events: none;
}

#webcam, #poseCanvas { 
  width: 100%; 
  height: 100%; 
  object-fit: cover; 
  transform: scaleX(-1); 
  position: absolute; 
  inset: 0; 
}
#poseCanvas { z-index: 2; pointer-events: none; }


/* Opsi Klik Udara */
.air-options-container { position: absolute; inset: 0; display: flex; justify-content: space-between; align-items: center; padding: 20px; z-index: 3; pointer-events: none; }
.air-option { width: 35%; background: rgba(255, 255, 255, 0.9); border: 4px solid var(--green); border-radius: 20px; padding: 15px; text-align: center; display: flex; flex-direction: column; gap: 5px; transition: all 0.3s; }
.air-option .letter { font-size: 1.5rem; font-weight: 900; color: white; background: var(--green); border-radius: 12px; width: 40px; height: 40px; display: grid; place-items: center; margin: 0 auto; }
.air-option .text { font-size: 1.1rem; font-weight: 800; }
.air-progress-bg { width: 100%; height: 12px; background: #ddd; border-radius: 10px; overflow: hidden; margin-top: 5px; }
.air-progress-fill { height: 100%; width: 0%; background: #ff9800; transition: width 0.1s; }
.air-option.hovered { transform: scale(1.05); border-color: #ff9800; box-shadow: 0 0 30px rgba(255, 152, 0, 0.8); }
.air-option.correct { background: #e2f6e8; border-color: #32935b; }
.air-option.wrong { background: #fff0ee; border-color: #d65b5b; }

/* OVERLAY: Tombol Manual (Melayang di bawah layar kamera) */
.manual-overlay { position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); display: flex; gap: 15px; z-index: 20; }
.manual-overlay button { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); border: 2px solid var(--green); color: var(--green); padding: 8px 20px; border-radius: 99px; font-weight: 800; font-size: 0.95rem; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); transition: 0.2s; pointer-events: auto; }
.manual-overlay button:hover { background: var(--green); color: white; }

/* OVERLAY: Tombol Lanjut Soal (Pindah ke pojok kanan atas) */
.next-overlay { position: absolute; top: 15px; right: 15px; z-index: 30; }
.next-overlay .btn { font-size: 1.2rem; padding: 12px 30px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4); animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; pointer-events: auto; }

@keyframes popIn { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }

/* Efek Visual Melayang */
.visual-fx { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 4rem; font-weight: 900; text-align: center; pointer-events: none; opacity: 0; z-index: 10; text-shadow: 0 5px 20px rgba(0, 0, 0, 0.6); }
.visual-fx.show { animation: popFloat 2.5s ease-out forwards; }
@keyframes popFloat {
  0% { opacity: 0; transform: scale(0.3) translateY(50px); }
  15% { opacity: 1; transform: scale(1.2) translateY(-10px); }
  80% { opacity: 1; transform: scale(1) translateY(-60px); }
  100% { opacity: 0; transform: scale(1) translateY(-100px); }
}
.fx-benar { color: #4CAF50; }
.fx-salah { color: #F44336; }

/* Footer Glassmorphism */
.footer {
  background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
  border-top: 1px solid rgba(255, 255, 255, 0.6); padding: 15px; text-align: center; color: var(--ink);
  font-size: 0.9rem; font-weight: 600; margin-top: auto; position: relative; z-index: 99;
}
.footer a.admin-link {
  color: var(--green); background: rgba(255, 255, 255, 0.7); padding: 5px 15px; border-radius: 99px;
  text-decoration: none; font-weight: 800; display: inline-block; margin-top: 5px; transition: 0.3s;
}
.footer a.admin-link:hover { background: var(--green); color: white; }
.result { text-align: center; padding: 50px; margin: auto; }
.result-icon { font-size: 6rem; }
.final-score { font-size: 4.5rem; font-weight: 900; color: var(--green); margin: 10px 0; }
</style>
</head>
<body>

<!-- Titipan Data dari Controller Laravel agar VS Code tidak error (Display None) -->
<div id="data-soal" data-soal="{{ json_encode($questions) }}" style="display: none;"></div>

<div class="app">
  <header class="topbar">
    <div class="brand">
      <div class="logo">👆</div>
      <div><h1>Pahlawan Rupiah Cilik!</h1><p>Kuis Klik Udara GenBI</p></div>
    </div>
    <div class="pill" id="scorePill">⭐ 0 Poin</div>
  </header>

  <!-- HALAMAN DEPAN -->
  <section id="home" class="panel hero">
    <div style="font-size: 5rem;">🪄</div>
    <h2>Kuis Ajaib GenBI!</h2>
    <p>Arahkan jari telunjukmu ke layar seperti pakai tongkat sihir! Tahan jarimu di atas jawaban yang benar sampai lingkarannya penuh ya.</p>
    <div style="margin-top: 20px;">
      <button class="btn btn-primary" id="startBtn">Mulai Kamera & Main! 🚀</button>
    </div>
    <p style="font-size: 0.9rem; color: #888; margin-top: 20px;">Pastikan klik "Allow/Izinkan" saat browser meminta akses kamera.</p>
  </section>

  <!-- AREA KUIS -->
  <section id="quiz" class="hidden" style="flex-grow: 1; display: flex; flex-direction: column;">
    <div class="quiz-header">
      <div class="pill" style="display: inline-block; margin-bottom: 5px;" id="qCount">Soal 1/5</div>
      <h2 class="question" id="qText">Pertanyaan muncul di sini?</h2>
      <div class="timer-bar"><div class="timer-fill" id="tFill"></div></div>
      <div style="font-weight: bold; color: var(--green); margin-top: 5px;" id="tText">30 Detik</div>
      <div id="feedbackMsg" style="font-size: 1rem; font-weight: bold; margin-top: 5px; min-height: 24px;"></div>
    </div>

    <!-- AREA KAMERA & OVERLAY UI -->
    <div class="camera-wrap" style="flex-grow: 1; max-height: 55vh;">
      
      <!-- PITA HIASAN KAMERA -->
      <div class="camera-badge">✨ Arahkan Jari Telunjukmu ke Opsi A / B ✨</div>

      <video id="webcam" autoplay playsinline muted></video>
      <canvas id="poseCanvas"></canvas>
      
      <!-- Opsi A dan B Melayang -->
      <div class="air-options-container" id="airOptions">
        ... (lanjutan kode lainnya tetap sama)
      <video id="webcam" autoplay playsinline muted></video>
      <canvas id="poseCanvas"></canvas>
      
      <!-- Opsi A dan B Melayang -->
      <div class="air-options-container" id="airOptions">
        <div class="air-option" id="optA">
          <div class="letter">A</div><div class="text" id="textA">Pilihan A</div>
          <div class="air-progress-bg"><div class="air-progress-fill" id="progA"></div></div>
        </div>
        <div class="air-option" id="optB">
          <div class="letter">B</div><div class="text" id="textB">Pilihan B</div>
          <div class="air-progress-bg"><div class="air-progress-fill" id="progB"></div></div>
        </div>
      </div>

      <!-- Tombol Manual Floating -->
      <div class="manual-overlay" id="manualOverlay">
          <button data-manual="0">A (Kiri)</button>
          <button data-manual="1">B (Kanan)</button>
      </div>

      <!-- Tombol Lanjut Soal Floating (Muncul saat soal terjawab) -->
      <div class="next-overlay hidden" id="nextContainer">
          <button class="btn btn-primary" id="nextBtn">Lanjut Soal ➡️</button>
      </div>

      <!-- Efek Pop up Visual -->
      <div id="visualFx" class="visual-fx"></div>
      
      <!-- Layar Loading Kamera -->
      <div id="camLoading" style="position:absolute; inset:0; background:rgba(0,0,0,0.7); color:white; display:none; align-items:center; justify-content:center; z-index:50; font-size:1.5rem; font-weight:bold;">Menyiapkan Kamera... ⏳</div>
    </div>
  </section>

  <!-- HASIL AKHIR -->
  <section id="result" class="panel result hidden">
    <div class="result-icon" id="rIcon">🥇</div>
    <h2>Kamu Hebat!</h2>
    <div class="final-score" id="rScore">0/500</div>
    <p id="rMsg" style="font-size: 1.2rem;">Terima kasih sudah main!</p>
    <button class="btn btn-primary" id="replayBtn" style="margin-top: 20px;">Main Lagi Yuk! 🎮</button>
  </section>
</div>

<!-- FOOTER GLASSMORPHISM -->
<footer class="footer">
    ✨ GenBI Undiksha Edukasi Interaktif ✨<br>
    @auth
        <a href="{{ route('admin.index') }}" class="admin-link">⚙️ Dashboard Admin</a>
    @else
        <a href="{{ route('login') }}" class="admin-link">🔒 Login Admin</a>
    @endauth
</footer>

<script type="module">
import { HandLandmarker, FilesetResolver } from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14";

// --- AUDIO SETUP ---
const suaraBenar = new Audio('{{ asset("benar.mp3") }}'); 
const suaraSalah = new Audio('{{ asset("salah.mp3") }}');

// --- DATABASE DATA EXTRACTION ---
const rawData = document.getElementById('data-soal').getAttribute('data-soal');
const questions = JSON.parse(rawData);

// Default kalau database kosong
if(questions.length === 0) {
    questions.push({
        q: "Belum ada soal! Kakak GenBI harus menambahkan soal dulu.", 
        a: "Siap", b: "Oke", ans: 0, ex: "Silakan login ke menu admin."
    });
}

// --- VARIABEL GAME ---
const $ = id => document.getElementById(id);
let current = 0, score = 0, answered = false;
let timeLeft = 30, timerId = null;
const ANSWER_SECONDS = 30;

let handLandmarker = null, cameraStream = null, animationId = null;
let hoverTarget = null; 
let hoverProgress = 0; 
const HOVER_MAX = 40; 
let cameraOn = false;

// --- FUNGSI NAVIGASI ---
function showSection(id){
  ["home","quiz","result"].forEach(s => $(s).classList.toggle("hidden", s !== id));
}

$("startBtn").addEventListener("click", () => {
    current = 0; score = 0;
    $("scorePill").textContent = `⭐ 0 Poin`;
    showSection("quiz");
    if(!cameraOn) enableCamera();
    else { resetGameLoop(); }
});

function resetGameLoop(){
    answered = false;
    hoverTarget = null;
    hoverProgress = 0;
    $("progA").style.width = "0%";
    $("progB").style.width = "0%";
    $("optA").className = "air-option";
    $("optB").className = "air-option";
    
    // Tampilkan manual buttons, sembunyikan tombol Next
    $("manualOverlay").classList.remove("hidden");
    $("nextContainer").classList.add("hidden");
    
    $("feedbackMsg").style.color = "transparent";
    $("visualFx").classList.remove("show", "fx-benar", "fx-salah");
    
    renderQuestion();
    startTimer();
}

function renderQuestion(){
  const q = questions[current];
  $("qCount").textContent = `Soal ${current+1} / ${questions.length}`;
  $("qText").textContent = q.q;
  $("textA").textContent = q.a;
  $("textB").textContent = q.b;
}

// --- FUNGSI WAKTU ---
function startTimer(){
  clearInterval(timerId); timeLeft = ANSWER_SECONDS; updateTimer();
  timerId = setInterval(()=>{
    if(answered){ clearInterval(timerId); return; }
    timeLeft--; updateTimer();
    if(timeLeft <= 0){
      clearInterval(timerId);
      if(!answered) processAnswer(-1, "timeout");
    }
  }, 1000);
}

function updateTimer(){
  $("tText").textContent = `${timeLeft} Detik`;
  $("tFill").style.width = `${Math.max(0, timeLeft/ANSWER_SECONDS*100)}%`;
  $("tFill").style.background = timeLeft <= 10 ? "#e16a54" : "#ffd66b";
}

// --- LOGIKA MENJAWAB ---
function processAnswer(index, source){
  if(answered) return;
  answered = true; clearInterval(timerId);
  const q = questions[current];
  const correct = index == q.ans; // Pake '==' supaya nilai numerik aman
  
  if(correct) score += 100;
  $("scorePill").textContent = `⭐ ${score} Poin`;

  // UI Jawaban
  if(q.ans == 0) $("optA").classList.add("correct"); else $("optB").classList.add("correct");
  if(index == 0 && !correct) $("optA").classList.add("wrong");
  if(index == 1 && !correct) $("optB").classList.add("wrong");

  const fb = $("feedbackMsg");
  fb.style.color = correct ? "#174c36" : "#b93838";
  
  const visFx = $("visualFx");
  visFx.classList.remove("show", "fx-benar", "fx-salah");
  void visFx.offsetWidth; 

  if(index < 0){
      fb.innerHTML = `⏰ Waktu habis! Jawaban: <b>${q.ans==0?'A':'B'}</b>. ${q.ex}`;
      suaraSalah.play().catch(()=>{});
      visFx.innerHTML = "⏰ WAKTU HABIS!"; visFx.classList.add("fx-salah", "show");
  } else if(correct){
      fb.innerHTML = `🎉 <b>BENAR!</b> +100 poin. ${q.ex}`;
      suaraBenar.play().catch(()=>{});
      visFx.innerHTML = "✨ BENAR! ✨"; visFx.classList.add("fx-benar", "show");
  } else {
      fb.innerHTML = `💪 <b>UPS!</b> Jawaban tepat: <b>${q.ans==0?'A':'B'}</b>. ${q.ex}`;
      suaraSalah.play().catch(()=>{});
      visFx.innerHTML = "💦 SALAH 💦"; visFx.classList.add("fx-salah", "show");
  }

  // Sembunyikan manual buttons, tampilkan tombol Lanjut Soal Melayang
  $("manualOverlay").classList.add("hidden");
  $("nextContainer").classList.remove("hidden");
  $("nextBtn").textContent = current === questions.length-1 ? "Lihat Nilai 🏆" : "Lanjut Soal ➡️";
}

// Event Lanjut Soal / Tombol Manual
$("nextBtn").addEventListener("click", () => {
    if(current < questions.length - 1){ current++; resetGameLoop(); }
    else finishGame();
});

document.querySelectorAll("[data-manual]").forEach(b => {
    b.addEventListener("click", () => processAnswer(Number(b.dataset.manual), "manual"));
});

function finishGame(){
  clearInterval(timerId); stopCamera(); showSection("result");
  $("rScore").textContent = `${score}/${questions.length*100}`;
  const c = score/100;
  if(c >= 4){ $("rIcon").textContent="🦸‍♂️🏅"; $("rMsg").textContent="Wah, kamu Super Pahlawan Rupiah!"; }
  else if(c >= 2){ $("rIcon").textContent="🌟"; $("rMsg").textContent="Hebat! Semangat belajarnya ya."; }
  else { $("rIcon").textContent="🌱"; $("rMsg").textContent="Belajar lagi sama kakak GenBI biar makin jago!"; }
}

$("replayBtn").addEventListener("click", () => { showSection("home"); });

// --- SISTEM KAMERA MEDIAPIPE ---
async function enableCamera(){
  $("camLoading").style.display = "flex";
  try{
    cameraStream = await navigator.mediaDevices.getUserMedia({video:{facingMode:"user",width:640,height:480}, audio:false});
    $("webcam").srcObject = cameraStream;
    
    const vision = await FilesetResolver.forVisionTasks("https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/wasm");
    handLandmarker = await HandLandmarker.createFromOptions(vision, {
      baseOptions: { modelAssetPath: "https://storage.googleapis.com/mediapipe-models/hand_landmarker/hand_landmarker/float16/1/hand_landmarker.task", delegate: "GPU" },
      runningMode: "VIDEO", numHands: 1
    });

    cameraOn = true;
    $("camLoading").style.display = "none";
    resetGameLoop();
    detectLoop();
  }catch(err){
    console.error("Kamera gagal:", err);
    $("camLoading").innerHTML = "Kamera gagal diakses.<br>Main pakai tombol manual di bawah ya!";
    setTimeout(() => { $("camLoading").style.display = "none"; resetGameLoop(); }, 2000);
  }
}

function stopCamera(){
  cameraOn = false;
  if(animationId) cancelAnimationFrame(animationId); animationId = null;
  if(cameraStream){ cameraStream.getTracks().forEach(t=>t.stop()); cameraStream = null; }
  handLandmarker?.close(); handLandmarker = null;
}

let lastVideoTime = -1;
function detectLoop(){
  if(!cameraOn || !handLandmarker) return;
  const video = $("webcam"), canvas = $("poseCanvas"), ctx = canvas.getContext("2d");
  
  if(video.readyState >= 2 && video.currentTime !== lastVideoTime){
    lastVideoTime = video.currentTime;
    canvas.width = video.videoWidth || 640; canvas.height = video.videoHeight || 480;
    ctx.clearRect(0,0,canvas.width,canvas.height);

    const result = handLandmarker.detectForVideo(video, performance.now());
    
    if(result.landmarks && result.landmarks.length > 0 && !answered){
      const indexFinger = result.landmarks[0][8];
      const cursorX = (1 - indexFinger.x) * canvas.width;
      const cursorY = indexFinger.y * canvas.height;

      // Gambar kursor di tangan anak
      ctx.beginPath();
      ctx.arc(cursorX, cursorY, 15, 0, 2*Math.PI);
      ctx.fillStyle = "#fff"; ctx.fill();
      ctx.lineWidth = 4; ctx.strokeStyle = "#4CAF50"; ctx.stroke();

      let currentZone = null;
      if(cursorX < canvas.width * 0.45) currentZone = 0; 
      else if(cursorX > canvas.width * 0.55) currentZone = 1;

      if(currentZone !== null){
          if(hoverTarget === currentZone){
              hoverProgress++;
              let perc = Math.min(100, (hoverProgress / HOVER_MAX) * 100);
              
              if(currentZone === 0) {
                  $("progA").style.width = perc + "%";
                  $("optA").classList.add("hovered");
                  $("optB").classList.remove("hovered");
                  $("progB").style.width = "0%";
              } else {
                  $("progB").style.width = perc + "%";
                  $("optB").classList.add("hovered");
                  $("optA").classList.remove("hovered");
                  $("progA").style.width = "0%";
              }

              if(hoverProgress >= HOVER_MAX){
                  processAnswer(currentZone, "air");
              }
          } else {
              hoverTarget = currentZone;
              hoverProgress = 0;
          }
      } else {
          hoverTarget = null;
          hoverProgress = 0;
          $("progA").style.width = "0\%"; $("progB").style.width = "0%";
          $("optA").classList.remove("hovered"); $("optB").classList.remove("hovered");
      }
    }
  }
  animationId = requestAnimationFrame(detectLoop);
}
</script>
</body>
</html>