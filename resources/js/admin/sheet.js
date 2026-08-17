/**
 * Bottom sheets on <dialog>.
 *
 * The platform gives focus trapping, Escape, inert background and the top
 * layer for free. The only thing it does not give — drag down to dismiss — is
 * the only thing written here.
 *
 *   <button data-sheet-open="filters">…</button>
 *   <dialog class="sheet" data-sheet="filters"> <div class="sheet-panel"> … </div> </dialog>
 */

const DISMISS_AT = 96
const reduced = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches

export function openSheet(name) {
  const el = document.querySelector(`[data-sheet="${name}"]`)
  if (!el || el.open) return
  el.showModal()
  document.documentElement.style.overflow = 'hidden'
  el.querySelector('[autofocus], input, select, textarea, button')?.focus?.()
}

export function closeSheet(el) {
  if (!el || !el.open) return
  const done = () => {
    el.classList.remove('is-closing')
    el.close()
    document.documentElement.style.overflow = ''
  }
  if (reduced()) return done()
  el.classList.add('is-closing')
  setTimeout(done, 210)
}

function wireDrag(el) {
  const panel = el.querySelector('.sheet-panel')
  if (!panel) return

  let startY = null
  let dy = 0

  panel.addEventListener('pointerdown', (e) => {
    // Only start a drag from the top strip; anywhere else and the user is
    // scrolling the sheet's own content.
    if (panel.scrollTop > 0) return
    if (e.target.closest('input, select, textarea, button, a, [contenteditable]')) return
    startY = e.clientY
    dy = 0
    panel.setPointerCapture(e.pointerId)
  })

  panel.addEventListener('pointermove', (e) => {
    if (startY === null) return
    const delta = e.clientY - startY
    // Upward drags meet resistance instead of a hard stop.
    dy = delta > 0 ? delta : delta / 4
    panel.style.transform = `translateY(${dy}px)`
    panel.style.transition = 'none'
  })

  const end = (e) => {
    if (startY === null) return
    startY = null
    panel.style.transition = ''
    panel.style.transform = ''
    try {
      panel.releasePointerCapture(e.pointerId)
    } catch {
      /* pointer already gone */
    }
    if (dy > DISMISS_AT) closeSheet(el)
  }

  panel.addEventListener('pointerup', end)
  panel.addEventListener('pointercancel', end)
}

export function initSheets() {
  document.querySelectorAll('dialog.sheet').forEach((el) => {
    wireDrag(el)

    // Clicking the backdrop closes; clicking inside the panel does not.
    el.addEventListener('pointerdown', (e) => {
      if (e.target === el) closeSheet(el)
    })

    // Escape must run our animation, not the browser's instant close.
    el.addEventListener('cancel', (e) => {
      e.preventDefault()
      closeSheet(el)
    })
  })

  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-sheet-open]')
    if (opener) {
      e.preventDefault()
      openSheet(opener.dataset.sheetOpen)
      return
    }
    const closer = e.target.closest('[data-sheet-close]')
    if (closer) {
      e.preventDefault()
      closeSheet(closer.closest('dialog.sheet'))
    }
  })
}

/**
 * Destructive actions confirm in a real dialog rather than window.confirm —
 * same platform guarantees, and the sentence can say what will actually happen.
 */
export function initConfirm() {
  const dialog = document.querySelector('[data-confirm-dialog]')
  if (!dialog) return

  const message = dialog.querySelector('[data-confirm-message]')
  const accept = dialog.querySelector('[data-confirm-accept]')
  let pending = null

  document.addEventListener('submit', (e) => {
    const form = e.target
    if (!form.dataset.confirm || form.dataset.confirmed === '1') return
    e.preventDefault()
    pending = form
    message.textContent = form.dataset.confirm
    dialog.showModal()
  })

  accept.addEventListener('click', () => {
    dialog.close()
    if (!pending) return
    pending.dataset.confirmed = '1'
    pending.requestSubmit()
    pending = null
  })

  dialog.addEventListener('close', () => {
    pending = null
  })
}
