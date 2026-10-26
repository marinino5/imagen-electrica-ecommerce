import { useEffect, useState } from 'react'
import { motion, useReducedMotion } from 'framer-motion'
import { Building2, Check, FileText, Headset, MapPin, Search, ShoppingCart, Store, Truck, UserRound, Zap } from 'lucide-react'
import { useCategories } from '../../hooks/useCategories'
import { lineImage } from '../../lib/catalog'
import { StatusTimeline } from '../auth/OrderPreview'

// Mini versiones de los componentes reales de la tienda. Todo es ilustrativo: no hay datos de productos inventados.

const norm = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()

// Encabezado de la tienda en pequeño: logo, buscador y contadores de cotización y carrito
function MiniHeader({ search, quote = 0, cart = 0 }) {
  return (
    <div className="mini-head">
      <img src="/img/logo-blanco.png" alt="" height="22" />
      <span className="mini-head__search">{search ?? <small>Buscar</small>}<Search size={14} aria-hidden="true" /></span>
      <span className="mini-head__icon"><FileText size={16} aria-hidden="true" />{quote > 0 && <b>{quote}</b>}</span>
      <span className="mini-head__icon"><ShoppingCart size={16} aria-hidden="true" />{cart > 0 && <b>{cart}</b>}</span>
    </div>
  )
}

// 1. Buscador con autocompletado: escribe "motor" y sugiere líneas reales del catálogo
export function SearchScreen() {
  const reduced = useReducedMotion()
  const { data: categories } = useCategories()
  const word = 'motor'
  const [typed, setTyped] = useState(reduced ? word.length : 0)

  useEffect(() => {
    if (reduced || typed >= word.length) return
    const t = setTimeout(() => setTyped((n) => n + 1), typed === 0 ? 700 : 260)
    return () => clearTimeout(t)
  }, [typed, reduced])

  const fallback = ['Motores eléctricos', 'Motorreductores', 'Motores trifásicos', 'Motores monofásicos']
  const found = categories.all.filter((c) => norm(c.name).includes(word)).map((c) => c.name)
  const suggestions = (found.length ? found : fallback).slice(0, 4)
  const roots = categories.roots.length ? categories.roots.map((r) => r.name) : ['Motorreductores', 'Motores eléctricos', 'Variadores y arrancadores', 'Ventilación', 'Iluminación', 'Cables']

  return (
    <div className="mini">
      <MiniHeader search={<span className="mini-type">{word.slice(0, typed)}<i /></span>} />
      <div className="mini-body">
        {typed >= 3 && (
          <motion.ul className="mini-suggest" initial={{ opacity: 0, y: -6 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.25 }}>
            {suggestions.map((s) => (
              <li key={s}><Search size={14} aria-hidden="true" /> {s}<span className="badge">Línea</span></li>
            ))}
          </motion.ul>
        )}
        <p className="mini-label"><Zap size={12} aria-hidden="true" /> Explora por línea</p>
        <ul className="mini-lines">
          {roots.slice(0, 6).map((name) => <li key={name}>{name}</li>)}
        </ul>
      </div>
    </div>
  )
}

// 2. Tarjeta de producto con las insignias reales del modo de venta y el botón que corresponde
export function ProductScreen({ mode }) {
  const reduced = useReducedMotion()
  const direct = mode === 'direct'
  const [added, setAdded] = useState(reduced)

  useEffect(() => {
    if (reduced) return
    const t = setTimeout(() => setAdded(true), 1500)
    return () => clearTimeout(t)
  }, [reduced])

  return (
    <div className="mini">
      <MiniHeader cart={direct && added ? 1 : 0} quote={!direct && added ? 1 : 0} />
      <div className="mini-body mini-body--center">
        <div className="mini-card card">
          <div className="mini-card__media">
            <span className="mini-card__badges">
              {direct ? (
                <span className="badge badge--success">Disponible</span>
              ) : (
                <>
                  <span className="badge badge--muted">Solo cotización</span>
                  <span className="badge badge--warning">Bajo pedido</span>
                </>
              )}
            </span>
            <img src={lineImage('motores-electricos')} alt="" width="320" height="240" />
          </div>
          <div className="mini-card__body">
            <span className="skel skel--sm" />
            <span className="skel" />
            {direct ? (
              <p className="mini-price"><span className="skel skel--price" /><small>IVA incluido</small></p>
            ) : (
              <p className="mini-price mini-price--quote">Precio por cotización</p>
            )}
            <motion.button
              type="button"
              tabIndex={-1}
              className={`btn btn--sm btn--block ${direct ? 'btn--accent' : 'btn--primary'}`}
              animate={added && !reduced ? { scale: [1, 0.94, 1] } : undefined}
              transition={{ duration: 0.35 }}
            >
              {direct ? <ShoppingCart size={16} aria-hidden="true" /> : <FileText size={16} aria-hidden="true" />}
              {direct ? 'Agregar al carrito' : 'Solicitar cotización'}
            </motion.button>
          </div>
        </div>
        <p className="mini-legend">
          <span className="badge badge--success">Disponible</span>
          <span className="badge badge--warning">Bajo pedido</span>
          <span className="badge badge--muted">Solo cotización</span>
          <span className="badge badge--warning">Sin stock: cotiza</span>
        </p>
      </div>
    </div>
  )
}

// 3. Ingresar o crear cuenta: pestañas y tarjetas de persona natural o empresa
export function AuthScreen() {
  return (
    <div className="mini">
      <MiniHeader />
      <div className="mini-body mini-body--center">
        <div className="mini-auth card">
          <div className="mini-tabs"><span>Ingresar</span><span className="is-on">Crear cuenta</span></div>
          <div className="mini-types">
            <span><UserRound size={20} aria-hidden="true" /> Persona natural</span>
            <span className="is-on"><Building2 size={20} aria-hidden="true" /> Empresa</span>
          </div>
          <span className="skel skel--input" />
          <span className="skel skel--input" />
          <span className="btn btn--accent btn--sm btn--block">Continuar</span>
        </div>
        <p className="mini-note"><Check size={14} aria-hidden="true" /> Tu carrito se conserva al terminar</p>
      </div>
    </div>
  )
}

const DELIVERY = [
  { icon: Store, label: 'Recoger en tienda' },
  { icon: MapPin, label: 'Envío local' },
  { icon: Truck, label: 'Envío nacional' },
]

// 4. Entrega y envío de la solicitud
export function DeliveryScreen({ mode }) {
  const reduced = useReducedMotion()
  const [pick, setPick] = useState(0)

  useEffect(() => {
    if (reduced) return
    const t = setInterval(() => setPick((n) => (n + 1) % DELIVERY.length), 1500)
    return () => clearInterval(t)
  }, [reduced])

  return (
    <div className="mini">
      <MiniHeader cart={mode === 'direct' ? 1 : 0} quote={mode === 'quote' ? 1 : 0} />
      <div className="mini-body">
        <p className="mini-label">{mode === 'direct' ? <ShoppingCart size={12} aria-hidden="true" /> : <FileText size={12} aria-hidden="true" />} {mode === 'direct' ? 'Compra directa' : 'Para cotizar'}</p>
        <div className="mini-line card"><span className="skel skel--thumb" /><span className="skel" /><span className="skel skel--qty" /></div>
        <p className="mini-label">¿Cómo lo recibes?</p>
        <ul className="mini-radios">
          {DELIVERY.map(({ icon: Icon, label }, i) => (
            <li key={label} className={i === pick ? 'is-on' : ''}>
              <span className="mini-radio" aria-hidden="true" />
              <Icon size={16} aria-hidden="true" /> {label}
            </li>
          ))}
        </ul>
        <span className="btn btn--accent btn--sm btn--block">Enviar solicitud</span>
      </div>
    </div>
  )
}

// 5. Mi cuenta: la línea de estados real del backend
export function StatusScreen({ mode }) {
  return (
    <div className="mini">
      <MiniHeader />
      <div className="mini-body mini-body--center">
        <div className="mini-account card">
          <p className="mini-label"><Zap size={12} aria-hidden="true" /> Mis pedidos y cotizaciones</p>
          <StatusTimeline kind={mode === 'direct' ? 'purchase' : 'quote'} />
        </div>
      </div>
    </div>
  )
}

const ADVISOR = {
  direct: ['Te indica los medios de pago disponibles', 'Confirma tu pago', 'Coordina la entrega'],
  quote: ['Valora tu cotización con precios', 'Te la envía para que la revises', 'Al aceptarla se crea tu pedido', 'Confirma el pago y coordina la entrega'],
}

// 6. El asesor: lista de tareas que se van marcando una a una
export function AdvisorScreen({ mode }) {
  const reduced = useReducedMotion()
  const items = ADVISOR[mode]
  const [done, setDone] = useState(reduced ? items.length : 0)

  useEffect(() => {
    if (reduced) return
    const t = setTimeout(() => setDone((n) => (n >= items.length ? 0 : n + 1)), done >= items.length ? 2600 : 1000)
    return () => clearTimeout(t)
  }, [done, items.length, reduced])

  return (
    <div className="mini">
      <MiniHeader />
      <div className="mini-body mini-body--center">
        <div className="mini-advisor card">
          <p className="mini-advisor__who"><span><Headset size={18} aria-hidden="true" /></span> Tu asesor</p>
          <ul>
            {items.map((item, i) => (
              <li key={item} className={i < done ? 'is-done' : ''}>
                <span className="mini-check">{i < done && <Check size={12} strokeWidth={3} aria-hidden="true" />}</span>
                {item}
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  )
}

export function Screen({ type, mode }) {
  switch (type) {
    case 'search': return <SearchScreen />
    case 'product': return <ProductScreen mode={mode} />
    case 'auth': return <AuthScreen />
    case 'delivery': return <DeliveryScreen mode={mode} />
    case 'status': return <StatusScreen mode={mode} />
    default: return <AdvisorScreen mode={mode} />
  }
}
