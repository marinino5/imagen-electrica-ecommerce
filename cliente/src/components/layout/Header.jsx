import { useEffect, useState } from 'react'
import { Link, NavLink, useLocation, useNavigate } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { FileText, Menu, MessageCircle, Search, ShoppingCart, User, X } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import { useCart } from '../../context/CartContext'
import { mainMenu, site } from '../../config/site'
import '../../styles/header.css'

function CountBadge({ count }) {
  if (!count) return null
  return (
    <motion.span
      key={count}
      className="count-badge"
      initial={{ scale: 0.4 }}
      animate={{ scale: 1 }}
      transition={{ type: 'spring', stiffness: 500, damping: 14 }}
    >
      {count > 99 ? '99+' : count}
    </motion.span>
  )
}

// Buscador del encabezado: lleva al catálogo con ?q=
function HeaderSearch({ className = '', onDone }) {
  const navigate = useNavigate()
  const [term, setTerm] = useState('')

  const submit = (e) => {
    e.preventDefault()
    const q = term.trim()
    if (!q) return
    navigate(`/productos?q=${encodeURIComponent(q)}`)
    setTerm('')
    onDone?.()
  }

  return (
    <form role="search" className={`header-search ${className}`} onSubmit={submit}>
      <input
        type="search"
        aria-label="Buscar productos"
        placeholder="Buscar"
        value={term}
        onChange={(e) => setTerm(e.target.value)}
        autoComplete="off"
      />
      <button type="submit" aria-label="Buscar">
        <Search size={18} />
      </button>
    </form>
  )
}

export default function Header() {
  const { pathname } = useLocation()
  const { isAuthenticated } = useAuth()
  const { purchaseCount, quoteCount } = useCart()
  const [scrolled, setScrolled] = useState(false)
  const [open, setOpen] = useState(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24)
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => setOpen(false), [pathname])

  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : ''
    return () => {
      document.body.style.overflow = ''
    }
  }, [open])

  const account = isAuthenticated ? { to: '/mi-cuenta', label: 'Mi cuenta' } : { to: '/crear-cuenta', label: 'Crea tu cuenta' }

  return (
    <header className="site-header">
      {/* Isla flotante: igual sobre el hero y al hacer scroll; al bajar solo gana sombra */}
      <div className={`island ${scrolled ? 'is-scrolled' : ''}`}>
        <Link to="/" className="island__logo" aria-label="Imagen Eléctrica, ir al inicio">
          <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" width="40" height="44" />
        </Link>

        <nav className="island__nav" aria-label="Principal">
          {mainMenu.map((item) => (
            <NavLink key={item.to} to={item.to} end={item.end}>
              {item.label}
            </NavLink>
          ))}
        </nav>

        <div className="island__actions">
          <HeaderSearch className="island__search" />
          <Link to={account.to} className="island__account">
            <User size={18} aria-hidden="true" />
            <span>{account.label}</span>
          </Link>
          <Link to="/carrito#cotizacion" className="icon-btn island__quote" data-fly-target="quote" aria-label={`Cotización: ${quoteCount} productos`}>
            <FileText size={22} />
            <CountBadge count={quoteCount} />
          </Link>
          <Link to="/carrito" className="icon-btn" data-fly-target="cart" aria-label={`Carrito: ${purchaseCount} productos`}>
            <ShoppingCart size={22} />
            <CountBadge count={purchaseCount} />
          </Link>
          <button type="button" className="icon-btn island__burger" onClick={() => setOpen(true)} aria-label="Abrir menú" aria-expanded={open}>
            <Menu size={26} />
          </button>
        </div>
      </div>

      <AnimatePresence>
        {open && (
          <motion.aside
            className="drawer"
            role="dialog"
            aria-modal="true"
            aria-label="Menú"
            initial={{ opacity: 0, y: -16 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -16 }}
            transition={{ duration: 0.25, ease: [0.22, 1, 0.36, 1] }}
          >
            <div className="drawer__head">
              <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" width="40" height="44" />
              <button type="button" className="icon-btn" onClick={() => setOpen(false)} aria-label="Cerrar menú">
                <X size={26} />
              </button>
            </div>

            <HeaderSearch className="drawer__search" onDone={() => setOpen(false)} />

            <nav className="drawer__nav" aria-label="Menú móvil">
              {mainMenu.map((item) => (
                <NavLink key={item.to} to={item.to} end={item.end}>
                  {item.label}
                </NavLink>
              ))}
              <NavLink to={account.to}>{account.label}</NavLink>
              <NavLink to="/carrito">
                Carrito {purchaseCount > 0 && <span className="drawer__count">{purchaseCount}</span>}
              </NavLink>
              <Link to="/carrito#cotizacion">
                Cotización {quoteCount > 0 && <span className="drawer__count">{quoteCount}</span>}
              </Link>
            </nav>

            <a className="btn btn--accent btn--block" href={site.whatsappUrl} target="_blank" rel="noreferrer">
              <MessageCircle size={18} aria-hidden="true" /> Escríbenos por WhatsApp
            </a>
          </motion.aside>
        )}
      </AnimatePresence>
    </header>
  )
}
