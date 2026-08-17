<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'
import { t } from '../i18n.js'

/**
 * A native-app bottom sheet, built on <dialog> so focus trapping, Escape and
 * inertness come from the platform rather than from us re-implementing them.
 * Adds the one thing the platform doesn't give: drag-down-to-dismiss.
 */
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

const dialog = ref(null)
const dragY = ref(0)
const dragging = ref(false)
const closing = ref(false)
let startY = 0

const DISMISS_AT = 96

watch(
  () => props.modelValue,
  (open) => {
    const el = dialog.value
    if (!el) return
    if (open) {
      dragY.value = 0
      closing.value = false
      el.showModal()
      document.documentElement.style.overflow = 'hidden'
    } else if (el.open) {
      runClose()
    }
  },
)

function runClose() {
  const el = dialog.value
  if (!el?.open) return
  closing.value = true
  const finish = () => {
    el.close()
    closing.value = false
    dragY.value = 0
    document.documentElement.style.overflow = ''
  }
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches
  if (reduce) finish()
  else setTimeout(finish, 220)
}

/** Escape / backdrop / the platform closing it behind our back */
function onCancel(e) {
  e.preventDefault()
  emit('update:modelValue', false)
}
function onBackdropPointer(e) {
  if (e.target === dialog.value) emit('update:modelValue', false)
}

function onGrabStart(e) {
  dragging.value = true
  startY = e.clientY
  e.currentTarget.setPointerCapture(e.pointerId)
}
function onGrabMove(e) {
  if (!dragging.value) return
  const delta = e.clientY - startY
  dragY.value = delta > 0 ? delta : delta / 4
}
function onGrabEnd() {
  if (!dragging.value) return
  dragging.value = false
  if (dragY.value > DISMISS_AT) emit('update:modelValue', false)
  else dragY.value = 0
}

onBeforeUnmount(() => {
  document.documentElement.style.overflow = ''
})
</script>

<template>
  <dialog
    ref="dialog"
    class="app-sheet"
    :class="{ 'is-closing': closing }"
    :aria-label="title"
    @cancel="onCancel"
    @pointerdown="onBackdropPointer"
  >
    <div
      class="app-sheet__panel"
      :style="{
        transform: dragY ? `translateY(${dragY}px)` : null,
        transition: dragging ? 'none' : null,
      }"
    >
      <!-- grab handle: the whole header area is draggable, as on iOS -->
      <div
        class="cursor-grab touch-none px-6 pt-3 pb-1 active:cursor-grabbing"
        @pointerdown="onGrabStart"
        @pointermove="onGrabMove"
        @pointerup="onGrabEnd"
        @pointercancel="onGrabEnd"
      >
        <span class="mx-auto block h-1.5 w-11 rounded-full bg-steel/70" aria-hidden="true" />
        <div class="mt-4 flex items-start justify-between gap-4">
          <div>
            <h2 class="font-display text-2xl font-black text-cream">{{ title }}</h2>
            <p v-if="subtitle" class="mt-1 text-[0.9rem] text-cream-3">{{ subtitle }}</p>
          </div>
          <button
            type="button"
            class="-me-1 grid size-9 shrink-0 place-items-center rounded-full border border-navy-3 text-cream-2 transition-transform active:scale-90"
            :aria-label="t.common.close"
            @click="emit('update:modelValue', false)"
          >
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
              <path d="m1 1 12 12M13 1 1 13" />
            </svg>
          </button>
        </div>
      </div>

      <div class="overscroll-contain px-6 pt-3 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
        <slot />
      </div>
    </div>
  </dialog>
</template>
