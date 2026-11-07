import { useEffect, useId, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { ArrowLeft, ArrowRight, Check, CheckCircle2, FileText, MessageCircle, Paperclip, Upload, X } from 'lucide-react'
import { quotesApi } from '../api'
import { site } from '../config/site'
import Hl from '../components/ui/Hl'
import Mix from '../components/ui/Mix'
import Reveal from '../components/ui/Reveal'
import '../styles/ie-cotizar.css'

const MAX_BYTES = 5 * 1024 * 1024
const TYPES = ['application/pdf', 'image/jpeg', 'image/png']
const EMPTY = { name: '', phone: '', address: '', city: '', email: '', company: '', nit: '', message: '', application: '', privacy: false, website: '' }

// Reglas de cada campo (las mismas del backend: StoreFreeQuoteRequestRequest). Devuelven el mensaje o ''.
const RULES = {
  name: (v) => (v.trim() ? '' : 'Escribe tu nombre.'),
  phone: (v) => (!v.trim() ? 'Escribe un teléfono o WhatsApp.' : /^[0-9+()\s.-]{7,30}$/.test(v.trim()) ? '' : 'Escribe un teléfono válido, por ejemplo 300 123 4567.'),
  address: (v) => (v.trim() ? '' : 'Escribe tu dirección.'),
  city: (v) => (v.trim() ? '' : 'Escribe tu ciudad.'),
  email: (v) => (!v.trim() || /^\S+@\S+\.\S+$/.test(v.trim()) ? '' : 'Escribe un correo válido o déjalo vacío.'),
  message: (v) => (v.trim().length >= 10 ? '' : 'Cuéntanos qué productos necesitas (mínimo 10 caracteres).'),
  privacy: (v) => (v ? '' : 'Debes autorizar el tratamiento de tus datos para enviar la solicitud.'),
}
const STEP1 = ['name', 'phone', 'address', 'city', 'email']
const STEP2 = ['message', 'privacy']

const fileError = (f) => {
  if (!f) return ''
  if (!TYPES.includes(f.type)) return 'El archivo debe ser PDF, JPG o PNG.'
  if (f.size > MAX_BYTES) return 'El archivo pesa más de 5 MB.'
  return ''
}
const fmtSize = (b) => (b < 1024 * 1024 ? `${Math.max(1, Math.round(b / 1024))} KB` : `${(b / 1024 / 1024).toFixed(1).replace('.', ',')} MB`)

function Input({ name, label, hint, values, errors, set, blur, as: Tag = 'input', required, ...props }) {
  const id = useId()
  const error = errors[name]
  return (
    <div className={`cz-field ${error ? 'has-error' : ''}`}>
      <label htmlFor={id}>{label}{required && <abbr title="obligatorio"> *</abbr>}</label>
      <Tag id={id} name={name} value={values[name]} onChange={set(name)} onBlur={() => blur(name)} aria-invalid={error ? 'true' : undefined} aria-describedby={error ? `${id}-e` : hint ? `${id}-h` : undefined} {...props} />
      {hint && !error && <span id={`${id}-h`} className="cz-hint">{hint}</span>}
      {error && <span id={`${id}-e`} className="cz-error" role="alert">{error}</span>}
    </div>
  )
}

export default function Cotizar() {
  const [params] = useSearchParams()
  const [choice, setChoice] = useState(null) // null | 'form'
  const [step, setStep] = useState(1)
  const [values, setValues] = useState({ ...EMPTY, message: params.get('q') ? `${params.get('q')}: ` : '' })
  const [errors, setErrors] = useState({})
  const [file, setFile] = useState(null)
  const [fileErr, setFileErr] = useState('')
  const [drag, setDrag] = useState(false)
  const [status, setStatus] = useState('idle') // idle | sending | done
  const [result, setResult] = useState(null)
  const formTop = useRef(null)
  const fileInput = useRef(null)

  // Llegar con ?q= (por ejemplo desde «Cotizar sistema solar») abre el formulario ya con el tema escrito
  useEffect(() => {
    if (params.get('q')) setChoice('form')
  }, [params])

  const set = (name) => (e) => setValues((v) => ({ ...v, [name]: e.target.type === 'checkbox' ? e.target.checked : e.target.value }))
  const blur = (name) => RULES[name] && setErrors((er) => ({ ...er, [name]: RULES[name](values[name]) }))
  const validate = (names) => {
    const found = {}
    names.forEach((n) => { const m = RULES[n](values[n]); if (m) found[n] = m })
    setErrors((er) => ({ ...er, ...Object.fromEntries(names.map((n) => [n, found[n] ?? ''])) }))
    return Object.keys(found).length === 0
  }

  const pickFile = (f) => {
    if (!f) return
    const msg = fileError(f)
    setFileErr(msg)
    setFile(msg ? null : f)
  }
  const onDrop = (e) => {
    e.preventDefault()
    setDrag(false)
    pickFile(e.dataTransfer.files?.[0])
  }

  const next = () => {
    if (!validate(STEP1)) return
    setStep(2)
    formTop.current?.scrollIntoView({ block: 'start', behavior: 'smooth' })
  }

  const submit = async (e) => {
    e.preventDefault()
    if (!validate(STEP2) || fileErr) return
    setStatus('sending')
    const body = new FormData()
    ;['name', 'phone', 'address', 'city', 'email', 'company', 'nit', 'message', 'application', 'website'].forEach((k) => values[k] !== '' && body.append(k, values[k].trim?.() ?? values[k]))
    body.append('privacy_accepted', values.privacy ? '1' : '0')
    if (file) body.append('attachment', file)
    try {
      const res = await quotesApi.createFree(body)
      setResult(res?.data ?? {})
      setStatus('done')
      window.scrollTo({ top: 0, behavior: 'smooth' })
    } catch (error) {
      setStatus('idle')
      if (error.kind === 'validation') {
        const fe = Object.fromEntries(Object.keys(error.errors ?? {}).map((k) => [k, error.fieldError(k)]))
        setErrors((er) => ({ ...er, ...fe }))
        if (fe.attachment) setFileErr(fe.attachment)
        if (STEP1.some((k) => fe[k])) setStep(1)
      } else {
        setErrors((er) => ({ ...er, _form: error.message }))
      }
    }
  }

  const waFree = site.whatsappLink('Hola, quiero cotizar algo que no encuentro en el catálogo.')
  const waDone = result ? site.whatsappLink(`Hola, acabo de enviar la solicitud de cotización ${result.reference}. Quedo atento a su respuesta.`) : '#'

  if (status === 'done') {
    return (
      <section className="cz-head bg-lila-arc cz-done" aria-labelledby="h-cz-ok">
        <div className="wrap">
          <Reveal className="cz-ok">
            <span className="cz-ok__ic"><CheckCircle2 size={40} aria-hidden="true" /></span>
            <p className="rd-label">Solicitud enviada</p>
            <h1 id="h-cz-ok" className="ie-h1"><Mix light="Recibimos">tu <Hl>cotización</Hl></Mix></h1>
            <p className="cz-ok__ref">Tu número de solicitud es <strong>{result?.reference}</strong></p>
            <p className="rd-lead">Un asesor te responde {result?.response_time ?? site.quoteResponse}, por WhatsApp o por correo, con la referencia, el precio y el tiempo de entrega.</p>
            <div className="cz-ok__actions">
              <a className="pill pill--o" href={waDone} target="_blank" rel="noreferrer"><span className="pill__label">Continuar por WhatsApp</span><span className="pill__ic" aria-hidden="true"><MessageCircle size={18} /></span></a>
              <Link className="cz-link" to="/productos">Seguir viendo el catálogo</Link>
            </div>
          </Reveal>
        </div>
      </section>
    )
  }

  const progress = step === 1 ? 50 : 100

  return (
    <>
      <section className="cz-head bg-lila-arc" aria-labelledby="h-cz">
        <div className="wrap">
          <Reveal>
            <p className="rd-label">Cotización libre</p>
            <h1 id="h-cz" className="ie-h1"><Mix light="Cotiza">lo que <Hl>necesites</Hl></Mix></h1>
            <p className="rd-lead">Aunque no esté en el catálogo.</p>
          </Reveal>

          <div className="cz-choices" role="group" aria-label="Cómo quieres cotizar">
            <button type="button" className={`cz-choice ${choice === 'form' ? 'is-on' : ''}`} aria-pressed={choice === 'form'} aria-expanded={choice === 'form'} aria-controls="cz-form" onClick={() => setChoice('form')}>
              <span className="cz-choice__ic"><FileText size={26} aria-hidden="true" /></span>
              <strong>Llenar formulario</strong>
              <small>Cuéntanos qué necesitas en dos pasos y adjunta una foto o la placa del equipo si la tienes.</small>
              <span className="cz-choice__go">Empezar <ArrowRight size={16} aria-hidden="true" /></span>
            </button>
            <a className="cz-choice cz-choice--wa" href={waFree} target="_blank" rel="noreferrer">
              <span className="cz-choice__ic"><MessageCircle size={26} aria-hidden="true" /></span>
              <strong>Escribir por WhatsApp</strong>
              <small>Abre el chat con un mensaje listo; solo cuéntale al asesor lo que buscas.</small>
              <span className="cz-choice__go">Abrir WhatsApp <ArrowRight size={16} aria-hidden="true" /></span>
            </a>
          </div>

          {/* El formulario se despliega con altura animada (300 ms) al elegir la primera opción */}
          <div className={`cz-collapse ${choice === 'form' ? 'is-open' : ''}`} id="cz-form" ref={formTop} inert={choice !== 'form'}>
            <div className="cz-collapse__in">
              <form className="cz-form" onSubmit={submit} noValidate aria-label="Formulario de cotización libre">
                <ol className="cz-steps" aria-label="Progreso">
                  <li className={step >= 1 ? 'is-on' : ''} aria-current={step === 1 ? 'step' : undefined}><b>{step > 1 ? <Check size={14} strokeWidth={3} aria-hidden="true" /> : 1}</b> Datos</li>
                  <li className={step >= 2 ? 'is-on' : ''} aria-current={step === 2 ? 'step' : undefined}><b>2</b> Pedido</li>
                  <span className="cz-steps__bar" role="progressbar" aria-valuemin={0} aria-valuemax={100} aria-valuenow={progress} aria-label={`Paso ${step} de 2`}><span style={{ width: `${progress}%` }} /></span>
                </ol>

                {/* Campo trampa contra bots: debe quedar vacío */}
                <div className="cz-trap" aria-hidden="true"><label>No lo llenes<input type="text" tabIndex={-1} autoComplete="off" value={values.website} onChange={set('website')} /></label></div>

                {step === 1 && (
                  <fieldset className="cz-grid">
                    <legend className="sr-only">Tus datos</legend>
                    <Input name="name" label="Nombre" required autoComplete="name" values={values} errors={errors} set={set} blur={blur} />
                    <Input name="phone" label="Teléfono o WhatsApp" required type="tel" inputMode="tel" autoComplete="tel" values={values} errors={errors} set={set} blur={blur} />
                    <Input name="address" label="Dirección" required autoComplete="street-address" values={values} errors={errors} set={set} blur={blur} />
                    <Input name="city" label="Ciudad" required autoComplete="address-level2" values={values} errors={errors} set={set} blur={blur} />
                    <Input name="email" label="Correo" type="email" inputMode="email" autoComplete="email" hint="Opcional" values={values} errors={errors} set={set} blur={blur} />
                    <Input name="company" label="Empresa" autoComplete="organization" hint="Opcional" values={values} errors={errors} set={set} blur={blur} />
                    <Input name="nit" label="NIT" hint="Opcional" values={values} errors={errors} set={set} blur={blur} />
                  </fieldset>
                )}

                {step === 2 && (
                  <fieldset className="cz-stack">
                    <legend className="sr-only">Tu pedido</legend>
                    <Input as="textarea" rows={5} name="message" label="¿Qué productos necesitas?" required placeholder="Ejemplo: 20 metros de cable 4x10 THHN, 2 guardamotores de 10 a 16 A y un contactor de 25 A para una bomba de 5 HP." values={values} errors={errors} set={set} blur={blur} />
                    <Input name="application" label="¿Para qué aplicación?" hint="Opcional. Ejemplo: bomba de agua, banda transportadora, tablero de obra." values={values} errors={errors} set={set} blur={blur} />

                    <div className="cz-field">
                      <span className="cz-label" id="cz-file-l">Adjunto <small>(opcional)</small></span>
                      <label
                        className={`cz-drop ${drag ? 'is-drag' : ''} ${fileErr ? 'has-error' : ''}`}
                        onDragOver={(e) => { e.preventDefault(); setDrag(true) }}
                        onDragLeave={() => setDrag(false)}
                        onDrop={onDrop}
                      >
                        <input ref={fileInput} type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" aria-labelledby="cz-file-l" aria-describedby="cz-file-h" onChange={(e) => pickFile(e.target.files?.[0])} />
                        <Upload size={22} aria-hidden="true" />
                        <span><b>Arrastra un archivo aquí</b> o toca para elegirlo</span>
                        <small id="cz-file-h">Foto, placa del equipo o lista de materiales. PDF, JPG o PNG, máximo 5 MB.</small>
                      </label>
                      {file && (
                        <p className="cz-file"><Paperclip size={16} aria-hidden="true" /> <span>{file.name} · {fmtSize(file.size)}</span>
                          <button type="button" onClick={() => { setFile(null); setFileErr(''); if (fileInput.current) fileInput.current.value = '' }} aria-label={`Quitar ${file.name}`}><X size={16} aria-hidden="true" /></button>
                        </p>
                      )}
                      {fileErr && <span className="cz-error" role="alert">{fileErr}</span>}
                    </div>

                    <div className={`cz-field ${errors.privacy ? 'has-error' : ''}`}>
                      <label className="cz-check">
                        <input type="checkbox" checked={values.privacy} onChange={set('privacy')} aria-invalid={errors.privacy ? 'true' : undefined} aria-describedby={errors.privacy ? 'cz-priv-e' : undefined} />
                        <span>Autorizo el tratamiento de mis datos personales según la Ley 1581 de 2012 y la <Link to="/politica-datos">política de tratamiento de datos</Link>. <abbr title="obligatorio">*</abbr></span>
                      </label>
                      {errors.privacy && <span id="cz-priv-e" className="cz-error" role="alert">{errors.privacy}</span>}
                    </div>
                    {errors._form && <p className="cz-error cz-error--form" role="alert">{errors._form}</p>}
                  </fieldset>
                )}

                <div className="cz-actions">
                  {step === 2 && <button type="button" className="cz-back" onClick={() => setStep(1)}><ArrowLeft size={16} aria-hidden="true" /> Volver a mis datos</button>}
                  {step === 1 ? (
                    <button type="button" className="cz-next" onClick={next}>Continuar <ArrowRight size={18} aria-hidden="true" /></button>
                  ) : (
                    <button type="submit" className="cz-next" disabled={status === 'sending'}>{status === 'sending' ? 'Enviando…' : 'Enviar cotización'}</button>
                  )}
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </>
  )
}
