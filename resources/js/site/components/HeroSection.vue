<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { packFacts, waDefault } from '../data/site.js'
import { t, isRTL } from '../i18n.js'

/* ── The signature moment ────────────────────────────────────────────────
   One control tells the whole product story: the fry leaves the freezer at
   −18°C and comes out of 175°C oil three and a half minutes later. It plays
   itself once on load, then stays draggable. It is a real <input type=range>,
   so keyboard, touch and screen-reader support come for free. */

const progress = ref(1) // 1 = golden (payoff visible by default)
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
  progress.value = 1
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
    <!-- ambient glow -->
    <div
      class="pointer-events-none absolute -top-24 -end-24 h-[36rem] w-[36rem] rounded-full transition-[background,opacity] duration-700 ease-out-quart lg:h-[48rem] lg:w-[48rem]"
      :style="glowStyle"
      aria-hidden="true"
    />

    <div class="container-zl grid gap-y-12 lg:grid-cols-12 lg:gap-x-12 lg:gap-y-0">
      <!-- ── pitch ── -->
      <div class="lg:col-span-6 lg:row-start-1 lg:self-center">
        <div class="flex flex-wrap items-center gap-2">
          <span class="badge badge-subtle">{{ t.hero.badgeVariety }}</span>
          <span class="badge badge-subtle">{{ t.hero.badgeCut }}</span>
          <span class="badge badge-subtle">{{ t.hero.badgeWeight }}</span>
        </div>

        <h1 class="text-display mt-6 font-display font-black tracking-tight text-cream">
          <span class="block">{{ t.hero.h1Line1 }}</span>
          <span class="block text-flame">{{ t.hero.h1Line2 }}</span>
        </h1>

        <p class="text-lead mt-6 text-cream-2">
          {{ t.hero.lead }}
        </p>

        <!-- CTA row -->
        <div class="mt-8 flex flex-wrap items-center gap-3 sm:gap-4 lg:mt-10">
          <a
            :href="waDefault"
            target="_blank"
            rel="noopener"
            class="btn btn-primary btn-lg"
          >
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="currentColor"
              aria-hidden="true"
            >
              <path
                d="M17.472 14.382c-.301-.15-1.78-.879-2.056-.98-.276-.1-.477-.15-.678.15-.201.3-.777.98-.953 1.181-.176.201-.351.226-.652.075-.301-.15-1.272-.469-2.423-1.496-.895-.798-1.5-1.784-1.676-2.085-.176-.301-.019-.464.132-.614.136-.135.301-.351.451-.527.15-.176.201-.301.301-.502.1-.201.05-.376-.025-.527-.075-.15-.678-1.633-.929-2.235-.245-.587-.494-.507-.678-.517-.176-.01-.376-.01-.577-.01-.201 0-.527.075-.803.376s-1.054 1.03-1.054 2.511c0 1.482 1.079 2.912 1.23 3.113.15.201 2.123 3.242 5.143 4.547.718.311 1.279.497 1.716.636.721.23 1.377.197 1.896.12.578-.087 1.78-.728 2.031-1.431.251-.703.251-1.306.176-1.431-.075-.126-.276-.201-.577-.351z"
              />
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.176L2.05 21.45a.75.75 0 0 0 .937.937l4.382-1.388A9.957 9.957 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zM3.5 12a8.5 8.5 0 1 1 14.773 5.75.75.75 0 0 0-.15.461l.966 3.053-3.11-.984a.75.75 0 0 0-.46.012A8.47 8.47 0 0 1 12 20.5 8.5 8.5 0 0 1 3.5 12z"
              />
            </svg>
            {{ t.hero.ctaWhatsApp }}
          </a>

          <a href="#quote" class="btn btn-secondary btn-lg">
            {{ t.hero.ctaQuote }}
          </a>
        </div>

        <!-- micro facts -->
        <dl class="mt-10 grid grid-cols-2 gap-4 border-t border-navy-2 pt-6 sm:grid-cols-4 lg:mt-12">
          <div v-for="fact in packFacts" :key="fact.label">
            <dt class="text-[0.78rem] text-cream-3">{{ fact.label }}</dt>
            <dd class="num mt-1 text-[1.2rem] font-bold text-cream">
              {{ fact.value }}
              <span v-if="fact.unit" class="text-[0.75em] text-cream-3">{{ fact.unit }}</span>
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
              src="/img/fries-heap.jpg"
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
              class="pointer-events-none absolute inset-0 bg-gradient-to-t from-abyss/60 via-transparent to-abyss/30"
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
