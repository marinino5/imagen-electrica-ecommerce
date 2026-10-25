import { useEffect, useState } from 'react'
import { Link, NavLink, useLocation } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { FileText, Menu, Phone, Search, ShoppingCart, User, X, MessageCircle, Truck } from 'lucide-react'
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

export default function Header() {
  const { pathname } = useLocation()
  const { isAuthenticated } = useAuth()
  const { purchaseCount, quoteCount } = useCart()
  const [scrolled, setScrolled] = useState(false)
  const [open, setOpen] = useState(false)

  const isHome = pathname === '/'
  // Transparente solo sobre la portada y arriba del todo; en el resto de páginas, violeta sólido
  const solid = !isHome || scrolled || open

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40)
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

  const accountLink = isAuthenticated ? { to: '/mi-cuenta', label: 'Mi cuenta' } : { to: '/crear-cuenta', label: 'Crea tu cuenta' }

  return (
    <header className={`site-header ${scrolled ? 'is-scrolled' : ''}`}>
      <div className="topbar">
        <div className="container topbar__inner">
          <a href={`tel:${site.phone.replace(/\s/g, '')}`}>
            <Phone size={14} aria-hidden="true" /> {site.phone}
          </a>
          <a href={site.whatsappUrl} target="_blank" rel="noreferrer" className="topbar__hide-sm">
            <MessageCircle size={14} aria-hidden="true" /> WhatsApp
          </a>
          <span className="topbar__right">
            <Truck size={14} aria-hidden="true" /> Envíos a todo el país
          </span>
        </div>
      </div>

      <div className={`mainbar ${solid ? 'mainbar--solid' : ''}`}>
        <div className="container mainbar__inner">
          <button type="button" className="icon-btn mainbar__burger" onClick={() => setOpen(true)} aria-label="Abrir menú" aria-expanded={open}>
            <Menu size={26} />
          </button>

          <Link to="/" className="mainbar__logo" aria-label="Imagen Eléctrica, ir al inicio">
            <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" height="44" />
          </Link>

          <nav className="mainbar__nav" aria-label="Principal">
            {mainMenu.map((item) => (
              <NavLink key={item.to} to={item.to} end={item.end}>
                {item.label}
              </NavLink>
            ))}
          </nav>

          <div className="mainbar__actions">
            <Link to="/productos?buscar=1" className="icon-btn" aria-label="Buscar productos">
              <Search size={22} />
            </Link>
            <Link to={accountLink.to} className="mainbar__account">
              <User size={20} aria-hidden="true" />
              <span>{accountLink.label}</span>
            </Link>
            <Link to="/carrito#cotizacion" className="icon-btn" aria-label={`Cotización: ${quoteCount} productos`}>
              <FileText size={22} />
              <CountBadge count={quoteCount} />
            </Link>
            <Link to="/carrito" className="icon-btn" aria-label={`Carrito: ${purchaseCount} productos`}>
              <ShoppingCart size={22} />
              <CountBadge count={purchaseCount} />
            </Link>
          </div>
        </div>
      </div>

      <AnimatePresence>
        {open && (
          <>
            <motion.div
              className="drawer-backdrop"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setOpen(false)}
            />
            <motion.aside
              className="drawer"
              role="dialog"
              aria-modal="true"
              aria-label="Menú"
              initial={{ x: '-100%' }}
              animate={{ x: 0 }}
              exit={{ x: '-100%' }}
              transition={{ type: 'tween', duration: 0.28, ease: [0.22, 1, 0.36, 1] }}
            >
              <div className="drawer__head">
                <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" height="40" />
                <button type="button" className="icon-btn" onClick={() => setOpen(false)} aria-label="Cerrar menú">
                  <X size={26} />
                </button>
              </div>
              <nav className="drawer__nav" aria-label="Menú móvil">
                {mainMenu.map((item) => (
                  <NavLink key={item.to} to={item.to} end={item.end}>
                    {item.label}
                  </NavLink>
                ))}
                <NavLink to={accountLink.to}>{accountLink.label}</NavLink>
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
          </>
        )}
      </AnimatePresence>
    </header>
  )
}
