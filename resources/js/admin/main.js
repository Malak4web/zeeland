import '../../css/admin.css'

import { initConfirm, initSheets, openSheet } from './sheet.js'
import { initOrderEditor } from './orderEditor.js'
import { initCoverUpload, initEditor } from './editor.js'
import { initSeoPanel } from './seoPanel.js'
import { initTheme } from '../theme.js'

/** Filter bars apply on change instead of hiding behind a "بحث" button. */
function initAutoFilters() {
  document.querySelectorAll('form[data-auto-filter]').forEach((form) => {
    form.addEventListener('change', (e) => {
      if (e.target.matches('select, input[type="date"], input[type="checkbox"]')) form.requestSubmit()
    })

    // Text search waits for a pause, not for every keystroke.
    const search = form.querySelector('input[type="search"]')
    if (!search) return
    let t = null
    search.addEventListener('input', () => {
      clearTimeout(t)
      t = setTimeout(() => form.requestSubmit(), 450)
    })
  })
}

/** Flash messages fade out on their own; errors stay until dismissed. */
function initFlash() {
  document.querySelectorAll('[data-flash]').forEach((el) => {
    el.querySelector('[data-flash-close]')?.addEventListener('click', () => el.remove())
    if (el.dataset.flash !== 'ok') return
    setTimeout(() => {
      el.style.transition = 'opacity .4s, transform .4s'
      el.style.opacity = '0'
      el.style.transform = 'translateY(-.5rem)'
      setTimeout(() => el.remove(), 420)
    }, 4200)
  })
}

/** Filter long <select> lists in place — customer pickers get long fast. */
function initSelectFilters() {
  document.querySelectorAll('[data-select-filter]').forEach((input) => {
    const select = document.querySelector(input.dataset.selectFilter)
    if (!select) return
    const all = [...select.options].map((o) => ({ value: o.value, text: o.text }))

    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase()
      const keep = all.filter((o) => !q || o.text.toLowerCase().includes(q))
      const current = select.value
      select.innerHTML = keep.map((o) => `<option value="${o.value}">${o.text}</option>`).join('')
      if (keep.some((o) => o.value === current)) select.value = current
    })
  })
}

/** Submit a form with ⌘/Ctrl + Enter from anywhere inside it. */
function initShortcuts() {
  document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
      e.target.closest('form')?.requestSubmit()
      return
    }
    // "/" focuses search unless you are already typing.
    if (e.key === '/' && !e.target.matches('input, textarea, select, [contenteditable]')) {
      const search = document.querySelector('input[type="search"]')
      if (search) {
        e.preventDefault()
        search.focus()
      }
    }
  })
}

/** Mobile: the top bar gets out of the way while you read down a long table. */
function initHideOnScroll() {
  const bar = document.querySelector('[data-hide-on-scroll]')
  if (!bar) return
  let last = window.scrollY
  let ticking = false

  const measure = () => {
    ticking = false
    const y = Math.max(0, window.scrollY)
    const delta = y - last
    if (Math.abs(delta) < 6) return
    bar.classList.toggle('-translate-y-full', delta > 0 && y > 140)
    last = y
  }

  window.addEventListener(
    'scroll',
    () => {
      if (ticking) return
      ticking = true
      requestAnimationFrame(measure)
    },
    { passive: true },
  )
}

function boot() {
  initTheme()
  initSheets()
  initConfirm()
  initAutoFilters()
  initFlash()
  initSelectFilters()
  initShortcuts()
  initHideOnScroll()
  initOrderEditor()
  initEditor()
  initCoverUpload()
  initSeoPanel()

  // Deep link straight into a sheet: /admin/payments#new
  if (location.hash.startsWith('#sheet-')) openSheet(location.hash.slice(7))
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot)
} else {
  boot()
}
