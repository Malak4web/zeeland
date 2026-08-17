<script setup>
import { productSpecs, nutrition } from '../data/site.js'
import { t } from '../i18n.js'
</script>

<template>
  <section id="specs" class="bg-paper py-[clamp(4.5rem,10vh,7.5rem)] text-abyss">
    <div class="container-zl">
      <div class="grid gap-x-14 gap-y-12 lg:grid-cols-12">
        <!-- pack -->
        <div v-reveal class="lg:col-span-5 lg:sticky lg:top-28 lg:self-start">
          <div
            class="overflow-hidden rounded-[1.25rem] border border-abyss/12 bg-white shadow-[0_24px_60px_-32px_var(--shadow-2)]"
          >
            <img
              src="/img/pack.jpg"
              :alt="t.specs.packAlt"
              width="1280"
              height="853"
              loading="lazy"
              decoding="async"
              class="aspect-4/5 w-full object-cover"
              style="object-position: 3% 50%"
            />
          </div>

          <p class="mt-5 text-[0.9rem] leading-relaxed text-navy-3">
            {{ t.specs.packNote }}
          </p>
        </div>

        <!-- sheet -->
        <div class="lg:col-span-7">
          <h2 v-reveal class="text-h2 text-abyss">{{ t.specs.title }}</h2>

          <dl v-reveal="70" class="mt-9">
            <div
              v-for="spec in productSpecs"
              :key="spec.label"
              class="grid grid-cols-3 gap-4 border-t border-abyss/12 py-3.5 first:border-t-0 first:pt-0 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]"
            >
              <dt class="text-[0.92rem] text-navy-3">{{ spec.label }}</dt>
              <dd class="col-span-2 text-[0.98rem] font-medium sm:col-span-1">{{ spec.value }}</dd>
            </div>
          </dl>

          <!-- nutrition -->
          <div v-reveal="140" class="mt-11 rounded-[1.25rem] border-2 border-abyss/85 p-5 sm:p-7">
            <h3 class="font-display text-2xl font-black">{{ t.specs.nutritionTitle }}</h3>
            <p class="mt-1.5 text-[0.78rem] text-navy-3">
              <span class="num tracking-wide"
                >{{ nutrition.servingSize }}</span
              >
            </p>

            <table class="mt-5 w-full text-[0.95rem]">
              <caption class="sr-only">
                {{ t.specs.tableCaption }}
              </caption>
              <thead>
                <tr class="border-b-2 border-abyss/85 text-[0.75rem] text-navy-3">
                  <th scope="col" class="pb-1.5 text-start font-medium">{{ t.specs.thNutrient }}</th>
                  <th scope="col" class="pb-1.5 text-end font-medium">{{ t.specs.thAmount }}</th>
                  <th scope="col" class="pb-1.5 text-end font-medium">{{ t.specs.thDv }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in nutrition.rows"
                  :key="row.label"
                  class="border-b border-abyss/12 last:border-b-0"
                >
                  <th
                    scope="row"
                    class="py-2 text-start font-normal"
                    :class="[row.indent ? 'ps-5 text-navy-3' : '', row.strong ? 'font-bold' : '']"
                  >
                    {{ row.label }}
                  </th>
                  <td
                    class="num py-2 text-end tabular-nums"
                    :class="row.strong ? 'text-[1.1rem] font-bold' : ''"
                  >
                    {{ row.value }}
                  </td>
                  <td class="num py-2 text-end text-navy-3 tabular-nums">{{ row.dv ?? '—' }}</td>
                </tr>
              </tbody>
            </table>

            <p class="mt-4 text-[0.78rem] leading-relaxed text-navy-3">
              {{ t.specs.footnote }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
