import { useInView } from './useInView'

/**
 * «Fondos con vida»: capas decorativas detrás del contenido de una sección clara. Cada sección usa 1 o 2 de estas
 * (nunca más) y nunca la misma combinación en dos secciones seguidas. El contenedor padre solo necesita llevar este
 * componente como hijo directo: bgfx.css le da position: relative e isolation: isolate con `:has(> .bgfx)`.
 *
 *  a  Dibujo técnico: silueta de línea (1 px, violeta-500 al 7 %) grande y cortada por el borde.
 *     Valor 'motor' | 'gear' | 'contactor' | 'plan', con lado: a="motor:r".
 *  b  Malla de color: dos manchas difusas (lila-200 y naranja al 10 %) en esquinas opuestas. `b="flip"` las invierte.
 *  c  Arco naranja de 1,5 px que entra desde un borde y se dibuja al aparecer. Valor 'l' | 'r' (lado).
 *  d  Cuadrícula de plano: líneas lila-200 cada 32 px al 40 %, desvanecidas hacia el centro.
 *  e  Banda diagonal lila-100 inclinada 6° detrás del contenido.
 * Los esquineros técnicos (f) son la clase `.tc` sobre el bloque clave.
 */

// Engranaje: dientes calculados con trigonometría para que el trazo sea de una sola línea continua
function gearPath(teeth, rOut, rIn, cx, cy) {
  const pts = []
  const step = (Math.PI * 2) / teeth
  for (let i = 0; i < teeth; i += 1) {
    const a = i * step
    const q = step / 4
    ;[[a - q * 1.5, rIn], [a - q * 0.8, rOut], [a + q * 0.8, rOut], [a + q * 1.5, rIn]].forEach(([t, r]) => pts.push([cx + Math.cos(t) * r, cy + Math.sin(t) * r]))
  }
  return `M${pts.map(([x, y]) => `${x.toFixed(1)} ${y.toFixed(1)}`).join('L')}Z`
}

const ART = {
  motor: (
    <>
      <rect x="180" y="190" width="380" height="220" rx="18" />
      {Array.from({ length: 16 }, (_, i) => <path key={i} d={`M${206 + i * 22} 202V398`} />)}
      <rect x="140" y="200" width="40" height="200" rx="8" />
      <rect x="560" y="206" width="96" height="188" rx="14" />
      {Array.from({ length: 7 }, (_, i) => <path key={i} d={`M${576 + i * 11} 222V378`} />)}
      <rect x="60" y="276" width="80" height="48" rx="4" />
      <path d="M72 286H132" />
      <rect x="330" y="140" width="104" height="50" rx="8" />
      <circle cx="382" cy="165" r="12" />
      <rect x="160" y="410" width="410" height="26" rx="6" />
      <rect x="200" y="436" width="86" height="20" rx="4" />
      <rect x="462" y="436" width="86" height="20" rx="4" />
      <circle cx="243" cy="446" r="5" />
      <circle cx="505" cy="446" r="5" />
      <path d="M520 190v-26a14 14 0 0 1 28 0v26" />
    </>
  ),
  gear: (
    <>
      <path d={gearPath(20, 290, 244, 400, 300)} />
      <circle cx="400" cy="300" r="205" />
      <circle cx="400" cy="300" r="70" />
      <circle cx="400" cy="300" r="40" />
      {Array.from({ length: 6 }, (_, i) => <circle key={i} cx={400 + Math.cos((i * Math.PI) / 3) * 138} cy={300 + Math.sin((i * Math.PI) / 3) * 138} r="34" />)}
      <path d={gearPath(11, 118, 90, 700, 560)} />
      <circle cx="700" cy="560" r="30" />
    </>
  ),
  contactor: (
    <>
      <rect x="240" y="100" width="320" height="400" rx="20" />
      {[290, 400, 510].map((x) => (
        <g key={x}>
          <rect x={x - 28} y="56" width="56" height="44" rx="6" />
          <circle cx={x} cy="78" r="11" />
          <rect x={x - 28} y="500" width="56" height="44" rx="6" />
          <circle cx={x} cy="522" r="11" />
        </g>
      ))}
      <rect x="290" y="190" width="220" height="130" rx="10" />
      <path d="M312 222H488M312 252H440M312 282H400" />
      <circle cx="330" cy="396" r="22" />
      <rect x="400" y="374" width="110" height="44" rx="8" />
      <path d="M240 160H190M240 440H190M560 160H610M560 440H610" />
      <rect x="150" y="146" width="40" height="28" rx="4" />
      <rect x="610" y="426" width="40" height="28" rx="4" />
    </>
  ),
  plan: (
    <>
      {[110, 150, 190].map((y) => <path key={y} d={`M30 ${y}H770`} />)}
      {[200, 330, 460].map((x, i) => (
        <g key={x}>
          <path d={`M${x} ${110 + i * 40}V240`} />
          <rect x={x - 12} y="240" width="24" height="52" rx="3" />
          <path d={`M${x} 292V330`} />
          <path d={`M${x} 330L${x + 22} 372`} />
          <circle cx={x} cy="330" r="4" />
          <circle cx={x} cy="388" r="4" />
          <path d={`M${x} 388V440`} />
        </g>
      ))}
      <rect x="170" y="440" width="320" height="64" rx="6" />
      <path d="M200 504V540M330 504V540M460 504V540" />
      <circle cx="330" cy="610" r="64" />
      <path d="M300 636V586l30 34 30-34v50" />
      <path d="M330 540V546" />
      <path d="M520 150H640V300" />
      <rect x="610" y="300" width="60" height="90" rx="4" />
      <path d="M640 390V470" />
      <circle cx="640" cy="490" r="20" />
      <path d="M626 490h28" />
      <circle cx="520" cy="150" r="5" />
      <circle cx="200" cy="110" r="5" />
      <circle cx="330" cy="150" r="5" />
      <circle cx="460" cy="190" r="5" />
    </>
  ),
}

function Art({ name, side }) {
  const side2 = side === 'l' ? 'l' : 'r'
  return (
    <svg className={`bgfx__art bgfx__art--${side2}`} viewBox="0 0 800 640" aria-hidden="true" focusable="false">
      <g fill="none" stroke="currentColor" strokeWidth="1" strokeLinejoin="round" strokeLinecap="round" vectorEffect="non-scaling-stroke">{ART[name]}</g>
    </svg>
  )
}

function ArcLine({ side, top }) {
  const [ref, seen] = useInView({ threshold: 0, margin: '0px 0px -5% 0px' })
  return (
    <svg ref={ref} className={`bgfx__arc bgfx__arc--${side} ${seen ? 'is-in' : ''}`} style={top ? { '--top': top } : undefined} viewBox="0 0 400 400" aria-hidden="true" focusable="false">
      <path pathLength="1" d={side === 'l' ? 'M0 360A360 360 0 0 1 360 0' : 'M400 360A360 360 0 0 0 40 0'} />
    </svg>
  )
}

export default function Bgfx({ a, b, c, d, e, arcTop }) {
  const [art, side] = a ? String(a).split(':') : []
  return (
    <div className="bgfx" aria-hidden="true">
      {e && <span className="bgfx__band" />}
      {d && <span className="bgfx__grid" />}
      {b && <span className={`bgfx__blob bgfx__blob--lila ${b === 'flip' ? 'is-flip' : ''}`} />}
      {b && <span className={`bgfx__blob bgfx__blob--orange ${b === 'flip' ? 'is-flip' : ''}`} />}
      {art && <Art name={art} side={side} />}
      {c && <ArcLine side={c === 'l' ? 'l' : 'r'} top={arcTop} />}
    </div>
  )
}
