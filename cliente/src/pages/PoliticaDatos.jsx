import { Info } from 'lucide-react'
import PageHeader from '../components/ui/PageHeader'
import { site } from '../config/site'
import '../styles/info-pages.css'

// Solo la estructura: el texto legal lo valida la empresa. No se redacta ni se inventa contenido aquí.
const sections = [
  { id: 'responsable', title: 'Responsable del tratamiento' },
  { id: 'datos', title: 'Datos que recopilamos' },
  { id: 'finalidades', title: 'Finalidades del tratamiento' },
  { id: 'derechos', title: 'Derechos de los titulares' },
  { id: 'ejercer', title: 'Cómo ejercer tus derechos' },
  { id: 'vigencia', title: 'Vigencia y cambios' },
]

export default function PoliticaDatos() {
  return (
    <>
      <PageHeader title="Política de" accent="datos" crumbs={[{ label: 'Política de datos' }]} />

      <section className="section">
        <div className="container policy">
          <p className="alert alert--warning policy__notice" role="note">
            <Info size={18} aria-hidden="true" /> Texto en validación con la empresa
          </p>

          <nav className="card policy__toc" aria-label="Contenido de la política">
            <h2 className="block-title">Contenido</h2>
            <ol>
              {sections.map((s) => (
                <li key={s.id}><a href={`#${s.id}`}>{s.title}</a></li>
              ))}
            </ol>
          </nav>

          <div className="policy__body">
            {sections.map((s, i) => (
              <article key={s.id} id={s.id} className="card policy__section">
                <h2><span>{String(i + 1).padStart(2, '0')}</span> {s.title}</h2>
                {s.id === 'responsable' ? (
                  <p>
                    {site.name} · {site.address}, {site.city} · <a href={`mailto:${site.email}`}>{site.email}</a>
                  </p>
                ) : null}
                <p className="policy__pending">Contenido pendiente de validación con la empresa.</p>
              </article>
            ))}
          </div>
        </div>
      </section>
    </>
  )
}
