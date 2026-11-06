import { useState } from 'react'
import { AMP_RANGES, HP_RANGES } from '../../lib/lines'
import FamilyTree from './FamilyTree'

const TOP_BRANDS = 6

// Filtros del catálogo, en este orden: familias → categorías, cómo comprar, marca, disponibilidad y, solo donde
// aplican, potencia o corriente. Es el mismo panel en la columna de escritorio y en la hoja del celular.
export default function CatalogFilters({ st, set, toggleBrand, brands, families, ranges, tech, onReset, activeCount }) {
  const [moreBrands, setMoreBrands] = useState(false)
  // Las marcas ya vienen ordenadas por número de fichas: se muestran las 6 con más y el resto tras «Ver más»
  const hiddenSelected = brands.slice(TOP_BRANDS).some((b) => st.brands.includes(b.name))
  const expanded = moreBrands || hiddenSelected
  const shownBrands = expanded ? brands : brands.slice(0, TOP_BRANDS)

  return (
    <div className="cat-filters">
      <FamilyTree families={families} st={st} set={set} />

      <div className="cat-fsec">
        <div className="cat-sh">
          <h3>Filtros</h3>
          {activeCount > 0 && <button type="button" className="cat-reset" onClick={onReset}>Limpiar ({activeCount})</button>}
        </div>

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
            {shownBrands.map((b) => (
              <button key={b.name} type="button" className={`cat-chip ${st.brands.includes(b.name) ? 'on' : ''}`} aria-pressed={st.brands.includes(b.name)} onClick={() => toggleBrand(b.name)}>
                {b.name} <span>{b.count}</span>
              </button>
            ))}
          </div>
          {brands.length > TOP_BRANDS && !hiddenSelected && (
            <button type="button" className="cat-more" aria-expanded={moreBrands} onClick={() => setMoreBrands((v) => !v)}>
              {moreBrands ? 'Ver menos' : `Ver más (${brands.length - TOP_BRANDS})`}
            </button>
          )}
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

      {tech.hp && ranges.hp.length > 0 && (
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

      {tech.amp && ranges.amp.length > 0 && (
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
    </div>
  )
}
