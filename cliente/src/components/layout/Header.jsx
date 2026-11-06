import { useEffect, useState } from 'react'
import { Link, NavLink, useLocation, useNavigate } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { Menu, MessageCircle, Phone, Search, ShoppingCart, User, X } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import { useCart } from '../../context/CartContext'
import { useFamilies } from '../../hooks/useFamilies'
import { mainMenu, site } from '../../config/site'
import '../../styles/header.css'

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
  const { lines } = useCart()
  const { data: families } = useFamilies()
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

  const account = isAuthenticated ? { to: '/mi-cuenta', label: 'Mi cuenta' } : { to: '/ingresar', label: 'Ingresar' }
  const count = lines.length

  return (
    <header className="site-header">
      {/* Isla flotante tipo píldora, separada 12 px del borde */}
      <div className="navw">
        <nav className={`nav ${scrolled ? 'is-scrolled' : ''}`} aria-label="Principal">
          <Link to="/" className="nav__logo" aria-label="Imagen Eléctrica, ir al inicio">
            <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" width="40" height="44" />
          </Link>

          <div className="nav__links">
            {mainMenu.map((item) => (
              <NavLink key={item.to} to={item.to} end={item.end}>
                {item.label}
              </NavLink>
            ))}
          </div>

          <Link to="/productos?buscar=1" className="nav__icon" aria-label="Buscar productos">
            <Search size={20} />
          </Link>
          <Link to={account.to} className="nav__icon nav__account" aria-label={account.label}>
            <User size={20} />
          </Link>
          <Link to="/carrito" className="nav__cart" data-fly-target="cart" aria-label={`Carrito: ${count} ${count === 1 ? 'producto' : 'productos'}`}>
            <span>Carrito</span>
            <i>{count > 99 ? '99+' : count}</i>
          </Link>
          <button type="button" className="nav__icon nav__burger" onClick={() => setOpen(true)} aria-label="Abrir menú" aria-expanded={open}>
            <Menu size={22} />
          </button>
        </nav>
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
            transition={{ duration: 0.4, ease: [0.32, 0.72, 0, 1] }}
          >
            <div className="drawer__head">
              <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" width="40" height="44" />
              <button type="button" className="icon-btn" onClick={() => setOpen(false)} aria-label="Cerrar menú">
                <X size={26} />
              </button>
            </div>

            <HeaderSearch className="drawer__search" onDone={() => setOpen(false)} />

            {families.length > 0 && (
              <section aria-labelledby="drawer-cats">
                <h2 id="drawer-cats" className="drawer__title">Líneas</h2>
                <ul className="drawer__cats">
                  {families.map((f) => (
                    <li key={f.id}><Link to={`/productos?familia=${f.slug}`}>{f.name}</Link></li>
                  ))}
                </ul>
              </section>
            )}

            <nav className="drawer__nav" aria-label="Menú móvil">
              {mainMenu.map((item) => (
                <NavLink key={item.to} to={item.to} end={item.end}>
                  {item.label}
                </NavLink>
              ))}
              <NavLink to={account.to}>{account.label}</NavLink>
              <NavLink to="/carrito">
                Carrito {count > 0 && <span className="drawer__count">{count}</span>}
              </NavLink>
            </nav>

            <div className="drawer__contact">
              <a className="btn btn--accent btn--block btn--lg" href={site.whatsappUrl} target="_blank" rel="noreferrer">
                <MessageCircle size={18} aria-hidden="true" /> Escríbenos por WhatsApp
              </a>
              <a className="btn btn--ghost-light btn--block" href={site.mainContact.href}>
                <Phone size={18} aria-hidden="true" /> Llámanos: {site.mainContact.label}
              </a>
              <a className="drawer__mail" href={`mailto:${site.email}`}>{site.email}</a>
            </div>
          </motion.aside>
        )}
      </AnimatePresence>
    </header>
  )
}
