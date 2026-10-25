import { catalogApi } from '../../api'
import { useApi } from '../../hooks/useApi'
import '../../styles/home.css'

function BrandItem({ brand }) {
  return brand.logo_url ? (
    <img className="brands__logo" src={brand.logo_url} alt={brand.name} height="32" loading="lazy" />
  ) : (
    <span className="brands__name">{brand.name}</span>
  )
}

// Franja en movimiento continuo. Si una marca no tiene logo, se muestra su nombre.
export default function BrandStrip() {
  const { data } = useApi(() => catalogApi.brands(), [])
  const brands = data?.data ?? []
  if (brands.length === 0) return null

  // Cada grupo repite la lista para llenar el ancho; el segundo grupo (aria-hidden) hace el bucle sin saltos
  const group = [...brands, ...brands]

  return (
    <section className="brands" aria-label="Marcas que distribuimos">
      <p className="brands__label">Marcas que distribuimos</p>
      <div className="brands__viewport">
        <div className="brands__track">
          {[0, 1].map((copy) => (
            <ul key={copy} className="brands__group" aria-hidden={copy === 1 ? 'true' : undefined}>
              {group.map((brand, i) => (
                <li key={`${brand.id}-${i}`}><BrandItem brand={brand} /></li>
              ))}
            </ul>
          ))}
        </div>
      </div>
    </section>
  )
}
