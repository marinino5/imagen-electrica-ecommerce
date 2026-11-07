import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import { useFamilies } from '../../hooks/useFamilies'
import { familyIcon, familyNumber, familyPhoto } from '../../lib/families'
import SectionLabel from '../ui/SectionLabel'
import Reveal from '../ui/Reveal'
import Hl from '../ui/Hl'
import Pill from '../ui/Pill'
import Arc from '../ui/Arc'
import ErrorState from '../ui/ErrorState'
import '../../styles/home.css'

// Bento: Motores grande primero y las 8 familias, cada una con su foto. Al pasar el mouse (o enfocar) aparece la lista de
// categorías con su contador. Sin foto (Compresores) se dibuja el diseño de marca, nunca un render.
function FamilyTile({ family, index }) {
  const photo = familyPhoto(family.slug)
  const Icon = familyIcon(family.slug)
  const size = index === 0 ? 'fam--big' : index === 3 ? 'fam--wide' : ''
  const cats = family.categories ?? []

  return (
    <Reveal as="li" i={index} className={`fam ${size}`}>
      <article className="fam__card lift">
        <div className={`fam__media ${photo?.product === false ? 'is-photo' : ''}`} aria-hidden="true">
          {photo ? (
            <img className={`zoom ${photo.product ? 'prod-img' : ''}`} src={photo.src} alt="" width="800" height="800" loading="lazy" />
          ) : (
            <span className="fam__art"><Icon strokeWidth={1.1} /></span>
          )}
        </div>
        <div className="fam__body">
          <span className="fam__num" aria-hidden="true">{familyNumber(index)}</span>
          <h3><Link to={`/productos?familia=${family.slug}`} className="fam__link">{family.name}</Link></h3>
          <p className="fam__count">{family.lines_count} {family.lines_count === 1 ? 'ficha' : 'fichas'}</p>
        </div>
        {cats.length > 0 && (
          <ul className="fam__cats" aria-label={`Categorías de ${family.name}`}>
            {cats.map((c) => (
              <li key={c.id}>
                <Link to={`/productos?familia=${family.slug}&categoria=${c.slug}`}>{c.name}<span>{c.lines_count}</span></Link>
              </li>
            ))}
          </ul>
        )}
        <span className="fam__arrow" aria-hidden="true"><ArrowUpRight size={20} /></span>
      </article>
    </Reveal>
  )
}

export default function HomeLines() {
  const { data: families, loading, error, reload } = useFamilies()

  return (
    <section className="rd-sec bg-lav tex-dots hm-fams" id="portafolio" aria-labelledby="h-lineas">
      <Arc pos="tr" />
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
