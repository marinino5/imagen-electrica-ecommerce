// Producto recortado sobre un pedestal (elipse lila con borde de luz y sombra de contacto). Ver .ped en ie.css.
export default function Pedestal({ src, alt = '', lift = false, className = '', style, ...img }) {
  return (
    <figure className={`ped ${lift ? 'ped--lift' : ''} ${className}`} style={style}>
      <img src={src} alt={alt} width="1000" height="1000" decoding="async" {...img} />
    </figure>
  )
}
