import Hero from '../components/home/Hero'
import HomeLines from '../components/home/HomeLines'
import HomeDeals from '../components/home/HomeDeals'
import BrandBlocks from '../components/home/BrandBlocks'
import SectorsFlip from '../components/home/SectorsFlip'
import BrandStrip from '../components/home/BrandStrip'
import Advisory from '../components/home/Advisory'

// Orden: Hero → Familias → En promoción y tendencia → Bloque de marca (asesoría y entrega) → Sectores y aplicaciones →
// Marcas (franja propia, antesala del cierre) → Asesoría y contacto. El pie lo pone Layout.
export default function Home() {
  return (
    <>
      <Hero />
      <HomeLines />
      <HomeDeals />
      <BrandBlocks />
      <SectorsFlip />
      <BrandStrip />
      <Advisory />
    </>
  )
}
