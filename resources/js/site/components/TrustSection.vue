<script setup>
import { certifications } from '../data/site.js'
import { t } from '../i18n.js'
</script>

<template>
  <section aria-labelledby="trust-heading" class="border-y border-navy-2 bg-navy/45">
    <div class="container-zl py-12 lg:py-14">
      <h2 id="trust-heading" v-reveal class="text-[0.95rem] text-cream-3">
        {{ t.trust.heading }}
      </h2>

      <ul class="mt-7 grid gap-x-12 gap-y-8 sm:grid-cols-3">
        <li
          v-for="(cert, i) in certifications"
          :key="cert.code"
          v-reveal="i * 70"
          class="sm:border-e sm:border-navy-2 sm:pe-12 sm:last:border-e-0"
        >
          <p class="text-[1.35rem] leading-none font-bold text-cream">
            <span class="num tracking-tight">{{ cert.code }}</span>
          </p>
          <p class="mt-2.5 text-[0.98rem] font-medium text-cream-2">{{ cert.name }}</p>
          <p class="mt-1.5 text-[0.86rem] leading-relaxed text-cream-3">{{ cert.note }}</p>
        </li>
      </ul>
    </div>
  </section>
</template>
