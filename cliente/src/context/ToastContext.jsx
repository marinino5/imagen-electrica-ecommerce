import { createContext, useCallback, useContext, useMemo, useState } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { CheckCircle2, X } from 'lucide-react'
import { Link } from 'react-router-dom'
import '../styles/toast.css'

const ToastContext = createContext(null)
let nextId = 1

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([])

  const dismiss = useCallback((id) => setToasts((list) => list.filter((t) => t.id !== id)), [])

  // action: { to, label } opcional (ej. "Ver carrito")
  const notify = useCallback(
    (message, action) => {
      const id = nextId++
      setToasts((list) => [...list.slice(-2), { id, message, action }])
      setTimeout(() => dismiss(id), 4500)
    },
    [dismiss],
  )

  const value = useMemo(() => ({ notify }), [notify])

  return (
    <ToastContext.Provider value={value}>
      {children}
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
