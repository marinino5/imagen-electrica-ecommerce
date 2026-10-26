const cop = new Intl.NumberFormat('es-CO', {
  style: 'currency',
  currency: 'COP',
  maximumFractionDigits: 0,
})

export const formatCOP = (value) => (value === null || value === undefined ? '—' : cop.format(value))

export const formatQty = (value) =>
  new Intl.NumberFormat('es-CO', { maximumFractionDigits: 2 }).format(value)

export const formatDate = (iso) =>
  iso
    ? new Intl.DateTimeFormat('es-CO', { dateStyle: 'medium', timeZone: 'America/Bogota' }).format(new Date(iso))
    : '—'

// specs usa claves como "potencia_hp" o "temperatura_max_c": la unidad es el último fragmento
const UNITS = {
  v: 'V', a: 'A', hp: 'HP', kw: 'kW', w: 'W', hz: 'Hz', rpm: 'rpm', m: 'm', mm: 'mm',
  c: '°C', awg: 'AWG', lm: 'lm', k: 'K', cfm: 'CFM', psi: 'psi', bar: 'bar', l: 'L', kg: 'kg',
}

export function formatSpec(key, value) {
  const parts = key.split('_')
  const last = parts[parts.length - 1]
  const hasUnit = parts.length > 1 && last in UNITS
  const words = (hasUnit ? parts.slice(0, -1) : parts).join(' ')
  const label = words.charAt(0).toUpperCase() + words.slice(1)
  const shown = typeof value === 'number' ? new Intl.NumberFormat('es-CO').format(value) : String(value)
  return { label, value: hasUnit ? `${shown} ${UNITS[last]}` : shown }
}
