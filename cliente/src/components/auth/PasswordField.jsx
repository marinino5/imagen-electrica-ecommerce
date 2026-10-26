import { useId, useState } from 'react'
import { Eye, EyeOff } from 'lucide-react'

const LEVELS = ['', 'Débil', 'Media', 'Buena', 'Fuerte']

// 0 a 4: largo, mayúsculas y minúsculas, números y símbolos
export function passwordLevel(value) {
  if (!value) return 0
  let points = 0
  if (value.length >= 8) points++
  if (value.length >= 12) points++
  if (/[a-z]/.test(value) && /[A-Z]/.test(value)) points++
  if (/\d/.test(value)) points++
  if (/[^A-Za-z0-9]/.test(value)) points++
  if (value.length < 8) return 1
  return Math.min(4, Math.max(2, points - 1))
}

// Contraseña con botón para mostrarla y medidor de seguridad (el medidor solo se muestra al crear la cuenta)
export default function PasswordField({ label = 'Contraseña', value, onChange, onBlur, error, hint, meter = false, autoComplete }) {
  const id = useId()
  const [visible, setVisible] = useState(false)
  const level = passwordLevel(value)
  const describedBy = [error && `${id}-err`, hint && !error && `${id}-hint`, meter && `${id}-meter`].filter(Boolean).join(' ') || undefined

  return (
    <div className="field">
      <label htmlFor={id}>{label}</label>
      <div className="pass">
        <input
          id={id}
          className="input"
          type={visible ? 'text' : 'password'}
          value={value}
          onChange={onChange}
          onBlur={onBlur}
          autoComplete={autoComplete}
          aria-invalid={error ? 'true' : undefined}
          aria-describedby={describedBy}
          required
        />
        <button type="button" className="pass__toggle" onClick={() => setVisible((v) => !v)} aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'} aria-pressed={visible}>
          {visible ? <EyeOff size={18} aria-hidden="true" /> : <Eye size={18} aria-hidden="true" />}
        </button>
      </div>
      {meter && (
        <div id={`${id}-meter`} className="meter" role="meter" aria-label="Seguridad de la contraseña" aria-valuemin={0} aria-valuemax={4} aria-valuenow={level} aria-valuetext={LEVELS[level] || 'Sin escribir'}>
          <span className="meter__bars" aria-hidden="true">
            {[1, 2, 3, 4].map((n) => <i key={n} className={n <= level ? `is-on is-lv${level}` : ''} />)}
          </span>
          <span className="meter__label">{level ? `Seguridad: ${LEVELS[level]}` : 'Usa 8 o más caracteres'}</span>
        </div>
      )}
      {hint && !error && <span id={`${id}-hint`} className="field-hint">{hint}</span>}
      {error && <span id={`${id}-err`} className="field-error">{error}</span>}
    </div>
  )
}
