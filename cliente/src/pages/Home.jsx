import { catalogApi } from '../api'
import { useApi } from '../hooks/useApi'
import Hero from '../components/home/Hero'
import BrandStrip from '../components/home/BrandStrip'
import LinesCarousel from '../components/home/LinesCarousel'
import FeaturedRail from '../components/home/FeaturedRail'
import HowItWorks from '../components/home/HowItWorks'
import Applications from '../components/home/Applications'
import JoinCta from '../components/home/JoinCta'
import TrustStrip from '../components/home/TrustStrip'

export default function Home() {
  // Los destacados alimentan la tarjeta de la portada y el carril; se piden una sola vez
  const featured = useApi((signal) => catalogApi.products({ featured: true, per_page: 12 }, signal), [])

  return (
    <>
      <Hero featured={featured.data?.data ?? []} />
      <TrustStrip />
      <BrandStrip />
      <LinesCarousel />
      <FeaturedRail featured={featured} />
      <HowItWorks />
      <Applications />
      <JoinCta />
    </>
  )
}
