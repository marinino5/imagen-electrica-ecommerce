import Hero from '../components/home/Hero'
import HomeStats from '../components/home/HomeStats'
import HomeLines from '../components/home/HomeLines'
import HomeFeatured from '../components/home/HomeFeatured'
import Applications from '../components/home/Applications'
import BrandStrip from '../components/home/BrandStrip'
import HomeQuoteBand from '../components/home/HomeQuoteBand'

export default function Home() {
  return (
    <>
      <Hero />
      <HomeStats />
      <HomeLines />
      <HomeFeatured />
      <Applications />
      <BrandStrip />
      <HomeQuoteBand />
    </>
  )
}
