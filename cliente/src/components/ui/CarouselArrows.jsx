import { useEffect, useState } from 'react'
import { ChevronLeft, ChevronRight } from 'lucide-react'

// Flechas de un carrusel Embla. `light` las dibuja en blanco para fondos violeta u oscuros.
export default function CarouselArrows({ api, light = false }) {
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
    <div className={`arrows ${light ? 'arrows--light' : ''}`}>
      <button type="button" className="arrow" onClick={() => api?.scrollPrev()} disabled={!state.prev} aria-label="Anterior">
        <ChevronLeft size={22} />
      </button>
      <button type="button" className="arrow" onClick={() => api?.scrollNext()} disabled={!state.next} aria-label="Siguiente">
        <ChevronRight size={22} />
      </button>
    </div>
  )
}
