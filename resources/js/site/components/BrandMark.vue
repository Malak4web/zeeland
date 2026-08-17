<script setup>
/**
 * The Zeeland windmill, redrawn as vector so it stays crisp at any size and
 * inherits the surrounding text colour (works on navy, cream or flame).
 */
defineProps({
  size: { type: Number, default: 40 },
  showWordmark: { type: Boolean, default: true },
  tagline: { type: Boolean, default: true },
})
</script>

<template>
  <span class="inline-flex items-center gap-3">
    <svg
      :width="size"
      :height="size"
      viewBox="0 0 64 64"
      fill="none"
      aria-hidden="true"
      class="shrink-0"
    >
      <rect x="2.5" y="2.5" width="59" height="59" rx="17" stroke="currentColor" stroke-width="2.5" />

      <!-- sails -->
      <g fill="currentColor">
        <path d="M32 23.5 20.2 14.1a1 1 0 0 1 1.3-1.5l11.2 9.6z" />
        <path d="M32 23.5 41.4 11.7a1 1 0 0 1 1.5 1.3l-9.6 11.2z" />
        <path d="M32 23.5 43.8 32.9a1 1 0 0 1-1.3 1.5l-11.2-9.6z" />
        <path d="M32 23.5 22.6 35.3a1 1 0 0 1-1.5-1.3l9.6-11.2z" />
      </g>
      <circle cx="32" cy="23.5" r="2.6" fill="currentColor" />

      <!-- mill house -->
      <path
        d="M28.4 27.4h7.2l3.1 15.4a1 1 0 0 1-1 1.2H26.3a1 1 0 0 1-1-1.2z"
        fill="currentColor"
      />

      <!-- furrows -->
      <g stroke="currentColor" stroke-width="2.4" stroke-linecap="round" opacity="0.9">
        <path d="M16.5 48.5h31" />
        <path d="M13.5 53.5h37" />
      </g>
    </svg>

    <span v-if="showWordmark" class="flex flex-col leading-none">
      <span
        class="font-display text-[1.35rem] font-black tracking-[0.16em] sm:text-[1.5rem]"
        style="direction: ltr"
        >ZEELAND</span
      >
      <span
        v-if="tagline"
        class="num mt-1 text-[0.5rem] tracking-[0.34em] text-cream-3 uppercase max-sm:hidden"
        >Premium Quality</span
      >
    </span>
  </span>
</template>
