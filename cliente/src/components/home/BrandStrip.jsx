import { catalogApi } from '../../api'
import { useApi } from '../../hooks/useApi'
import '../../styles/home.css'

// Marquesina de marcas con chips en bisel. Si una marca no tiene logo, se muestra su nombre.
export default function BrandStrip() {
  const { data } = useApi(() => catalogApi.brands(), [])
  const brands = data?.data ?? []
  if (brands.length === 0) return null

  return (
    <section className="hm-brands" aria-label="Marcas que distribuimos">
      <p className="hm-brands__label">Marcas que distribuimos</p>
      <div className="hm-brands__viewport">
        <div className="hm-brands__track">
          {[0, 1].map((copy) => (
            <ul key={copy} className="hm-brands__group" aria-hidden={copy === 1 ? 'true' : undefined}>
              {brands.map((b) => (
                <li key={`${copy}-${b.id}`} className="bz">
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
