import { NavLink, useLocation } from 'react-router-dom'
import { Home, LayoutGrid, Search, ShoppingCart, User } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import { useCart } from '../../context/CartContext'
import '../../styles/bottom-nav.css'

// Barra inferior del celular: Inicio, Productos, Buscar, Carrito y Cuenta.
// No se muestra donde ya hay una barra de acción fija (ficha de producto y carrito).
export default function BottomNav() {
  const { isAuthenticated } = useAuth()
  const { lines } = useCart()
  const { pathname } = useLocation()
  if (barFor(pathname) !== 'nav') return null

  const items = [
    { to: '/', label: 'Inicio', Icon: Home, end: true },
    { to: '/productos', label: 'Productos', Icon: LayoutGrid, end: true },
    { to: '/productos?buscar=1', label: 'Buscar', Icon: Search, search: true },
    { to: '/carrito', label: 'Carrito', Icon: ShoppingCart, count: lines.length },
    { to: isAuthenticated ? '/mi-cuenta' : '/ingresar', label: 'Cuenta', Icon: User },
  ]

  return (
    <nav className="bottom-nav" aria-label="Navegación rápida">
      {items.map(({ to, label, Icon, end, count, search }) => (
        <NavLink
          key={label}
          to={to}
          end={end}
          className={({ isActive }) => (isActive && !search ? 'is-active' : '')}
          data-fly-target={label === 'Carrito' ? 'cart-mobile' : undefined}
        >
          <span className="bottom-nav__icon">
            <Icon size={22} aria-hidden="true" />
            {count > 0 && <b className="count-badge" aria-label={`${count} en el carrito`}>{count > 99 ? '99+' : count}</b>}
          </span>
          {label}
        </NavLink>
      ))}
    </nav>
  )
}

// Qué barra fija ocupa la parte baja del celular en cada ruta: 'nav', 'action' o 'none'
export function barFor(pathname) {
  if (pathname.startsWith('/productos/') || pathname === '/carrito') return 'action'
  return 'nav'
}
