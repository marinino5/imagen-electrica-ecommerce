import { Link } from 'react-router-dom'
import { ArrowRight, ShoppingCart } from 'lucide-react'
import { useCart } from '../../context/CartContext'
import { formatCOP } from '../../lib/format'

// Mini carrito al final de la columna: la persona no pierde de vista lo que agregó.
export default function MiniCart() {
  const { lines, purchaseLines, quoteLines, purchaseTotal } = useCart()
  const units = lines.reduce((n, l) => n + l.quantity, 0)

  return (
    <aside className="cat-mini" aria-label="Resumen del carrito">
      <div className="cat-mini__top">
        <span className="cat-mini__ic" aria-hidden="true"><ShoppingCart size={18} /></span>
        <div>
          <strong>{lines.length === 0 ? 'Tu carrito está vacío' : `${units} ${units === 1 ? 'unidad' : 'unidades'}`}</strong>
          <small>
            {lines.length === 0 ? 'Agrega referencias desde cada ficha' : `${purchaseLines.length} para comprar · ${quoteLines.length} para cotizar`}
          </small>
        </div>
      </div>
      {lines.length > 0 && (
        <p className="cat-mini__total">
          {purchaseLines.length > 0 ? formatCOP(purchaseTotal) : 'Por cotizar'}
          {purchaseLines.length > 0 && <small>IVA incluido</small>}
        </p>
      )}
      <Link to="/carrito" className="pill pill--o pill--sm pill--block">
        <span className="pill__label">Ver carrito</span>
        <span className="pill__ic" aria-hidden="true"><ArrowRight size={16} /></span>
      </Link>
    </aside>
  )
}
