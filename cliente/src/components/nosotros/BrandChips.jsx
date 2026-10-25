import { catalogApi } from '../../api'
import { useApi } from '../../hooks/useApi'
import SectionHeading from '../ui/SectionHeading'
import Reveal from '../ui/Reveal'

// Marcas que distribuimos como chips blancos: nombre violeta, naranja al pasar el mouse
export default function BrandChips() {
  const { data } = useApi(() => catalogApi.brands(), [])
  const brands = data?.data ?? []
  if (brands.length === 0) return null

  return (
    <section className="section section--violet section--clip brand-chips" aria-labelledby="h-marcas">
      <div className="container">
        <SectionHeading id="h-marcas" eyebrow="Marcas" lead="Distribuimos las marcas que tu planta ya conoce.">
          Marcas que <em>distribuimos</em>
        </SectionHeading>
        <ul className="brand-chips__grid">
          {brands.map((brand, i) => (
            <Reveal as="li" key={brand.id} delay={(i % 6) * 0.04}>
              <span className="brand-chip">{brand.name}</span>
            </Reveal>
          ))}
        </ul>
      </div>
    </section>
  )
}
