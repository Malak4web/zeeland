<script setup>
import { fryRules } from '../data/site.js'
import { t } from '../i18n.js'
</script>

<template>
  <section id="fry" class="relative">
    <!-- the heat moment, full bleed -->
    <div class="relative isolate min-h-[26rem] overflow-hidden lg:min-h-[34rem]">
      <img
        src="/img/fry-basket-dark.jpg"
        :alt="t.fry.altBasket"
        width="2400"
        height="1600"
        loading="lazy"
        decoding="async"
        class="absolute inset-0 -z-10 size-full object-cover object-center"
      />
      <div
        class="absolute inset-0 -z-10 bg-gradient-to-t from-abyss via-abyss/78 to-abyss/45"
        aria-hidden="true"
      />
      <div
        class="absolute inset-0 -z-10 bg-gradient-to-l from-abyss/85 via-transparent to-abyss/40"
        aria-hidden="true"
      />

      <div class="container-zl flex min-h-[26rem] flex-col justify-end py-16 lg:min-h-[34rem] lg:py-20">
        <p v-reveal class="mb-4 text-[0.75rem] text-flame-ink">
          <span class="num tracking-[0.3em] uppercase">{{ t.fry.param }}</span>
        </p>
        <h2 v-reveal="60" class="text-h2 max-w-[16ch] text-cream">
          {{ t.fry.title }}
        </h2>
        <p v-reveal="120" class="prose-zl mt-5">
          {{ t.fry.lead }}
        </p>
      </div>
    </div>

    <!-- the four rules -->
    <div class="container-zl pt-14 pb-[clamp(4.5rem,10vh,7.5rem)]">
      <div class="grid gap-x-14 sm:grid-cols-2">
        <article
          v-for="(rule, i) in fryRules"
          :key="rule.title"
          v-reveal="i * 70"
          class="border-t border-navy-2 py-8"
          :class="i < 2 ? 'sm:border-t-0 sm:pt-0' : ''"
        >
          <p class="mb-3 flex items-center gap-2.5 text-[0.85rem] text-cream-3">
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              class="shrink-0 text-steel"
              aria-hidden="true"
            >
              <circle cx="12" cy="12" r="9" />
              <path d="m5.6 5.6 12.8 12.8" />
            </svg>
            <span class="line-through decoration-steel">{{ rule.bad }}</span>
          </p>
          <h3 class="text-h3 text-cream">{{ rule.title }}</h3>
          <p class="mt-3 max-w-[52ch] text-[1rem] leading-[1.9] text-cream-2">{{ rule.body }}</p>
        </article>
      </div>
    </div>
  </section>
</template>
