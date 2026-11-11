import { useEffect, useState } from 'react'
import { Link, useParams, useSearchParams } from 'react-router-dom'
import { CheckCircle2, Circle, ShieldAlert } from 'lucide-react'
import { guestOrdersApi } from '../api'
import { formatCOP } from '../lib/format'
import Bezel from '../components/ui/Bezel'
import Eyebrow from '../components/ui/Eyebrow'
import ErrorState from '../components/ui/ErrorState'
import Pill from '../components/ui/Pill'
import { site } from '../config/site'
import '../styles/checkout.css'

const dateFmt = new Intl.DateTimeFormat('es-CO', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'America/Bogota' })
const qty = (n) => String(Number(n)).replace('.', ',')

// Estado de un pedido hecho sin cuenta. Se abre con el enlace del correo (`?expires=…&signature=…`): la API verifica la firma.
export default function GuestOrder() {
  const { code } = useParams()
  const [params] = useSearchParams()
  const [state, setState] = useState({ order: null, loading: true, error: null })
  const link = { expires: params.get('expires'), signature: params.get('signature') }

  useEffect(() => {
    let alive = true
    if (!link.expires || !link.signature) {
      setState({ order: null, loading: false, error: Object.assign(new Error('Falta el enlace del correo.'), { kind: 'forbidden' }) })
      return undefined
    }
    guestOrdersApi.show(code, link).then(
      (res) => alive && setState({ order: res.data, loading: false, error: null }),
      (error) => alive && setState({ order: null, loading: false, error }),
    )
    return () => { alive = false }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [code, link.expires, link.signature])

  const { order, loading, error } = state

  return (
    <div className="pgbody bg-lila-soft">
      <section className="wrap go">
        <Eyebrow>Estado de tu pedido</Eyebrow>
        <h1 className="h-hero"><span>{code}</span></h1>

        {loading && <p role="status">Cargando tu pedido…</p>}

        {error && (error.kind === 'forbidden' ? (
          <div className="state" role="alert">
            <ShieldAlert size={36} aria-hidden="true" />
            <h3>Este enlace no sirve</h3>
            <p>El enlace no es válido o ya venció (dura {60} días). Escríbenos y te lo reenviamos.</p>
            <Pill as="a" href={site.whatsappUrl} tone="o" target="_blank" rel="noreferrer">Escribir por WhatsApp</Pill>
          </div>
        ) : <ErrorState error={error} />)}

        {order && (
          <Bezel className="go-card">
            <div className="go-body">
              <header className="go-head">
                <div><strong>{order.code}</strong><small>{order.type_label} · {dateFmt.format(new Date(order.created_at))}</small></div>
                <span className="badge">{order.status_label}</span>
                <span className="go-total">{order.total === null ? 'Por valorar' : formatCOP(order.total)}</span>
              </header>

              <ol className="go-timeline" aria-label="Historial de estados">
                {(order.status_history ?? []).map((h, i, all) => (
                  <li key={h.id ?? i} className={i === all.length - 1 ? 'is-now' : ''}>
                    {i === all.length - 1 ? <Circle size={14} aria-hidden="true" /> : <CheckCircle2 size={14} aria-hidden="true" />}
                    <span><strong>{h.to_status_label ?? h.status_label ?? h.to_status}</strong><small>{h.created_at ? dateFmt.format(new Date(h.created_at)) : ''}</small></span>
                  </li>
                ))}
              </ol>

              <table className="go-table" aria-label={`Productos de ${order.code}`}>
                <thead><tr><th>Producto</th><th>Cant.</th><th>Valor</th></tr></thead>
                <tbody>
                  {order.items?.map((item) => (
                    <tr key={item.id}>
                      <td>{item.product_name}<small>Ref. {item.sku}</small></td>
                      <td>{qty(item.quantity)} {item.unit}</td>
                      <td>{item.line_total === null ? 'Por cotizar' : formatCOP(item.line_total)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>

              <dl className="go-facts">
                <div><dt>Entrega</dt><dd>{order.delivery_method_label}</dd></div>
                {order.address && <div><dt>Dirección</dt><dd>{order.address}</dd></div>}
                {order.shipping !== null && <div><dt>Envío</dt><dd>{Number(order.shipping) === 0 ? 'Gratis' : formatCOP(order.shipping)}</dd></div>}
                {order.billing && <div><dt>Facturado a</dt><dd>{order.company_name ?? order.contact_name} · {order.billing.doc_type} {order.billing.doc_number}{order.billing.doc_dv ? `-${order.billing.doc_dv}` : ''}</dd></div>}
                {order.billing?.email && <div><dt>Correo de facturación</dt><dd>{order.billing.email}</dd></div>}
              </dl>
              <p className="co-muted">¿Dudas con tu pedido? <a href={site.whatsappUrl} target="_blank" rel="noreferrer">Escríbenos por WhatsApp</a> o <Link to="/contacto">contáctanos</Link>.</p>
            </div>
          </Bezel>
        )}
      </section>
    </div>
  )
}
