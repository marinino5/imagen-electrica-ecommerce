import { useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion, useMotionValueEvent, useReducedMotion, useScroll, useTransform } from 'framer-motion'
import { ArrowRight, FileCheck2, PackageCheck, Search, ShoppingCart } from 'lucide-react'
import Reveal from '../ui/Reveal'
import SectionHeading from '../ui/SectionHeading'
import '../../styles/how.css'

const icons = [Search, ShoppingCart, FileCheck2, PackageCheck]

// Los mismos 4 pasos, con textos distintos según el camino
const paths = {
  direct: {
    label: 'Compra directa',
    steps: [
      { title: 'Busca', text: 'Encuentra el producto por nombre, referencia o línea.' },
      { title: 'Agrega', text: 'Suma al carrito lo que está disponible, con su precio e IVA.' },
      { title: 'Confirma', text: 'Envía tu pedido y un asesor coordina el pago contigo.' },
      { title: 'Recibe', text: 'Recógelo en tienda o te lo enviamos a todo el país.' },
    ],
  },
  quote: {
    label: 'Cotización',
    steps: [
      { title: 'Busca', text: 'Encuentra el producto o pide la referencia que no ves.' },
      { title: 'Agrega', text: 'Añádelo a tu cotización con la cantidad que necesitas.' },
      { title: 'Cotiza', text: 'Un asesor la valora y te la envía con el precio.' },
      { title: 'Recibe', text: 'Acepta la cotización y recibe tu pedido.' },
    ],
  },
}

const NODES = 4

export default function HowItWorks() {
  const listRef = useRef(null)
  const reduced = useReducedMotion()
  const [mode, setMode] = useState('direct')
  const [reached, setReached] = useState(reduced ? NODES - 1 : 0)

  // La corriente naranja se dibuja con el scroll y va activando cada paso
  const { scrollYProgress } = useScroll({ target: listRef, offset: ['start 70%', 'end 55%'] })
  const drawn = useTransform(scrollYProgress, [0, 1], [0, 1], { clamp: true })
  useMotionValueEvent(drawn, 'change', (v) => setReached(Math.min(NODES - 1, Math.floor(v * (NODES - 1) + 0.02))))
  const active = reduced ? NODES - 1 : reached

  const steps = paths[mode].steps

  return (
    <section className="section section--light" aria-labelledby="h-como">
      <div className="container how">
        <div className="how__intro">
          <SectionHeading id="h-como" eyebrow="Compra sin complicaciones" lead="Elige cómo quieres comprar: los pasos son los mismos, cambia quién pone el precio.">
            Así de <em>fácil</em>
          </SectionHeading>

          <Reveal>
            <div className="how__switch" role="group" aria-label="Elige el camino">
              {Object.entries(paths).map(([key, path]) => (
                <button key={key} type="button" aria-pressed={mode === key} className={mode === key ? 'is-on' : ''} onClick={() => setMode(key)}>
                  {path.label}
                </button>
              ))}
            </div>
            <Link to="/como-comprar" className="how__more">
              Conoce más sobre cómo comprar <ArrowRight size={18} aria-hidden="true" />
            </Link>
          </Reveal>
        </div>

        <div className="how__list" ref={listRef}>
          {/* La corriente: trazo naranja que une los 4 pasos y se dibuja con el scroll */}
          <svg className="how__line" viewBox="0 0 40 400" preserveAspectRatio="none" aria-hidden="true" fill="none">
            <path d="M20 0 L20 400" stroke="var(--lavender-2)" strokeWidth="3" vectorEffect="non-scaling-stroke" />
            <motion.path
              d="M20 0 L20 400"
              stroke="var(--accent)"
              strokeWidth="3"
              strokeLinecap="round"
              vectorEffect="non-scaling-stroke"
              style={{ pathLength: reduced ? 1 : drawn }}
            />
          </svg>

          <ol>
            {steps.map((step, i) => {
              const Icon = icons[i]
              const state = i === active ? 'is-active' : i < active ? 'is-done' : ''
              return (
                <li key={`${mode}-${i}`} className={`how__step ${state}`}>
                  <span className="how__node">
                    <Icon size={24} aria-hidden="true" />
                  </span>
                  <div>
                    <h3><b>{i + 1}</b> {step.title}</h3>
                    <p>{step.text}</p>
                  </div>
                </li>
              )
            })}
          </ol>
        </div>
      </div>
    </section>
  )
}
