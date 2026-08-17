<script setup>
import SpecScale from './SpecScale.vue'
import { santanaSpecs } from '../data/site.js'
import { t } from '../i18n.js'
</script>

<template>
  <section id="santana" class="relative py-[clamp(4.5rem,10vh,7.5rem)]">
    <div class="container-zl">
      <div class="max-w-3xl">
        <h2 v-reveal class="text-h2 text-cream">
          {{ t.santana.title }}<br />
          <span class="text-cream-3">{{ t.santana.titleHighlight }}</span>
        </h2>
        <p v-reveal="80" class="prose-zl mt-6">
          {{ t.santana.lead }}
        </p>
      </div>

      <div class="mt-14 grid gap-x-14 gap-y-12 lg:grid-cols-12">
        <!-- the field -->
        <figure v-reveal class="lg:col-span-5 lg:sticky lg:top-28 lg:self-start">
          <div class="overflow-hidden rounded-[1.5rem] border border-navy-2">
            <img
              src="/img/potato-harvest.jpg"
              :alt="t.santana.photoAlt"
              width="1800"
              height="1200"
              loading="lazy"
              decoding="async"
              class="aspect-4/3 w-full object-cover lg:aspect-3/4"
            />
          </div>

          <figcaption
            class="mt-6 border-s-0 ps-0 font-display text-[1.45rem] leading-snug font-bold text-cream"
          >
            {{ t.santana.quoteCaption }}
          </figcaption>
          <p class="mt-3 text-[0.9rem] text-cream-3">
            {{ t.santana.qcNote }}
          </p>
        </figure>

        <!-- the readings -->
        <div class="lg:col-span-7">
          <p v-reveal class="mb-8 text-[0.72rem] text-cream-3">
            <span class="num tracking-[0.28em] uppercase">{{ t.santana.acceptanceWindow }}</span>
          </p>

          <div class="flex flex-col">
            <article
              v-for="(spec, i) in santanaSpecs"
              :key="spec.key"
              v-reveal="i * 70"
              class="border-t border-navy-2 py-7 first:border-t-0 first:pt-0"
            >
              <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2">
                <h3 class="text-h3 text-cream">{{ spec.title }}</h3>
                <p
                  class="num text-[1.6rem] leading-none font-semibold"
                  :class="spec.invert ? 'text-frost-ink' : 'text-flame-ink'"
                >
                  {{ spec.value
                  }}<span class="text-[0.6em] text-cream-3">{{ spec.unit }}</span>
                </p>
              </div>

              <SpecScale class="mt-5" :fill="spec.fill" :invert="spec.invert" />

              <p class="mt-4 max-w-[60ch] text-[0.98rem] leading-[1.9] text-cream-2">
                {{ spec.why }}
              </p>
            </article>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
