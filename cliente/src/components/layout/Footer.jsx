import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Banknote, Building2, Landmark, Mail, MapPin, MessageCircle, Phone, Store } from 'lucide-react'
import { site } from '../../config/site'
import { newsletterApi } from '../../api'
import { useFamilies } from '../../hooks/useFamilies'
import { Facebook, Instagram } from '../ui/SocialIcons'
import Arc from '../ui/Arc'
import '../../styles/footer.css'

const helpLinks = [
  { to: '/como-comprar', label: 'Cómo comprar' },
  { to: '/carrito#cotizacion', label: 'Cotizar sin cuenta' },
  { to: '/nosotros', label: 'Nosotros' },
  { to: '/contacto', label: 'Contacto y tienda' },
  { to: '/crear-cuenta', label: 'Crea tu cuenta' },
]

// Medios de pago que la empresa acepta hoy. El espacio de PSE y tarjetas queda preparado y oculto hasta la fase 2.
const payments = [
  { Icon: Landmark, label: 'Transferencia' },
  { Icon: Banknote, label: 'Consignación' },
  { Icon: Store, label: 'Efectivo en tienda' },
]
const SHOW_ONLINE_PAYMENTS = false

function Newsletter() {
  const [email, setEmail] = useState('')
  const [consent, setConsent] = useState(false)
  const [state, setState] = useState({ status: 'idle', message: '' })

  const submit = async (e) => {
    e.preventDefault()
    if (!consent) {
      setState({ status: 'error', message: 'Debes autorizar el tratamiento de tus datos para suscribirte.' })
      return
    }
    setState({ status: 'loading', message: '' })
    try {
      const res = await newsletterApi.subscribe({ email: email.trim(), privacy_accepted: true })
      setState({ status: 'ok', message: res?.data?.message ?? 'Listo, te suscribimos al boletín.' })
      setEmail('')
      setConsent(false)
    } catch (error) {
      setState({ status: 'error', message: error.fieldError?.('email') ?? error.fieldError?.('privacy_accepted') ?? error.message })
    }
  }

  return (
    <form className="ft__news" onSubmit={submit} noValidate>
      <label htmlFor="ft-email" className="sr-only">Correo electrónico</label>
      <div className="ft__news-row">
        <input id="ft-email" type="email" inputMode="email" autoComplete="email" placeholder="tu@correo.com" value={email} onChange={(e) => setEmail(e.target.value)} required />
        <button type="submit" disabled={state.status === 'loading'}>{state.status === 'loading' ? 'Enviando…' : 'Suscribirme'}</button>
      </div>
      <label className="ft__check">
        <input type="checkbox" checked={consent} onChange={(e) => setConsent(e.target.checked)} required />
        <span>
          Autorizo el tratamiento de mis datos personales según la Ley 1581 de 2012 y la{' '}
          <Link to="/politica-datos">política de tratamiento de datos</Link>.
        </span>
      </label>
      <p className={`ft__news-msg ${state.status === 'error' ? 'is-error' : ''}`} role="status" aria-live="polite">{state.message}</p>
    </form>
  )
}

// Pie oscuro con aire: marca, portafolio, ayuda, contacto y suscripción. Los teléfonos van en una sola lista compacta.
export default function Footer() {
  const { data: families } = useFamilies()

  return (
    <footer className="ft">
      <Arc pos="tr" size="clamp(160px, 22vw, 320px)" />
      <div className="wrap">
        <div className="ft__grid">
          <div className="ft__brand">
            <img src="/img/logo-blanco.png" alt="Imagen Eléctrica" width="72" height="79" loading="lazy" />
            <p>{site.tagline}. Más de {site.years} años distribuyendo material eléctrico industrial en Bucaramanga.</p>
            <div className="ft__social">
              <a href={site.social.facebook} target="_blank" rel="noreferrer" aria-label="Facebook de Imagen Eléctrica"><Facebook size={18} /></a>
              <a href={site.social.instagram} target="_blank" rel="noreferrer" aria-label="Instagram de Imagen Eléctrica"><Instagram size={18} /></a>
            </div>
          </div>

          <nav className="ft__col" aria-labelledby="ft-port">
            <h2 id="ft-port">Portafolio</h2>
            <ul>
              <li><Link to="/productos">Todo el catálogo</Link></li>
              {families.map((f) => <li key={f.id}><Link to={`/productos?familia=${f.slug}`}>{f.name}</Link></li>)}
            </ul>
          </nav>

          <nav className="ft__col" aria-labelledby="ft-ayuda">
            <h2 id="ft-ayuda">Ayuda</h2>
            <ul>{helpLinks.map((l) => <li key={l.to}><Link to={l.to}>{l.label}</Link></li>)}</ul>
          </nav>

          <div className="ft__col ft__contact">
            <h2>Contacto</h2>
            <ul>
              <li><MapPin size={16} aria-hidden="true" /> <span>{site.address}<br />{site.city}</span></li>
              <li className="ft__phones">
                <Phone size={16} aria-hidden="true" />
                <span>{site.contacts.map((c) => <a key={c.href} href={c.href}>{c.label}</a>)}</span>
              </li>
              <li><Mail size={16} aria-hidden="true" /> <a href={`mailto:${site.email}`}>{site.email}</a></li>
              <li><MessageCircle size={16} aria-hidden="true" /> <a href={site.whatsappUrl} target="_blank" rel="noreferrer">WhatsApp</a></li>
            </ul>
          </div>

          <div className="ft__col ft__subscribe">
            <h2>Suscríbete</h2>
            <p>Novedades y promociones de material eléctrico industrial.</p>
            <Newsletter />
          </div>
        </div>

        <div className="ft__pay" aria-label="Medios de pago">
          <span>Medios de pago</span>
          <ul>
            {payments.map(({ Icon, label }) => <li key={label}><Icon size={16} aria-hidden="true" /> {label}</li>)}
            {SHOW_ONLINE_PAYMENTS && <li><Building2 size={16} aria-hidden="true" /> PSE y tarjetas</li>}
          </ul>
        </div>

        <div className="ft__legal">
          <span>© 2026 {site.legalName || 'Imagen Eléctrica'}{site.nit && <> · NIT {site.nit}</>}</span>
          <nav aria-label="Legal">
            <Link to="/politica-datos">Política de datos</Link>
            <Link to="/politica-datos#terminos">Términos</Link>
            <a href={site.sicUrl} target="_blank" rel="noreferrer">SIC</a>
            <Link to="/contacto?asunto=pqrs">PQRS</Link>
          </nav>
        </div>
      </div>
    </footer>
  )
}
