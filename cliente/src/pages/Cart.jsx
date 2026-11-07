import { useMemo, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { CheckCircle2, FileText, Minus, Plus, ShieldCheck, ShoppingCart, Trash2, Truck, Headset, Lock } from 'lucide-react'
import { ordersApi, quotesApi } from '../api'
import { site } from '../config/site'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { useLines } from '../hooks/useLines'
import { formatCOP } from '../lib/format'
import Bezel from '../components/ui/Bezel'
import Cover from '../components/ui/Cover'
import Eyebrow from '../components/ui/Eyebrow'
import Field from '../components/ui/Field'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import LineCard from '../components/catalog/LineCard'
import '../styles/cart.css'
import '../styles/rd-pages.css'

const deliveryOptions = [
  { value: 'pickup', label: 'Recoger en tienda', note: 'Sin costo · Cra 18 No 24-32, Bucaramanga' },
  { value: 'local', label: 'Envío local', note: 'Bucaramanga y área metropolitana · lo confirma tu asesor' },
  { value: 'national', label: 'Envío nacional', note: 'A todo el país · lo confirma tu asesor' },
]

const itemHref = (p) => (p.line_slug ? `/productos/${p.line_slug}${p.sku ? `?ref=${encodeURIComponent(p.sku)}` : ''}` : '/productos')

// IVA incluido en el precio: base = total / (1 + tasa)
function breakdown(lines) {
  return lines.reduce(
    (acc, l) => {
      const total = l.product.price * l.quantity
      const base = total / (1 + Number(l.product.tax_rate) / 100)
      return { total: acc.total + total, base: acc.base + base, tax: acc.tax + (total - base) }
    },
    { total: 0, base: 0, tax: 0 },
  )
}

function Item({ line, showPrice }) {
  const { setQuantity, remove } = useCart()
  const { product, quantity } = line
  const over = product.sale_mode === 'direct' && product.price !== null && quantity > product.stock

  return (
    <li className="bz cart-item">
      <div className="core">
        <Link to={itemHref(product)} className="cart-item__img" aria-hidden="true" tabIndex={-1}>
          {product.image_url && <img src={product.image_url} alt="" width="96" height="72" loading="lazy" />}
        </Link>
        <div className="cart-item__info">
          <Link to={itemHref(product)} className="cart-item__name">{product.line_name ?? product.name}</Link>
          <span className="cart-item__variant">{product.label ?? product.name}{product.sku ? ` · SKU ${product.sku}` : ' · Bajo pedido'}</span>
          {showPrice && <span className="cart-item__unit">{formatCOP(product.price)} por {product.unit}</span>}
          {over && <span className="cart-item__warn">Pides más de lo que hay en bodega ({product.stock}): va como cotización.</span>}
        </div>
        <div className="cart-item__qty" role="group" aria-label={`Cantidad de ${product.name}`}>
          <button type="button" onClick={() => setQuantity(product.id, quantity - 1)} disabled={quantity <= 1} aria-label="Restar uno"><Minus size={16} /></button>
          <input type="number" inputMode="numeric" min="1" step="1" value={quantity} onChange={(e) => setQuantity(product.id, e.target.value)} aria-label="Cantidad" />
          <button type="button" onClick={() => setQuantity(product.id, quantity + 1)} aria-label="Sumar uno"><Plus size={16} /></button>
        </div>
        {showPrice && <strong className="cart-item__total">{formatCOP(product.price * quantity)}</strong>}
        <button type="button" className="cart-item__remove" onClick={() => remove(product.id)} aria-label={`Quitar ${product.name}`}><Trash2 size={18} /></button>
      </div>
    </li>
  )
}

const emptyQuote = { name: '', phone: '', email: '', city: '', company: '', nit: '', message: '', privacy: false, website: '' }

// Cotización sin cuenta: su propio bloque. Los renglones salen del carrito.
function QuoteBlock({ quoteLines }) {
  const { remove } = useCart()
  const { user, isAuthenticated } = useAuth()
  const [form, setForm] = useState(() => ({
    ...emptyQuote,
    name: user?.name ?? '',
    email: user?.email ?? '',
    phone: user?.customer?.phone ?? '',
    city: user?.customer?.city ?? '',
    company: user?.customer?.company_name ?? '',
    nit: user?.customer?.nit ?? '',
  }))
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)
  const [sent, setSent] = useState(false)
  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.type === 'checkbox' ? e.target.checked : e.target.value }))

  const submit = async (e) => {
    e.preventDefault()
    setBusy(true)
    setError(null)
    try {
      await quotesApi.create({
        name: form.name.trim(),
        phone: form.phone.trim(),
        email: form.email.trim(),
        city: form.city.trim(),
        company: form.company.trim() || undefined,
        nit: form.nit.trim() || undefined,
        message: form.message.trim() || undefined,
        privacy_accepted: form.privacy,
        website: form.website,
        items: quoteLines.map((l) =>
          String(l.product.id).startsWith('line-')
            ? { line: l.product.line_slug, quantity: l.quantity }
            : { product_id: l.product.id, quantity: l.quantity },
        ),
      })
      quoteLines.forEach((l) => remove(l.product.id))
      setSent(true)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  if (sent) {
    return (
      <Bezel className="cart-quote" id="cotizacion">
        <div className="cart-quote__done">
          <CheckCircle2 size={44} aria-hidden="true" />
          <h2>¡Cotización enviada!</h2>
          <p>Un asesor te responde pronto por WhatsApp o por correo con las referencias y los precios.</p>
          <Pill as="link" to="/productos" tone="d">Seguir viendo el catálogo</Pill>
        </div>
      </Bezel>
    )
  }

  const details = error?.kind === 'validation' ? [...new Set(Object.values(error.errors).flat())] : []

  return (
    <Bezel className="cart-quote" id="cotizacion">
      <div className="cart-quote__body">
        <Eyebrow>Cotización sin cuenta</Eyebrow>
        <h2 className="h2-sys">Pide tu <em>cotización</em></h2>
        <p className="cart-quote__lead">
          {quoteLines.length > 0
            ? 'Un asesor responde con referencias, precios y tiempos de entrega. No necesitas cuenta.'
            : 'Agrega referencias desde cada ficha con «Cotizar volumen» o «Solicitar cotización».'}
        </p>

        {quoteLines.length > 0 && (
          <>
            <ul className="cart-list">{quoteLines.map((l) => <Item key={l.product.id} line={l} showPrice={false} />)}</ul>

            <form className="cart-form" onSubmit={submit} noValidate>
              {error && (
                <div className="alert alert--error" role="alert">
                  <p>{error.message}</p>
                  {details.length > 1 && <ul>{details.map((d) => <li key={d}>{d}</li>)}</ul>}
                </div>
              )}
              <div className="cart-form__grid">
                <Field label="Nombre" value={form.name} onChange={set('name')} error={error?.fieldError?.('name')} autoComplete="name" required />
                <Field label="WhatsApp o teléfono" type="tel" inputMode="tel" value={form.phone} onChange={set('phone')} error={error?.fieldError?.('phone')} autoComplete="tel" required />
                <Field label="Correo" type="email" value={form.email} onChange={set('email')} error={error?.fieldError?.('email')} autoComplete="email" required />
                <Field label="Ciudad" value={form.city} onChange={set('city')} error={error?.fieldError?.('city')} autoComplete="address-level2" required />
                <Field label="Empresa (opcional)" value={form.company} onChange={set('company')} autoComplete="organization" />
                <Field label="NIT (opcional)" inputMode="numeric" value={form.nit} onChange={set('nit')} />
              </div>
              <Field as="textarea" rows={3} label="Mensaje (opcional)" value={form.message} onChange={set('message')} maxLength={2000} hint="Cuéntanos la aplicación o la fecha en que lo necesitas." />
              {/* Campo trampa contra bots: las personas no lo ven */}
              <div className="cart-form__trap" aria-hidden="true">
                <label>No llenes este campo<input tabIndex={-1} autoComplete="off" value={form.website} onChange={set('website')} /></label>
              </div>
              <div className="field">
                <label className="check">
                  <input type="checkbox" checked={form.privacy} onChange={set('privacy')} />
                  <span>Acepto la <Link to="/politica-datos" target="_blank" rel="noopener">política de tratamiento de datos personales</Link>.</span>
                </label>
                {error?.fieldError?.('privacy_accepted') && <span className="field-error">{error.fieldError('privacy_accepted')}</span>}
              </div>
              <Pill tone="d" type="submit" icon={FileText} disabled={busy}>{busy ? 'Enviando…' : 'Enviar cotización'}</Pill>
              {isAuthenticated && <p className="field-hint">Tus datos de cuenta ya están cargados; puedes cambiarlos.</p>}
            </form>
          </>
        )}
      </div>
    </Bezel>
  )
}

function OrderResult({ orders }) {
  return (
    <section className="wrap cart-done">
      <CheckCircle2 size={48} aria-hidden="true" />
      <Eyebrow>Pedido enviado</Eyebrow>
      <h1 className="h-hero"><span>¡Gracias!</span><em>Ya lo tenemos</em></h1>
      <p>Recibimos tu solicitud de compra. Te contactaremos pronto para confirmar el pago y la entrega.</p>
      <ul className="cart-done__list">
        {orders.map((o) => (
          <li key={o.code} className="bz"><div className="core"><strong>{o.code}</strong> · {o.type_label}<span>{o.total === null ? 'Total por valorar' : `Total ${formatCOP(o.total)}`}</span></div></li>
        ))}
      </ul>
      <div className="cover__actions">
        <Pill as="link" to="/mi-cuenta" tone="d">Ver mis pedidos</Pill>
        <Pill as="link" to="/productos" tone="l">Seguir comprando</Pill>
      </div>
    </section>
  )
}

export default function Cart() {
  const { lines, purchaseLines, quoteLines, clear, remove } = useCart()
  const { user, isAuthenticated } = useAuth()
  const { data: catalog } = useLines()
  const location = useLocation()
  const [delivery, setDelivery] = useState('pickup')
  const [address, setAddress] = useState('')
  const [notes, setNotes] = useState('')
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)
  const [created, setCreated] = useState(null)

  const money = useMemo(() => breakdown(purchaseLines), [purchaseLines])
  // Carrito vacío: 4 fichas destacadas para empezar
  const featured = useMemo(() => catalog.filter((l) => l.is_featured).slice(0, 4), [catalog])
  // Sugerencias: otras líneas de las mismas familias que ya están en el carrito
  const suggestions = useMemo(() => {
    const inCart = new Set(lines.map((l) => l.product.line_slug))
    const families = new Set(catalog.filter((l) => inCart.has(l.slug)).map((l) => l.family?.slug))
    return catalog.filter((l) => !inCart.has(l.slug) && families.has(l.family?.slug) && l.sale_mode === 'direct').slice(0, 3)
  }, [catalog, lines])

  if (created) return <OrderResult orders={created} />

  const savedAddress = user?.customer?.address
  const needsAddress = delivery !== 'pickup' && !savedAddress

  const submit = async (e) => {
    e.preventDefault()
    setBusy(true)
    setError(null)
    try {
      const res = await ordersApi.create({
        items: purchaseLines.map((l) => ({ product_id: l.product.id, quantity: l.quantity })),
        delivery_method: delivery,
        address: delivery !== 'pickup' && address.trim() ? address.trim() : undefined,
        notes: notes.trim() || undefined,
      })
      purchaseLines.forEach((l) => remove(l.product.id))
      setCreated(res.data)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }
  const details = error?.kind === 'validation' ? [...new Set(Object.values(error.errors).flat())] : []
  const empty = lines.length === 0
  const steps = [
    ['Revisa', true],
    ['Entrega', purchaseLines.length > 0],
    ['Confirma', purchaseLines.length > 0 && isAuthenticated],
  ]

  return (
    <>
      <Cover
        eyebrow="Tu pedido"
        lines={['Tu', { text: 'carrito', accent: true }]}
        lead={empty ? 'Aún no hay nada aquí. Agrega referencias desde el catálogo para comprarlas en línea o cotizarlas.' : 'Revisa lo que elegiste, escoge cómo lo recibes y envíalo. Lo que no tiene precio publicado se cotiza sin cuenta.'}
        actions={empty ? <Pill as="link" to="/productos" tone="o">Explorar el catálogo</Pill> : null}
      >
        <Bezel className="cart-sum cart-sum--cover">
          <div className="cart-sum__body">
            <ol className="cart-steps" aria-label="Pasos">
              {steps.map(([label, done], i) => (
                <li key={label} className={done ? 'is-done' : ''}><span>{i + 1}</span>{label}</li>
              ))}
            </ol>
            <dl className="cart-sum__rows">
              <div><dt>Para comprar</dt><dd>{purchaseLines.length} {purchaseLines.length === 1 ? 'producto' : 'productos'}</dd></div>
              <div><dt>Para cotizar</dt><dd>{quoteLines.length} {quoteLines.length === 1 ? 'producto' : 'productos'}</dd></div>
              <div className="is-total"><dt>Total a pagar</dt><dd>{purchaseLines.length > 0 ? formatCOP(money.total) : '—'}</dd></div>
            </dl>
          </div>
        </Bezel>
      </Cover>

      {empty && (
        <section className="wrap cart-sug" aria-labelledby="h-sug">
          <div className="cart-sug__hero">
            <div className="cart-sug__img" aria-hidden="true"><img src="/img/catalogo/weg-accesorios-variadores.webp" alt="" width="240" height="240" /></div>
            <div>
              <h2 id="h-sug" className="rd-h2">Empieza por <span className="hl is-in">lo más pedido</span></h2>
              <p className="rd-lead">Agrega referencias desde el catálogo: lo que está en bodega se compra en línea y lo demás se cotiza sin cuenta.</p>
            </div>
          </div>
          {featured.length > 0 && <ul className="cart-sug__grid">{featured.map((l) => <li key={l.slug}><LineCard line={l} /></li>)}</ul>}
        </section>
      )}

      {!empty && (
        <div className="wrap cart-layout">
          <div className="cart-main">
            <Reveal>
              <Bezel className="cart-block" id="compra">
                <div className="cart-block__body">
                  <h2 className="cart-block__title"><ShoppingCart size={20} aria-hidden="true" /> Compra en línea</h2>
                  {purchaseLines.length === 0 ? (
                    <p className="cart-empty">No tienes productos de compra en línea. <Link to="/productos?modo=en-linea">Ver lo que se compra en línea</Link></p>
                  ) : (
                    <ul className="cart-list">{purchaseLines.map((l) => <Item key={l.product.id} line={l} showPrice />)}</ul>
                  )}
                </div>
              </Bezel>
            </Reveal>

            {suggestions.length > 0 && (
              <section className="cart-suggest" aria-labelledby="sug-title">
                <Eyebrow>Completa tu pedido</Eyebrow>
                <h2 id="sug-title" className="h2-sys">Líneas que <em>combinan</em></h2>
                <div className="cart-suggest__grid">
                  {suggestions.map((l) => <LineCard key={l.slug} line={l} />)}
                </div>
              </section>
            )}

            <Reveal><QuoteBlock quoteLines={quoteLines} /></Reveal>
          </div>

          <aside className="cart-side" aria-label="Resumen y entrega">
            <Bezel className="cart-sum">
              <form className="cart-sum__body" onSubmit={submit} noValidate id="cart-form">
                <h2 className="cart-block__title">Resumen de compra</h2>
                {purchaseLines.length === 0 ? (
                  <p className="cart-empty">Cuando agregues productos de compra en línea verás aquí el total.</p>
                ) : (
                  <>
                    <dl className="cart-sum__rows">
                      <div><dt>Base</dt><dd>{formatCOP(money.base)}</dd></div>
                      <div><dt>IVA incluido</dt><dd>{formatCOP(money.tax)}</dd></div>
                      <div><dt>Envío</dt><dd>{delivery === 'pickup' ? 'Gratis' : 'Lo confirma tu asesor'}</dd></div>
                      <div className="is-total"><dt>Total</dt><dd>{formatCOP(money.total)}</dd></div>
                    </dl>

                    {error && (
                      <div className="alert alert--error" role="alert">
                        <p>{error.message}</p>
                        {details.length > 1 && <ul>{details.map((d) => <li key={d}>{d}</li>)}</ul>}
                      </div>
                    )}

                    <fieldset className="cart-delivery">
                      <legend>¿Cómo lo recibes?</legend>
                      {deliveryOptions.map((o) => (
                        <label key={o.value} className={delivery === o.value ? 'is-on' : ''}>
                          <input type="radio" name="delivery" value={o.value} checked={delivery === o.value} onChange={() => setDelivery(o.value)} />
                          <span><strong>{o.label}</strong><small>{o.note}</small></span>
                        </label>
                      ))}
                    </fieldset>

                    {delivery !== 'pickup' && (
                      <Field
                        label={savedAddress ? 'Dirección de entrega (opcional)' : 'Dirección de entrega'}
                        value={address}
                        onChange={(e) => setAddress(e.target.value)}
                        autoComplete="street-address"
                        error={error?.fieldError?.('address')}
                        hint={savedAddress ? `Si lo dejas vacío usamos: ${savedAddress}` : undefined}
                        required={needsAddress}
                      />
                    )}
                    <Field as="textarea" rows={2} label="Notas (opcional)" value={notes} onChange={(e) => setNotes(e.target.value)} maxLength={1000} />

                    {isAuthenticated ? (
                      <Pill tone="o" type="submit" icon={Lock} disabled={busy} className="pill--block">{busy ? 'Enviando…' : 'Enviar pedido'}</Pill>
                    ) : (
                      <div className="cart-gate">
                        <p><Lock size={16} aria-hidden="true" /> Para comprar en línea necesitas una cuenta.</p>
                        <Pill as="link" to="/ingresar" state={{ from: location.pathname }} tone="o" className="pill--block">Ingresar</Pill>
                        <Pill as="link" to="/crear-cuenta" state={{ from: location.pathname }} tone="l" className="pill--block">Crear cuenta</Pill>
                      </div>
                    )}
                    <p className="field-hint">El pago en línea llegará más adelante: hoy un asesor confirma el pago y la entrega contigo.</p>
                  </>
                )}

                <ul className="cart-trust">
                  <li><Truck size={18} aria-hidden="true" /> Envíos a todo el país</li>
                  <li><Headset size={18} aria-hidden="true" /> Asesoría técnica en cada pedido</li>
                  <li><ShieldCheck size={18} aria-hidden="true" /> Pago seguro: próximamente</li>
                </ul>
                <a className="cart-wa" href={site.whatsappUrl} target="_blank" rel="noreferrer">¿Dudas? Escríbenos por WhatsApp</a>
              </form>
            </Bezel>
            {purchaseLines.length > 0 && <button type="button" className="cart-clear" onClick={clear}>Vaciar carrito</button>}
          </aside>
        </div>
      )}

      {/* Resumen fijo abajo en celular */}
      {!empty && (
        <div className="cart-bar" role="region" aria-label="Resumen del carrito">
          <p>
            <strong>{purchaseLines.length > 0 ? formatCOP(money.total) : 'Por cotizar'}</strong>
            <small>{purchaseLines.length > 0 ? 'IVA incluido' : `${quoteLines.length} para cotizar`}</small>
          </p>
          {purchaseLines.length > 0 && isAuthenticated ? (
            <Pill tone="o" type="submit" form="cart-form" icon={Lock} size="sm" disabled={busy}>Enviar pedido</Pill>
          ) : purchaseLines.length > 0 ? (
            <Pill as="link" to="/ingresar" state={{ from: location.pathname }} tone="o" size="sm">Ingresar</Pill>
          ) : (
            <Pill as="a" href="#cotizacion" tone="o" size="sm">Cotizar</Pill>
          )}
        </div>
      )}
    </>
  )
}
