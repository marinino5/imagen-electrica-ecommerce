import { useEffect, useRef, useState } from 'react'
import { AnimatePresence, motion, useInView, useReducedMotion, useScroll } from 'framer-motion'
import { Check } from 'lucide-react'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import SectionHeading from '../ui/SectionHeading'
import BrowserWindow from './BrowserWindow'
import { Screen } from './screens'
import { MODES, STEPS } from './modeData'
import '../../styles/auth.css' // la línea de estados comparte estilos con la vista previa de Mi cuenta

// Un paso del recorrido: avisa cuando cruza el centro de la pantalla. En celular lleva su propia mini pantalla.
function Step({ index, step, active, mode, inline, onEnter }) {
  const ref = useRef(null)
  const inView = useInView(ref, { margin: '-42% 0px -42% 0px' })
  useEffect(() => {
    if (inView) onEnter(index)
  }, [inView, index, onEnter])

  const state = index === active ? 'is-active' : index < active ? 'is-done' : ''
  return (
    <li ref={ref} className={`step ${state}`}>
      <span className="step__node" aria-hidden="true">{index < active ? <Check size={20} strokeWidth={3} /> : index + 1}</span>
      <div className="step__copy">
        <h3><span className="sr-only">Paso {index + 1}: </span>{step.title}</h3>
        <p>{step.text}</p>
        {inline && (
          <BrowserWindow path={step.path} className="bwin--inline">
            <Screen type={step.screen} mode={mode} />
          </BrowserWindow>
        )}
      </div>
    </li>
  )
}

// Recorrido interactivo: la ventana de navegador queda fija a la izquierda y cambia con cada paso mientras
// la línea de corriente se llena con el scroll. En celular, cada paso trae su mini pantalla en orden vertical.
export default function Journey({ mode }) {
  const desktop = useMediaQuery('(min-width: 960px)')
  const reduced = useReducedMotion()
  const listRef = useRef(null)
  const [active, setActive] = useState(0)
  const steps = STEPS[mode]

  // La línea naranja se llena entre el primer y el último paso
  const { scrollYProgress } = useScroll({ target: listRef, offset: ['start 55%', 'end 55%'] })

  useEffect(() => setActive(0), [mode])

  const current = steps[active]

  return (
    <section className="section journey" aria-labelledby="h-recorrido">
      <div className="container">
        <SectionHeading id="h-recorrido" eyebrow="Paso a paso" lead={MODES[mode].lead}>
          Así funciona <em>{MODES[mode].label.toLowerCase()}</em>
        </SectionHeading>

        <div className="journey__grid">
          {desktop && (
            <div className="journey__stage">
              <BrowserWindow path={current.path}>
                <AnimatePresence mode="wait" initial={false}>
                  <motion.div
                    key={`${mode}-${active}`}
                    className="journey__screen"
                    initial={{ opacity: 0, y: 14 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -10 }}
                    transition={{ duration: 0.25 }}
                  >
                    <Screen type={current.screen} mode={mode} />
                  </motion.div>
                </AnimatePresence>
              </BrowserWindow>
              <p className="journey__caption" aria-hidden="true">Paso {active + 1} de {steps.length} · Mini versión de la tienda</p>
            </div>
          )}

          <div className="journey__list">
            {/* La corriente: línea naranja que se llena con el scroll */}
            <span className="journey__line" aria-hidden="true">
              <motion.span style={{ scaleY: reduced ? 1 : scrollYProgress }} />
            </span>
            <ol ref={listRef}>
              {steps.map((step, i) => (
                <Step key={`${mode}-${step.title}`} index={i} step={step} active={active} mode={mode} inline={!desktop} onEnter={setActive} />
              ))}
            </ol>
          </div>
        </div>
      </div>
    </section>
  )
}
