import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import { useFamilies } from '../../hooks/useFamilies'
import { FAMILY_COVER, familyIcon, familyNumber } from '../../lib/families'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import ErrorState from '../ui/ErrorState'
import '../../styles/home.css'

// Bento de las familias del portafolio. Cada tarjeta lleva su portada, el número real de fichas (de la API) y,
// al pasar el mouse o enfocar con el teclado, las categorías que contiene. Sin portada se dibuja el diseño de marca.
function FamilyTile({ family, index }) {
  const cover = FAMILY_COVER[family.slug]
  const Icon = familyIcon(family.slug)
  const size = index === 0 ? 'hm-fam--big' : index === 3 ? 'hm-fam--wide' : ''
  const cats = family.categories ?? []

  return (
    <li className={`hm-fam ${size}`}>
      <article className="hm-fam__card">
        <div className="hm-fam__media" aria-hidden="true">
          {cover ? (
            <img src={`/img/categorias/${cover}.webp`} alt="" width="900" height="675" loading="lazy" />
          ) : (
            <span className="hm-fam__art"><span className="hm-fam__arc" /><Icon strokeWidth={1.3} /></span>
          )}
        </div>
        <div className="hm-fam__body">
          <span className="hm-fam__num" aria-hidden="true">{familyNumber(index)}</span>
          <h3>
            <Link to={`/productos?familia=${family.slug}`} className="hm-fam__link">{family.name}</Link>
          </h3>
          <p className="hm-fam__count">{family.lines_count} {family.lines_count === 1 ? 'ficha' : 'fichas'}</p>
          {cats.length > 0 && (
            <ul className="hm-fam__cats" aria-label={`Categorías de ${family.name}`}>
              {cats.map((c) => (
                <li key={c.id}>
                  <Link to={`/productos?familia=${family.slug}&categoria=${c.slug}`}>{c.name}<span>{c.lines_count}</span></Link>
                </li>
              ))}
            </ul>
          )}
          <span className="hm-fam__arrow" aria-hidden="true"><ArrowUpRight size={20} /></span>
        </div>
      </article>
    </li>
  )
}

export default function HomeLines() {
  const { data: families, loading, error, reload } = useFamilies()

  return (
    <section className="wrap hm-section" id="portafolio" aria-labelledby="h-lineas">
      <div className="hm-head">
        <div>
          <Eyebrow>Portafolio</Eyebrow>
          <h2 id="h-lineas" className="h2-sys">Todo para <em>tu proyecto</em></h2>
        </div>
        <Pill as="link" to="/productos" tone="l" size="sm">Ver todo el catálogo</Pill>
      </div>

      {loading && <div className="skeleton" style={{ height: 520, borderRadius: 30 }} aria-hidden="true" />}
      {error && <ErrorState error={error} onRetry={reload} />}
      {!loading && !error && (
        <ul className="hm-bento">
          {families.map((f, i) => <FamilyTile key={f.id} family={f} index={i} />)}
        </ul>
      )}
    </section>
  )
}
