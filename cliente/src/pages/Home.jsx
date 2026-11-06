import Hero from '../components/home/Hero'
import BrandStrip from '../components/home/BrandStrip'
import HomeLines from '../components/home/HomeLines'
import Promotions from '../components/home/Promotions'
import HomeFeatured from '../components/home/HomeFeatured'
import Applications from '../components/home/Applications'
import StepsStrip from '../components/home/StepsStrip'
import WhyUs from '../components/home/WhyUs'
import JoinCta from '../components/home/JoinCta'

// El cierre (CTA de cotizar y pie) lo pone Layout. Las cifras viven solo aquí, dentro del hero.
export default function Home() {
  return (
    <>
      <Hero />
      <BrandStrip />
      <HomeLines />
      <Promotions />
      <HomeFeatured />
      <Applications />
      <StepsStrip />
      <WhyUs />
      <JoinCta />
    </>
  )
}
