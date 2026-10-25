import { Link } from 'react-router-dom'
import PageHeader from '../components/ui/PageHeader'
import '../styles/pages.css'

// Páginas del menú que se construyen en una etapa posterior
export default function Pending({ title }) {
  return (
    <>
      <PageHeader title={title} crumbs={[{ label: title }]} />
      <section className="page-narrow">
        <p className="page-lead">Estamos preparando esta página. Mientras tanto, explora el catálogo o escríbenos por WhatsApp.</p>
        <Link to="/productos" className="btn btn--primary">Ver productos</Link>
      </section>
    </>
  )
}
