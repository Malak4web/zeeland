/**
 * Blog-page behaviour. Everything here is progressive: the article reads fine
 * with this file removed, so nothing gates content on JS.
 */

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches

function readingProgress() {
  const bar = document.querySelector('[data-reading-progress]')
  const article = document.querySelector('article')
  if (!bar || !article) return

  let ticking = false
  const measure = () => {
    ticking = false
    const start = article.offsetTop
    const span = article.offsetHeight - window.innerHeight
    if (span <= 0) return
    const p = Math.min(1, Math.max(0, (window.scrollY - start) / span))
    bar.style.width = `${(p * 100).toFixed(2)}%`
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
  measure()
}

/** Highlight the section you are actually reading in the pinned contents. */
function tocSpy() {
  const list = document.querySelector('[data-toc]')
  if (!list) return

  const links = [...list.querySelectorAll('a[href^="#"]')]
  const targets = links
    .map((a) => document.getElementById(decodeURIComponent(a.getAttribute('href').slice(1))))
    .filter(Boolean)
  if (!targets.length) return

  let ticking = false
  const measure = () => {
    ticking = false
    const line = window.innerHeight * 0.3
    let active = 0
    targets.forEach((t, i) => {
      if (t.getBoundingClientRect().top <= line) active = i
    })
    links.forEach((a, i) => a.setAttribute('aria-current', String(i === active)))
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
  measure()
}

function copyLink() {
  document.querySelectorAll('[data-copy-link]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const url = btn.dataset.copyLink
      const label = btn.textContent
      try {
        await navigator.clipboard.writeText(url)
        btn.textContent = 'اتنسخ ✓'
      } catch {
        // clipboard is blocked on insecure origins — show the URL instead
        btn.textContent = url
      }
      setTimeout(() => {
        btn.textContent = label
      }, 2000)
    })
  })
}

export function initBlog() {
  if (!document.querySelector('article')) return
  if (!reduced) readingProgress()
  tocSpy()
  copyLink()
}
