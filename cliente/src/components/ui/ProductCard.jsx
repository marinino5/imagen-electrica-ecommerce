import { FileText, Eye, ShoppingCart } from 'lucide-react'
import { useCart } from '../../context/CartContext'
import { useCategories } from '../../hooks/useCategories'
import { availability, isDirectPurchase, productImage } from '../../lib/catalog'
import { formatCOP, formatSpec } from '../../lib/format'
import '../../styles/product-card.css'

export function ProductPrice({ product }) {
  if (product.price === null) return <span className="price price--quote">Precio por cotización</span>
  return (
    <span className="price">
      <strong>{formatCOP(product.price)}</strong>
      <small>IVA incluido · por {product.unit}</small>
    </span>
  )
}

export function AddButton({ product, quantity = 1, block = false, size }) {
  const { add } = useCart()
  const { data: categories } = useCategories()
  const direct = isDirectPurchase(product, quantity)
  const Icon = direct ? ShoppingCart : FileText
  return (
    <button
      type="button"
      className={`btn ${direct ? 'btn--accent' : 'btn--primary'} ${block ? 'btn--block' : ''} ${size ? `btn--${size}` : ''}`}
      onClick={(e) => add(product, quantity, { rect: e.currentTarget.getBoundingClientRect(), image: productImage(product, categories) })}
    >
      <Icon size={18} aria-hidden="true" />
      {direct ? 'Agregar al carrito' : 'Solicitar cotización'}
    </button>
  )
}

const pad = (n) => String(n).padStart(2, '0')

// index/total (opcionales) muestran la etiqueta "01 / 08" del catálogo
export default function ProductCard({ product, onQuickView, index, total }) {
  const { data: categories } = useCategories()
  const stock = availability(product)
  const image = productImage(product, categories)
  const specs = Object.entries(product.specs ?? {}).slice(0, 3)

  return (
    <article className="pcard card">
      <div className={`pcard__media ${product.image_url ? 'pcard__media--photo' : ''}`}>
        {/* Fondo del catálogo: círculo lavanda, arco naranja arriba a la izquierda y curva violeta abajo a la derecha */}
        <span className="pcard__circle" aria-hidden="true" />
        <span className="pcard__arc" aria-hidden="true" />
        <span className="pcard__curve" aria-hidden="true" />
        {image ? (
          <img src={image} alt="" width="480" height="360" loading="lazy" />
        ) : (
          <div className="pcard__placeholder" aria-hidden="true">Sin imagen</div>
        )}
        <span className={`badge badge--${stock.tone}`}>{stock.label}</span>
        {onQuickView && (
          <button type="button" className="pcard__quick" onClick={() => onQuickView(product)} aria-label={`Vista rápida de ${product.name}`}>
            <Eye size={18} aria-hidden="true" />
            <span>Vista rápida</span>
          </button>
        )}
      </div>

      <div className="pcard__body">
        {index !== undefined && <p className="pcard__idx" aria-hidden="true">{pad(index + 1)} / {pad(total)}</p>}
        <p className="pcard__meta">
          {product.brand?.name && <span className="pcard__brand">{product.brand.name}</span>}
          <span>Ref. {product.sku}</span>
        </p>
        <h3 className="pcard__name">{product.name}</h3>
        {specs.length > 0 && (
          <ul className="pcard__specs">
            {specs.map(([key, value]) => {
              const spec = formatSpec(key, value)
              return (
                <li key={key}>
                  <span>{spec.label}</span> {spec.value}
                </li>
              )
            })}
          </ul>
        )}
        <div className="pcard__foot">
          <ProductPrice product={product} />
          <AddButton product={product} block />
        </div>
      </div>
    </article>
  )
}
