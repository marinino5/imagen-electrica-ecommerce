import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { CheckCircle2, FileText, Minus, Plus, ShieldCheck, ShoppingCart, Trash2, Truck, Headset, Lock } from 'lucide-react'
import { guestOrdersApi, quotesApi } from '../api'
import { site } from '../config/site'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { useLines } from '../hooks/useLines'
import { isDirectPurchase } from '../lib/catalog'
import { lineImage, lineThumb, priceText } from '../lib/lines'
import { formatCOP } from '../lib/format'
import Bezel from '../components/ui/Bezel'
import Cover from '../components/ui/Cover'
import Eyebrow from '../components/ui/Eyebrow'
import Field from '../components/ui/Field'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import LineCard from '../components/catalog/LineCard'
import QuickAdd from '../components/catalog/QuickAdd'
import CheckoutForm from '../components/checkout/CheckoutForm'
import Bgfx from '../components/ui/Bgfx'
import '../styles/cart.css'
import '../styles/rd-pages.css'

const itemHref = (p) => (p.line_slug ? `/productos/${p.line_slug}${p.sku ? `?ref=${encodeURIComponent(p.sku)}` : ''}` : '/productos')

// Los precios ya incluyen IVA: el total de los productos es la suma de precio × cantidad (no se desglosa base e IVA)
const subtotalOf = (lines) => lines.reduce((sum, l) => sum + l.product.price * l.quantity, 0)

// La miniatura sale SIEMPRE de los datos actuales del catálogo (la línea del producto, por su slug), no de la ruta que quedó guardada
// en el carrito al agregarlo: si la foto cambió después, la guardada ya no existe. La guardada solo es el último recurso.
function Item({ line, showPrice }) {
  const { setQuantity, remove } = useCart()
  const { data: catalog } = useLines()
  const { product, quantity } = line
  const current = catalog.find((l) => l.slug === product.line_slug)
  const thumb = (current && (lineThumb(current) || lineImage(current))) || product.image_url
  // Si una ruta falla se recuerda cuál: al llegar el catálogo actual la miniatura cambia y se vuelve a intentar
  const [brokenSrc, setBrokenSrc] = useState(null)
  const over = product.sale_mode === 'direct' && product.price !== null && !isDirectPurchase(product, quantity)

  return (
    <li className="bz cart-item">
      <div className="core">
        <Link to={itemHref(product)} className="cart-item__img" aria-hidden="true" tabIndex={-1}>
          {thumb && thumb !== brokenSrc && <img src={thumb} alt="" width="88" height="88" loading="lazy" onError={() => setBrokenSrc(thumb)} />}
        </Link>
        <div className="cart-item__info">
          <Link to={itemHref(product)} className="cart-item__name">{product.line_name ?? product.name}</Link>
          <span className="cart-item__variant">{product.label ?? product.name}{product.sku ? ` · SKU ${product.sku}` : ' · Bajo pedido'}</span>
          {showPrice && <span className="cart-item__unit">{formatCOP(product.price)} por {product.unit}</span>}
          {over && <span className="cart-item__warn">Esta cantidad se envía como cotización: un asesor te responde con precio y tiempo de entrega.</span>}
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

// Cotización: su propio bloque. Los renglones salen del carrito.
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
        <Eyebrow>Cotización</Eyebrow>
        <h2 className="h2-sys">Pide tu <em>cotización</em></h2>
        <p className="cart-quote__lead">
          {quoteLines.length > 0
            ? 'Un asesor responde con referencias, precios y tiempos de entrega.'
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

// Enlace del correo → { expires, signature } (para pedirle a la API el estado y crear la cuenta)
function linkParams(url) {
  try {
    const q = new URL(url, window.location.origin).searchParams
    return { expires: q.get('expires'), signature: q.get('signature') }
  } catch {
    return null
  }
}

// «Crea tu cuenta con un clic»: los datos de la compra ya están; solo falta la contraseña
function AccountCta({ code, link, email, accountExists }) {
  const { startSession } = useAuth()
  const [password, setPassword] = useState('')
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)
  const [created, setCreated] = useState(false)

  if (created) {
    return <div className="co-cta is-done"><CheckCircle2 size={22} aria-hidden="true" /><p><strong>¡Cuenta creada!</strong> Ya puedes ver tus pedidos en <Link to="/mi-cuenta">Mi cuenta</Link>.</p></div>
  }

  if (accountExists) {
    return (
      <div className="co-cta">
        <p><strong>Ya tienes una cuenta con {email}.</strong> Inicia sesión para ver este pedido en Mi cuenta (no hace falta para recibirlo).</p>
        <Pill as="link" to="/ingresar" tone="l" size="sm">Iniciar sesión</Pill>
      </div>
    )
  }

  const submit = async (e) => {
    e.preventDefault()
    setBusy(true)
    setError(null)
    try {
      await startSession(await guestOrdersApi.createAccount(code, link, { password }))
      setCreated(true)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  return (
    <form className="co-cta" onSubmit={submit} noValidate>
      <p><strong>Crea tu cuenta con un clic.</strong> Ya tenemos tus datos: solo elige una contraseña y tendrás tus pedidos y tus datos de facturación guardados. Es opcional.</p>
      <div className="co-cta__row">
        <Field label="Contraseña (mínimo 8 caracteres)" type="password" value={password} onChange={(e) => setPassword(e.target.value)} autoComplete="new-password" error={error?.fieldError?.('password')} />
        <Pill tone="d" type="submit" disabled={busy || password.length < 8}>{busy ? 'Creando…' : 'Crear mi cuenta'}</Pill>
      </div>
      {error && !error.fieldError?.('password') && <p className="field-error" role="alert">{error.message} {error.status === 422 && <Link to="/ingresar">Inicia sesión</Link>}</p>}
    </form>
  )
}

function OrderResult({ result }) {
  const { orders, meta, email } = result
  const first = orders[0]
  const guest = meta.guest
  const link = first ? linkParams(meta.status_urls?.[first.code]) : null

  return (
    <section className="wrap cart-done">
      <CheckCircle2 size={48} aria-hidden="true" />
      <Eyebrow>Pedido enviado</Eyebrow>
      <h1 className="h-hero"><span>¡Gracias!</span><em>Ya lo tenemos</em></h1>
      <p>Recibimos tu solicitud de compra. Te contactaremos pronto para confirmar el pago y la entrega.{guest ? ` Te enviamos el resumen a ${email}, con un enlace para ver el estado de tu pedido.` : ''}</p>
      <ul className="cart-done__list">
        {orders.map((o) => (
          <li key={o.code} className="bz">
            <div className="core">
              <strong>{o.code}</strong> · {o.type_label}
              <span>{o.total === null ? 'Total por valorar' : `Total ${formatCOP(o.total)}`}</span>
              {guest && meta.status_urls?.[o.code] && <a className="cart-done__status" href={new URL(meta.status_urls[o.code]).pathname + new URL(meta.status_urls[o.code]).search}>Ver el estado</a>}
            </div>
          </li>
        ))}
      </ul>
      {guest && first && link && <AccountCta code={first.code} link={link} email={email} accountExists={meta.account_exists} />}
      <div className="cover__actions">
        {!guest && <Pill as="link" to="/mi-cuenta" tone="d">Ver mis pedidos</Pill>}
        <Pill as="link" to="/productos" tone="l">Seguir comprando</Pill>
      </div>
    </section>
  )
}

// Sugerencia compacta de la columna derecha: foto pequeña, nombre, precio y botón de carrito
function Suggestion({ line }) {
  const image = lineThumb(line) || lineImage(line)
  return (
    <li className="cart-sg">
      <Link to={`/productos/${line.slug}`} className="cart-sg__img" aria-hidden="true" tabIndex={-1}>{image && <img src={image} alt="" width="64" height="64" loading="lazy" />}</Link>
      <div className="cart-sg__info">
        <Link to={`/productos/${line.slug}`} className="cart-sg__name">{line.name}</Link>
        <small>{priceText(line)}</small>
        <QuickAdd line={line} image={image} compact />
      </div>
    </li>
  )
}

export default function Cart() {
  const { lines, purchaseLines, quoteLines, clear, remove } = useCart()
  const { data: catalog } = useLines()
  const [created, setCreated] = useState(null)
  const [shipping, setShipping] = useState(0)

  const subtotal = useMemo(() => subtotalOf(purchaseLines), [purchaseLines])
  // Carrito vacío: 4 fichas destacadas para empezar
  const featured = useMemo(() => catalog.filter((l) => l.is_featured).slice(0, 4), [catalog])
  // Sugerencias: otras líneas de las mismas familias que ya están en el carrito (máximo 3)
  const suggestions = useMemo(() => {
    const inCart = new Set(lines.map((l) => l.product.line_slug))
    const families = new Set(catalog.filter((l) => inCart.has(l.slug)).map((l) => l.family?.slug))
    return catalog.filter((l) => !inCart.has(l.slug) && families.has(l.family?.slug) && l.sale_mode === 'direct').slice(0, 3)
  }, [catalog, lines])

  if (created) return <OrderResult result={created} />

  const onCreated = (result) => {
    purchaseLines.forEach((l) => remove(l.product.id))
    setCreated(result)
  }

  const empty = lines.length === 0
  const steps = [
    ['Revisa', true],
    ['Tus datos', purchaseLines.length > 0],
    ['Confirma', false],
  ]

  return (
    <>
      <Cover
        eyebrow="Tu pedido"
        lines={['Tu', { text: 'carrito', accent: true }]}
        lead={empty ? 'Aún no hay nada aquí. Agrega referencias desde el catálogo para comprarlas en línea o cotizarlas.' : 'Revisa lo que elegiste, elige cómo lo recibes y confirma. Lo que no tiene precio publicado se cotiza.'}
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
              <div className="is-total"><dt>Total a pagar</dt><dd>{purchaseLines.length > 0 ? formatCOP(subtotal + shipping) : '—'}</dd></div>
            </dl>
          </div>
        </Bezel>
      </Cover>
      <div className="pgbody bg-lila-soft">
        <Bgfx d e />

      {empty && (
        <section className="wrap cart-sug" aria-labelledby="h-sug">
          <div className="cart-sug__hero">
            <div className="cart-sug__img" aria-hidden="true"><img src="/img/catalogo/weg-accesorios-variadores.webp" alt="" width="240" height="240" /></div>
            <div>
              <h2 id="h-sug" className="rd-h2">Empieza por <span className="hl is-in">lo más pedido</span></h2>
              <p className="rd-lead">Agrega referencias desde el catálogo: lo que tiene precio publicado se compra en línea y lo demás se cotiza.</p>
            </div>
          </div>
          {featured.length > 0 && <ul className="cart-sug__grid">{featured.map((l) => <li key={l.slug}><LineCard line={l} /></li>)}</ul>}
        </section>
      )}

      {!empty && (
        <div className="wrap cart-layout">
          <div className="cart-main">
            {/* Un solo bloque: productos, entrega, total y datos de compra (sin cuadros aparte) */}
            <Reveal>
              <Bezel className="cart-block" id="compra">
                <div className="cart-block__body">
                  <h2 className="cart-block__title"><ShoppingCart size={20} aria-hidden="true" /> Tu compra</h2>
                  {purchaseLines.length === 0 ? (
                    <p className="cart-empty">No tienes productos de compra en línea. <Link to="/productos?modo=en-linea">Ver lo que se compra en línea</Link></p>
                  ) : (
                    <>
                      <p className="cart-guest"><Lock size={14} aria-hidden="true" /> Puedes comprar sin crear una cuenta.</p>
                      <ul className="cart-list">{purchaseLines.map((l) => <Item key={l.product.id} line={l} showPrice />)}</ul>
                      <div id="pagar">
                        <CheckoutForm purchaseLines={purchaseLines} subtotal={subtotal} onShipping={setShipping} onCreated={onCreated} />
                      </div>
                      <p className="field-hint">El pago en línea llegará más adelante: hoy un asesor confirma el pago y la entrega contigo.</p>
                    </>
                  )}
                  <ul className="cart-trust">
                    <li><Truck size={18} aria-hidden="true" /> Envíos a todo el país</li>
                    <li><Headset size={18} aria-hidden="true" /> Asesoría técnica en cada pedido</li>
                    <li><ShieldCheck size={18} aria-hidden="true" /> Pago seguro: próximamente</li>
                  </ul>
                  <a className="cart-wa" href={site.whatsappUrl} target="_blank" rel="noreferrer">¿Dudas? Escríbenos por WhatsApp</a>
                </div>
              </Bezel>
              {purchaseLines.length > 0 && <button type="button" className="cart-clear" onClick={clear}>Vaciar carrito</button>}
            </Reveal>

            <Reveal><QuoteBlock quoteLines={quoteLines} /></Reveal>
          </div>

          {/* Sugerencias compactas (máx. 3): a la derecha en escritorio y al final en el celular */}
          {suggestions.length > 0 && (
            <aside className="cart-side" aria-labelledby="sug-title">
              <Eyebrow>Completa tu pedido</Eyebrow>
              <h2 id="sug-title" className="h2-sys">Líneas que <em>combinan</em></h2>
              <ul className="cart-sg__list">{suggestions.map((l) => <Suggestion key={l.slug} line={l} />)}</ul>
            </aside>
          )}
        </div>
      )}

      </div>

      {/* Barra fija abajo en celular: lleva al formulario de compra */}
      {!empty && (
        <div className="cart-bar" role="region" aria-label="Resumen del carrito">
          <p>
            <strong>{purchaseLines.length > 0 ? formatCOP(subtotal + shipping) : 'Por cotizar'}</strong>
            <small>{purchaseLines.length > 0 ? 'IVA incluido' : `${quoteLines.length} para cotizar`}</small>
          </p>
          <Pill as="a" href={purchaseLines.length > 0 ? '#pagar' : '#cotizacion'} tone="o" size="sm">{purchaseLines.length > 0 ? 'Comprar' : 'Cotizar'}</Pill>
        </div>
      )}
    </>
  )
}
