/**
 * The order builder.
 *
 * Lines are added, removed and priced in the page; the total recomputes on
 * every keystroke so nobody saves an order whose number they have not seen.
 * The server recalculates from the saved lines anyway — this is the visible
 * half of the same arithmetic, never the authority.
 */

const money = (n) =>
  (Number(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

export function initOrderEditor() {
  const root = document.querySelector('[data-order-editor]')
  if (!root) return

  const body = root.querySelector('[data-lines]')
  const template = root.querySelector('template[data-line-template]')
  const addBtn = root.querySelector('[data-add-line]')
  const products = JSON.parse(root.dataset.products || '[]')
  const customers = JSON.parse(root.dataset.customers || '[]')
  const customerSelect = document.querySelector('[data-customer-select]')

  let seq = body.querySelectorAll('[data-line]').length

  function renumber() {
    body.querySelectorAll('[data-line]').forEach((row, i) => {
      row.querySelectorAll('[name]').forEach((input) => {
        input.name = input.name.replace(/items\[\d*\]/, `items[${i}]`)
      })
      const n = row.querySelector('[data-line-no]')
      if (n) n.textContent = String(i + 1)
    })
    // A single remaining line cannot be removed — an order needs one.
    const rows = body.querySelectorAll('[data-line]')
    rows.forEach((r) => {
      const btn = r.querySelector('[data-remove-line]')
      if (btn) btn.disabled = rows.length <= 1
    })
  }

  function recalc() {
    let subtotal = 0
    let packs = 0

    body.querySelectorAll('[data-line]').forEach((row) => {
      const qty = Number(row.querySelector('[data-qty]')?.value) || 0
      const price = Number(row.querySelector('[data-price]')?.value) || 0
      const total = qty * price
      subtotal += total
      packs += qty
      const cell = row.querySelector('[data-line-total]')
      if (cell) cell.textContent = money(total)
    })

    const discount = Number(root.querySelector('[data-discount]')?.value) || 0
    const shipping = Number(root.querySelector('[data-shipping]')?.value) || 0
    const total = Math.max(0, subtotal - discount + shipping)

    const set = (sel, value) => {
      const el = root.querySelector(sel)
      if (el) el.textContent = money(value)
    }
    set('[data-subtotal]', subtotal)
    set('[data-grand-total]', total)

    const packsEl = root.querySelector('[data-packs]')
    if (packsEl) packsEl.textContent = money(packs).replace(/\.00$/, '')
  }

  function fillFromProduct(row, id) {
    const p = products.find((x) => String(x.id) === String(id))
    if (!p) return
    row.querySelector('[data-name]').value = p.name
    row.querySelector('[data-unit]').value = p.unit
    const price = row.querySelector('[data-price]')
    // Customer-specific pricing wins over the list price.
    const custom = customerPrice()
    if (!price.value || Number(price.value) === 0) price.value = custom ?? p.price
    recalc()
  }

  function customerPrice() {
    if (!customerSelect) return null
    const c = customers.find((x) => String(x.id) === String(customerSelect.value))
    const v = c?.price_per_pack
    return v ? Number(v) : null
  }

  function addLine() {
    const frag = template.content.cloneNode(true)
    const row = frag.querySelector('[data-line]')
    row.querySelectorAll('[name]').forEach((input) => {
      input.name = input.name.replace('items[]', `items[${seq}]`)
    })
    body.appendChild(frag)
    seq++
    renumber()
    recalc()
    body.lastElementChild.querySelector('[data-product]')?.focus()
  }

  addBtn?.addEventListener('click', addLine)

  root.addEventListener('click', (e) => {
    const remove = e.target.closest('[data-remove-line]')
    if (!remove) return
    if (body.querySelectorAll('[data-line]').length <= 1) return
    remove.closest('[data-line]').remove()
    renumber()
    recalc()
  })

  root.addEventListener('input', (e) => {
    if (e.target.matches('[data-qty], [data-price], [data-discount], [data-shipping]')) recalc()
  })

  root.addEventListener('change', (e) => {
    const select = e.target.closest('[data-product]')
    if (select) fillFromProduct(select.closest('[data-line]'), select.value)
  })

  // Switching customer re-prices any line still sitting at zero.
  customerSelect?.addEventListener('change', () => {
    const price = customerPrice()
    const c = customers.find((x) => String(x.id) === String(customerSelect.value))
    const addr = document.querySelector('[data-delivery-address]')
    if (addr && !addr.value && c?.address) addr.value = c.address
    if (price == null) return
    body.querySelectorAll('[data-price]').forEach((input) => {
      if (!input.value || Number(input.value) === 0) input.value = price
    })
    recalc()
  })

  renumber()
  recalc()
}
