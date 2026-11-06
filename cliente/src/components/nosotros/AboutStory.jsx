import { Camera } from 'lucide-react'
import { site } from '../../config/site'
import { useFamilies } from '../../hooks/useFamilies'
import Bezel from '../ui/Bezel'
import Counter from '../ui/Counter'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import Reveal from '../ui/Reveal'

// Espacio reservado para material real de la empresa (no se representa nada que no exista)
function Slot({ title, hint }) {
  return (
    <Bezel className="ab-slot">
      <div className="ab-slot__core">
        <Camera size={28} aria-hidden="true" />
        <strong>{title}</strong>
        <span>{hint}</span>
      </div>
    </Bezel>
  )
}

// Historia y propósito, cifras y espacios para fotos o video de la operación
export default function AboutStory() {
  const { meta } = useFamilies()

  return (
    <section className="wrap ab-story" aria-labelledby="h-historia">
      <Reveal className="ab-story__text">
        <Eyebrow>Nuestra historia</Eyebrow>
        <h2 id="h-historia" className="h2-sys">Soluciones <em>industriales</em></h2>
        <p>
          Imagen Eléctrica es una distribuidora de material eléctrico industrial en Bucaramanga, Santander. Con más de {site.years} años de
          experiencia acompañamos a empresas y personas con motores, variadores, cables, iluminación y automatización.
        </p>
        <p>
          Hoy llevamos esa experiencia a la tienda en línea: compra lo que está disponible o pide tu cotización sin cuenta y un asesor te responde.
        </p>
        <Pill as="link" to="/productos" tone="d">Ver el catálogo</Pill>
      </Reveal>

      <Reveal className="ab-story__nums" delay={0.08}>
        <Bezel>
          <ul className="ab-nums">
            <li><Counter to={site.years} prefix="+" className="ab-nums__v" /><span>años de experiencia</span></li>
            <li><Counter to={meta.references || 351} className="ab-nums__v" /><span>referencias en inventario</span></li>
            <li><Counter to={meta.lines || 33} className="ab-nums__v" /><span>líneas de producto</span></li>
          </ul>
        </Bezel>
      </Reveal>

      <Reveal className="ab-story__slots" delay={0.12}>
        <Slot title="Foto del equipo" hint="Espacio reservado: foto real de las personas de Imagen Eléctrica" />
        <Slot title="Interior y operación" hint="Espacio reservado: foto o video real de la tienda y la bodega" />
      </Reveal>
    </section>
  )
}
