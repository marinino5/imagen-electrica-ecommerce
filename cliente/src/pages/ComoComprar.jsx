import { useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { Zap } from 'lucide-react'
import WordReveal from '../components/ui/WordReveal'
import ModeSwitch from '../components/comprar/ModeSwitch'
import Journey from '../components/comprar/Journey'
import Compare from '../components/comprar/Compare'
import Delivery from '../components/comprar/Delivery'
import Payments from '../components/comprar/Payments'
import Faq from '../components/comprar/Faq'
import { MODES } from '../components/comprar/modeData'
import '../styles/kit.css'
import '../styles/como-comprar.css'

// Climas: marca (portada) → claro (recorrido) → profundo (comparación) → claro (entregas)
// → marca (pagos) → claro (preguntas); después viene el pie, que trae su propia banda CTA
export default function ComoComprar() {
  const [params] = useSearchParams()
  const [mode, setMode] = useState(params.get('modo') === 'cotizacion' ? 'quote' : 'direct')

  return (
    <>
      <header className="cover how-cover on-violet">
        <span className="how-cover__ring how-cover__ring--a" aria-hidden="true" />
        <span className="how-cover__ring how-cover__ring--b" aria-hidden="true" />
        <div className="container how-cover__inner">
          <p className="tag-pill"><Zap size={14} aria-hidden="true" /> Cómo comprar</p>
          <WordReveal
            className="display how-cover__title"
            delay={0.1}
            lines={[{ text: 'COMPRAR ES' }, { text: 'ASÍ DE FÁCIL', accent: true }]}
          />
          <div className="how-cover__switch">
            <p>Elige cómo quieres comprar:</p>
            <ModeSwitch mode={mode} onChange={setMode} />
          </div>
          <AnimatePresence mode="wait" initial={false}>
            <motion.p key={mode} className="how-cover__lead" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} transition={{ duration: 0.2 }}>
              {MODES[mode].lead}
            </motion.p>
          </AnimatePresence>
        </div>
      </header>

      {/* Cambiar de modo reemplaza el contenido de toda la página con una transición */}
      <AnimatePresence mode="wait" initial={false}>
        <motion.div
          key={mode}
          initial={{ opacity: 0, y: 18 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.3 }}
        >
          <Journey mode={mode} />
          <Compare mode={mode} />
          <Delivery />
          <Payments mode={mode} />
          <Faq mode={mode} />
        </motion.div>
      </AnimatePresence>
    </>
  )
}
