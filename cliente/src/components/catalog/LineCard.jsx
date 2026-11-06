import { Link } from 'react-router-dom'
import { ArrowUpRight, Plus } from 'lucide-react'
import { useCart } from '../../context/CartContext'
import { cartItem, hpText, lineImage, modeTag, priceNote, priceText } from '../../lib/lines'
import '../../styles/catalog.css'

// Tarjeta por LÍNEA (la ficha), no por SKU. Al pasar el mouse muestra las potencias disponibles.
export default function LineCard({ line, featured = false, className = '' }) {
  const { add } = useCart()
  const image = lineImage(line)
  const hover = line.hp_values.slice(0, 6).map(hpText)
  const quick = line.quick_variant
  const tagClass = line.sale_mode === 'direct' ? 'is-on' : 'is-q'

  return (
    <article className={`bz cat-card ${featured ? 'cat-card--feature' : ''} ${className}`}>
      <div className="core">
        <div className="cat-card__media">
          {image && <img src={image} alt="" width="900" height="675" loading="lazy" />}
          <span className={`cat-card__tag ${tagClass}`}>{modeTag(line)}</span>
          {hover.length > 0 && (
            <div className="cat-card__vars" aria-hidden="true">
              {hover.map((h) => <span key={h}>{h}</span>)}
            </div>
          )}
        </div>

        <div className="cat-card__body">
          <p className="cat-card__brand">{[line.brand_label, line.category?.name].filter(Boolean).join(' · ')}</p>
          <h3 className="cat-card__name">
            <Link to={`/productos/${line.slug}`} className="cat-card__link">{line.name}</Link>
          </h3>
          <ul className="cat-card__chips">
            {line.spec_chips.map((s) => <li key={s}>{s}</li>)}
            {line.refs_count > 0 && <li>{line.refs_count} {line.refs_count === 1 ? 'ref.' : 'refs.'}</li>}
          </ul>
          <div className="cat-card__foot">
            <p className="cat-card__price">
              <strong>{priceText(line)}</strong>
              <small>{priceNote(line)}</small>
            </p>
            {quick && (
              <button
                type="button"
                className="cat-card__add"
                aria-label={`Agregar ${line.name} al carrito`}
                onClick={(e) => add(cartItem(line, quick), 1, { rect: e.currentTarget.getBoundingClientRect(), image })}
              >
                <Plus size={18} aria-hidden="true" /> <span>Agregar</span>
              </button>
            )}
            <span className="cat-card__arrow" aria-hidden="true"><ArrowUpRight size={20} /></span>
          </div>
        </div>
      </div>
    </article>
  )
}
