import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import { useFamilies } from '../../hooks/useFamilies'
import { familyIcon, familyNumber, familyPhoto } from '../../lib/families'
import SectionLabel from '../ui/SectionLabel'
import Reveal from '../ui/Reveal'
import Hl from '../ui/Hl'
import Pill from '../ui/Pill'
import ErrorState from '../ui/ErrorState'
import Bgfx from '../ui/Bgfx'
import '../../styles/home.css'
import '../../styles/home2.css'

// Las 8 familias en una cuadrícula pareja: todas con la imagen recortada en el mismo formato (mismo tamaño visual y mismo
// margen). Al pasar el mouse (o enfocar) aparece la lista de categorías con su contador. Compresores usa la ilustración
// de marca en el mismo formato (ver docs/imagenes-pendientes.md).
function FamilyTile({ family, index }) {
  const photo = familyPhoto(family.slug)
  const Icon = familyIcon(family.slug)
  const cats = family.categories ?? []

  return (
    <Reveal as="li" i={index} className="fam">
      <article className="fam__card lift">
        <div className="fam__media" aria-hidden="true">
          {photo ? (
            <img className="zoom" src={photo.sm} alt="" width="480" height="480" loading="lazy" />
          ) : (
            <span className="fam__art"><Icon strokeWidth={1.1} /></span>
          )}
          {cats.length > 0 && (
            <ul className="fam__cats" aria-hidden="true">
              {cats.map((c) => <li key={c.id}><span>{c.name}<b>{c.lines_count}</b></span></li>)}
            </ul>
          )}
        </div>
        <div className="fam__body">
          <span className="fam__num" aria-hidden="true">{familyNumber(index)}</span>
          <h3><Link to={`/productos?familia=${family.slug}`} className="fam__link">{family.name}</Link></h3>
          <p className="fam__count">{family.lines_count} {family.lines_count === 1 ? 'ficha' : 'fichas'}</p>
        </div>
        <span className="fam__arrow" aria-hidden="true"><ArrowUpRight size={18} /></span>
      </article>
    </Reveal>
  )
}

export default function HomeLines() {
  const { data: families, loading, error, reload } = useFamilies()

  return (
    <section className="rd-sec bg-lav hm-fams" id="portafolio" aria-labelledby="h-lineas">
      <Bgfx a="gear:r" b />
            <div className="wrap">
        <div className="hm-head">
          <div>
            <SectionLabel n="01">Portafolio</SectionLabel>
            <h2 id="h-lineas" className="rd-h2">Todo para <Hl>tu proyecto</Hl></h2>
          </div>
          <Pill as="link" to="/productos" tone="l" size="sm">Ver todo el catálogo</Pill>
        </div>

        {loading && <div className="skeleton" style={{ height: 520, borderRadius: 8 }} aria-hidden="true" />}
        {error && <ErrorState error={error} onRetry={reload} />}
        {!loading && !error && (
          <ul className="fam-grid">
            {families.map((f, i) => <FamilyTile key={f.id} family={f} index={i} />)}
          </ul>
        )}
      </div>
    </section>
  )
}
