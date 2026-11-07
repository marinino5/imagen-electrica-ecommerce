import { Link } from 'react-router-dom'
import { Boxes, Headset, Navigation, ShieldCheck, Truck, Users, Warehouse } from 'lucide-react'
import { site } from '../config/site'
import { withLogos } from '../config/brands'
import { useFamilies } from '../hooks/useFamilies'
import Arc from '../components/ui/Arc'
import Counter from '../components/ui/Counter'
import Hl from '../components/ui/Hl'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import SectionLabel from '../components/ui/SectionLabel'
import '../styles/nosotros.css'

const story = [
  {
    n: '01',
    label: 'Nuestra historia',
    title: ['Más de 25 años', 'distribuyendo', 'energía'],
    hl: 2,
    text: [
      'Imagen Eléctrica es una distribuidora de material eléctrico industrial en Bucaramanga, Santander.',
      'Acompañamos a empresas y a técnicos con motores, variadores, cables, iluminación y automatización.',
    ],
    photo: '/img/fotos/bodega-cajas.webp',
    alt: 'Pasillo de la bodega con cajas en estanterías',
  },
  {
    n: '02',
    label: 'Cómo trabajamos',
    title: ['El producto', 'correcto,', 'con respaldo'],
    hl: 1,
    text: [
      'Un asesor revisa tu lista, te muestra la referencia y te dice qué hay en bodega.',
      'Hoy llevamos esa atención a la tienda en línea: compra lo disponible o cotiza sin cuenta.',
    ],
    photo: '/img/fotos/tablero-cerca.webp',
    alt: 'Cableado de un tablero eléctrico',
  },
]

const values = [
  { Icon: Headset, title: 'Asesoría', text: 'Te ayudamos a elegir.', extra: 'Potencia, tensión y protección para tu motor, tablero o instalación.', photo: '/img/fotos/electricista-tablero.webp', alt: 'Electricista revisando las conexiones de un tablero' },
  { Icon: Boxes, title: 'Disponibilidad', text: 'Bodega con rotación.', extra: 'Lo que está en bodega lo ves con su unidad y su precio; lo demás lo conseguimos.', photo: '/img/fotos/bodega.webp', alt: 'Bodega con estanterías altas' },
  { Icon: ShieldCheck, title: 'Marcas reconocidas', text: 'Las que la industria ya conoce.', extra: 'WEG, ABB, Siemens, Fluke y más, con respaldo del fabricante.', photo: '/img/fotos/planta-operando.webp', alt: 'Operario en una planta industrial' },
  { Icon: Truck, title: 'Envíos', text: 'A donde estés.', extra: 'Recoge en Bucaramanga o recibe en tu obra o planta, en todo el país.', photo: '/img/fotos/despacho-montacargas.webp', alt: 'Bodega con estanterías y un montacargas' },
]

export default function Nosotros() {
  const { meta } = useFamilies()
  const brands = withLogos()
  // Los dos protagonistas: los años en el sector y las referencias reales del catálogo (de la API, nunca escritas a mano)
  const stats = [
    { value: site.years, prefix: '+', label: 'años en el sector' },
    { value: meta.references, label: 'referencias' },
  ].filter((s) => s.value > 0)

  return (
    <>
      {/* Hero: una sola foto de la fachada, a sangre y sin deformar (object-fit: cover con punto focal en el letrero) */}
      <section className="ab-hero on-dark" aria-labelledby="h-nosotros">
        <img className="ab-hero__img" src="/img/fachada.webp" alt="Fachada de la tienda Imagen Eléctrica en Bucaramanga, con su letrero y el interior iluminado" width="1600" height="800" fetchPriority="high" />
        <div className="wrap ab-hero__txt">
          <p className="ab-hero__tag">Nosotros</p>
          <h1 id="h-nosotros">Soluciones <strong>industriales</strong></h1>
          <p>Material eléctrico industrial en Bucaramanga, con la experiencia de quien conoce cada referencia.</p>
        </div>
      </section>

      {/* Protagonistas: años en el sector y referencias, grandes y animados */}
      {stats.length > 0 && (
        <section className="ab-stats on-dark" aria-label="Imagen Eléctrica en cifras">
          <img src="/img/fotos/bodega-oscura.webp" alt="" width="1280" height="853" loading="lazy" />
          <ul className="wrap">
            {stats.map((s) => (
              <li key={s.label}>
                <Counter to={s.value} prefix={s.prefix} className="ab-stats__n" />
                <span>{s.label}</span>
              </li>
            ))}
          </ul>
        </section>
      )}

      {/* Historia: bloques alternados con la foto en cápsula */}
      <section className="rd-sec bg-white tex-dots ab-story" aria-label="Historia">
        <div className="wrap ab-story__stack">
          {story.map((b, i) => {
            const flip = i % 2 === 1
            return (
              <div key={b.n} className={`split ${flip ? 'split--flip' : ''}`}>
                <Reveal side={flip ? 'r' : 'l'} className="split__media">
                  <div className={`capsule ${flip ? 'capsule--r' : ''} ab-capsule`}><img src={b.photo} alt={b.alt} width="1000" height="800" loading="lazy" /></div>
                </Reveal>
                <Reveal side={flip ? 'l' : 'r'} delay={0.12} className="split__text">
                  <SectionLabel n={b.n}>{b.label}</SectionLabel>
                  <h2 className="rd-h2">{b.title.map((t, k) => <span key={t} className="ab-line">{k === b.hl ? <Hl>{t}</Hl> : t}</span>)}</h2>
                  {b.text.map((t) => <p key={t} className="rd-lead ab-p">{t}</p>)}
                </Reveal>
              </div>
            )
          })}
        </div>
      </section>

      {/* Dos espacios para las fotos que enviará la empresa. Mientras tanto, un diseño de marca sin texto de relleno
          (pendientes en docs/imagenes-pendientes.md: «foto interior del almacén» y «foto del equipo»). */}
      <section className="rd-sec bg-soft ab-slots" aria-label="El almacén y el equipo">
        <div className="wrap">
          <ul className="ab-slots__grid">
            <Reveal as="li" i={0}>
              <figure className="ab-slot">
                <span className="ab-slot__art" aria-hidden="true"><Warehouse strokeWidth={1} /></span>
                <figcaption>Nuestro almacén <strong>por dentro</strong></figcaption>
              </figure>
            </Reveal>
            <Reveal as="li" i={1}>
              <figure className="ab-slot">
                <span className="ab-slot__art" aria-hidden="true"><Users strokeWidth={1} /></span>
                <figcaption>Nuestro <strong>equipo</strong></figcaption>
              </figure>
            </Reveal>
          </ul>
        </div>
      </section>

      {/* Valores: 4 tarjetas con foto; al pasar el mouse revelan una frase extra */}
      <section className="rd-sec bg-lav ab-values" aria-labelledby="h-valores">
        <Arc pos="tr" />
        <div className="wrap">
          <SectionLabel n="03">Lo que nos define</SectionLabel>
          <h2 id="h-valores" className="rd-h2">Hecho para <Hl>tu proyecto</Hl></h2>
          <ul className="ab-values__grid">
            {values.map(({ Icon, title, text, extra, photo, alt }, i) => (
              <Reveal as="li" key={title} i={i}>
                <article className="ab-val lift" tabIndex={0}>
                  <img className="zoom" src={photo} alt={alt} width="600" height="800" loading="lazy" />
                  <div className="ab-val__txt">
                    <Icon size={26} aria-hidden="true" />
                    <h3>{title}</h3>
                    <p>{text}</p>
                    <p className="ab-val__extra">{extra}</p>
                  </div>
                </article>
              </Reveal>
            ))}
          </ul>
        </div>
      </section>

      {/* Marcas: tarjetas con el logo oficial (gris que pasa a color) y las líneas que distribuimos */}
      <section className="rd-sec bg-white ab-brands" aria-labelledby="h-marcas">
        <div className="wrap">
          <SectionLabel n="04">Marcas</SectionLabel>
          <h2 id="h-marcas" className="rd-h2">Marcas que <Hl>distribuimos</Hl></h2>
          <ul className="ab-brands__grid">
            {brands.map((b, i) => (
              <Reveal as="li" key={b.name} i={i % 4}>
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

      {/* Visítanos */}
      <section className="rd-sec bg-soft ab-visit" aria-labelledby="h-visitanos" id="visitanos">
        <div className="wrap">
          <SectionLabel n="05">Visítanos</SectionLabel>
          <h2 id="h-visitanos" className="rd-h2">Estamos en <Hl>Bucaramanga</Hl></h2>
          <div className="ab-visit__grid">
            <Reveal side="l" className="ab-visit__map">
              <iframe title="Mapa: Bucaramanga, Santander, Colombia" src={site.mapUrl} loading="lazy" referrerPolicy="no-referrer-when-downgrade" allowFullScreen />
            </Reveal>
            <Reveal side="r" delay={0.12} className="ab-visit__info">
              <h3>{site.address}</h3>
              <p>{site.city}</p>
              <ul>
                {site.contacts.map((c) => <li key={c.href}><a href={c.href}>{c.label}</a></li>)}
              </ul>
              <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="o" icon={Navigation}>Cómo llegar</Pill>
            </Reveal>
          </div>
        </div>
      </section>
    </>
  )
}
