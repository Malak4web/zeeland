<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { packFacts, waDefault } from '../data/site.js'
import { t, isRTL } from '../i18n.js'

/* ── The signature moment ────────────────────────────────────────────────
   One control tells the whole product story: the fry leaves the freezer at
   −18°C and comes out of 175°C oil three and a half minutes later. It plays
   itself once on load, then stays draggable. It is a real <input type=range>,
   so keyboard, touch and screen-reader support come for free. */

const progress = ref(0) // 0 = frozen, 1 = golden
const userTook = ref(false)
let raf = 0

const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v))
const smoothstep = (a, b, v) => {
  const t = clamp((v - a) / (b - a))
  return t * t * (3 - 2 * t)
}
const easeOutQuint = (t) => 1 - Math.pow(1 - t, 5)
const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3)

const reduced = () =>
  typeof matchMedia === 'function' && matchMedia('(prefers-reduced-motion: reduce)').matches

onMounted(() => {
  if (reduced()) {
    progress.value = 1
    return
  }
  const DURATION = 2200
  const DELAY = 420
  let start = 0
  const step = (now) => {
    if (userTook.value) return
    if (!start) start = now
    const t = clamp((now - start - DELAY) / DURATION)
    progress.value = easeOutQuint(t)
    if (t < 1) raf = requestAnimationFrame(step)
  }
  raf = requestAnimationFrame(step)
})
onBeforeUnmount(() => cancelAnimationFrame(raf))

function onInput(e) {
  userTook.value = true
  cancelAnimationFrame(raf)
  progress.value = Number(e.target.value) / 1000
}

/* Readouts. The fry hits 175°C oil almost immediately; the remaining time is
   colour development, which is exactly what the images show. */
const temperature = computed(() => {
  const tVal = easeOutCubic(clamp(progress.value / 0.14))
  return Math.round(-18 + 193 * tVal)
})

const elapsed = computed(() => {
  const total = Math.round(progress.value * 210) // 3:30
  const m = Math.floor(total / 60)
  const s = total % 60
  return `${m}:${String(s).padStart(2, '0')}`
})

const stage = computed(() => {
  const p = progress.value
  const stages = t.value.hero.stages
  if (p < 0.05) return stages.frozen
  if (p < 0.45) return stages.oil
  if (p < 0.88) return stages.browning
  return stages.ready
})

const frozenOpacity = computed(() => 1 - smoothstep(0.02, 0.4, progress.value))
const goldenOpacity = computed(() => smoothstep(0.22, 0.95, progress.value))
const frostOpacity = computed(() => (1 - smoothstep(0, 0.25, progress.value)) * 0.5)

const heatMix = computed(() => smoothstep(0.05, 0.7, progress.value))
const glowColor = computed(
  () => `color-mix(in oklab, var(--flame) ${(heatMix.value * 100).toFixed(1)}%, var(--frost))`,
)

const glowStyle = computed(() => ({
  background: `radial-gradient(closest-side, ${glowColor.value}, transparent 72%)`,
  opacity: `calc(${(0.2 + heatMix.value * 0.22).toFixed(3)} * var(--bloom))`,
}))

const goldenFilter = computed(() => {
  const p = clamp((progress.value - 0.2) / 0.8)
  return `saturate(${(0.55 + p * 0.6).toFixed(2)}) brightness(${(0.82 + p * 0.26).toFixed(2)}) contrast(${(0.95 + p * 0.12).toFixed(2)})`
})

const valueText = computed(() =>
  t.value.hero.valueTextTemplate(stage.value.label, temperature.value, elapsed.value),
)
</script>

<template>
  <section id="top" class="grain relative overflow-hidden pt-20 pb-14 lg:pt-32 lg:pb-24">
    <!-- ambient layers -->
    <div
      class="pointer-events-none absolute top-[-7rem] left-1/2 -z-10 h-[30rem] w-[42rem] max-w-[135vw] -translate-x-1/2 transition-[background,opacity] duration-700 ease-out-quart lg:top-[-5rem] lg:left-[32%] lg:h-[44rem] lg:w-[54rem]"
      :style="glowStyle"
      aria-hidden="true"
    />
    <div
      class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-64 bg-[url('/img/fry-basket-dark.jpg')] bg-cover bg-center opacity-[0.05] blur-[3px]"
      aria-hidden="true"
    />
    <div
      class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-64 bg-gradient-to-t from-abyss via-abyss/75 to-transparent"
      aria-hidden="true"
    />

    <div class="container-zl grid gap-y-10 lg:grid-cols-12 lg:gap-x-14 lg:gap-y-12">
      <!-- ── the claim ── -->
      <div class="lg:col-span-6 lg:col-start-1 lg:row-start-1 lg:self-end">
        <p class="mb-6 flex flex-wrap items-center gap-x-3 gap-y-2 text-[0.82rem] text-cream-3">
          <span
            v-for="badge in t.hero.badges"
            :key="badge"
            class="num rounded-full border border-navy-3 px-3 py-1"
          >
            {{ badge }}
          </span>
        </p>

        <h1 class="text-display font-black">
          <span class="block text-cream">{{ t.hero.titleLine1 }}</span>
          <span class="block text-flame-ink">{{ t.hero.titleLine2 }}</span>
        </h1>

        <p class="prose-zl mt-6 lg:mt-7">
          {{ t.hero.lead }}
        </p>
      </div>

      <!-- ── actions ── -->
      <div class="order-last lg:order-none lg:col-span-6 lg:col-start-1 lg:row-start-2 lg:self-start">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
          <a
            :href="waDefault"
            target="_blank"
            rel="noopener"
            class="group inline-flex items-center justify-center gap-2.5 rounded-full bg-flame px-7 py-4 font-medium text-on-flame shadow-[0_16px_40px_-16px] shadow-flame/70 transition-[background-color,transform] duration-300 ease-out-quart hover:bg-flame-hi active:scale-[0.98]"
          >
            <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path
                d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.27.86 5.82 2.42a8.19 8.19 0 0 1 2.42 5.82c0 4.54-3.7 8.24-8.25 8.24a8.25 8.25 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.26-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.21.89 2.39 1.01 2.55.12.17 1.72 2.62 4.16 3.68.58.25 1.03.4 1.39.51.58.19 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16s.2-1.06.14-1.16-.22-.16-.47-.28c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.25-.62.79-.76.95-.14.17-.28.19-.52.07-.25-.13-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.24-.01-.37.11-.49.11-.11.24-.29.37-.43s.17-.25.25-.41c.08-.17.04-.31-.02-.43s-.55-1.34-.76-1.83c-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.47-.02"
              />
            </svg>
            {{ t.hero.ctaWhatsapp }}
          </a>

          <a
            href="#quote"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-navy-3 px-7 py-4 font-medium text-cream transition-colors duration-300 hover:border-steel hover:bg-navy"
          >
            {{ t.hero.ctaQuote }}
          </a>
        </div>

        <dl class="mt-10 grid max-w-lg grid-cols-2 gap-x-6 gap-y-6 sm:grid-cols-4">
          <div v-for="fact in packFacts" :key="fact.label">
            <dt class="text-[0.75rem] text-cream-3">{{ fact.label }}</dt>
            <dd class="mt-1.5 text-[1.35rem] font-semibold text-cream">
              <span class="num"
                >{{ fact.value
                }}<span class="text-[0.72em] text-cream-3">{{ fact.unit }}</span></span
              >
            </dd>
          </div>
        </dl>
      </div>

      <!-- ── temperature stage ── -->
      <div class="lg:col-span-6 lg:col-start-7 lg:row-span-2 lg:row-start-1 lg:self-center">
        <figure class="relative">
          <div
            data-theme="dark"
            class="relative aspect-17/10 overflow-hidden rounded-[1.75rem] border border-navy-2 bg-gradient-to-b from-navy-2 to-abyss lg:aspect-4/3"
          >
            <!-- golden: the payoff -->
            <img
              src="/img/fries-heap.jpg?v=2"
              :alt="t.hero.altGolden"
              width="1800"
              height="1200"
              fetchpriority="high"
              class="absolute inset-0 size-full object-cover transition-[opacity,filter] duration-300 ease-out-quart"
              :style="{ opacity: goldenOpacity, filter: goldenFilter }"
            />

            <!-- frozen: the product as it is delivered -->
            <img
              src="/img/fries-dark.jpg"
              :alt="t.hero.altFrozen"
              width="2000"
              height="1333"
              fetchpriority="high"
              class="absolute inset-0 size-full object-contain mix-blend-screen transition-opacity duration-300 ease-out-quart"
              :style="{ opacity: frozenOpacity }"
            />

            <img
              src="/img/frost-dark.jpg"
              alt=""
              aria-hidden="true"
              class="pointer-events-none absolute inset-0 size-full object-cover mix-blend-screen transition-opacity duration-500 ease-out-quart"
              :style="{ opacity: frostOpacity }"
            />

            <div
              class="pointer-events-none absolute inset-0 bg-gradient-to-t from-abyss/92 via-abyss/12 to-abyss/45"
              aria-hidden="true"
            />

            <!-- readout -->
            <div class="absolute inset-x-0 top-0 flex items-start justify-between p-5 sm:p-7">
              <div>
                <p
                  class="num text-[2.6rem] leading-none font-bold tabular-nums sm:text-[3.4rem]"
                  :style="{ color: glowColor }"
                >
                  {{ temperature < 0 ? '−' + Math.abs(temperature) : temperature }}<span
                    class="text-[0.45em] align-super"
                    >°C</span
                  >
                </p>
                <p class="mt-2 font-display text-xl font-bold text-cream sm:text-2xl">
                  {{ stage.label }}
                </p>
              </div>

              <div class="text-end">
                <p class="num text-[0.7rem] tracking-widest text-cream-3 uppercase">{{ t.hero.timerLabel }}</p>
                <p class="num mt-1 text-[1.4rem] leading-none font-semibold text-cream tabular-nums sm:text-[1.8rem]">
                  {{ elapsed }}
                </p>
              </div>
            </div>

            <figcaption
              class="absolute inset-x-0 bottom-0 p-5 text-[0.9rem] text-cream-2 sm:p-7"
            >
              {{ stage.note }}
            </figcaption>
          </div>

          <!-- control -->
          <div class="mt-4 px-1 lg:mt-5">
            <label for="fry-scrubber" class="mb-0.5 block text-[0.85rem] text-cream-2">
              {{ t.hero.scrubberLabel }}
            </label>
            <input
              id="fry-scrubber"
              class="temp-range"
              type="range"
              :dir="isRTL ? 'rtl' : 'ltr'"
              min="0"
              max="1000"
              step="1"
              :value="Math.round(progress * 1000)"
              :aria-valuetext="valueText"
              @input="onInput"
            />
            <p class="mt-1 flex justify-between text-[0.7rem] tracking-widest" aria-hidden="true">
              <span class="num text-frost-ink">{{ t.hero.tempStart }}</span>
              <span class="num text-flame-ink">{{ t.hero.tempEnd }}</span>
            </p>
          </div>
        </figure>
      </div>
    </div>
  </section>
</template>
