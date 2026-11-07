import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { ArrowUpRight, MessageCircle, Navigation } from 'lucide-react'
import { useReducedMotion } from 'framer-motion'
import { withLogos } from '../config/brands'
import Bgfx from '../components/ui/Bgfx'
import Counter from '../components/ui/Counter'
import Hl from '../components/ui/Hl'
import PhotoSlot from '../components/ui/PhotoSlot'
import Reveal from '../components/ui/Reveal'
import Pill from '../components/ui/Pill'
import { site } from '../config/site'
import { useMediaQuery } from '../hooks/useMediaQuery'
import { clamp01, useScrollFrame } from '../hooks/useScrollFrame'
import '../styles/nosotros.css'
import '../styles/ie-nosotros.css'

// Equipo y almacén: espacios reservados. Cuando lleguen las fotos se ponen en `photo` y `alt` (ver docs/imagenes-pendientes.md).
const SLOTS = [
  { id: 'equipo', title: 'Nuestro equipo' },
  { id: 'almacen', title: 'Nuestro almacén por dentro' },
]

// Muro de producto: 12 productos recortados de distintas familias sobre baldosas lila de tamaños distintos. `t` es la forma de la
// baldosa en la grilla de escritorio (w: ancho doble, h: alto doble). Al pasar el mouse (o enfocar) aparece su nombre y su familia.
const WALL = [
  { src: 'weg-w22-iec', name: 'Motor trifásico W22', fam: 'Motores eléctricos', slug: 'motores-electricos', t: 'w' },
  { src: 'weg-cfw500', name: 'Variador CFW500', fam: 'Control y automatización', slug: 'control-y-automatizacion' },
  { src: 'soler-palau-extractor-axial', name: 'Extractor axial tubular', fam: 'Ventilación y climatización', slug: 'ventilacion-y-climatizacion' },
  { src: 'weg-cestari-wcg20', name: 'Motorreductor helicoidal', fam: 'Reductores y motorreductores', slug: 'reductores-y-motorreductores', t: 'h' },
  { src: 'fluke-multimetro-117', name: 'Multímetro Fluke 117', fam: 'Medición y testeo', slug: 'medicion-y-testeo', t: 'h' },
  { src: 'weg-contactores', name: 'Contactores CWB', fam: 'Control y automatización', slug: 'control-y-automatizacion' },
  { src: 'weg-guardamotores', name: 'Guardamotor MPW', fam: 'Control y automatización', slug: 'control-y-automatizacion' },
  { src: 'centelsa-cable-thhn', name: 'Cable THHN/THWN-2', fam: 'Cables y conductores', slug: 'cables-y-conductores', t: 'w' },
  { src: 'sylvania-start-highbay', name: 'Campana LED START', fam: 'Iluminación', slug: 'iluminacion' },
  { src: 'abac-compresor-vertical', name: 'Compresor vertical', fam: 'Compresores', slug: 'compresores' },
  { src: 'weg-plc500', name: 'PLC WEG PLC500', fam: 'Control y automatización', slug: 'control-y-automatizacion' },
  { src: 'weg-ssw05', name: 'Arrancador suave SSW05', fam: 'Control y automatización', slug: 'control-y-automatizacion' },
]

// Cómo trabajamos: tres paneles de foto que se apilan al bajar (tarjetas apiladas)
const PILLARS = [
  { title: 'Asesoría técnica', text: 'Te ayudamos a elegir la referencia exacta para tu máquina o tu tablero.\nCon la ficha técnica del fabricante a la mano.', photo: '/img/fotos/nosotros-asesoria.webp', alt: 'Dos asesores mirando juntos una pantalla para elegir una referencia', pos: '50% 30%' },
  { title: 'Marcas originales', text: 'Distribuimos WEG, Soler & Palau, Nexans y otras marcas reconocidas.\nProducto original, almacenado y listo para despachar.', photo: '/img/fotos/nosotros-estanteria.webp', alt: 'Estantería industrial con cajas paletizadas y rotuladas', pos: '50% 50%' },
  { title: 'Envíos a todo el país', text: 'Recoge en la tienda de Bucaramanga o recíbelo en tu obra o planta.\nUn asesor coordina la entrega contigo.', photo: '/img/fotos/nosotros-despacho.webp', alt: 'Operarios conversando en una bodega junto a la mercancía lista para despachar', pos: '50% 55%' },
]

// Manifiesto: las palabras en naranja son las clave
const MANIFEST = [
  ['Durante'], ['más'], ['de'], ['25'], ['años'], ['hemos'], ['acompañado'], ['a'], ['talleres,'], ['plantas'], ['y'], ['obras'], ['de'], ['Santander', 1],
  ['con'], ['la'], ['referencia', 1], ['exacta,'], ['asesoría', 1], ['de'], ['verdad'], ['y'], ['las'], ['marcas', 1], ['en'], ['las'], ['que'], ['confía'], ['la'], ['industria.', 1],
]

function WallTile({ p, i }) {
  return (
    <Reveal as="li" mask delay={(i % 6) * 0.07} className={`wall__t ${p.t ? `wall__t--${p.t}` : ''}`}>
      <Link to={`/productos?familia=${p.slug}`} className="wall__link" aria-label={`${p.name}, ${p.fam}`}>
        <img src={`/img/catalogo/${p.src}-sm.webp`} alt="" width="480" height="480" loading="lazy" decoding="async" />
        <span className="wall__cap"><strong>{p.name}</strong>{p.fam}</span>
      </Link>
    </Reveal>
  )
}

const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - (-2 * t + 2) ** 3 / 2) // cúbica in-out

/* ---------- 6.1 Hero: la fachada crece hasta pantalla completa ---------- */
// Sección de 250vh con un cuadro fijo de 100vh: durante los 150vh de recorrido, la foto (que parte del marco 16:10) se escala
// hasta cubrir la ventana y su radio baja de 28 a 0; el texto se desvanece y aparece «Bucaramanga, Santander» en grande.
function GrowHero({ reduced }) {
  const sec = useRef(null)
  const stick = useRef(null)
  const slot = useRef(null)
  const photo = useRef(null)

  useScrollFrame(() => {
    const s = sec.current
    const st = stick.current
    const sl = slot.current
    const ph = photo.current
    if (!s || !st || !sl || !ph) return
    const vh = window.innerHeight
    const vw = document.documentElement.clientWidth
    const total = Math.max(1, s.offsetHeight - vh)
    const p = clamp01(-s.getBoundingClientRect().top / total)
    const e = ease(p)
    // El marco de partida se mide respecto al cuadro fijo: no cambia mientras se desplaza
    const a = sl.getBoundingClientRect()
    const b = st.getBoundingClientRect()
    const x0 = a.left - b.left
    const y0 = a.top - b.top
    const sc = Math.max(vw / a.width, vh / a.height)
    const scale = 1 + (sc - 1) * e
    const tx = (vw / 2 - (x0 + a.width / 2)) * e
    const ty = (vh / 2 - (y0 + a.height / 2)) * e
    ph.style.cssText = `left:${x0}px;top:${y0}px;width:${a.width}px;height:${a.height}px;transform:translate3d(${tx.toFixed(1)}px,${ty.toFixed(1)}px,0) scale(${scale.toFixed(4)});border-radius:${((28 * (1 - e)) / scale).toFixed(2)}px`
    s.style.setProperty('--p', p.toFixed(4))
  }, !reduced)

  const img = <img className="ab-grow__img" src="/img/fachada.webp" alt="Fachada de la tienda Imagen Eléctrica en Bucaramanga, con su letrero completo y el interior iluminado" width="1600" height="800" fetchPriority="high" loading="eager" />

  return (
    <section ref={sec} className={`ab-pin ${reduced ? 'ab-pin--flow' : ''}`} aria-labelledby="h-nosotros">
      <div ref={stick} className="ab-pin__stick bg-lila-fade">
        <Bgfx a="gear:l" c="r" arcTop="10%" />
        <div className="wrap ab__grid">
          <div className="ab__text">
            <h1 id="h-nosotros" className="ie-h1">Más de {site.years} años <span className="mix-l">impulsando la industria</span> de <Hl>Santander</Hl></h1>
            <p className="rd-lead">Somos una distribuidora de Bucaramanga que acompaña a empresas y técnicos con la referencia exacta para cada equipo.</p>
            <dl className="ab__figs">
              <Reveal mask always className="ab__fig" as="div">
                <dt><Counter to={site.years} prefix="+" /></dt>
                <dd>años en el sector</dd>
              </Reveal>
              <Reveal mask always delay={0.12} className="ab__fig" as="div">
                <dt><Counter to={1000} prefix="+" /></dt>
                <dd>productos</dd>
              </Reveal>
            </dl>
          </div>
          <div className="ab__stack">
            <span className="ab__layer" aria-hidden="true" />
            <div ref={slot} className="ab__slot">{reduced && <figure className="ab-grow ab-grow--static">{img}</figure>}</div>
          </div>
        </div>
        {!reduced && (
          <>
            <figure ref={photo} className="ab-grow">{img}<span className="ab-grow__veil" aria-hidden="true" /></figure>
            <p className="ab-pin__tag" aria-hidden="true">Bucaramanga, Santander</p>
          </>
        )}
      </div>
    </section>
  )
}

/* ---------- 6.2 Manifiesto: el párrafo se «escribe» con el scroll ---------- */
function Manifesto({ reduced }) {
  const sec = useRef(null)
  useScrollFrame(() => {
    const el = sec.current
    if (!el) return
    const total = Math.max(1, el.offsetHeight - window.innerHeight)
    const raw = clamp01(-el.getBoundingClientRect().top / total)
    el.style.setProperty('--p', clamp01((raw - 0.08) / 0.72).toFixed(4))
  }, !reduced)

  return (
    <section ref={sec} className={`mf bg-lila-soft ${reduced ? 'mf--flow' : ''}`} style={{ '--n': MANIFEST.length, '--p': reduced ? 1 : 0 }} aria-labelledby="h-manifiesto">
      <div className="mf__stick">
        <h2 id="h-manifiesto" className="sr-only">Quiénes somos</h2>
        <p className="mf__text">
          {MANIFEST.map(([w, key], i) => <span key={i} className={`mf__w ${key ? 'is-key' : ''}`} style={{ '--i': i }}>{w} </span>)}
        </p>
      </div>
    </section>
  )
}

/* ---------- 6.3 Muro de producto con parallax por columnas ---------- */
function Wall({ reduced }) {
  const wide = useMediaQuery('(min-width: 900px)')
  const box = useRef(null)
  // --wp va de −1 a 1 mientras el muro cruza la ventana; el CSS lo multiplica por −40, 0 o +40 px según la columna
  useScrollFrame(() => {
    const el = box.current
    if (!el) return
    const r = el.getBoundingClientRect()
    const vh = window.innerHeight
    el.style.setProperty('--wp', Math.max(-1, Math.min(1, (vh / 2 - (r.top + r.height / 2)) / (vh / 2 + r.height / 2))).toFixed(4))
  }, wide && !reduced, [wide, reduced])

  return (
    <section className="ab-wall bg-lila-fade" aria-labelledby="h-muro">
      <Bgfx d b="flip" />
      <div className="wrap">
        <h2 id="h-muro" className="sr-only">Productos que distribuimos</h2>
        <ul className="wall" ref={box}>
          {WALL.slice(0, 5).map((p, i) => <WallTile key={p.src} p={p} i={i} />)}
          <li className="wall__core">
            <Reveal mask delay={0.2} className="wall__core-in">
              <p className="wall__big">+1.000</p>
              <p className="wall__txt">productos de las mejores marcas</p>
              <Pill as="link" to="/productos" tone="o" icon={ArrowUpRight}>Explorar catálogo</Pill>
            </Reveal>
          </li>
          {WALL.slice(5).map((p, i) => <WallTile key={p.src} p={p} i={i + 5} />)}
        </ul>
      </div>
    </section>
  )
}

/* ---------- 6.4 Cómo trabajamos: tarjetas apiladas ---------- */
// Cada panel queda fijo arriba y el siguiente sube y lo tapa; el de abajo se reduce hasta 0,94 y se oscurece (--cv, 0 → 1).
function Stack({ reduced }) {
  const box = useRef(null)
  useScrollFrame(() => {
    const cards = [...(box.current?.children ?? [])]
    cards.forEach((c, i) => {
      const next = cards[i + 1]
      if (!next) return c.style.setProperty('--cv', '0')
      const h = c.offsetHeight
      const stuck = parseFloat(getComputedStyle(c).top) || 0
      c.style.setProperty('--cv', clamp01((stuck + h - next.getBoundingClientRect().top) / (h - 24)).toFixed(4))
    })
  }, !reduced)

  return (
    <section className="ab-how bg-lav" aria-labelledby="h-trabajamos">
      <Bgfx a="contactor:r" e />
      <div className="wrap">
        <h2 id="h-trabajamos" className="rd-h2">Cómo <Hl>trabajamos</Hl></h2>
        <ul className="stk" ref={box}>
          {PILLARS.map((p, i) => (
            <li key={p.title} className="stk__card" style={{ '--i': i }}>
              <img className="stk__img" src={p.photo} alt={p.alt} width="1800" height="1012" loading="lazy" decoding="async" style={{ objectPosition: p.pos }} />
              <span className="stk__veil" aria-hidden="true" />
              <div className="stk__txt">
                <span className="stk__n" aria-hidden="true">{i + 1}</span>
                <h3>{p.title}</h3>
                <p>{p.text.split('\n').map((l, k) => <span key={k}>{l}</span>)}</p>
              </div>
              <span className="stk__dim" aria-hidden="true" />
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}

export default function Nosotros() {
  const brands = withLogos()
  const reduced = useReducedMotion()

  return (
    <div className="ab-page">
      <GrowHero reduced={reduced} />
      <Manifesto reduced={reduced} />
      <Wall reduced={reduced} />
      <Stack reduced={reduced} />

      {/* 6.5 Equipo y almacén: dos marcos escalonados, cada uno con un bloque violeta extendido detrás */}
      <section className="ab-gal bg-lila-soft" aria-label="Nuestro equipo y nuestro almacén">
        <Bgfx b="flip" />
        <div className="wrap">
          <ul className="ab-gal__grid">
            {SLOTS.map((s, i) => (
              <li key={s.id} className={i ? 'is-r' : 'is-l'}>
                <Reveal mask delay={i * 0.12}><PhotoSlot title={s.title} photo={s.photo} alt={s.alt} /></Reveal>
              </li>
            ))}
          </ul>
        </div>
      </section>

      {/* 6.6 Marcas: tarjetas con el logo oficial (gris que pasa a color) y las líneas que distribuimos */}
      <section className="rd-sec bg-white ab-brands" aria-labelledby="h-marcas">
        <Bgfx d c="l" arcTop="6%" />
        <div className="wrap">
          <h2 id="h-marcas" className="rd-h2">Marcas que <Hl>distribuimos</Hl></h2>
          <ul className="ab-brands__grid">
            {brands.map((b, i) => (
              <Reveal as="li" mask key={b.name} delay={(i % 4) * 0.07}>
                <Link to={`/productos?marca=${encodeURIComponent(b.name.split(' / ')[0])}`} className="ab-brand lift">
                  <span className="ab-brand__logo">{b.logo_url ? <img src={b.logo_url} alt="" height="56" loading="lazy" /> : <span>{b.name}</span>}</span>
                  <strong>{b.name}</strong>
                  <span>{b.lines}</span>
                </Link>
              </Reveal>
            ))}
          </ul>
        </div>
      </section>

      {/* 6.7 Cierre claro: tarjeta blanca con arco naranja, sin franja violeta */}
      <section className="ab-end2 bg-lila-fade" aria-labelledby="h-cierre">
        <div className="wrap">
          <Reveal className="ab-end2__card">
            <svg className="ab-end2__arc" viewBox="0 0 320 320" aria-hidden="true" focusable="false"><path d="M320 0A320 320 0 0 1 0 320" /></svg>
            <h2 id="h-cierre" className="ab-end2__h">Pásate por la tienda <span>o escríbenos</span></h2>
            <p>{site.address}, Bucaramanga. Te atendemos en el mostrador o por WhatsApp.</p>
            <div className="ab-end2__btns">
              <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="o" icon={Navigation}>Cómo llegar</Pill>
              <Pill as="a" href={site.whatsappUrl} target="_blank" rel="noreferrer" tone="l" icon={MessageCircle}>Escríbenos</Pill>
            </div>
          </Reveal>
        </div>
      </section>
    </div>
  )
}
