import { useLocation } from 'react-router-dom'
import { MessageCircle } from 'lucide-react'
import { site } from '../../config/site'
import Pill from '../ui/Pill'
import '../../styles/footer.css'

// Páginas donde la banda sobra: ya están cotizando, ingresando o gestionando su cuenta
const HIDDEN = ['/carrito', '/contacto', '/ingresar', '/crear-cuenta', '/mi-cuenta']

// Banda compacta violeta sobre el pie: cotización sin cuenta en todas las páginas
export default function QuoteBand() {
  const { pathname } = useLocation()
  if (HIDDEN.includes(pathname)) return null

  return (
    <section className="wrap qb" aria-labelledby="h-qb">
      <div className="qb__box">
        <div>
          <h2 id="h-qb">¿Necesitas una cotización o asesoría técnica?</h2>
          <p>Cuéntanos qué necesitas y un asesor te responde con referencia, precio y tiempo de entrega. Sin crear cuenta.</p>
        </div>
        <div className="qb__actions">
          <Pill as="link" to="/carrito#cotizacion" tone="o">Solicitar cotización</Pill>
          <a href={site.whatsappUrl} target="_blank" rel="noreferrer" className="qb__wa"><MessageCircle size={16} aria-hidden="true" /> Escríbenos por WhatsApp</a>
        </div>
      </div>
    </section>
  )
}
