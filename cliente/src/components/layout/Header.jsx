import { useEffect, useRef, useState } from 'react'
import { Link, NavLink, useLocation, useNavigate } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { ChevronDown, Menu, MessageCircle, Phone, Search, User, X } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import { useCart } from '../../context/CartContext'
import { useFamilies } from '../../hooks/useFamilies'
import { familyPhoto } from '../../lib/families'
import { mainMenu, site } from '../../config/site'
import '../../styles/header.css'

// Buscador del cajón móvil: lleva al catálogo con ?q=
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
      <input type="search" aria-label="Buscar productos" placeholder="Buscar" value={term} onChange={(e) => setTerm(e.target.value)} autoComplete="off" />
      <button type="submit" aria-label="Buscar"><Search size={18} /></button>
    </form>
  )
}

// Mega-menú: las 8 familias del portafolio en dos filas de cuatro. Cada una lleva su miniatura (formato único de la
// tienda) y una lista vertical limpia de categorías, una por renglón: nada de categorías que se juntan en la misma línea.
function Mega({ families, onClose }) {
  return (
    <div className="mega" role="region" aria-label="Familias de productos">
      <ul className="mega__grid">
        {families.map((f) => {
          const photo = familyPhoto(f.slug)
          return (
            <li key={f.id} className="mega__fam">
              <Link className="mega__thumb" to={`/productos?familia=${f.slug}`} onClick={onClose} tabIndex={-1} aria-hidden="true">
                {photo && <img src={photo.sm} alt="" width="72" height="72" loading="lazy" />}
              </Link>
              <div className="mega__col">
                <Link className="mega__name" to={`/productos?familia=${f.slug}`} onClick={onClose}>{f.name}</Link>
                <ul className="mega__cats">
                  {(f.categories ?? []).map((c) => (
                    <li key={c.id}><Link to={`/productos?familia=${f.slug}&categoria=${c.slug}`} onClick={onClose}>{c.name}</Link></li>
                  ))}
                </ul>
              </div>
            </li>
          )
        })}
      </ul>
      <p className="mega__foot"><span>{families.length} familias de producto</span><Link to="/productos" onClick={onClose}>Ver todo el catálogo →</Link></p>
    </div>
  )
}

export default function Header() {
  const { pathname } = useLocation()
  const { isAuthenticated } = useAuth()
  const { lines } = useCart()
  const { data: families } = useFamilies()
  const [scrolled, setScrolled] = useState(false)
  const [open, setOpen] = useState(false)
  const [mega, setMega] = useState(false)
  const closeTimer = useRef(null)

  const isHome = pathname === '/'
  const solid = scrolled || !isHome || mega

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24)
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    setOpen(false)
    setMega(false)
  }, [pathname])

  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : ''
    return () => {
      document.body.style.overflow = ''
    }
  }, [open])

  useEffect(() => {
    if (!mega) return
    const onKey = (e) => e.key === 'Escape' && setMega(false)
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [mega])

  const openMega = () => {
    clearTimeout(closeTimer.current)
    setMega(true)
  }
  const closeMega = () => {
    clearTimeout(closeTimer.current)
    closeTimer.current = setTimeout(() => setMega(false), 140)
  }

  const account = isAuthenticated ? { to: '/mi-cuenta', label: 'Mi cuenta' } : { to: '/ingresar', label: 'Ingresar' }
  const count = lines.length
  const isActive = (item) => (item.end ? pathname === item.to : pathname.startsWith(item.to))

  return (
    <header className={`site-header ${solid ? 'is-solid' : ''} ${scrolled ? 'is-scrolled' : ''}`}>
      <nav className="nav" aria-label="Principal">
        <Link to="/" className="nav__logo" aria-label="Imagen Eléctrica, ir al inicio">
          <img src={solid ? '/img/logo-ajustado.png' : '/img/logo-blanco-ajustado.png'} alt="Imagen Eléctrica" width="53" height="64" />
        </Link>

        <div className="nav__links">
          {mainMenu.map((item) => {
            const active = isActive(item)
            const bar = active && <motion.span layoutId="nav-bar" className="nav__bar" transition={{ type: 'spring', stiffness: 500, damping: 40 }} />
            if (item.to === '/productos') {
              return (
                <div key={item.to} className={`nav__item ${mega ? 'is-open' : ''}`} onMouseEnter={openMega} onMouseLeave={closeMega} onBlur={(e) => !e.currentTarget.contains(e.relatedTarget) && closeMega()} onFocus={openMega}>
                  <NavLink to={item.to} className="nav__link" aria-haspopup="true" aria-expanded={mega}>
                    {item.label}
                    <ChevronDown className="nav__chev" size={16} aria-hidden="true" />
                    {bar}
                  </NavLink>
                  {mega && families.length > 0 && <Mega families={families} onClose={() => setMega(false)} />}
                </div>
              )
            }
            return (
              <NavLink key={item.to} to={item.to} end={item.end} className="nav__link">
                {item.label}
                {bar}
              </NavLink>
            )
          })}
        </div>

        <Link to="/productos?buscar=1" className="nav__icon" aria-label="Buscar productos"><Search size={22} /></Link>
        <Link to={account.to} className="nav__icon nav__account" aria-label={account.label}><User size={22} /></Link>
        <Link to="/cotizar" className="nav__quote">Cotizar</Link>
        <Link to="/carrito" className="nav__cart" data-fly-target="cart" aria-label={`Carrito: ${count} ${count === 1 ? 'producto' : 'productos'}`}>
          <span>Carrito</span>
          <i>{count > 99 ? '99+' : count}</i>
        </Link>
        <button type="button" className="nav__icon nav__burger" onClick={() => setOpen(true)} aria-label="Abrir menú" aria-expanded={open}>
          <Menu size={24} />
        </button>
      </nav>

      <AnimatePresence>
        {open && (
          <motion.aside className="drawer" role="dialog" aria-modal="true" aria-label="Menú" initial={{ opacity: 0, y: -16 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -16 }} transition={{ duration: 0.4, ease: [0.32, 0.72, 0, 1] }}>
            <div className="drawer__head">
              <img src="/img/logo-blanco-ajustado.png" alt="Imagen Eléctrica" width="36" height="44" />
              <button type="button" className="icon-btn" onClick={() => setOpen(false)} aria-label="Cerrar menú"><X size={26} /></button>
            </div>
            <HeaderSearch className="drawer__search" onDone={() => setOpen(false)} />
            {families.length > 0 && (
              <section aria-labelledby="drawer-cats">
                <h2 id="drawer-cats" className="drawer__title">Líneas</h2>
                <ul className="drawer__cats">
                  {families.map((f) => <li key={f.id}><Link to={`/productos?familia=${f.slug}`}>{f.name}</Link></li>)}
                </ul>
              </section>
            )}
            <nav className="drawer__nav" aria-label="Menú móvil">
              {mainMenu.map((item) => <NavLink key={item.to} to={item.to} end={item.end}>{item.label}</NavLink>)}
              <NavLink to={account.to}>{account.label}</NavLink>
              <NavLink to="/cotizar">Cotizar</NavLink>
              <NavLink to="/carrito">Carrito {count > 0 && <span className="drawer__count">{count}</span>}</NavLink>
            </nav>
            <div className="drawer__contact">
              <a className="btn btn--accent btn--block btn--lg" href={site.whatsappUrl} target="_blank" rel="noreferrer"><MessageCircle size={18} aria-hidden="true" /> Escríbenos por WhatsApp</a>
              <a className="btn btn--ghost-light btn--block" href={site.mainContact.href}><Phone size={18} aria-hidden="true" /> Llámanos: {site.mainContact.label}</a>
              <a className="drawer__mail" href={`mailto:${site.email}`}>{site.email}</a>
            </div>
          </motion.aside>
        )}
      </AnimatePresence>
    </header>
  )
}
