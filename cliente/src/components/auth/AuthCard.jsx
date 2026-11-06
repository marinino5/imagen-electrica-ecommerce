import { motion } from 'framer-motion'
import Bezel from '../ui/Bezel'

// Formulario en bisel con la barra de progreso naranja arriba (progress de 0 a 1)
export default function AuthCard({ progress, caption, children }) {
  return (
    <Bezel className="auth-card">
      <div className="auth-card__bar" role="progressbar" aria-valuemin={0} aria-valuemax={100} aria-valuenow={Math.round(progress * 100)} aria-label={caption}>
        <motion.span initial={false} animate={{ width: `${progress * 100}%` }} transition={{ duration: 0.6, ease: [0.32, 0.72, 0, 1] }} />
      </div>
      <div className="auth-card__body">{children}</div>
    </Bezel>
  )
}
