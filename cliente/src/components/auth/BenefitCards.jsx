import Bezel from '../ui/Bezel'

// Tres beneficios en tarjetas de bisel pequeñas con icono de línea fina
export default function BenefitCards({ items }) {
  return (
    <ul className="benefit-cards" aria-label="Beneficios de tener cuenta">
      {items.slice(0, 3).map(({ icon: Icon, text }) => (
        <li key={text}>
          <Bezel className="benefit-card">
            <div className="benefit-card__core">
              <span className="benefit-card__ic"><Icon size={22} strokeWidth={1.25} aria-hidden="true" /></span>
              <span>{text}</span>
            </div>
          </Bezel>
        </li>
      ))}
    </ul>
  )
}
