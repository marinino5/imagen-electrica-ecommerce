import { useEffect, useLayoutEffect } from 'react'
import { Outlet, useLocation, useNavigationType } from 'react-router-dom'
import Header from './Header'
import Footer from './Footer'
import WhatsAppButton from './WhatsAppButton'
import BottomNav, { barFor } from './BottomNav'

// Posición de scroll por entrada del historial. El navegador no la restaura bien porque las páginas se descargan
// después (lazy): al volver, la altura aún es corta. Aquí se guarda y se reintenta hasta que la página tenga altura.
// La primera entrada de cada pestaña siempre se llama «default»: por eso la clave incluye la ruta
const posKey = (path, key) => `ie-scroll:${path}:${key}`
const readPos = (path, key) => {
  try { return Number(sessionStorage.getItem(posKey(path, key))) || 0 } catch { return 0 }
}

// Nueva ruta → arriba (o al ancla #); botón atrás/adelante o recarga → vuelve a donde estaba
function ScrollManager() {
  const { pathname, hash, key } = useLocation()
  const navType = useNavigationType()

  useEffect(() => {
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual'
  }, [])

  // Guarda la posición mientras se desplaza
  useEffect(() => {
    let t = 0
    const save = () => {
      t = 0
      if (window.__ieRestoring) return // la página aún no tiene altura: guardar 0 borraría la posición pendiente
      try { sessionStorage.setItem(posKey(pathname, key), String(Math.round(window.scrollY))) } catch { /* sin almacenamiento */ }
    }
    const onScroll = () => { if (!t) t = window.setTimeout(save, 120) }
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => {
      window.removeEventListener('scroll', onScroll)
      if (t) clearTimeout(t)
      save()
    }
  }, [pathname, key])

  // useLayoutEffect: corre antes que los efectos de las páginas, que así ya saben si hay una posición por restaurar
  useLayoutEffect(() => {
    window.__ieRestoring = false
    if (hash) {
      document.getElementById(hash.slice(1))?.scrollIntoView()
      return
    }
    // Recarga: la primera ruta llega como POP con la clave de historial guardada
    const saved = navType === 'POP' ? readPos(pathname, key) : 0
    if (!saved) {
      window.scrollTo(0, 0)
      return
    }
    let tries = 0
    let id = 0
    window.__ieRestoring = true // las entradas animadas de las portadas aparecen ya: la persona no está arriba
    const go = () => {
      window.scrollTo(0, saved)
      const reached = Math.abs(window.scrollY - saved) < 4
      if (!reached && tries++ < 25) id = window.setTimeout(go, 80)
      else window.setTimeout(() => { window.__ieRestoring = false }, 400)
    }
    go()
    return () => clearTimeout(id)
  }, [pathname, hash, key, navType])
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
      <Footer />
      <BottomNav />
      <WhatsAppButton />
    </>
  )
}
