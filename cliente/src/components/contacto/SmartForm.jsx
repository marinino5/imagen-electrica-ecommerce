import { useState } from 'react'
import { Link } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { ArrowLeft, CheckCircle2, Mail, MessageCircle, RotateCcw, Zap } from 'lucide-react'
import Field from '../ui/Field'
import { site } from '../../config/site'
import { topics } from './contactTopics'

const person = { name: '', email: '', phone: '', company: '', privacy: false }

function validatePerson(p) {
  const e = {}
  if (!p.name.trim()) e.name = 'Escribe tu nombre.'
  if (!/^\S+@\S+\.\S+$/.test(p.email.trim())) e.email = 'Escribe un correo válido.'
  if (!p.phone.trim()) e.phone = 'Escribe tu teléfono.'
  if (!p.privacy) e.privacy = 'Debes aceptar la política de tratamiento de datos.'
  return e
}

// Como no hay endpoint, el mensaje se arma aquí y se abre en WhatsApp o en el correo del usuario
function buildMessage(p, body) {
  return [
    `Hola, soy ${p.name.trim()}${p.company.trim() ? ` de ${p.company.trim()}` : ''}.`,
    `Correo: ${p.email.trim()}`,
    `Teléfono: ${p.phone.trim()}`,
    '',
    body.trim(),
  ].join('\n')
}

// Formulario inteligente: primero se elige el motivo; según la opción cambian los campos y la plantilla del mensaje
export default function SmartForm() {
  const [topicId, setTopicId] = useState(null)
  const [values, setValues] = useState({}) // campos propios del motivo
  const [message, setMessage] = useState('')
  const [edited, setEdited] = useState(false) // true cuando la persona cambió el mensaje a mano
  const [data, setData] = useState(person)
  const [errors, setErrors] = useState({})
  const [ready, setReady] = useState(false)

  const topic = topics.find((t) => t.id === topicId)
  const auto = topic ? topic.template(values) : ''
  const body = edited ? message : auto

  const pick = (id) => {
    setTopicId(id)
    setValues({})
    setEdited(false)
    setErrors({})
    setReady(false)
  }
  const setValue = (name) => (e) => setValues((v) => ({ ...v, [name]: e.target.value }))
  const setPerson = (name) => (e) => setData((d) => ({ ...d, [name]: e.target.type === 'checkbox' ? e.target.checked : e.target.value }))

  const submit = (e) => {
    e.preventDefault()
    const found = validatePerson(data)
    topic.fields.filter((f) => f.required && !values[f.name]?.trim()).forEach((f) => (found[f.name] = 'Completa este dato.'))
    if (topic.id === 'other' && body.trim().length < 6) found.message = 'Cuéntanos qué necesitas.'
    setErrors(found)
    if (Object.keys(found).length === 0) setReady(true)
  }

  const text = buildMessage(data, body)
  const sendWhatsApp = () => window.open(site.whatsappLink(text), '_blank', 'noopener')
  const sendMail = () => {
    window.location.href = `mailto:${site.email}?subject=${encodeURIComponent(`${topic.label} · contacto desde la tienda`)}&body=${encodeURIComponent(text)}`
  }

  return (
    <div className="smart card">
      <p className="eyebrow"><Zap size={14} aria-hidden="true" /> Escríbenos</p>
      <h2 className="smart__title" id="h-ayuda">¿En qué te ayudamos?</h2>

      <div className="chips" role="radiogroup" aria-labelledby="h-ayuda">
        {topics.map(({ id, label, icon: Icon }) => (
          <button key={id} type="button" role="radio" aria-checked={topicId === id} className={`chip ${topicId === id ? 'is-on' : ''}`} onClick={() => pick(id)}>
            <Icon size={18} aria-hidden="true" /> {label}
          </button>
        ))}
      </div>

      <AnimatePresence mode="wait" initial={false}>
        {!topic && (
          <motion.p key="hint" className="smart__hint" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
            Elige una opción y te mostramos lo que necesitamos saber.
          </motion.p>
        )}

        {topic && !ready && (
          <motion.form
            key={`form-${topic.id}`}
            className="smart__form"
            noValidate
            onSubmit={submit}
            initial={{ opacity: 0, y: 18 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.3 }}
          >
            {topic.fields.length > 0 && (
              <div className="smart__group">
                {topic.fields.map((f) =>
                  f.type === 'select' ? (
                    <Field key={f.name} as="select" label={f.label} value={values[f.name] ?? ''} onChange={setValue(f.name)} error={errors[f.name]}>
                      <option value="">Selecciona una opción</option>
                      {f.options.map((o) => <option key={o} value={o}>{o}</option>)}
                    </Field>
                  ) : (
                    <Field
                      key={f.name}
                      as={f.type === 'textarea' ? 'textarea' : 'input'}
                      rows={f.type === 'textarea' ? 3 : undefined}
                      label={f.label}
                      placeholder={f.placeholder}
                      value={values[f.name] ?? ''}
                      onChange={setValue(f.name)}
                      error={errors[f.name]}
                      required={f.required}
                    />
                  ),
                )}
              </div>
            )}

            <div className="smart__message">
              <Field
                as="textarea"
                rows={5}
                label="Mensaje"
                hint="Se arma con lo que escribes arriba; puedes ajustarlo."
                value={body}
                onChange={(e) => {
                  setMessage(e.target.value)
                  setEdited(true)
                }}
                error={errors.message}
              />
              {edited && topic.id !== 'other' && (
                <button type="button" className="link-btn" onClick={() => setEdited(false)}>
                  <RotateCcw size={14} aria-hidden="true" /> Restablecer plantilla
                </button>
              )}
            </div>

            <h3 className="smart__sub">Tus datos</h3>
            <div className="form-grid">
              <Field label="Nombre" value={data.name} onChange={setPerson('name')} error={errors.name} autoComplete="name" required />
              <Field label="Correo electrónico" type="email" value={data.email} onChange={setPerson('email')} error={errors.email} autoComplete="email" required />
              <Field label="Teléfono" type="tel" value={data.phone} onChange={setPerson('phone')} error={errors.phone} autoComplete="tel" required />
              <Field label="Empresa (opcional)" value={data.company} onChange={setPerson('company')} autoComplete="organization" />
            </div>

            <div className="field">
              <label className="check">
                <input type="checkbox" checked={data.privacy} onChange={setPerson('privacy')} />
                <span>
                  Acepto la <Link to="/politica-datos" target="_blank">política de tratamiento de datos personales</Link>.
                </span>
              </label>
              {errors.privacy && <span className="field-error">{errors.privacy}</span>}
            </div>

            <button type="submit" className="btn btn--accent btn--lg smart__submit">Enviar mensaje</button>
          </motion.form>
        )}

        {topic && ready && (
          <motion.div
            key="ready"
            className="smart__ready"
            role="status"
            initial={{ opacity: 0, scale: 0.97 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.3 }}
          >
            <CheckCircle2 size={40} aria-hidden="true" />
            <h3>Tu mensaje está listo</h3>
            <p>Elige cómo enviarlo: se abrirá WhatsApp o tu correo con el texto armado.</p>
            <pre className="smart__preview">{text}</pre>
            <div className="smart__send">
              <button type="button" className="btn btn--accent btn--lg" onClick={sendWhatsApp}>
                <MessageCircle size={18} aria-hidden="true" /> Enviar por WhatsApp
              </button>
              <button type="button" className="btn btn--ghost btn--lg" onClick={sendMail}>
                <Mail size={18} aria-hidden="true" /> Enviar por correo
              </button>
            </div>
            <button type="button" className="link-btn" onClick={() => setReady(false)}>
              <ArrowLeft size={14} aria-hidden="true" /> Editar mensaje
            </button>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}
