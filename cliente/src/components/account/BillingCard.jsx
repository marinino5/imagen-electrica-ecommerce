import { useState } from 'react'
import { Pencil, ReceiptText } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import { useFields } from '../../hooks/useFields'
import { useDepartments, useMunicipalities } from '../../hooks/useLocations'
import { FISCAL_CODES, ID_TYPES, initialForm, toInvoicePayload, validateInvoice } from '../../lib/checkout'
import Bezel from '../ui/Bezel'
import Pill from '../ui/Pill'
import InvoiceFields, { InvoiceSwitch } from '../forms/InvoiceFields'

// Formulario de edición: parte de los datos guardados (los mismos que autocompletan el paso 3 del pago)
const startForm = (user) => ({ ...initialForm(user), delivery_method: 'pickup', billing_same_as_delivery: false })

/** Datos de facturación guardados (nombres de Siigo). Se editan con los mismos campos del pago; apagar el interruptor los borra. */
export default function BillingCard({ user, customer }) {
  const { updateBilling } = useAuth()
  const { form, setForm, errors, setErrors, set } = useFields(() => startForm(user))
  const [editing, setEditing] = useState(false)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)
  const [saved, setSaved] = useState(false)
  const states = useDepartments().list
  const cities = useMunicipalities(customer.state_code).list

  const has = Boolean(customer.id_type)
  const company = customer.type === 'company'
  const idLabel = ID_TYPES.find((t) => t.value === customer.id_type)?.label
  const fiscal = FISCAL_CODES.find((c) => c.value === customer.fiscal_responsibilities?.[0])
  const place = [cities.find((c) => c.code === customer.city_code)?.name, states.find((d) => d.code === customer.state_code)?.name].filter(Boolean).join(', ')
  const name = company ? customer.company_name : [customer.first_name, customer.last_name].filter(Boolean).join(' ')

  const open = () => {
    setForm({ ...startForm(user), needs_invoice: true })
    setErrors({})
    setError(null)
    setSaved(false)
    setEditing(true)
  }

  const submit = async (e) => {
    e.preventDefault()
    const found = validateInvoice(form)
    setErrors(found)
    if (Object.keys(found).length > 0) return
    setBusy(true)
    setError(null)
    try {
      await updateBilling(toInvoicePayload(form))
      setEditing(false)
      setSaved(true)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  if (!editing) {
    return (
      <Bezel as="section" className="acc-card" aria-labelledby="h-fact"><div className="acc-card__core">
        <div className="profile-card__head">
          <h2 id="h-fact" className="block-title"><ReceiptText size={20} aria-hidden="true" /> Datos de facturación</h2>
          <Pill tone="l" size="sm" icon={Pencil} onClick={open}>{has ? 'Editar' : 'Agregar'}</Pill>
        </div>
        {saved && <p className="alert alert--success" role="status">{has ? 'Datos de facturación guardados.' : 'Datos de facturación borrados.'}</p>}
        {has ? (
          <dl className="profile">
            <div><dt>{company ? 'Razón social' : 'Nombre'}</dt><dd>{name}</dd></div>
            <div>
              <dt>{company ? 'NIT' : idLabel}</dt>
              <dd>{company ? customer.nit : (customer.nit ?? '')}{' '}<span className="acc-tag">{company ? 'Empresa' : 'Persona natural'}</span></dd>
            </div>
            {fiscal && <div><dt>Responsabilidad fiscal</dt><dd>{fiscal.label} ({fiscal.value}){customer.vat_responsible ? ' · Responsable de IVA' : ''}</dd></div>}
            {customer.billing_email && <div><dt>Correo de facturación</dt><dd>{customer.billing_email}</dd></div>}
            {customer.address && <div><dt>Dirección</dt><dd>{customer.address}{place ? ` · ${place}` : ''}</dd></div>}
          </dl>
        ) : (
          <p className="muted-box">Aún no guardas datos de facturación. Agrégalos una vez y el pago los usará solo; si no necesitas factura, déjalo así.</p>
        )}
      </div>
      </Bezel>
    )
  }

  return (
    <Bezel as="div" className="acc-card"><form className="acc-card__core acc-form" onSubmit={submit} noValidate>
      <h2 className="block-title"><ReceiptText size={20} aria-hidden="true" /> Datos de facturación</h2>
      {error && <div className="alert alert--error" role="alert">{error.message}</div>}
      <InvoiceSwitch checked={form.needs_invoice} onChange={set('needs_invoice')} title="Guardar datos de facturación" hint="Apágalo y guarda para borrar los datos que tienes guardados." />
      {form.needs_invoice && <InvoiceFields form={form} setForm={setForm} set={set} errors={errors} serverError={error} />}
      <div className="row-actions">
        <button type="submit" className="btn btn--accent" disabled={busy}>{busy ? 'Guardando…' : 'Guardar'}</button>
        <button type="button" className="btn btn--ghost" onClick={() => setEditing(false)}>Cancelar</button>
      </div>
    </form></Bezel>
  )
}
