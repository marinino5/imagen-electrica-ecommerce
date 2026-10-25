import { Link } from 'react-router-dom'
import { Zap } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import Reveal from '../ui/Reveal'

// Banda de cierre: invita a crear la cuenta (si ya hay sesión, lleva al catálogo)
export default function ClosingBand({ mode }) {
  const { isAuthenticated } = useAuth()
  return (
    <section className="section section--ink section--clip closing" aria-labelledby="h-cierre">
      <span className="closing__arc closing__arc--a" aria-hidden="true" />
      <span className="closing__arc closing__arc--b" aria-hidden="true" />
      <Zap className="closing__bolt" size={260} strokeWidth={1} aria-hidden="true" />
      <div className="container closing__inner">
        <Reveal className="closing__copy">
          <p className="eyebrow"><Zap size={14} aria-hidden="true" /> {isAuthenticated ? 'Ya tienes cuenta' : 'Empieza hoy'}</p>
          <h2 id="h-cierre" className="section-title">
            {isAuthenticated ? <>Encuentra lo que <em>necesitas</em></> : <>Crea tu cuenta y <em>{mode === 'direct' ? 'compra hoy' : 'cotiza hoy'}</em></>}
          </h2>
          <p className="closing__text">Gratis para personas y empresas. Tus datos quedan guardados para la próxima vez.</p>
        </Reveal>
        <Reveal className="closing__actions" delay={0.1}>
          {isAuthenticated ? (
            <Link to="/productos" className="btn btn--accent btn--lg">Ver productos</Link>
          ) : (
            <>
              <Link to="/crear-cuenta" className="btn btn--accent btn--lg">Crear cuenta gratis</Link>
              <Link to="/ingresar" className="btn btn--ghost-light btn--lg">Ya tengo cuenta</Link>
            </>
          )}
        </Reveal>
      </div>
    </section>
  )
}
