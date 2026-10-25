import { Zap } from 'lucide-react'
import '../../styles/auth.css'

// Pantalla dividida de crear cuenta e ingresar: a la izquierda la fachada con el mensaje encima,
// a la derecha el formulario. Pasa por debajo del encabezado flotante.
export default function AuthShell({ eyebrow, title, accent, benefits = [], children }) {
  return (
    <section className="auth">
      <aside className="auth__visual">
        <img src="/img/fachada.webp" alt="" width="1200" height="900" fetchPriority="high" />
        <span className="auth__shade" aria-hidden="true" />
        <span className="auth__arc" aria-hidden="true" />
        <div className="auth__copy">
          <p className="eyebrow"><Zap size={14} aria-hidden="true" /> {eyebrow}</p>
          <h1 className="display">{title} <em>{accent}</em></h1>
          {benefits.length > 0 && (
            <ul>
              {benefits.map(({ icon: Icon, text }) => (
                <li key={text}>
                  <Icon size={20} aria-hidden="true" />
                  <span>{text}</span>
                </li>
              ))}
            </ul>
          )}
        </div>
      </aside>
      <div className="auth__panel">{children}</div>
    </section>
  )
}
