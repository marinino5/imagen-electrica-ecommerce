import { ChevronDown } from 'lucide-react'
import { familyIcon } from '../../lib/families'

// Árbol de familias → categorías con contadores. Va arriba de los filtros y se ve sin hacer scroll.
// Una familia abierta es la que está filtrando; tocarla otra vez quita el filtro.
export default function FamilyTree({ families, st, set }) {
  const total = families.reduce((n, f) => n + f.lines_count, 0)

  return (
    <nav className="cat-tree" aria-label="Familias y categorías">
      <div className="cat-sh">
        <h3>Familias</h3>
        <small>{total} fichas</small>
      </div>
      <ul>
        <li>
          <button type="button" className={`cat-tree__all ${!st.family ? 'on' : ''}`} aria-current={!st.family ? 'true' : undefined} onClick={() => set({ family: '', category: '' })}>
            Todo el catálogo <i>{total}</i>
          </button>
        </li>
        {families.map((f) => {
          const open = st.family === f.slug
          const Icon = familyIcon(f.slug)
          return (
            <li key={f.id} className={`cat-tree__fam ${open ? 'open' : ''}`}>
              <button
                type="button"
                className="cat-tree__row"
                aria-expanded={open}
                aria-controls={`cats-${f.slug}`}
                onClick={() => set({ family: open && !st.category ? '' : f.slug, category: '' })}
              >
                <span className="cat-tree__ic" aria-hidden="true"><Icon size={18} strokeWidth={1.7} /></span>
                <span className="cat-tree__name">{f.name}</span>
                <i>{f.lines_count}</i>
                <ChevronDown size={16} aria-hidden="true" className="cat-tree__chev" />
              </button>
              <div className="cat-tree__cats" id={`cats-${f.slug}`} aria-hidden={!open}>
                <ul>
                  <li>
                    <button type="button" tabIndex={open ? 0 : -1} className={open && !st.category ? 'on' : ''} onClick={() => set({ family: f.slug, category: '' })}>
                      Ver toda la familia <span>{f.lines_count}</span>
                    </button>
                  </li>
                  {(f.categories ?? []).map((c) => (
                    <li key={c.id}>
                      <button type="button" tabIndex={open ? 0 : -1} className={st.category === c.slug ? 'on' : ''} aria-current={st.category === c.slug ? 'true' : undefined} onClick={() => set({ family: f.slug, category: c.slug })}>
                        {c.name} <span>{c.lines_count}</span>
                      </button>
                    </li>
                  ))}
                </ul>
              </div>
            </li>
          )
        })}
      </ul>
    </nav>
  )
}
