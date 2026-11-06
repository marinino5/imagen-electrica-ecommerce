import { ChevronDown } from 'lucide-react'

// Líneas: acordeón de familias → categorías. Al elegir una categoría, solo queda abierta la familia activa.
export default function LinesNav({ families, st, set }) {
  const total = families.reduce((n, f) => n + f.lines_count, 0)

  return (
    <nav className="cat-lines" aria-label="Líneas del portafolio">
      <div className="cat-sh">
        <h3>Líneas</h3>
        <small>{total} fichas</small>
      </div>
      {families.map((f) => {
        const open = st.family === f.slug
        return (
          <div key={f.id} className={`cat-fam ${open ? 'open' : ''}`}>
            <button type="button" aria-expanded={open} onClick={() => set({ family: open && !st.category ? '' : f.slug, category: '' })}>
              <span className="cat-fam__ph" aria-hidden="true">{f.name.charAt(0)}</span>
              <span className="cat-fam__name">{f.name}</span>
              <i>{f.lines_count}</i>
              <ChevronDown size={16} aria-hidden="true" className="cat-fam__chev" />
            </button>
            <div className="cat-cats" aria-hidden={!open}>
              <div>
                <button type="button" tabIndex={open ? 0 : -1} className={open && !st.category ? 'on' : ''} onClick={() => set({ family: f.slug, category: '' })}>
                  Ver todo <span>{f.lines_count}</span>
                </button>
                {f.categories.map((c) => (
                  <button key={c.id} type="button" tabIndex={open ? 0 : -1} className={st.category === c.slug ? 'on' : ''} onClick={() => set({ family: f.slug, category: c.slug })}>
                    {c.name} <span>{c.lines_count}</span>
                  </button>
                ))}
              </div>
            </div>
          </div>
        )
      })}
    </nav>
  )
}
