import { motion } from 'framer-motion'

// Tarjeta blanca del formulario con la barra de progreso naranja arriba (progress de 0 a 1)
export default function AuthCard({ progress, caption, children }) {
  return (
    <div className="auth-card">
      <div className="auth-card__bar" role="progressbar" aria-valuemin={0} aria-valuemax={100} aria-valuenow={Math.round(progress * 100)} aria-label={caption}>
        <motion.span initial={false} animate={{ width: `${progress * 100}%` }} transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }} />
      </div>
      <div className="auth-card__body">{children}</div>
    </div>
  )
}
