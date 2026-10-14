// Hiasan melayang untuk anak-anak: balon, bintang, pelangi.
const ITEMS = [
  { e: '🎈', left: '4%', size: '3rem', dur: '11s', delay: '0s' },
  { e: '⭐', left: '12%', size: '2rem', dur: '14s', delay: '2s' },
  { e: '🎈', left: '22%', size: '2.2rem', dur: '12s', delay: '5s' },
  { e: '🌈', left: '35%', size: '2.6rem', dur: '16s', delay: '1s' },
  { e: '🍬', left: '48%', size: '2rem', dur: '13s', delay: '4s' },
  { e: '🎈', left: '60%', size: '3rem', dur: '15s', delay: '3s' },
  { e: '⭐', left: '72%', size: '2.4rem', dur: '11s', delay: '6s' },
  { e: '🎮', left: '84%', size: '2.4rem', dur: '14s', delay: '2.5s' },
  { e: '🎈', left: '93%', size: '2rem', dur: '12s', delay: '7s' },
];

export default function Decor() {
  return (
    <div aria-hidden className="pointer-events-none fixed inset-0 z-0 overflow-hidden">
      {ITEMS.map((it, i) => (
        <span
          key={i}
          className="animate-drift absolute bottom-0"
          style={{
            left: it.left,
            fontSize: it.size,
            animationDuration: it.dur,
            animationDelay: it.delay,
          }}
        >
          {it.e}
        </span>
      ))}
    </div>
  );
}
