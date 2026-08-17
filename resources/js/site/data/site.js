/**
 * Zeeland — Single source of truth for dynamic site data and localized content
 */
import { computed } from 'vue'
import { t, currentLocale, isRTL, isLTR, setLocale, toggleLocale } from '../i18n.js'

export { currentLocale, isRTL, isLTR, setLocale, toggleLocale, t }

/* ── Contact ────────────────────────────────────────────────────────────── */
export const contact = computed(() => t.value.contact)

export const waMessage = (body) =>
  `https://wa.me/${t.value.contact.whatsapp}?text=${encodeURIComponent(body)}`

export const waDefault = computed(() =>
  waMessage(t.value.quote.waDefaultMessage),
)

/* ── Navigation ─────────────────────────────────────────────────────────── */
export const navLinks = computed(() => t.value.navLinks)

/* ── Hero Pack Facts ────────────────────────────────────────────────────── */
export const packFacts = computed(() => t.value.packFacts)

/* ── Why Santana Specs ─────────────────────────────────────────────────── */
export const santanaSpecs = computed(() => t.value.santana.specs)

/* ── The 6 Stages Journey ───────────────────────────────────────────────── */
export const journey = computed(() => t.value.journey.stages)

/* ── Frying Technique ───────────────────────────────────────────────────── */
export const fryRules = computed(() => t.value.fry.rules)

/* ── Product Specifications ────────────────────────────────────────────── */
export const productSpecs = computed(() => t.value.specs.productSpecs)

/* ── Nutrition Facts ────────────────────────────────────────────────────── */
export const nutrition = computed(() => ({
  servingSize: t.value.specs.nutritionServing,
  servingsPerContainer: 25,
  rows: t.value.specs.nutritionRows,
}))

/* ── Target Audiences & Promises ────────────────────────────────────────── */
export const audiences = computed(() => t.value.foodservice.audiences)
export const promises = computed(() => t.value.foodservice.promises)

/* ── Certifications ─────────────────────────────────────────────────────── */
export const certifications = computed(() => t.value.trust.certifications)

/* ── FAQ ────────────────────────────────────────────────────────────────── */
export const faq = computed(() => t.value.faq.items)

/* ── Quote Form Options ─────────────────────────────────────────────────── */
export const businessTypes = computed(() => t.value.quote.businessTypes)
export const monthlyVolumes = computed(() => t.value.quote.monthlyVolumes)
export const governorates = computed(() => t.value.quote.governorates)
