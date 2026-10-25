import { Headset, Truck } from 'lucide-react'
import Counter from '../ui/Counter'
import { site } from '../../config/site'
import '../../styles/home.css'

// Tarjeta blanca que se monta sobre el borde inferior del hero
export default function TrustStrip() {
  return (
    <section className="stats" aria-label="Por qué elegirnos">
      <div className="container">
        <ul className="stats__card">
          <li>
            <Counter to={site.years} prefix="+" className="stats__value" />
            <span>años de experiencia</span>
          </li>
          <li>
            <Counter to={1000} prefix="+" className="stats__value" />
            <span>referencias en catálogo</span>
          </li>
          <li>
            <Truck size={38} aria-hidden="true" />
            <span>Envíos a todo el país</span>
          </li>
          <li>
            <Headset size={38} aria-hidden="true" />
            <span>Asesoría técnica</span>
          </li>
        </ul>
      </div>
    </section>
  )
}
