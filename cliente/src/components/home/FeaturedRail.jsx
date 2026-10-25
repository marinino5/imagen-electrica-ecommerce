import { useState } from 'react'
import { Link } from 'react-router-dom'
import useEmblaCarousel from 'embla-carousel-react'
import ProductCard from '../ui/ProductCard'
import QuickView from '../ui/QuickView'
import ErrorState from '../ui/ErrorState'
import Reveal from '../ui/Reveal'
import { CarouselArrows } from './LinesCarousel'
import '../../styles/home.css'

export default function FeaturedRail({ featured }) {
  const { data, loading, error, reload } = featured
  const [emblaRef, emblaApi] = useEmblaCarousel({ align: 'start', containScroll: 'trimSnaps', slidesToScroll: 1 })
  const [quick, setQuick] = useState(null)
  const products = data?.data ?? []

  return (
    <section className="section section--surface" aria-labelledby="h-destacados">
      <div className="container">
        <div className="section-head">
          <Reveal>
            <h2 id="h-destacados" className="section-title">Destacados y <em>ofertas</em></h2>
            <p className="section-lead">Referencias con alta rotación. Compra en línea o pide tu cotización.</p>
          </Reveal>
          <div className="section-head__actions">
            <Link to="/productos" className="btn btn--ghost btn--sm">Ver todo el catálogo</Link>
            <CarouselArrows api={emblaApi} />
          </div>
        </div>

        {loading && (
          <div className="rail-skeleton" aria-hidden="true">
            {[0, 1, 2, 3].map((i) => <div key={i} className="skeleton" style={{ height: 380 }} />)}
          </div>
        )}
        {error && <ErrorState error={error} onRetry={reload} />}
        {!loading && !error && products.length > 0 && (
          <div className="embla" ref={emblaRef}>
            <div className="embla__container">
              {products.map((p) => (
                <div className="embla__slide embla__slide--product" key={p.id}>
                  <ProductCard product={p} onQuickView={setQuick} />
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
      <QuickView product={quick} onClose={() => setQuick(null)} />
    </section>
  )
}
