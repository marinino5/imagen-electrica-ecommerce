import { useCallback, useEffect, useState } from 'react'
import useEmblaCarousel from 'embla-carousel-react'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { useLines } from '../../hooks/useLines'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import LineCard from '../catalog/LineCard'
import '../../styles/home.css'

// Carril de fichas destacadas: las mismas tarjetas del catálogo
export default function HomeFeatured() {
  const { data: lines, loading } = useLines()
  const [ref, embla] = useEmblaCarousel({ align: 'start', containScroll: 'trimSnaps' })
  const [can, setCan] = useState({ prev: false, next: false })
  const featured = lines.filter((l) => l.is_featured)

  const update = useCallback(() => {
    if (embla) setCan({ prev: embla.canScrollPrev(), next: embla.canScrollNext() })
  }, [embla])

  useEffect(() => {
    if (!embla) return
    update()
    embla.on('select', update).on('reInit', update)
    return () => embla.off('select', update).off('reInit', update)
  }, [embla, update])

  if (!loading && featured.length === 0) return null

  return (
    <section className="hm-feat" aria-labelledby="h-destacados">
      <div className="wrap hm-section">
        <div className="hm-head">
          <div>
            <Eyebrow>Lo más pedido</Eyebrow>
            <h2 id="h-destacados" className="h2-sys">Fichas <em>destacadas</em></h2>
          </div>
          <div className="hm-head__actions">
            <button type="button" className="hm-arrow" onClick={() => embla?.scrollPrev()} disabled={!can.prev} aria-label="Anteriores"><ChevronLeft size={20} /></button>
            <button type="button" className="hm-arrow" onClick={() => embla?.scrollNext()} disabled={!can.next} aria-label="Siguientes"><ChevronRight size={20} /></button>
            <Pill as="link" to="/productos" tone="l" size="sm" className="hm-head__all">Ver catálogo</Pill>
          </div>
        </div>

        <div className="hm-rail" ref={ref}>
          <div className="hm-rail__track">
            {loading
              ? [0, 1, 2].map((i) => <div key={i} className="hm-rail__slide"><div className="skeleton" style={{ height: 400, borderRadius: 30 }} /></div>)
              : featured.map((l) => <div key={l.slug} className="hm-rail__slide"><LineCard line={l} /></div>)}
          </div>
        </div>
      </div>
    </section>
  )
}
