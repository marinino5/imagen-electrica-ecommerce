import { useState } from 'react'
import { Link } from 'react-router-dom'
import useEmblaCarousel from 'embla-carousel-react'
import ProductCard from '../ui/ProductCard'
import QuickView from '../ui/QuickView'
import ErrorState from '../ui/ErrorState'
import CarouselArrows from '../ui/CarouselArrows'
import SectionHeading from '../ui/SectionHeading'
import '../../styles/home.css'

export default function FeaturedRail({ featured }) {
  const { data, loading, error, reload } = featured
  const [emblaRef, emblaApi] = useEmblaCarousel({ align: 'start', containScroll: 'trimSnaps', slidesToScroll: 1 })
  const [quick, setQuick] = useState(null)
  const products = data?.data ?? []

  return (
    <section className="section section--violet section--clip" aria-labelledby="h-destacados">
      <span className="feat__arc feat__arc--a" aria-hidden="true" />
      <span className="feat__arc feat__arc--b" aria-hidden="true" />
      <div className="container">
        <SectionHeading
          id="h-destacados"
          eyebrow="Lo más pedido"
          lead="Referencias con alta rotación: compra en línea o pide tu cotización."
          actions={
            <div className="section-head__actions">
              <Link to="/productos" className="btn btn--ghost-light btn--sm">Ver todo el catálogo</Link>
              <CarouselArrows api={emblaApi} light />
            </div>
          }
        >
          Destacados y <em>ofertas</em>
        </SectionHeading>

        {loading && (
          <div className="rail-skeleton" aria-hidden="true">
            {[0, 1, 2, 3].map((i) => <div key={i} className="skeleton" style={{ height: 380 }} />)}
          </div>
        )}
        {error && <ErrorState error={error} onRetry={reload} />}
        {!loading && !error && products.length > 0 && (
          <div className="embla" ref={emblaRef}>
            <div className="embla__container">
              {products.map((p, i) => (
                <div className="embla__slide embla__slide--product" key={p.id}>
                  <ProductCard product={p} onQuickView={setQuick} index={i} total={products.length} />
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
