import { useState } from 'react'
import { Link, Navigate, useLocation, useNavigate } from 'react-router-dom'
import { BadgeCheck, ClipboardList, FileText, Headset } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import Field from '../components/ui/Field'
import AuthShell from '../components/ui/AuthShell'
import '../styles/pages.css'

const benefits = [
  { icon: FileText, text: 'Solicita cotizaciones en línea y recíbelas valoradas por un asesor.' },
  { icon: ClipboardList, text: 'Sigue el estado de tus pedidos y cotizaciones.' },
  { icon: BadgeCheck, text: 'Tus datos quedan guardados para comprar más rápido.' },
  { icon: Headset, text: 'Asesoría técnica de un equipo con más de 25 años en el sector.' },
]

const types = [
  { value: 'natural', label: 'Persona natural' },
  { value: 'company', label: 'Empresa' },
]

const empty = {
  type: 'company', name: '', company_name: '', nit: '', email: '', phone: '', city: '', address: '',
  password: '', privacy_accepted: false, marketing_opt_in: false,
}

export default function Register() {
  const { register, isAuthenticated } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [form, setForm] = useState(empty)
  const [error, setError] = useState(null)
  const [busy, setBusy] = useState(false)

  if (isAuthenticated) return <Navigate to="/mi-cuenta" replace />

  const isCompany = form.type === 'company'
  const set = (name) => (e) => setForm((f) => ({ ...f, [name]: e.target.type === 'checkbox' ? e.target.checked : e.target.value }))
  const err = (name) => error?.fieldError?.(name)

  const submit = async (e) => {
    e.preventDefault()
    setBusy(true)
    setError(null)
    try {
      // Persona natural: sin razón social; la cédula va en nit y es opcional
      const { company_name, nit, address, ...rest } = form
      await register({
        ...rest,
        company_name: isCompany ? company_name : undefined,
        nit: nit || undefined,
        address: address || undefined,
      })
      navigate(location.state?.from ?? '/mi-cuenta', { replace: true })
    } catch (e2) {
      setError(e2)
    } finally {
      setBusy(false)
    }
  }

  return (
    <AuthShell eyebrow="Cuenta gratuita" title="Crea tu" accent="cuenta" benefits={benefits}>
      <h2>Datos de tu cuenta</h2>
      <p className="page-lead">Elige si compras como persona natural o como empresa.</p>

      <form className="form" onSubmit={submit} noValidate>
        {error && <p className="alert alert--error" role="alert">{error.message}</p>}

        <fieldset className="segmented" aria-label="Tipo de cliente">
          {types.map((t) => (
            <label key={t.value}>
              <input type="radio" name="type" value={t.value} checked={form.type === t.value} onChange={set('type')} />
              {t.label}
            </label>
          ))}
        </fieldset>
        {err('type') && <span className="field-error">{err('type')}</span>}

        <div className="form-grid">
          {isCompany ? (
            <>
              <Field label="Razón social" value={form.company_name} onChange={set('company_name')} error={err('company_name')} autoComplete="organization" required />
              <Field label="NIT" value={form.nit} onChange={set('nit')} error={err('nit')} required />
              <Field label="Nombre de contacto" value={form.name} onChange={set('name')} error={err('name')} autoComplete="name" required />
            </>
          ) : (
            <>
              <Field label="Nombre completo" value={form.name} onChange={set('name')} error={err('name')} autoComplete="name" required />
              <Field label="Cédula (opcional)" value={form.nit} onChange={set('nit')} error={err('nit')} inputMode="numeric" />
            </>
          )}
          <Field label="Teléfono" type="tel" value={form.phone} onChange={set('phone')} error={err('phone')} autoComplete="tel" required />
          <Field label="Correo electrónico" type="email" value={form.email} onChange={set('email')} error={err('email')} autoComplete="email" required />
          <Field label="Ciudad" value={form.city} onChange={set('city')} error={err('city')} autoComplete="address-level2" required />
          <Field label="Dirección (opcional)" value={form.address} onChange={set('address')} error={err('address')} autoComplete="street-address" />
        </div>
        <Field label="Contraseña" type="password" value={form.password} onChange={set('password')} error={err('password')} hint="Mínimo 8 caracteres." autoComplete="new-password" required />

        <div className="field">
          <label className="check">
            <input type="checkbox" checked={form.privacy_accepted} onChange={set('privacy_accepted')} />
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

        <button type="submit" className="btn btn--accent btn--lg btn--block" disabled={busy}>
          {busy ? 'Creando cuenta…' : 'Crear cuenta gratis'}
        </button>
      </form>

      <p className="auth__foot">¿Ya tienes cuenta? <Link to="/ingresar" state={location.state}>Ingresa aquí</Link></p>
    </AuthShell>
  )
}
