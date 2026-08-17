<script setup>
import BrandMark from './BrandMark.vue'
import LangToggle from './LangToggle.vue'
import { contact, navLinks } from '../data/site.js'
import { t } from '../i18n.js'

const year = new Date().getFullYear()
</script>

<template>
  <footer class="border-t border-navy-2 bg-navy/35 pb-[calc(6rem+env(safe-area-inset-bottom))] lg:pb-0">
    <div class="container-zl py-14 lg:py-16">
      <div class="grid gap-x-14 gap-y-10 lg:grid-cols-12">
        <div class="lg:col-span-5">
          <div class="text-cream">
            <BrandMark :size="44" />
          </div>
          <p class="mt-6 max-w-[42ch] text-[1rem] leading-[1.9] text-cream-2">
            {{ t.footer.description }}
          </p>

          <div class="mt-6 flex items-center gap-3">
            <LangToggle compact />
          </div>
        </div>

        <nav aria-labelledby="footer-nav" class="lg:col-span-3">
          <h2 id="footer-nav" class="text-[0.82rem] tracking-wide text-cream-3">{{ t.footer.navHeading }}</h2>
          <ul class="mt-4 flex flex-col gap-1">
            <li v-for="link in navLinks" :key="link.href">
              <a
                :href="link.href"
                class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi"
                >{{ link.label }}</a
              >
            </li>
            <li>
              <a
                href="#quote"
                class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi"
                >{{ t.footer.quoteLink }}</a
              >
            </li>
          </ul>
        </nav>

        <div class="lg:col-span-4">
          <h2 class="text-[0.82rem] tracking-wide text-cream-3">{{ t.footer.contactHeading }}</h2>
          <ul class="mt-4 flex flex-col gap-2 text-cream-2">
            <li>
              <a
                :href="contact.phoneHref"
                class="num inline-block py-1 text-[1.05rem] text-cream transition-colors hover:text-flame-ink-hi"
                >{{ contact.phoneDisplay }}</a
              >
            </li>
            <li>
              <a
                :href="`mailto:${contact.email}`"
                class="num inline-block py-1 text-[0.95rem] transition-colors hover:text-flame-ink-hi"
                >{{ contact.email }}</a
              >
            </li>
            <li class="pt-1 text-[0.95rem] leading-relaxed text-cream-3">
              <span v-for="line in contact.addressLines" :key="line" class="block">{{ line }}</span>
            </li>
            <li class="text-[0.95rem] text-cream-3">{{ contact.hours }}</li>
          </ul>
        </div>
      </div>

      <hr class="rule-fade my-10" />

      <div class="flex flex-col gap-3 text-[0.85rem] text-cream-3 sm:flex-row sm:justify-between sm:items-center">
        <p><span class="ltr-iso">© {{ year }} {{ contact.company }}</span> — {{ t.common.allRightsReserved }}</p>
        <p class="num tracking-wide" dir="ltr">{{ t.common.productOfEgypt }}</p>
      </div>
    </div>
  </footer>
</template>
