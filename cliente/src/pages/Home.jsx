import Hero from '../components/home/Hero'
import HomeStats from '../components/home/HomeStats'
import HomeLines from '../components/home/HomeLines'
import HomeFeatured from '../components/home/HomeFeatured'
import HowItWorks from '../components/home/HowItWorks'
import JoinCta from '../components/home/JoinCta'
import Applications from '../components/home/Applications'
import BrandStrip from '../components/home/BrandStrip'

export default function Home() {
  return (
    <>
      <Hero />
      <HomeStats />
      <HomeLines />
      <HomeFeatured />
      <HowItWorks />
      <Applications />
      <JoinCta />
      <BrandStrip />
    </>
  )
}
