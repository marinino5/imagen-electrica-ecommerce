import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import { useCategories } from '../../hooks/useCategories'
import { lineImage, lineInfo } from '../../lib/catalog'
import HorizontalScroller from '../ui/HorizontalScroller'
import SectionHeading from '../ui/SectionHeading'

const pad = (n) => String(n).padStart(2, '0')

// Tarjeta grande al estilo del catálogo: la imagen de la línea ya trae su círculo, arco y curva; lleva "01 / 08" en naranja
function LineCard({ root, index, count }) {
  const image = lineImage(root.slug)
  return (
    <article className="line-card">
      <div className="line-card__media">
        {image && <img src={image} alt="" width="640" height="480" loading="lazy" draggable="false" />}
      </div>
      <div className="line-card__body">
        <p className="line-card__idx" aria-hidden="true">{pad(index + 1)} / {pad(count)}</p>
        <h3>{root.name}</h3>
        <p className="line-card__text">{lineInfo(root.slug)}</p>
        <Link to={`/productos?category=${root.slug}`} className="line-card__link">
          Ver productos <ArrowRight size={18} aria-hidden="true" />
        </Link>
      </div>
    </article>
  )
}

// "Todo para tu proyecto": las líneas pasan de lado mientras la sección queda fija
export default function LinesScroller() {
  const { data: categories, loading } = useCategories()
  const roots = categories.roots

  if (!loading && roots.length === 0) return null

  return (
    <section className="section--violet lines-scroll" aria-labelledby="h-hacemos">
      {loading ? (
        <div className="container lines-scroll__loading"><div className="skeleton" style={{ height: 380 }} aria-hidden="true" /></div>
      ) : (
        <HorizontalScroller
          head={
            <div className="container lines-scroll__head">
              <SectionHeading id="h-hacemos" eyebrow="Qué hacemos" lead="Nuestras líneas de producto, de lado a lado.">
                Todo para tu <em>proyecto</em>
              </SectionHeading>
            </div>
          }
        >
          {roots.map((root, i) => <LineCard key={root.id} root={root} index={i} count={roots.length} />)}
        </HorizontalScroller>
      )}
    </section>
  )
}
