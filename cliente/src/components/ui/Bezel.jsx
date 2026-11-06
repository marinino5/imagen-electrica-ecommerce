// Doble bisel: marco exterior translúcido (padding 6 px, radio 30 px) y núcleo blanco (radio 24 px).
export default function Bezel({ as: Tag = 'div', className = '', coreClassName = '', children, ...props }) {
  return (
    <Tag className={`bz ${className}`} {...props}>
      <div className={`core ${coreClassName}`}>{children}</div>
    </Tag>
  )
}
