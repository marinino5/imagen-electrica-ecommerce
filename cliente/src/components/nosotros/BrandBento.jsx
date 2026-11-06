import { catalogApi } from '../../api'
import { useApi } from '../../hooks/useApi'
import Eyebrow from '../ui/Eyebrow'
import Reveal from '../ui/Reveal'

// Marcas en bento de chips en bisel. La primera (WEG) ocupa más espacio.
export default function BrandBento() {
  const { data } = useApi(() => catalogApi.brands(), [])
  const brands = data?.data ?? []
  if (brands.length === 0) return null
  const sorted = [...brands].sort((a, b) => (a.slug === 'weg' ? -1 : b.slug === 'weg' ? 1 : 0))

  return (
    <section className="wrap ab-brands" aria-labelledby="h-marcas">
      <Reveal>
        <Eyebrow>Marcas</Eyebrow>
        <h2 id="h-marcas" className="h2-sys">Marcas que <em>distribuimos</em></h2>
        <p className="ab-brands__lead">Las marcas que las plantas industriales ya conocen y en las que confían.</p>
      </Reveal>
      <ul className="ab-brands__grid">
        {sorted.map((b, i) => (
          <Reveal as="li" key={b.id} delay={Math.min(i, 8) * 0.03} className={`bz ab-chip ${b.slug === 'weg' ? 'ab-chip--big' : ''}`}>
            <span className="core">{b.logo_url ? <img src={b.logo_url} alt={b.name} height="32" loading="lazy" /> : b.name}</span>
          </Reveal>
        ))}
      </ul>
    </section>
  )
}
