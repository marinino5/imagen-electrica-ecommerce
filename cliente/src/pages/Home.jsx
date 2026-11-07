import Hero from '../components/home/Hero'
import BrandStrip from '../components/home/BrandStrip'
import HomeLines from '../components/home/HomeLines'
import BrandBlocks from '../components/home/BrandBlocks'
import Promotions from '../components/home/Promotions'
import HomeFeatured from '../components/home/HomeFeatured'
import Solutions from '../components/home/Solutions'
import Sectors from '../components/home/Sectors'
import Advisory from '../components/home/Advisory'

// Orden: Hero → Marcas → Familias → Bloques de marca → Promociones → Destacados → Aplicaciones → Sectores → Asesoría.
// El cierre es la sección de asesoría (único llamado a la acción del Inicio); el pie lo pone Layout.
export default function Home() {
  return (
    <>
      <Hero />
      <BrandStrip />
      <HomeLines />
      <BrandBlocks />
      <Promotions />
      <HomeFeatured />
      <Solutions />
      <Sectors />
      <Advisory />
    </>
  )
}
