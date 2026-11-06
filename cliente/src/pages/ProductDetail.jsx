import { useEffect, useMemo, useState } from 'react'
import { Link, useParams, useSearchParams } from 'react-router-dom'
import { ArrowLeft, FileText, MessageCircle, Minus, Plus, ShoppingCart } from 'lucide-react'
import { catalogApi } from '../api'
import { site } from '../config/site'
import { useCart } from '../context/CartContext'
import { useApi } from '../hooks/useApi'
import { isDirectPurchase } from '../lib/catalog'
import { formatCOP } from '../lib/format'
import { cartItem, hpText, lineImage, lineQuoteItem, modeTag, twinTag, variantTag } from '../lib/lines'
import Bezel from '../components/ui/Bezel'
import Eyebrow from '../components/ui/Eyebrow'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import ErrorState from '../components/ui/ErrorState'
import LineCard from '../components/catalog/LineCard'
import '../styles/product-detail.css'

function DetailSkeleton() {
  return (
    <div className="wrap pd" aria-busy="true" aria-label="Cargando la ficha">
      <div className="skeleton" style={{ aspectRatio: '1 / 0.9', borderRadius: 30 }} />
      <div className="pd__info">
        <div className="skeleton" style={{ height: 24, width: '40%' }} />
        <div className="skeleton" style={{ height: 70 }} />
        <div className="skeleton" style={{ height: 260, borderRadius: 30 }} />
      </div>
    </div>
  )
}

const uniqueSorted = (list) => [...new Set(list.filter((x) => x !== null && x !== undefined))].sort((a, b) => a - b)

export default function ProductDetail() {
  const { slug } = useParams()
  const [params, setParams] = useSearchParams()
  const { data, loading, error, reload } = useApi(() => catalogApi.line(slug), [slug])
  const { add } = useCart()
  const [qty, setQty] = useState(1)
  const line = data?.data
  const related = data?.related ?? []
  const variants = useMemo(() => line?.variants ?? [], [line])

  // La referencia elegida vive en la URL (?ref=SKU): se puede compartir y el carrito vuelve a la misma variante
  const refSku = params.get('ref')
  const current = variants.find((v) => v.sku === refSku) ?? variants[0] ?? null

  useEffect(() => setQty(1), [slug])

  if (loading) return <DetailSkeleton />
  if (error?.status === 404) {
    return (
      <div className="wrap pd-missing">
        <Eyebrow>Ficha no encontrada</Eyebrow>
        <h1 className="h-hero"><span>Esta línea</span><em>ya no está</em></h1>
        <p>Puede que haya cambiado de nombre o que ya no se maneje. Mira el catálogo o escríbenos.</p>
        <Pill as="link" to="/productos" tone="d">Volver al catálogo</Pill>
      </div>
    )
  }
  if (error) return <div className="wrap"><ErrorState error={error} onRetry={reload} /></div>

  const image = lineImage(line)
  const choose = (variant) => variant && setParams({ ref: variant.sku }, { replace: true })

  // Potencias y velocidades, de menor a mayor; las combinaciones que no existen se apagan
  const hps = uniqueSorted(variants.map((v) => v.hp))
  const rpms = uniqueSorted(variants.map((v) => v.rpm))
  const byHp = (hp) => variants.find((v) => v.hp === hp && (current?.rpm == null || v.rpm === current.rpm)) ?? variants.find((v) => v.hp === hp)
  const byRpm = (rpm) => variants.find((v) => v.hp === current?.hp && v.rpm === rpm)
  // Referencias con la misma potencia y velocidad: se distinguen por corriente, tensión o protección
  const twins = current && hps.length ? variants.filter((v) => v.hp === current.hp && v.rpm === current.rpm) : []

  const purchasable = current ? isDirectPurchase(current, qty) : false
  const direct = current?.sale_mode === 'direct' && current.price !== null
  const item = current ? cartItem(line, current) : lineQuoteItem(line)
  const addBuy = (e) => add(item, qty, { rect: e.currentTarget.getBoundingClientRect(), image })
  const addQuote = (e) => add(item, qty, { rect: e.currentTarget.getBoundingClientRect(), image }, { asQuote: true })
  const total = current && direct ? current.price * qty : null
  const waText = `Hola, quiero información sobre ${line.name}${current ? ` (${current.label}, SKU ${current.sku})` : ''}.`

  const cells = current
    ? [
        ['Potencia', current.hp != null ? hpText(current.hp) : '—'],
        ['Velocidad', current.rpm ? `${current.rpm} RPM` : '—'],
        ['Corriente', current.amp ? `${String(current.amp).replace('.', ',')} A` : '—'],
        ['Protección', current.ip || '—'],
        ['Tensión', current.volt ? `${current.volt} V` : 'Según placa'],
        ['Marca', current.brand?.name ?? line.brand_label ?? '—'],
        ['Línea', line.category?.name ?? '—'],
        ['En bodega', String(current.stock)],
      ]
    : line.spec_chips.map((s, i) => [`Dato ${i + 1}`, s])

  return (
    <>
      <div className="wrap pd-back">
        <Link to="/productos" className="pd-back__link"><ArrowLeft size={18} aria-hidden="true" /> Volver al catálogo</Link>
      </div>

      <article className="wrap pd">
        <Reveal className="pd__gal">
          <Bezel className="pd-gal">
            {image && <img src={image} alt={line.name} width="900" height="675" fetchPriority="high" />}
            <div className="pd-gal__badges">
              <span>{modeTag(line)}</span>
              {line.refs_count > 0 && <span>{line.refs_count} referencias</span>}
            </div>
          </Bezel>
        </Reveal>

        <Reveal className="pd__info" delay={0.05}>
          <Eyebrow>{[line.brand_label, line.category?.name].filter(Boolean).join(' · ')}</Eyebrow>
          <h1 className="pd__name">{line.name}</h1>
          <p className="pd__desc">
            {line.description ?? 'Línea del portafolio de Imagen Eléctrica.'}
            {line.refs_count > 0 ? ` ${line.refs_count} referencias en esta ficha.` : ' Disponible bajo pedido: te cotizamos la referencia exacta.'}
          </p>

          <Bezel className="pd-pick">
            <div className="pd-pick__body">
              {variants.length > 0 && current ? (
                <>
                  {hps.length > 0 ? (
                    <>
                      <h2 className="pd-h">Potencia <span>{current.hp != null ? hpText(current.hp) : ''}</span></h2>
                      <div className="pd-vg" role="group" aria-label="Potencia">
                        {hps.map((hp) => (
                          <button key={hp} type="button" className={current.hp === hp ? 'on' : ''} aria-pressed={current.hp === hp} onClick={() => choose(byHp(hp))}>
                            {hpText(hp)}
                          </button>
                        ))}
                      </div>
                      {rpms.length > 1 && (
                        <>
                          <h2 className="pd-h">Velocidad <span>{current.rpm ? `${current.rpm} RPM` : ''}</span></h2>
                          <div className="pd-vg" role="group" aria-label="Velocidad">
                            {rpms.map((rpm) => {
                              const target = byRpm(rpm)
                              return (
                                <button key={rpm} type="button" className={current.rpm === rpm ? 'on' : ''} aria-pressed={current.rpm === rpm} disabled={!target} onClick={() => choose(target)}>
                                  {rpm}
                                </button>
                              )
                            })}
                          </div>
                        </>
                      )}
                      {twins.length > 1 && (
                        <>
                          <h2 className="pd-h">Referencia <span>{twins.length} opciones</span></h2>
                          <div className="pd-vg" role="group" aria-label="Referencia">
                            {twins.map((v) => (
                              <button key={v.sku} type="button" className={current.sku === v.sku ? 'on' : ''} aria-pressed={current.sku === v.sku} onClick={() => choose(v)}>
                                {twinTag(v, twins)}
                              </button>
                            ))}
                          </div>
                        </>
                      )}
                    </>
                  ) : (
                    <>
                      <h2 className="pd-h">Referencia <span>{variants.length} opciones</span></h2>
                      <div className="pd-vg" role="group" aria-label="Referencia">
                        {variants.map((v) => (
                          <button key={v.sku} type="button" className={current.sku === v.sku ? 'on' : ''} aria-pressed={current.sku === v.sku} onClick={() => choose(v)}>
                            {v.label}
                          </button>
                        ))}
                      </div>
                    </>
                  )}

                  <div className="pd-buy">
                    <p className="pd-price" aria-live="polite">
                      <strong>{direct ? formatCOP(current.price) : 'Precio por cotización'}</strong>
                      <small>{direct ? 'IVA incluido · por unidad' : 'Te respondemos con precio y tiempo de entrega'}</small>
                    </p>
                    <div className="pd-qty" role="group" aria-label="Cantidad">
                      <button type="button" onClick={() => setQty((q) => Math.max(1, q - 1))} aria-label="Menos"><Minus size={16} /></button>
                      <input type="number" inputMode="numeric" min="1" value={qty} onChange={(e) => setQty(Math.max(1, Number(e.target.value) || 1))} aria-label="Cantidad" />
                      <button type="button" onClick={() => setQty((q) => q + 1)} aria-label="Más"><Plus size={16} /></button>
                    </div>
                  </div>
                  {total !== null && qty > 1 && <p className="pd-note">Total de {qty} unidades: <strong>{formatCOP(total)}</strong> con IVA incluido.</p>}
                  <p className="pd-stock">
                    <span className={`pd-stock__dot ${current.stock > 0 ? '' : 'no'}`} aria-hidden="true" />
                    {current.stock > 0 ? `${current.stock} en bodega` : 'Bajo pedido'} · SKU {current.sku}
                  </p>
                  {current.sale_mode === 'direct' && current.stock > 0 && !purchasable && (
                    <p className="pd-note">Pides más de lo que hay en bodega ({current.stock}): se enviará como cotización.</p>
                  )}

                  <div className="pd-cta">
                    {purchasable ? (
                      <>
                        <Pill tone="o" icon={ShoppingCart} onClick={addBuy}>Agregar al carrito</Pill>
                        <Pill tone="l" icon={FileText} onClick={addQuote}>Cotizar volumen</Pill>
                      </>
                    ) : (
                      <Pill tone="d" icon={FileText} onClick={addQuote}>Solicitar cotización</Pill>
                    )}
                  </div>
                </>
              ) : (
                <>
                  <h2 className="pd-h">Bajo pedido</h2>
                  <p className="pd-lead">Esta línea se maneja por pedido. Cuéntanos la referencia o la aplicación y te cotizamos sin crear cuenta.</p>
                  <div className="pd-cta">
                    <Pill tone="d" icon={FileText} onClick={addQuote}>Solicitar cotización</Pill>
                  </div>
                </>
              )}
            </div>
          </Bezel>

          <p className="pd-assist">
            ¿No sabes cuál elegir?{' '}
            <a href={site.whatsappLink(waText)} target="_blank" rel="noreferrer">
              <MessageCircle size={14} aria-hidden="true" /> Consulta con un asesor por WhatsApp
            </a>
          </p>
        </Reveal>
      </article>

      {/* Placa de datos: imita la placa de un motor y lista todas las referencias */}
      <Reveal as="section" className="wrap pd-plate" aria-labelledby="placa-title">
        <Bezel>
          <div className="pd-plate__core">
            <div className="pd-plate__top">
              <h2 id="placa-title">Placa de datos</h2>
              <span>{(current?.name ?? line.name).toUpperCase()}</span>
            </div>
            <dl className="pd-cells">
              {cells.map(([k, v]) => (
                <div key={k}><dt>{k}</dt><dd>{v}</dd></div>
              ))}
            </dl>
            {variants.length > 1 && (
              <div className="pd-ref">
                <table>
                  <caption className="sr-only">Referencias de {line.name}: toca una fila para elegirla</caption>
                  <thead>
                    <tr><th scope="col">Referencia</th><th scope="col">SKU</th><th scope="col">Bodega</th><th scope="col">Precio</th></tr>
                  </thead>
                  <tbody>
                    {variants.map((v) => (
                      <tr key={v.sku} className={v.sku === current?.sku ? 'on' : ''}>
                        <td>
                          <button type="button" onClick={() => choose(v)} aria-label={`Elegir ${v.label}`}>{v.label}</button>
                        </td>
                        <td>{v.sku}</td>
                        <td>{v.stock || 'Pedido'}</td>
                        <td>{v.sale_mode === 'direct' && v.price !== null ? formatCOP(v.price) : 'Cotizar'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </Bezel>
      </Reveal>

      {related.length > 0 && (
        <section className="wrap pd-rel" aria-labelledby="rel-title">
          <Eyebrow>Completa tu instalación</Eyebrow>
          <h2 id="rel-title" className="h2-sys">Líneas relacionadas</h2>
          <div className="pd-rel__grid">
            {related.map((l) => <Reveal key={l.slug}><LineCard line={l} /></Reveal>)}
          </div>
        </section>
      )}

      {/* Barra flotante en celular: precio, variante y botón */}
      <div className="pd-mbuy" role="region" aria-label="Comprar esta línea">
        <p>
          <strong>{current && direct ? formatCOP(current.price * qty) : 'Cotización'}</strong>
          <small>{current ? current.label : 'Bajo pedido'}</small>
        </p>
        {purchasable ? (
          <Pill tone="o" icon={ShoppingCart} size="sm" onClick={addBuy}>Agregar</Pill>
        ) : (
          <Pill tone="o" icon={FileText} size="sm" onClick={addQuote}>Cotizar</Pill>
        )}
      </div>
    </>
  )
}
