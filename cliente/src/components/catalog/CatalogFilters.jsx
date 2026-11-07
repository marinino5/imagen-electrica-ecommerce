import { useEffect, useMemo, useRef, useState } from 'react'
import { ChevronDown, Search } from 'lucide-react'
import { AMP_RANGES, HP_RANGES } from '../../lib/lines'
import BrandMark from '../ui/BrandMark'

const ORDERS = [
  ['', 'Recomendados'],
  ['refs', 'Más referencias'],
  ['stock', 'En bodega primero'],
]

// Desplegable de marca con buscador y logos
function BrandMenu({ brands, selected, toggle }) {
  const [open, setOpen] = useState(false)
  const [term, setTerm] = useState('')
  const box = useRef(null)
  const list = useMemo(() => brands.filter((b) => b.name.toLowerCase().includes(term.trim().toLowerCase())), [brands, term])

  useEffect(() => {
    if (!open) return
    const out = (e) => !box.current?.contains(e.target) && setOpen(false)
    const key = (e) => e.key === 'Escape' && setOpen(false)
    document.addEventListener('mousedown', out)
    document.addEventListener('keydown', key)
    return () => {
      document.removeEventListener('mousedown', out)
      document.removeEventListener('keydown', key)
    }
  }, [open])

  return (
    <div className="fb__drop" ref={box}>
      <button type="button" className={`fb__btn ${selected.length ? 'on' : ''}`} aria-expanded={open} aria-haspopup="true" onClick={() => setOpen((v) => !v)}>
        Marca{selected.length ? ` (${selected.length})` : ''} <ChevronDown size={16} aria-hidden="true" />
      </button>
      {open && (
        <div className="fb__pop">
          <label className="fb__find">
            <Search size={16} aria-hidden="true" />
            <span className="sr-only">Buscar marca</span>
            <input type="search" placeholder="Buscar marca" value={term} onChange={(e) => setTerm(e.target.value)} autoFocus />
          </label>
          <ul>
            {list.map((b) => (
              <li key={b.name}>
                <label>
                  <input type="checkbox" checked={selected.includes(b.name)} onChange={() => toggle(b.name)} />
                  <span className="fb__logo"><BrandMark name={b.name} /></span>
                  <span className="fb__n">{b.count}</span>
                </label>
              </li>
            ))}
            {list.length === 0 && <li className="fb__none">Sin resultados</li>}
          </ul>
        </div>
      )}
    </div>
  )
}

// Barra horizontal de filtros arriba de la grilla: cómo comprar, marca, disponibilidad, potencia o corriente (solo donde
// aplican) y ordenar. Es la misma en escritorio y dentro de la hoja del celular.
export default function CatalogFilters({ st, set, toggleBrand, brands, ranges, tech, onReset, activeCount, sheet = false }) {
  return (
    <div className={`fb ${sheet ? 'fb--sheet' : ''}`} role="group" aria-label="Filtros">
      <div className="fb__item">
        <span className="fb__label">Cómo comprar</span>
        <div className="cat-seg" role="group" aria-label="Cómo comprar">
          {[['', 'Todo'], ['on', 'En línea'], ['q', 'Cotizar']].map(([value, label]) => (
            <button key={label} type="button" className={st.mode === value ? 'on' : ''} aria-pressed={st.mode === value} onClick={() => set({ mode: value })}>{label}</button>
          ))}
        </div>
      </div>

      {brands.length > 0 && (
        <div className="fb__item">
          <span className="fb__label">Marca</span>
          <BrandMenu brands={brands} selected={st.brands} toggle={toggleBrand} />
        </div>
      )}

      <div className="fb__item">
        <span className="fb__label">Disponibilidad</span>
        <button type="button" role="switch" aria-checked={st.stock} className={`fb__switch ${st.stock ? 'on' : ''}`} onClick={() => set({ stock: !st.stock })}>
          <i aria-hidden="true" /> En bodega
        </button>
      </div>

      {tech.hp && ranges.hp.length > 0 && (
        <div className="fb__item">
          <label className="fb__label" htmlFor="f-hp">Potencia</label>
          <select id="f-hp" className="fb__select" value={st.hp} onChange={(e) => set({ hp: e.target.value })}>
            <option value="">Todas</option>
            {HP_RANGES.filter((r) => ranges.hp.includes(r) || st.hp === r.key).map((r) => <option key={r.key} value={r.key}>{r.label}</option>)}
          </select>
        </div>
      )}

      {tech.amp && ranges.amp.length > 0 && (
        <div className="fb__item">
          <label className="fb__label" htmlFor="f-amp">Corriente</label>
          <select id="f-amp" className="fb__select" value={st.amp} onChange={(e) => set({ amp: e.target.value })}>
            <option value="">Todas</option>
            {AMP_RANGES.filter((r) => ranges.amp.includes(r) || st.amp === r.key).map((r) => <option key={r.key} value={r.key}>{r.label}</option>)}
          </select>
        </div>
      )}

      <div className="fb__item fb__item--sort">
        <label className="fb__label" htmlFor="f-sort">Ordenar</label>
        <select id="f-sort" className="fb__select" value={st.order} onChange={(e) => set({ order: e.target.value })}>
          {ORDERS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
        </select>
      </div>
      {sheet && activeCount > 0 && <button type="button" className="cat-reset" onClick={onReset}>Limpiar filtros ({activeCount})</button>}
    </div>
  )
}
