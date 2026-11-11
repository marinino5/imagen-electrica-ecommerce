import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowLeft, ArrowRight, Building2, Check, Lock, MapPin, Store, Truck, User } from 'lucide-react'
import { checkoutApi, shippingApi } from '../../api'
import { site } from '../../config/site'
import { useAuth } from '../../context/AuthContext'
import { useDepartments, useMunicipalities } from '../../hooks/useLocations'
import { FISCAL_CODES, ID_TYPES, PRIVACY_VERSION, digits, initialForm, nitCheckDigit, stepOfError, toPayload, validateContact, validateDelivery, validateInvoice } from '../../lib/checkout'
import { formatCOP } from '../../lib/format'
import Field from '../ui/Field'
import Pill from '../ui/Pill'
import '../../styles/checkout.css'

const STEPS = ['Entrega', 'Tus datos', 'Facturación', 'Confirmar']

// Departamento → municipio (lista dependiente con código DANE). `prefix` distingue la dirección de entrega de la de facturación.
function PlaceFields({ stateField, cityField, form, set, errors, serverError }) {
  const dept = useDepartments()
  const code = form[stateField]
  const cities = useMunicipalities(code)
  const err = (k) => errors[k] ?? serverError?.fieldError?.(k)

  return (
    <>
      <Field as="select" label="Departamento" value={code} onChange={set(stateField, { reset: cityField })} error={err(stateField)} autoComplete="address-level1" required>
        <option value="">{dept.loading ? 'Cargando…' : 'Elige el departamento'}</option>
        {dept.list.map((d) => <option key={d.code} value={d.code}>{d.name}</option>)}
      </Field>
      <Field as="select" label="Municipio" value={form[cityField]} onChange={set(cityField)} error={err(cityField)} disabled={!code || cities.loading} autoComplete="address-level2" required hint={code ? undefined : 'Primero elige el departamento.'}>
        <option value="">{cities.loading ? 'Cargando…' : 'Elige el municipio'}</option>
        {cities.list.map((c) => <option key={c.code} value={c.code}>{c.name}</option>)}
      </Field>
      {(dept.error || cities.error) && <p className="co-warn" role="alert">No pudimos cargar la lista de ubicaciones. Revisa tu conexión y recarga la página.</p>}
    </>
  )
}

/**
 * Compra por pasos: 1 entrega · 2 tus datos · 3 facturación electrónica (opcional) · 4 confirmar. Sirve para invitados y para quien tiene
 * cuenta (el formulario llega autocompletado desde su perfil). El servidor vuelve a validar todo (422 por campo).
 */
export default function CheckoutForm({ purchaseLines, subtotal, onShipping, onCreated }) {
  const { user, isAuthenticated } = useAuth()
  const [form, setForm] = useState(() => initialForm(user))
  const [step, setStep] = useState(1)
  const [errors, setErrors] = useState({})
  const [busy, setBusy] = useState(false)
  const [serverError, setServerError] = useState(null)
  const [quote, setQuote] = useState(null)
  const root = useRef(null)

  const company = form.person_type === 'Company'
  const pickup = form.delivery_method === 'pickup'
  const dvHint = company && digits(form.identification).length >= 3 ? nitCheckDigit(form.identification) : ''
  const states = useDepartments().list
  const billingCities = useMunicipalities(form.billing_state_code)
  const deliveryCities = useMunicipalities(form.delivery_state_code)
  const savedAddresses = user?.customer?.delivery_addresses ?? []

  // Costo de envío en vivo: depende del municipio de entrega y de si la compra llega al envío gratis
  useEffect(() => {
    if (pickup || !form.delivery_city_code) {
      setQuote(null)
      onShipping?.(0)
      return undefined
    }
    let alive = true
    shippingApi.quote(form.delivery_city_code, subtotal).then(
      (res) => { if (alive) { setQuote(res.data); onShipping?.(res.data.cost) } },
      () => { if (alive) setQuote(null) },
    )
    return () => { alive = false }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pickup, form.delivery_city_code, subtotal])

  // set('campo') maneja inputs y checkboxes; { reset } vacía el campo dependiente (el municipio al cambiar el departamento)
  const set = (name, { reset } = {}) => (e) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value
    setForm((f) => {
      const next = { ...f, [name]: value, ...(reset ? { [reset]: '' } : {}) }
      // El DV se calcula solo al escribir el NIT (se puede corregir a mano; el servidor lo valida igual)
      if (name === 'identification' && f.person_type === 'Company') next.check_digit = nitCheckDigit(value)
      return next
    })
    setErrors((er) => ({ ...er, [name]: undefined }))
  }

  const setType = (type) => setForm((f) => ({
    ...f,
    person_type: type,
    id_type: type === 'Company' ? '31' : f.id_type === '31' ? '13' : f.id_type,
    check_digit: type === 'Company' ? nitCheckDigit(f.identification) : '',
    vat_responsible: type === 'Company' ? true : f.vat_responsible,
  }))

  const useSaved = (index) => {
    const a = savedAddresses[Number(index)]
    if (!a) return
    setForm((f) => ({
      ...f, delivery_state_code: a.state_code, delivery_city_code: a.city_code, delivery_neighborhood: a.neighborhood ?? '', delivery_road: a.road ?? '',
      delivery_number: a.number ?? '', delivery_complement: a.complement ?? '', delivery_postal_code: a.postal_code ?? '', delivery_notes: a.notes ?? '',
    }))
    setErrors({})
  }

  const scrollTop = () => root.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })

  const validate = { 1: validateDelivery, 2: validateContact, 3: validateInvoice }
  const next = () => {
    const found = validate[step](form)
    setErrors(found)
    if (Object.keys(found).length === 0) {
      setStep(step + 1)
      scrollTop()
    }
  }
  const back = () => { setStep(step - 1); scrollTop() }

  const submit = async (e) => {
    e.preventDefault()
    if (step < 4) return next()
    setBusy(true)
    setServerError(null)
    try {
      const res = await checkoutApi.create(toPayload(form, purchaseLines.map((l) => ({ product_id: l.product.id, quantity: l.quantity }))), { auth: isAuthenticated })
      onCreated({ orders: res.data, meta: res.meta ?? {}, email: form.email.trim() })
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
  const cityName = (list, code) => list.find((c) => c.code === code)?.name ?? ''
  const stateName = (code) => states.find((d) => d.code === code)?.name ?? ''
  const shipping = pickup ? 0 : quote?.cost ?? 0
  const total = subtotal + shipping
  const idLabel = ID_TYPES.find((t) => t.value === (company ? '31' : form.id_type))?.label ?? ''

  return (
    <form className="co" onSubmit={submit} noValidate ref={root} id="cart-form" aria-label="Datos de compra">
      <ol className="co-steps" aria-label="Pasos de la compra">
        {STEPS.map((label, i) => (
          <li key={label} className={step === i + 1 ? 'is-on' : step > i + 1 ? 'is-done' : ''}>
            <button type="button" disabled={step <= i + 1} onClick={() => setStep(i + 1)} aria-current={step === i + 1 ? 'step' : undefined}>
              <span>{step > i + 1 ? <Check size={14} aria-hidden="true" /> : i + 1}</span><b>{label}</b>
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

      {/* 1 · Entrega */}
      {step === 1 && (
        <fieldset className="co-pane">
          <legend>¿Cómo lo recibes?</legend>
          <div className="co-opts co-opts--2" role="radiogroup" aria-label="Forma de entrega">
            <label className={pickup ? 'is-on' : ''}>
              <input type="radio" name="delivery" value="pickup" checked={pickup} onChange={set('delivery_method')} />
              <Store size={20} aria-hidden="true" />
              <span><strong>Recoger en tienda</strong><small>Sin costo · lo recoges cuando esté listo</small></span>
            </label>
            <label className={!pickup ? 'is-on' : ''}>
              <input type="radio" name="delivery" value="delivery" checked={!pickup} onChange={set('delivery_method')} />
              <Truck size={20} aria-hidden="true" />
              <span><strong>Envío a domicilio</strong><small>Local en el área metropolitana; nacional al resto del país</small></span>
            </label>
          </div>

          {pickup ? (
            <div className="co-store">
              <MapPin size={20} aria-hidden="true" />
              <div>
                <strong>{site.name}</strong>
                <p>{site.address}, {site.city}</p>
                <p className="co-muted">{site.hours ?? 'El horario de recogida lo confirma tu asesor cuando el pedido esté listo.'}</p>
              </div>
            </div>
          ) : (
            <>
              {savedAddresses.length > 0 && (
                <Field as="select" label="Usar una dirección guardada" defaultValue="" onChange={(ev) => useSaved(ev.target.value)}>
                  <option value="">Escribir otra dirección</option>
                  {savedAddresses.map((a, i) => <option key={i} value={i}>{[a.road, a.number, a.complement].filter(Boolean).join(' ')} · {a.neighborhood}</option>)}
                </Field>
              )}
              <div className="co-grid">
                <PlaceFields stateField="delivery_state_code" cityField="delivery_city_code" form={form} set={set} errors={errors} serverError={serverError} />
                <Field label="Barrio" value={form.delivery_neighborhood} onChange={set('delivery_neighborhood')} error={e('delivery_neighborhood')} autoComplete="address-level3" required />
                <Field label="Código postal (opcional)" value={form.delivery_postal_code} onChange={set('delivery_postal_code')} error={e('delivery_postal_code')} maxLength={10} autoComplete="postal-code" />
                <Field label="Vía (tipo y nombre)" value={form.delivery_road} onChange={set('delivery_road')} error={e('delivery_road')} placeholder="Carrera 27" required />
                <Field label="Número" value={form.delivery_number} onChange={set('delivery_number')} error={e('delivery_number')} placeholder="# 34-12" required />
                <Field label="Complemento (opcional)" value={form.delivery_complement} onChange={set('delivery_complement')} placeholder="Bodega 3, piso 2" wrap="co-span2" />
              </div>
              <Field as="textarea" rows={2} label="Indicaciones de entrega (opcional)" value={form.delivery_notes} onChange={set('delivery_notes')} maxLength={500} hint="Portería, horario, persona que recibe…" />
              {quote ? (
                <p className="co-ship" role="status">
                  <Truck size={18} aria-hidden="true" />
                  <span><strong>Envío {quote.method === 'local' ? 'local' : 'nacional'}</strong> · {quote.zone_label}: {quote.free ? <b>gratis</b> : <b>{formatCOP(quote.cost)}</b>}{quote.free ? ` (compras desde ${formatCOP(quote.free_from)})` : ` · gratis desde ${formatCOP(quote.free_from)}`}</span>
                </p>
              ) : (
                <p className="co-muted">Elige el municipio para ver el costo y el tipo de envío.</p>
              )}
            </>
          )}
          <Field as="textarea" rows={2} label="Notas del pedido (opcional)" value={form.notes} onChange={set('notes')} maxLength={1000} />
        </fieldset>
      )}

      {/* 2 · Tus datos */}
      {step === 2 && (
        <fieldset className="co-pane">
          <legend>Tus datos de contacto</legend>
          <p className="co-lead">Solo lo necesario para avisarte del pedido. {isAuthenticated ? 'Vienen de tu perfil.' : 'No necesitas cuenta.'}</p>
          <div className="co-grid">
            <Field label="Nombre completo" value={form.name} onChange={set('name')} error={e('name')} autoComplete="name" required wrap="co-span2" />
            <Field label="Celular" type="tel" inputMode="tel" value={form.phone} onChange={set('phone')} error={e('phone')} autoComplete="tel" required hint="Por aquí te llamamos o escribimos por WhatsApp." />
            <Field label="Correo" type="email" value={form.email} onChange={set('email')} error={e('email')} autoComplete="email" required hint="Aquí llega el resumen y el enlace de tu pedido." />
          </div>
        </fieldset>
      )}

      {/* 3 · Facturación electrónica */}
      {step === 3 && (
        <fieldset className="co-pane">
          <legend>Facturación electrónica</legend>
          <label className="co-switch">
            <input type="checkbox" role="switch" checked={form.needs_invoice} onChange={set('needs_invoice')} />
            <span className="co-switch__ui" aria-hidden="true" />
            <span><strong>Necesito factura a nombre de una persona o empresa</strong><small>Si no la necesitas, sigue al paso siguiente: no te pedimos documento.</small></span>
          </label>

          {form.needs_invoice && (
            <>
              <div className="co-seg" role="radiogroup" aria-label="Tipo de persona">
                <label className={!company ? 'is-on' : ''}><input type="radio" name="person_type" checked={!company} onChange={() => setType('Person')} /><User size={18} aria-hidden="true" /> Persona natural</label>
                <label className={company ? 'is-on' : ''}><input type="radio" name="person_type" checked={company} onChange={() => setType('Company')} /><Building2 size={18} aria-hidden="true" /> Empresa</label>
              </div>

              <div className="co-grid">
                {company ? (
                  <>
                    <Field label="NIT (sin dígito de verificación)" inputMode="numeric" value={form.identification} onChange={set('identification')} error={e('identification')} maxLength={13} required />
                    <Field label="Dígito de verificación" inputMode="numeric" value={form.check_digit} onChange={set('check_digit')} error={e('check_digit')} maxLength={1} required hint={dvHint ? `Calculado para ese NIT: ${dvHint}` : 'Se calcula al escribir el NIT.'} />
                    <Field label="Razón social" value={form.company_name} onChange={set('company_name')} error={e('company_name')} autoComplete="organization" maxLength={100} required />
                    <Field label="Nombre comercial (opcional)" value={form.commercial_name} onChange={set('commercial_name')} maxLength={100} />
                    <Field label="Nombres del contacto" value={form.contact_first_name} onChange={set('contact_first_name')} maxLength={50} hint="Por defecto, quien compra." />
                    <Field label="Apellidos del contacto" value={form.contact_last_name} onChange={set('contact_last_name')} maxLength={50} />
                  </>
                ) : (
                  <>
                    <Field as="select" label="Tipo de documento" value={form.id_type} onChange={set('id_type')} error={e('id_type')} required>
                      {ID_TYPES.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                    </Field>
                    <Field label="Número de documento" inputMode={form.id_type === '13' || form.id_type === '31' ? 'numeric' : 'text'} value={form.identification} onChange={set('identification')} error={e('identification')} maxLength={20} required />
                    <Field label="Nombres" value={form.first_name} onChange={set('first_name')} error={e('first_name')} autoComplete="given-name" maxLength={100} required />
                    <Field label="Apellidos" value={form.last_name} onChange={set('last_name')} error={e('last_name')} autoComplete="family-name" maxLength={100} required />
                    <Field label="Nombre comercial (opcional)" value={form.commercial_name} onChange={set('commercial_name')} maxLength={100} wrap="co-span2" />
                  </>
                )}
                <Field as="select" label="Responsabilidad fiscal" value={form.fiscal_responsibility} onChange={set('fiscal_responsibility')} error={e('fiscal_responsibilities')} required>
                  {FISCAL_CODES.map((o) => <option key={o.value} value={o.value}>{o.label} ({o.value})</option>)}
                </Field>
                <label className="check co-vat"><input type="checkbox" checked={form.vat_responsible} onChange={set('vat_responsible')} /><span>Responsable de IVA</span></label>
                <Field label="Correo de facturación (opcional)" type="email" value={form.billing_email} onChange={set('billing_email')} error={e('billing_email')} hint="Si lo dejas vacío usamos tu correo de contacto." wrap="co-span2" />
              </div>

              {!pickup && (
                <label className="check co-same">
                  <input type="checkbox" checked={form.billing_same_as_delivery} onChange={set('billing_same_as_delivery')} />
                  <span>Usar la misma dirección de entrega para la factura</span>
                </label>
              )}
              {(pickup || !form.billing_same_as_delivery) && (
                <div className="co-grid">
                  <Field label="Dirección de facturación" value={form.billing_address} onChange={set('billing_address')} error={e('billing_address')} autoComplete="street-address" maxLength={256} required wrap="co-span2" />
                  <PlaceFields stateField="billing_state_code" cityField="billing_city_code" form={form} set={set} errors={errors} serverError={serverError} />
                  <Field label="Código postal (opcional)" value={form.billing_postal_code} onChange={set('billing_postal_code')} error={e('billing_postal_code')} maxLength={10} />
                </div>
              )}
            </>
          )}
        </fieldset>
      )}

      {/* 4 · Confirmar */}
      {step === 4 && (
        <fieldset className="co-pane">
          <legend>Revisa y confirma</legend>
          <div className="co-recap">
            <section>
              <h3>Entrega <button type="button" onClick={() => setStep(1)}>Editar</button></h3>
              {pickup ? <p><strong>Recoger en tienda</strong> · {site.address}, {site.city}</p> : (
                <>
                  <p><strong>Envío {quote?.method === 'local' ? 'local' : 'nacional'}</strong> · {cityName(deliveryCities.list, form.delivery_city_code)}, {stateName(form.delivery_state_code)}</p>
                  <p>{[form.delivery_road, form.delivery_number, form.delivery_complement].filter(Boolean).join(' ')} · Barrio {form.delivery_neighborhood}</p>
                </>
              )}
            </section>
            <section>
              <h3>Contacto <button type="button" onClick={() => setStep(2)}>Editar</button></h3>
              <p><strong>{form.name}</strong></p>
              <p>{form.phone} · {form.email}</p>
            </section>
            <section className="co-recap__wide">
              <h3>Facturación <button type="button" onClick={() => setStep(3)}>Editar</button></h3>
              {form.needs_invoice ? (
                <>
                  <p><strong>{company ? form.company_name : `${form.first_name} ${form.last_name}`}</strong> · {company ? `NIT ${digits(form.identification)}-${form.check_digit}` : `${idLabel} ${form.identification}`}</p>
                  <p>{form.billing_email || form.email}{form.vat_responsible ? ' · Responsable de IVA' : ''}</p>
                  {!pickup && form.billing_same_as_delivery ? <p className="co-muted">En la dirección de entrega.</p> : <p>{form.billing_address}, {cityName(billingCities.list, form.billing_city_code)}, {stateName(form.billing_state_code)}</p>}
                </>
              ) : <p className="co-muted">Sin factura a nombre de persona o empresa.</p>}
            </section>
          </div>

          <dl className="co-totals">
            <div><dt>Productos (IVA incluido)</dt><dd>{formatCOP(subtotal)}</dd></div>
            <div><dt>Envío</dt><dd>{pickup ? 'Gratis (recoges en tienda)' : quote ? (quote.free ? 'Gratis' : formatCOP(quote.cost)) : 'Se confirma'}</dd></div>
            <div className="is-total"><dt>Total a pagar</dt><dd>{formatCOP(total)}</dd></div>
          </dl>

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
        {step < 4 ? (
          <Pill tone="o" type="submit" icon={ArrowRight}>Continuar</Pill>
        ) : (
          <Pill tone="o" type="submit" icon={Lock} disabled={busy || !form.privacy_accepted}>{busy ? 'Enviando…' : 'Confirmar pedido'}</Pill>
        )}
      </div>
    </form>
  )
}
