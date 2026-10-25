import { Link } from 'react-router-dom'
import { ChevronRight } from 'lucide-react'
import '../../styles/page-header.css'

// Encabezado de las páginas internas: banda violeta que pasa por debajo del encabezado flotante.
// cover: foto opcional de portada (con degradado ink solo del lado del texto).
// title: texto blanco; accent: la palabra de acento en naranja. crumbs: [{ label, to? }] sin el Inicio.
export default function PageHeader({ title, accent, crumbs = [], cover, children }) {
  return (
    <header className={`page-header ${cover ? 'page-header--cover' : ''}`}>
      {cover && <img className="page-header__cover" src={cover} alt="" fetchPriority="high" />}
      <span className="page-header__arc page-header__arc--orange" aria-hidden="true" />
      <span className="page-header__arc page-header__arc--soft" aria-hidden="true" />
      <div className="container page-header__inner">
        <nav aria-label="Migas de pan" className="crumbs">
          <ol>
            <li><Link to="/">Inicio</Link></li>
            {crumbs.map((c) => (
              <li key={c.label}>
                <ChevronRight size={14} aria-hidden="true" />
                {c.to ? <Link to={c.to}>{c.label}</Link> : <span aria-current="page">{c.label}</span>}
              </li>
            ))}
          </ol>
        </nav>
        <h1 className="display page-header__title">
          {title} {accent && <em>{accent}</em>}
        </h1>
        {children && <p className="page-header__lead">{children}</p>}
      </div>
    </header>
  )
}
