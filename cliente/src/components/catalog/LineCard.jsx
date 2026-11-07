import { Link } from 'react-router-dom'
import { Plus } from 'lucide-react'
import { useCart } from '../../context/CartContext'
import { cartItem, hpText, lineImage, modeTag, priceNote, priceText } from '../../lib/lines'
import BrandMark from '../ui/BrandMark'
import LineVisual from './LineVisual'
import '../../styles/line-card.css'

// Tarjeta por LÍNEA (la ficha): imagen oficial grande sobre fondo claro, marca con su logo, nombre, rangos y precio "Desde".
// Si la línea tiene una segunda vista (image_alt_url) la imagen cambia a ella al pasar el mouse.
export default function LineCard({ line, className = '' }) {
  const { add } = useCart()
  const image = lineImage(line)
  const brand = (line.brand_label ?? '').split(' · ')[0]
  const quick = line.quick_variant
  const range = line.hp_values?.length ? `${hpText(line.hp_min)} – ${hpText(line.hp_max)}` : null
  const chips = [range, ...(line.spec_chips ?? [])].filter(Boolean).slice(0, 3)
  const tagClass = line.sale_mode === 'direct' ? 'is-on' : 'is-q'

  return (
    <article className={`pc lift ${className}`}>
      <Link to={`/productos/${line.slug}`} className="pc__media" tabIndex={-1} aria-hidden="true">
        {image ? (
          <>
            <img className="pc__img zoom prod-img" src={image} alt="" width="600" height="600" loading="lazy" />
            {line.image_alt_url && <img className="pc__img pc__img--alt prod-img" src={line.image_alt_url} alt="" width="600" height="600" loading="lazy" />}
          </>
        ) : (
          <LineVisual line={line} />
        )}
      </Link>
      <span className={`pc__tag ${tagClass}`}>{modeTag(line)}</span>

      <div className="pc__body">
        <p className="pc__brand">
          {brand && <BrandMark name={brand} />}
          <span>{line.category?.name}</span>
        </p>
        <h3 className="pc__name">
          <Link to={`/productos/${line.slug}`}>{line.name}</Link>
        </h3>
        {chips.length > 0 && (
          <ul className="pc__chips">
            {chips.map((s) => <li key={s}>{s}</li>)}
          </ul>
        )}
        <div className="pc__foot">
          <p className="pc__price">
            <strong>{priceText(line)}</strong>
            <small>{priceNote(line)}</small>
          </p>
          {quick ? (
            <button
              type="button"
              className="pc__add"
              aria-label={`Agregar ${line.name} al carrito`}
              onClick={(e) => add(cartItem(line, quick), 1, { rect: e.currentTarget.getBoundingClientRect(), image })}
            >
              <Plus size={18} aria-hidden="true" /> Agregar
            </button>
          ) : (
            <Link to={`/productos/${line.slug}`} className="pc__more">Ver ficha</Link>
          )}
        </div>
      </div>
    </article>
  )
}
