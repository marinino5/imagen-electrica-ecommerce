import { useState } from 'react'
import { Link } from 'react-router-dom'
import { ChevronDown, LogOut } from 'lucide-react'
import { ordersApi } from '../api'
import { useAuth } from '../context/AuthContext'
import { useApi } from '../hooks/useApi'
import { formatCOP, formatDate, formatQty } from '../lib/format'
import ErrorState from '../components/ui/ErrorState'
import EmptyState from '../components/ui/EmptyState'
import PageHeader from '../components/ui/PageHeader'
import '../styles/pages.css'

const tabs = [
  { value: '', label: 'Todos' },
  { value: 'purchase', label: 'Pedidos' },
  { value: 'quote', label: 'Cotizaciones' },
]

function OrderRow({ order }) {
  const [open, setOpen] = useState(false)
  return (
    <li className="card order">
      <button type="button" className="order__head" aria-expanded={open} onClick={() => setOpen((o) => !o)}>
        <span>
          <strong>{order.code}</strong>
          <small>{order.type_label} · {formatDate(order.created_at)}</small>
        </span>
        <span className="badge">{order.status_label}</span>
        <span className="order__total">{order.total === null ? 'Por valorar' : formatCOP(order.total)}</span>
        <ChevronDown size={18} aria-hidden="true" className={open ? 'is-open' : ''} />
      </button>

      {open && (
        <div className="order__body">
          <table>
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
          <dl className="order__totals">
            <div><dt>Entrega</dt><dd>{order.delivery_method_label}</dd></div>
            {order.shipping !== null && <div><dt>Envío</dt><dd>{formatCOP(order.shipping)}</dd></div>}
            {order.total !== null && <div><dt>Total (IVA incluido)</dt><dd>{formatCOP(order.total)}</dd></div>}
          </dl>
        </div>
      )}
    </li>
  )
}

export default function Account() {
  const { user, logout } = useAuth()
  const [type, setType] = useState('')
  const { data, loading, error, reload } = useApi(() => ordersApi.list({ type, per_page: 20 }), [type])
  const customer = user?.customer
  const orders = data?.data ?? []

  return (
    <>
    <PageHeader title="Mi" accent="cuenta" crumbs={[{ label: 'Mi cuenta' }]} />
    <div className="container account">
      <header className="card account__head">
        <div>
          {customer && (
            <p className="page-lead">
              {customer.type === 'company'
                ? `${customer.company_name} · NIT ${customer.nit}`
                : `${customer.type_label}${customer.nit ? ` · C.C. ${customer.nit}` : ''}`}
            </p>
          )}
        </div>
        <button type="button" className="btn btn--ghost btn--sm" onClick={logout}>
          <LogOut size={16} aria-hidden="true" /> Cerrar sesión
        </button>
      </header>

      {customer && (
        <dl className="card profile">
          <div><dt>Contacto</dt><dd>{user.name}</dd></div>
          <div><dt>Correo</dt><dd>{user.email}</dd></div>
          <div><dt>Teléfono</dt><dd>{customer.phone}</dd></div>
          <div><dt>Ciudad</dt><dd>{customer.city}</dd></div>
        </dl>
      )}

      <section className="card account__orders" aria-labelledby="h-pedidos">
      <h2 id="h-pedidos" className="block-title">Mis pedidos y cotizaciones</h2>
      <div className="tabs" role="tablist" aria-label="Filtrar por tipo">
        {tabs.map((t) => (
          <button key={t.value} type="button" role="tab" aria-selected={type === t.value} className={type === t.value ? 'is-active' : ''} onClick={() => setType(t.value)}>
            {t.label}
          </button>
        ))}
      </div>

      {loading && <div className="skeleton" style={{ height: 160 }} aria-label="Cargando" />}
      {!loading && error && <ErrorState error={error} onRetry={reload} />}
      {!loading && !error && orders.length === 0 && (
        <EmptyState title="Aún no tienes solicitudes">
          Cuando envíes tu carrito, tus pedidos y cotizaciones aparecerán aquí. <Link to="/productos">Explorar productos</Link>
        </EmptyState>
      )}
      {!loading && !error && orders.length > 0 && (
        <ul className="orders">{orders.map((o) => <OrderRow key={o.code} order={o} />)}</ul>
      )}
      </section>
    </div>
    </>
  )
}
