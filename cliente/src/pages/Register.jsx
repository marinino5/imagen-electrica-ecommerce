import { useState } from 'react'
import { Link, Navigate, useLocation, useNavigate } from 'react-router-dom'
import { BadgeCheck, ClipboardList, FileText, Headset } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import Field from '../components/ui/Field'
import '../styles/pages.css'

const benefits = [
  { icon: FileText, text: 'Solicita cotizaciones en línea y recíbelas valoradas por un asesor.' },
  { icon: ClipboardList, text: 'Sigue el estado de tus pedidos y cotizaciones.' },
  { icon: BadgeCheck, text: 'Tus datos de empresa quedan guardados para comprar más rápido.' },
  { icon: Headset, text: 'Asesoría técnica de un equipo con más de 25 años en el sector.' },
]

const empty = {
  name: '', company_name: '', nit: '', email: '', phone: '', city: '', address: '',
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

  const set = (name) => (e) => setForm((f) => ({ ...f, [name]: e.target.type === 'checkbox' ? e.target.checked : e.target.value }))
  const err = (name) => error?.fieldError?.(name)

  const submit = async (e) => {
    e.preventDefault()
    setBusy(true)
    setError(null)
    try {
      await register({ ...form, address: form.address || undefined })
      navigate(location.state?.from ?? '/mi-cuenta', { replace: true })
    } catch (e2) {
      setError(e2)
    } finally {
      setBusy(false)
    }
  }

  return (
    <section className="container register">
      <aside className="register__benefits">
        <p className="eyebrow">Cuenta empresarial</p>
        <h1 className="page-title">Crea tu <em>cuenta</em></h1>
        <ul>
          {benefits.map(({ icon: Icon, text }) => (
            <li key={text}>
              <Icon size={22} aria-hidden="true" />
              <span>{text}</span>
            </li>
          ))}
        </ul>
        <p className="form-foot">¿Ya tienes cuenta? <Link to="/ingresar" state={location.state}>Ingresa aquí</Link></p>
      </aside>

      <form className="form card" onSubmit={submit} noValidate>
        {error && <p className="alert alert--error" role="alert">{error.message}</p>}
        <div className="form-grid">
          <Field label="Razón social" value={form.company_name} onChange={set('company_name')} error={err('company_name')} autoComplete="organization" required />
          <Field label="NIT" value={form.nit} onChange={set('nit')} error={err('nit')} required />
          <Field label="Nombre de contacto" value={form.name} onChange={set('name')} error={err('name')} autoComplete="name" required />
          <Field label="Teléfono" type="tel" value={form.phone} onChange={set('phone')} error={err('phone')} autoComplete="tel" required />
          <Field label="Correo electrónico" type="email" value={form.email} onChange={set('email')} error={err('email')} autoComplete="email" required />
          <Field label="Ciudad" value={form.city} onChange={set('city')} error={err('city')} autoComplete="address-level2" required />
        </div>
        <Field label="Dirección (opcional)" value={form.address} onChange={set('address')} error={err('address')} autoComplete="street-address" />
        <Field label="Contraseña" type="password" value={form.password} onChange={set('password')} error={err('password')} hint="Mínimo 8 caracteres." autoComplete="new-password" required />

        <div className="field">
          <label className="check">
            <input type="checkbox" checked={form.privacy_accepted} onChange={set('privacy_accepted')} />
            <span>Acepto la política de tratamiento de datos personales de Imagen Eléctrica.</span>
          </label>
          {err('privacy_accepted') && <span className="field-error">{err('privacy_accepted')}</span>}
        </div>
        <label className="check">
          <input type="checkbox" checked={form.marketing_opt_in} onChange={set('marketing_opt_in')} />
          <span>Quiero recibir novedades y promociones (opcional).</span>
        </label>

        <button type="submit" className="btn btn--primary btn--lg btn--block" disabled={busy}>
          {busy ? 'Creando cuenta…' : 'Crear cuenta gratis'}
        </button>
      </form>
    </section>
  )
}
