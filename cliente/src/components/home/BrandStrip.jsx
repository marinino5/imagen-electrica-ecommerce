import { Link } from 'react-router-dom'
import { withLogos } from '../../config/brands'
import SectionLabel from '../ui/SectionLabel'
import Hl from '../ui/Hl'
import Squares from '../ui/Squares'
import '../../styles/home.css'
import '../../styles/home2.css'

// Logos oficiales en gris que pasan a color con el mouse, en un loop continuo que se detiene con el mouse o el foco del teclado (y no se mueve
// con prefers-reduced-motion). Sin botón de pausa. Sin logo, el nombre en la tipografía de marca.
export default function BrandStrip() {
  const brands = withLogos()
  const track = [...brands, ...brands]

  return (
    <section className="rd-sec bgt bgt-b hm-brands hm-brands--band" aria-labelledby="h-marcas">
      <Squares items={[{ w: 160, h: 120, tone: 'violet', rate: 0.1, top: '-20px', right: '8%' }]} />
      <div className="wrap hm-brands__head">
        <div>
          <SectionLabel n="06">Aliados</SectionLabel>
          <h2 id="h-marcas" className="rd-h2">Marcas que <Hl>distribuimos</Hl></h2>
        </div>
      </div>
      <div className="hm-brands__loop">
        <ul className="hm-brands__track">
          {track.map((b, i) => (
            <li key={`${b.name}-${i}`} aria-hidden={i >= brands.length ? 'true' : undefined}>
              <Link to={`/productos?marca=${encodeURIComponent(b.name.split(' / ')[0])}`} tabIndex={i >= brands.length ? -1 : undefined} aria-label={`Ver productos de ${b.name}`}>
                {b.logo_url ? <img src={b.logo_url} alt={b.name} height="36" loading="lazy" style={{ '--optical': b.optical }} /> : <span>{b.name}</span>}
              </Link>
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}
