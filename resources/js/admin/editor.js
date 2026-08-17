/**
 * The article editor.
 *
 * A contenteditable surface with a fixed toolbar, written here rather than
 * pulled from a library: the whole app is meant to be readable and repairable
 * by whoever inherits it.
 *
 * Two rules keep it out of trouble:
 *   1. paste is always inserted as plain text, so Word and Google Docs cannot
 *      smuggle in spans, fonts and comment markup
 *   2. the visible surface is never the source of truth — every change syncs
 *      into a real <textarea name="body">, which is what the form submits
 */

const BLOCKS = {
  p: 'نص عادي',
  h2: 'عنوان رئيسي',
  h3: 'عنوان فرعي',
  h4: 'عنوان صغير',
}

function exec(cmd, value = null) {
  document.execCommand(cmd, false, value)
}

export function initEditor() {
  const wrap = document.querySelector('[data-editor]')
  if (!wrap) return

  const surface = wrap.querySelector('[data-editor-surface]')
  const output = document.querySelector('textarea[name="body"]')
  const toolbar = wrap.querySelector('[data-editor-toolbar]')
  const counter = wrap.querySelector('[data-editor-count]')
  if (!surface || !output) return

  surface.innerHTML = output.value || ''

  const sync = () => {
    output.value = surface.innerHTML.trim()
    output.dispatchEvent(new Event('input', { bubbles: true }))
    if (counter) {
      const text = surface.innerText.trim()
      counter.textContent = text ? String(text.split(/\s+/).length) : '0'
    }
  }

  surface.addEventListener('input', sync)
  surface.addEventListener('blur', sync)

  // Plain-text paste, always.
  surface.addEventListener('paste', (e) => {
    e.preventDefault()
    const text = (e.clipboardData || window.clipboardData).getData('text/plain')
    exec('insertText', text)
    sync()
  })

  // Enter inside a heading should start a paragraph, not another heading.
  surface.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter' || e.shiftKey) return
    const block = document.getSelection()?.anchorNode
    const el = block?.nodeType === 1 ? block : block?.parentElement
    if (el?.closest('h2, h3, h4')) {
      setTimeout(() => exec('formatBlock', '<p>'), 0)
    }
  })

  toolbar?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-cmd]')
    if (!btn) return
    e.preventDefault()
    surface.focus()

    const { cmd, value } = btn.dataset

    switch (cmd) {
      case 'block':
        exec('formatBlock', `<${value}>`)
        break
      case 'link': {
        const url = prompt('الرابط:', 'https://')
        if (url) exec('createLink', url)
        break
      }
      case 'unlink':
        exec('unlink')
        break
      case 'image':
        wrap.querySelector('[data-editor-upload]')?.click()
        break
      case 'hr':
        exec('insertHTML', '<hr>')
        break
      case 'quote':
        exec('formatBlock', '<blockquote>')
        break
      case 'clean':
        exec('removeFormat')
        break
      default:
        exec(cmd)
    }
    sync()
  })

  // Reflect the caret's current block in the toolbar.
  const blockSelect = wrap.querySelector('[data-block-select]')
  if (blockSelect) {
    blockSelect.innerHTML = Object.entries(BLOCKS)
      .map(([tag, label]) => `<option value="${tag}">${label}</option>`)
      .join('')

    blockSelect.addEventListener('change', () => {
      surface.focus()
      exec('formatBlock', `<${blockSelect.value}>`)
      sync()
    })

    document.addEventListener('selectionchange', () => {
      if (!surface.contains(document.getSelection()?.anchorNode)) return
      const node = document.getSelection().anchorNode
      const el = node?.nodeType === 1 ? node : node?.parentElement
      const tag = el?.closest('h2, h3, h4, p')?.tagName?.toLowerCase()
      if (tag && BLOCKS[tag]) blockSelect.value = tag
    })
  }

  /* ------------------------------------------------------------- uploads */

  const upload = wrap.querySelector('[data-editor-upload]')
  upload?.addEventListener('change', async () => {
    const file = upload.files?.[0]
    if (!file) return
    const path = await uploadFile(file, wrap.dataset.uploadUrl)
    if (path) {
      surface.focus()
      exec(
        'insertHTML',
        `<figure><img src="${path}" alt=""><figcaption>اكتب وصف الصورة</figcaption></figure><p><br></p>`,
      )
      sync()
    }
    upload.value = ''
  })

  sync()
}

async function uploadFile(file, url) {
  const data = new FormData()
  data.append('file', file)

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        Accept: 'application/json',
      },
      body: data,
    })
    const body = await res.json()
    if (!res.ok) {
      alert(body?.errors?.file?.[0] || 'الرفع فشل.')
      return null
    }
    return body.path
  } catch {
    alert('الرفع فشل — راجع الاتصال.')
    return null
  }
}

/** Cover-image picker, same endpoint, different destination. */
export function initCoverUpload() {
  document.querySelectorAll('[data-cover-upload]').forEach((input) => {
    const target = document.querySelector(input.dataset.coverTarget)
    const preview = document.querySelector(input.dataset.coverPreview)

    input.addEventListener('change', async () => {
      const file = input.files?.[0]
      if (!file || !target) return
      const path = await uploadFile(file, input.dataset.uploadUrl)
      if (path) {
        target.value = path
        target.dispatchEvent(new Event('input', { bubbles: true }))
        if (preview) {
          preview.src = path
          preview.classList.remove('hidden')
        }
      }
      input.value = ''
    })
  })
}
