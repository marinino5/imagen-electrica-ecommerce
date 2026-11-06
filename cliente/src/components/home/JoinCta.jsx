import { Check } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import { site } from '../../config/site'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

const benefits = [
  'Cotiza en línea y recibe tu cotización valorada por un asesor.',
  'Consulta el estado de cada pedido y cotización desde tu cuenta.',
  'Tus datos quedan guardados para comprar más rápido.',
  `Asesoría técnica de un equipo con más de ${site.years} años en el sector.`,
]

// Captación de cuentas (recuperada del diseño anterior). Si ya tiene sesión, no aplica.
export default function JoinCta() {
  const { isAuthenticated } = useAuth()
  if (isAuthenticated) return null

  return (
    <section className="wrap hm-section" aria-labelledby="h-cuenta">
      <Reveal className="hm-join">
        <div>
          <Eyebrow>Para empresas y personas</Eyebrow>
          <h2 id="h-cuenta" className="h2-sys">Crea tu cuenta y <em>cotiza hoy</em></h2>
          <div className="hm-join__cta">
            <Pill as="link" to="/crear-cuenta" tone="o">Crear cuenta gratis</Pill>
            <Pill as="link" to="/ingresar" tone="l">Ya tengo cuenta</Pill>
          </div>
        </div>
        <ul className="hm-join__list">
          {benefits.map((text) => (
            <li key={text}><Check size={18} aria-hidden="true" /><span>{text}</span></li>
          ))}
        </ul>
      </Reveal>
    </section>
  )
}
