// Marco de foto 4:3 de «Nosotros». Sin foto muestra un panel lila con cuadrícula de plano, el logo como marca de agua, el título
// del espacio y la etiqueta «Foto próximamente». Cuando llegue la foto (`photo` y `alt`) reemplaza el panel sin tocar el diseño.
export default function PhotoSlot({ title, photo, alt }) {
  if (photo) {
    return (
      <figure className="slot">
        <img src={photo} alt={alt} width="1200" height="900" loading="lazy" />
      </figure>
    )
  }
  return (
    <figure className="slot" aria-label={`${title}: foto próximamente`}>
      <span className="slot__grid" aria-hidden="true" />
      <img className="slot__mark" src="/img/logo-ajustado.png" alt="" width="120" height="145" aria-hidden="true" />
      <figcaption className="slot__body">
        <h2 className="slot__title">{title}</h2>
        <span className="slot__soon">Foto próximamente</span>
      </figcaption>
    </figure>
  )
}
