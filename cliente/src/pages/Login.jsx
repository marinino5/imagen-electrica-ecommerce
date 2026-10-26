import { useState } from 'react'
import { Link, Navigate, useLocation } from 'react-router-dom'
import { ClipboardList, FileText, ShoppingCart } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import { useAuthRedirect } from '../hooks/useAuthRedirect'
import Field from '../components/ui/Field'
import AuthLayout from '../components/auth/AuthLayout'
import AuthCard from '../components/auth/AuthCard'
import PasswordField from '../components/auth/PasswordField'
import '../styles/pages.css'

const benefits = [
  { icon: ClipboardList, text: 'Consulta el estado de tus pedidos y cotizaciones.' },
  { icon: FileText, text: 'Envía tu carrito y recibe tu cotización valorada.' },
  { icon: ShoppingCart, text: 'Tu carrito te espera tal como lo dejaste.' },
]

const emailOk = (v) => /^\S+@\S+\.\S+$/.test(v.trim())

export default function Login() {
  const { login, logout, isAuthenticated } = useAuth()
  const location = useLocation()
  const { target, fromCart, finish } = useAuthRedirect()
  const [form, setForm] = useState({ email: '', password: '' })
  const [touched, setTouched] = useState({})
  const [error, setError] = useState(null)
  const [busy, setBusy] = useState(false)

  if (isAuthenticated) return <Navigate to={target} replace />

  const set = (name) => (e) => {
    setForm((f) => ({ ...f, [name]: e.target.value }))
    setError((err) => (err?.kind === 'validation' ? null : err))
  }
  const blur = (name) => () => setTouched((t) => ({ ...t, [name]: true }))

  // Validación en línea: aparece al salir del campo
  const local = {
    email: !form.email.trim() ? 'Escribe tu correo.' : !emailOk(form.email) ? 'Escribe un correo válido.' : null,
    password: !form.password ? 'Escribe tu contraseña.' : null,
  }
  const shown = (name) => (touched[name] ? local[name] : null) ?? error?.fieldError?.(name)

  const submit = async (e) => {
    e.preventDefault()
    setTouched({ email: true, password: true })
    if (local.email || local.password) return
    setBusy(true)
    setError(null)
    try {
      const user = await login(form.email, form.password)
      if (user.role !== 'cliente') {
        // Administradores y comerciales entran por el panel, no por la tienda
        await logout()
        setError({ message: 'Esta cuenta es del equipo de Imagen Eléctrica y entra por el panel administrativo.' })
        return
      }
      finish(user)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  return (
    <AuthLayout
      eyebrow="Mi cuenta"
      lines={[{ text: 'Bienvenido' }, { text: 'de nuevo', accent: true }]}
      benefits={benefits}
      previewKind="quote"
    >
      <AuthCard progress={1} caption="Ingresar">
        <h2 className="auth-card__title">Ingresar</h2>
        <p className="auth-card__lead">
          {fromCart ? 'Ingresa para enviar tu carrito. Tus productos siguen aquí.' : 'Accede a tu cuenta para enviar solicitudes y ver tus pedidos.'}
        </p>

        {location.state?.notice && <p className="alert alert--warning" role="status">{location.state.notice}</p>}

        <form className="form auth-form" onSubmit={submit} noValidate>
          {error && <p className="alert alert--error" role="alert">{error.message}</p>}
          <Field label="Correo electrónico" type="email" autoComplete="email" value={form.email} onChange={set('email')} onBlur={blur('email')} error={shown('email')} required />
          <PasswordField value={form.password} onChange={set('password')} onBlur={blur('password')} error={shown('password')} autoComplete="current-password" />
          <button type="submit" className="btn btn--accent btn--lg btn--block" disabled={busy}>
            {busy ? 'Ingresando…' : 'Ingresar'}
          </button>
        </form>

        <p className="auth__foot">
          ¿Aún no tienes cuenta? <Link to="/crear-cuenta" state={location.state}>Crea tu cuenta</Link>
        </p>
      </AuthCard>
    </AuthLayout>
  )
}
