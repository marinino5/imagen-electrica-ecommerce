import { useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react'
import { Link, useParams, useSearchParams } from 'react-router-dom'
import { Activity, ArrowLeft, ArrowRight, BadgeCheck, Download, FileText, Gauge, Headset, Layers, MessageCircle, Minus, Plug, Plus, Search, ShieldCheck, ShoppingCart, Truck, Zap } from 'lucide-react'
import { catalogApi } from '../api'
import { site } from '../config/site'
import { useCart } from '../context/CartContext'
import { useApi } from '../hooks/useApi'
import { isDirectPurchase } from '../lib/catalog'
import { formatCOP } from '../lib/format'
import { cartItem, hpText, imageMatchesBrand, lineBrands, lineImage, lineQuoteItem, twinTag } from '../lib/lines'
import { WHERE_USED } from '../lib/datasheets'
import BrandMark from '../components/ui/BrandMark'
import Eyebrow from '../components/ui/Eyebrow'
import Gallery from '../components/catalog/Gallery'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import ErrorState from '../components/ui/ErrorState'
import LineCard from '../components/catalog/LineCard'
import Bgfx from '../components/ui/Bgfx'
import '../styles/product-detail.css'
import '../styles/ie-ficha.css'

function DetailSkeleton() {
  return (
    <div className="wrap pd" aria-busy="true" aria-label="Cargando la ficha">
      <div className="skeleton" style={{ aspectRatio: '1', borderRadius: 8 }} />
      <div className="pd__info">
        <div className="skeleton" style={{ height: 24, width: '40%' }} />
        <div className="skeleton" style={{ height: 70 }} />
        <div className="skeleton" style={{ height: 260, borderRadius: 8 }} />
      </div>
    </div>
  )
}

const uniqueSorted = (list) => [...new Set(list.filter((x) => x !== null && x !== undefined))].sort((a, b) => a - b)

const TABS = [
  ['spec', 'Especificaciones'],
  ['refs', 'Todas las referencias'],
  ['used', 'Dónde se usa'],
]

// Pestañas accesibles (tablist): flechas, Inicio y Fin cambian de pestaña
function useTabs(initial = 'spec') {
  const [tab, setTab] = useState(initial)
  const refs = useRef({})
  const [bar, setBar] = useState({ x: 0, w: 0 })
  // Indicador naranja de 3 px que se desliza hasta la pestaña activa (transform, 240 ms)
  // Se mide tras cada render (las pestañas aparecen cuando carga la ficha); solo se actualiza si cambió
  useLayoutEffect(() => {
    const el = refs.current[tab]
    if (el) setBar((b) => (b.x === el.offsetLeft && b.w === el.offsetWidth ? b : { x: el.offsetLeft, w: el.offsetWidth }))
  })
  useEffect(() => {
    const measure = () => {
      const el = refs.current[tab]
      if (el) setBar({ x: el.offsetLeft, w: el.offsetWidth })
    }
    window.addEventListener('resize', measure)
    return () => window.removeEventListener('resize', measure)
  }, [tab])
  const onKeyDown = (e) => {
    const i = TABS.findIndex(([k]) => k === tab)
    const next = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: TABS.length - 1 }[e.key]
    if (next === undefined) return
    e.preventDefault()
    const key = TABS[(next + TABS.length) % TABS.length][0]
    setTab(key)
    refs.current[key]?.focus()
  }
  return { tab, setTab, refs, onKeyDown, bar }
}

export default function ProductDetail() {
  const { slug } = useParams()
  const [params, setParams] = useSearchParams()
  const { data, loading, error, reload } = useApi(() => catalogApi.line(slug), [slug])
  const { add } = useCart()
  const [qty, setQty] = useState(1)
  const [refTerm, setRefTerm] = useState('')
  const tabs = useTabs()
  const line = data?.data
  const related = data?.related ?? []
  const variants = useMemo(() => line?.variants ?? [], [line])

  // La referencia elegida vive en la URL (?ref=SKU): se puede compartir y el carrito vuelve a la misma variante
  const refSku = params.get('ref')
  const current = variants.find((v) => v.sku === refSku) ?? variants[0] ?? null

  useEffect(() => {
    setQty(1)
    setRefTerm('')
    tabs.setTab('spec')
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [slug])

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
  // El logo y la foto siguen a la referencia elegida: nunca un logo de ABB junto a un motor WEG
  const brand = current?.brand?.name ?? lineBrands(line)[0] ?? ''
  const galleryLine = imageMatchesBrand(line.image_url, brand) ? line : { ...line, image_url: null, image_alt_url: null }

  // Datos técnicos de la referencia elegida. Los que no tienen valor no se muestran (nada de «—»).
  const specs = current
    ? [
        [Zap, 'Potencia', current.hp != null ? hpText(current.hp) : null],
        [Gauge, 'Velocidad', current.rpm ? `${current.rpm} RPM` : null],
        [Activity, 'Corriente', current.amp ? `${String(current.amp).replace('.', ',')} A` : null],
        [Plug, 'Tensión', current.volt ? `${current.volt} V` : null],
        [ShieldCheck, 'Protección', current.ip || null],
        [BadgeCheck, 'Marca', current.brand?.name ?? line.brand_label ?? null],
        [Layers, 'Categoría', line.category?.name ?? null],
      ].filter(([, , value]) => value)
    : line.spec_chips.map((chip, i) => [Layers, `Dato ${i + 1}`, chip])
  // Tabla de la ficha técnica: lo mismo en filas, más identificación
  const sheetRows = current
    ? [
        ['Línea', line.name],
        ['Referencia', current.label],
        ['SKU', current.sku],
        ...specs.map(([, label, value]) => [label, value]),
      ].filter(([, v], i, rows) => v && rows.findIndex(([l]) => l === rows[i][0]) === i)
    : []
  const needle = refTerm.trim().toLowerCase()
  const shownRefs = needle ? variants.filter((v) => `${v.label} ${v.sku} ${v.name}`.toLowerCase().includes(needle)) : variants
  const used = WHERE_USED[line.family?.slug] ?? WHERE_USED.default
  const addRow = (v) => (e) => add(cartItem(line, v), 1, { rect: e.currentTarget.getBoundingClientRect(), image })
  const quoteRow = (v) => (e) => add(cartItem(line, v), 1, { rect: e.currentTarget.getBoundingClientRect(), image }, { asQuote: true })
  const printSheet = () => window.print()

  return (
    <div className="fx bg-lila-fade">
      <Bgfx b e />
      <div className="wrap pd-back">
        <Link to="/productos" className="pd-back__link"><ArrowLeft size={18} aria-hidden="true" /> Volver al catálogo</Link>
      </div>

      <article className="wrap pd">
        <Reveal className="pd__gal">
          <Gallery line={galleryLine} />
        </Reveal>

        <Reveal className="pd__info" delay={0.05}>
          {/* 1 · Marca y línea */}
          <p className="pd-kicker">
            {brand && <BrandMark name={brand} />}
            <span>{[line.category?.name, current?.sku && `SKU ${current.sku}`].filter(Boolean).join(' · ')}</span>
          </p>
          {/* 2 · Nombre */}
          <h1 className="pd__name">{line.name}</h1>
          {/* 3 · Una línea de descripción */}
          <p className="pd__desc">{line.description ?? 'Línea del portafolio de Imagen Eléctrica.'}</p>

          {variants.length > 0 && current ? (
            <>
              {/* 4 · Selector de referencia (chips) */}
              <div className="pd-sel">
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
                    <h2 className="pd-h">Referencia <span>{variants.length > 1 ? `${variants.length} opciones` : ''}</span></h2>
                    <div className="pd-vg" role="group" aria-label="Referencia">
                      {variants.map((v) => (
                        <button key={v.sku} type="button" className={current.sku === v.sku ? 'on' : ''} aria-pressed={current.sku === v.sku} onClick={() => choose(v)}>
                          {v.label}
                        </button>
                      ))}
                    </div>
                  </>
                )}
              </div>

              {/* 5 · Separador · precio de 44 px · IVA */}
              <hr className="pd-sep" />
              <div className="pd-pricebox">
                <p className="pd-price" aria-live="polite">
                  <strong>{direct ? formatCOP(current.price) : 'Precio por cotización'}</strong>
                  <small>{direct ? 'IVA incluido' : 'Te respondemos con precio y tiempo de entrega'}</small>
                </p>
              </div>

              {/* 7 · Cantidad */}
              <div className="pd-qtyrow">
                <span id="qty-label">Cantidad</span>
                <div className="pd-qty" role="group" aria-labelledby="qty-label">
                  <button type="button" onClick={() => setQty((q) => Math.max(1, q - 1))} aria-label="Menos"><Minus size={16} /></button>
                  <input type="number" inputMode="numeric" min="1" value={qty} onChange={(e) => setQty(Math.max(1, Number(e.target.value) || 1))} aria-label="Cantidad" />
                  <button type="button" onClick={() => setQty((q) => q + 1)} aria-label="Más"><Plus size={16} /></button>
                </div>
                {total !== null && qty > 1 && <span className="pd-total">Total: <strong>{formatCOP(total)}</strong></span>}
              </div>
              {current.sale_mode === 'direct' && current.stock > 0 && !purchasable && (
                <p className="pd-note">Esta cantidad no se compra en línea: usa «Solicitar cotización» y un asesor te responde con precio.</p>
              )}

              {/* 8 · Dos botones grandes */}
              <div className="pd-cta">
                {purchasable && (
                  <button type="button" className="pd-btn pd-btn--buy" onClick={addBuy}><ShoppingCart size={20} aria-hidden="true" /> Agregar al carrito</button>
                )}
                <button type="button" className={`pd-btn ${purchasable ? 'pd-btn--quote' : 'pd-btn--main'}`} onClick={addQuote}><FileText size={20} aria-hidden="true" /> Solicitar cotización</button>
              </div>
            </>
          ) : (
            <div className="pd-pricebox">
              <h2 className="pd-h">Bajo pedido</h2>
              <p className="pd-lead">Esta línea se maneja por pedido. Cuéntanos la referencia o la aplicación y te cotizamos sin crear cuenta.</p>
              <div className="pd-cta">
                <button type="button" className="pd-btn pd-btn--main" onClick={addQuote}><FileText size={20} aria-hidden="true" /> Solicitar cotización</button>
              </div>
            </div>
          )}

          {/* 9 · Tres microgarantías */}
          <ul className="pd-guar">
            <li><BadgeCheck size={16} aria-hidden="true" /> Referencia original</li>
            <li><Headset size={16} aria-hidden="true" /> Asesoría técnica</li>
            <li><Truck size={16} aria-hidden="true" /> Envío a todo el país</li>
          </ul>

          {/* 10 · Dudas, pequeño */}
          <p className="pd-assist">
            ¿Dudas?{' '}
            <a href={site.whatsappLink(waText)} target="_blank" rel="noreferrer">
              <MessageCircle size={14} aria-hidden="true" /> Asesor por WhatsApp
            </a>
          </p>
          {line.category?.slug === 'motores-trifasicos' && (
            <Link to="/productos?familia=motores-electricos&categoria=atmosferas-explosivas" className="pd-cross">
              <span>¿Tu motor va en zona clasificada? <b>Ver atmósferas explosivas</b></span>
              <ArrowRight size={18} aria-hidden="true" />
            </Link>
          )}
        </Reveal>
      </article>

      {/* Pestañas: Especificaciones · Todas las referencias · Dónde se usa */}
      <section className="wrap pd-tabs" aria-label="Detalles de la ficha">
        <div className="pd-tabs__list" role="tablist" aria-label="Detalles de la ficha" onKeyDown={tabs.onKeyDown}>
          {TABS.map(([key, label]) => (
            <button
              key={key}
              ref={(el) => { tabs.refs.current[key] = el }}
              type="button"
              role="tab"
              id={`tab-${key}`}
              aria-selected={tabs.tab === key}
              aria-controls={`panel-${key}`}
              tabIndex={tabs.tab === key ? 0 : -1}
              className={tabs.tab === key ? 'on' : ''}
              onClick={() => tabs.setTab(key)}
            >
              {label}
              {key === 'refs' && variants.length > 0 && <i>{variants.length}</i>}
            </button>
          ))}
          <span className="pd-tabs__bar" aria-hidden="true" style={{ transform: `translateX(${tabs.bar.x}px) scaleX(${tabs.bar.w / 100})` }} />
        </div>

        <div role="tabpanel" id="panel-spec" aria-labelledby="tab-spec" hidden={tabs.tab !== 'spec'} className="pd-panel">
          {current ? (
            <>
              <p className="pd-panel__lead">Datos de la referencia elegida: <b>{current.label}</b> · SKU {current.sku}</p>
              {specs.length > 0 && (
                <dl className="pd-cards">
                  {specs.map(([Icon, label, value]) => (
                    <div key={label} className="pd-card">
                      <span className="pd-card__ic" aria-hidden="true"><Icon size={20} strokeWidth={1.7} /></span>
                      <dt>{label}</dt>
                      <dd>{value}</dd>
                    </div>
                  ))}
                </dl>
              )}
              <h3 className="pd-panel__h">Ficha técnica</h3>
              <table className="pd-sheet">
                <caption className="sr-only">Ficha técnica de {line.name}, referencia {current.label}</caption>
                <tbody>
                  {sheetRows.map(([label, value]) => <tr key={label}><th scope="row">{label}</th><td>{value}</td></tr>)}
                </tbody>
              </table>
              <button type="button" className="pd-dl" onClick={printSheet}>
                <Download size={20} aria-hidden="true" />
                <span><b>Descargar ficha técnica (PDF)</b><small>Ficha de Imagen Eléctrica con las especificaciones de esta línea</small></span>
              </button>
            </>
          ) : (
            <>
              <p className="pd-panel__lead">Esta línea se cotiza bajo pedido. Cuéntanos la referencia que buscas y te respondemos con ficha, precio y tiempo de entrega.</p>
              {specs.length > 0 && (
                <dl className="pd-cards">
                  {specs.map(([Icon, label, value]) => (
                    <div key={label} className="pd-card">
                      <span className="pd-card__ic" aria-hidden="true"><Icon size={20} strokeWidth={1.7} /></span>
                      <dt>{label}</dt>
                      <dd>{value}</dd>
                    </div>
                  ))}
                </dl>
              )}
            </>
          )}
        </div>

        <div role="tabpanel" id="panel-refs" aria-labelledby="tab-refs" hidden={tabs.tab !== 'refs'} className="pd-panel">
          {variants.length > 0 ? (
            <div className="pd-refs">
              <div className="pd-refs__bar">
                <p className="pd-panel__lead">{variants.length} {variants.length === 1 ? 'referencia' : 'referencias'} en esta ficha</p>
                <label className="pd-refs__find">
                  <Search size={16} aria-hidden="true" />
                  <span className="sr-only">Buscar en las referencias</span>
                  <input type="search" placeholder="Buscar referencia o SKU" value={refTerm} onChange={(e) => setRefTerm(e.target.value)} />
                </label>
              </div>
              <table className="pd-table">
                <caption className="sr-only">Referencias de {line.name}: toca el nombre para elegir una</caption>
                <thead>
                  <tr>
                    <th scope="col">Referencia</th>
                    <th scope="col">Marca</th>
                    <th scope="col">SKU</th>
                    <th scope="col" className="pd-table__num">Precio</th>
                    <th scope="col"><span className="sr-only">Acción</span></th>
                  </tr>
                </thead>
                <tbody>
                  {shownRefs.map((v) => {
                    const selected = v.sku === current?.sku
                    const buy = isDirectPurchase(v, 1)
                    return (
                      <tr key={v.sku} className={selected ? 'on' : ''}>
                        <td data-label="Referencia">
                          <button type="button" className="pd-table__pick" aria-pressed={selected} onClick={() => choose(v)}>{v.label}</button>
                        </td>
                        <td data-label="Marca" className="pd-table__brand">{v.brand?.name ? <BrandMark name={v.brand.name} /> : '—'}</td>
                        <td data-label="SKU" className="pd-table__sku">{v.sku}</td>
                        <td data-label="Precio" className="pd-table__num">{v.sale_mode === 'direct' && v.price !== null ? formatCOP(v.price) : 'Cotizar'}</td>
                        <td className="pd-table__act">
                          {buy ? (
                            <button type="button" className="pd-row-btn pd-row-btn--buy" onClick={addRow(v)} aria-label={`Agregar ${v.label} al carrito`}>
                              <ShoppingCart size={16} aria-hidden="true" /> Agregar
                            </button>
                          ) : (
                            <button type="button" className="pd-row-btn pd-row-btn--quote" onClick={quoteRow(v)} aria-label={`Cotizar ${v.label}`}>
                              <FileText size={16} aria-hidden="true" /> Cotizar
                            </button>
                          )}
                        </td>
                      </tr>
                    )
                  })}
                  {shownRefs.length === 0 && <tr><td colSpan={5} className="pd-table__none">Ninguna referencia coincide con «{refTerm}».</td></tr>}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="pd-panel__lead">Esta línea no tiene referencias publicadas todavía: se cotiza bajo pedido.</p>
          )}
        </div>

        <div role="tabpanel" id="panel-used" aria-labelledby="tab-used" hidden={tabs.tab !== 'used'} className="pd-panel">
          {/* Las fotos se montan al abrir la pestaña y cargan de inmediato: un lazy dentro de un panel oculto dejaba imágenes sin cargar */}
          {tabs.tab === 'used' && (
            <ul className="pd-used">
              {used.map(([name, photo]) => (
                <li key={name}>
                  <img src={photo} alt="" width="480" height="320" loading="eager" decoding="async" onError={(e) => { e.currentTarget.style.visibility = 'hidden' }} />
                  <span>{name}</span>
                </li>
              ))}
            </ul>
          )}
        </div>
      </section>

      {related.length > 0 && (
        <section className="wrap pd-rel" aria-labelledby="rel-title">
          <Eyebrow>Completa tu instalación</Eyebrow>
          <h2 id="rel-title" className="h2-sys">Líneas relacionadas</h2>
          <div className="pd-rel__grid">
            {related.map((l) => <Reveal key={l.slug}><LineCard line={l} /></Reveal>)}
          </div>
        </section>
      )}

      {/* Celular: barra fija inferior con precio, «Agregar» y «Cotizar» */}
      <div className="pd-mbuy" role="region" aria-label="Comprar esta línea">
        <p>
          <strong>{current && direct ? formatCOP(current.price * qty) : 'Cotización'}</strong>
          <small>{current ? current.label : 'Bajo pedido'}</small>
        </p>
        {purchasable && <button type="button" className="pd-mbuy__buy" onClick={addBuy}><ShoppingCart size={18} aria-hidden="true" /> Agregar</button>}
        <button type="button" className={purchasable ? 'pd-mbuy__quote' : 'pd-mbuy__buy'} onClick={addQuote}>Cotizar</button>
      </div>

      {/* Ficha para imprimir o guardar en PDF: marca de Imagen Eléctrica y solo especificaciones (sin datos del fabricante) */}
      <section className="pd-print" aria-hidden="true">
        <header className="pd-print__head">
          <img src="/img/logo.png" alt="" width="54" height="60" />
          <div>
            <strong>{site.name}</strong>
            <span>{site.tagline} · {site.address}, {site.city}</span>
            <span>{site.mainContact.label} · {site.email}</span>
          </div>
        </header>
        <p className="pd-print__title">{line.name}</p>
        <p>{[line.brand_label, line.category?.name].filter(Boolean).join(' · ')}</p>
        {image && <img className="pd-print__img" src={image} alt="" width="320" height="320" />}
        {variants.length > 0 ? (
          <table>
            <thead>
              <tr><th>Referencia</th><th>SKU</th><th>Potencia</th><th>Velocidad</th><th>Corriente</th><th>Tensión</th><th>Protección</th></tr>
            </thead>
            <tbody>
              {variants.map((v) => (
                <tr key={v.sku}>
                  <td>{v.label}</td><td>{v.sku}</td>
                  <td>{v.hp != null ? hpText(v.hp) : ''}</td><td>{v.rpm ? `${v.rpm} RPM` : ''}</td>
                  <td>{v.amp ? `${String(v.amp).replace('.', ',')} A` : ''}</td><td>{v.volt ? `${v.volt} V` : ''}</td><td>{v.ip ?? ''}</td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <p>{line.spec_chips.join(' · ')}</p>
        )}
        <footer>Ficha elaborada por {site.name}. Precios sujetos a confirmación de un asesor.</footer>
      </section>
    </div>
  )
}
