import { catalogApi } from '../api'
import { useApi } from './useApi'

// Promociones vigentes. Si la API falla o no hay ninguna, `data` queda vacío y la sección del Inicio se oculta.
export function usePromotions() {
  const { data, loading } = useApi((signal) => catalogApi.promotions(signal), [])
  return { data: data?.data ?? [], loading }
}
