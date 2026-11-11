import { useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowUpRight, Copy, FileText, Headset, Mail, MapPin, MessageCircle, Navigation, PackageSearch, Phone, ShieldCheck } from 'lucide-react'
import CallPill from '../components/contacto/CallPill'
import SmartForm from '../components/contacto/SmartForm'
import Squares from '../components/ui/Squares'
import Hl from '../components/ui/Hl'
import Pill from '../components/ui/Pill'
import Pop from '../components/ui/Pop'
import Reveal from '../components/ui/Reveal'
import { site } from '../config/site'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'
import '../styles/contacto.css'
import '../styles/ie-contacto.css'

// Contacto como «centro de atención». Recurso firma: el bento de fotos.
//  1. Hero oscuro (fondo E) bajo la barra transparente, con la foto del asesor a la derecha y tres acciones.
//  2. «¿Qué necesitas?»: bento asimétrico (una tarjeta 2 × 2 y tres medianas), cada una con su foto.
//  3. Formulario con altura animada (solo para «Estado de mi pedido» sin sesión y «Garantías y devoluciones»).
//  4. Canales: tres columnas con separadores lila.
//  5. Visítanos: mapa estático y dirección.
export default function Contacto() {
  const { isAuthenticated } = useAuth()
  const { notify } = useToast()
  const [open, setOpen] = useState(null)
  const [last, setLast] = useState(null) // se conserva mientras el formulario se cierra
  const formRef = useRef(null)

  const toggle = (id) => {
    if (open === id) return setOpen(null)
    setOpen(id)
    setLast(id)
    setTimeout(() => formRef.current?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 320)
  }

  const copyEmail = async () => {
    try {
      await navigator.clipboard.writeText(site.email)
      notify('Correo copiado')
    } catch {
      notify(`Copia el correo a mano: ${site.email}`)
    }
  }

  const tiles = [
    { id: 'quote', cls: 'bento__big', icon: FileText, title: 'Cotizar un proyecto', text: 'Envía tu lista y te respondemos con precio.', to: '/cotizar', photo: '/img/fotos/contacto-cotizar.webp', alt: 'Dos personas revisan planos sobre una mesa para cotizar un proyecto' },
    { id: 'advice', cls: 'bento__a', icon: Headset, title: 'Asesoría técnica', text: 'Habla con un asesor por WhatsApp.', href: site.whatsappLink('Hola, necesito asesoría técnica sobre…'), photo: '/img/fotos/contacto-asesoria.webp', alt: 'Técnico revisando un tablero de control industrial abierto' },
    isAuthenticated
      ? { id: 'order', cls: 'bento__b', icon: PackageSearch, title: 'Estado de mi pedido', text: 'Míralo en Mi cuenta, con sus estados.', to: '/mi-cuenta', photo: '/img/fotos/contacto-pedido.webp', alt: 'Entrega de un paquete de mano en mano' }
      : { id: 'order', cls: 'bento__b', icon: PackageSearch, title: 'Estado de mi pedido', text: 'Escribe tu número de pedido.', photo: '/img/fotos/contacto-pedido.webp', alt: 'Entrega de un paquete de mano en mano' },
    { id: 'warranty', cls: 'bento__c', icon: ShieldCheck, title: 'Garantías y devoluciones', text: 'Cuéntanos qué pasó con tu producto.', photo: '/img/fotos/contacto-garantia.webp', alt: 'Mano revisando el panel de una máquina industrial' },
  ]

  return (
    <>
      {/* 5.1 Hero oscuro de 60vh */}
      <section className="ct-hero bgt bgt-e" aria-labelledby="h-contacto">
        <img className="ct-hero__bg" src="/img/fotos/contacto-asesor.webp" alt="Técnico con casco y ropa reflectiva revisando un tablero eléctrico abierto" width="1600" height="1200" fetchPriority="high" loading="eager" />
        <span className="ct-hero__veil" aria-hidden="true" />
        <div className="wrap ct-hero__in">
          <Pop className="ct-hero__text">
            <h1 id="h-contacto" className="ct-h1">
              <span className="ct-h1__thin">Hablemos de</span>
              <span className="ct-h1__strong">tu proyecto</span>
            </h1>
            <p className="ct-hero__lead">Escríbenos o llámanos y un asesor te responde con la referencia y el precio.</p>
            <div className="ct-hero__acts">
              <a className="ct-pill ct-pill--wa" href={site.whatsappUrl} target="_blank" rel="noreferrer"><MessageCircle size={18} aria-hidden="true" /> WhatsApp</a>
              <CallPill />
              <Link className="ct-pill ct-pill--quote" to="/cotizar"><FileText size={18} aria-hidden="true" /> Cotizar</Link>
            </div>
          </Pop>
        </div>
      </section>

      {/* 5.2 Bento de fotos */}
      <section className="ct-need bgt bgt-a" aria-labelledby="h-need">
        <Squares items={[{ w: 220, h: 200, tone: 'lila', rate: 0.1, top: '4%', right: '-40px' }, { w: 140, tone: 'orange', rate: -0.1, bottom: '10%', left: '-30px' }]} />
        <div className="wrap">
          <Pop><h2 id="h-need" className="rd-h2">¿Qué <Hl>necesitas?</Hl></h2></Pop>
          <ul className="bento">
            {tiles.map(({ id, cls, icon: Icon, title, text, to, href, photo, alt }, n) => {
              const inner = (
                <>
                  <img className="bento__img" src={photo} alt={alt} width="1600" height="1067" loading="lazy" decoding="async" />
                  <span className="bento__veil" aria-hidden="true" />
                  <span className="bento__go" aria-hidden="true"><ArrowUpRight size={22} /></span>
                  <span className="bento__txt">
                    <Icon className="bento__ic" size={32} strokeWidth={1.6} aria-hidden="true" />
                    <strong>{title}</strong>
                    <span>{text}</span>
                  </span>
                </>
              )
              return (
                <Reveal as="li" mask i={n} key={id} className={`bento__cell ${cls}`}>
                  {to ? <Link to={to} className="bento__tile">{inner}</Link>
                    : href ? <a href={href} target="_blank" rel="noreferrer" className="bento__tile">{inner}</a>
                    : <button type="button" className={`bento__tile ${open === id ? 'is-open' : ''}`} aria-expanded={open === id} aria-controls="formulario" onClick={() => toggle(id)}>{inner}</button>}
                </Reveal>
              )
            })}
          </ul>

          {/* 5.4 El formulario solo aparece al elegir «Estado de mi pedido» (sin sesión) o «Garantías y devoluciones»; la altura se anima 300 ms */}
          <div className={`ct-reveal ${open ? 'is-open' : ''}`} id="formulario" ref={formRef} inert={!open}>
            <div className="ct-reveal__in">
              <div className="ct-panel" aria-labelledby="h-ayuda">
                {last && <SmartForm key={last} fixedTopic={last} />}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 5.3 Canales: tres columnas con separadores lila */}
      <section className="ct-chan bgt bgt-d bgt--arc-l" aria-label="Formas de contactarnos">
        <Squares items={[{ w: 160, h: 200, tone: 'orange', rate: 0.1, top: '10%', right: '6%' }, { w: 120, tone: 'lila', rate: -0.1, bottom: '12%', left: '30%' }]} />
        <div className="wrap">
          <ul className="chan3">
            <Reveal as="li" i={0}>
              <span className="chan3__ic"><Mail size={22} aria-hidden="true" /></span>
              <p className="chan3__lbl">Correo</p>
              <a className="chan3__big chan3__big--mail" href={`mailto:${site.email}`}>{site.email}</a>
              <button type="button" className="chan3__act" onClick={copyEmail}><Copy size={16} aria-hidden="true" /> Copiar</button>
            </Reveal>
            <Reveal as="li" i={1}>
              <span className="chan3__ic"><Phone size={22} aria-hidden="true" /></span>
              <p className="chan3__lbl">Teléfono</p>
              <span className="chan3__big">{site.mainContact.label}</span>
              <a className="chan3__act" href={site.mainContact.href}><Phone size={16} aria-hidden="true" /> Llamar</a>
            </Reveal>
            <Reveal as="li" i={2}>
              <span className="chan3__ic"><MessageCircle size={22} aria-hidden="true" /></span>
              <p className="chan3__lbl">WhatsApp</p>
              <span className="chan3__big">Un asesor te responde</span>
              <a className="chan3__act" href={site.whatsappUrl} target="_blank" rel="noreferrer"><MessageCircle size={16} aria-hidden="true" /> Chat</a>
            </Reveal>
          </ul>
        </div>
      </section>

      {/* 5.5 Visítanos */}
      <section className="ct-where bgt bgt-b" aria-labelledby="h-visit">
        <Squares items={[{ w: 200, h: 160, tone: 'violet', rate: 0.1, top: '14%', left: '-40px' }, { w: 150, tone: 'orange', rate: -0.1, bottom: '16%', right: '3%' }]} />
        <div className="wrap">
          <Pop>
            <div className="visit2">
              <a className="visit2__map" href={site.directionsUrl} target="_blank" rel="noreferrer" aria-label="Abrir la ubicación de la tienda en Google Maps">
                <img src="/img/mapa-tienda.webp" alt="Mapa del centro de Bucaramanga con la ubicación de la tienda" width="900" height="520" loading="lazy" />
                <span className="visit2__pin" aria-hidden="true"><MapPin size={38} strokeWidth={2.2} fill="currentColor" /></span>
                <small>© OpenStreetMap</small>
              </a>
              <div className="visit2__info">
                <h2 id="h-visit" className="rd-h2">Visítanos en <Hl>la tienda</Hl></h2>
                <p className="visit2__addr"><MapPin size={18} aria-hidden="true" /><span><strong>{site.address}</strong><br />{site.city}</span></p>
                {site.hours && <p className="visit2__hours">{site.hours}</p>}
                <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="o" icon={Navigation}>Cómo llegar</Pill>
              </div>
            </div>
          </Pop>
        </div>
      </section>
    </>
  )
}
