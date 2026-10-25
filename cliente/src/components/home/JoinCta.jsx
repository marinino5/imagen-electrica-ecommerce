import { Link } from 'react-router-dom'
import { ClipboardList, FileText, Headset, Zap } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import Reveal from '../ui/Reveal'
import '../../styles/join.css'

const benefits = [
  { icon: FileText, title: 'Cotiza en línea', text: 'Arma tu lista y recibe la cotización valorada por un asesor.' },
  { icon: ClipboardList, title: 'Sigue tus pedidos', text: 'Consulta el estado de cada pedido y cotización desde tu cuenta.' },
  { icon: Zap, title: 'Compra más rápido', text: 'Tus datos de empresa quedan guardados para la próxima vez.' },
  { icon: Headset, title: 'Asesoría técnica', text: 'Un equipo con más de 25 años en el sector te acompaña.' },
]

export default function JoinCta() {
  const { isAuthenticated } = useAuth()
  // Si ya tiene cuenta, este bloque de captación no aplica
  if (isAuthenticated) return null

  return (
    <section className="join" aria-labelledby="h-cuenta">
      <img className="join__bg" src="/media/hero-tablero-poster.jpg" alt="" loading="lazy" />
      <div className="join__shade" />
      <div className="container join__inner">
        <Reveal className="join__copy">
          <p className="eyebrow">Cuenta empresarial gratuita</p>
          <h2 id="h-cuenta" className="section-title">Crea tu cuenta y <em>cotiza hoy</em></h2>
          <p className="section-lead">Registra tu empresa en un minuto y empieza a comprar o cotizar en línea.</p>
          <Link to="/crear-cuenta" className="btn btn--accent btn--lg">Crear cuenta gratis</Link>
        </Reveal>

        <ul className="join__cards">
          {benefits.map(({ icon: Icon, title, text }, i) => (
            <Reveal as="li" key={title} delay={i * 0.08}>
              {/* Tarjeta de vidrio que se voltea al pasar el mouse o con el teclado */}
              <div className="flip" tabIndex={0}>
                <div className="flip__inner">
                  <div className="flip__face flip__front">
                    <Icon size={30} aria-hidden="true" />
                    <h3>{title}</h3>
                  </div>
                  <div className="flip__face flip__back">
                    <p>{text}</p>
                  </div>
                </div>
              </div>
            </Reveal>
          ))}
        </ul>
      </div>
    </section>
  )
}
