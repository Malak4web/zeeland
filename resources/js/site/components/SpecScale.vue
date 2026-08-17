<script setup>
import { computed } from 'vue'

/**
 * A lab-style linear readout: fine ruled scale, filled span, taller major
 * ticks every fifth mark. Colour is never the only carrier — the printed
 * value and the caption beside it say the same thing.
 */
const props = defineProps({
  fill: { type: Number, required: true }, // 0–1
  invert: { type: Boolean, default: false }, // true = a LOW reading is the good one
})

const TICKS = 40
const pct = computed(() => Math.round(Math.min(1, Math.max(0, props.fill)) * 100))
const isFilled = (i) => (i - 0.5) / TICKS <= props.fill
</script>

<template>
  <div role="img" :aria-label="`القراءة عند ${pct}٪ من المدى`">
    <!-- ticks run from the inline start, i.e. right in this RTL document -->
    <div class="flex h-6 items-end justify-between gap-px" aria-hidden="true">
      <span
        v-for="i in TICKS"
        :key="i"
        class="w-[2px] shrink-0 rounded-full transition-[height,background-color] duration-500 ease-out-quart"
        :class="isFilled(i) ? (invert ? 'bg-frost' : 'bg-flame') : 'bg-cream/14'"
        :style="{ height: (i - 1) % 5 === 0 ? '1.35rem' : '0.6rem' }"
      />
    </div>
  </div>
</template>
