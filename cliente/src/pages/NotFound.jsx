import { Link } from 'react-router-dom'
import { MessageCircle } from 'lucide-react'
import { site } from '../config/site'
import Pill from '../components/ui/Pill'
import '../styles/rd-pages.css'

export default function NotFound() {
  return (
    <section className="nf on-dark" aria-labelledby="h-404">
      <img className="pg-hero__bg" src="/img/fotos/tablero-manos.webp" alt="Manos de un técnico trabajando en un tablero eléctrico con los cables abiertos" width="852" height="1280" fetchPriority="high" />
      <div className="wrap nf__txt">
        <p className="pg-hero__tag">Error 404</p>
        <h1 id="h-404">Este circuito <strong>no cierra</strong></h1>
        <p>La página que buscas no está conectada. Vuelve a un punto seguro:</p>
        <div className="nf__links">
          <Pill as="link" to="/" tone="o">Inicio</Pill>
          <Pill as="link" to="/productos" tone="l">Catálogo</Pill>
          <a className="nf__wa" href={site.whatsappUrl} target="_blank" rel="noreferrer"><MessageCircle size={18} aria-hidden="true" /> Escribir por WhatsApp</a>
        </div>
        <p className="nf__tip">¿Buscabas un producto? Prueba el <Link to="/productos?buscar=1">buscador del catálogo</Link>.</p>
      </div>
    </section>
  )
}
