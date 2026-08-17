/**
 * The live SEO assistant.
 *
 * All the rules live on the server (App\Support\SeoAnalyzer). This file only
 * gathers the form, debounces, and paints the answer — so the score an editor
 * watches while typing is the same score that gets saved, with no second copy
 * of the rules to drift out of sync.
 */

const DEBOUNCE_MS = 550

const ICONS = {
  pass: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.5 5 5 10-11"/></svg>',
  warn: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 6v8M12 18h.01"/></svg>',
  fail: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>',
}

const TONE = { pass: 'good', warn: 'warn', fail: 'bad' }

export function initSeoPanel() {
  const panel = document.querySelector('[data-seo-panel]')
  const form = document.querySelector('[data-post-form]')
  if (!panel || !form) return

  const list = panel.querySelector('[data-seo-checks]')
  const ring = panel.querySelector('[data-seo-ring]')
  const scoreEl = panel.querySelector('[data-seo-score]')
  const verdict = panel.querySelector('[data-seo-verdict]')
  const wordsEl = panel.querySelector('[data-seo-words]')
  const snippetTitle = panel.querySelector('[data-snippet-title]')
  const snippetUrl = panel.querySelector('[data-snippet-url]')
  const snippetDesc = panel.querySelector('[data-snippet-desc]')
  const origin = panel.dataset.origin || ''

  let timer = null
  let inflight = null

  const field = (name) => form.querySelector(`[name="${name}"]`)
  const slugField = form.querySelector('[data-slug-target]')

  // Once someone edits the slug by hand it is theirs; we stop suggesting.
  let slugTouched = Boolean(slugField?.value.trim())
  slugField?.addEventListener('input', () => {
    slugTouched = true
  })

  function payload() {
    const get = (n) => field(n)?.value ?? ''
    return {
      title: get('title'),
      meta_title: get('meta_title'),
      meta_description: get('meta_description'),
      // Send the slug only once it is the author's. Sending back the value we
      // just suggested would pin it: the server would keep re-deriving the
      // slug from itself instead of from the title.
      slug: slugTouched ? get('slug') : '',
      excerpt: get('excerpt'),
      body: get('body'),
      focus_keyword: get('focus_keyword'),
      cover_image: get('cover_image'),
      cover_alt: get('cover_alt'),
      noindex: field('noindex')?.checked ? 1 : 0,
    }
  }

  function paint(data) {
    // Ring: 2πr for r=26 ≈ 163.4
    const c = 163.4
    if (ring) {
      ring.style.strokeDasharray = `${c}`
      ring.style.strokeDashoffset = `${c - (c * data.score) / 100}`
      ring.style.stroke =
        data.score >= 80
          ? 'var(--good)'
          : data.score >= 55
            ? 'var(--warn)'
            : 'var(--bad)'
    }
    if (scoreEl) scoreEl.textContent = String(data.score)
    if (verdict) {
      verdict.textContent =
        data.score >= 80
          ? 'جاهز للنشر'
          : data.score >= 55
            ? 'كويس — فيه نقط تتظبط'
            : 'محتاج شغل قبل النشر'
    }
    if (wordsEl) wordsEl.textContent = String(data.words)

    if (list) {
      list.innerHTML = data.checks
        .map((c) => {
          const tone = TONE[c.status]
          return `<li class="flex items-start gap-2.5 py-2 border-b border-navy-2/60 last:border-0">
            <span class="mt-0.5 grid size-[1.15rem] shrink-0 place-items-center rounded-full text-abyss" style="background:var(--${tone})">${ICONS[c.status]}</span>
            <span class="min-w-0">
              <span class="block text-xs font-medium text-cream">${c.label}</span>
              <span class="block text-2xs leading-[1.7] text-cream-3">${c.hint}</span>
            </span>
          </li>`
        })
        .join('')
    }

    // The server owns slug generation; the field just receives it. An empty
    // title has no slug to suggest, so leave the field alone.
    if (slugField && !slugTouched && field('title')?.value.trim()) {
      slugField.value = data.suggested_slug || ''
    }

    const p = data.preview
    if (snippetTitle) snippetTitle.textContent = p.title || 'عنوان المقال'
    if (snippetUrl) snippetUrl.textContent = `${origin}/blog/${p.url || '…'}`
    if (snippetDesc) {
      snippetDesc.textContent =
        p.description || 'الوصف اللي هيظهر تحت العنوان في نتايج البحث بيتكتب من هنا.'
    }
  }

  async function run() {
    inflight?.abort()
    inflight = new AbortController()

    try {
      const res = await fetch(panel.dataset.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify(payload()),
        signal: inflight.signal,
      })
      if (!res.ok) return
      paint(await res.json())
    } catch {
      /* aborted or offline — the last painted result stays on screen */
    }
  }

  const schedule = () => {
    clearTimeout(timer)
    timer = setTimeout(run, DEBOUNCE_MS)
  }

  form.addEventListener('input', schedule)
  form.addEventListener('change', schedule)

  // Paint once on load so the panel is never blank before the first keystroke.
  run()
}
