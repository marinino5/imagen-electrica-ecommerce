import { useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, FileCheck2, PackageCheck, Search, ShoppingCart } from 'lucide-react'
import Eyebrow from '../ui/Eyebrow'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

const icons = [Search, ShoppingCart, FileCheck2, PackageCheck]

// Los mismos 4 pasos con textos distintos según el camino (recuperada del diseño anterior)
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

export default function HowItWorks() {
  const [mode, setMode] = useState('direct')

  return (
    <section className="wrap hm-section" aria-labelledby="h-como">
      <Reveal className="hm-head">
        <div>
          <Eyebrow>Compra sin complicaciones</Eyebrow>
          <h2 id="h-como" className="h2-sys">Así de <em>fácil</em></h2>
          <p className="hm-head__lead">Los pasos son los mismos; cambia quién pone el precio.</p>
        </div>
        <div className="hm-head__actions">
          <div className="hm-how__switch" role="group" aria-label="Elige el camino">
            {Object.entries(paths).map(([key, path]) => (
              <button key={key} type="button" aria-pressed={mode === key} onClick={() => setMode(key)}>{path.label}</button>
            ))}
          </div>
        </div>
      </Reveal>

      <ol className="hm-how__steps">
        {paths[mode].steps.map((step, i) => {
          const Icon = icons[i]
          return (
            <Reveal as="li" key={`${mode}-${i}`} delay={i * 0.05} className="bz hm-how__step">
              <div className="core">
                <span className="hm-how__node"><Icon size={26} aria-hidden="true" /></span>
                <h3><b>{i + 1}</b> {step.title}</h3>
                <p>{step.text}</p>
              </div>
            </Reveal>
          )
        })}
      </ol>

      <Link to="/como-comprar" className="hm-how__more">
        Ver la guía completa de compra <ArrowRight size={18} aria-hidden="true" />
      </Link>
    </section>
  )
}
