// Lógica del formulario de compra, sin React. Cuatro pasos: 1 entrega · 2 tus datos · 3 facturación electrónica (opcional) · 4 confirmar.
// Los campos de la factura llevan los nombres y códigos de la API de clientes de Siigo Nube (developers.siigo.com).

/** Versión de la política de tratamiento de datos que se muestra al comprar. Va igual que `shop.privacy_policy_version` del backend. */
export const PRIVACY_VERSION = '2026-11-v1'

/** Tipos de documento: el valor es el `id_type` de Siigo. */
export const ID_TYPES = [
  { value: '13', label: 'Cédula de ciudadanía' },
  { value: '31', label: 'NIT' },
  { value: '22', label: 'Cédula de extranjería' },
  { value: '41', label: 'Pasaporte' },
]

/** Responsabilidades fiscales: el valor es el `code` de `fiscal_responsibilities` de Siigo. */
export const FISCAL_CODES = [
  { value: 'R-99-PN', label: 'No aplica / otros' },
  { value: 'O-13', label: 'Gran contribuyente' },
  { value: 'O-15', label: 'Autorretenedor' },
  { value: 'O-23', label: 'Agente de retención de IVA' },
  { value: 'O-47', label: 'Régimen simple de tributación' },
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

const req = (value) => String(value ?? '').trim() !== ''

/** «Laura Gómez Pérez» → ['Laura', 'Gómez Pérez'] (con cuatro palabras o más, los dos primeros son los nombres). */
export function splitName(full) {
  const words = String(full ?? '').trim().split(/\s+/).filter(Boolean)
  const cut = words.length >= 4 ? 2 : 1
  return [words.slice(0, cut).join(' '), words.slice(cut).join(' ')]
}

/** Datos guardados del cliente → valores iniciales del formulario (para autocompletar). */
export function initialForm(user) {
  const c = user?.customer
  const company = c?.type === 'company'
  const [nitNumber, nitDv] = company && c?.nit ? String(c.nit).split('-') : [c?.nit ?? '', '']
  const saved = c?.delivery_addresses?.[0]
  const hasInvoiceData = Boolean(c?.id_type)
  const [first, last] = splitName(c?.contact_name ?? user?.name)

  return {
    // 1 · Entrega
    delivery_method: 'pickup',
    delivery_state_code: saved?.state_code ?? '',
    delivery_city_code: saved?.city_code ?? '',
    delivery_neighborhood: saved?.neighborhood ?? '',
    delivery_road: saved?.road ?? '',
    delivery_number: saved?.number ?? '',
    delivery_complement: saved?.complement ?? '',
    delivery_postal_code: saved?.postal_code ?? '',
    delivery_notes: '',
    notes: '',
    // 2 · Tus datos
    name: c?.contact_name ?? user?.name ?? '',
    phone: c?.phone ?? '',
    email: user?.email ?? '',
    // 3 · Facturación electrónica
    needs_invoice: hasInvoiceData,
    person_type: company ? 'Company' : 'Person',
    id_type: company ? '31' : c?.id_type ?? '13',
    identification: digits(nitNumber) || (c?.id_type === '41' || c?.id_type === '22' ? c?.nit ?? '' : ''),
    check_digit: company ? nitDv || c?.check_digit || '' : '',
    first_name: c?.first_name ?? (company ? '' : first),
    last_name: c?.last_name ?? (company ? '' : last),
    company_name: c?.company_name ?? '',
    commercial_name: c?.commercial_name ?? '',
    vat_responsible: c?.vat_responsible ?? false,
    fiscal_responsibility: c?.fiscal_responsibilities?.[0] ?? 'R-99-PN',
    billing_email: c?.billing_email ?? '',
    billing_same_as_delivery: !hasInvoiceData,
    billing_address: c?.address ?? '',
    billing_state_code: c?.state_code ?? '',
    billing_city_code: c?.city_code ?? '',
    billing_postal_code: c?.postal_code ?? '',
    contact_first_name: company ? first : '',
    contact_last_name: company ? last : '',
    // 4 · Confirmar
    privacy_accepted: false,
    save_to_profile: true,
    website: '',
  }
}

/** Paso 1 · Entrega. Recoger en tienda no pide dirección. */
export function validateDelivery(f) {
  const e = {}
  if (f.delivery_method === 'pickup') return e
  if (!req(f.delivery_state_code)) e.delivery_state_code = 'Elige el departamento.'
  if (!req(f.delivery_city_code)) e.delivery_city_code = 'Elige el municipio.'
  if (!req(f.delivery_neighborhood)) e.delivery_neighborhood = 'Escribe el barrio.'
  if (!req(f.delivery_road)) e.delivery_road = 'Escribe la vía (ej. Carrera 27).'
  if (!req(f.delivery_number)) e.delivery_number = 'Escribe el número (ej. # 34-12).'
  if (f.delivery_postal_code && !/^[A-Za-z0-9]{1,10}$/.test(f.delivery_postal_code)) e.delivery_postal_code = 'El código postal lleva solo letras y números (hasta 10).'
  return e
}

/** Paso 2 · Tus datos: nombre, celular y correo. */
export function validateContact(f) {
  const e = {}
  if (String(f.name).trim().length < 3) e.name = 'Escribe tu nombre completo.'
  if (!/^(\+57)?3\d{9}$/.test(String(f.phone).replace(/[^\d+]/g, ''))) e.phone = 'Escribe un celular colombiano de 10 dígitos (empieza por 3).'
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(String(f.email).trim())) e.email = 'Escribe un correo válido.'
  return e
}

/** Paso 3 · Facturación electrónica. Sin el interruptor no se pide nada. */
export function validateInvoice(f) {
  const e = {}
  if (!f.needs_invoice) return e
  const company = f.person_type === 'Company'
  const numeric = company || f.id_type === '13' || f.id_type === '31'
  if (!company && !req(f.id_type)) e.id_type = 'Elige el tipo de documento.'
  if (!req(f.identification)) e.identification = company ? 'Escribe el NIT (sin dígito de verificación).' : 'Escribe el número de documento.'
  else if (numeric && !/^\d{3,13}$/.test(digits(f.identification))) e.identification = 'Usa solo números (de 3 a 13 dígitos).'
  else if (!numeric && !/^[A-Za-z0-9]{1,20}$/.test(f.identification)) e.identification = 'Usa solo letras y números (hasta 20).'
  if (company) {
    if (!req(f.check_digit)) e.check_digit = 'Escribe el dígito de verificación.'
    else if (!e.identification && f.check_digit !== nitCheckDigit(f.identification)) e.check_digit = `No corresponde a ese NIT: el dígito de verificación es ${nitCheckDigit(f.identification)}.`
    if (!req(f.company_name)) e.company_name = 'Escribe la razón social.'
  } else {
    if (!req(f.first_name)) e.first_name = 'Escribe los nombres.'
    if (!req(f.last_name)) e.last_name = 'Escribe los apellidos.'
  }
  if (req(f.billing_email) && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(String(f.billing_email).trim())) e.billing_email = 'Escribe un correo válido.'
  const same = f.delivery_method !== 'pickup' && f.billing_same_as_delivery
  if (!same) {
    if (!req(f.billing_address)) e.billing_address = 'Escribe la dirección de facturación.'
    if (!req(f.billing_state_code)) e.billing_state_code = 'Elige el departamento.'
    if (!req(f.billing_city_code)) e.billing_city_code = 'Elige el municipio.'
  }
  if (f.billing_postal_code && !/^[A-Za-z0-9]{1,10}$/.test(f.billing_postal_code)) e.billing_postal_code = 'El código postal lleva solo letras y números (hasta 10).'
  return e
}

const STEP_FIELDS = {
  1: ['delivery_method', 'delivery_state_code', 'delivery_city_code', 'delivery_neighborhood', 'delivery_road', 'delivery_number', 'delivery_complement', 'delivery_postal_code', 'delivery_notes', 'notes'],
  2: ['name', 'phone', 'email'],
  3: ['needs_invoice', 'person_type', 'id_type', 'identification', 'check_digit', 'first_name', 'last_name', 'company_name', 'commercial_name', 'vat_responsible', 'fiscal_responsibilities', 'billing_email', 'billing_same_as_delivery', 'billing_address', 'billing_state_code', 'billing_city_code', 'billing_postal_code', 'contact_first_name', 'contact_last_name'],
}

/** Paso al que pertenece el primer campo con error del servidor (para llevar a la persona a corregirlo). */
export function stepOfError(fields) {
  for (const step of [1, 2, 3]) if (fields.some((k) => STEP_FIELDS[step].some((s) => k === s || k.startsWith(`${s}.`)))) return step
  return 4
}

/** Valores del formulario → cuerpo de POST /checkout. */
export function toPayload(f, items) {
  const company = f.person_type === 'Company'
  const delivers = f.delivery_method !== 'pickup'
  const invoice = f.needs_invoice
  const same = invoice && delivers && f.billing_same_as_delivery
  const clean = (v) => (typeof v === 'string' ? v.trim() : v)
  const opt = (v) => clean(v) || undefined

  return {
    items,
    delivery_method: f.delivery_method,
    delivery_state_code: delivers ? f.delivery_state_code : undefined,
    delivery_city_code: delivers ? f.delivery_city_code : undefined,
    delivery_neighborhood: delivers ? clean(f.delivery_neighborhood) : undefined,
    delivery_road: delivers ? clean(f.delivery_road) : undefined,
    delivery_number: delivers ? clean(f.delivery_number) : undefined,
    delivery_complement: delivers ? opt(f.delivery_complement) : undefined,
    delivery_postal_code: delivers ? opt(f.delivery_postal_code) : undefined,
    delivery_notes: delivers ? opt(f.delivery_notes) : undefined,
    notes: opt(f.notes),
    name: clean(f.name),
    phone: clean(f.phone),
    email: clean(f.email),
    needs_invoice: invoice,
    person_type: invoice ? f.person_type : undefined,
    id_type: invoice ? (company ? '31' : f.id_type) : undefined,
    identification: invoice ? clean(f.identification) : undefined,
    check_digit: invoice && company ? f.check_digit : undefined,
    first_name: invoice && !company ? clean(f.first_name) : undefined,
    last_name: invoice && !company ? clean(f.last_name) : undefined,
    company_name: invoice && company ? clean(f.company_name) : undefined,
    commercial_name: invoice ? opt(f.commercial_name) : undefined,
    vat_responsible: invoice ? f.vat_responsible : undefined,
    fiscal_responsibilities: invoice ? [f.fiscal_responsibility] : undefined,
    billing_email: invoice ? opt(f.billing_email) : undefined,
    billing_same_as_delivery: invoice && delivers ? f.billing_same_as_delivery : undefined,
    billing_address: invoice && !same ? clean(f.billing_address) : undefined,
    billing_state_code: invoice && !same ? f.billing_state_code : undefined,
    billing_city_code: invoice && !same ? f.billing_city_code : undefined,
    billing_postal_code: invoice && !same ? opt(f.billing_postal_code) : undefined,
    contact_first_name: invoice && company ? opt(f.contact_first_name) : undefined,
    contact_last_name: invoice && company ? opt(f.contact_last_name) : undefined,
    privacy_accepted: f.privacy_accepted,
    save_to_profile: f.save_to_profile,
    website: f.website,
  }
}
