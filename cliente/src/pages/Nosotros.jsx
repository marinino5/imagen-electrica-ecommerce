import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion, useScroll, useTransform } from 'framer-motion'
import { site } from '../config/site'
import { withLogos } from '../config/brands'
import { useFamilies } from '../hooks/useFamilies'
import { useMediaQuery } from '../hooks/useMediaQuery'
import Counter from '../components/ui/Counter'
import Hl from '../components/ui/Hl'
import Mix from '../components/ui/Mix'
import Reveal from '../components/ui/Reveal'
import SectionLabel from '../components/ui/SectionLabel'
import VisitFull from '../components/ui/VisitFull'
import Xb from '../components/ui/Xb'
import '../styles/nosotros.css'
import '../styles/ie-nosotros.css'

// Galería horizontal: fachada, interior del almacén, mostrador, despacho y equipo. Las fotos que aún no existen muestran un
// panel «Próximamente» (pendientes en docs/imagenes-pendientes.md); no hay texto de relleno.
const GALLERY = [
  { id: 'fachada', caption: 'Nuestra fachada en Bucaramanga', photo: '/img/fachada.webp', alt: 'Fachada de la tienda Imagen Eléctrica con su letrero' },
  { id: 'interior', caption: 'El almacén por dentro' },
  { id: 'mostrador', caption: 'El mostrador' },
  { id: 'despacho', caption: 'Despacho de pedidos', photo: '/img/fotos/despacho-montacargas.webp', alt: 'Bodega con estanterías y un montacargas listo para despachar cajas' },
  { id: 'equipo', caption: 'Nuestro equipo' },
]

function Slide({ g }) {
  return (
    <figure className="gal2__slide">
      {g.photo ? (
        <img src={g.photo} alt={g.alt} width="1200" height="800" loading="lazy" />
      ) : (
        <div className="gal2__soon bg-lila-soft" role="img" aria-label={`${g.caption}: próximamente`}>
          <img className="gal2__mark" src="/img/logo-ajustado.png" alt="" width="120" height="145" />
          <span>Próximamente</span>
        </div>
      )}
      <figcaption>{g.caption}</figcaption>
    </figure>
  )
}

// Con el scroll, la sección se queda fija y las fotos se desplazan en horizontal. En celular es un carrusel normal.
function Gallery() {
  const wide = useMediaQuery('(min-width: 900px)')
  const sec = useRef(null)
  const track = useRef(null)
  const [shift, setShift] = useState(0)
  const { scrollYProgress } = useScroll({ target: sec, offset: ['start start', 'end end'] })
  const x = useTransform(scrollYProgress, [0, 1], [0, -shift])

  useEffect(() => {
    if (!wide) return
    const measure = () => track.current && setShift(Math.max(0, track.current.scrollWidth - window.innerWidth))
    measure()
    window.addEventListener('resize', measure)
    return () => window.removeEventListener('resize', measure)
  }, [wide])

  if (!wide) {
    return (
      <section className="gal2 gal2--swipe bg-lila-fade" aria-label="Galería">
        <ul className="gal2__row">{GALLERY.map((g) => <li key={g.id}><Slide g={g} /></li>)}</ul>
      </section>
    )
  }
  return (
    <section className="gal2 bg-lila-fade" ref={sec} style={{ height: `calc(100vh + ${shift}px)` }} aria-label="Galería">
      <div className="gal2__pin">
        <motion.ul className="gal2__row" ref={track} style={{ x }}>{GALLERY.map((g) => <li key={g.id}><Slide g={g} /></li>)}</motion.ul>
      </div>
    </section>
  )
}

// Tres hitos con una línea vertical que se llena con el scroll. Solo datos reales: años, referencias del catálogo y la tienda en línea.
function Milestones({ references }) {
  const list = useRef(null)
  useEffect(() => {
    const el = list.current
    if (!el) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return el.style.setProperty('--fill', '1')
    let frame = 0
    const update = () => {
      frame = 0
      const r = el.getBoundingClientRect()
      el.style.setProperty('--fill', String(Math.min(1, Math.max(0, (window.innerHeight * 0.65 - r.top) / r.height))))
    }
    const onScroll = () => { if (!frame) frame = requestAnimationFrame(update) }
    update()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
    return () => { cancelAnimationFrame(frame); window.removeEventListener('scroll', onScroll); window.removeEventListener('resize', onScroll) }
  }, [])

  const items = [
    { big: <Counter to={site.years} prefix="+" />, title: 'años en el sector', text: 'Una distribuidora de material eléctrico industrial en Bucaramanga, Santander, que acompaña a empresas y técnicos.' },
    references > 0 && { big: <Counter to={references} />, title: 'referencias en catálogo', text: 'Motores, variadores, cables, iluminación y automatización de marcas reconocidas, con su disponibilidad real.' },
    { big: <span>Hoy</span>, title: 'también en línea', text: 'La misma atención de la tienda, ahora desde el celular: compra lo disponible o cotiza lo que necesites.' },
  ].filter(Boolean)

  return (
    <section className="rd-sec bg-lila-soft ms" aria-labelledby="h-historia">
      <div className="wrap">
        <SectionLabel n="03">Nuestra historia</SectionLabel>
        <h2 id="h-historia" className="rd-h2"><Mix light="Experiencia que"><Hl>se nota</Hl></Mix></h2>
        <ol className="ms__list" ref={list}>
          <span className="ms__line" aria-hidden="true"><span /></span>
          {items.map((m, i) => (
            <Reveal as="li" key={m.title} i={i} className="ms__item">
              <span className="ms__dot" aria-hidden="true" />
              <b className="ms__big">{m.big}</b>
              <div><h3>{m.title}</h3><p>{m.text}</p></div>
            </Reveal>
          ))}
        </ol>
      </div>
    </section>
  )
}

export default function Nosotros() {
  const { meta } = useFamilies()
  const brands = withLogos()

  return (
    <>
      {/* 10.1 Hero editorial: el «25» en contorno que se rellena de abajo hacia arriba y una sola foto de la fachada en arco */}
      <section className="ab2 bg-lila-fade" aria-labelledby="h-nosotros">
        <Xb
          side="r"
          arch
          col="52%"
          ratio="4 / 5"
          photo={<img src="/img/fachada.webp" alt="Fachada de la tienda Imagen Eléctrica en Bucaramanga, con su letrero y el interior iluminado" width="1600" height="800" fetchPriority="high" loading="eager" />}
        >
          <p className="rd-label">Nosotros</p>
          <h1 id="h-nosotros" className="ab2__h1">
            <span className="ab2__25" aria-hidden="true"><i>{site.years}</i><i className="ab2__fill">{site.years}</i></span>
            <span className="sr-only">{site.years} años en el sector</span>
            <span className="ab2__years" aria-hidden="true">Años en el sector</span>
          </h1>
          {meta.references > 0 && (
            <p className="ab2__refs"><Counter to={meta.references} className="ab2__refs-n" /> <span>referencias en catálogo</span></p>
          )}
          <p className="rd-lead">Material eléctrico industrial en Bucaramanga, con la experiencia de quien conoce cada referencia.</p>
        </Xb>
      </section>

      {/* 10.2 Galería en scroll horizontal fijado */}
      <Gallery />

      {/* 10.3 Historia en tres hitos */}
      <Milestones references={meta.references} />

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

      {/* 10.5 Visítanos: mapa grande con tarjeta flotante (sin repetir la foto de la fachada) */}
      <VisitFull id="visitanos" n="05" />
    </>
  )
}
