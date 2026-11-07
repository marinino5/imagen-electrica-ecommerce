import { Link } from 'react-router-dom'
import { withLogos } from '../config/brands'
import Bgfx from '../components/ui/Bgfx'
import Counter from '../components/ui/Counter'
import Hl from '../components/ui/Hl'
import Mix from '../components/ui/Mix'
import PhotoSlot from '../components/ui/PhotoSlot'
import Reveal from '../components/ui/Reveal'
import VisitFull from '../components/ui/VisitFull'
import { site } from '../config/site'
import '../styles/nosotros.css'
import '../styles/ie-nosotros.css'

// Equipo y almacén: espacios reservados. Cuando lleguen las fotos se ponen en `photo` y `alt` (ver docs/imagenes-pendientes.md).
const SLOTS = [
  { id: 'equipo', title: 'Nuestro equipo' },
  { id: 'almacen', title: 'Nuestro almacén por dentro' },
]

export default function Nosotros() {
  const brands = withLogos()

  return (
    <div className="ab-page">
      {/* Hero: texto con las dos cifras y la fachada en marco horizontal con una capa violeta detrás */}
      <section className="ab bg-lila-fade" aria-labelledby="h-nosotros">
        <Bgfx a="gear:l" c="r" arcTop="10%" />
        <div className="wrap ab__grid">
          <div className="ab__text">
            <h1 id="h-nosotros" className="ie-h1"><Mix light="Material eléctrico">industrial, <Hl>con experiencia</Hl></Mix></h1>
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

      <VisitFull id="visitanos" fx={{ a: 'plan:l', b: true }} />
    </div>
  )
}
