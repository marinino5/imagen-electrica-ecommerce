// Ventana de navegador dibujada en CSS: barra con puntos y ruta, y el contenido de la mini pantalla
export default function BrowserWindow({ path, children, className = '' }) {
  return (
    <div className={`bwin ${className}`}>
      <div className="bwin__bar" aria-hidden="true">
        <span className="bwin__dots"><i /><i /><i /></span>
        <span className="bwin__url">{path}</span>
      </div>
      <div className="bwin__screen">{children}</div>
    </div>
  )
}
