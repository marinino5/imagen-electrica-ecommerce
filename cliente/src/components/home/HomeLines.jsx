import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import { useFamilies } from '../../hooks/useFamilies'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import Reveal from '../ui/Reveal'
import ErrorState from '../ui/ErrorState'
import '../../styles/home.css'

// Imagen de cada familia (public/img/categorias)
const FAMILY_IMAGE = {
  'motores-electricos': 'motores',
  'reductores-y-motorreductores': 'motorreductores',
  'control-y-automatizacion': 'variadores-arrancadores',
  'ventilacion-y-climatizacion': 'ventilacion',
  'cables-y-conductores': 'cables',
  iluminacion: 'iluminacion',
  compresores: 'compresores',
}

// Bento asimétrico: 2 tarjetas grandes (motores y control) y 4 pequeñas
const BIG = ['motores-electricos', 'control-y-automatizacion']
const SMALL = ['reductores-y-motorreductores', 'ventilacion-y-climatizacion', 'cables-y-conductores', 'iluminacion']

function FamilyTile({ family, big }) {
  const image = FAMILY_IMAGE[family.slug]
  return (
    <Link to={`/productos?familia=${family.slug}`} className={`bz hm-tile ${big ? 'hm-tile--big' : ''}`}>
      <span className="core">
        <span className="hm-tile__media">
          {image && <img src={`/img/categorias/${image}.webp`} alt="" width="900" height="675" loading="lazy" />}
        </span>
        <span className="hm-tile__body">
          <span>
            <small>{family.lines_count} {family.lines_count === 1 ? 'ficha' : 'fichas'}</small>
            <strong>{family.name}</strong>
          </span>
          <span className="hm-tile__arrow" aria-hidden="true"><ArrowUpRight size={20} /></span>
        </span>
      </span>
    </Link>
  )
}

export default function HomeLines() {
  const { data: families, loading, error, reload } = useFamilies()
  const bySlug = (slug) => families.find((f) => f.slug === slug)
  const big = BIG.map(bySlug).filter(Boolean)
  const small = SMALL.map(bySlug).filter(Boolean)

  return (
    <section className="wrap hm-section" aria-labelledby="h-lineas">
      <div className="hm-head">
        <div>
          <Eyebrow>Líneas del portafolio</Eyebrow>
          <h2 id="h-lineas" className="h2-sys">Todo para <em>tu planta</em></h2>
        </div>
        <Pill as="link" to="/productos" tone="l" size="sm">Ver todo el catálogo</Pill>
      </div>

      {loading && <div className="skeleton" style={{ height: 420, borderRadius: 30 }} aria-hidden="true" />}
      {error && <ErrorState error={error} onRetry={reload} />}
      {!loading && !error && (
        <div className="hm-bento">
          {big.map((f, i) => <Reveal key={f.id} delay={i * 0.06} className="hm-bento__big"><FamilyTile family={f} big /></Reveal>)}
          {small.map((f, i) => <Reveal key={f.id} delay={0.1 + i * 0.05} className="hm-bento__small"><FamilyTile family={f} /></Reveal>)}
        </div>
      )}
    </section>
  )
}
