<template>
  <teleport to="body">
    <div v-if="visible"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60
                backdrop-blur-sm p-4 sm:p-6"
         @click.self="$emit('close')">
      <div class="bg-white w-full max-w-[95%] sm:max-w-[90%] md:max-w-[70%]
                  h-[85vh] rounded-xl shadow-2xl flex flex-col overflow-hidden">

        <div class="p-3 flex justify-between items-center border-b bg-gray-50 shrink-0">
          <h3 class="font-medium text-sm sm:text-base">
            {{ title || $t('contracts.preview') }}
          </h3>
          <button @click="$emit('close')" class="p-1 hover:text-red-600 text-xl">✕</button>
        </div>

        <object v-if="pdfUrl"
                :data="pdfUrl"
                :key="pdfUrl"     
                type="application/pdf"
                class="flex-1 w-full"
                style="min-height:0">
          <div class="h-full w-full flex flex-col items-center justify-center p-4 space-y-4">
            <p class="text-center text-gray-600 text-sm">{{ $t('common.noInlinePdf') }}</p>
            <a :href="pdfUrl" target="_blank" rel="noopener"
               class="button-primary px-4 py-2 rounded-md">
              {{ $t('common.openNewTab') }}
            </a>
          </div>
        </object>

        <div v-else class="flex-1 flex items-center justify-center">
          <svg class="animate-spin h-8 w-8 text-gray-400" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
        </div>

      </div>
    </div>
  </teleport>
</template>

<script setup lang="ts">
defineProps<{ visible:boolean; pdfUrl:string|null; title?:string }>()
defineEmits(['close'])
</script>
