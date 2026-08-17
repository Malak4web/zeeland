/**
 * v-reveal — scroll-entrance that can never hide content permanently.
 *
 * The element is visible in CSS by default. This directive *arms* the hidden
 * state only when it is genuinely able to un-arm it: an IntersectionObserver
 * exists and the user has not asked for reduced motion. On top of that, a
 * 1.2s safety timer un-arms the element no matter what — so a background tab,
 * a headless render, or a dead observer still ships the section fully visible.
 *
 *   v-reveal            → default entrance
 *   v-reveal="150"      → 150ms delay (for hand-tuned staggers)
 */

const SAFETY_MS = 1200

const reduced = () =>
  typeof matchMedia === 'function' && matchMedia('(prefers-reduced-motion: reduce)').matches

let observer = null

function getObserver() {
  if (observer) return observer
  observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue
        entry.target.dataset.reveal = 'in'
        observer.unobserve(entry.target)
      }
    },
    { rootMargin: '0px 0px -12% 0px', threshold: 0.08 },
  )
  return observer
}

export const reveal = {
  mounted(el, binding) {
    if (typeof IntersectionObserver === 'undefined' || reduced()) return

    const delay = Number(binding.value) || 0
    if (delay) el.style.setProperty('--reveal-delay', `${delay}ms`)

    el.dataset.reveal = 'armed'
    getObserver().observe(el)

    el._revealTimer = setTimeout(() => {
      if (el.dataset.reveal !== 'in') {
        el.dataset.reveal = 'in'
        observer?.unobserve(el)
      }
    }, SAFETY_MS + delay)
  },

  unmounted(el) {
    clearTimeout(el._revealTimer)
    observer?.unobserve(el)
  },
}
