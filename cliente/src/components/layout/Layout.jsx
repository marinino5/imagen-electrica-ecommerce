import { useEffect } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import Header from './Header'
import Footer from './Footer'
import QuoteBand from './QuoteBand'
import WhatsAppButton from './WhatsAppButton'
import BottomNav, { barFor } from './BottomNav'

// Al cambiar de ruta se vuelve arriba (o al ancla #, si la hay)
function ScrollManager() {
  const { pathname, hash } = useLocation()
  useEffect(() => {
    if (hash) {
      document.getElementById(hash.slice(1))?.scrollIntoView()
      return
    }
    window.scrollTo(0, 0)
  }, [pathname, hash])
  return null
}

export default function Layout() {
  const { pathname } = useLocation()
  // La portada del inicio pasa por debajo del encabezado; las demás páginas empiezan debajo
  const offset = pathname === '/' ? '' : 'main--offset'

  // La barra fija de abajo (celular) empuja el contenido y el botón de WhatsApp
  useEffect(() => {
    document.documentElement.dataset.bar = barFor(pathname)
  }, [pathname])

  return (
    <>
      <a className="skip-link" href="#contenido">Saltar al contenido</a>
      <ScrollManager />
      <Header />
      <main id="contenido" className={offset}>
        <Outlet />
      </main>
      <QuoteBand />
      <Footer />
      <BottomNav />
      <WhatsAppButton />
    </>
  )
}
