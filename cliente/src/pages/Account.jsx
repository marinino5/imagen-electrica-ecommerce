import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Check, ChevronDown, ClipboardList, FileText, LogOut, MapPin, Pencil, RotateCcw, Truck } from 'lucide-react'
import { catalogApi, ordersApi } from '../api'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { useToast } from '../context/ToastContext'
import { useApi } from '../hooks/useApi'
import { formatCOP, formatDate, formatQty } from '../lib/format'
import { cartItem } from '../lib/lines'
import Field from '../components/ui/Field'
import ErrorState from '../components/ui/ErrorState'
import EmptyState from '../components/ui/EmptyState'
import Bezel from '../components/ui/Bezel'
import Cover from '../components/ui/Cover'
import Pill from '../components/ui/Pill'
import Bgfx from '../components/ui/Bgfx'
import '../styles/pages.css'
import '../styles/account.css'

const tabs = [
  { value: '', label: 'Todos' },
  { value: 'purchase', label: 'Pedidos' },
  { value: 'quote', label: 'Cotizaciones' },
]

// Líneas de estados (igual que las reglas de negocio del backend)
const FLOW = {
  purchase: [
    { status: 'pending_payment', label: 'Pago pendiente' },
    { status: 'paid', label: 'Pagado' },
    { status: 'shipped', label: 'Enviado' },
    { status: 'delivered', label: 'Entregado' },
  ],
  quote: [
    { status: 'new', label: 'Recibida' },
    { status: 'in_review', label: 'En revisión' },
    { status: 'sent', label: 'Enviada' },
    { status: 'accepted', label: 'Aceptada' },
  ],
}

function Timeline({ order }) {
  if (order.status === 'cancelled' || order.status === 'rejected') {
    return <p className="timeline__end">{order.status_label}: la solicitud ya no avanza.</p>
  }
  const steps = FLOW[order.type] ?? []
  const current = steps.findIndex((s) => s.status === order.status)
  return (
    <ol className="timeline" aria-label={`Estado: ${order.status_label}`}>
      {steps.map((s, i) => (
        <li key={s.status} className={i < current ? 'is-done' : i === current ? 'is-current' : ''} aria-current={i === current ? 'step' : undefined}>
          <span className="timeline__dot">{i < current ? <Check size={12} aria-hidden="true" /> : i + 1}</span>
          <span className="timeline__label">{s.label}</span>
        </li>
      ))}
    </ol>
  )
}

function Reorder({ order }) {
  const { add } = useCart()
  const { notify } = useToast()
  const [busy, setBusy] = useState(false)
  const items = (order.items ?? []).filter((i) => i.product_id)

  const run = async () => {
    setBusy(true)
    let added = 0
    // El pedido guarda copia del producto: se busca la referencia vigente por su SKU para tomar precio, stock y línea actuales
    for (const item of items) {
      try {
        const found = await catalogApi.lines({ q: item.sku })
        const lineSummary = found.data[0]
        if (!lineSummary) continue
        const detail = (await catalogApi.line(lineSummary.slug)).data
        const variant = detail.variants.find((v) => v.sku === item.sku)
        if (variant) {
          add(cartItem(detail, variant), item.quantity)
          added += 1
        }
      } catch {
        /* sin conexión o producto retirado: se omite y se avisa abajo */
      }
    }
    setBusy(false)
    if (added < items.length) notify(`Se agregaron ${added} de ${items.length} productos; el resto ya no se puede agregar o no se pudo consultar.`)
  }

  if (items.length === 0) return null
  return (
    <Pill tone="o" size="sm" icon={RotateCcw} onClick={run} disabled={busy}>{busy ? 'Agregando…' : 'Volver a pedir'}</Pill>
  )
}

function OrderRow({ order }) {
  const [open, setOpen] = useState(false)
  const bodyId = `pedido-${order.code}`
  return (
    <li className="bz acc-order">
      <div className="core">
        <div className="acc-order__head">
          <div>
            <strong className="acc-order__code">{order.code}</strong>
            <small>{order.type_label} · {formatDate(order.created_at)}</small>
          </div>
          <span className="badge">{order.status_label}</span>
          <span className="acc-order__total">{order.total === null ? 'Por valorar' : formatCOP(order.total)}</span>
        </div>
        <Timeline order={order} />
        <div className="acc-order__actions">
          <Reorder order={order} />
          <button type="button" className="acc-order__more" aria-expanded={open} aria-controls={bodyId} onClick={() => setOpen((o) => !o)}>
            {open ? 'Ocultar detalle' : 'Ver detalle'} <ChevronDown size={16} aria-hidden="true" className={open ? 'is-open' : ''} />
          </button>
        </div>

        {open && (
          <div className="acc-order__body" id={bodyId}>
            <table aria-label={`Productos de ${order.code}`}>
              <thead>
                <tr><th>Producto</th><th>Cant.</th><th>Valor</th></tr>
              </thead>
              <tbody>
                {order.items?.map((item) => (
                  <tr key={item.id}>
                    <td>{item.product_name}<small>Ref. {item.sku}</small></td>
                    <td>{formatQty(item.quantity)} {item.unit}</td>
                    <td>{formatCOP(item.line_total)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            <dl className="acc-order__totals">
              <div><dt>Entrega</dt><dd>{order.delivery_method_label}</dd></div>
              {order.shipping !== null && <div><dt>Envío</dt><dd>{formatCOP(order.shipping)}</dd></div>}
              {order.total !== null && <div><dt>Total (IVA incluido)</dt><dd>{formatCOP(order.total)}</dd></div>}
            </dl>
          </div>
        )}
      </div>
    </li>
  )
}

// Datos editables: los mismos que acepta PUT /me (no cambian el NIT, el tipo ni el correo)
function ProfileCard({ user, customer }) {
  const { updateProfile } = useAuth()
  const [editing, setEditing] = useState(false)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)
  const [saved, setSaved] = useState(false)
  const start = () => ({ name: user.name, company_name: customer.company_name ?? '', phone: customer.phone ?? '', city: customer.city ?? '', address: customer.address ?? '' })
  const [form, setForm] = useState(start)
  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }))
  const company = customer.type === 'company'

  const submit = async (e) => {
    e.preventDefault()
    setBusy(true)
    setError(null)
    try {
      await updateProfile({ ...form, company_name: company ? form.company_name : undefined, address: form.address || null })
      setEditing(false)
      setSaved(true)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  if (!editing) {
    return (
      <Bezel as="section" className="acc-card" aria-labelledby="h-datos"><div className="acc-card__core">
        <div className="profile-card__head">
          <h2 id="h-datos" className="block-title">Mis datos</h2>
          <Pill tone="l" size="sm" icon={Pencil} onClick={() => { setForm(start()); setSaved(false); setEditing(true) }}>Editar</Pill>
        </div>
        {saved && <p className="alert alert--success" role="status">Datos actualizados.</p>}
        <dl className="profile">
          <div><dt>Contacto</dt><dd>{user.name}</dd></div>
          <div><dt>Correo</dt><dd>{user.email}</dd></div>
          <div><dt>Teléfono</dt><dd>{customer.phone}</dd></div>
          <div><dt>Ciudad</dt><dd>{customer.city}</dd></div>
          {customer.address && <div><dt>Dirección</dt><dd>{customer.address}</dd></div>}
        </dl>
      </div>
      </Bezel>
    )
  }

  return (
    <Bezel as="div" className="acc-card"><form className="acc-card__core" onSubmit={submit} noValidate>
      <h2 className="block-title">Editar mis datos</h2>
      {error && <div className="alert alert--error" role="alert">{error.message}</div>}
      <Field label={company ? 'Nombre de contacto' : 'Nombre completo'} value={form.name} onChange={set('name')} error={error?.fieldError?.('name')} autoComplete="name" required />
      {company && <Field label="Razón social" value={form.company_name} onChange={set('company_name')} error={error?.fieldError?.('company_name')} autoComplete="organization" required />}
      <Field label="Teléfono" type="tel" inputMode="tel" value={form.phone} onChange={set('phone')} error={error?.fieldError?.('phone')} autoComplete="tel" required />
      <Field label="Ciudad" value={form.city} onChange={set('city')} error={error?.fieldError?.('city')} autoComplete="address-level2" required />
      <Field label="Dirección (opcional)" value={form.address} onChange={set('address')} error={error?.fieldError?.('address')} autoComplete="street-address" />
      <p className="field-hint">El correo, el tipo de cliente y el NIT no se pueden cambiar aquí: escríbenos si necesitas corregirlos.</p>
      <div className="row-actions">
        <button type="submit" className="btn btn--accent" disabled={busy}>{busy ? 'Guardando…' : 'Guardar'}</button>
        <button type="button" className="btn btn--ghost" onClick={() => setEditing(false)}>Cancelar</button>
      </div>
    </form></Bezel>
  )
}

export default function Account() {
  const { user, logout } = useAuth()
  const [type, setType] = useState('')
  const { data, loading, error, reload } = useApi(() => ordersApi.list({ type, per_page: 20 }), [type])
  const all = useApi(() => ordersApi.list({ per_page: 50 }), [])
  const customer = user?.customer
  const orders = data?.data ?? []
  const summary = all.data?.data ?? []
  const open = (o) => !['delivered', 'cancelled', 'rejected', 'accepted'].includes(o.status)
  const inProgress = summary.filter(open)
  const first = user?.name?.trim().split(' ')[0]

  return (
    <>
      <Cover
        eyebrow="Mi cuenta"
        lines={['Hola,', { text: first ?? 'bienvenido', accent: true }]}
        lead={customer ? (customer.type === 'company' ? `${customer.company_name} · NIT ${customer.nit}` : `${customer.type_label}${customer.nit ? ` · C.C. ${customer.nit}` : ''}`) : undefined}
        actions={
          <>
            <Pill as="link" to="/productos" tone="o">Ir al catálogo</Pill>
            <Pill tone="l" icon={LogOut} onClick={logout}>Cerrar sesión</Pill>
          </>
        }
      >
        <ul className="acc-bento" aria-label="Resumen de tu cuenta">
          <li className="bz"><div className="core"><ClipboardList size={20} strokeWidth={1.5} aria-hidden="true" /><strong>{summary.filter((o) => o.type === 'purchase').length}</strong><span>Pedidos</span></div></li>
          <li className="bz"><div className="core"><FileText size={20} strokeWidth={1.5} aria-hidden="true" /><strong>{summary.filter((o) => o.type === 'quote').length}</strong><span>Cotizaciones</span></div></li>
          <li className="bz acc-bento__wide">
            <div className="core acc-bento__ink">
              <Truck size={20} strokeWidth={1.5} aria-hidden="true" />
              <strong>{inProgress.length}</strong>
              <span>{inProgress.length === 1 ? 'solicitud en curso' : 'solicitudes en curso'}</span>
              {inProgress[0] && <small>Última: {inProgress[0].code} · {inProgress[0].status_label}</small>}
            </div>
          </li>
        </ul>
      </Cover>
      <div className="pgbody bg-lila-fade acc-body">
        <Bgfx b a="gear:r" />

      <div className="wrap acc-grid">
        <div className="acc-side">
          {customer && <ProfileCard user={user} customer={customer} />}
          <Bezel as="section" className="acc-card" aria-labelledby="h-dir">
            <div className="acc-card__core">
              <h2 id="h-dir" className="block-title"><MapPin size={20} aria-hidden="true" /> Direcciones guardadas</h2>
              <p className="muted-box">Aún no se pueden guardar. Por ahora indicas la dirección de entrega al enviar tu solicitud.</p>
              <button type="button" className="btn btn--ghost btn--sm" disabled>Agregar dirección</button>
            </div>
          </Bezel>
        </div>

        <section className="acc-orders" aria-labelledby="h-pedidos">
          <h2 id="h-pedidos" className="h2-sys">Mis pedidos y <em>cotizaciones</em></h2>
          <div className="acc-tabs" role="tablist" aria-label="Filtrar por tipo">
            {tabs.map((t) => (
              <button key={t.value} type="button" role="tab" aria-selected={type === t.value} className={type === t.value ? 'is-active' : ''} onClick={() => setType(t.value)}>
                {t.label}
              </button>
            ))}
          </div>

          {loading && <div className="skeleton" style={{ height: 160, borderRadius: 30 }} aria-label="Cargando" />}
          {!loading && error && <ErrorState error={error} onRetry={reload} />}
          {!loading && !error && orders.length === 0 && (
            <EmptyState title="Aún no tienes solicitudes">
              Cuando envíes tu carrito, tus pedidos aparecerán aquí. <Link to="/productos">Explorar productos</Link>
            </EmptyState>
          )}
          {!loading && !error && orders.length > 0 && (
            <ul className="acc-list">{orders.map((o) => <OrderRow key={o.code} order={o} />)}</ul>
          )}
        </section>
      </div>
      </div>
    </>
  )
}
