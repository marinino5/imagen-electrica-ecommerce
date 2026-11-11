import { Link } from 'react-router-dom'
import { cardImage, cardState, hpText, lineBrands, modeTag, priceNote, priceText } from '../../lib/lines'
import BrandMark from '../ui/BrandMark'
import LineVisual from './LineVisual'
import QuickAdd from './QuickAdd'
import '../../styles/line-card.css'

// Mismo rango escrito de dos formas («0,25 HP – 3 HP» y «0,25–3 HP») cuenta como uno solo: se compara sin unidades,
// espacios ni guiones y se conserva la primera forma.
const uniqueChips = (list) => {
  const seen = new Set()
  return list.filter(Boolean).filter((c) => {
    const key = c.toLowerCase().replace(/hp|\s|–|-/g, '')
    return !seen.has(key) && seen.add(key)
  })
}

// Tarjeta por LÍNEA (la ficha): imagen oficial grande sobre fondo claro, marca con su logo, nombre, rangos y precio "Desde".
// Dos acciones: «Ver ficha» (contorno) y el botón circular de compra rápida (QuickAdd).
// Si la línea tiene una segunda vista (image_alt_url) la imagen cambia a ella al pasar el mouse.
export default function LineCard({ line, className = '' }) {
  const image = cardImage(line)
  const brands = lineBrands(line)
  const range = line.hp_values?.length ? `${hpText(line.hp_min)} – ${hpText(line.hp_max)}` : null
  const all = uniqueChips([...(line.spec_chips ?? []), range])
  const chips = all.slice(0, 2)
  const more = all.length - chips.length
  const tagClass = cardState(line) === 'buy' ? 'is-on' : 'is-q'

  return (
    <article className={`pc lift ${className}`}>
      <Link to={`/productos/${line.slug}`} className="pc__media" tabIndex={-1} aria-hidden="true">
        {image ? (
          <>
            <img className="pc__img zoom" src={image} alt="" width="600" height="600" loading="lazy" />
            {line.image_alt_url && <img className="pc__img pc__img--alt" src={line.image_alt_url} alt="" width="600" height="600" loading="lazy" />}
          </>
        ) : (
          <LineVisual line={line} />
        )}
      </Link>
      <span className={`pc__tag ${tagClass}`}>{modeTag(line)}</span>

      <div className="pc__body">
        {/* Fila 1: logos (máximo 2; si hay más, «+N»). Fila 2: la categoría en texto pequeño. Nunca superpuestos. */}
        <div className="pc__brand">
          <span className="pc__logos">
            {brands.slice(0, 2).map((b) => <BrandMark key={b} name={b} />)}
            {brands.length > 2 && <span className="pc__logos-more" aria-label={`${brands.length - 2} marcas más`}>+{brands.length - 2}</span>}
          </span>
          <span className="pc__cat">{line.category?.name}</span>
        </div>
        <h3 className="pc__name">
          <Link to={`/productos/${line.slug}`}>{line.name}</Link>
        </h3>
        {chips.length > 0 && (
          <ul className="pc__chips">
            {chips.map((s) => <li key={s}>{s}</li>)}
            {more > 0 && <li className="pc__more-chip" aria-label={`${more} datos más`}>+{more}</li>}
          </ul>
        )}
        <p className="pc__price">
          <strong>{priceText(line)}</strong>
          <small>{priceNote(line)}</small>
        </p>
        <div className="pc__actions">
          <Link to={`/productos/${line.slug}`} className="pc__more" aria-label={`Ver ficha de ${line.name}`}>Ver ficha</Link>
          <QuickAdd line={line} image={image} />
        </div>
      </div>
    </article>
  )
}
