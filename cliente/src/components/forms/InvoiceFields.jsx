import { Building2, User } from 'lucide-react'
import { FISCAL_CODES, ID_TYPES, digits, nitCheckDigit, withPersonType } from '../../lib/checkout'
import Field from '../ui/Field'
import PlaceFields from './PlaceFields'
import '../../styles/checkout.css'

/** Interruptor «necesito factura»: lo comparten el pago, Crear cuenta y Mi cuenta (cada uno pone su texto). */
export function InvoiceSwitch({ checked, onChange, title, hint }) {
  return (
    <label className="co-switch">
      <input type="checkbox" role="switch" checked={checked} onChange={onChange} />
      <span className="co-switch__ui" aria-hidden="true" />
      <span><strong>{title}</strong><small>{hint}</small></span>
    </label>
  )
}

/**
 * Datos de facturación electrónica con los nombres y códigos de la API de clientes de Siigo. Son los mismos del paso 3 del pago,
 * de Crear cuenta y de «Datos de facturación» en Mi cuenta: una sola pieza para que no se desalineen.
 * `canUseDelivery` muestra «usar la misma dirección de entrega» (solo en el pago con envío a domicilio).
 */
export default function InvoiceFields({ form, setForm, set, errors, serverError, canUseDelivery = false }) {
  const company = form.person_type === 'Company'
  const dvHint = company && digits(form.identification).length >= 3 ? nitCheckDigit(form.identification) : ''
  // «nit» es el nombre que usa el servidor al rechazar un documento repetido (ya hay una cuenta con ese NIT o cédula)
  const e = (k) => errors[k] ?? serverError?.fieldError?.(k) ?? (k === 'identification' ? serverError?.fieldError?.('nit') : undefined)
  const own = !(canUseDelivery && form.billing_same_as_delivery)

  return (
    <>
      <div className="co-seg" role="radiogroup" aria-label="Tipo de persona">
        <label className={!company ? 'is-on' : ''}><input type="radio" name="person_type" checked={!company} onChange={() => setForm((f) => withPersonType(f, 'Person'))} /><User size={18} aria-hidden="true" /> Persona natural</label>
        <label className={company ? 'is-on' : ''}><input type="radio" name="person_type" checked={company} onChange={() => setForm((f) => withPersonType(f, 'Company'))} /><Building2 size={18} aria-hidden="true" /> Empresa</label>
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
        <Field label="Correo de facturación (opcional)" type="email" value={form.billing_email} onChange={set('billing_email')} error={e('billing_email')} hint="Si lo dejas vacío usamos el correo de contacto." wrap="co-span2" />
      </div>

      {canUseDelivery && (
        <label className="check co-same">
          <input type="checkbox" checked={form.billing_same_as_delivery} onChange={set('billing_same_as_delivery')} />
          <span>Usar la misma dirección de entrega para la factura</span>
        </label>
      )}
      {own && (
        <div className="co-grid">
          <Field label="Dirección de facturación" value={form.billing_address} onChange={set('billing_address')} error={e('billing_address')} autoComplete="street-address" maxLength={256} required wrap="co-span2" />
          <PlaceFields stateField="billing_state_code" cityField="billing_city_code" form={form} set={set} errors={errors} serverError={serverError} />
          <Field label="Código postal (opcional)" value={form.billing_postal_code} onChange={set('billing_postal_code')} error={e('billing_postal_code')} maxLength={10} />
        </div>
      )}
    </>
  )
}
