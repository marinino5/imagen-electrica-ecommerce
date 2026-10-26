import { useId } from 'react'

// Campo de formulario con etiqueta, ayuda y error (el 422 del backend llega por campo)
export default function Field({ label, error, hint, as: Tag = 'input', children, ...props }) {
  const id = useId()
  const describedBy = [error && `${id}-err`, hint && `${id}-hint`].filter(Boolean).join(' ') || undefined
  return (
    <div className="field">
      <label htmlFor={id}>{label}</label>
      <Tag id={id} className="input" aria-invalid={error ? 'true' : undefined} aria-describedby={describedBy} {...props}>
        {children}
      </Tag>
      {hint && !error && <span id={`${id}-hint`} className="field-hint">{hint}</span>}
      {error && <span id={`${id}-err`} className="field-error">{error}</span>}
    </div>
  )
}
