<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { waDefault } from '../data/site.js'
import { t } from '../i18n.js'

const show = ref(false)
const onScroll = () => (show.value = window.scrollY > 620)

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <Transition
    enter-active-class="transition-[opacity,transform] duration-500 ease-out-expo"
    enter-from-class="opacity-0 translate-y-4 scale-90"
    leave-active-class="transition-[opacity,transform] duration-300 ease-out-quart"
    leave-to-class="opacity-0 translate-y-4 scale-90"
  >
    <a
      v-show="show"
      :href="waDefault"
      target="_blank"
      rel="noopener"
      class="fixed bottom-5 end-5 z-40 hidden items-center gap-2.5 rounded-full bg-flame py-3.5 ps-5 pe-4 font-medium text-on-flame shadow-[0_18px_44px_-14px] shadow-flame/60 transition-[background-color,transform] duration-300 ease-out-quart hover:bg-flame-hi active:scale-95 lg:flex"
    >
      <span class="text-[0.95rem]">{{ t.common.orderNow }}</span>
      <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path
          d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.27.86 5.82 2.42a8.19 8.19 0 0 1 2.42 5.82c0 4.54-3.7 8.24-8.25 8.24a8.25 8.25 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.26-8.24m-3.51 3.66c-.16 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.21.89 2.39 1.01 2.55.12.17 1.72 2.62 4.16 3.68.58.25 1.03.4 1.39.51.58.19 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16s.2-1.06.14-1.16-.22-.16-.47-.28c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.25-.62.79-.76.95-.14.17-.28.19-.52.07-.25-.13-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.24-.01-.37.11-.49.11-.11.24-.29.37-.43s.17-.25.25-.41c.08-.17.04-.31-.02-.43s-.55-1.34-.76-1.83c-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.47-.02"
        />
      </svg>
      <span class="sr-only">{{ t.common.chatWhatsapp }}</span>
    </a>
  </Transition>
</template>
