import { useEffect, useRef, useState } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { X } from 'lucide-react'
import { useCategories } from '../../hooks/useCategories'
import { availability, isDirectPurchase, productImage } from '../../lib/catalog'
import { formatSpec } from '../../lib/format'
import { AddButton, ProductPrice } from './ProductCard'
import '../../styles/quickview.css'

function QuickViewDialog({ product, onClose }) {
  const { data: categories } = useCategories()
  const [quantity, setQuantity] = useState(1)
  const closeRef = useRef(null)
  const stock = availability(product)
  const image = productImage(product, categories)
  const specs = Object.entries(product.specs ?? {})
  const max = product.sale_mode === 'direct' && product.stock > 0 ? product.stock : undefined

  useEffect(() => {
    const previous = document.activeElement
    closeRef.current?.focus()
    const onKey = (e) => e.key === 'Escape' && onClose()
    document.addEventListener('keydown', onKey)
    document.body.style.overflow = 'hidden'
    return () => {
      document.removeEventListener('keydown', onKey)
      document.body.style.overflow = ''
      previous?.focus?.()
    }
  }, [onClose])

  const qty = Number(quantity) > 0 ? Number(quantity) : 1

  return (
    <motion.div className="qv-backdrop" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} onClick={onClose}>
      <motion.div
        className="qv"
        role="dialog"
        aria-modal="true"
        aria-label={`Vista rápida: ${product.name}`}
        initial={{ opacity: 0, y: 30, scale: 0.97 }}
        animate={{ opacity: 1, y: 0, scale: 1 }}
        exit={{ opacity: 0, y: 20 }}
        transition={{ duration: 0.25 }}
        onClick={(e) => e.stopPropagation()}
      >
        <button ref={closeRef} type="button" className="qv__close icon-btn" onClick={onClose} aria-label="Cerrar vista rápida">
          <X size={22} />
        </button>

        <div className="qv__media">
          {image && <img src={image} alt="" />}
        </div>

        <div className="qv__body">
          <p className="qv__meta">
            {product.brand?.name} · Ref. {product.sku}
          </p>
          <h2>{product.name}</h2>
          <span className={`badge badge--${stock.tone}`}>{stock.label}</span>
          {product.short_description && <p className="qv__desc">{product.short_description}</p>}

          {specs.length > 0 && (
            <dl className="qv__specs">
              {specs.map(([key, value]) => {
                const spec = formatSpec(key, value)
                return (
                  <div key={key}>
                    <dt>{spec.label}</dt>
                    <dd>{spec.value}</dd>
                  </div>
                )
              })}
            </dl>
          )}

          <ProductPrice product={product} />

          <div className="qv__buy">
            <div className="field qv__qty">
              <label htmlFor="qv-qty">Cantidad ({product.unit_label?.toLowerCase()})</label>
              <input
                id="qv-qty"
                className="input"
                type="number"
                min="1"
                max={max}
                step="1"
                value={quantity}
                onChange={(e) => setQuantity(e.target.value)}
              />
            </div>
            <AddButton product={product} quantity={qty} />
          </div>
          {product.sale_mode === 'direct' && product.stock > 0 && !isDirectPurchase(product, qty) && (
            <p className="field-hint">Pides más de lo disponible ({product.stock}): se enviará como cotización.</p>
          )}
        </div>
      </motion.div>
    </motion.div>
  )
}

export default function QuickView({ product, onClose }) {
  return <AnimatePresence>{product && <QuickViewDialog key={product.id} product={product} onClose={onClose} />}</AnimatePresence>
}
