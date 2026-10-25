import { Link } from 'react-router-dom'
import '../styles/pages.css'

// Páginas del menú que se construyen en una etapa posterior
export default function Pending({ title }) {
  return (
    <section className="page-narrow">
      <h1 className="page-title">{title}</h1>
      <p className="page-lead">Estamos preparando esta página. Mientras tanto, explora el catálogo o escríbenos por WhatsApp.</p>
      <Link to="/productos" className="btn btn--primary">Ver productos</Link>
    </section>
  )
}
