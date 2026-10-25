import { useState } from 'react'
import { Link, Navigate, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { ClipboardList, FileText } from 'lucide-react'
import Field from '../components/ui/Field'
import AuthShell from '../components/ui/AuthShell'
import '../styles/pages.css'

export default function Login() {
  const { login, logout, isAuthenticated } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [form, setForm] = useState({ email: '', password: '' })
  const [error, setError] = useState(null)
  const [busy, setBusy] = useState(false)

  if (isAuthenticated) return <Navigate to={location.state?.from ?? '/mi-cuenta'} replace />

  const set = (name) => (e) => setForm((f) => ({ ...f, [name]: e.target.value }))

  const submit = async (e) => {
    e.preventDefault()
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
      navigate(location.state?.from ?? '/mi-cuenta', { replace: true })
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  return (
    <AuthShell
      eyebrow="Mi cuenta"
      title="Bienvenido"
      accent="de nuevo"
      benefits={[
        { icon: ClipboardList, text: 'Consulta el estado de tus pedidos y cotizaciones.' },
        { icon: FileText, text: 'Envía tu carrito y recibe tu cotización valorada.' },
      ]}
    >
      <h2>Ingresar</h2>
      <p className="page-lead">Accede a tu cuenta para enviar solicitudes y ver tus pedidos.</p>

      {location.state?.notice && <p className="alert alert--warning" role="status">{location.state.notice}</p>}

      <form className="form" onSubmit={submit} noValidate>
        {error && <p className="alert alert--error" role="alert">{error.message}</p>}
        <Field label="Correo electrónico" type="email" autoComplete="email" value={form.email} onChange={set('email')} error={error?.fieldError?.('email')} required />
        <Field label="Contraseña" type="password" autoComplete="current-password" value={form.password} onChange={set('password')} error={error?.fieldError?.('password')} required />
        <button type="submit" className="btn btn--accent btn--lg btn--block" disabled={busy}>
          {busy ? 'Ingresando…' : 'Ingresar'}
        </button>
      </form>

      <p className="auth__foot">
        ¿Aún no tienes cuenta? <Link to="/crear-cuenta" state={location.state}>Crea tu cuenta</Link>
      </p>
    </AuthShell>
  )
}
