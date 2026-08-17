<script setup>
import { t } from '../i18n.js'

defineProps({ compact: Boolean })
</script>

<template>
  <button
    type="button"
    data-theme-toggle
    class="theme-toggle border border-navy-3 text-cream transition-[transform,border-color,background-color] duration-300 ease-out-quart hover:border-steel hover:bg-navy active:scale-90"
    :class="compact ? 'size-[2.375rem]' : 'size-10'"
    :aria-label="t.common.themeToggleAria"
  >
    <svg
      class="i-sun"
      width="18"
      height="18"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="1.7"
      stroke-linecap="round"
      aria-hidden="true"
    >
      <circle cx="12" cy="12" r="4.1" />
      <path d="M12 2.4v2M12 19.6v2M21.6 12h-2M4.4 12h-2M18.8 5.2l-1.4 1.4M6.6 17.4l-1.4 1.4M18.8 18.8l-1.4-1.4M6.6 6.6 5.2 5.2" />
    </svg>

    <svg
      class="i-moon"
      width="18"
      height="18"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="1.7"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
    >
      <path d="M20.5 14.3A8.6 8.6 0 0 1 9.7 3.5a8.6 8.6 0 1 0 10.8 10.8" />
    </svg>
  </button>
</template>
