<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="$emit('close')"
      >
        <div
          ref="modalRef"
          class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="titleId"
        >
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h2 :id="titleId" class="text-lg font-semibold text-gray-900">{{ title }}</h2>
            <button
              class="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100 transition-colors"
              @click="$emit('close')"
            >
              <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="px-6 py-4">
            <slot />
          </div>

          <div v-if="$slots.footer" class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onBeforeUnmount } from 'vue'

const props = defineProps<{
  open: boolean
  title: string
}>()

defineEmits<{
  close: []
}>()

const titleId = `modal-title-${Math.random().toString(36).slice(2)}`
const modalRef = ref<HTMLElement | null>(null)
let previousActiveElement: HTMLElement | null = null

function getFocusableElements(): HTMLElement[] {
  if (!modalRef.value) return []
  const selectors = [
    'button:not([disabled])',
    '[href]',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
  ]
  return Array.from(modalRef.value.querySelectorAll<HTMLElement>(selectors.join(',')))
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    return
  }
  if (e.key === 'Tab') {
    const focusable = getFocusableElements()
    if (focusable.length === 0) {
      e.preventDefault()
      return
    }
    const first = focusable[0]
    const last = focusable[focusable.length - 1]
    if (e.shiftKey) {
      if (document.activeElement === first) {
        e.preventDefault()
        last?.focus()
      }
    } else {
      if (document.activeElement === last) {
        e.preventDefault()
        first?.focus()
      }
    }
  }
}

watch(
  () => props.open,
  async (isOpen) => {
    if (isOpen) {
      previousActiveElement = document.activeElement as HTMLElement
      document.body.style.overflow = 'hidden'
      await nextTick()
      window.addEventListener('keydown', handleKeydown)
      const focusable = getFocusableElements()
      if (focusable.length > 0) {
        focusable[0]?.focus()
      }
    } else {
      document.body.style.overflow = ''
      window.removeEventListener('keydown', handleKeydown)
      previousActiveElement?.focus()
    }
  },
)

onBeforeUnmount(() => {
  document.body.style.overflow = ''
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-active > :nth-child(2),
.modal-leave-active > :nth-child(2) {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from > :nth-child(2),
.modal-leave-to > :nth-child(2) {
  transform: scale(0.95) translateY(8px);
  opacity: 0;
}
</style>
