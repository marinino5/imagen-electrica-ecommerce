import { useDepartments, useMunicipalities } from '../../hooks/useLocations'
import Field from '../ui/Field'

/**
 * Departamento → municipio (lista dependiente con código DANE). Se usa para la entrega, la facturación y las direcciones guardadas.
 * `stateField` y `cityField` son los nombres de los campos en el formulario (por ejemplo delivery_state_code / delivery_city_code).
 */
export default function PlaceFields({ stateField, cityField, form, set, errors, serverError }) {
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
