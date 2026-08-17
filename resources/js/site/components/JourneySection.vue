<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { journey } from '../data/site.js'
import { t } from '../i18n.js'

/* The mono face is Latin-only by design (it is for instrument readouts).
   Arabic strings must never land in it, or they fall back and letter-space. */
const isLatin = (s) => !/[؀-ۿ]/.test(s)

/* On phones the six stages become a swipe deck — a stepper you thumb through,
   the way an app would present it. On lg it stays a vertical rail. */
const rail = ref(null)
const step = ref(0)
let ticking = false

function measure() {
  ticking = false
  const el = rail.value
  if (!el || el.scrollWidth <= el.clientWidth) return
  const card = el.querySelector('li')
  const width = card?.getBoundingClientRect().width
  if (!width) return
  const stride = width + 16 // .deck gap
  step.value = Math.min(
    journey.value.length - 1,
    Math.max(0, Math.round(Math.abs(el.scrollLeft) / stride)),
  )
}
function onRailScroll() {
  if (ticking) return
  ticking = true
  requestAnimationFrame(measure)
}

function goTo(i) {
  const card = rail.value?.querySelectorAll('li')[i]
  card?.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' })
}

onMounted(() => {
  step.value = 0
  rail.value?.addEventListener('scroll', onRailScroll, { passive: true })
})
onBeforeUnmount(() => rail.value?.removeEventListener('scroll', onRailScroll))
</script>

<template>
  <section id="journey" class="relative overflow-clip py-[clamp(4rem,10vh,7.5rem)]">
    <!-- the cold world -->
    <img
      src="/img/frost-dark.jpg"
      alt=""
      aria-hidden="true"
      loading="lazy"
      decoding="async"
      class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[38rem] w-full object-cover opacity-[0.09]"
    />
    <div
      class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[38rem] bg-gradient-to-b from-abyss/40 via-abyss/70 to-abyss"
      aria-hidden="true"
    />

    <div class="container-zl grid gap-x-14 gap-y-10 lg:grid-cols-12">
      <!-- intro -->
      <div class="min-w-0 lg:col-span-4 lg:sticky lg:top-28 lg:self-start">
        <h2 v-reveal class="text-h2 text-cream">
          {{ t.journey.titleLine1 }}<br />{{ t.journey.titleLine2 }}
        </h2>
        <p v-reveal="80" class="mt-5 max-w-[46ch] text-[1rem] leading-[1.85] text-cream-2 lg:mt-6 lg:text-[1.05rem] lg:leading-[1.9]">
          {{ t.journey.lead }}
        </p>

        <p
          v-reveal="140"
          class="mt-5 inline-flex items-center gap-2.5 rounded-full border border-navy-3 px-4 py-2 text-[0.85rem] text-frost-ink lg:mt-8"
        >
          <svg
            width="15"
            height="15"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
          >
            <path d="M12 2v20M3.34 7 20.66 17M20.66 7 3.34 17" />
            <path d="m9.4 4.3 2.6 2.6 2.6-2.6M9.4 19.7l2.6-2.6 2.6 2.6" />
            <path d="m3.9 11.1-1-3.5 3.5-1M20.1 12.9l1 3.5-3.5 1M6.4 17.4l-3.5 1-1-3.5M17.6 6.6l3.5-1 1 3.5" />
          </svg>
          {{ t.journey.badge }}
        </p>
      </div>

      <!-- the stages -->
      <div class="min-w-0 lg:col-span-7 lg:col-start-6">
        <ol
          ref="rail"
          class="deck relative"
        >
          <span
            class="pointer-events-none absolute top-3 bottom-3 start-[1.15rem] hidden w-px bg-gradient-to-b from-frost/70 via-steel/40 to-flame/70 lg:block sm:start-[1.4rem]"
            aria-hidden="true"
          />

          <li
            v-for="(stage, i) in journey"
            :key="stage.n"
            v-reveal="i * 60"
            class="relative w-[78vw] max-w-[20rem] shrink-0 rounded-3xl border border-navy-2 bg-navy/55 p-5 backdrop-blur-sm lg:w-auto lg:max-w-none lg:shrink lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:ps-16 lg:pb-11 lg:backdrop-blur-none lg:last:pb-0"
          >
            <span
              class="mb-4 grid size-[2.4rem] place-items-center rounded-full border border-navy-3 bg-abyss lg:absolute lg:start-0 lg:top-0 lg:mb-0 lg:size-[2.8rem]"
              aria-hidden="true"
            >
              <span class="num text-[0.75rem] font-semibold text-cream-2 lg:text-[0.8rem]">{{
                stage.n
              }}</span>
            </span>

            <p
              class="mb-2 text-[0.72rem] text-cream-3"
              :class="isLatin(stage.meta) ? 'num tracking-[0.22em] uppercase' : 'tracking-[0.06em]'"
            >
              {{ stage.meta }}
            </p>
            <h3 class="text-h3 text-cream">{{ stage.title }}</h3>
            <p class="mt-3 max-w-[58ch] text-[0.98rem] leading-[1.85] text-cream-2 lg:text-[1rem] lg:leading-[1.9]">
              {{ stage.body }}
            </p>
          </li>
        </ol>

        <!-- deck position, phones only -->
        <div class="mt-3 flex items-center justify-center lg:hidden">
          <button
            v-for="(stage, i) in journey"
            :key="stage.n"
            type="button"
            class="grid h-6 min-w-6 place-items-center px-1"
            :aria-label="`${stage.n}: ${stage.title}`"
            :aria-current="step === i ? 'true' : undefined"
            @click="goTo(i)"
          >
            <span
              class="block h-1.5 rounded-full transition-[width,background-color] duration-400 ease-out-expo"
              :class="step === i ? 'w-7 bg-flame' : 'w-1.5 bg-steel/60'"
            />
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
