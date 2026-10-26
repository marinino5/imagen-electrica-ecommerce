import { useEffect, useState } from 'react'
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion'
import { Check, Zap } from 'lucide-react'

// Estados reales del backend (App\Enums\OrderStatus) en el orden del flujo feliz
export const FLOWS = {
  purchase: {
    code: 'PED-0001',
    kind: 'Pedido de compra directa',
    steps: ['Pendiente de pago', 'Pagado', 'Enviado', 'Entregado'],
  },
  quote: {
    code: 'COT-0001',
    kind: 'Cotización',
    steps: ['Nueva', 'En revisión', 'Enviada al cliente', 'Aceptada'],
  },
}

// Cabecera y línea de estados de un pedido de ejemplo que avanza en bucle (la usan la vista previa y el recorrido de Cómo comprar)
export function StatusTimeline({ kind = 'purchase' }) {
  const reduced = useReducedMotion()
  const flow = FLOWS[kind]
  const last = flow.steps.length - 1
  const [index, setIndex] = useState(reduced ? 1 : 0)

  useEffect(() => {
    if (reduced) return
    // Avanza cada 1,8 s; en el último estado se queda un poco más y vuelve a empezar
    const timer = setTimeout(() => setIndex((n) => (n >= last ? 0 : n + 1)), index === last ? 3000 : 1800)
    return () => clearTimeout(timer)
  }, [index, last, reduced])

  return (
    <>
      <div className="preview__head">
        <div>
          <p className="preview__code">{flow.code}</p>
          <p className="preview__kind">{flow.kind}</p>
        </div>
        <div className="preview__status" aria-live="off">
          <AnimatePresence mode="wait" initial={false}>
            <motion.span
              key={index}
              className="badge"
              initial={{ opacity: 0, y: 8 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -8 }}
              transition={{ duration: 0.2 }}
            >
              {flow.steps[index]}
            </motion.span>
          </AnimatePresence>
        </div>
      </div>

      <ol className="preview__steps" style={{ '--n': flow.steps.length }}>
        <span className="preview__track" aria-hidden="true">
          <motion.span
            animate={{ scaleX: index / last }}
            transition={{ duration: reduced ? 0 : 0.6, ease: [0.22, 1, 0.36, 1] }}
          />
        </span>
        {flow.steps.map((label, i) => (
          <li key={label} className={i < index ? 'is-done' : i === index ? 'is-now' : ''}>
            <span className="preview__dot">{i < index ? <Check size={12} strokeWidth={3} aria-hidden="true" /> : i + 1}</span>
            <span className="preview__label">{label}</span>
          </li>
        ))}
      </ol>
    </>
  )
}

// "Vista previa de Mi cuenta": un pedido de ejemplo que avanza por los estados en bucle
export default function OrderPreview({ kind = 'purchase' }) {
  const reduced = useReducedMotion()
  return (
    <motion.aside
      className="preview"
      aria-label="Ejemplo de cómo verás tus pedidos en Mi cuenta"
      animate={reduced ? undefined : { y: [0, -8, 0], rotate: [-0.8, 0.4, -0.8] }}
      transition={{ duration: 7, repeat: Infinity, ease: 'easeInOut' }}
    >
      <div className="preview__top">
        <span className="preview__tag"><Zap size={14} aria-hidden="true" /> Así verás tus pedidos</span>
        <span className="preview__example">Ejemplo</span>
      </div>
      <StatusTimeline kind={kind} />
    </motion.aside>
  )
}
