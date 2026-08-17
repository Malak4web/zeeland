<script setup>
import { packFacts } from '../data/site.js'
import { t } from '../i18n.js'
</script>

<template>
  <section aria-labelledby="band-heading" class="relative isolate overflow-hidden">
    <img
      src="/img/brand-kitchen.jpg"
      alt=""
      aria-hidden="true"
      width="1900"
      height="1069"
      loading="lazy"
      decoding="async"
      class="absolute inset-0 -z-20 size-full scale-105 object-cover blur-[3px]"
    />
    <div class="absolute inset-0 -z-10 bg-abyss/84" aria-hidden="true" />
    <div
      class="absolute inset-0 -z-10 bg-gradient-to-t from-abyss via-abyss/60 to-abyss/90"
      aria-hidden="true"
    />

    <div class="container-zl py-[clamp(4rem,10vh,7rem)]">
      <div class="grid items-center gap-x-14 gap-y-9 lg:grid-cols-12">
        <figure v-reveal class="order-2 lg:order-none lg:col-span-5">
          <img
            src="/img/brand-pack.jpg"
            :alt="t.brandBand.packAlt"
            width="1000"
            height="1337"
            loading="lazy"
            decoding="async"
            class="mx-auto w-[66%] max-w-[20rem] rounded-2xl shadow-[0_40px_90px_-28px_var(--shadow-3)] sm:w-[48%] lg:w-full lg:max-w-[26rem]"
          />
        </figure>

        <div class="lg:col-span-6 lg:col-start-7">
          <h2 id="band-heading" v-reveal class="text-h2 text-cream">
            {{ t.brandBand.heading }}
          </h2>
          <p v-reveal="70" class="prose-zl mt-5">
            {{ t.brandBand.lead }}
          </p>

          <dl
            v-reveal="130"
            class="mt-8 grid max-w-xl grid-cols-2 gap-x-6 gap-y-5 sm:grid-cols-4"
          >
            <div v-for="fact in packFacts" :key="fact.label">
              <dt class="text-[0.75rem] text-cream-3">{{ fact.label }}</dt>
              <dd class="mt-1 text-[1.3rem] font-semibold text-cream">
                <span class="num"
                  >{{ fact.value
                  }}<span class="text-[0.72em] text-cream-3">{{ fact.unit }}</span></span
                >
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </section>
</template>
