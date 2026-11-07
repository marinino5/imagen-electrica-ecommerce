import { Link } from 'react-router-dom'
import { ArrowUpRight, MessageCircle, Navigation } from 'lucide-react'
import { withLogos } from '../config/brands'
import Bgfx from '../components/ui/Bgfx'
import Counter from '../components/ui/Counter'
import Hl from '../components/ui/Hl'
import PhotoSlot from '../components/ui/PhotoSlot'
import Reveal from '../components/ui/Reveal'
import Pill from '../components/ui/Pill'
import { site } from '../config/site'
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

// Cómo trabajamos: tres pilares en tarjetas altas (foto arriba, 60 %; texto debajo)
const PILLARS = [
  { title: 'Asesoría técnica', text: 'Te ayudamos a elegir la referencia exacta para tu máquina o tu tablero, con ficha técnica del fabricante.', photo: '/img/fotos/electricista-tablero.webp', alt: 'Electricista conectando un tablero' },
  { title: 'Marcas originales', text: 'Distribuimos WEG, Soler & Palau, Nexans y otras marcas reconocidas, con producto original y garantía.', photo: '/img/fotos/almacen-cajas.webp', alt: 'Cajas de material eléctrico organizadas en estantería' },
  { title: 'Envíos a todo el país', text: 'Recoge en la tienda de Bucaramanga o recíbelo en tu obra o planta. Un asesor coordina la entrega.', photo: '/img/fotos/despacho-montacargas.webp', alt: 'Montacargas listo para despachar cajas' },
]

function WallTile({ p, i }) {
  return (
    <Reveal as="li" mask delay={(i % 6) * 0.07} className={`wall__t ${p.t ? `wall__t--${p.t}` : ''}`}>
      <Link to={`/productos?familia=${p.slug}`} className="wall__link" aria-label={`${p.name}, ${p.fam}`}>
        <img src={`/img/catalogo/${p.src}-sm.webp`} alt="" width="480" height="480" loading="lazy" />
        <span className="wall__cap"><strong>{p.name}</strong>{p.fam}</span>
      </Link>
    </Reveal>
  )
}

export default function Nosotros() {
  const brands = withLogos()

  return (
    <div className="ab-page">
      {/* Hero: texto con las dos cifras y la fachada en marco horizontal con una capa violeta detrás */}
      <section className="ab bg-lila-fade" aria-labelledby="h-nosotros">
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
            <Reveal mask always delay={0.1} className="ab__layer" aria-hidden="true" />
            <Reveal mask always>
              <img className="ab__photo" src="/img/fachada.webp" alt="Fachada de la tienda Imagen Eléctrica en Bucaramanga, con su letrero completo y el interior iluminado" width="1600" height="800" fetchPriority="high" loading="eager" />
            </Reveal>
            <Reveal mask always delay={0.25} className="ab__tag">Bucaramanga, Santander</Reveal>
          </div>
        </div>
      </section>

      {/* Muro de producto: firma de esta página */}
      <section className="ab-wall bg-lila-fade" aria-labelledby="h-muro">
        <Bgfx d b="flip" />
        <div className="wrap">
          <h2 id="h-muro" className="sr-only">Productos que distribuimos</h2>
          <ul className="wall">
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

      {/* Cómo trabajamos */}
      <section className="ab-how bg-lav" aria-labelledby="h-trabajamos">
        <Bgfx a="contactor:r" e />
        <div className="wrap">
          <h2 id="h-trabajamos" className="rd-h2">Cómo <Hl>trabajamos</Hl></h2>
          <ul className="how3">
            {PILLARS.map((p, i) => (
              <Reveal as="li" mask key={p.title} delay={i * 0.12}>
                <article className="how3__card">
                  <div className="how3__img"><img src={p.photo} alt={p.alt} width="800" height="600" loading="lazy" /></div>
                  <div className="how3__txt"><h3>{p.title}</h3><p>{p.text}</p></div>
                </article>
              </Reveal>
            ))}
          </ul>
        </div>
      </section>

      {/* Equipo y almacén: dos marcos 4:3 reservados */}
      <section className="ab-gal bg-lila-soft" aria-label="Nuestro equipo y nuestro almacén">
        <Bgfx b="flip" e />
        <div className="wrap">
          <ul className="ab-gal__grid">
            {SLOTS.map((s, i) => (
              <li key={s.id}>
                <Reveal mask delay={i * 0.12}><PhotoSlot title={s.title} photo={s.photo} alt={s.alt} /></Reveal>
              </li>
            ))}
          </ul>
        </div>
      </section>

      {/* Marcas: tarjetas con el logo oficial (gris que pasa a color) y las líneas que distribuimos */}
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

      {/* Cierre: franja con el arco naranja grande; sin mapa (el mapa abre en Google Maps) */}
      <section className="ab-end" aria-labelledby="h-cierre">
        <svg className="ab-end__arc" viewBox="0 0 600 600" aria-hidden="true" focusable="false"><circle cx="300" cy="300" r="298" /></svg>
        <div className="wrap ab-end__in">
          <h2 id="h-cierre" className="rd-h2">Pásate por la tienda o <Hl>escríbenos</Hl></h2>
          <p>{site.address}, Bucaramanga. Te atendemos en el mostrador o por WhatsApp.</p>
          <div className="ab-end__btns">
            <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="o" icon={Navigation}>Visítanos en la tienda</Pill>
            <Pill as="a" href={site.whatsappUrl} target="_blank" rel="noreferrer" tone="l" icon={MessageCircle}>Escríbenos</Pill>
          </div>
        </div>
      </section>
    </div>
  )
}
