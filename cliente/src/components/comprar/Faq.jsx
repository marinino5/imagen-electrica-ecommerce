import { useId, useState } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { Plus } from 'lucide-react'
import SectionHeading from '../ui/SectionHeading'
import { FAQS } from './modeData'

function Item({ q, a }) {
  const id = useId()
  const [open, setOpen] = useState(false)
  return (
    <div className={`faq-item ${open ? 'is-open' : ''}`}>
      <h3>
        <button type="button" aria-expanded={open} aria-controls={id} onClick={() => setOpen((o) => !o)}>
          <span>{q}</span>
          <span className="faq-item__plus"><Plus size={20} aria-hidden="true" /></span>
        </button>
      </h3>
      <AnimatePresence initial={false}>
        {open && (
          <motion.div
            id={id}
            role="region"
            className="faq-item__panel"
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.28, ease: [0.22, 1, 0.36, 1] }}
          >
            <p>{a}</p>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}

// Acordeón en 2 columnas con un "+" que gira; solo muestra las preguntas del modo elegido
export default function Faq({ mode }) {
  const items = FAQS.filter((f) => f.modes.includes(mode))
  const half = Math.ceil(items.length / 2)
  return (
    <section className="section faq-section" aria-labelledby="h-faq">
      <div className="container">
        <SectionHeading id="h-faq" eyebrow="Preguntas frecuentes" lead="Respuestas basadas en cómo funciona la tienda hoy.">
          Resolvemos tus <em>dudas</em>
        </SectionHeading>
        <div className="faq2">
          {[items.slice(0, half), items.slice(half)].map((col, c) => (
            <div key={c} className="faq2__col">
              {col.map((item) => <Item key={item.q} {...item} />)}
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
