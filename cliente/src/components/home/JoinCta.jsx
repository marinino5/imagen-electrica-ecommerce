import { Link } from 'react-router-dom'
import { Zap } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import Reveal from '../ui/Reveal'
import Current from '../ui/Current'
import '../../styles/join.css'

const benefits = [
  'Cotiza en línea y recibe tu cotización valorada por un asesor.',
  'Consulta el estado de cada pedido y cotización desde tu cuenta.',
  'Tus datos quedan guardados para comprar más rápido.',
  `Asesoría técnica de un equipo con más de 25 años en el sector.`,
]

export default function JoinCta() {
  const { isAuthenticated } = useAuth()
  // Si ya tiene cuenta, este bloque de captación no aplica
  if (isAuthenticated) return null

  return (
    <section className="section join-section" aria-labelledby="h-cuenta">
      <div className="container">
        <div className="join">
          <span className="join__arc join__arc--a" aria-hidden="true" />
          <Reveal className="join__copy">
            <p className="eyebrow"><Zap size={14} aria-hidden="true" /> Para empresas y personas</p>
            <h2 id="h-cuenta" className="section-title">Crea tu cuenta y <em>cotiza hoy</em></h2>
            <ul className="join__list">
              {benefits.map((text) => (
                <li key={text}>
                  <Zap size={18} aria-hidden="true" />
                  <span>{text}</span>
                </li>
              ))}
            </ul>
            <Link to="/crear-cuenta" className="btn btn--accent btn--lg">Crear cuenta gratis</Link>
          </Reveal>

          <Reveal className="join__photo" delay={0.1}>
            <Current
              className="join__current"
              viewBox="0 0 300 100"
              d="M0 50 C 60 0, 110 100, 170 50 S 260 30, 300 60"
              offset={['start 90%', 'center 50%']}
            />
            <span className="join__frame" aria-hidden="true" />
            <div className="join__art" aria-hidden="true">
              <svg viewBox="0 0 400 300" fill="none">
                <circle cx="200" cy="150" r="128" stroke="var(--violet-glow)" strokeWidth="2" />
                <circle cx="200" cy="150" r="98" stroke="var(--violet-glow)" strokeWidth="2" strokeDasharray="2 10" strokeLinecap="round" />
                <circle cx="200" cy="150" r="70" fill="var(--glass-bg)" stroke="var(--glass-border)" strokeWidth="2" />
              </svg>
              <Zap size={84} strokeWidth={1.25} />
            </div>
          </Reveal>
        </div>
      </div>
    </section>
  )
}
