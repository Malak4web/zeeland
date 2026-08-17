<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import AppSheet from './AppSheet.vue'
import { contact, waDefault, waMessage } from '../data/site.js'
import { t } from '../i18n.js'

const tabs = computed(() => t.value.mobileDock.tabs)

const active = ref('top')
const sheet = ref(false)
let ticking = false

function measure() {
  ticking = false
  const line = window.innerHeight * 0.34
  const currentTabs = tabs.value
  let current = currentTabs[0]?.id || 'top'
  for (const tab of currentTabs) {
    const el = document.getElementById(tab.id)
    if (el && el.getBoundingClientRect().top <= line) current = tab.id
  }
  const doc = document.documentElement
  if (window.scrollY + window.innerHeight >= doc.scrollHeight - 8) current = currentTabs.at(-1)?.id || current
  active.value = current
}

function onScroll() {
  if (ticking) return
  ticking = true
  requestAnimationFrame(measure)
}

onMounted(() => {
  measure()
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('resize', onScroll, { passive: true })
})
onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('resize', onScroll)
})

const sampleMessage = computed(() =>
  waMessage(t.value.mobileDock.sampleMessage),
)

function goQuote() {
  sheet.value = false
  setTimeout(() => document.getElementById('quote')?.scrollIntoView({ block: 'start' }), 260)
}
</script>

<template>
  <nav
    class="fixed inset-x-0 bottom-0 z-40 lg:hidden"
    :style="{ paddingBottom: 'calc(0.625rem + env(safe-area-inset-bottom))' }"
    :aria-label="t.common.pageSections"
  >
    <div
      class="mx-3 flex items-center gap-1 rounded-[1.65rem] border border-navy-2 bg-navy/78 p-1.5 shadow-[0_18px_44px_-12px_var(--shadow-2)] backdrop-blur-2xl"
    >
      <a
        v-for="tab in tabs"
        :key="tab.id"
        :href="tab.href"
        class="relative flex flex-1 flex-col items-center gap-1 rounded-[1.25rem] px-1 py-2 transition-[color,background-color,transform] duration-300 ease-out-quart active:scale-[0.93]"
        :class="active === tab.id ? 'bg-navy-2 text-flame-ink' : 'text-cream-3'"
        :aria-current="active === tab.id ? 'true' : undefined"
      >
        <svg
          width="21"
          height="21"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.7"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <template v-if="tab.id === 'top'">
            <path d="M3 10.5 12 3l9 7.5" />
            <path d="M5.5 9.4V20a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V9.4" />
          </template>
          <template v-else-if="tab.id === 'santana'">
            <path d="M9.5 3h5M10.5 3v6.2L5.6 17.9A2 2 0 0 0 7.3 21h9.4a2 2 0 0 0 1.7-3.1L13.5 9.2V3" />
            <path d="M7.9 14.5h8.2" />
          </template>
          <template v-else>
            <path d="M6 3h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" />
            <path d="M14.5 3v4.5H19M8.5 12.5h7M8.5 16.5h4.5" />
          </template>
        </svg>
        <span class="text-[0.66rem] leading-none font-medium">{{ tab.label }}</span>
      </a>

      <button
        type="button"
        class="flex flex-1 items-center justify-center gap-2 rounded-[1.25rem] bg-flame px-2 py-3.5 text-[0.95rem] font-semibold whitespace-nowrap text-on-flame transition-transform duration-300 ease-out-quart active:scale-[0.94]"
        :aria-label="t.mobileDock.orderBtn"
        @click="sheet = true"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path
            d="M12 3c-5 0-9 3.6-9 8 0 2.2 1 4.2 2.7 5.6-.1 1.2-.6 2.6-1.7 3.9 0 0 2.9-.3 5.1-2 .9.2 1.9.4 2.9.4 5 0 9-3.6 9-8s-4-8-9-8"
          />
        </svg>
        {{ t.mobileDock.orderBtn }}
      </button>
    </div>
  </nav>

  <AppSheet
    v-model="sheet"
    :title="t.mobileDock.sheetTitle"
    :subtitle="t.mobileDock.sheetSubtitle"
  >
    <ul class="flex flex-col gap-2.5">
      <li>
        <a
          :href="waDefault"
          target="_blank"
          rel="noopener"
          class="flex items-center gap-4 rounded-2xl border border-navy-2 bg-flame/10 p-4 transition-transform duration-200 active:scale-[0.98]"
          @click="sheet = false"
        >
          <span class="grid size-11 shrink-0 place-items-center rounded-full bg-flame text-on-flame">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path
                d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.27.86 5.82 2.42a8.19 8.19 0 0 1 2.42 5.82c0 4.54-3.7 8.24-8.25 8.24a8.25 8.25 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.26-8.24m-3.51 3.66c-.16 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.21.89 2.39 1.01 2.55.12.17 1.72 2.62 4.16 3.68.58.25 1.03.4 1.39.51.58.19 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16s.2-1.06.14-1.16-.22-.16-.47-.28c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.25-.62.79-.76.95-.14.17-.28.19-.52.07-.25-.13-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.24-.01-.37.11-.49.11-.11.24-.29.37-.43s.17-.25.25-.41c.08-.17.04-.31-.02-.43s-.55-1.34-.76-1.83c-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.47-.02"
              />
            </svg>
          </span>
          <span>
            <span class="block font-display text-lg font-bold text-cream">{{ t.mobileDock.optionWhatsappTitle }}</span>
            <span class="block text-[0.85rem] text-cream-2">{{ t.mobileDock.optionWhatsappSub }}</span>
          </span>
        </a>
      </li>

      <li>
        <a
          :href="contact.phoneHref"
          class="flex items-center gap-4 rounded-2xl border border-navy-2 p-4 transition-transform duration-200 active:scale-[0.98]"
          @click="sheet = false"
        >
          <span class="grid size-11 shrink-0 place-items-center rounded-full border border-navy-3 text-flame-ink">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6.6 2.5h3l1.5 4-2 1.4a13 13 0 0 0 6 6l1.4-2 4 1.5v3a2 2 0 0 1-2.2 2A17.5 17.5 0 0 1 4.6 4.7a2 2 0 0 1 2-2.2" />
            </svg>
          </span>
          <span>
            <span class="block font-display text-lg font-bold text-cream">{{ t.mobileDock.optionCallTitle }}</span>
            <span class="num block text-[0.85rem] text-cream-2">{{ contact.phoneDisplay }}</span>
          </span>
        </a>
      </li>

      <li>
        <a
          :href="sampleMessage"
          target="_blank"
          rel="noopener"
          class="flex items-center gap-4 rounded-2xl border border-navy-2 p-4 transition-transform duration-200 active:scale-[0.98]"
          @click="sheet = false"
        >
          <span class="grid size-11 shrink-0 place-items-center rounded-full border border-navy-3 text-frost-ink">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 8.5h14l-1.2 11a2 2 0 0 1-2 1.8H8.2a2 2 0 0 1-2-1.8z" />
              <path d="M8.8 8.5V6.2a3.2 3.2 0 0 1 6.4 0v2.3" />
            </svg>
          </span>
          <span>
            <span class="block font-display text-lg font-bold text-cream">{{ t.mobileDock.optionSampleTitle }}</span>
            <span class="block text-[0.85rem] text-cream-2">{{ t.mobileDock.optionSampleSub }}</span>
          </span>
        </a>
      </li>

      <li>
        <button
          type="button"
          class="flex w-full items-center gap-4 rounded-2xl border border-navy-2 p-4 text-start transition-transform duration-200 active:scale-[0.98]"
          @click="goQuote"
        >
          <span class="grid size-11 shrink-0 place-items-center rounded-full border border-navy-3 text-cream-2">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 3h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" />
              <path d="M14.5 3v4.5H19M8.5 13h7M8.5 17h4" />
            </svg>
          </span>
          <span>
            <span class="block font-display text-lg font-bold text-cream">{{ t.mobileDock.optionQuoteTitle }}</span>
            <span class="block text-[0.85rem] text-cream-2">{{ t.mobileDock.optionQuoteSub }}</span>
          </span>
        </button>
      </li>
    </ul>
  </AppSheet>
</template>
