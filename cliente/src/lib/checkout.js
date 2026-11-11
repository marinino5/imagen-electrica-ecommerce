// Lógica del formulario de compra (facturación electrónica DIAN y entrega). Sin React: se usa desde el formulario y se puede probar sola.

/** Versión de la política de tratamiento de datos que se muestra al comprar. Va igual que `shop.privacy_policy_version` del backend. */
export const PRIVACY_VERSION = '2026-11-v1'

export const DOC_TYPES = [
  { value: 'CC', label: 'Cédula de ciudadanía' },
  { value: 'CE', label: 'Cédula de extranjería' },
  { value: 'PP', label: 'Pasaporte' },
  { value: 'NIT', label: 'NIT' },
]

export const FISCAL = [
  { value: 'vat_responsible', label: 'Responsable de IVA' },
  { value: 'not_vat_responsible', label: 'No responsable de IVA' },
  { value: 'large_taxpayer', label: 'Gran contribuyente' },
  { value: 'self_withholder', label: 'Autorretenedor' },
  { value: 'simple_regime', label: 'Régimen simple de tributación' },
]

export const DELIVERY = [
  { value: 'pickup', label: 'Recoger en tienda', note: 'Sin costo · Cra 18 No 24-32, Bucaramanga' },
  { value: 'local', label: 'Envío local', note: 'Bucaramanga y área metropolitana · lo confirma tu asesor' },
  { value: 'national', label: 'Envío nacional', note: 'A todo el país · lo confirma tu asesor' },
]

const WEIGHTS = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71]

export const digits = (value) => String(value ?? '').replace(/\D+/g, '')

/** Dígito de verificación del NIT (módulo 11 con los pesos de la DIAN). Vacío si aún no hay número. */
export function nitCheckDigit(number) {
  const list = digits(number).split('').reverse()
  if (list.length === 0) return ''
  const sum = list.reduce((acc, d, i) => acc + Number(d) * (WEIGHTS[i] ?? 0), 0)
  const rest = sum % 11
  return String(rest > 1 ? 11 - rest : rest)
}

/** Datos guardados del cliente → valores iniciales del formulario (para autocompletar). */
export function initialForm(user) {
  const c = user?.customer
  const company = c?.type === 'company'
  const [nitNumber, nitDv] = company && c?.nit ? String(c.nit).split('-') : [c?.nit ?? '', '']
  const names = String(c?.contact_name ?? user?.name ?? '').trim().split(/\s+/)
  return {
    person_type: company ? 'company' : 'natural',
    doc_type: company ? 'NIT' : c?.doc_type ?? 'CC',
    doc_number: digits(nitNumber) || (c?.doc_type === 'PP' ? c?.nit ?? '' : ''),
    nit_dv: company ? nitDv || c?.doc_dv || '' : '',
    first_name: c?.first_name ?? (company ? '' : names.slice(0, Math.ceil(names.length / 2)).join(' ')),
    last_name: c?.last_name ?? (company ? '' : names.slice(Math.ceil(names.length / 2)).join(' ')),
    company_name: c?.company_name ?? '',
    fiscal_responsibility: c?.fiscal_responsibility ?? 'vat_responsible',
    contact_name: company ? c?.contact_name ?? user?.name ?? '' : '',
    billing_email: c?.billing_email ?? user?.email ?? '',
    phone: c?.phone ?? '',
    billing_address: c?.address ?? '',
    department_code: c?.department_code ?? '',
    city_code: c?.city_code ?? '',
    delivery_method: 'pickup',
    delivery_same_as_billing: true,
    delivery_department_code: '',
    delivery_city_code: '',
    delivery_neighborhood: '',
    delivery_road: '',
    delivery_number: '',
    delivery_complement: '',
    delivery_postal_code: '',
    delivery_notes: '',
    notes: '',
    privacy_accepted: false,
    save_to_profile: true,
    website: '',
  }
}

const req = (value) => String(value ?? '').trim() !== ''

/** Errores del paso 1 (facturación): { campo: mensaje }. Vacío = se puede avanzar. El servidor vuelve a validar todo. */
export function validateBilling(f) {
  const e = {}
  const company = f.person_type === 'company'
  if (!company && !req(f.doc_type)) e.doc_type = 'Elige el tipo de documento.'
  if (!req(f.doc_number)) e.doc_number = company ? 'Escribe el NIT (sin dígito de verificación).' : 'Escribe el número de documento.'
  else if (company || f.doc_type !== 'PP') {
    if (!/^\d{5,12}$/.test(digits(f.doc_number))) e.doc_number = 'Usa solo números (de 5 a 12 dígitos).'
  } else if (!/^[A-Za-z0-9]{5,16}$/.test(f.doc_number)) e.doc_number = 'El pasaporte lleva de 5 a 16 letras o números.'
  if (company) {
    if (!req(f.nit_dv)) e.nit_dv = 'Escribe el dígito de verificación.'
    else if (!e.doc_number && f.nit_dv !== nitCheckDigit(f.doc_number)) e.nit_dv = `No corresponde a ese NIT: el dígito de verificación es ${nitCheckDigit(f.doc_number)}.`
    if (!req(f.company_name)) e.company_name = 'Escribe la razón social.'
    if (!req(f.fiscal_responsibility)) e.fiscal_responsibility = 'Elige la responsabilidad fiscal.'
    if (!req(f.contact_name)) e.contact_name = 'Escribe la persona de contacto.'
  } else {
    if (!req(f.first_name)) e.first_name = 'Escribe tus nombres.'
    if (!req(f.last_name)) e.last_name = 'Escribe tus apellidos.'
  }
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(f.billing_email.trim())) e.billing_email = 'Escribe un correo válido.'
  if (!/^(\+57)?3\d{9}$/.test(String(f.phone).replace(/[^\d+]/g, ''))) e.phone = 'Escribe un celular colombiano de 10 dígitos (empieza por 3).'
  if (!req(f.billing_address)) e.billing_address = 'Escribe la dirección.'
  if (!req(f.department_code)) e.department_code = 'Elige el departamento.'
  if (!req(f.city_code)) e.city_code = 'Elige el municipio.'
  return e
}

/** Errores del paso 2 (entrega). Recoger en tienda no pide dirección. */
export function validateDelivery(f) {
  const e = {}
  if (f.delivery_method === 'pickup' || f.delivery_same_as_billing) return e
  if (!req(f.delivery_department_code)) e.delivery_department_code = 'Elige el departamento.'
  if (!req(f.delivery_city_code)) e.delivery_city_code = 'Elige el municipio.'
  if (!req(f.delivery_neighborhood)) e.delivery_neighborhood = 'Escribe el barrio.'
  if (!req(f.delivery_road)) e.delivery_road = 'Escribe la vía (ej. Carrera 27).'
  if (!req(f.delivery_number)) e.delivery_number = 'Escribe el número (ej. # 34-12).'
  if (f.delivery_postal_code && !/^\d{6}$/.test(f.delivery_postal_code)) e.delivery_postal_code = 'El código postal tiene 6 números.'
  return e
}

const STEP_FIELDS = {
  1: ['person_type', 'doc_type', 'doc_number', 'nit_dv', 'first_name', 'last_name', 'company_name', 'fiscal_responsibility', 'contact_name', 'billing_email', 'phone', 'billing_address', 'department_code', 'city_code'],
  2: ['delivery_method', 'delivery_same_as_billing', 'delivery_department_code', 'delivery_city_code', 'delivery_neighborhood', 'delivery_road', 'delivery_number', 'delivery_complement', 'delivery_postal_code', 'delivery_notes', 'notes'],
}

/** Paso al que pertenece el primer campo con error del servidor (para llevar a la persona a corregirlo). */
export function stepOfError(fields) {
  for (const step of [1, 2]) if (fields.some((k) => STEP_FIELDS[step].includes(k))) return step
  return 3
}

/** Valores del formulario → cuerpo de POST /checkout. */
export function toPayload(f, items) {
  const company = f.person_type === 'company'
  const pickup = f.delivery_method === 'pickup'
  const own = !pickup && !f.delivery_same_as_billing
  const clean = (v) => (typeof v === 'string' ? v.trim() : v)
  return {
    items,
    person_type: f.person_type,
    doc_type: company ? 'NIT' : f.doc_type,
    doc_number: company ? digits(f.doc_number) : clean(f.doc_number),
    nit_dv: company ? f.nit_dv : undefined,
    first_name: company ? undefined : clean(f.first_name),
    last_name: company ? undefined : clean(f.last_name),
    company_name: company ? clean(f.company_name) : undefined,
    fiscal_responsibility: company ? f.fiscal_responsibility : undefined,
    contact_name: company ? clean(f.contact_name) : undefined,
    billing_email: clean(f.billing_email),
    phone: clean(f.phone),
    billing_address: clean(f.billing_address),
    department_code: f.department_code,
    city_code: f.city_code,
    delivery_method: f.delivery_method,
    delivery_same_as_billing: pickup ? undefined : f.delivery_same_as_billing,
    delivery_department_code: own ? f.delivery_department_code : undefined,
    delivery_city_code: own ? f.delivery_city_code : undefined,
    delivery_neighborhood: own ? clean(f.delivery_neighborhood) : undefined,
    delivery_road: own ? clean(f.delivery_road) : undefined,
    delivery_number: own ? clean(f.delivery_number) : undefined,
    delivery_complement: own ? clean(f.delivery_complement) || undefined : undefined,
    delivery_postal_code: pickup ? undefined : clean(f.delivery_postal_code) || undefined,
    delivery_notes: pickup ? undefined : clean(f.delivery_notes) || undefined,
    notes: clean(f.notes) || undefined,
    privacy_accepted: f.privacy_accepted,
    save_to_profile: f.save_to_profile,
    website: f.website,
  }
}
