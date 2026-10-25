import { Link } from 'react-router-dom'
import { Mail, MapPin, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import '../../styles/footer.css'

// lucide-react ya no incluye íconos de marcas: se dibujan aquí con el mismo trazo
function Facebook({ size = 18 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
    </svg>
  )
}
function Instagram({ size = 18 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <rect x="2" y="2" width="20" height="20" rx="5" />
      <circle cx="12" cy="12" r="4" />
      <path d="M17.5 6.5h.01" />
    </svg>
  )
}

const shopLinks = [
  { to: '/productos', label: 'Nuestros productos' },
  { to: '/carrito', label: 'Carrito y cotización' },
  { to: '/como-comprar', label: 'Cómo comprar' },
  { to: '/mi-cuenta', label: 'Mi cuenta' },
]
const companyLinks = [
  { to: '/nosotros', label: 'Nosotros' },
  { to: '/contacto', label: 'Contacto' },
  { to: '/crear-cuenta', label: 'Crea tu cuenta' },
  { to: '/politica-datos', label: 'Política de datos' },
]

// En celular cada bloque es un acordeón; en escritorio el contenido siempre se ve
function Block({ title, children }) {
  const desktop = useMediaQuery('(min-width: 768px)')
  return (
    <details key={desktop} className="footer__block" open={desktop}>
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

export default function Footer() {
  return (
    <footer className="footer">
      {/* Banda CTA: se monta sobre el límite entre la página y el pie */}
      <div className="container footer__cta-wrap">
        <div className="footer-cta">
          <span className="footer-cta__arc footer-cta__arc--a" aria-hidden="true" />
          <span className="footer-cta__arc footer-cta__arc--b" aria-hidden="true" />
          <h2 className="display">¿Necesitas una cotización o asesoría técnica?</h2>
          <div className="footer-cta__actions">
            <Link to="/productos?sale_mode=quote" className="btn btn--ink btn--lg">Solicitar cotización</Link>
            <a href={site.whatsappUrl} target="_blank" rel="noreferrer" className="btn btn--ghost-ink btn--lg">
              <MessageCircle size={18} aria-hidden="true" /> Escríbenos por WhatsApp
            </a>
          </div>
        </div>
      </div>

      <div className="footer__body">
        <span className="footer__bigarc" aria-hidden="true" />
        <div className="container footer__grid">
          <div className="footer__brand">
            <img src="/img/logo.png" alt="Imagen Eléctrica" width="72" height="80" loading="lazy" />
            <p>{site.tagline}. Más de {site.years} años distribuyendo material eléctrico industrial en Bucaramanga.</p>
            <div className="footer__social">
              <a href={site.social.facebook} target="_blank" rel="noreferrer" aria-label="Facebook de Imagen Eléctrica"><Facebook size={18} /></a>
              <a href={site.social.instagram} target="_blank" rel="noreferrer" aria-label="Instagram de Imagen Eléctrica"><Instagram size={18} /></a>
            </div>
          </div>

          <Block title="Tienda"><LinkList links={shopLinks} /></Block>
          <Block title="Empresa"><LinkList links={companyLinks} /></Block>

          <Block title="Visítanos">
            <ul className="footer__contact">
              <li><MapPin size={16} aria-hidden="true" /> <span>{site.address}<br />{site.city}</span></li>
              {site.phones.map((p) => (
                <li key={p.href}><Phone size={16} aria-hidden="true" /> <a href={p.href}>{p.label}</a></li>
              ))}
              <li><Mail size={16} aria-hidden="true" /> <a href={`mailto:${site.email}`}>{site.email}</a></li>
            </ul>
          </Block>
        </div>

        <div className="footer__legal">
          <div className="container">
            <span>
              © {new Date().getFullYear()} Imagen Eléctrica · <Link to="/politica-datos">Política de datos</Link>
            </span>
            <span>Precios en COP con IVA incluido</span>
          </div>
        </div>
      </div>
    </footer>
  )
}
