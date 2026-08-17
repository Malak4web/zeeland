<script setup>
import { computed, nextTick, ref } from 'vue'
import { businessTypes, contact, governorates, monthlyVolumes, waMessage } from '../data/site.js'
import { t } from '../i18n.js'

/* The request is saved server-side first, then WhatsApp opens. That order
   matters: a chef who taps "send" and then closes WhatsApp without pressing
   send is still a lead we have, instead of one we never knew about. */

const form = ref({
  name: '',
  business: '',
  type: '',
  volume: '',
  governorate: '',
  phone: '',
  note: '',
  website: '', // honeypot — hidden from people, irresistible to bots
})

const errors = ref({})
const submitted = ref(false)
const sending = ref(false)
const sentUrl = ref('')
const serverError = ref('')

const EG_MOBILE = /^01[0125]\d{8}$/
const normalisePhone = (raw) =>
  raw
    .replace(/[٠-٩]/g, (d) => String(d.charCodeAt(0) - 0x0660)) // Arabic-Indic digits
    .replace(/\D/g, '')
    .replace(/^0020/, '0')
    .replace(/^20(?=1)/, '0')

function validate() {
  const f = form.value
  const e = {}
  const v = t.value.quote.validation

  if (f.name.trim().length < 2) e.name = v.name
  if (f.business.trim().length < 2) e.business = v.business
  if (!f.type) e.type = v.type
  if (!f.volume) e.volume = v.volume
  if (!f.governorate) e.governorate = v.gov

  const phone = normalisePhone(f.phone)
  if (!phone) e.phone = v.phoneEmpty
  else if (!EG_MOBILE.test(phone)) e.phone = v.phoneInvalid

  errors.value = e
  return Object.keys(e).length === 0
}

const errorCount = computed(() => Object.keys(errors.value).length)

/** The message we hand to WhatsApp if the server has none for us. */
function composeMessage() {
  const f = { ...form.value, phone: normalisePhone(form.value.phone) }
  return t.value.quote.waQuoteTemplate(f)
}

async function onSubmit() {
  if (sending.value) return
  serverError.value = ''

  if (!validate()) {
    await nextTick()
    document.querySelector('[aria-invalid="true"]')?.focus()
    return
  }

  sending.value = true
  const f = form.value

  const payload = {
    name: f.name.trim(),
    business_name: f.business.trim(),
    business_type: f.type,
    monthly_volume: f.volume,
    governorate: f.governorate,
    phone: normalisePhone(f.phone),
    message: f.note.trim(),
    website: f.website,
    source: 'landing_form',
    page_url: window.location.href,
    referrer: document.referrer,
  }

  const params = new URLSearchParams(window.location.search)
  for (const key of ['utm_source', 'utm_medium', 'utm_campaign']) {
    if (params.get(key)) payload[key] = params.get(key)
  }

  try {
    const res = await fetch('/api/leads', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(payload),
    })

    if (res.status === 422) {
      const body = await res.json()
      const map = { business_name: 'business', business_type: 'type', monthly_volume: 'volume' }
      errors.value = Object.fromEntries(
        Object.entries(body.errors ?? {}).map(([k, vVal]) => [map[k] ?? k, vVal[0]]),
      )
      await nextTick()
      document.querySelector('[aria-invalid="true"]')?.focus()
      return
    }

    if (!res.ok) throw new Error(String(res.status))

    const body = await res.json()
    sentUrl.value = body.whatsapp || waMessage(composeMessage())
  } catch {
    serverError.value = t.value.quote.serverErrorMsg
    sentUrl.value = waMessage(composeMessage())
  } finally {
    sending.value = false
  }

  submitted.value = true
  window.open(sentUrl.value, '_blank', 'noopener')
}

function reset() {
  form.value = {
    name: '',
    business: '',
    type: '',
    volume: '',
    governorate: '',
    phone: '',
    note: '',
    website: '',
  }
  errors.value = {}
  submitted.value = false
  sentUrl.value = ''
  serverError.value = ''
}

const fieldClass =
  'w-full rounded-xl border bg-[var(--field-bg)] px-4 py-3.5 text-[1rem] text-cream placeholder:text-cream-3 transition-colors duration-200 focus:border-flame-ink focus:bg-[var(--field-bg-focus)] focus:outline-none'
</script>

<template>
  <section id="quote" class="grain relative overflow-clip py-[clamp(4.5rem,10vh,7.5rem)]">
    <div
      class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[30rem] bg-[radial-gradient(closest-side,var(--flame),transparent_72%)] opacity-[calc(0.14*var(--bloom))]"
      aria-hidden="true"
    />

    <div class="container-zl grid gap-x-14 gap-y-12 lg:grid-cols-12">
      <!-- pitch + direct lines -->
      <div class="lg:col-span-5 lg:sticky lg:top-28 lg:self-start">
        <h2 v-reveal class="text-h2 text-cream">{{ t.quote.heading }}</h2>
        <p v-reveal="70" class="prose-zl mt-6">
          {{ t.quote.lead }}
        </p>

        <div v-reveal="130" class="mt-10 flex flex-col gap-3">
          <a
            :href="`https://wa.me/${contact.whatsapp}`"
            target="_blank"
            rel="noopener"
            class="flex items-center justify-between gap-4 rounded-2xl border border-navy-2 bg-navy/60 px-5 py-4 transition-colors duration-300 hover:border-flame/60 hover:bg-navy-2"
          >
            <span>
              <span class="block text-[0.82rem] text-cream-3">{{ contact.waSalesTitle }}</span>
              <span class="mt-0.5 block text-[1.05rem] font-semibold text-cream">
                <span class="num">{{ contact.phoneDisplay }}</span>
              </span>
            </span>
            <span class="text-flame-ink" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.27.86 5.82 2.42a8.19 8.19 0 0 1 2.42 5.82c0 4.54-3.7 8.24-8.25 8.24a8.25 8.25 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.26-8.24m-3.51 3.66c-.16 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.21.89 2.39 1.01 2.55.12.17 1.72 2.62 4.16 3.68.58.25 1.03.4 1.39.51.58.19 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16s.2-1.06.14-1.16-.22-.16-.47-.28c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.25-.62.79-.76.95-.14.17-.28.19-.52.07-.25-.13-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.24-.01-.37.11-.49.11-.11.24-.29.37-.43s.17-.25.25-.41c.08-.17.04-.31-.02-.43s-.55-1.34-.76-1.83c-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.47-.02"
                />
              </svg>
            </span>
          </a>

          <a
            :href="contact.phoneHref"
            class="flex items-center justify-between gap-4 rounded-2xl border border-navy-2 bg-navy/60 px-5 py-4 transition-colors duration-300 hover:border-flame/60 hover:bg-navy-2"
          >
            <span>
              <span class="block text-[0.82rem] text-cream-3">{{ contact.directCallTitle }}</span>
              <span class="mt-0.5 block text-[1.05rem] font-semibold text-cream">{{
                contact.hours
              }}</span>
            </span>
            <span class="text-flame-ink" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6.6 2.5h3l1.5 4-2 1.4a13 13 0 0 0 6 6l1.4-2 4 1.5v3a2 2 0 0 1-2.2 2A17.5 17.5 0 0 1 4.6 4.7a2 2 0 0 1 2-2.2" />
              </svg>
            </span>
          </a>
        </div>
      </div>

      <!-- form -->
      <div class="lg:col-span-7">
        <!-- success -->
        <div
          v-if="submitted"
          class="rounded-[1.5rem] border border-flame/40 bg-navy/70 p-8 sm:p-10"
          role="status"
        >
          <span class="grid size-14 place-items-center rounded-full bg-flame text-on-flame" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m4.5 12.5 5 5 10-11" />
            </svg>
          </span>
          <h3 class="text-h3 mt-6 text-cream">{{ t.quote.successTitle }}</h3>
          <p class="mt-3 max-w-[46ch] text-[1rem] leading-[1.9] text-cream-2">
            {{ serverError ? serverError : t.quote.successMsg }}
          </p>
          <div class="mt-6 flex flex-wrap gap-3">
            <a
              :href="sentUrl"
              target="_blank"
              rel="noopener"
              class="rounded-full bg-flame px-6 py-3 font-medium text-on-flame transition-colors hover:bg-flame-hi"
            >
              {{ t.quote.btnOpenWhatsapp }}
            </a>
            <button
              type="button"
              class="rounded-full border border-navy-3 px-6 py-3 font-medium text-cream transition-colors hover:bg-navy-2"
              @click="reset"
            >
              {{ t.quote.btnAnother }}
            </button>
          </div>
        </div>

        <!-- the form -->
        <form
          v-else
          novalidate
          class="relative rounded-[1.5rem] border border-navy-2 bg-navy/50 p-6 sm:p-9"
          @submit.prevent="onSubmit"
        >
          <p aria-live="polite" class="sr-only">
            {{ errorCount ? t.quote.formAriaCount(errorCount) : '' }}
          </p>

          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label for="q-name" class="mb-2 block text-[0.9rem] text-cream-2">{{ t.quote.labelName }}</label>
              <input
                id="q-name"
                v-model="form.name"
                type="text"
                autocomplete="name"
                :placeholder="t.quote.placeholderName"
                :class="[fieldClass, errors.name ? 'border-flame' : 'border-line']"
                :aria-invalid="Boolean(errors.name)"
                :aria-describedby="errors.name ? 'q-name-err' : undefined"
              />
              <p v-if="errors.name" id="q-name-err" class="mt-1.5 text-[0.85rem] text-flame-ink-hi">
                {{ errors.name }}
              </p>
            </div>

            <div>
              <label for="q-business" class="mb-2 block text-[0.9rem] text-cream-2">{{ t.quote.labelBusiness }}</label>
              <input
                id="q-business"
                v-model="form.business"
                type="text"
                autocomplete="organization"
                :placeholder="t.quote.placeholderBusiness"
                :class="[fieldClass, errors.business ? 'border-flame' : 'border-line']"
                :aria-invalid="Boolean(errors.business)"
                :aria-describedby="errors.business ? 'q-business-err' : undefined"
              />
              <p v-if="errors.business" id="q-business-err" class="mt-1.5 text-[0.85rem] text-flame-ink-hi">
                {{ errors.business }}
              </p>
            </div>

            <div>
              <label for="q-type" class="mb-2 block text-[0.9rem] text-cream-2">{{ t.quote.labelType }}</label>
              <select
                id="q-type"
                v-model="form.type"
                :class="[fieldClass, 'pe-11', errors.type ? 'border-flame' : 'border-line']"
                :aria-invalid="Boolean(errors.type)"
                :aria-describedby="errors.type ? 'q-type-err' : undefined"
              >
                <option value="" disabled>{{ t.quote.selectPlaceholder }}</option>
                <option v-for="typeItem in businessTypes" :key="typeItem" :value="typeItem">{{ typeItem }}</option>
              </select>
              <p v-if="errors.type" id="q-type-err" class="mt-1.5 text-[0.85rem] text-flame-ink-hi">
                {{ errors.type }}
              </p>
            </div>

            <div>
              <label for="q-volume" class="mb-2 block text-[0.9rem] text-cream-2">{{ t.quote.labelVolume }}</label>
              <select
                id="q-volume"
                v-model="form.volume"
                :class="[fieldClass, 'pe-11', errors.volume ? 'border-flame' : 'border-line']"
                :aria-invalid="Boolean(errors.volume)"
                :aria-describedby="errors.volume ? 'q-volume-err' : undefined"
              >
                <option value="" disabled>{{ t.quote.selectPlaceholder }}</option>
                <option v-for="v in monthlyVolumes" :key="v" :value="v">{{ v }}</option>
              </select>
              <p v-if="errors.volume" id="q-volume-err" class="mt-1.5 text-[0.85rem] text-flame-ink-hi">
                {{ errors.volume }}
              </p>
            </div>

            <div>
              <label for="q-gov" class="mb-2 block text-[0.9rem] text-cream-2">{{ t.quote.labelGov }}</label>
              <select
                id="q-gov"
                v-model="form.governorate"
                :class="[fieldClass, 'pe-11', errors.governorate ? 'border-flame' : 'border-line']"
                :aria-invalid="Boolean(errors.governorate)"
                :aria-describedby="errors.governorate ? 'q-gov-err' : undefined"
              >
                <option value="" disabled>{{ t.quote.selectPlaceholder }}</option>
                <option v-for="g in governorates" :key="g" :value="g">{{ g }}</option>
              </select>
              <p v-if="errors.governorate" id="q-gov-err" class="mt-1.5 text-[0.85rem] text-flame-ink-hi">
                {{ errors.governorate }}
              </p>
            </div>

            <div>
              <label for="q-phone" class="mb-2 block text-[0.9rem] text-cream-2">{{ t.quote.labelPhone }}</label>
              <input
                id="q-phone"
                v-model="form.phone"
                type="tel"
                inputmode="tel"
                autocomplete="tel"
                dir="ltr"
                placeholder="01xxxxxxxxx"
                :class="[
                  fieldClass,
                  'num text-start',
                  errors.phone ? 'border-flame' : 'border-navy-3',
                ]"
                :aria-invalid="Boolean(errors.phone)"
                :aria-describedby="errors.phone ? 'q-phone-err' : 'q-phone-hint'"
              />
              <p
                :id="errors.phone ? 'q-phone-err' : 'q-phone-hint'"
                class="mt-1.5 text-[0.85rem]"
                :class="errors.phone ? 'text-flame-ink-hi' : 'text-cream-3'"
              >
                {{ errors.phone || t.quote.phoneHint }}
              </p>
            </div>

            <div class="sm:col-span-2">
              <label for="q-note" class="mb-2 block text-[0.9rem] text-cream-2">
                {{ t.quote.labelNote }} <span class="text-cream-3">{{ t.quote.optional }}</span>
              </label>
              <textarea
                id="q-note"
                v-model="form.note"
                rows="3"
                :placeholder="t.quote.placeholderNote"
                :class="[fieldClass, 'resize-y border-line']"
              />
            </div>
          </div>

          <!-- honeypot: off-screen -->
          <div class="absolute -start-[9999px] size-px overflow-hidden" aria-hidden="true">
            <label for="q-website">{{ t.quote.honeypotLabel }}</label>
            <input id="q-website" v-model="form.website" type="text" tabindex="-1" autocomplete="off" />
          </div>

          <button
            type="submit"
            :disabled="sending"
            class="mt-7 w-full rounded-full bg-flame px-8 py-4 text-[1.05rem] font-semibold text-on-flame transition-[background-color,transform,opacity] duration-300 ease-out-quart hover:bg-flame-hi active:scale-[0.99] disabled:opacity-60 sm:w-auto"
          >
            {{ sending ? t.quote.submitting : t.quote.submit }}
          </button>

          <p class="mt-4 text-[0.82rem] leading-relaxed text-cream-3">
            {{ t.quote.disclaimer }}
          </p>
        </form>
      </div>
    </div>
  </section>
</template>
