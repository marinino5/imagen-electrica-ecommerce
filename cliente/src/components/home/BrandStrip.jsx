import { useState } from 'react'
import { Pause, Play } from 'lucide-react'
import { catalogApi } from '../../api'
import { useApi } from '../../hooks/useApi'
import { withLogos } from '../../config/brands'
import '../../styles/home.css'

// Tres copias de la lista: la pista se desplaza el ancho de UNA copia (un tercio) y vuelve al inicio sin
// que se note, aunque la pantalla sea muy ancha. Pausa con el mouse, con el teclado y con el botón.
const COPIES = 3

export default function BrandStrip() {
  const { data } = useApi(() => catalogApi.brands(), [])
  const [paused, setPaused] = useState(false)
  const brands = withLogos(data?.data)

  return (
    <section className={`hm-brands ${paused ? 'is-paused' : ''}`} aria-label="Marcas que distribuimos">
      <div className="wrap hm-brands__head">
        <p>Marcas que distribuimos</p>
        <button type="button" className="hm-brands__pause" onClick={() => setPaused((p) => !p)} aria-label={paused ? 'Reanudar el movimiento de las marcas' : 'Pausar el movimiento de las marcas'}>
          {paused ? <Play size={16} aria-hidden="true" /> : <Pause size={16} aria-hidden="true" />}
        </button>
      </div>
      <div className="hm-brands__viewport">
        <div className="hm-brands__track">
          {Array.from({ length: COPIES }, (_, copy) => (
            <ul key={copy} className="hm-brands__group" aria-hidden={copy > 0 ? 'true' : undefined}>
              {brands.map((b) => (
                <li key={`${copy}-${b.name}`} className="hm-brand" translate="no">
                  {b.logo_url ? <img src={b.logo_url} alt={b.name} height="40" loading="lazy" /> : <span>{b.name}</span>}
                </li>
              ))}
            </ul>
          ))}
        </div>
      </div>
    </section>
  )
}
