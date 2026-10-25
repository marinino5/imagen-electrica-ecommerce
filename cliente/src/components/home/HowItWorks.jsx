import { useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion, useMotionValue, useMotionValueEvent, useReducedMotion, useScroll, useTransform } from 'framer-motion'
import { FileCheck2, PackageCheck, Search, ShoppingCart } from 'lucide-react'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import '../../styles/how.css'

const icons = [Search, ShoppingCart, FileCheck2, PackageCheck]

// Los mismos 4 pasos, con textos distintos según el camino
const paths = {
  direct: {
    label: 'Compra directa',
    steps: [
      { title: 'Busca', text: 'Encuentra el producto por nombre, referencia o línea.' },
      { title: 'Agrega', text: 'Suma al carrito lo que está disponible, con su precio y IVA.' },
      { title: 'Confirma', text: 'Envía tu pedido y coordinamos el pago contigo.' },
      { title: 'Recibe', text: 'Recógelo en tienda o te lo enviamos a tu empresa.' },
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

function Track({ desktop, reduced }) {
  const ref = useRef(null)
  const [mode, setMode] = useState('direct')
  const [reached, setReached] = useState(reduced ? NODES - 1 : 0)

  // Desktop: la sección queda fija y el scroll dibuja la línea. Celular: se dibuja mientras la sección cruza la pantalla
  const { scrollYProgress } = useScroll({
    target: ref,
    offset: desktop ? ['start start', 'end end'] : ['start 75%', 'end 60%'],
  })
  const scrolled = useTransform(scrollYProgress, [0.06, 0.9], [0, 1], { clamp: true })
  const still = useMotionValue(1)
  const drawn = reduced ? still : scrolled

  useMotionValueEvent(drawn, 'change', (v) => setReached(Math.min(NODES - 1, Math.floor(v * (NODES - 1) + 0.02))))

  const steps = paths[mode].steps

  return (
    <div ref={ref} className="how__track">
      <div className="how__pin">
        <div className="container how__inner">
          <div className="how__head">
            <p className="eyebrow">Compra sin complicaciones</p>
            <h2 className="section-title">Así de <em>fácil</em></h2>

            <div className="how__switch" role="group" aria-label="Elige el camino">
              {Object.entries(paths).map(([key, path]) => (
                <button key={key} type="button" aria-pressed={mode === key} className={mode === key ? 'is-on' : ''} onClick={() => setMode(key)}>
                  {path.label}
                </button>
              ))}
            </div>
          </div>

          <div className="how__steps">
            {/* La corriente: trazo naranja que se dibuja con el scroll (horizontal en escritorio, vertical en celular) */}
            <svg className="how__line how__line--h" viewBox="0 0 1000 80" preserveAspectRatio="none" aria-hidden="true">
              <path d="M125 40 C210 0 290 80 375 40 C460 0 540 80 625 40 C710 0 790 80 875 40" className="how__base" />
              <motion.path d="M125 40 C210 0 290 80 375 40 C460 0 540 80 625 40 C710 0 790 80 875 40" className="how__draw" style={{ pathLength: drawn }} />
            </svg>
            <svg className="how__line how__line--v" viewBox="0 0 40 400" preserveAspectRatio="none" aria-hidden="true">
              <path d="M20 0 C40 60 0 120 20 180 C40 240 0 300 20 400" className="how__base" />
              <motion.path d="M20 0 C40 60 0 120 20 180 C40 240 0 300 20 400" className="how__draw" style={{ pathLength: drawn }} />
            </svg>

            <ol>
              {steps.map((step, i) => {
                const Icon = icons[i]
                return (
                  <li key={`${mode}-${i}`} className={i <= reached ? 'is-reached' : ''}>
                    <span className="how__node">
                      <Icon size={28} aria-hidden="true" />
                      <b>{i + 1}</b>
                    </span>
                    <div>
                      <h3>{step.title}</h3>
                      <p>{step.text}</p>
                    </div>
                  </li>
                )
              })}
            </ol>
          </div>

          <Link to="/como-comprar" className="btn btn--ghost-light">Conoce más sobre cómo comprar</Link>
        </div>
      </div>
    </div>
  )
}

export default function HowItWorks() {
  const desktop = useMediaQuery('(min-width: 900px)')
  const reduced = useReducedMotion()
  // `key` reinicia el seguimiento del scroll si la pantalla cambia de tamaño
  return (
    <section className={`section--ink how ${reduced ? 'how--static' : ''}`} aria-label="Así de fácil">
      <Track key={`${desktop}-${reduced}`} desktop={desktop} reduced={reduced} />
    </section>
  )
}
