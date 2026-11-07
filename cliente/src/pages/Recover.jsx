import { useState } from 'react'
import { Link, Navigate, useLocation } from 'react-router-dom'
import { CheckCircle2, CircleAlert } from 'lucide-react'
import { authApi } from '../api'
import { useAuth } from '../context/AuthContext'
import Field from '../components/ui/Field'
import AuthLayout from '../components/auth/AuthLayout'
import AuthCard from '../components/auth/AuthCard'
import '../styles/pages.css'

const emailOk = (v) => /^\S+@\S+\.\S+$/.test(v.trim())

// Paso 1 de recuperar la contraseña: pide el correo. La respuesta es siempre la misma, exista o no la cuenta.
export default function Recover() {
  const { isAuthenticated } = useAuth()
  const location = useLocation()
  const [email, setEmail] = useState(location.state?.email ?? '')
  const [touched, setTouched] = useState(false)
  const [busy, setBusy] = useState(false)
  const [sent, setSent] = useState(false)
  const [error, setError] = useState(null)

  if (isAuthenticated) return <Navigate to="/mi-cuenta" replace />

  const local = !email.trim() ? 'Escribe tu correo.' : !emailOk(email) ? 'Escribe un correo válido.' : null

  const submit = async (e) => {
    e.preventDefault()
    setTouched(true)
    if (local) return
    setBusy(true)
    setError(null)
    try {
      await authApi.forgotPassword(email.trim())
      setSent(true)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  return (
    <AuthLayout title={<>Recupera tu <em>contraseña</em></>}>
      <AuthCard>
        {sent ? (
          <div role="status">
            <p className="alert alert--success"><CheckCircle2 size={18} aria-hidden="true" /> <span>Si el correo existe, te enviamos un enlace. Revisa tu bandeja (y el spam): vence en 60 minutos y sirve una sola vez.</span></p>
            <p className="auth__foot"><Link to="/ingresar">Volver a ingresar</Link></p>
          </div>
        ) : (
          <>
            <p className="auth-card__lead">Escribe el correo de tu cuenta y te enviamos un enlace para crear una contraseña nueva.</p>
            <form className="form auth-form" onSubmit={submit} noValidate>
              {error && <p className="alert alert--error" role="alert"><CircleAlert size={18} aria-hidden="true" /> <span>{error.message}</span></p>}
              <Field label="Correo electrónico" type="email" autoComplete="email" value={email} onChange={(e) => setEmail(e.target.value)} onBlur={() => setTouched(true)} error={touched ? local : null} required />
              <button type="submit" className="btn btn--accent btn--lg btn--block" disabled={busy}>{busy ? 'Enviando…' : 'Enviar enlace'}</button>
            </form>
            <p className="auth__foot"><Link to="/ingresar">Volver a ingresar</Link></p>
          </>
        )}
      </AuthCard>
    </AuthLayout>
  )
}
