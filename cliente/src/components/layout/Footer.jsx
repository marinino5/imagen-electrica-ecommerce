import { Link } from 'react-router-dom'
import { Mail, MapPin, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import '../../styles/footer.css'

export default function Footer() {
  return (
    <footer className="footer">
      <div className="container footer__grid">
        <div className="footer__brand">
          <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" height="52" loading="lazy" />
          <p>
            Distribuidora de material eléctrico industrial en Bucaramanga. Motores, variadores, cables, iluminación y
            automatización para tu empresa.
          </p>
        </div>

        <nav aria-label="Tienda">
          <h3>Tienda</h3>
          <ul>
            <li><Link to="/productos">Nuestros productos</Link></li>
            <li><Link to="/carrito">Carrito y cotización</Link></li>
            <li><Link to="/como-comprar">Cómo comprar</Link></li>
            <li><Link to="/mi-cuenta">Mi cuenta</Link></li>
          </ul>
        </nav>

        <nav aria-label="Empresa">
          <h3>Empresa</h3>
          <ul>
            <li><Link to="/nosotros">Nosotros</Link></li>
            <li><Link to="/contacto">Contacto</Link></li>
            <li><Link to="/crear-cuenta">Crea tu cuenta empresarial</Link></li>
          </ul>
        </nav>

        <div>
          <h3>Contáctanos</h3>
          <ul>
            <li><Phone size={16} aria-hidden="true" /> <a href={`tel:${site.phone.replace(/\s/g, '')}`}>{site.phone}</a></li>
            <li><MessageCircle size={16} aria-hidden="true" /> <a href={site.whatsappUrl} target="_blank" rel="noreferrer">WhatsApp</a></li>
            <li><Mail size={16} aria-hidden="true" /> <a href={`mailto:${site.email}`}>{site.email}</a></li>
            <li><MapPin size={16} aria-hidden="true" /> {site.city}</li>
          </ul>
        </div>
      </div>

      <div className="footer__legal">
        <div className="container">
          <span>© {new Date().getFullYear()} Imagen Eléctrica. Todos los derechos reservados.</span>
          <span>Precios en COP con IVA incluido.</span>
        </div>
      </div>
    </footer>
  )
}
