import { useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowUpRight, FileText, Headset, PackageSearch, ShieldCheck } from 'lucide-react'
import BusinessCard from '../components/contacto/BusinessCard'
import SmartForm from '../components/contacto/SmartForm'
import StoreCard from '../components/contacto/StoreCard'
import Bgfx from '../components/ui/Bgfx'
import Hl from '../components/ui/Hl'
import Mix from '../components/ui/Mix'
import Pop from '../components/ui/Pop'
import { site } from '../config/site'
import { useAuth } from '../context/AuthContext'
import '../styles/contacto.css'
import '../styles/ie-contacto.css'

// Contacto como «centro de atención»: hero oscuro, cuatro mosaicos para elegir qué se necesita, la tarjeta de visita con los
// canales, el formulario (solo al elegir «Estado de mi pedido» sin sesión o «Garantías y devoluciones») y la dirección.
// Entrada propia de la página: fundido con escala 0,98 → 1.
export default function Contacto() {
  const { isAuthenticated } = useAuth()
  const [open, setOpen] = useState(null)
  const [last, setLast] = useState(null) // se conserva mientras el formulario se cierra
  const formRef = useRef(null)

  const toggle = (id) => {
    if (open === id) return setOpen(null)
    setOpen(id)
    setLast(id)
    setTimeout(() => formRef.current?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 320)
  }

  const tiles = [
    { id: 'quote', icon: FileText, title: 'Cotizar un proyecto', text: 'Envía tu lista y te respondemos con precio.', to: '/cotizar' },
    { id: 'advice', icon: Headset, title: 'Asesoría técnica', text: 'Habla con un asesor por WhatsApp.', href: site.whatsappLink('Hola, necesito asesoría técnica sobre…') },
    isAuthenticated
      ? { id: 'order', icon: PackageSearch, title: 'Estado de mi pedido', text: 'Míralo en Mi cuenta, con su línea de estados.', to: '/mi-cuenta' }
      : { id: 'order', icon: PackageSearch, title: 'Estado de mi pedido', text: 'Escribe tu número de pedido y te respondemos.' },
    { id: 'warranty', icon: ShieldCheck, title: 'Garantías y devoluciones', text: 'Cuéntanos qué pasó con tu producto.' },
  ]

  return (
    <>
      <section className="ct-hero" aria-labelledby="h-contacto">
        <div className="wrap ct-hero__grid">
          <Pop className="ct-hero__text">
            <h1 id="h-contacto" className="ie-h1"><Mix light="Hablemos de">tu <Hl>proyecto</Hl></Mix></h1>
            <p className="rd-lead">Escríbenos o llámanos y un asesor te responde con la referencia y el precio.</p>
          </Pop>
          <Pop delay={0.12} className="ct-hero__photo">
            <img src="/img/fotos/asesoria-tablero.webp" alt="Técnico con casco junto a un tablero eléctrico abierto, revisando las conexiones" width="1280" height="960" fetchPriority="high" loading="eager" />
          </Pop>
        </div>
      </section>

      <section className="ct-need bg-lila-fade" aria-labelledby="h-need">
        <Bgfx b a="tablero:r" />
        <div className="wrap">
          <Pop>
            <h2 id="h-need" className="rd-h2">¿Qué <Hl>necesitas?</Hl></h2>
            <ul className="ct-tiles">
              {tiles.map(({ id, icon: Icon, title, text, to, href }) => {
                const inner = (
                  <>
                    <span className="ct-tile__ic"><Icon size={40} strokeWidth={1.5} aria-hidden="true" /></span>
                    <strong>{title}</strong>
                    <span>{text}</span>
                    <ArrowUpRight className="ct-tile__go" size={22} aria-hidden="true" />
                  </>
                )
                return (
                  <li key={id}>
                    {to ? <Link to={to} className="ct-tile">{inner}</Link>
                      : href ? <a href={href} target="_blank" rel="noreferrer" className="ct-tile">{inner}</a>
                      : <button type="button" className={`ct-tile ${open === id ? 'is-open' : ''}`} aria-expanded={open === id} aria-controls="formulario" onClick={() => toggle(id)}>{inner}</button>}
                  </li>
                )
              })}
            </ul>
          </Pop>

          {/* El formulario solo aparece al elegir «Estado de mi pedido» (sin sesión) o «Garantías y devoluciones»; la altura se anima 300 ms */}
          <div className={`ct-reveal ${open ? 'is-open' : ''}`} id="formulario" ref={formRef} inert={!open}>
            <div className="ct-reveal__in">
              <div className="ct-panel" aria-labelledby="h-ayuda">
                {last && <SmartForm key={last} fixedTopic={last} />}
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="ct-card" aria-label="Formas de contactarnos">
        <Bgfx e />
        <div className="wrap"><Pop><BusinessCard /></Pop></div>
      </section>

      <section className="ct-where bg-lila-soft" aria-label="Dónde estamos">
        <Bgfx d c="l" arcTop="12%" />
        <div className="wrap"><Pop><StoreCard /></Pop></div>
      </section>
    </>
  )
}
