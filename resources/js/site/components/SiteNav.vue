<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import BrandMark from './BrandMark.vue'
import AppSheet from './AppSheet.vue'
import ThemeToggle from './ThemeToggle.vue'
import LangToggle from './LangToggle.vue'
import { navLinks, contact, waDefault } from '../data/site.js'
import { t } from '../i18n.js'

const scrolled = ref(false)
const hidden = ref(false)
const menu = ref(false)

/* Native nav-bar behaviour on phones: the bar gets out of the way while you
   read downward and comes straight back the moment you reach for it. */
let lastY = 0
let ticking = false

function measure() {
  ticking = false
  const y = Math.max(0, window.scrollY)
  scrolled.value = y > 24
  const delta = y - lastY
  if (Math.abs(delta) > 6) {
    hidden.value = delta > 0 && y > 180
    lastY = y
  }
}
function onScroll() {
  if (ticking) return
  ticking = true
  requestAnimationFrame(measure)
}

onMounted(() => {
  lastY = window.scrollY
  measure()
  window.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))

function goto(href) {
  menu.value = false
  // A page link leaves the site section; only anchors get the scroll treatment.
  if (!href.startsWith('#')) {
    window.location.href = href
    return
  }
  setTimeout(() => document.querySelector(href)?.scrollIntoView({ block: 'start' }), 260)
}
</script>

<template>
  <a
    href="#main"
    class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-[60] focus:rounded-full focus:bg-flame focus:px-5 focus:py-2.5 focus:font-medium focus:text-on-flame"
  >
    {{ t.common.skipToContent }}
  </a>

  <header
    class="fixed inset-x-0 top-0 z-20 transition-[background-color,backdrop-filter,border-color,transform] duration-400 ease-out-quart"
    :class="[
      scrolled ? 'border-b border-navy-2 bg-abyss/88 backdrop-blur-xl' : 'border-b border-transparent',
      hidden ? '-translate-y-full lg:translate-y-0' : 'translate-y-0',
    ]"
  >
    <div class="container-zl flex h-16 items-center justify-between gap-4 xl:gap-6 lg:h-[4.5rem]">
      <a href="#top" class="shrink-0 text-cream transition-opacity active:opacity-70" :aria-label="t.common.home">
        <BrandMark :size="34" />
      </a>

      <nav :aria-label="t.common.pageSections" class="hidden lg:block">
        <ul class="flex items-center gap-0.5 xl:gap-1.5">
          <li v-for="link in navLinks" :key="link.href">
            <a
              :href="link.href"
              class="relative block rounded-full px-3 py-2 text-[0.88rem] xl:px-4 xl:text-[0.95rem] font-medium text-cream-2 whitespace-nowrap transition-colors duration-300 hover:bg-navy-2 hover:text-cream"
            >
              {{ link.label }}
            </a>
          </li>
        </ul>
      </nav>

      <div class="flex shrink-0 items-center gap-2 sm:gap-2.5">
        <!-- Language toggle on desktop -->
        <LangToggle class="hidden sm:inline-flex" />

        <!-- Theme toggle on desktop -->
        <ThemeToggle class="hidden lg:grid" />

        <a
          :href="waDefault"
          target="_blank"
          rel="noopener"
          class="hidden rounded-full bg-flame px-5 py-2.5 text-[0.92rem] font-bold text-on-flame shadow-xs transition-[background-color,transform] duration-300 ease-out-quart hover:bg-flame-hi active:scale-[0.97] lg:block"
        >
          {{ t.common.chatWhatsapp }}
        </a>

        <a
          :href="contact.phoneHref"
          class="grid size-10 place-items-center rounded-full border border-navy-3 text-cream transition-transform duration-300 ease-out-quart active:scale-90 lg:hidden"
          :aria-label="t.common.directCall"
        >
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6.6 2.5h3l1.5 4-2 1.4a13 13 0 0 0 6 6l1.4-2 4 1.5v3a2 2 0 0 1-2.2 2A17.5 17.5 0 0 1 4.6 4.7a2 2 0 0 1 2-2.2" />
          </svg>
        </a>

        <button
          type="button"
          class="grid size-10 place-items-center rounded-full border border-navy-3 text-cream transition-transform duration-300 ease-out-quart active:scale-90 lg:hidden"
          :aria-label="t.common.openMenu"
          @click="menu = true"
        >
          <svg width="18" height="12" viewBox="0 0 18 12" aria-hidden="true" fill="none">
            <path d="M1 1h16M1 6h16M1 11h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
          </svg>
        </button>
      </div>
    </div>
  </header>

  <AppSheet v-model="menu" :title="t.common.pageSections" :subtitle="t.common.pageSectionsSubtitle">
    <ul class="flex flex-col">
      <li v-for="(link, i) in navLinks" :key="link.href">
        <button
          type="button"
          class="flex w-full items-center justify-between gap-4 border-b border-navy-2 py-4 text-start transition-transform duration-200 active:scale-[0.985]"
          @click="goto(link.href)"
        >
          <span class="font-display text-2xl font-bold text-cream">{{ link.label }}</span>
          <span class="num text-[0.75rem] text-cream-3">{{ String(i + 1).padStart(2, '0') }}</span>
        </button>
      </li>
    </ul>

    <div class="mt-6 flex flex-col gap-2.5">
      <a
        :href="waDefault"
        target="_blank"
        rel="noopener"
        class="rounded-full bg-flame px-6 py-3.5 text-center font-semibold text-on-flame transition-transform duration-200 active:scale-[0.97]"
        @click="menu = false"
      >
        {{ t.common.chatWhatsapp }}
      </a>
      <button
        type="button"
        class="rounded-full border border-navy-3 px-6 py-3.5 text-center font-medium text-cream transition-transform duration-200 active:scale-[0.97]"
        @click="goto('#quote')"
      >
        {{ t.common.requestQuote }}
      </button>

      <div class="mt-1 flex items-center justify-between gap-4 rounded-full border border-navy-3 py-2 pe-2 ps-5">
        <span class="text-[0.92rem] text-cream-2">{{ t.common.language }}</span>
        <LangToggle compact />
      </div>

      <div class="flex items-center justify-between gap-4 rounded-full border border-navy-3 py-2 pe-2 ps-5">
        <span class="text-[0.92rem] text-cream-2">{{ t.common.themeAppearance }}</span>
        <ThemeToggle compact />
      </div>
    </div>
  </AppSheet>
</template>
