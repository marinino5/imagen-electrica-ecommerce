import { useState } from 'react'
import { catalogApi } from '../../api'
import { useApi } from '../../hooks/useApi'
import { useBrandLines } from '../../hooks/useBrandLines'
import SectionHeading from '../ui/SectionHeading'

// Tarjeta blanca con el nombre de la marca. Si la marca tiene productos en la tienda, se voltea a naranja y
// muestra sus líneas: con el mouse al pasar encima, en celular con un toque.
function BrandCard({ brand, lines }) {
  const [flipped, setFlipped] = useState(false)
  const hasLines = lines.length > 0

  const face = (
    <>
      <span className="brand-card__face brand-card__front">{brand.name}</span>
      <span className="brand-card__face brand-card__back">
        <small>Líneas en la tienda</small>
        <span>{lines.join(' · ')}</span>
      </span>
    </>
  )

  if (!hasLines) {
    return <span className="brand-card"><span className="brand-card__face brand-card__front">{brand.name}</span></span>
  }
  return (
    <button type="button" className={`brand-card brand-card--flip ${flipped ? 'is-flipped' : ''}`} aria-pressed={flipped} onClick={() => setFlipped((f) => !f)} onBlur={() => setFlipped(false)}>
      {face}
    </button>
  )
}

// Una fila infinita: la lista se repite hasta llenar el ancho y se duplica para el bucle sin saltos
function Row({ brands, lines, reverse }) {
  const base = brands.length ? Array.from({ length: Math.max(1, Math.ceil(8 / brands.length)) }, () => brands).flat() : []
  return (
    <div className={`brand-row ${reverse ? 'brand-row--reverse' : ''}`}>
      <div className="brand-row__track">
        {[0, 1].map((copy) => (
          <ul key={copy} className="brand-row__group" aria-hidden={copy === 1 ? 'true' : undefined}>
            {base.map((brand, i) => (
              <li key={`${brand.id}-${i}`}><BrandCard brand={brand} lines={lines.get(brand.id) ?? []} /></li>
            ))}
          </ul>
        ))}
      </div>
    </div>
  )
}

// Marcas que distribuimos: dos filas de tarjetas en marquesina, en sentidos opuestos
export default function BrandMarquee() {
  const { data } = useApi(() => catalogApi.brands(), [])
  const lines = useBrandLines()
  const brands = data?.data ?? []
  if (brands.length === 0) return null

  const half = Math.ceil(brands.length / 2)
  const rows = brands.length > 1 ? [brands.slice(0, half), brands.slice(half)] : [brands, brands]

  return (
    <section className="section section--violet section--clip brands-flip" aria-labelledby="h-marcas">
      <span className="brands-flip__arc brands-flip__arc--a" aria-hidden="true" />
      <span className="brands-flip__arc brands-flip__arc--b" aria-hidden="true" />
      <div className="container">
        <SectionHeading id="h-marcas" eyebrow="Marcas" lead="Distribuimos las marcas que tu planta ya conoce. Pasa el mouse por una tarjeta (o tócala) para ver sus líneas.">
          Marcas que <em>distribuimos</em>
        </SectionHeading>
      </div>
      <div className="brands-flip__rows">
        <Row brands={rows[0]} lines={lines} />
        <Row brands={rows[1]} lines={lines} reverse />
      </div>
    </section>
  )
}
