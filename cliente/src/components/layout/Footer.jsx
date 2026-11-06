import { Link } from 'react-router-dom'
import { Mail, MapPin, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import { useFamilies } from '../../hooks/useFamilies'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import { Facebook, Instagram } from '../ui/SocialIcons'
import '../../styles/footer.css'

const companyLinks = [
  { to: '/nosotros', label: 'Nosotros' },
  { to: '/nosotros#h-marcas', label: 'Marcas que distribuimos' },
  { to: '/contacto', label: 'Contacto y tienda' },
  { to: '/crear-cuenta', label: 'Crea tu cuenta' },
]
const helpLinks = [
  { to: '/como-comprar', label: 'Cómo comprar' },
  { to: '/carrito#cotizacion', label: 'Cotizar sin cuenta' },
  { to: '/carrito', label: 'Carrito' },
  { to: '/mi-cuenta', label: 'Mi cuenta' },
  { to: '/politica-datos', label: 'Política de datos' },
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

// Pie de ancho completo en ink: marca, empresa, portafolio, ayuda y contacto con redes
export default function Footer() {
  const { data: families } = useFamilies()

  return (
    <footer className="ft">
      <div className="wrap">
        <div className="ft__grid">
          <div className="ft__brand">
            <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" width="64" height="70" loading="lazy" />
            <p>{site.tagline}. Más de {site.years} años distribuyendo material eléctrico industrial en Bucaramanga.</p>
          </div>

          <Block title="Empresa"><LinkList links={companyLinks} /></Block>

          <Block title="Portafolio">
            <ul>
              <li><Link to="/productos">Todo el catálogo</Link></li>
              {families.map((f) => <li key={f.id}><Link to={`/productos?familia=${f.slug}`}>{f.name}</Link></li>)}
            </ul>
          </Block>

          <Block title="Ayuda"><LinkList links={helpLinks} /></Block>

          <Block title="Contacto" className="ft__contact">
            <ul>
              <li><MapPin size={16} aria-hidden="true" /> <span>{site.address}<br />{site.city}</span></li>
              {site.contacts.map((c) => (
                <li key={c.href}><Phone size={16} aria-hidden="true" /> <a href={c.href}>{c.label}</a>{c.main && <small>Línea principal</small>}</li>
              ))}
              <li><Mail size={16} aria-hidden="true" /> <a href={`mailto:${site.email}`}>{site.email}</a></li>
              <li><MessageCircle size={16} aria-hidden="true" /> <a href={site.whatsappUrl} target="_blank" rel="noreferrer">Escríbenos por WhatsApp</a></li>
            </ul>
            <div className="ft__social">
              <a href={site.social.facebook} target="_blank" rel="noreferrer" aria-label="Facebook de Imagen Eléctrica"><Facebook size={18} /></a>
              <a href={site.social.instagram} target="_blank" rel="noreferrer" aria-label="Instagram de Imagen Eléctrica"><Instagram size={18} /></a>
            </div>
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
    </footer>
  )
}
