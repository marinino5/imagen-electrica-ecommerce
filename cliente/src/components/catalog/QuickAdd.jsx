import { useCallback, useEffect, useId, useLayoutEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { useNavigate } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { Check, FileText, Minus, Plus, ShoppingCart, X } from 'lucide-react'
import { catalogApi } from '../../api'
import { useCart } from '../../context/CartContext'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import { isDirectPurchase } from '../../lib/catalog'
import { formatCOP } from '../../lib/format'
import { cardState, cartItem, lineQuoteItem } from '../../lib/lines'
import '../../styles/quick-add.css'

// Las referencias de una línea se piden una sola vez y se recuerdan mientras la pestaña esté abierta
const cache = new Map()
const loadVariants = (slug) => {
  if (!cache.has(slug)) {
    cache.set(
      slug,
      catalogApi.line(slug).then(
        (res) => res.data?.variants ?? [],
        (err) => {
          cache.delete(slug)
          throw err
        },
      ),
    )
  }
  return cache.get(slug)
}

const POPOVER_W = 320

/**
 * Compra rápida de una tarjeta de línea (botón circular de 44 px):
 *  - línea con una sola referencia comprable: se agrega directo;
 *  - varias referencias: mini selector (popover en escritorio, hoja inferior en el celular);
 *  - línea solo de cotización: «Cotizar», abre la cotización con esa línea precargada;
 *  - línea con precio pero sin stock: «Agotado · cotizar» (sin compra en línea).
 * El botón lleva texto (Agregar al carrito / Cotizar / Agotado · cotizar), no solo el icono.
 * Al agregar: toast (lo da el carrito), pulso del contador y un check en el botón durante 1,2 s.
 */
export default function QuickAdd({ line, image, preselect = null }) {
  const { add } = useCart()
  const navigate = useNavigate()
  const sheet = useMediaQuery('(max-width: 767px)')
  const btn = useRef(null)
  const panel = useRef(null)
  const titleId = useId()
  const [open, setOpen] = useState(false)
  const [done, setDone] = useState(false)
  const [state, setState] = useState({ status: 'idle', variants: [] })
  const [sku, setSku] = useState(null)
  const [qty, setQty] = useState(1)
  const [pos, setPos] = useState(null)

  const kind = cardState(line)
  const quoteOnly = kind !== 'buy' // cotizar: solo cotización o agotada
  const quick = line.quick_variant
  const origin = () => ({ rect: btn.current.getBoundingClientRect(), image })

  // Check de 1,2 s en el botón
  useEffect(() => {
    if (!done) return undefined
    const t = setTimeout(() => setDone(false), 1200)
    return () => clearTimeout(t)
  }, [done])

  const close = useCallback((restoreFocus = true) => {
    setOpen(false)
    if (restoreFocus) btn.current?.focus()
  }, [])

  // Posición del popover junto al botón (escritorio): abre hacia donde haya más espacio
  useLayoutEffect(() => {
    if (!open || sheet || !btn.current) return
    const r = btn.current.getBoundingClientRect()
    const below = window.innerHeight - r.bottom
    const up = below < 380 && r.top > below
    const left = Math.min(Math.max(12, r.right - POPOVER_W), window.innerWidth - POPOVER_W - 12)
    setPos({
      left,
      top: up ? undefined : r.bottom + 10,
      bottom: up ? window.innerHeight - r.top + 10 : undefined,
      origin: `${r.right - left - 22}px ${up ? '100%' : '0'}`,
    })
  }, [open, sheet])

  // Cerrar: Escape, clic fuera, scroll o cambio de tamaño (el popover no sigue al botón)
  useEffect(() => {
    if (!open) return undefined
    const onKey = (e) => e.key === 'Escape' && close()
    const onDown = (e) => {
      if (!panel.current?.contains(e.target) && !btn.current?.contains(e.target)) close(false)
    }
    const away = () => !sheet && close(false)
    document.addEventListener('keydown', onKey)
    document.addEventListener('pointerdown', onDown)
    window.addEventListener('scroll', away, { passive: true })
    window.addEventListener('resize', away)
    if (sheet) document.documentElement.style.overflow = 'hidden'
    return () => {
      document.removeEventListener('keydown', onKey)
      document.removeEventListener('pointerdown', onDown)
      window.removeEventListener('scroll', away)
      window.removeEventListener('resize', away)
      document.documentElement.style.overflow = ''
    }
  }, [open, sheet, close])

  const buyable = state.variants.filter((v) => isDirectPurchase(v, 1))
  const chosen = buyable.find((v) => v.sku === sku) ?? buyable[0] ?? null

  // Al abrir: pedir las referencias y llevar el foco al panel
  useEffect(() => {
    if (!open) return
    setQty(1)
    setSku(preselect)
    if (state.status === 'ready') return
    let alive = true
    setState({ status: 'loading', variants: [] })
    loadVariants(line.slug).then(
      (variants) => alive && setState({ status: 'ready', variants }),
      () => alive && setState({ status: 'error', variants: [] }),
    )
    return () => {
      alive = false
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, line.slug])

  useEffect(() => {
    if (open && (pos || sheet)) panel.current?.querySelector('button, a')?.focus({ preventScroll: true })
  }, [open, pos, sheet, state.status])

  const flash = () => setDone(true)

  const onClick = () => {
    if (quoteOnly) {
      add(lineQuoteItem(line), 1, origin(), { asQuote: true })
      navigate('/carrito#cotizacion')
      return
    }
    if (quick) {
      add(cartItem(line, quick), 1, origin())
      flash()
      return
    }
    setOpen((o) => !o)
  }

  const addChosen = () => {
    if (!chosen) return
    const fits = isDirectPurchase(chosen, qty)
    add(cartItem(line, chosen), qty, origin(), fits ? {} : { asQuote: true })
    close(false)
    flash()
  }

  const Icon = quoteOnly ? FileText : done ? Check : ShoppingCart
  const label = kind === 'out' ? `${line.name}: agotado, cotizar` : quoteOnly ? `Cotizar ${line.name}` : `Agregar ${line.name} al carrito`
  const text = kind === 'out' ? ['Agotado · cotizar', 'Agotado'] : quoteOnly ? ['Cotizar', 'Cotizar'] : done ? ['Agregado', 'Agregado'] : ['Agregar al carrito', 'Agregar']
  const fits = chosen ? isDirectPurchase(chosen, qty) : true

  const body = (
    <>
      <header className="qa__head">
        <p id={titleId}>Agregar al carrito</p>
        <button type="button" className="qa__x" onClick={() => close()} aria-label="Cerrar"><X size={18} aria-hidden="true" /></button>
      </header>
      <p className="qa__line">{line.name}</p>
      {state.status === 'loading' && <p className="qa__msg" role="status">Cargando referencias…</p>}
      {state.status === 'error' && (
        <p className="qa__msg">No pudimos cargar las referencias. <button type="button" className="qa__link" onClick={() => navigate(`/productos/${line.slug}`)}>Abrir la ficha</button></p>
      )}
      {state.status === 'ready' && buyable.length === 0 && (
        <p className="qa__msg">Esta línea se cotiza. <button type="button" className="qa__link" onClick={() => navigate(`/productos/${line.slug}`)}>Ver la ficha</button></p>
      )}
      {chosen && (
        <>
          <div className="qa__chips" role="group" aria-label="Referencia">
            {buyable.map((v) => (
              <button key={v.sku} type="button" className={v.sku === chosen.sku ? 'is-on' : ''} aria-pressed={v.sku === chosen.sku} onClick={() => { setSku(v.sku); setQty(1) }}>
                {v.label ?? v.sku}
              </button>
            ))}
          </div>
          <div className="qa__row">
            <p className="qa__price"><strong>{formatCOP(chosen.price)}</strong><small>IVA incluido</small></p>
            <div className="qa__qty" role="group" aria-label="Cantidad">
              <button type="button" onClick={() => setQty((q) => Math.max(1, q - 1))} aria-label="Menos"><Minus size={16} aria-hidden="true" /></button>
              <input type="number" inputMode="numeric" min="1" value={qty} onChange={(e) => setQty(Math.max(1, Number(e.target.value) || 1))} aria-label="Cantidad" />
              <button type="button" onClick={() => setQty((q) => q + 1)} aria-label="Más"><Plus size={16} aria-hidden="true" /></button>
            </div>
          </div>
          {fits ? (
            <button type="button" className="qa__go" onClick={addChosen}><ShoppingCart size={18} aria-hidden="true" /> Agregar</button>
          ) : (
            <>
              <p className="qa__msg">Esta cantidad no se compra en línea: cotízala y un asesor te responde.</p>
              <button type="button" className="qa__go qa__go--quote" onClick={addChosen}><FileText size={18} aria-hidden="true" /> Cotizar esta cantidad</button>
            </>
          )}
        </>
      )}
    </>
  )

  return (
    <>
      <button
        ref={btn}
        type="button"
        className={`qa__pill ${done ? 'is-done' : ''} ${quoteOnly ? 'is-quote' : ''} ${kind === 'out' ? 'is-out' : ''}`}
        aria-label={done ? 'Agregado' : label}
        aria-haspopup={!quoteOnly && !quick ? 'dialog' : undefined}
        aria-expanded={!quoteOnly && !quick ? open : undefined}
        onClick={onClick}
      >
        <Icon size={18} aria-hidden="true" />
        <span className="qa__long">{text[0]}</span>
        <span className="qa__short">{text[1]}</span>
      </button>
      {createPortal(
        <AnimatePresence>
          {open && (
            <>
              {sheet && <motion.div key="bd" className="qa__backdrop" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} transition={{ duration: 0.2 }} />}
              <motion.div
                key="panel"
                ref={panel}
                role="dialog"
                aria-labelledby={titleId}
                className={sheet ? 'qa qa--sheet' : 'qa qa--pop'}
                style={sheet ? undefined : { left: pos?.left, top: pos?.top, bottom: pos?.bottom, width: POPOVER_W, transformOrigin: pos?.origin, visibility: pos ? 'visible' : 'hidden' }}
                initial={sheet ? { y: '100%' } : { opacity: 0, scale: 0.95 }}
                animate={sheet ? { y: 0 } : { opacity: 1, scale: 1 }}
                exit={sheet ? { y: '100%' } : { opacity: 0, scale: 0.97 }}
                transition={sheet ? { duration: 0.28, ease: [0.32, 0.72, 0, 1] } : { duration: 0.16, ease: [0.23, 1, 0.32, 1] }}
              >
                {body}
              </motion.div>
            </>
          )}
        </AnimatePresence>,
        document.body,
      )}
    </>
  )
}
