import { computed, reactive, ref } from 'vue'
import ar from './data/locales/ar.js'
import en from './data/locales/en.js'

const STORAGE_KEY = 'zeeland_lang'
const SUPPORTED_LOCALES = ['ar', 'en']

const messages = { ar, en }

// Read saved preference or default to Arabic
function getInitialLocale() {
  if (typeof window !== 'undefined') {
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved && SUPPORTED_LOCALES.includes(saved)) {
        return saved
      }
      // Check query parameter ?lang=
      const params = new URLSearchParams(window.location.search)
      const qLang = params.get('lang')
      if (qLang && SUPPORTED_LOCALES.includes(qLang)) {
        return qLang
      }
    } catch {
      // Storage unavailable or disabled
    }
  }
  return 'ar'
}

export const currentLocale = ref(getInitialLocale())

export const isRTL = computed(() => currentLocale.value === 'ar')
export const isLTR = computed(() => currentLocale.value === 'en')

export const t = computed(() => messages[currentLocale.value] || messages.ar)

export function applyDocumentLanguage(locale) {
  if (typeof document === 'undefined') return
  const dir = locale === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.setAttribute('lang', locale)
  document.documentElement.setAttribute('dir', dir)
  document.documentElement.dataset.locale = locale
}

export function setLocale(locale) {
  if (!SUPPORTED_LOCALES.includes(locale)) return
  currentLocale.value = locale
  try {
    localStorage.setItem(STORAGE_KEY, locale)
  } catch {
    // ignore
  }
  applyDocumentLanguage(locale)
}

export function toggleLocale() {
  const next = currentLocale.value === 'ar' ? 'en' : 'ar'
  setLocale(next)
  return next
}

// Initialize document attributes on module load
if (typeof document !== 'undefined') {
  applyDocumentLanguage(currentLocale.value)
}
