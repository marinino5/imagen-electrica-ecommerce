import { useCallback, useEffect, useState } from 'react'
import useEmblaCarousel from 'embla-carousel-react'

// Galería táctil (embla). Con una sola imagen no muestra miniaturas ni puntos.
export default function ProductGallery({ images, name, framed = true }) {
  const [viewport, embla] = useEmblaCarousel({ loop: false })
  const [selected, setSelected] = useState(0)

  useEffect(() => {
    if (!embla) return
    const onSelect = () => setSelected(embla.selectedScrollSnap())
    onSelect()
    embla.on('select', onSelect)
    return () => embla.off('select', onSelect)
  }, [embla])

  const go = useCallback((i) => embla?.scrollTo(i), [embla])

  if (images.length === 0) {
    return <div className="gallery gallery--empty" role="img" aria-label="Sin imagen disponible">Sin imagen</div>
  }

  return (
    <div className="gallery">
      <div className={`gallery__viewport ${framed ? 'gallery__viewport--framed' : ''}`} ref={viewport}>
        <div className="gallery__track">
          {images.map((src, i) => (
            <div className="gallery__slide" key={src} role="group" aria-roledescription="imagen" aria-label={`${i + 1} de ${images.length}`}>
              <img src={src} alt={i === 0 ? name : `${name}, vista ${i + 1}`} width="900" height="675" loading={i === 0 ? 'eager' : 'lazy'} fetchPriority={i === 0 ? 'high' : undefined} />
            </div>
          ))}
        </div>
      </div>
      {images.length > 1 && (
        <div className="gallery__thumbs" role="tablist" aria-label="Miniaturas">
          {images.map((src, i) => (
            <button key={src} type="button" role="tab" aria-selected={i === selected} aria-label={`Ver imagen ${i + 1}`} className={i === selected ? 'is-on' : ''} onClick={() => go(i)}>
              <img src={src} alt="" width="72" height="54" loading="lazy" />
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
