import { createContext, useCallback, useContext, useMemo, useState } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { CheckCircle2, X, Zap } from 'lucide-react'
import { Link } from 'react-router-dom'
import '../styles/toast.css'

const ToastContext = createContext(null)
let nextId = 1

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([])

  const [flights, setFlights] = useState([])

  const dismiss = useCallback((id) => setToasts((list) => list.filter((t) => t.id !== id)), [])

  // El producto "vuela" desde el botón hasta el ícono del carrito o de la cotización.
  // from: rectángulo del botón; kind: 'cart' | 'quote'; image: miniatura opcional.
  const fly = useCallback((from, kind, image) => {
    if (!from || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    const visible = (el) => el && el.getBoundingClientRect().width > 0
    let target = document.querySelector(`[data-fly-target="${kind}"]`)
    if (!visible(target)) target = document.querySelector('[data-fly-target="cart"]') // en celular no hay ícono de cotización
    if (!visible(target)) return
    const to = target.getBoundingClientRect()
    const id = nextId++
    setFlights((list) => [
      ...list,
      {
        id,
        image,
        x0: from.left + from.width / 2 - 22,
        y0: from.top + from.height / 2 - 22,
        x1: to.left + to.width / 2 - 22,
        y1: to.top + to.height / 2 - 22,
      },
    ])
  }, [])

  const endFlight = useCallback((id) => setFlights((list) => list.filter((f) => f.id !== id)), [])

  // action: { to, label } opcional (ej. "Ver carrito")
  const notify = useCallback(
    (message, action) => {
      const id = nextId++
      setToasts((list) => [...list.slice(-2), { id, message, action }])
      setTimeout(() => dismiss(id), 4500)
    },
    [dismiss],
  )

  const value = useMemo(() => ({ notify, fly }), [notify, fly])

  return (
    <ToastContext.Provider value={value}>
      {children}
      {flights.map((f) => (
        <motion.span
          key={f.id}
          className="flight"
          aria-hidden="true"
          initial={{ x: f.x0, y: f.y0, scale: 1, opacity: 1 }}
          animate={{ x: f.x1, y: f.y1, scale: 0.35, opacity: 0.4 }}
          transition={{ duration: 0.75, ease: [0.5, 0, 0.2, 1] }}
          onAnimationComplete={() => endFlight(f.id)}
        >
          {f.image ? <img src={f.image} alt="" /> : <Zap size={20} />}
        </motion.span>
      ))}
      <div className="toasts" role="status" aria-live="polite">
        <AnimatePresence>
          {toasts.map((toast) => (
            <motion.div
              key={toast.id}
              className="toast"
              layout
              initial={{ opacity: 0, y: 24, scale: 0.96 }}
              animate={{ opacity: 1, y: 0, scale: 1 }}
              exit={{ opacity: 0, x: 40 }}
              transition={{ duration: 0.25 }}
            >
              <CheckCircle2 size={20} aria-hidden="true" />
              <span className="toast__msg">{toast.message}</span>
              {toast.action && (
                <Link to={toast.action.to} className="toast__action" onClick={() => dismiss(toast.id)}>
                  {toast.action.label}
                </Link>
              )}
              <button type="button" className="toast__close" onClick={() => dismiss(toast.id)} aria-label="Cerrar aviso">
                <X size={16} />
              </button>
            </motion.div>
          ))}
        </AnimatePresence>
      </div>
    </ToastContext.Provider>
  )
}

export function useToast() {
  const ctx = useContext(ToastContext)
  if (!ctx) throw new Error('useToast debe usarse dentro de ToastProvider')
  return ctx
}
