import { useEffect, useState } from 'react'
import { locationsApi } from '../api'

// Las listas de departamentos y municipios no cambian: se piden una vez y se recuerdan mientras la pestaña esté abierta
let departmentsPromise = null
const municipalities = new Map()

const departmentsOnce = () => (departmentsPromise ??= locationsApi.departments().then((r) => r.data, (e) => { departmentsPromise = null; throw e }))
const municipalitiesOnce = (code) => {
  if (!municipalities.has(code)) {
    municipalities.set(code, locationsApi.municipalities(code).then((r) => r.data, (e) => { municipalities.delete(code); throw e }))
  }
  return municipalities.get(code)
}

/** Departamentos: { list, loading, error }. */
export function useDepartments() {
  const [state, setState] = useState({ list: [], loading: true, error: null })
  useEffect(() => {
    let alive = true
    departmentsOnce().then((list) => alive && setState({ list, loading: false, error: null }), (error) => alive && setState({ list: [], loading: false, error }))
    return () => { alive = false }
  }, [])
  return state
}

/** Municipios del departamento elegido (lista dependiente): { list, loading, error }. Sin departamento, lista vacía. */
export function useMunicipalities(departmentCode) {
  const [state, setState] = useState({ list: [], loading: false, error: null })
  useEffect(() => {
    if (!departmentCode) { setState({ list: [], loading: false, error: null }); return undefined }
    let alive = true
    setState((s) => ({ ...s, loading: true, error: null }))
    municipalitiesOnce(departmentCode).then((list) => alive && setState({ list, loading: false, error: null }), (error) => alive && setState({ list: [], loading: false, error }))
    return () => { alive = false }
  }, [departmentCode])
  return state
}
