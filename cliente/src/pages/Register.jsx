import { useState } from 'react'
import { Link, Navigate, useLocation } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { ArrowLeft, ArrowRight, BadgeCheck, Building2, ClipboardList, FileText, Headset, UserRound } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import { useAuthRedirect } from '../hooks/useAuthRedirect'
import Field from '../components/ui/Field'
import AuthLayout from '../components/auth/AuthLayout'
import AuthCard from '../components/auth/AuthCard'
import PasswordField from '../components/auth/PasswordField'
import '../styles/pages.css'

const benefits = [
  { icon: FileText, text: 'Solicita cotizaciones en línea y recíbelas valoradas por un asesor.' },
  { icon: ClipboardList, text: 'Sigue el estado de tus pedidos y cotizaciones.' },
  { icon: BadgeCheck, text: 'Tus datos quedan guardados para comprar más rápido.' },
  { icon: Headset, text: 'Asesoría técnica de un equipo con más de 25 años en el sector.' },
]

const types = [
  { value: 'natural', icon: UserRound, title: 'Persona natural', text: 'Compras a título personal. Solo necesitas tu nombre.' },
  { value: 'company', icon: Building2, title: 'Empresa', text: 'Con razón social y NIT, para comprar y cotizar para tu empresa.' },
]

const empty = {
  type: 'company', email: '', password: '',
  name: '', company_name: '', nit: '', phone: '', city: '', address: '',
  privacy_accepted: false, marketing_opt_in: false,
}

// Campos de cada paso: sirven para llevar al usuario al paso donde el backend marcó un error (422)
const STEP_ONE = ['type', 'email', 'password']
const STEP_TWO = ['name', 'company_name', 'nit', 'phone', 'city', 'address', 'privacy_accepted', 'marketing_opt_in']

function validate(form) {
  const e = {}
  if (!form.email.trim()) e.email = 'Escribe tu correo.'
  else if (!/^\S+@\S+\.\S+$/.test(form.email.trim())) e.email = 'Escribe un correo válido.'
  if (!form.password) e.password = 'Crea una contraseña.'
  else if (form.password.length < 8) e.password = 'Debe tener mínimo 8 caracteres.'

  const company = form.type === 'company'
  if (company && !form.company_name.trim()) e.company_name = 'Escribe la razón social.'
  if (company && !form.nit.trim()) e.nit = 'Escribe el NIT.'
  if (!form.name.trim()) e.name = company ? 'Escribe el nombre de contacto.' : 'Escribe tu nombre completo.'
  if (form.phone.replace(/\D/g, '').length < 7) e.phone = 'Escribe un teléfono válido.'
  if (!form.city.trim()) e.city = 'Escribe tu ciudad.'
  if (!form.privacy_accepted) e.privacy_accepted = 'Debes aceptar la política de tratamiento de datos.'
  return e
}

export default function Register() {
  const { register, isAuthenticated } = useAuth()
  const location = useLocation()
  const { target, fromCart, finish } = useAuthRedirect()
  const [step, setStep] = useState(1)
  const [dir, setDir] = useState(1)
  const [form, setForm] = useState(empty)
  const [touched, setTouched] = useState({})
  const [server, setServer] = useState({}) // errores 422 por campo
  const [general, setGeneral] = useState(null)
  const [busy, setBusy] = useState(false)

  // Si ya hay sesión (por ejemplo, justo después de registrarse) se va al destino, no a Mi cuenta
  if (isAuthenticated) return <Navigate to={target} replace />

  const isCompany = form.type === 'company'
  const local = validate(form)
  const set = (name) => (e) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value
    setForm((f) => ({ ...f, [name]: value }))
    setServer((s) => (s[name] ? { ...s, [name]: undefined } : s))
  }
  const blur = (name) => () => setTouched((t) => ({ ...t, [name]: true }))
  // Primero el error del navegador (en línea), luego el que devolvió el servidor
  const err = (name) => (touched[name] ? local[name] : null) ?? server[name]

  const go = (to) => {
    setDir(to > step ? 1 : -1)
    setStep(to)
  }

  const next = () => {
    setTouched((t) => ({ ...t, email: true, password: true }))
    if (STEP_ONE.some((f) => local[f])) return
    go(2)
  }

  const submit = async (e) => {
    e.preventDefault()
    if (step === 1) return next()
    setTouched((t) => ({ ...t, ...Object.fromEntries(STEP_TWO.map((f) => [f, true])) }))
    const firstOne = STEP_ONE.find((f) => local[f])
    if (firstOne) return go(1)
    if (STEP_TWO.some((f) => local[f])) return

    setBusy(true)
    setGeneral(null)
    try {
      // Persona natural: sin razón social; la cédula va en nit y es opcional
      const { company_name, nit, address, ...rest } = form
      const user = await register({
        ...rest,
        company_name: isCompany ? company_name : undefined,
        nit: nit || undefined,
        address: address || undefined,
      })
      finish(user)
    } catch (e2) {
      if (e2.kind === 'validation') {
        const fields = Object.fromEntries(Object.entries(e2.errors ?? {}).map(([k, v]) => [k, v[0]]))
        setServer(fields)
        setTouched((t) => ({ ...t, ...Object.fromEntries(Object.keys(fields).map((k) => [k, true])) }))
        // Lleva al paso donde está el primer campo con error
        if (Object.keys(fields).some((k) => STEP_ONE.includes(k))) go(1)
      }
      setGeneral(e2.message)
    } finally {
      setBusy(false)
    }
  }

  return (
    <AuthLayout
      eyebrow="Cuenta gratuita"
      lines={[{ text: 'Crea tu' }, { text: 'cuenta', accent: true }]}
      benefits={benefits}
      previewKind="purchase"
    >
      <AuthCard progress={step / 2} caption={`Paso ${step} de 2`}>
        <p className="auth-card__step">Paso {step} de 2</p>
        <h2 className="auth-card__title">{step === 1 ? 'Tu cuenta' : isCompany ? 'Datos de la empresa' : 'Tus datos'}</h2>

        <form className="form auth-form" onSubmit={submit} noValidate>
          {general && <p className="alert alert--error" role="alert">{general}</p>}

          <AnimatePresence mode="wait" initial={false} custom={dir}>
            <motion.div
              key={step}
              className="auth-form__step"
              custom={dir}
              variants={{
                enter: (d) => ({ opacity: 0, x: 28 * d }),
                shown: { opacity: 1, x: 0 },
                leave: (d) => ({ opacity: 0, x: -28 * d }),
              }}
              initial="enter"
              animate="shown"
              exit="leave"
              transition={{ duration: 0.22 }}
            >
              {step === 1 ? (
                <>
                  <fieldset className="type-cards">
                    <legend>¿Cómo compras?</legend>
                    {types.map(({ value, icon: Icon, title, text }) => (
                      <label key={value} className={`type-card ${form.type === value ? 'is-on' : ''}`}>
                        <input type="radio" name="type" value={value} checked={form.type === value} onChange={set('type')} />
                        <span className="type-card__icon"><Icon size={26} aria-hidden="true" /></span>
                        <strong>{title}</strong>
                        <span>{text}</span>
                      </label>
                    ))}
                  </fieldset>
                  {err('type') && <span className="field-error">{err('type')}</span>}

                  <Field label="Correo electrónico" type="email" value={form.email} onChange={set('email')} onBlur={blur('email')} error={err('email')} autoComplete="email" required />
                  <PasswordField value={form.password} onChange={set('password')} onBlur={blur('password')} error={err('password')} hint="Mínimo 8 caracteres." autoComplete="new-password" meter />

                  <button type="submit" className="btn btn--accent btn--lg btn--block">
                    Continuar <ArrowRight size={18} aria-hidden="true" />
                  </button>
                </>
              ) : (
                <>
                  <p className="auth-form__chosen">
                    {isCompany ? <Building2 size={18} aria-hidden="true" /> : <UserRound size={18} aria-hidden="true" />}
                    <span>{isCompany ? 'Empresa' : 'Persona natural'} · {form.email}</span>
                    <button type="button" className="link-btn" onClick={() => go(1)}>Cambiar</button>
                  </p>

                  <div className="form-grid">
                    {isCompany ? (
                      <>
                        <Field label="Razón social" value={form.company_name} onChange={set('company_name')} onBlur={blur('company_name')} error={err('company_name')} autoComplete="organization" required />
                        <Field label="NIT" value={form.nit} onChange={set('nit')} onBlur={blur('nit')} error={err('nit')} required />
                        <Field label="Nombre de contacto" value={form.name} onChange={set('name')} onBlur={blur('name')} error={err('name')} autoComplete="name" required />
                      </>
                    ) : (
                      <>
                        <Field label="Nombre completo" value={form.name} onChange={set('name')} onBlur={blur('name')} error={err('name')} autoComplete="name" required />
                        <Field label="Cédula (opcional)" value={form.nit} onChange={set('nit')} onBlur={blur('nit')} error={err('nit')} inputMode="numeric" />
                      </>
                    )}
                    <Field label="Teléfono" type="tel" value={form.phone} onChange={set('phone')} onBlur={blur('phone')} error={err('phone')} autoComplete="tel" required />
                    <Field label="Ciudad" value={form.city} onChange={set('city')} onBlur={blur('city')} error={err('city')} autoComplete="address-level2" required />
                  </div>
                  <Field label="Dirección (opcional)" value={form.address} onChange={set('address')} error={err('address')} autoComplete="street-address" />

                  <div className="field">
                    <label className="check">
                      <input type="checkbox" checked={form.privacy_accepted} onChange={set('privacy_accepted')} onBlur={blur('privacy_accepted')} />
                      <span>
                        Acepto la <Link to="/politica-datos" target="_blank">política de tratamiento de datos personales</Link> de Imagen Eléctrica.
                      </span>
                    </label>
                    {err('privacy_accepted') && <span className="field-error">{err('privacy_accepted')}</span>}
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
