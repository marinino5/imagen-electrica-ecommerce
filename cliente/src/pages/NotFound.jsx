import { Link } from 'react-router-dom'
import '../styles/pages.css'

export default function NotFound() {
  return (
    <section className="page-narrow">
      <p className="eyebrow">Error 404</p>
      <h1 className="page-title">No encontramos esa página</h1>
      <p className="page-lead">Puede que el enlace haya cambiado. Vuelve al inicio o busca en el catálogo.</p>
      <div className="row-actions">
        <Link to="/" className="btn btn--primary">Ir al inicio</Link>
        <Link to="/productos" className="btn btn--ghost">Ver productos</Link>
      </div>
    </section>
  )
}
