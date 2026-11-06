import { Search } from 'lucide-react'
import { AMP_RANGES, HP_RANGES } from '../../lib/lines'

// Filtros del catálogo (columna izquierda en escritorio, pestaña "Filtros" de la hoja en celular)
export default function CatalogFilters({ st, set, toggleBrand, brands, ranges, onReset, activeCount }) {
  return (
    <div className="cat-filters">
      <div className="cat-sh">
        <h3>Filtros</h3>
        {activeCount > 0 && (
          <button type="button" className="cat-reset" onClick={onReset}>Limpiar ({activeCount})</button>
        )}
      </div>

      <div className="cat-fsec">
        <label className="cat-search">
          <Search size={18} aria-hidden="true" />
          <input
            type="search"
            enterKeyHint="search"
            autoComplete="off"
            placeholder="Referencia, SKU o marca"
            aria-label="Buscar en el catálogo"
            value={st.q}
            onChange={(e) => set({ q: e.target.value })}
          />
        </label>
      </div>

      <div className="cat-fsec">
        <h4>Cómo comprar</h4>
        <div className="cat-seg" role="group" aria-label="Cómo comprar">
          {[['', 'Todo'], ['on', 'En línea'], ['q', 'Cotizar']].map(([value, label]) => (
            <button key={label} type="button" className={st.mode === value ? 'on' : ''} aria-pressed={st.mode === value} onClick={() => set({ mode: value })}>
              {label}
            </button>
          ))}
        </div>
      </div>

      {brands.length > 0 && (
        <div className="cat-fsec">
          <h4>Marca</h4>
          <div className="cat-chips">
            {brands.map((b) => (
              <button key={b.name} type="button" className={`cat-chip ${st.brands.includes(b.name) ? 'on' : ''}`} aria-pressed={st.brands.includes(b.name)} onClick={() => toggleBrand(b.name)}>
                {b.name}
              </button>
            ))}
          </div>
        </div>
      )}

      {ranges.hp.length > 0 && (
        <div className="cat-fsec">
          <h4>Potencia</h4>
          <div className="cat-chips">
            {HP_RANGES.filter((r) => ranges.hp.includes(r) || st.hp === r.key).map((r) => (
              <button key={r.key} type="button" className={`cat-chip ${st.hp === r.key ? 'on' : ''}`} aria-pressed={st.hp === r.key} onClick={() => set({ hp: st.hp === r.key ? '' : r.key })}>
                {r.label}
              </button>
            ))}
          </div>
        </div>
      )}

      {ranges.amp.length > 0 && (
        <div className="cat-fsec">
          <h4>Corriente</h4>
          <div className="cat-chips">
            {AMP_RANGES.filter((r) => ranges.amp.includes(r) || st.amp === r.key).map((r) => (
              <button key={r.key} type="button" className={`cat-chip ${st.amp === r.key ? 'on' : ''}`} aria-pressed={st.amp === r.key} onClick={() => set({ amp: st.amp === r.key ? '' : r.key })}>
                {r.label}
              </button>
            ))}
          </div>
        </div>
      )}

      <div className="cat-fsec">
        <h4>Disponibilidad</h4>
        <div className="cat-chips">
          <button type="button" className={`cat-chip ${st.stock ? 'on' : ''}`} aria-pressed={st.stock} onClick={() => set({ stock: !st.stock })}>
            Con unidades en bodega
          </button>
        </div>
      </div>
    </div>
  )
}
