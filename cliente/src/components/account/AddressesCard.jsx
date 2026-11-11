import { useState } from 'react'
import { MapPin, Pencil, Plus, Trash2 } from 'lucide-react'
import { useAuth } from '../../context/AuthContext'
import { useFields } from '../../hooks/useFields'
import { useDepartments } from '../../hooks/useLocations'
import { validateDelivery } from '../../lib/checkout'
import Bezel from '../ui/Bezel'
import Field from '../ui/Field'
import PlaceFields from '../forms/PlaceFields'
import data from '../../data/divipola.json'

const MAX = 5
const blank = { delivery_state_code: '', delivery_city_code: '', delivery_neighborhood: '', delivery_road: '', delivery_number: '', delivery_complement: '', delivery_postal_code: '', delivery_notes: '' }

// Dirección guardada (state_code…) ⇄ campos del formulario de entrega (delivery_state_code…): los mismos del paso 1 del pago
const toForm = (a) => ({ delivery_state_code: a.state_code, delivery_city_code: a.city_code, delivery_neighborhood: a.neighborhood ?? '', delivery_road: a.road ?? '', delivery_number: a.number ?? '', delivery_complement: a.complement ?? '', delivery_postal_code: a.postal_code ?? '', delivery_notes: a.notes ?? '' })
const toAddress = (f) => ({ state_code: f.delivery_state_code, city_code: f.delivery_city_code, neighborhood: f.delivery_neighborhood.trim(), road: f.delivery_road.trim(), number: f.delivery_number.trim(), complement: f.delivery_complement.trim() || null, postal_code: f.delivery_postal_code.trim() || null, notes: f.delivery_notes.trim() || null })
const cityName = (a) => data.find((d) => d.code === a.state_code)?.cities.find((c) => c.code === a.city_code)?.name ?? ''

/** Direcciones de entrega guardadas (hasta 5): se agregan, editan y borran aquí y autocompletan el paso de entrega del pago. */
export default function AddressesCard({ customer }) {
  const { updateAddresses } = useAuth()
  const list = customer.delivery_addresses ?? []
  const { form, setForm, errors, setErrors, set } = useFields(blank)
  const [index, setIndex] = useState(null) // null = cerrado · -1 = nueva · n = editando la n-ésima
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)
  useDepartments() // precarga la lista de ubicaciones

  const open = (i) => {
    setForm(i >= 0 ? toForm(list[i]) : blank)
    setErrors({})
    setError(null)
    setIndex(i)
  }

  const persist = async (next) => {
    setBusy(true)
    setError(null)
    try {
      await updateAddresses(next)
      setIndex(null)
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  const submit = (e) => {
    e.preventDefault()
    const found = validateDelivery({ ...form, delivery_method: 'delivery' })
    setErrors(found)
    if (Object.keys(found).length > 0) return
    persist(index >= 0 ? list.map((a, i) => (i === index ? toAddress(form) : a)) : [...list, toAddress(form)])
  }

  return (
    <Bezel as="section" className="acc-card" aria-labelledby="h-dir"><div className="acc-card__core">
      <div className="profile-card__head">
        <h2 id="h-dir" className="block-title"><MapPin size={20} aria-hidden="true" /> Direcciones guardadas</h2>
        {index === null && list.length < MAX && <button type="button" className="btn btn--ghost btn--sm" onClick={() => open(-1)}><Plus size={16} aria-hidden="true" /> Agregar</button>}
      </div>

      {error && index === null && <div className="alert alert--error" role="alert">{error.message}</div>}

      {list.length === 0 && index === null && <p className="muted-box">Aún no guardas direcciones. Agrega una y el pago con envío a domicilio la ofrecerá para elegirla en un clic.</p>}

      {list.length > 0 && (
        <ul className="acc-addr">
          {list.map((a, i) => (
            <li key={`${a.city_code}-${a.road}-${a.number}-${i}`}>
              <span>
                <strong>{[a.road, a.number, a.complement].filter(Boolean).join(' ')}</strong>
                <small>{a.neighborhood} · {cityName(a)}</small>
              </span>
              <span className="acc-addr__act">
                <button type="button" aria-label={`Editar la dirección ${a.road} ${a.number}`} onClick={() => open(i)} disabled={busy}><Pencil size={16} aria-hidden="true" /></button>
                <button type="button" aria-label={`Borrar la dirección ${a.road} ${a.number}`} onClick={() => persist(list.filter((_, n) => n !== i))} disabled={busy}><Trash2 size={16} aria-hidden="true" /></button>
              </span>
            </li>
          ))}
        </ul>
      )}

      {index !== null && (
        <form className="acc-form" onSubmit={submit} noValidate>
          <h3 className="acc-form__title">{index >= 0 ? 'Editar dirección' : 'Nueva dirección'}</h3>
          {error && <div className="alert alert--error" role="alert">{error.message}</div>}
          <div className="co-grid">
            <PlaceFields stateField="delivery_state_code" cityField="delivery_city_code" form={form} set={set} errors={errors} serverError={error} />
            <Field label="Barrio" value={form.delivery_neighborhood} onChange={set('delivery_neighborhood')} error={errors.delivery_neighborhood} required />
            <Field label="Código postal (opcional)" value={form.delivery_postal_code} onChange={set('delivery_postal_code')} error={errors.delivery_postal_code} maxLength={10} />
            <Field label="Vía (tipo y nombre)" value={form.delivery_road} onChange={set('delivery_road')} error={errors.delivery_road} placeholder="Carrera 27" required />
            <Field label="Número" value={form.delivery_number} onChange={set('delivery_number')} error={errors.delivery_number} placeholder="# 34-12" required />
            <Field label="Complemento (opcional)" value={form.delivery_complement} onChange={set('delivery_complement')} placeholder="Bodega 3, piso 2" wrap="co-span2" />
          </div>
          <div className="row-actions">
            <button type="submit" className="btn btn--accent" disabled={busy}>{busy ? 'Guardando…' : 'Guardar dirección'}</button>
            <button type="button" className="btn btn--ghost" onClick={() => setIndex(null)}>Cancelar</button>
          </div>
        </form>
      )}
      {list.length >= MAX && index === null && <p className="field-hint">Llegaste al máximo de {MAX} direcciones: borra una para agregar otra.</p>}
    </div></Bezel>
  )
}
