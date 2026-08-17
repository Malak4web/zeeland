<script setup>
import { currentLocale, toggleLocale, isRTL } from '../i18n.js'

defineProps({
  compact: { type: Boolean, default: false },
})
</script>

<template>
  <button
    id="lang-toggle-btn"
    type="button"
    data-lang-toggle
    class="lang-toggle group inline-flex items-center justify-center transition-[transform,border-color,background-color,color] duration-300 ease-out-quart active:scale-95 cursor-pointer"
    :class="[
      compact
        ? 'h-9 gap-2 rounded-full border border-navy-3 bg-navy/60 px-3.5 text-[0.82rem] font-medium text-cream hover:border-steel hover:bg-navy'
        : 'h-10 px-3.5 gap-2 rounded-full border border-navy-3 bg-navy/50 text-cream hover:border-steel hover:bg-navy shadow-xs',
    ]"
    :aria-label="currentLocale === 'ar' ? 'Switch to English' : 'التحويل إلى العربية'"
    @click.prevent="toggleLocale"
  >
    <!-- Globe Icon -->
    <svg
      width="15"
      height="15"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="1.8"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
      class="shrink-0 text-flame-ink transition-transform duration-500 ease-out-expo group-hover:rotate-45"
    >
      <circle cx="12" cy="12" r="10" />
      <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
      <path d="M2 12h20" />
    </svg>

    <!-- Language Label -->
    <span
      class="font-display font-bold leading-none select-none tracking-wide text-[0.82rem] text-cream"
    >
      {{ currentLocale === 'ar' ? 'English' : 'العربية' }}
    </span>
  </button>
</template>

<style scoped>
.lang-toggle {
  touch-action: manipulation;
  user-select: none;
}
</style>
