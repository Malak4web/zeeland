<script setup>
import { audiences, promises, waMessage } from '../data/site.js'
import { t, isRTL } from '../i18n.js'

const offsets = ['lg:mt-0', 'lg:mt-20', 'lg:mt-10']
</script>

<template>
  <section id="foodservice" class="py-[clamp(4.5rem,10vh,7.5rem)]">
    <div class="container-zl">
      <div class="grid gap-x-14 gap-y-6 lg:grid-cols-12 lg:items-end">
        <h2 v-reveal class="text-h2 text-cream lg:col-span-6">
          {{ t.foodservice.titleLine1 }}<br />{{ t.foodservice.titleLine2 }}
        </h2>
        <p v-reveal="80" class="prose-zl lg:col-span-6">
          {{ t.foodservice.lead }}
        </p>
      </div>

      <!-- audiences -->
      <div class="deck mt-14 lg:mt-16 lg:grid lg:grid-cols-3 lg:gap-x-8">
        <article
          v-for="(a, i) in audiences"
          :key="a.title"
          v-reveal="i * 90"
          class="w-[80vw] max-w-[21rem] shrink-0 lg:w-auto lg:max-w-none lg:shrink"
          :class="offsets[i]"
        >
          <div class="overflow-hidden rounded-[1.25rem]">
            <img
              :src="a.image"
              :alt="a.alt"
              width="1200"
              height="1600"
              loading="lazy"
              decoding="async"
              class="aspect-3/4 w-full object-cover transition-transform duration-[900ms] ease-out-expo hover:scale-[1.035]"
            />
          </div>
          <h3 class="text-h3 mt-5 text-cream lg:mt-6">{{ a.title }}</h3>
          <p class="mt-3 text-[0.98rem] leading-[1.85] text-cream-2 lg:text-[1rem] lg:leading-[1.9]">
            {{ a.body }}
          </p>
        </article>
      </div>

      <!-- what you actually get -->
      <div class="mt-20">
        <hr v-reveal class="rule-fade" />
        <div class="mt-10 grid gap-x-14 gap-y-8 sm:grid-cols-2">
          <div v-for="(p, i) in promises" :key="p.title" v-reveal="i * 60" class="flex gap-4">
            <span
              class="num mt-1 min-w-[3.25rem] shrink-0 rounded-md border border-navy-3 px-2.5 py-1 text-center text-[0.8rem] font-semibold whitespace-nowrap text-flame-ink"
            >
              {{ p.value }}<span v-if="p.unit" class="text-cream-3">{{ p.unit }}</span>
            </span>
            <div>
              <h3 class="font-display text-[1.3rem] font-bold text-cream">{{ p.title }}</h3>
              <p class="mt-1.5 max-w-[46ch] text-[0.98rem] leading-[1.85] text-cream-2">
                {{ p.body }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <p v-reveal class="mt-12">
        <a
          :href="waMessage(t.foodservice.sampleWaText)"
          target="_blank"
          rel="noopener"
          class="group inline-flex items-center gap-3 text-[1.05rem] font-medium text-flame-ink transition-colors hover:text-flame-ink-hi"
        >
          {{ t.foodservice.sampleCta }}
          <svg
            width="20"
            height="12"
            viewBox="0 0 20 12"
            fill="none"
            stroke="currentColor"
            stroke-width="1.6"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
            class="transition-transform duration-400 ease-out-expo"
            :class="isRTL ? 'group-hover:-translate-x-1.5' : 'group-hover:translate-x-1.5'"
          >
            <path v-if="isRTL" d="M19 6H1M6 1 1 6l5 5" />
            <path v-else d="M1 6h18M14 1l5 5-5 5" />
          </svg>
        </a>
      </p>
    </div>
  </section>
</template>
