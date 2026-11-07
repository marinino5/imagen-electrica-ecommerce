import { Link } from 'react-router-dom'
import SectionLabel from '../ui/SectionLabel'
import Hl from '../ui/Hl'
import '../../styles/home.css'

// Sectores que atiende la empresa: fila de fotos en scroll horizontal con el texto sobre un degradado (idea de ABB)
const sectors = [
  { name: 'Construcción', text: 'Tableros de obra, iluminación y cable para proyectos nuevos.', photo: '/img/fotos/sector-construccion.webp', alt: 'Obrero con chaleco y casco junto a una mezcladora de concreto', q: 'tablero' },
  { name: 'Agroindustria', text: 'Motores y variadores para silos, trilladoras y secadoras.', photo: '/img/fotos/sector-agroindustria.webp', alt: 'Silos de almacenamiento de grano', q: 'motor' },
  { name: 'Petróleo y gas', text: 'Motores a prueba de explosión para zonas clasificadas.', photo: '/img/fotos/sector-petroleo.webp', alt: 'Planta de procesamiento de petróleo y gas de noche', q: 'explosión' },
  { name: 'Alimentos', text: 'Control y protección para líneas de producción continuas.', photo: '/img/fotos/sector-alimentos.webp', alt: 'Operarios en una planta de procesamiento de alimentos', q: 'variador' },
  { name: 'Talleres', text: 'Motores, arrancadores y cable para el taller que no se detiene.', photo: '/img/fotos/sector-talleres.webp', alt: 'Mecánico trabajando en un taller', q: 'monofásico' },
  { name: 'Minería', text: 'Motorreducción y control para equipos de trabajo pesado.', photo: '/img/fotos/sector-mineria.webp', alt: 'Excavadora gigante en una mina a cielo abierto', q: 'reductor' },
]

export default function Sectors() {
  return (
    <section className="rd-sec bg-lav hm-sectors" aria-labelledby="h-sectores">
      <div className="wrap">
        <SectionLabel n="07">Sectores</SectionLabel>
        <h2 id="h-sectores" className="rd-h2">Equipamos a quienes <Hl>mueven el país</Hl></h2>
      </div>
      <ul className="sectors" tabIndex={0} aria-label="Sectores que atendemos (desliza para ver más)">
        {sectors.map((s) => (
          <li key={s.name}>
            <Link to={`/productos?q=${encodeURIComponent(s.q)}`} className="sector lift">
              <img className="zoom" src={s.photo} alt={s.alt} width="800" height="1000" loading="lazy" />
              <span className="sector__txt">
                <strong>{s.name}</strong>
                <span>{s.text}</span>
              </span>
            </Link>
          </li>
        ))}
      </ul>
    </section>
  )
}
