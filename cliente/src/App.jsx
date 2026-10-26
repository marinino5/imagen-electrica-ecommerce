import { lazy, Suspense } from 'react'
import { Route, Routes } from 'react-router-dom'
import { MotionConfig } from 'framer-motion'
import { ToastProvider } from './context/ToastContext'
import { AuthProvider } from './context/AuthContext'
import { CartProvider } from './context/CartContext'
import Layout from './components/layout/Layout'
import RequireAuth from './components/RequireAuth'
import Home from './pages/Home'
import NotFound from './pages/NotFound'

// El inicio carga de inmediato; el resto de páginas se descargan al visitarlas
const Products = lazy(() => import('./pages/Products'))
const Cart = lazy(() => import('./pages/Cart'))
const Login = lazy(() => import('./pages/Login'))
const Register = lazy(() => import('./pages/Register'))
const Account = lazy(() => import('./pages/Account'))
const ComoComprar = lazy(() => import('./pages/ComoComprar'))
const Nosotros = lazy(() => import('./pages/Nosotros'))
const Contacto = lazy(() => import('./pages/Contacto'))
const PoliticaDatos = lazy(() => import('./pages/PoliticaDatos'))

export default function App() {
  return (
    // reducedMotion="user": con prefers-reduced-motion, framer-motion omite los desplazamientos y zooms
    <MotionConfig reducedMotion="user">
      <ToastProvider>
        <AuthProvider>
          <CartProvider>
            <Suspense fallback={<div className="state" role="status">Cargando…</div>}>
            <Routes>
              <Route element={<Layout />}>
                <Route index element={<Home />} />
                <Route path="productos" element={<Products />} />
                <Route path="carrito" element={<Cart />} />
                <Route path="ingresar" element={<Login />} />
                <Route path="crear-cuenta" element={<Register />} />
                <Route path="mi-cuenta" element={<RequireAuth><Account /></RequireAuth>} />
                <Route path="como-comprar" element={<ComoComprar />} />
                <Route path="nosotros" element={<Nosotros />} />
                <Route path="contacto" element={<Contacto />} />
                <Route path="politica-datos" element={<PoliticaDatos />} />
                <Route path="*" element={<NotFound />} />
              </Route>
            </Routes>
            </Suspense>
          </CartProvider>
        </AuthProvider>
      </ToastProvider>
    </MotionConfig>
  )
}
