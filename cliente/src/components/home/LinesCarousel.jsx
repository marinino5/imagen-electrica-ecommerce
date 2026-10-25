import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import useEmblaCarousel from 'embla-carousel-react'
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-react'
import { useCategories } from '../../hooks/useCategories'
import { lineImage } from '../../lib/catalog'
import ErrorState from '../ui/ErrorState'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

export function CarouselArrows({ api }) {
  const [state, setState] = useState({ prev: false, next: false })
  useEffect(() => {
    if (!api) return
    const update = () => setState({ prev: api.canScrollPrev(), next: api.canScrollNext() })
    update()
    api.on('select', update).on('reInit', update)
    return () => {
      api.off('select', update).off('reInit', update)
    }
  }, [api])

  return (
    <div className="arrows">
      <button type="button" className="arrow" onClick={() => api?.scrollPrev()} disabled={!state.prev} aria-label="Anterior">
        <ChevronLeft size={22} />
      </button>
      <button type="button" className="arrow" onClick={() => api?.scrollNext()} disabled={!state.next} aria-label="Siguiente">
        <ChevronRight size={22} />
      </button>
    </div>
  )
}

function LineCard({ root, children, total }) {
  const image = lineImage(root.slug)
  return (
    <Link to={`/productos?category=${root.slug}`} className="line-card">
      {image ? <img src={image} alt="" width="640" height="800" loading="lazy" draggable="false" /> : <div className="line-card__fallback" />}
      <div className="line-card__shade" />
      <div className="line-card__body">
        <h3>{root.name}</h3>
        <p className="line-card__count">{total} {total === 1 ? 'producto' : 'productos'}</p>
        <div className="line-card__more">
          {children.length > 0 && <p>{children.map((c) => c.name).join(' · ')}</p>}
          <span>Ver productos <ArrowRight size={16} aria-hidden="true" /></span>
        </div>
      </div>
    </Link>
  )
}

export default function LinesCarousel() {
  const { data: categories, loading, error, reload } = useCategories()
  const [emblaRef, emblaApi] = useEmblaCarousel({ dragFree: true, align: 'start', containScroll: 'trimSnaps' })

  return (
    <section className="section" aria-labelledby="h-lineas">
      <div className="container">
        <div className="section-head">
          <Reveal>
            <h2 id="h-lineas" className="section-title">Explora por <em>línea</em></h2>
            <p className="section-lead">Todo el material eléctrico que tu planta necesita, organizado por línea de producto.</p>
          </Reveal>
          <CarouselArrows api={emblaApi} />
        </div>
      </div>

      <div className="lines-carousel container">
        {loading && <div className="skeleton" style={{ height: 420 }} aria-hidden="true" />}
        {error && <ErrorState error={error} onRetry={reload} />}
        {!loading && !error && (
          <div className="embla" ref={emblaRef}>
            <div className="embla__container">
              {categories.roots.map((root) => {
                const children = categories.childrenOf(root.id)
                const total = children.reduce((sum, c) => sum + (c.products_count ?? 0), root.products_count ?? 0)
                return (
                  <div className="embla__slide embla__slide--line" key={root.id}>
                    <LineCard root={root} children={children} total={total} />
                  </div>
                )
              })}
            </div>
          </div>
        )}
      </div>
    </section>
  )
}
