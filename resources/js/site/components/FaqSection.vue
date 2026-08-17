<script setup>
import { faq, contact } from '../data/site.js'
import { t } from '../i18n.js'
</script>

<template>
  <section aria-labelledby="faq-heading" class="py-[clamp(4rem,8vh,6rem)]">
    <div class="container-zl grid gap-x-14 gap-y-10 lg:grid-cols-12">
      <div class="lg:col-span-4 lg:sticky lg:top-28 lg:self-start">
        <h2 id="faq-heading" v-reveal class="text-h2 text-cream">{{ t.faq.heading }}</h2>
        <p v-reveal="70" class="mt-5 max-w-[38ch] text-[1rem] leading-[1.9] text-cream-2">
          {{ t.faq.callPrompt }}
          <a
            :href="contact.phoneHref"
            class="num whitespace-nowrap text-flame-ink underline decoration-flame-ink/40 underline-offset-4 transition-colors hover:text-flame-ink-hi"
            >{{ contact.phoneDisplay }}</a
          >
          — {{ contact.hours }}.
        </p>
      </div>

      <!-- native <details>: keyboard, screen reader and no-JS support for free -->
      <div class="lg:col-span-7 lg:col-start-6">
        <details
          v-for="(item, i) in faq"
          :key="item.q"
          v-reveal="i * 50"
          class="group border-t border-navy-2 first:border-t-0"
        >
          <summary
            class="flex cursor-pointer list-none items-center justify-between gap-6 py-5 font-display text-[1.2rem] font-bold text-cream transition-colors hover:text-flame-ink-hi sm:text-[1.35rem] [&::-webkit-details-marker]:hidden"
          >
            {{ item.q }}
            <span
              class="grid size-8 shrink-0 place-items-center rounded-full border border-navy-3 transition-[transform,border-color] duration-400 ease-out-expo group-open:rotate-45 group-open:border-flame group-open:text-flame-ink"
              aria-hidden="true"
            >
              <svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                <path d="M7 1v12M1 7h12" />
              </svg>
            </span>
          </summary>
          <p class="max-w-[58ch] pb-6 text-[1rem] leading-[1.9] text-cream-2">{{ item.a }}</p>
        </details>
      </div>
    </div>
  </section>
</template>
