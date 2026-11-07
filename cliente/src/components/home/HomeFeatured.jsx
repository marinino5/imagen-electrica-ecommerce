import { useCallback, useEffect, useState } from 'react'
import useEmblaCarousel from 'embla-carousel-react'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { useLines } from '../../hooks/useLines'
import { usePromotions } from '../../hooks/usePromotions'
import SectionLabel from '../ui/SectionLabel'
import Hl from '../ui/Hl'
import Pill from '../ui/Pill'
import Reveal from '../ui/Reveal'
import LineCard from '../catalog/LineCard'
import '../../styles/home.css'

// Carril de fichas destacadas: imagen oficial, marca, rango, precio y botón rápido de agregar
export default function HomeFeatured() {
  const { data: lines, loading } = useLines()
  const { data: promos } = usePromotions()
  const [ref, embla] = useEmblaCarousel({ align: 'start', containScroll: 'trimSnaps' })
  const [can, setCan] = useState({ prev: false, next: false })
  // Sin repetidos y solo con foto oficial: una ficha destacada aparece una sola vez
  // y sin las que ya salen en Promociones, para no mostrar la misma imagen dos veces
  const promoted = new Set(promos.map((p) => p.line.slug))
  const featured = lines.filter((l, i) => l.is_featured && !promoted.has(l.slug) && lines.findIndex((x) => x.slug === l.slug) === i)

  const update = useCallback(() => embla && setCan({ prev: embla.canScrollPrev(), next: embla.canScrollNext() }), [embla])
  useEffect(() => {
    if (!embla) return
    update()
    embla.on('select', update).on('reInit', update)
    return () => embla.off('select', update).off('reInit', update)
  }, [embla, update])

  if (!loading && featured.length === 0) return null

  return (
    <section className="rd-sec bg-white hm-feat" aria-labelledby="h-destacados">
      <div className="wrap">
        <div className="hm-head">
          <div>
            <SectionLabel n="05">Lo más pedido</SectionLabel>
            <h2 id="h-destacados" className="rd-h2">Fichas <Hl>destacadas</Hl></h2>
          </div>
          <div className="hm-arrows">
            <button type="button" className="hm-arrow" onClick={() => embla?.scrollPrev()} disabled={!can.prev} aria-label="Anteriores"><ChevronLeft size={20} /></button>
            <button type="button" className="hm-arrow" onClick={() => embla?.scrollNext()} disabled={!can.next} aria-label="Siguientes"><ChevronRight size={20} /></button>
            <Pill as="link" to="/productos" tone="l" size="sm">Ver catálogo</Pill>
          </div>
        </div>

        <Reveal>
          <div className="hm-rail" ref={ref}>
            <div className="hm-rail__track">
              {loading
                ? [0, 1, 2].map((i) => <div key={i} className="hm-rail__slide"><div className="skeleton" style={{ height: 420, borderRadius: 8 }} /></div>)
                : featured.map((l) => <div key={l.slug} className="hm-rail__slide"><LineCard line={l} /></div>)}
            </div>
          </div>
        </Reveal>
      </div>
    </section>
  )
}
