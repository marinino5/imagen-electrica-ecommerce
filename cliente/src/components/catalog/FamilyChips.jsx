import { familyIcon, familyPhoto } from '../../lib/families'

// Celular: las familias en un carrusel horizontal de chips con la foto arriba
export default function FamilyChips({ families, st, set }) {
  return (
    <nav className="fam-chips" aria-label="Familias de productos">
      <ul>
        <li>
          <button type="button" className={!st.family ? 'on' : ''} aria-current={!st.family ? 'true' : undefined} onClick={() => set({ family: '', category: '' })}>
            <span className="fam-chips__img fam-chips__all" aria-hidden="true">Todo</span>
            <span>Catálogo</span>
          </button>
        </li>
        {families.map((f) => {
          const photo = familyPhoto(f.slug)
          const Icon = familyIcon(f.slug)
          return (
            <li key={f.id}>
              <button type="button" className={st.family === f.slug ? 'on' : ''} aria-current={st.family === f.slug ? 'true' : undefined} onClick={() => set({ family: f.slug, category: '' })}>
                <span className="fam-chips__img" aria-hidden="true">
                  {photo ? <img className={photo.product ? 'prod-img' : 'is-photo'} src={photo.src} alt="" width="64" height="64" loading="lazy" /> : <Icon size={24} />}
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
