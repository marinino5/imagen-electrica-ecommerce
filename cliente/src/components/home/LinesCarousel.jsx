import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import { useCategories } from '../../hooks/useCategories'
import { lineImage } from '../../lib/catalog'
import ErrorState from '../ui/ErrorState'
import Reveal from '../ui/Reveal'
import SectionHeading from '../ui/SectionHeading'
import '../../styles/home.css'

// Mosaico sin huecos en 4 columnas: dos tarjetas grandes de 2×2 y el resto de 1×1.
// Las tarjetas de la última fila se reparten el ancho para que no quede un hueco.
function layoutFor(count) {
  const bigs = count >= 6 ? 2 : 0
  const smalls = count - bigs
  const tail = smalls % 4
  return Array.from({ length: count }, (_, i) => {
    if (i < bigs) return 'big'
    const fromEnd = count - i // 1 = última tarjeta
    if (tail === 1) return 'span-4'
    if (tail === 2 && fromEnd <= 2) return 'span-2'
    if (tail === 3 && fromEnd === 1) return 'span-2'
    return ''
  })
}

const pad = (n) => String(n).padStart(2, '0')

function LineTile({ root, total, size, index, count }) {
  const image = lineImage(root.slug)
  return (
    <Link to={`/productos?category=${root.slug}`} className={`line-tile ${size ? `line-tile--${size}` : ''}`}>
      <span className="line-tile__media">
        {image ? <img src={image} alt="" width="640" height="480" loading="lazy" draggable="false" /> : <span className="line-tile__fallback" />}
      </span>
      <span className="line-tile__body">
        <span>
          <small className="line-tile__idx" aria-hidden="true">{pad(index + 1)} / {pad(count)}</small>
          <strong>{root.name}</strong>
          <small>{total > 0 ? `${total} ${total === 1 ? 'producto' : 'productos'}` : 'Bajo pedido'}</small>
        </span>
        <ArrowRight size={22} aria-hidden="true" className="line-tile__arrow" />
      </span>
    </Link>
  )
}

export default function LinesCarousel() {
  const { data: categories, loading, error, reload } = useCategories()
  const layout = layoutFor(categories.roots.length)

  return (
    <section className="section section--clip" aria-labelledby="h-lineas">
      {/* Arco lavanda gigante y puntos detrás del título */}
      <span className="lines__arc" aria-hidden="true" />
      <span className="deco-dots lines__dots" aria-hidden="true" />

      <div className="container">
        <SectionHeading
          id="h-lineas"
          eyebrow="Catálogo"
          lead="Todo el material eléctrico que tu planta necesita, organizado por línea de producto."
          actions={<Link to="/productos" className="btn btn--ghost btn--sm">Ver todo el catálogo <ArrowRight size={16} aria-hidden="true" /></Link>}
        >
          Explora por <em>línea</em>
        </SectionHeading>

        {loading && <div className="skeleton" style={{ height: 420 }} aria-hidden="true" />}
        {error && <ErrorState error={error} onRetry={reload} />}
        {!loading && !error && (
          <div className="bento-lines">
            {categories.roots.map((root, i) => {
              const children = categories.childrenOf(root.id)
              const total = children.reduce((sum, c) => sum + (c.products_count ?? 0), root.products_count ?? 0)
              return (
                <Reveal key={root.id} delay={(i % 4) * 0.05} className={`bento-lines__cell ${layout[i] === 'big' ? 'is-big' : layout[i]}`}>
                  <LineTile root={root} total={total} size={layout[i] === 'big' ? 'big' : ''} index={i} count={categories.roots.length} />
                </Reveal>
              )
            })}
          </div>
        )}
      </div>
    </section>
  )
}
