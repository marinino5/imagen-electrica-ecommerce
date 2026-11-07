import { useEffect, useRef } from 'react'
import { familyIcon, familyPhoto } from '../../lib/families'

// Celular: las familias en una fila de chips con miniatura, fija bajo la barra de búsqueda. Con el catálogo agrupado el chip
// activo cambia solo con el scroll (`spy`) y la fila se desplaza para mostrarlo; pulsar un chip lleva a su grupo.
export default function FamilyChips({ families, st, set, grouped = false, spy = '', onJump }) {
  const list = useRef(null)

  useEffect(() => {
    const el = list.current?.querySelector('[aria-current="true"]')
    const box = list.current
    if (!el || !box) return
    box.scrollTo({ left: el.offsetLeft - (box.clientWidth - el.offsetWidth) / 2, behavior: 'smooth' })
  }, [spy, st.family])

  const pick = (slug) => (grouped ? onJump?.(slug) : set({ family: slug, category: '' }))
  const isOn = (slug) => (grouped ? spy === slug : st.family === slug)

  return (
    <nav className="fam-chips" aria-label="Familias de productos">
      <ul ref={list}>
        <li>
          <button type="button" className={!st.family && !spy ? 'on' : ''} aria-current={!st.family && !spy ? 'true' : undefined} onClick={() => set({ family: '', category: '' })}>
            <span className="fam-chips__img fam-chips__all" aria-hidden="true">Todo</span>
            <span>Catálogo</span>
          </button>
        </li>
        {families.map((f) => {
          const photo = familyPhoto(f.slug)
          const Icon = familyIcon(f.slug)
          return (
            <li key={f.id}>
              <button type="button" className={isOn(f.slug) ? 'on' : ''} aria-current={isOn(f.slug) ? 'true' : undefined} onClick={() => pick(f.slug)}>
                <span className="fam-chips__img" aria-hidden="true">
                  {photo ? <img src={photo.sm} alt="" width="32" height="32" loading="lazy" /> : <Icon size={18} />}
                </span>
                <span>{f.name}</span>
              </button>
            </li>
          )
        })}
      </ul>
    </nav>
  )
}
