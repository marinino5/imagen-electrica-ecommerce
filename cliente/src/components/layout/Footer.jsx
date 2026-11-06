import { Link } from 'react-router-dom'
import { Mail, MapPin, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import { useFamilies } from '../../hooks/useFamilies'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import Pill from '../ui/Pill'
import { Facebook, Instagram } from '../ui/SocialIcons'
import '../../styles/footer.css'

const shopLinks = [
  { to: '/productos', label: 'Todo el catálogo' },
  { to: '/carrito', label: 'Carrito' },
  { to: '/carrito#cotizacion', label: 'Cotizar sin cuenta' },
  { to: '/como-comprar', label: 'Cómo comprar' },
  { to: '/mi-cuenta', label: 'Mi cuenta' },
]
const companyLinks = [
  { to: '/nosotros', label: 'Nosotros' },
  { to: '/contacto', label: 'Contacto' },
  { to: '/crear-cuenta', label: 'Crea tu cuenta' },
]

// En celular cada bloque es un acordeón; en escritorio el contenido siempre se ve
function Block({ title, children, className = '' }) {
  const desktop = useMediaQuery('(min-width: 900px)')
  return (
    <details key={desktop} className={`ft__block ${className}`} open={desktop}>
      <summary>{title}</summary>
      {children}
    </details>
  )
}

function LinkList({ links }) {
  return (
    <ul>
      {links.map((l) => (
        <li key={l.to}><Link to={l.to}>{l.label}</Link></li>
      ))}
    </ul>
  )
}

// Pie en una sola pieza de doble bisel: la banda de cotizar arriba, integrada, y debajo el pie completo
export default function Footer({ hideCta = false }) {
  const { data: families } = useFamilies()

  return (
    <footer className="ft wrap">
      <div className="bz ft__bz">
        <div className="core ft__core">
          {!hideCta && (
            <div className="ft__cta">
              <div>
                <h2>¿Necesitas una cotización o asesoría técnica?</h2>
                <p>Cuéntanos qué necesitas y un asesor te responde con referencia, precio y tiempo de entrega. Sin crear cuenta.</p>
              </div>
              <div className="ft__cta-actions">
                <Pill as="link" to="/carrito#cotizacion" tone="o">Solicitar cotización</Pill>
                <a href={site.whatsappUrl} target="_blank" rel="noreferrer" className="ft__wa"><MessageCircle size={16} aria-hidden="true" /> Escríbenos por WhatsApp</a>
              </div>
            </div>
          )}

          <div className="ft__grid">
            <div className="ft__brand">
              <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" width="64" height="70" loading="lazy" />
              <p>{site.tagline}. Más de {site.years} años distribuyendo material eléctrico industrial en Bucaramanga.</p>
              <div className="ft__social">
                <a href={site.social.facebook} target="_blank" rel="noreferrer" aria-label="Facebook de Imagen Eléctrica"><Facebook size={18} /></a>
                <a href={site.social.instagram} target="_blank" rel="noreferrer" aria-label="Instagram de Imagen Eléctrica"><Instagram size={18} /></a>
              </div>
            </div>

            <Block title="Tienda"><LinkList links={shopLinks} /></Block>
            {families.length > 0 && (
              <Block title="Líneas">
                <ul>
                  {families.map((f) => <li key={f.id}><Link to={`/productos?familia=${f.slug}`}>{f.name}</Link></li>)}
                </ul>
              </Block>
            )}
            <Block title="Empresa"><LinkList links={companyLinks} /></Block>

            <Block title="Visítanos" className="ft__contact">
              <ul>
                <li><MapPin size={16} aria-hidden="true" /> <span>{site.address}<br />{site.city}</span></li>
                {site.contacts.map((c) => (
                  <li key={c.href}><Phone size={16} aria-hidden="true" /> <a href={c.href}>{c.label}</a>{c.main && <small>Línea principal</small>}</li>
                ))}
                <li><Mail size={16} aria-hidden="true" /> <a href={`mailto:${site.email}`}>{site.email}</a></li>
              </ul>
            </Block>
          </div>

          <div className="ft__legal">
            <span>
              © {new Date().getFullYear()} {site.legalName || 'Imagen Eléctrica'}
              {site.nit && <> · NIT {site.nit}</>}
            </span>
            <nav aria-label="Legal">
              <Link to="/politica-datos">Política de datos</Link>
              <a href={site.sicUrl} target="_blank" rel="noreferrer">Superintendencia de Industria y Comercio (SIC)</a>
            </nav>
            <span>Precios en COP con IVA incluido</span>
          </div>
        </div>
      </div>
    </footer>
  )
}
