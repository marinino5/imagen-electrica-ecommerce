import { useEffect, useState } from 'react'

// Departamentos y municipios de Colombia con su código DANE (DIVIPOLA, datos.gov.co). La lista vive en el proyecto (src/data/divipola.json, una
// copia idéntica de backend/database/data/divipola.json: lo verifica una prueba del backend) y se descarga solo al llegar al pago: así el
// formulario no depende de la red ni de que la API publicada tenga la lista. Se pide una vez y se recuerda mientras la pestaña esté abierta.
let all = null
const load = () => (all ??= import('../data/divipola.json').then((m) => m.default))

/** Departamentos: { list, loading, error }. */
export function useDepartments() {
  const [state, setState] = useState({ list: [], loading: true, error: null })
  useEffect(() => {
    let alive = true
    load().then(
      (data) => alive && setState({ list: data.map((d) => ({ code: d.code, name: d.name })), loading: false, error: null }),
      (error) => alive && setState({ list: [], loading: false, error }),
    )
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
    load().then(
      (data) => alive && setState({ list: data.find((d) => d.code === departmentCode)?.cities ?? [], loading: false, error: null }),
      (error) => alive && setState({ list: [], loading: false, error }),
    )
    return () => { alive = false }
  }, [departmentCode])
  return state
}
