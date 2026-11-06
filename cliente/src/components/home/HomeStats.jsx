import { Headset, Truck } from 'lucide-react'
import Counter from '../ui/Counter'
import { useFamilies } from '../../hooks/useFamilies'
import { site } from '../../config/site'
import '../../styles/home.css'

// Tarjeta flotante que se monta sobre el borde inferior del hero
export default function HomeStats() {
  const { meta } = useFamilies()

  return (
    <section className="wrap hm-stats" aria-label="Imagen Eléctrica en cifras">
      <div className="bz hm-stats__card">
        <ul className="core hm-stats__core">
          <li>
          <Counter to={site.years} prefix="+" className="hm-stats__value" />
          <span>años de experiencia</span>
        </li>
        <li>
          <Counter to={meta.references || 351} className="hm-stats__value" />
          <span>referencias en inventario</span>
        </li>
        <li>
          <Truck size={34} aria-hidden="true" />
          <span>Envíos a todo el país</span>
        </li>
        <li>
          <Headset size={34} aria-hidden="true" />
          <span>Asesoría técnica</span>
        </li>
        </ul>
      </div>
    </section>
  )
}
