import { get } from './http'

// Catálogo público: no necesita token
export const catalogApi = {
  /** filtros: q, category (slug), brand (slug), sale_mode, featured, in_stock, sort, per_page, page */
  products: (params, signal) => get('/products', { params, signal }),
  product: (slug) => get(`/products/${encodeURIComponent(slug)}`),
  /** Catálogo por líneas (fichas): filtros q, family, category, brand, mode, in_stock, hp_min, hp_max */
  // per_page 200: el catálogo trae todas las líneas de una vez (hay unas 60) y la tienda filtra en el navegador
  lines: (params, signal) => get('/lines', { params: { per_page: 200, ...params }, signal }),
  line: (slug) => get(`/lines/${encodeURIComponent(slug)}`),
  families: () => get('/families'),
  /** Promociones vigentes hoy: si no hay ninguna, data viene vacío y la sección se oculta */
  promotions: (signal) => get('/promotions', { signal }),
  categories: () => get('/categories', { params: { per_page: 50 } }),
  brands: () => get('/brands', { params: { per_page: 50 } }),
}
