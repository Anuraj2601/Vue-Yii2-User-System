<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  show: Boolean,
  title: {
    type: String,
    default: 'Are you sure?',
  },
  message: {
    type: String,
    default: 'Do you really want to delete this post? This action cannot be undone.',
  },
})

const emit = defineEmits(['confirm', 'close'])

function confirm() {
  emit('confirm')
}

function cancel() {
  emit('close')
}
</script>

<template>
  <TransitionRoot as="template" :show="show">
    <Dialog class="relative z-50" @close="cancel">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-white rounded-lg shadow-xl transform transition-all w-full max-w-md p-6 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100">
              <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 19c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z" />
              </svg>
            </div>

            <DialogTitle as="h3" class="text-lg font-medium text-gray-900 mt-4">
              {{ title }}
            </DialogTitle>

            <div class="mt-2">
              <p class="text-sm text-gray-500">
                {{ message }}
              </p>
            </div>

            <div class="mt-6 flex justify-center space-x-4">
              <button @click="confirm" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                {{ $t('common.yes') }} , {{ $t('common.delete') }}
              </button>
              <button @click="cancel" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm">
                {{ $t('common.cancel') }}
              </button>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>


