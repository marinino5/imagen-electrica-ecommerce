import { Zap } from 'lucide-react'
import '../../styles/auth.css'

// Crear cuenta e ingresar: a la izquierda el mensaje y los beneficios en mini tarjetas (sobre el fondo global),
// a la derecha el formulario en una tarjeta blanca. En celular va primero el formulario.
export default function AuthShell({ eyebrow, title, accent, benefits = [], children }) {
  return (
    <section className="container auth">
      <aside className="auth__intro">
        <span className="auth__arc" aria-hidden="true" />
        <p className="eyebrow"><Zap size={14} aria-hidden="true" /> {eyebrow}</p>
        <h1 className="display">{title} <em>{accent}</em></h1>
        <p className="auth__lead">Para personas y empresas</p>
        {benefits.length > 0 && (
          <ul className="auth__benefits">
            {benefits.map(({ icon: Icon, text }) => (
              <li key={text}>
                <span className="auth__icon"><Icon size={20} aria-hidden="true" /></span>
                <span>{text}</span>
              </li>
            ))}
          </ul>
        )}
      </aside>
      <div className="auth__card card">{children}</div>
    </section>
  )
}
