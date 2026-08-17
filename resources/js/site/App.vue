<script setup>
import SiteNav from './components/SiteNav.vue'
import HeroSection from './components/HeroSection.vue'
import TrustSection from './components/TrustSection.vue'
import SantanaSection from './components/SantanaSection.vue'
import JourneySection from './components/JourneySection.vue'
import FrySection from './components/FrySection.vue'
import SpecsSection from './components/SpecsSection.vue'
import FoodserviceSection from './components/FoodserviceSection.vue'
import FaqSection from './components/FaqSection.vue'
import BlogTeaser from './components/BlogTeaser.vue'
import BrandBand from './components/BrandBand.vue'
import QuoteSection from './components/QuoteSection.vue'
import SiteFooter from './components/SiteFooter.vue'
import WhatsappFab from './components/WhatsappFab.vue'
import MobileDock from './components/MobileDock.vue'
</script>

<template>
  <SiteNav />

  <main id="main">
    <HeroSection />
    <TrustSection />
    <SantanaSection />
    <JourneySection />
    <FrySection />
    <SpecsSection />
    <FoodserviceSection />
    <FaqSection />
    <BlogTeaser />
    <BrandBand />
    <QuoteSection />
  </main>

  <SiteFooter />
  <WhatsappFab />
  <MobileDock />
</template>
