import { catalogApi } from '../../api'
import { useApi } from '../../hooks/useApi'
import { withLogos } from '../../config/brands'
import '../../styles/home.css'

// Cuatro copias de la lista: la pista se desplaza el ancho de UNA copia (25 %) y vuelve al inicio sin
// que se note, aunque la pantalla sea más ancha que una sola lista.
const COPIES = 4

// Marquesina de marcas con chips en bisel. Si una marca no tiene logo, se muestra su nombre.
export default function BrandStrip() {
  const { data } = useApi(() => catalogApi.brands(), [])
  const brands = withLogos(data?.data)

  return (
    <section className="hm-brands" aria-label="Marcas que distribuimos">
      <p className="hm-brands__label">Marcas que distribuimos</p>
      <div className="hm-brands__viewport">
        <div className="hm-brands__track">
          {Array.from({ length: COPIES }, (_, copy) => (
            <ul key={copy} className="hm-brands__group" aria-hidden={copy > 0 ? 'true' : undefined}>
              {brands.map((b) => (
                <li key={`${copy}-${b.name}`} className="bz">
                  <span className="core">{b.logo_url ? <img src={b.logo_url} alt={b.name} height="28" loading="lazy" /> : b.name}</span>
                </li>
              ))}
            </ul>
          ))}
        </div>
      </div>
    </section>
  )
}
