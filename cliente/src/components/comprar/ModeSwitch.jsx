import { motion } from 'framer-motion'
import { MODES } from './modeData'

// Interruptor grande Compra directa / Cotización: el fondo naranja se desliza hacia la opción elegida
export default function ModeSwitch({ mode, onChange, id = 'mode' }) {
  return (
    <div className="mode-switch" role="radiogroup" aria-label="Elige cómo quieres comprar">
      {Object.values(MODES).map(({ id: key, label, icon: Icon }) => (
        <button key={key} type="button" role="radio" aria-checked={mode === key} className={mode === key ? 'is-on' : ''} onClick={() => onChange(key)}>
          {mode === key && (
            <motion.span layoutId={`${id}-thumb`} className="mode-switch__thumb" transition={{ type: 'spring', stiffness: 420, damping: 36 }} />
          )}
          <span className="mode-switch__label"><Icon size={20} aria-hidden="true" /> {label}</span>
        </button>
      ))}
    </div>
  )
}
