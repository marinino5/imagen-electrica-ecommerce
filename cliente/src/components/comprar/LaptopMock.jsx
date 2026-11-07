// Portátil con la pantalla real de la tienda (captura del buscador). Es decoración: el sentido lo da `alt` en el contenedor.
export default function LaptopMock({ alt }) {
  return (
    <div className="laptop" role="img" aria-label={alt}>
      <div className="laptop__lid">
        <span className="laptop__cam" aria-hidden="true" />
        <div className="laptop__screen"><img src="/img/mockups/laptop.webp" alt="" width="1280" height="800" loading="lazy" /></div>
      </div>
      <div className="laptop__base" aria-hidden="true" />
    </div>
  )
}
