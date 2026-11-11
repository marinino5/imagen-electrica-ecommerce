import { useCallback, useState } from 'react'
import { nitCheckDigit } from '../lib/checkout'

/**
 * Estado de un formulario plano: valores, errores por campo y el `set('campo')` que sirve para inputs, selects y checkboxes.
 * `set('campo', { reset: 'otro' })` vacía un campo dependiente (el municipio al cambiar el departamento).
 * Al escribir el NIT de una empresa se calcula solo el dígito de verificación (se puede corregir a mano; el servidor lo valida igual).
 */
export function useFields(initial) {
  const [form, setForm] = useState(initial)
  const [errors, setErrors] = useState({})

  const set = useCallback((name, { reset } = {}) => (e) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value
    setForm((f) => {
      const next = { ...f, [name]: value, ...(reset ? { [reset]: '' } : {}) }
      if (name === 'identification' && f.person_type === 'Company') next.check_digit = nitCheckDigit(value)
      return next
    })
    setErrors((er) => ({ ...er, [name]: undefined }))
  }, [])

  return { form, setForm, errors, setErrors, set }
}
