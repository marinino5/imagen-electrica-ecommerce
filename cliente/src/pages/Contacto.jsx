import { useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowUpRight, Mail, MapPin, MessageCircle, Navigation, Phone } from 'lucide-react'
import PageHeader from '../components/ui/PageHeader'
import Field from '../components/ui/Field'
import MapEmbed from '../components/ui/MapEmbed'
import { Facebook, Instagram } from '../components/ui/SocialIcons'
import Reveal from '../components/ui/Reveal'
import { site } from '../config/site'
import '../styles/pages.css'
import '../styles/info-pages.css'

const empty = { name: '', email: '', phone: '', company: '', message: '', privacy: false }

// Como no hay endpoint, el formulario arma el mensaje y lo abre en WhatsApp o en el correo del usuario
function buildMessage(f) {
  return [
    `Hola, soy ${f.name.trim()}${f.company.trim() ? ` de ${f.company.trim()}` : ''}.`,
    `Correo: ${f.email.trim()}`,
    `Teléfono: ${f.phone.trim()}`,
    '',
    f.message.trim(),
  ].join('\n')
}

function validate(f) {
  const errors = {}
  if (!f.name.trim()) errors.name = 'Escribe tu nombre.'
  if (!/^\S+@\S+\.\S+$/.test(f.email.trim())) errors.email = 'Escribe un correo válido.'
  if (!f.phone.trim()) errors.phone = 'Escribe tu teléfono.'
  if (!f.message.trim()) errors.message = 'Cuéntanos qué necesitas.'
  if (!f.privacy) errors.privacy = 'Debes aceptar la política de tratamiento de datos.'
  return errors
}

export default function Contacto() {
  const [form, setForm] = useState(empty)
  const [errors, setErrors] = useState({})

  const set = (name) => (e) => setForm((f) => ({ ...f, [name]: e.target.type === 'checkbox' ? e.target.checked : e.target.value }))

  const send = (channel) => {
    const found = validate(form)
    setErrors(found)
    if (Object.keys(found).length > 0) return
    const text = buildMessage(form)
    if (channel === 'whatsapp') {
      window.open(site.whatsappLink(text), '_blank', 'noopener')
    } else {
      window.location.href = `mailto:${site.email}?subject=${encodeURIComponent('Contacto desde la tienda')}&body=${encodeURIComponent(text)}`
    }
  }

  return (
    <>
      <PageHeader title="Hablemos de tu" accent="proyecto" crumbs={[{ label: 'Contacto' }]}>
        Escríbenos y un asesor te responde.
      </PageHeader>

      {/* Acciones rápidas: tres tarjetas grandes con hover elevado */}
      <section className="quick" aria-label="Contacto rápido">
        <div className="container quick__grid">
          <a className="quick__card quick__card--accent" href={site.whatsappUrl} target="_blank" rel="noreferrer">
            <MessageCircle size={34} aria-hidden="true" />
            <h2>WhatsApp</h2>
            <p>Escríbenos y te respondemos en el momento.</p>
            <span className="quick__go">Abrir chat <ArrowUpRight size={18} aria-hidden="true" /></span>
          </a>

          <div className="quick__card quick__card--violet">
            <Phone size={34} aria-hidden="true" />
            <h2>Llámanos</h2>
            <p>Habla directo con un asesor.</p>
            <div className="quick__phones">
              {site.phones.map((p) => (
                <a key={p.href} href={p.href}>{p.label}</a>
              ))}
            </div>
          </div>

          <a className="quick__card quick__card--white" href={`mailto:${site.email}`}>
            <Mail size={34} aria-hidden="true" />
            <h2>Escríbenos</h2>
            <p>Envíanos tu solicitud por correo.</p>
            <span className="quick__go">{site.email} <ArrowUpRight size={18} aria-hidden="true" /></span>
          </a>
        </div>
      </section>

      <section className="section">
        <div className="container contact">
          <Reveal>
            <form
              className="card contact__form"
              noValidate
              onSubmit={(e) => {
                e.preventDefault()
                send('whatsapp')
              }}
            >
              <h2 className="block-title">Envíanos un mensaje</h2>
              <div className="form-grid">
                <Field label="Nombre" value={form.name} onChange={set('name')} error={errors.name} autoComplete="name" required />
                <Field label="Correo electrónico" type="email" value={form.email} onChange={set('email')} error={errors.email} autoComplete="email" required />
                <Field label="Teléfono" type="tel" value={form.phone} onChange={set('phone')} error={errors.phone} autoComplete="tel" required />
                <Field label="Empresa (opcional)" value={form.company} onChange={set('company')} autoComplete="organization" />
              </div>
              <Field as="textarea" label="Mensaje" rows={5} value={form.message} onChange={set('message')} error={errors.message} required />

              <div className="field">
                <label className="check">
                  <input type="checkbox" checked={form.privacy} onChange={set('privacy')} />
                  <span>
                    Acepto la <Link to="/politica-datos" target="_blank">política de tratamiento de datos personales</Link>.
                  </span>
                </label>
                {errors.privacy && <span className="field-error">{errors.privacy}</span>}
              </div>

              <div className="contact__actions">
                <button type="submit" className="btn btn--accent btn--lg">
                  <MessageCircle size={18} aria-hidden="true" /> Enviar por WhatsApp
                </button>
                <button type="button" className="btn btn--ghost btn--lg" onClick={() => send('email')}>
                  <Mail size={18} aria-hidden="true" /> Enviar por correo
                </button>
              </div>
              <p className="field-hint">Se abrirá WhatsApp o tu correo con el mensaje listo para enviar.</p>
            </form>
          </Reveal>

          <Reveal delay={0.08}>
            <aside className="card visit-card" aria-labelledby="h-visit">
              <h2 id="h-visit" className="block-title"><MapPin size={20} aria-hidden="true" /> Visítanos</h2>
              <MapEmbed className="visit-card__map" />
              <p className="visit-card__address">
                <strong>{site.address}</strong><br />{site.city}
              </p>
              <div className="visit-card__foot">
                <a className="btn btn--primary" href={site.directionsUrl} target="_blank" rel="noreferrer">
                  <Navigation size={18} aria-hidden="true" /> Cómo llegar
                </a>
                <div className="visit-card__social">
                  <a href={site.social.facebook} target="_blank" rel="noreferrer" aria-label="Facebook de Imagen Eléctrica"><Facebook size={18} /></a>
                  <a href={site.social.instagram} target="_blank" rel="noreferrer" aria-label="Instagram de Imagen Eléctrica"><Instagram size={18} /></a>
                </div>
              </div>
            </aside>
          </Reveal>
        </div>
      </section>
    </>
  )
}
