import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { ChevronRight, MessageCircle, ShieldCheck, Zap } from 'lucide-react'
import { catalogApi } from '../api'
import { useApi } from '../hooks/useApi'
import { useCategories } from '../hooks/useCategories'
import { availability, isDirectPurchase, productImage } from '../lib/catalog'
import { formatCOP, formatSpec } from '../lib/format'
import { site } from '../config/site'
import ProductGallery from '../components/ui/ProductGallery'
import ProductCard, { AddButton, ProductPrice } from '../components/ui/ProductCard'
import ErrorState from '../components/ui/ErrorState'
import '../styles/product-detail.css'

function DetailSkeleton() {
  return (
    <div className="container pdetail" aria-busy="true" aria-label="Cargando producto">
      <div className="pdetail__grid">
        <div className="skeleton" style={{ aspectRatio: '4 / 3' }} />
        <div className="pdetail__info">
          <div className="skeleton" style={{ height: 18, width: '40%' }} />
          <div className="skeleton" style={{ height: 40, width: '90%' }} />
          <div className="skeleton" style={{ height: 120 }} />
        </div>
      </div>
    </div>
  )
}

function Related({ product }) {
  const slug = product.category?.slug
  const { data } = useApi((signal) => catalogApi.products({ category: slug, per_page: 5 }, signal), [slug])
  const items = (data?.data ?? []).filter((p) => p.id !== product.id).slice(0, 4)
  if (!slug || items.length === 0) return null
  return (
    <section className="pdetail__related" aria-labelledby="rel-title">
      <h2 id="rel-title" className="display">Productos <em>relacionados</em></h2>
      <div className="product-grid">
        {items.map((p) => <ProductCard key={p.id} product={p} />)}
      </div>
    </section>
  )
}

export default function ProductDetail() {
  const { slug } = useParams()
  const { data: categories } = useCategories()
  const { data, loading, error, reload } = useApi(() => catalogApi.product(slug), [slug])
  const [quantity, setQuantity] = useState('1')

  useEffect(() => setQuantity('1'), [slug])

  if (loading) return <DetailSkeleton />
  if (error?.status === 404) {
    return (
      <div className="container">
        <div className="state" role="alert">
          <h1 className="page-title">Producto no <em>encontrado</em></h1>
          <p>Puede que ya no esté disponible o que el enlace haya cambiado.</p>
          <Link to="/productos" className="btn btn--primary">Ver el catálogo</Link>
        </div>
      </div>
    )
  }
  if (error) return <div className="container"><ErrorState error={error} onRetry={reload} /></div>

  const product = data.data
  const stock = availability(product)
  const image = productImage(product, categories)
  const specs = Object.entries(product.specs ?? {})
  const qty = Number(quantity) > 0 ? Number(quantity) : 1
  const max = product.sale_mode === 'direct' && product.stock > 0 ? product.stock : undefined
  const direct = isDirectPurchase(product, qty)
  const parent = product.category && categories.byId.get(categories.byId.get(product.category.id)?.parent_id)

  return (
    <article className="container pdetail">
      <nav aria-label="Migas de pan" className="pdetail__crumbs">
        <ol>
          <li><Link to="/productos">Productos</Link></li>
          {parent && <li><ChevronRight size={14} aria-hidden="true" /><Link to={`/productos?category=${parent.slug}`}>{parent.name}</Link></li>}
          {product.category && <li><ChevronRight size={14} aria-hidden="true" /><Link to={`/productos?category=${product.category.slug}`}>{product.category.name}</Link></li>}
        </ol>
      </nav>

      <div className="pdetail__grid">
        <ProductGallery images={image ? [image] : []} name={product.name} framed={!product.image_url} />

        <div className="pdetail__info">
          <p className="pdetail__meta">
            {product.brand?.name && <Link to={`/productos?brand=${product.brand.slug}`} className="pdetail__brand">{product.brand.name}</Link>}
            <span>SKU {product.sku}</span>
          </p>
          <h1 className="pdetail__name">{product.name}</h1>
          <span className={`badge badge--${stock.tone}`}>{stock.label}</span>
          {product.sale_mode === 'direct' && product.stock > 0 && <span className="pdetail__units">{product.stock} {product.stock === 1 ? 'unidad' : 'unidades'} en inventario</span>}
          {product.short_description && <p className="pdetail__desc">{product.short_description}</p>}

          <div className="pdetail__price">
            <ProductPrice product={product} />
            {product.price === null && <p className="field-hint">Te enviamos el valor por correo o WhatsApp después de revisar tu solicitud.</p>}
            {product.price !== null && qty > 1 && (
              <p className="field-hint">Total de {qty} {product.unit_label?.toLowerCase()}: <strong>{formatCOP(product.price * qty)}</strong> con IVA incluido.</p>
            )}
          </div>

          <div className="pdetail__buy" id="comprar">
            <div className="field pdetail__qty">
              <label htmlFor="pd-qty">Cantidad ({product.unit_label?.toLowerCase()})</label>
              <input id="pd-qty" className="input" type="number" inputMode="numeric" min="1" max={max} step="1" value={quantity} onChange={(e) => setQuantity(e.target.value)} />
            </div>
            <AddButton product={product} quantity={qty} size="lg" />
          </div>
          {product.sale_mode === 'direct' && product.stock > 0 && !direct && (
            <p className="field-hint">Pides más de lo disponible ({product.stock}): se enviará como cotización.</p>
          )}

          <a className="btn btn--ghost btn--block" href={site.whatsappLink(`Hola, quiero información sobre ${product.name} (SKU ${product.sku}).`)} target="_blank" rel="noreferrer">
            <MessageCircle size={18} aria-hidden="true" /> Preguntar a {site.mainContact.name} por este producto
          </a>

          <p className="pdetail__warranty">
            <ShieldCheck size={20} aria-hidden="true" />
            <span><strong>Garantía y devoluciones.</strong> Las condiciones se confirman en tu cotización o pedido; si las necesitas antes de comprar, consúltalas por WhatsApp.</span>
          </p>
        </div>
      </div>

      <div className="pdetail__sections">
        {product.description && (
          <section aria-labelledby="desc-title">
            <h2 id="desc-title" className="display">Descripción</h2>
            <p>{product.description}</p>
          </section>
        )}
        {specs.length > 0 && (
          <section aria-labelledby="spec-title">
            <h2 id="spec-title" className="display"><Zap size={20} aria-hidden="true" /> Especificaciones <em>técnicas</em></h2>
            <table className="spec-table">
              <caption className="sr-only">Especificaciones técnicas de {product.name}</caption>
              <tbody>
                {specs.map(([key, value]) => {
                  const spec = formatSpec(key, value)
                  return <tr key={key}><th scope="row">{spec.label}</th><td>{spec.value}</td></tr>
                })}
                <tr><th scope="row">Unidad de venta</th><td>{product.unit_label}</td></tr>
                <tr><th scope="row">IVA</th><td>{product.tax_rate}%</td></tr>
              </tbody>
            </table>
          </section>
        )}
      </div>

      <Related product={product} />

      {/* Barra de acción fija en celular */}
      <div className="pdetail__bar" role="region" aria-label="Comprar este producto">
        <div className="pdetail__bar-price">
          {product.price === null ? <strong>Cotizar</strong> : <strong>{formatCOP(product.price)}</strong>}
          <small>{product.price === null ? 'Sin precio publicado' : `IVA incl. · por ${product.unit}`}</small>
        </div>
        <AddButton product={product} quantity={qty} />
      </div>
    </article>
  )
}
