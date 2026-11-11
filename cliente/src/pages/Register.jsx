import { useRef, useState } from 'react'
import { Link, Navigate, useLocation } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { ArrowLeft, ArrowRight, CircleAlert } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import { useAuthRedirect } from '../hooks/useAuthRedirect'
import { useFields } from '../hooks/useFields'
import { initialForm, splitName, toInvoicePayload, validateContact, validateInvoice } from '../lib/checkout'
import Field from '../components/ui/Field'
import AuthLayout, { Stepper } from '../components/auth/AuthLayout'
import AuthCard from '../components/auth/AuthCard'
import PasswordField from '../components/auth/PasswordField'
import InvoiceFields, { InvoiceSwitch } from '../components/forms/InvoiceFields'
import '../styles/pages.css'

// Campos del paso 1 (cuenta y contacto): sirven para llevar a la persona al paso donde el servidor marcó un error (422)
const STEP_ONE = ['name', 'email', 'phone', 'password']

// Validación en vivo del paso 1: aparece al salir del campo y se actualiza mientras se escribe (mismas reglas que al continuar)
const liveError = (name, value) => {
  const v = String(value ?? '')
  if (name === 'name') return v.trim().length < 3 ? 'Escribe tu nombre completo.' : undefined
  if (name === 'email') return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v.trim()) ? undefined : 'Escribe un correo válido.'
  if (name === 'phone') return /^(\+57)?3\d{9}$/.test(v.replace(/[^\d+]/g, '')) ? undefined : 'Escribe un celular colombiano de 10 dígitos (empieza por 3).'
  if (name === 'password') return v.length >= 8 ? undefined : v ? 'Debe tener mínimo 8 caracteres.' : 'Crea una contraseña.'
  return undefined
}

const emptyForm = () => ({ ...initialForm(null), password: '', privacy_accepted: false, marketing_opt_in: false, billing_same_as_delivery: false })

/**
 * Crear cuenta en dos pasos: 1 tus datos (nombre, correo, celular, contraseña) · 2 facturación electrónica opcional, con los mismos
 * campos del paso 3 del pago (nombres de Siigo), y la autorización de datos. Sin factura la cuenta se crea solo con el contacto.
 */
export default function Register() {
  const { register, isAuthenticated } = useAuth()
  const location = useLocation()
  const { target, fromCart, finish } = useAuthRedirect()
  const { form, setForm, errors, setErrors, set } = useFields(emptyForm)
  const [step, setStep] = useState(1)
  const [dir, setDir] = useState(1)
  const [serverError, setServerError] = useState(null)
  const [busy, setBusy] = useState(false)
  const touched = useRef({})

  // Si ya hay sesión (por ejemplo, justo después de registrarse) se va al destino, no a Mi cuenta
  if (isAuthenticated) return <Navigate to={target} replace />

  const go = (to) => {
    setDir(to > step ? 1 : -1)
    setStep(to)
  }
  const e = (k) => errors[k] ?? serverError?.fieldError?.(k)
  // Campo del paso 1 con validación en vivo: al salir se valida y, una vez salido, se revalida con cada letra
  const live = (name) => ({
    onChange: (ev) => {
      set(name)(ev)
      if (touched.current[name]) setErrors((er) => ({ ...er, [name]: liveError(name, ev.target.value) }))
    },
    onBlur: () => {
      touched.current[name] = true
      setErrors((er) => ({ ...er, [name]: liveError(name, form[name]) }))
    },
  })

  const checkStepOne = () => {
    const found = validateContact(form)
    if (!form.password) found.password = 'Crea una contraseña.'
    else if (form.password.length < 8) found.password = 'Debe tener mínimo 8 caracteres.'
    return found
  }

  const submit = async (ev) => {
    ev.preventDefault()
    if (step === 1) {
      const found = checkStepOne()
      setErrors(found)
      if (Object.keys(found).length === 0) go(2)
      return
    }
    const found = { ...checkStepOne(), ...validateInvoice(form) }
    if (!form.privacy_accepted) found.privacy_accepted = 'Debes autorizar el tratamiento de tus datos personales.'
    setErrors(found)
    if (Object.keys(found).length > 0) return go(STEP_ONE.some((k) => found[k]) ? 1 : 2)

    setBusy(true)
    setServerError(null)
    try {
      const user = await register({
        name: form.name.trim(),
        email: form.email.trim(),
        phone: form.phone.trim(),
        password: form.password,
        privacy_accepted: form.privacy_accepted,
        marketing_opt_in: form.marketing_opt_in,
        ...toInvoicePayload(form),
      })
      finish(user)
    } catch (err) {
      setServerError(err)
      // Un 422 lleva a la persona al paso donde está el campo a corregir
      if (err.kind === 'validation') go(Object.keys(err.errors ?? {}).some((k) => STEP_ONE.includes(k)) ? 1 : 2)
    } finally {
      setBusy(false)
    }
  }

  // Al encender la factura, los nombres se adelantan con lo escrito en «Nombre completo»
  const toggleInvoice = (ev) => {
    const on = ev.target.checked
    setForm((f) => {
      const [first, last] = splitName(f.name)
      return { ...f, needs_invoice: on, first_name: f.first_name || (f.person_type === 'Person' ? first : ''), last_name: f.last_name || (f.person_type === 'Person' ? last : '') }
    })
  }

  return (
    <AuthLayout title={<>Crea tu <em>cuenta</em></>} stepper={<Stepper steps={['Tus datos', 'Facturación']} current={step} />}>
      <AuthCard>
        <h2 className="auth-card__title">{step === 1 ? 'Cuéntanos quién eres' : 'Facturación electrónica (opcional)'}</h2>

        <form className="form auth-form" onSubmit={submit} noValidate>
          {serverError && <p className="alert alert--error" role="alert"><CircleAlert size={18} aria-hidden="true" /> <span>{serverError.message}</span></p>}

          <AnimatePresence mode="wait" initial={false} custom={dir}>
            <motion.div
              key={step}
              className="auth-form__step"
              custom={dir}
              variants={{ enter: (d) => ({ opacity: 0, x: 28 * d }), shown: { opacity: 1, x: 0 }, leave: (d) => ({ opacity: 0, x: -28 * d }) }}
              initial="enter"
              animate="shown"
              exit="leave"
              transition={{ duration: 0.22 }}
            >
              {step === 1 ? (
                <>
                  <Field label="Nombre completo" value={form.name} {...live('name')} error={e('name')} autoComplete="name" required />
                  <div className="form-grid">
                    <Field label="Correo electrónico" type="email" value={form.email} {...live('email')} error={e('email')} autoComplete="email" required />
                    <Field label="Celular" type="tel" inputMode="tel" value={form.phone} {...live('phone')} error={e('phone')} autoComplete="tel" required hint="Por aquí te llamamos o escribimos por WhatsApp." />
                  </div>
                  <PasswordField value={form.password} {...live('password')} error={e('password')} hint="Mínimo 8 caracteres." autoComplete="new-password" meter />
                  <button type="submit" className="btn btn--accent btn--lg btn--block">
                    Continuar <ArrowRight size={18} aria-hidden="true" />
                  </button>
                </>
              ) : (
                <>
                  <InvoiceSwitch checked={form.needs_invoice} onChange={toggleInvoice} title="Guardar mis datos de facturación" hint="Opcional: quedan listos para tus próximas compras. Puedes agregarlos o cambiarlos después en Mi cuenta." />
                  {form.needs_invoice && <InvoiceFields form={form} setForm={setForm} set={set} errors={errors} serverError={serverError} />}

                  <div className="field">
                    <label className="check">
                      <input type="checkbox" checked={form.privacy_accepted} onChange={set('privacy_accepted')} aria-invalid={e('privacy_accepted') ? 'true' : undefined} />
                      <span>
                        Autorizo el tratamiento de mis datos personales según la <Link to="/politica-datos" target="_blank" rel="noopener">política de tratamiento de datos</Link> (Ley 1581 de 2012).
                      </span>
                    </label>
                    {e('privacy_accepted') && <span className="field-error">{e('privacy_accepted')}</span>}
                  </div>
                  <label className="check">
                    <input type="checkbox" checked={form.marketing_opt_in} onChange={set('marketing_opt_in')} />
                    <span>Quiero recibir novedades y promociones (opcional).</span>
                  </label>

                  <div className="auth-form__actions">
                    <button type="button" className="btn btn--ghost btn--lg" onClick={() => go(1)}>
                      <ArrowLeft size={18} aria-hidden="true" /> Atrás
                    </button>
                    <button type="submit" className="btn btn--accent btn--lg" disabled={busy}>
                      {busy ? 'Creando cuenta…' : 'Crear cuenta gratis'}
                    </button>
                  </div>
                </>
              )}
            </motion.div>
          </AnimatePresence>
        </form>

        <p className="auth__foot">
          {fromCart && 'Al terminar volverás a tu carrito. '}
          ¿Ya tienes cuenta? <Link to="/ingresar" state={location.state}>Ingresa aquí</Link>
        </p>
      </AuthCard>
    </AuthLayout>
  )
}
