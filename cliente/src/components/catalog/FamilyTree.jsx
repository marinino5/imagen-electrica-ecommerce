import { ChevronDown } from 'lucide-react'
import { familyIcon, familyPhoto } from '../../lib/families'

// Columna izquierda: SOLO las familias, como acordeón. Cada una con su miniatura de foto y su contador; al abrirse (con
// animación) muestra sus categorías. La activa queda resaltada con una barra naranja.
export default function FamilyTree({ families, st, set }) {
  const total = families.reduce((n, f) => n + f.lines_count, 0)

  return (
    <nav className="fam-tree" aria-label="Familias de productos">
      <h2 className="fam-tree__title">Familias</h2>
      <ul>
        <li>
          <button type="button" className={`fam-tree__all ${!st.family ? 'on' : ''}`} aria-current={!st.family ? 'true' : undefined} onClick={() => set({ family: '', category: '' })}>
            Todo el catálogo <i>{total}</i>
          </button>
        </li>
        {families.map((f) => {
          const open = st.family === f.slug
          const photo = familyPhoto(f.slug)
          const Icon = familyIcon(f.slug)
          return (
            <li key={f.id} className={`fam-tree__fam ${open ? 'open' : ''}`}>
              <button type="button" className="fam-tree__row" aria-expanded={open} aria-controls={`cats-${f.slug}`} onClick={() => set({ family: open && !st.category ? '' : f.slug, category: '' })}>
                <span className="fam-tree__thumb" aria-hidden="true">
                  {photo ? <img src={photo.sm} alt="" width="44" height="44" loading="lazy" /> : <Icon size={20} strokeWidth={1.6} />}
                </span>
                <span className="fam-tree__name">{f.name}</span>
                <i>{f.lines_count}</i>
                <ChevronDown size={16} aria-hidden="true" className="fam-tree__chev" />
              </button>
              <div className="fam-tree__cats" id={`cats-${f.slug}`} aria-hidden={!open}>
                <ul>
                  <li><button type="button" tabIndex={open ? 0 : -1} className={open && !st.category ? 'on' : ''} onClick={() => set({ family: f.slug, category: '' })}>Ver toda la familia <span>{f.lines_count}</span></button></li>
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
