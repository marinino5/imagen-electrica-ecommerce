// Titular de pesos mezclados: la primera parte en Barlow Condensed 300 y la segunda en 700.
export default function Mix({ light, children }) {
  return (
    <>
      <span className="mix-l">{light}</span> {children}
    </>
  )
}
