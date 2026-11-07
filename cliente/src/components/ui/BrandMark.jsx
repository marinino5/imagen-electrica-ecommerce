import { brandLogo } from '../../config/brands'

// Logo oficial de la marca en tamaño pequeño; si todavía no hay archivo, el nombre en la tipografía de marca
export default function BrandMark({ name, className = '' }) {
  const logo = brandLogo(name)
  if (!logo) return <span className={`brandmark brandmark--text ${className}`}>{name}</span>
  return <img className={`brandmark ${className}`} src={logo} alt={name} loading="lazy" />
}
