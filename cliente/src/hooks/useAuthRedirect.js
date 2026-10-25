import { useLocation, useNavigate } from 'react-router-dom'
import { useToast } from '../context/ToastContext'

// Al terminar de ingresar o crear la cuenta, vuelve a la página de origen (por ejemplo /carrito, con los
// productos intactos porque el carrito vive en localStorage) y da la bienvenida con un aviso.
export function useAuthRedirect() {
  const location = useLocation()
  const navigate = useNavigate()
  const { notify } = useToast()

  const raw = location.state?.from
  // Solo rutas internas: evita redirigir a otro sitio
  const from = typeof raw === 'string' && raw.startsWith('/') && !raw.startsWith('//') ? raw : null
  const target = from ?? '/mi-cuenta'

  const finish = (user) => {
    const first = user?.name?.trim().split(' ')[0]
    const hello = first ? `Te damos la bienvenida, ${first}.` : 'Te damos la bienvenida.'
    notify(from === '/carrito' ? `${hello} Tu carrito sigue intacto.` : hello)
    navigate(target, { replace: true })
  }

  return { target, fromCart: from === '/carrito', finish }
}
