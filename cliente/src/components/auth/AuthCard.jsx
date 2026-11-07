import { motion } from 'framer-motion'

// Bloque del formulario dentro de la tarjeta de acceso. Con `progress` (0 a 1) lleva la barra de pasos; sin él, no hay barra
export default function AuthCard({ progress, caption, children }) {
  return (
    <div className="auth-card">
      {progress !== undefined && (
        <div className="auth-card__bar" role="progressbar" aria-valuemin={0} aria-valuemax={100} aria-valuenow={Math.round(progress * 100)} aria-label={caption}>
          <motion.span initial={false} animate={{ width: `${progress * 100}%` }} transition={{ duration: 0.5, ease: [0.32, 0.72, 0, 1] }} />
        </div>
      )}
      <div className="auth-card__body">{children}</div>
    </div>
  )
}
