/**
 * Light / dark, shared by the site and the dashboard.
 *
 * The resolved theme is already on <html> before this file loads — the boot
 * snippet in the layout head does that synchronously so the first paint is
 * never the wrong colour. This module only owns the *change*: the buttons,
 * the persisted choice, and following the OS while no choice has been made.
 */

const KEY = 'zl-theme'
const DARK = 'dark'
const LIGHT = 'light'

/* Matches the ground of each theme in theme.css. Mobile browsers paint the
   address bar with this, and a dark bar over a light page is the loudest way
   to say the theme was bolted on afterwards. */
const BAR = { dark: '#010e1e', light: '#f3f8fd' }

function stored() {
  try {
    const v = localStorage.getItem(KEY)
    return v === LIGHT || v === DARK ? v : null
  } catch {
    // Private mode / disabled storage: the toggle still works for this page.
    return null
  }
}

const systemTheme = () =>
  window.matchMedia?.('(prefers-color-scheme: light)').matches ? LIGHT : DARK

const current = () => document.documentElement.dataset.theme || systemTheme()

const motionOk = () => !window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

function paint(theme) {
  const root = document.documentElement
  root.dataset.theme = theme

  for (const meta of document.querySelectorAll('meta[name="theme-color"]')) {
    // Two media-scoped tags ship for the no-JS case; once a choice exists they
    // must stop competing, so the losing one is muted rather than removed.
    if (meta.dataset.scheme) meta.media = meta.dataset.scheme === theme ? 'all' : 'not all'
    else meta.content = BAR[theme]
  }

  for (const btn of document.querySelectorAll('[data-theme-toggle]')) {
    const next = theme === DARK ? 'الوضع الفاتح' : 'الوضع الداكن'
    btn.setAttribute('aria-label', `بدّل لـ${next}`)
    btn.setAttribute('title', next)
    // aria-pressed would claim the button is a state; it is a switch between
    // two named modes, so the accessible name carries the meaning instead.
  }

  document.dispatchEvent(new CustomEvent('zl:theme', { detail: { theme } }))
}

/**
 * Swap the theme with a circular reveal growing out of the control that was
 * pressed. Everything about it is optional: no View Transitions, reduced
 * motion, or no event, and the swap simply happens.
 */
function set(theme, origin) {
  if (theme === current()) return
  try {
    localStorage.setItem(KEY, theme)
  } catch {
    /* choice lasts for this page only */
  }

  if (!document.startViewTransition || !motionOk() || !origin) {
    paint(theme)
    return
  }

  const root = document.documentElement
  const { x, y } = origin
  // Radius to the farthest corner, so the wipe always finishes off-screen.
  const r = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y))

  root.style.setProperty('--wipe-x', `${x}px`)
  root.style.setProperty('--wipe-y', `${y}px`)
  root.style.setProperty('--wipe-r', `${r}px`)
  root.dataset.themeAnim = ''

  const transition = document.startViewTransition(() => paint(theme))
  transition.finished.finally(() => delete root.dataset.themeAnim)
}

export function initTheme() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest?.('[data-theme-toggle]')
    if (!btn) return
    e.preventDefault()
    const box = btn.getBoundingClientRect()
    set(current() === DARK ? LIGHT : DARK, {
      x: box.left + box.width / 2,
      y: box.top + box.height / 2,
    })
  })

  // Follow the OS until the visitor overrides it — and keep following it in
  // every other tab of the same site once they clear the choice.
  window.matchMedia?.('(prefers-color-scheme: light)').addEventListener?.('change', () => {
    if (!stored()) paint(systemTheme())
  })

  paint(current())
}
