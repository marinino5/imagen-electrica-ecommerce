import { useState } from 'react'
import { Link, Navigate, useNavigate, useSearchParams } from 'react-router-dom'
import { CircleAlert } from 'lucide-react'
import { authApi } from '../api'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'
import AuthLayout from '../components/auth/AuthLayout'
import AuthCard from '../components/auth/AuthCard'
import PasswordField from '../components/auth/PasswordField'
import '../styles/pages.css'

// Paso 2 de recuperar la contraseña: llega desde el enlace del correo (/restablecer?token=…&email=…). Pide la contraseña
// nueva con confirmación y, si sale bien, inicia sesión sola.
export default function Reset() {
  const { login, isAuthenticated } = useAuth()
  const { notify } = useToast()
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const token = params.get('token') ?? ''
  const email = params.get('email') ?? ''
  const [form, setForm] = useState({ password: '', confirm: '' })
  const [touched, setTouched] = useState({})
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)

  if (isAuthenticated) return <Navigate to="/mi-cuenta" replace />

  const local = {
    password: !form.password ? 'Escribe la contraseña nueva.' : form.password.length < 8 ? 'La contraseña debe tener al menos 8 caracteres.' : form.password.length > 100 ? 'Usa máximo 100 caracteres.' : null,
    confirm: form.confirm !== form.password ? 'Las contraseñas no coinciden.' : null,
  }
  const shown = (name) => (touched[name] ? local[name] : null) ?? (name === 'password' ? error?.fieldError?.('password') : null)
  const linkBad = !token || !email || error?.fieldError?.('token')

  const submit = async (e) => {
    e.preventDefault()
    setTouched({ password: true, confirm: true })
    if (local.password || local.confirm) return
    setBusy(true)
    setError(null)
    let changed = false
    try {
      await authApi.resetPassword({ token, email, password: form.password, password_confirmation: form.confirm })
      changed = true
      const user = await login(email, form.password)
      notify(`Cambiaste tu contraseña${user?.name ? `, ${user.name.trim().split(' ')[0]}` : ''}. Ya ingresaste.`)
      navigate('/mi-cuenta', { replace: true })
    } catch (err) {
      // Si la contraseña ya se cambió y solo falló el ingreso automático (red, 401…), el enlace ya se gastó: la persona entra a mano
      if (changed) navigate('/ingresar', { replace: true, state: { notice: 'Cambiaste tu contraseña. Ingresa con la nueva.' } })
      else setError(err)
    } finally {
      setBusy(false)
    }
  }

  return (
    <AuthLayout title={<>Crea una <em>contraseña nueva</em></>}>
      <AuthCard>
        {linkBad ? (
          <div role="alert">
            <p className="alert alert--error"><CircleAlert size={18} aria-hidden="true" /> <span>{error?.fieldError?.('token') ?? 'El enlace no es válido.'} <Link to="/recuperar" state={{ email }}>Pedir un enlace nuevo</Link></span></p>
          </div>
        ) : (
          <>
            <p className="auth-card__lead">Para la cuenta <strong>{email}</strong>. Usa 8 o más caracteres.</p>
            <form className="form auth-form" onSubmit={submit} noValidate>
              {error && !error.fieldError?.('password') && <p className="alert alert--error" role="alert"><CircleAlert size={18} aria-hidden="true" /> <span>{error.message}</span></p>}
              <PasswordField label="Contraseña nueva" value={form.password} onChange={(e) => setForm((f) => ({ ...f, password: e.target.value }))} onBlur={() => setTouched((t) => ({ ...t, password: true }))} error={shown('password')} autoComplete="new-password" meter />
              <PasswordField label="Repite la contraseña" value={form.confirm} onChange={(e) => setForm((f) => ({ ...f, confirm: e.target.value }))} onBlur={() => setTouched((t) => ({ ...t, confirm: true }))} error={shown('confirm')} autoComplete="new-password" />
              <button type="submit" className="btn btn--accent btn--lg btn--block" disabled={busy}>{busy ? 'Guardando…' : 'Cambiar contraseña'}</button>
            </form>
          </>
        )}
      </AuthCard>
    </AuthLayout>
  )
}
