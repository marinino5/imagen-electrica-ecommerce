import { Link } from 'react-router-dom'
import { BadgePercent } from 'lucide-react'
import { usePromotions } from '../../hooks/usePromotions'
import { formatCOP } from '../../lib/format'
import LineVisual from '../catalog/LineVisual'
import Eyebrow from '../ui/Eyebrow'
import '../../styles/home.css'

const until = new Intl.DateTimeFormat('es-CO', { day: 'numeric', month: 'long', timeZone: 'America/Bogota' })
// La vigencia llega como 'YYYY-MM-DD': se lee como fecha local para que no se corra un día
const untilText = (iso) => until.format(new Date(`${iso}T12:00:00`))

function badge(p) {
  if (p.discount_percent) return `${String(p.discount_percent).replace('.', ',')} % menos`
  return 'Precio especial'
}

// Promociones del momento: salen del panel (tabla promotions). Sin promociones vigentes la sección no existe.
export default function Promotions() {
  const { data: promos } = usePromotions()
  if (promos.length === 0) return null

  return (
    <section className="hm-promos" aria-labelledby="h-promos">
      <div className="wrap hm-section">
        <div className="hm-head">
          <div>
            <Eyebrow>Por tiempo limitado</Eyebrow>
            <h2 id="h-promos" className="h2-sys">Promociones <em>del momento</em></h2>
          </div>
        </div>
        <ul className="hm-promos__grid">
          {promos.map((p) => {
            const to = `/productos/${p.line.slug}${p.sku ? `?ref=${encodeURIComponent(p.sku)}` : ''}`
            const line = { ...p.line, family: p.line.family }
            return (
              <li key={p.id} className="hm-promo">
                <div className="hm-promo__media">
                  <LineVisual line={line} width={600} height={450} />
                  <span className="hm-promo__badge"><BadgePercent size={16} aria-hidden="true" />{badge(p)}</span>
                </div>
                <div className="hm-promo__body">
                  <p className="hm-promo__line">{[p.line.brand_label, p.line.name].filter(Boolean).join(' · ')}</p>
                  <h3><Link to={to} className="hm-promo__link">{p.title}</Link></h3>
                  {p.description && <p className="hm-promo__desc">{p.description}</p>}
                  <p className="hm-promo__price">
                    {p.final_price !== null ? (
                      <>
                        <strong>{p.is_from_price ? 'Desde ' : ''}{formatCOP(p.final_price)}</strong>
                        {p.original_price !== null && p.original_price !== p.final_price && <s>{formatCOP(p.original_price)}</s>}
                      </>
                    ) : (
                      <strong>Consulta el precio</strong>
                    )}
                  </p>
                  <p className="hm-promo__until">Hasta el {untilText(p.ends_at)} · IVA incluido</p>
                </div>
              </li>
            )
          })}
        </ul>
      </div>
    </section>
  )
}
