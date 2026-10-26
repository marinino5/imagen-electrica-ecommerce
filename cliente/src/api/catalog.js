import { get } from './http'

// Catálogo público: no necesita token
export const catalogApi = {
  /** filtros: q, category (slug), brand (slug), sale_mode, featured, in_stock, sort, per_page, page */
  products: (params, signal) => get('/products', { params, signal }),
  product: (slug) => get(`/products/${encodeURIComponent(slug)}`),
  categories: () => get('/categories', { params: { per_page: 50 } }),
  brands: () => get('/brands', { params: { per_page: 50 } }),
}
