import { useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { CheckCircle2, FileText, Minus, Plus, ShoppingCart, Trash2 } from 'lucide-react'
import { ordersApi } from '../api'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { useCategories } from '../hooks/useCategories'
import { productImage } from '../lib/catalog'
import { formatCOP } from '../lib/format'
import Field from '../components/ui/Field'
import '../styles/pages.css'

const deliveryOptions = [
  { value: 'pickup', label: 'Recoger en tienda' },
  { value: 'local', label: 'Envío local (Bucaramanga y área metropolitana)' },
  { value: 'national', label: 'Envío nacional' },
]

function Line({ line, showPrice }) {
  const { setQuantity, remove } = useCart()
  const { data: categories } = useCategories()
  const { product, quantity } = line
  const image = productImage(product, categories)
  const overStock = product.sale_mode === 'direct' && product.price !== null && quantity > product.stock

  return (
    <li className="line">
      {image ? <img src={image} alt="" width="72" height="72" loading="lazy" /> : <div className="line__ph" />}
      <div className="line__info">
        <strong>{product.name}</strong>
        <span>Ref. {product.sku}{product.brand ? ` · ${product.brand}` : ''}</span>
        {showPrice && <span>{formatCOP(product.price)} por {product.unit}</span>}
        {overStock && <span className="field-error">Pides más de lo disponible ({product.stock}): va como cotización.</span>}
      </div>
      <div className="line__qty" role="group" aria-label={`Cantidad de ${product.name}`}>
        <button type="button" className="icon-btn" onClick={() => setQuantity(product.id, quantity - 1)} aria-label="Restar uno" disabled={quantity <= 1}>
          <Minus size={16} />
        </button>
        <input
          className="input"
          type="number"
          min="1"
          step="1"
          value={quantity}
          onChange={(e) => setQuantity(product.id, e.target.value)}
          aria-label="Cantidad"
        />
        <button type="button" className="icon-btn" onClick={() => setQuantity(product.id, quantity + 1)} aria-label="Sumar uno">
          <Plus size={16} />
        </button>
      </div>
      {showPrice && <strong className="line__total">{formatCOP(product.price * quantity)}</strong>}
      <button type="button" className="icon-btn line__remove" onClick={() => remove(product.id)} aria-label={`Quitar ${product.name}`}>
        <Trash2 size={18} />
      </button>
    </li>
  )
}

function OrderResult({ orders }) {
  return (
    <section className="page-narrow">
      <CheckCircle2 size={44} className="ok-icon" aria-hidden="true" />
      <h1 className="page-title">¡Solicitud enviada!</h1>
      <p className="page-lead">
        {orders.length > 1
          ? 'Tu carrito tenía productos de compra directa y de cotización, así que creamos dos solicitudes.'
          : 'Recibimos tu solicitud. Te contactaremos pronto.'}
      </p>
      <ul className="results">
        {orders.map((o) => (
          <li key={o.code} className="card">
            <div>
              <strong>{o.code}</strong> · {o.type_label}
              <span className="badge">{o.status_label}</span>
            </div>
            <span>{o.total === null ? 'Total por valorar' : `Total ${formatCOP(o.total)}`}</span>
          </li>
        ))}
      </ul>
      <div className="row-actions">
        <Link to="/mi-cuenta" className="btn btn--primary">Ver mis pedidos</Link>
        <Link to="/productos" className="btn btn--ghost">Seguir comprando</Link>
      </div>
    </section>
  )
}

export default function Cart() {
  const { lines, purchaseLines, quoteLines, purchaseTotal, clear } = useCart()
  const { user, isAuthenticated } = useAuth()
  const location = useLocation()
  const [delivery, setDelivery] = useState('pickup')
  const [address, setAddress] = useState('')
  const [notes, setNotes] = useState('')
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)
  const [created, setCreated] = useState(null)

  if (created) return <OrderResult orders={created} />

  if (lines.length === 0) {
    return (
      <section className="page-narrow">
        <ShoppingCart size={44} className="ok-icon" aria-hidden="true" />
        <h1 className="page-title">Tu carrito está vacío</h1>
        <p className="page-lead">Agrega productos para comprarlos en línea o solicitar una cotización.</p>
        <Link to="/productos" className="btn btn--primary">Explorar productos</Link>
      </section>
    )
  }

  const savedAddress = user?.customer?.address
  const needsAddress = delivery !== 'pickup' && !savedAddress

  const submit = async (e) => {
    e.preventDefault()
    setBusy(true)
    setError(null)
    try {
      const res = await ordersApi.create({
        items: lines.map((l) => ({ product_id: l.product.id, quantity: l.quantity })),
        delivery_method: delivery,
        address: delivery !== 'pickup' && address.trim() ? address.trim() : undefined,
        notes: notes.trim() || undefined,
      })
      clear()
      setCreated(res.data)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  // 422: además del mensaje general, se listan los detalles que mande el backend
  const details = error?.kind === 'validation' ? [...new Set(Object.values(error.errors).flat())] : []

  return (
    <div className="container cart">
      <h1 className="page-title">Tu <em>carrito</em></h1>

      <div className="cart__layout">
        <div className="cart__lists">
          <section aria-labelledby="h-compra">
            <h2 id="h-compra" className="block-title"><ShoppingCart size={20} aria-hidden="true" /> Compra directa</h2>
            {purchaseLines.length === 0 ? (
              <p className="muted-box">No tienes productos de compra en línea.</p>
            ) : (
              <>
                <ul className="lines">{purchaseLines.map((l) => <Line key={l.product.id} line={l} showPrice />)}</ul>
                <p className="subtotal">Subtotal (IVA incluido): <strong>{formatCOP(purchaseTotal)}</strong></p>
              </>
            )}
          </section>

          <section id="cotizacion" aria-labelledby="h-cot">
            <h2 id="h-cot" className="block-title"><FileText size={20} aria-hidden="true" /> Para cotizar</h2>
            {quoteLines.length === 0 ? (
              <p className="muted-box">No tienes productos para cotizar.</p>
            ) : (
              <>
                <ul className="lines">{quoteLines.map((l) => <Line key={l.product.id} line={l} />)}</ul>
                <p className="field-hint">Un asesor pondrá el precio y te enviará la cotización.</p>
              </>
            )}
          </section>
        </div>

        <form className="cart__side card" onSubmit={submit} noValidate>
          <h2 className="block-title">Enviar solicitud</h2>

          {error && (
            <div className="alert alert--error" role="alert">
              <p>{error.message}</p>
              {details.length > 1 && <ul>{details.map((d) => <li key={d}>{d}</li>)}</ul>}
            </div>
          )}

          <fieldset className="radios">
            <legend>¿Cómo lo recibes?</legend>
            {deliveryOptions.map((o) => (
              <label key={o.value} className="check">
                <input type="radio" name="delivery" value={o.value} checked={delivery === o.value} onChange={() => setDelivery(o.value)} />
                <span>{o.label}</span>
              </label>
            ))}
          </fieldset>

          {delivery !== 'pickup' && (
            <Field
              label={savedAddress ? 'Dirección de entrega (opcional)' : 'Dirección de entrega'}
              value={address}
              onChange={(e) => setAddress(e.target.value)}
              error={error?.fieldError?.('address')}
              hint={savedAddress ? `Si lo dejas vacío usamos: ${savedAddress}` : undefined}
              required={needsAddress}
            />
          )}

          <Field as="textarea" label="Notas (opcional)" value={notes} onChange={(e) => setNotes(e.target.value)} maxLength={1000} />

          <p className="field-hint">
            El envío se calcula al confirmar la solicitud. Los pedidos y las cotizaciones se crean por separado.
          </p>

          {isAuthenticated ? (
            <button type="submit" className="btn btn--accent btn--lg btn--block" disabled={busy}>
              {busy ? 'Enviando…' : 'Enviar solicitud'}
            </button>
          ) : (
            <div className="login-gate">
              <p>Para enviar tu solicitud necesitas una cuenta empresarial.</p>
              <Link to="/ingresar" state={{ from: location.pathname }} className="btn btn--primary btn--block">Ingresar</Link>
              <Link to="/crear-cuenta" state={{ from: location.pathname }} className="btn btn--ghost btn--block">Crear cuenta</Link>
            </div>
          )}
        </form>
      </div>
    </div>
  )
}
