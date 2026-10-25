import { Navigate, useLocation } from 'react-router-dom'
import { Loader2 } from 'lucide-react'
import { useAuth } from '../context/AuthContext'

// Protege las páginas que necesitan sesión de cliente
export default function RequireAuth({ children }) {
  const { status } = useAuth()
  const location = useLocation()

  if (status === 'loading') {
    return (
      <div className="state" role="status">
        <Loader2 size={32} className="searchbox__spin" aria-hidden="true" />
        <p>Cargando tu cuenta…</p>
      </div>
    )
  }
  if (status !== 'authenticated') {
    return <Navigate to="/ingresar" replace state={{ from: location.pathname }} />
  }
  return children
}
