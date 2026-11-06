import { Truck } from 'lucide-react'
import Counter from '../ui/Counter'
import { useFamilies } from '../../hooks/useFamilies'
import { site } from '../../config/site'
import '../../styles/home.css'

// Tarjeta que se monta sobre el borde inferior del hero: una frase que da sentido y cuatro cifras con jerarquía
export default function HomeStats() {
  const { meta } = useFamilies()

  return (
    <section className="wrap hm-stats" aria-label="Imagen Eléctrica en cifras">
      <div className="bz hm-stats__card">
        <div className="core hm-stats__core">
          <p className="hm-stats__lead">
            Una distribuidora de Bucaramanga que conoce <strong>cada referencia que vende</strong>.
          </p>
          <ul className="hm-stats__list">
            <li>
              <Counter to={site.years} prefix="+" className="hm-stats__value" />
              <b>años en el sector</b>
              <span>Surtiendo a talleres, plantas y obras.</span>
            </li>
            <li>
              <Counter to={meta.references || 351} className="hm-stats__value" />
              <b>referencias en inventario</b>
              <span>Con ficha técnica y existencia a la vista.</span>
            </li>
            <li>
              <Counter to={meta.lines || 33} className="hm-stats__value" />
              <b>líneas de producto</b>
              <span>Ordenadas por familia para encontrar rápido.</span>
            </li>
            <li>
              <span className="hm-stats__value hm-stats__value--icon"><Truck size={44} strokeWidth={1.75} aria-hidden="true" /></span>
              <b>Envíos a todo el país</b>
              <span>O recoge en la tienda de Bucaramanga.</span>
            </li>
          </ul>
        </div>
      </div>
    </section>
  )
}
