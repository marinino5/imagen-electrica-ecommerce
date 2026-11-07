import { useEffect, useMemo, useRef, useState } from 'react'
import { ChevronDown, Search } from 'lucide-react'
import BrandMark from '../ui/BrandMark'

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

// Barra de filtros arriba de la grilla: buscador (nombre, referencia o SKU), cómo comprar y marca con logos.
// Es la misma en escritorio y dentro de la hoja del celular. Potencia y orden ya no se ofrecen.
export default function CatalogFilters({ st, set, toggleBrand, brands, finderRef, sheet = false }) {
  return (
    <div className={`fb ${sheet ? 'fb--sheet' : ''}`} role="group" aria-label="Filtros">
      <label className="fb__search">
        <Search size={18} aria-hidden="true" />
        <span className="sr-only">Buscar productos</span>
        <input ref={sheet ? undefined : finderRef} type="search" enterKeyHint="search" autoComplete="off" placeholder="Busca por nombre, referencia o SKU" value={st.q} onChange={(e) => set({ q: e.target.value })} />
      </label>

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
    </div>
  )
}
