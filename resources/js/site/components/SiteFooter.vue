<script setup>
import { ref } from 'vue'
import BrandMark from './BrandMark.vue'
import LangToggle from './LangToggle.vue'
import AppSheet from './AppSheet.vue'
import { contact, navLinks } from '../data/site.js'
import { t } from '../i18n.js'

const year = new Date().getFullYear()
const loginOpen = ref(false)
const loginForm = ref({ email: '', password: '', remember: false })
const loginError = ref('')
const submitting = ref(false)

async function handleLogin() {
  loginError.value = ''
  submitting.value = true
  try {
    /* Fetch the CSRF cookie first (Laravel Sanctum / web guard) */
    const tokenMeta = document.querySelector('meta[name="csrf-token"]')
    const token = tokenMeta ? tokenMeta.content : ''

    const res = await fetch('/admin/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token,
      },
      body: JSON.stringify({
        email: loginForm.value.email,
        password: loginForm.value.password,
        remember: loginForm.value.remember ? '1' : '',
      }),
    })

    if (res.redirected) {
      window.location.href = res.url
      return
    }

    if (res.ok) {
      window.location.href = '/admin'
      return
    }

    /* Try to extract a validation / auth error message */
    const contentType = res.headers.get('content-type') || ''
    if (contentType.includes('application/json')) {
      const data = await res.json()
      loginError.value = data.message || data.errors?.email?.[0] || t.value?.common?.loginError || 'Login failed'
    } else {
      /* Probably a redirect to the login page with session errors — just
         fall back to posting a normal form so the server can handle it. */
      submitAsForm()
      return
    }
  } catch {
    submitAsForm()
  } finally {
    submitting.value = false
  }
}

/** Fallback: post a normal HTML form to /admin/login */
function submitAsForm() {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/admin/login'

  const tokenMeta = document.querySelector('meta[name="csrf-token"]')
  if (tokenMeta) {
    const csrf = document.createElement('input')
    csrf.type = 'hidden'
    csrf.name = '_token'
    csrf.value = tokenMeta.content
    form.appendChild(csrf)
  }

  const email = document.createElement('input')
  email.type = 'hidden'
  email.name = 'email'
  email.value = loginForm.value.email
  form.appendChild(email)

  const pw = document.createElement('input')
  pw.type = 'hidden'
  pw.name = 'password'
  pw.value = loginForm.value.password
  form.appendChild(pw)

  if (loginForm.value.remember) {
    const rem = document.createElement('input')
    rem.type = 'hidden'
    rem.name = 'remember'
    rem.value = '1'
    form.appendChild(rem)
  }

  document.body.appendChild(form)
  form.submit()
}
</script>

<template>
  <footer class="border-t border-navy-2 bg-navy/35 pb-[calc(6rem+env(safe-area-inset-bottom))] lg:pb-0">
    <div class="container-zl py-14 lg:py-16">
      <div class="grid gap-x-14 gap-y-10 lg:grid-cols-12">
        <div class="lg:col-span-5">
          <div class="text-cream">
            <BrandMark :size="44" />
          </div>
          <p class="mt-6 max-w-[42ch] text-[1rem] leading-[1.9] text-cream-2">
            {{ t.footer.description }}
          </p>

          <div class="mt-6 flex items-center gap-3">
            <LangToggle compact />
          </div>
        </div>

        <nav aria-labelledby="footer-nav" class="lg:col-span-3">
          <h2 id="footer-nav" class="text-[0.82rem] tracking-wide text-cream-3">{{ t.footer.navHeading }}</h2>
          <ul class="mt-4 flex flex-col gap-1">
            <li v-for="link in navLinks" :key="link.href">
              <a
                :href="link.href"
                class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi"
                >{{ link.label }}</a
              >
            </li>
            <li>
              <a
                href="#quote"
                class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi"
                >{{ t.footer.quoteLink }}</a
              >
            </li>
          </ul>
        </nav>

        <div class="lg:col-span-4">
          <h2 class="text-[0.82rem] tracking-wide text-cream-3">{{ t.footer.contactHeading }}</h2>
          <ul class="mt-4 flex flex-col gap-2 text-cream-2">
            <li>
              <a
                :href="contact.phoneHref"
                class="num inline-block py-1 text-[1.05rem] text-cream transition-colors hover:text-flame-ink-hi"
                >{{ contact.phoneDisplay }}</a
              >
            </li>
            <li>
              <a
                :href="`mailto:${contact.email}`"
                class="num inline-block py-1 text-[0.95rem] transition-colors hover:text-flame-ink-hi"
                >{{ contact.email }}</a
              >
            </li>
            <li class="pt-1 text-[0.95rem] leading-relaxed text-cream-3">
              <span v-for="line in contact.addressLines" :key="line" class="block">{{ line }}</span>
            </li>
            <li class="text-[0.95rem] text-cream-3">{{ contact.hours }}</li>
          </ul>
        </div>
      </div>

      <hr class="rule-fade my-10" />

      <div class="flex flex-col gap-3 text-[0.85rem] text-cream-3 sm:flex-row sm:justify-between sm:items-center">
        <p><span class="ltr-iso">© {{ year }} {{ contact.company }}</span> — {{ t.common.allRightsReserved }}</p>
        <p>
          <a href="https://zadians.com/" target="_blank" rel="noopener" class="transition-colors hover:text-flame-ink-hi">{{ t.common.developedBy }}</a>
        </p>
        <button
          type="button"
          class="inline-flex items-center gap-1.5 text-cream-3/60 transition-colors hover:text-flame-ink-hi cursor-pointer"
          @click="loginOpen = true"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          {{ t.common.adminLogin }}
        </button>
      </div>
    </div>

    <!-- Admin Login Sheet -->
    <AppSheet v-model="loginOpen" :title="t.common.adminLogin" :subtitle="t.common.adminLoginSubtitle">
      <form @submit.prevent="handleLogin" class="flex flex-col gap-4">
        <!-- Error message -->
        <div
          v-if="loginError"
          role="alert"
          class="rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-cream"
        >
          {{ loginError }}
        </div>

        <!-- Email -->
        <div>
          <label for="login-email" class="mb-1.5 block text-[0.85rem] text-cream-2">{{ t.common.email }}</label>
          <input
            id="login-email"
            v-model="loginForm.email"
            type="email"
            inputmode="email"
            autocomplete="username"
            required
            dir="ltr"
            placeholder="you@zeeland-foods.com"
            class="w-full rounded-xl border border-navy-3 bg-navy/60 px-4 py-3 text-[0.95rem] text-cream placeholder:text-cream-3/50 outline-none transition-colors focus:border-flame/60 text-start"
          />
        </div>

        <!-- Password -->
        <div>
          <label for="login-password" class="mb-1.5 block text-[0.85rem] text-cream-2">{{ t.common.password }}</label>
          <input
            id="login-password"
            v-model="loginForm.password"
            type="password"
            autocomplete="current-password"
            required
            dir="ltr"
            class="w-full rounded-xl border border-navy-3 bg-navy/60 px-4 py-3 text-[0.95rem] text-cream outline-none transition-colors focus:border-flame/60 text-start"
          />
        </div>

        <!-- Remember me -->
        <label class="flex cursor-pointer items-center gap-2.5 py-1 text-sm text-cream-2">
          <input v-model="loginForm.remember" type="checkbox" class="size-4 accent-flame rounded" />
          {{ t.common.rememberMe }}
        </label>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="submitting"
          class="mt-1 w-full rounded-xl bg-flame px-6 py-3.5 text-center font-display font-bold text-flame-ink transition-all hover:brightness-110 active:scale-[0.98] disabled:opacity-60"
        >
          {{ submitting ? '...' : t.common.loginBtn }}
        </button>
      </form>
    </AppSheet>
  </footer>
</template>
