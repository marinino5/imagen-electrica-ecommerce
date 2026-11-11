import { useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowLeft, ArrowRight, Building2, Check, Lock, User } from 'lucide-react'
import { checkoutApi } from '../../api'
import { useAuth } from '../../context/AuthContext'
import { useDepartments, useMunicipalities } from '../../hooks/useLocations'
import { DELIVERY, DOC_TYPES, FISCAL, PRIVACY_VERSION, digits, initialForm, nitCheckDigit, stepOfError, toPayload, validateBilling, validateDelivery } from '../../lib/checkout'
import { formatCOP } from '../../lib/format'
import Field from '../ui/Field'
import Pill from '../ui/Pill'
import '../../styles/checkout.css'

const STEPS = ['Facturación', 'Entrega', 'Confirmar']

// Lista de departamentos y municipios (DANE) en dos selects dependientes
function PlaceFields({ prefix = '', form, set, errors, serverError }) {
  const dept = useDepartments()
  const code = form[`${prefix}department_code`]
  const cities = useMunicipalities(code)
  const err = (k) => errors[k] ?? serverError?.fieldError?.(k)

  return (
    <>
      <Field as="select" label="Departamento" value={code} onChange={set(`${prefix}department_code`, { reset: `${prefix}city_code` })} error={err(`${prefix}department_code`)} autoComplete="address-level1" required>
        <option value="">{dept.loading ? 'Cargando…' : 'Elige el departamento'}</option>
        {dept.list.map((d) => <option key={d.code} value={d.code}>{d.name}</option>)}
      </Field>
      <Field as="select" label="Municipio" value={form[`${prefix}city_code`]} onChange={set(`${prefix}city_code`)} error={err(`${prefix}city_code`)} disabled={!code || cities.loading} autoComplete="address-level2" required hint={code ? undefined : 'Primero elige el departamento.'}>
        <option value="">{cities.loading ? 'Cargando…' : 'Elige el municipio'}</option>
        {cities.list.map((c) => <option key={c.code} value={c.code}>{c.name}</option>)}
      </Field>
      {(dept.error || cities.error) && <p className="co-warn" role="alert">No pudimos cargar la lista de ubicaciones. Revisa tu conexión y recarga la página.</p>}
    </>
  )
}

/**
 * Compra por pasos: 1 facturación (DIAN) · 2 entrega · 3 confirmar. Sirve para invitados (sin cuenta) y para quien tiene cuenta:
 * con cuenta el formulario llega autocompletado desde su perfil. El servidor vuelve a validar todo (422 por campo).
 */
export default function CheckoutForm({ purchaseLines, total, shippingNote, onCreated }) {
  const { user, isAuthenticated } = useAuth()
  const [form, setForm] = useState(() => initialForm(user))
  const [step, setStep] = useState(1)
  const [errors, setErrors] = useState({})
  const [busy, setBusy] = useState(false)
  const [serverError, setServerError] = useState(null)
  const root = useRef(null)

  const company = form.person_type === 'company'
  const pickup = form.delivery_method === 'pickup'
  const dvHint = company && digits(form.doc_number).length >= 5 ? nitCheckDigit(form.doc_number) : ''

  // set('campo') maneja inputs y checkboxes; { reset } vacía el campo dependiente (el municipio al cambiar el departamento)
  const set = (name, { reset } = {}) => (e) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value
    setForm((f) => {
      const next = { ...f, [name]: value, ...(reset ? { [reset]: '' } : {}) }
      // El DV se calcula solo al escribir el NIT (se puede corregir a mano; el servidor lo valida igual)
      if (name === 'doc_number' && f.person_type === 'company') next.nit_dv = nitCheckDigit(value)
      return next
    })
    setErrors((er) => ({ ...er, [name]: undefined }))
  }

  const setType = (type) => setForm((f) => ({
    ...f,
    person_type: type,
    doc_type: type === 'company' ? 'NIT' : f.doc_type === 'NIT' ? 'CC' : f.doc_type,
    nit_dv: type === 'company' ? nitCheckDigit(f.doc_number) : '',
  }))

  const scrollTop = () => root.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })

  const next = () => {
    const found = step === 1 ? validateBilling(form) : validateDelivery(form)
    setErrors(found)
    if (Object.keys(found).length === 0) {
      setStep(step + 1)
      scrollTop()
    }
  }
  const back = () => { setStep(step - 1); scrollTop() }

  const submit = async (e) => {
    e.preventDefault()
    if (step < 3) return next()
    setBusy(true)
    setServerError(null)
    try {
      const res = await checkoutApi.create(toPayload(form, purchaseLines.map((l) => ({ product_id: l.product.id, quantity: l.quantity }))), { auth: isAuthenticated })
      onCreated({ orders: res.data, meta: res.meta ?? {}, email: form.billing_email.trim() })
    } catch (err) {
      setServerError(err)
      // Un 422 lleva a la persona al paso donde está el campo a corregir
      if (err.kind === 'validation') {
        setStep(stepOfError(Object.keys(err.errors ?? {})))
        scrollTop()
      }
    } finally {
      setBusy(false)
    }
  }

  const e = (k) => errors[k] ?? serverError?.fieldError?.(k)
  const details = serverError?.kind === 'validation' ? [...new Set(Object.values(serverError.errors).flat())] : []
  const deptName = useDepartments().list
  const billingPlace = useMemo(() => deptName.find((d) => d.code === form.department_code)?.name ?? '', [deptName, form.department_code])

  return (
    <form className="co" onSubmit={submit} noValidate ref={root} id="cart-form" aria-label="Datos de compra">
      <ol className="co-steps" aria-label="Pasos de la compra">
        {STEPS.map((label, i) => (
          <li key={label} className={step === i + 1 ? 'is-on' : step > i + 1 ? 'is-done' : ''}>
            <button type="button" disabled={step <= i + 1} onClick={() => setStep(i + 1)} aria-current={step === i + 1 ? 'step' : undefined}>
              <span>{step > i + 1 ? <Check size={14} aria-hidden="true" /> : i + 1}</span>{label}
            </button>
          </li>
        ))}
      </ol>

      {serverError && (
        <div className="alert alert--error" role="alert">
          <p>{serverError.message}</p>
          {details.length > 1 && <ul>{details.map((d) => <li key={d}>{d}</li>)}</ul>}
        </div>
      )}

      {/* 1 · Facturación */}
      {step === 1 && (
        <fieldset className="co-pane">
          <legend>Datos de facturación electrónica</legend>
          <p className="co-lead">Los pedimos tal como van en la factura (DIAN). {isAuthenticated ? 'Vienen de tu perfil: corrígelos si cambiaron.' : 'No necesitas cuenta.'}</p>

          <div className="co-seg" role="radiogroup" aria-label="Tipo de persona">
            <label className={!company ? 'is-on' : ''}><input type="radio" name="person_type" checked={!company} onChange={() => setType('natural')} /><User size={18} aria-hidden="true" /> Persona natural</label>
            <label className={company ? 'is-on' : ''}><input type="radio" name="person_type" checked={company} onChange={() => setType('company')} /><Building2 size={18} aria-hidden="true" /> Empresa</label>
          </div>

          <div className="co-grid">
            {company ? (
              <>
                <Field label="NIT (sin dígito de verificación)" inputMode="numeric" value={form.doc_number} onChange={set('doc_number')} error={e('doc_number')} maxLength={14} required />
                <Field label="Dígito de verificación" inputMode="numeric" value={form.nit_dv} onChange={set('nit_dv')} error={e('nit_dv')} maxLength={1} required hint={dvHint ? `Calculado para ese NIT: ${dvHint}` : 'Se calcula al escribir el NIT.'} />
                <Field label="Razón social" value={form.company_name} onChange={set('company_name')} error={e('company_name')} autoComplete="organization" required wrap="co-span2" />
                <Field as="select" label="Responsabilidad fiscal" value={form.fiscal_responsibility} onChange={set('fiscal_responsibility')} error={e('fiscal_responsibility')} required>
                  {FISCAL.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                </Field>
                <Field label="Persona de contacto" value={form.contact_name} onChange={set('contact_name')} error={e('contact_name')} autoComplete="name" required />
              </>
            ) : (
              <>
                <Field as="select" label="Tipo de documento" value={form.doc_type} onChange={set('doc_type')} error={e('doc_type')} required>
                  {DOC_TYPES.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                </Field>
                <Field label="Número de documento" inputMode={form.doc_type === 'PP' ? 'text' : 'numeric'} value={form.doc_number} onChange={set('doc_number')} error={e('doc_number')} maxLength={16} required />
                <Field label="Nombres" value={form.first_name} onChange={set('first_name')} error={e('first_name')} autoComplete="given-name" required />
                <Field label="Apellidos" value={form.last_name} onChange={set('last_name')} error={e('last_name')} autoComplete="family-name" required />
              </>
            )}
            <Field label="Correo de facturación electrónica" type="email" value={form.billing_email} onChange={set('billing_email')} error={e('billing_email')} autoComplete="email" required hint="Aquí llega la factura y el enlace de tu pedido." />
            <Field label="Celular" type="tel" inputMode="tel" value={form.phone} onChange={set('phone')} error={e('phone')} autoComplete="tel" required />
            <Field label="Dirección de facturación" value={form.billing_address} onChange={set('billing_address')} error={e('billing_address')} autoComplete="street-address" required wrap="co-span2" />
            <PlaceFields form={form} set={set} errors={errors} serverError={serverError} />
          </div>
        </fieldset>
      )}

      {/* 2 · Entrega */}
      {step === 2 && (
        <fieldset className="co-pane">
          <legend>¿Cómo lo recibes?</legend>
          <div className="co-opts" role="radiogroup" aria-label="Forma de entrega">
            {DELIVERY.map((o) => (
              <label key={o.value} className={form.delivery_method === o.value ? 'is-on' : ''}>
                <input type="radio" name="delivery" value={o.value} checked={form.delivery_method === o.value} onChange={set('delivery_method')} />
                <span><strong>{o.label}</strong><small>{o.note}</small></span>
              </label>
            ))}
          </div>

          {!pickup && (
            <>
              <label className="check co-same">
                <input type="checkbox" checked={form.delivery_same_as_billing} onChange={set('delivery_same_as_billing')} />
                <span>Entregar en la dirección de facturación{form.billing_address ? `: ${form.billing_address}${billingPlace ? `, ${billingPlace}` : ''}` : ''}</span>
              </label>

              {!form.delivery_same_as_billing && (
                <div className="co-grid">
                  <PlaceFields prefix="delivery_" form={form} set={set} errors={errors} serverError={serverError} />
                  <Field label="Barrio" value={form.delivery_neighborhood} onChange={set('delivery_neighborhood')} error={e('delivery_neighborhood')} autoComplete="address-level3" required />
                  <Field label="Código postal (opcional)" inputMode="numeric" value={form.delivery_postal_code} onChange={set('delivery_postal_code')} error={e('delivery_postal_code')} maxLength={6} autoComplete="postal-code" />
                  <Field label="Vía (tipo y nombre)" value={form.delivery_road} onChange={set('delivery_road')} error={e('delivery_road')} placeholder="Carrera 27" required />
                  <Field label="Número" value={form.delivery_number} onChange={set('delivery_number')} error={e('delivery_number')} placeholder="# 34-12" required />
                  <Field label="Complemento (opcional)" value={form.delivery_complement} onChange={set('delivery_complement')} placeholder="Bodega 3, piso 2" wrap="co-span2" />
                </div>
              )}
              <Field as="textarea" rows={2} label="Indicaciones de entrega (opcional)" value={form.delivery_notes} onChange={set('delivery_notes')} maxLength={500} hint="Portería, horario, persona que recibe…" />
            </>
          )}
          <Field as="textarea" rows={2} label="Notas del pedido (opcional)" value={form.notes} onChange={set('notes')} maxLength={1000} />
        </fieldset>
      )}

      {/* 3 · Confirmar */}
      {step === 3 && (
        <fieldset className="co-pane">
          <legend>Revisa y confirma</legend>
          <div className="co-recap">
            <section>
              <h3>Facturación <button type="button" onClick={() => setStep(1)}>Editar</button></h3>
              <p><strong>{company ? form.company_name : `${form.first_name} ${form.last_name}`}</strong></p>
              <p>{company ? `NIT ${digits(form.doc_number)}-${form.nit_dv}` : `${form.doc_type} ${form.doc_number}`}</p>
              <p>{form.billing_email} · {form.phone}</p>
              <p>{form.billing_address}{billingPlace ? `, ${billingPlace}` : ''}</p>
            </section>
            <section>
              <h3>Entrega <button type="button" onClick={() => setStep(2)}>Editar</button></h3>
              {pickup ? <p><strong>Recoger en tienda</strong> · Cra 18 No 24-32, Bucaramanga</p> : form.delivery_same_as_billing ? <p><strong>{DELIVERY.find((o) => o.value === form.delivery_method)?.label}</strong> a la dirección de facturación</p> : (
                <>
                  <p><strong>{DELIVERY.find((o) => o.value === form.delivery_method)?.label}</strong></p>
                  <p>{[form.delivery_road, form.delivery_number, form.delivery_complement].filter(Boolean).join(' ')} · Barrio {form.delivery_neighborhood}</p>
                </>
              )}
              <p className="co-muted">{shippingNote}</p>
            </section>
          </div>

          <div className="co-total"><span>Total a pagar <small>IVA incluido</small></span><strong>{formatCOP(total)}</strong></div>

          {/* Campo trampa contra bots: las personas no lo ven */}
          <div className="cart-form__trap" aria-hidden="true">
            <label>No llenes este campo<input tabIndex={-1} autoComplete="off" value={form.website} onChange={set('website')} /></label>
          </div>

          <div className="field">
            <label className="check">
              <input type="checkbox" checked={form.privacy_accepted} onChange={set('privacy_accepted')} aria-invalid={e('privacy_accepted') ? 'true' : undefined} />
              <span>
                Autorizo el tratamiento de mis datos personales para gestionar esta compra y la facturación, según la <Link to="/politica-datos" target="_blank" rel="noopener">política de tratamiento de datos</Link> (Ley 1581 de 2012).
                <small className="co-muted"> Versión {PRIVACY_VERSION} · {new Date().toLocaleDateString('es-CO', { day: 'numeric', month: 'long', year: 'numeric' })}</small>
              </span>
            </label>
            {e('privacy_accepted') && <span className="field-error">{e('privacy_accepted')}</span>}
          </div>
          {isAuthenticated && (
            <label className="check"><input type="checkbox" checked={form.save_to_profile} onChange={set('save_to_profile')} /><span>Guardar estos datos en mi perfil para la próxima compra</span></label>
          )}
        </fieldset>
      )}

      <div className="co-actions">
        {step > 1 && <Pill tone="l" type="button" icon={ArrowLeft} onClick={back} disabled={busy}>Atrás</Pill>}
        {step < 3 ? (
          <Pill tone="o" type="submit" icon={ArrowRight}>Continuar</Pill>
        ) : (
          <Pill tone="o" type="submit" icon={Lock} disabled={busy || !form.privacy_accepted}>{busy ? 'Enviando…' : 'Confirmar pedido'}</Pill>
        )}
      </div>
    </form>
  )
}
