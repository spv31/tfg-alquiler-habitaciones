<template>
  <teleport to="body">
    <div
      v-if="visible"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 sm:p-6"
      @click.self="$emit('close')"
    >
      <div
        class="bg-white w-full max-w-[95%] sm:max-w-[90%] md:max-w-[70%] h-[85vh] rounded-xl shadow-2xl flex flex-col overflow-hidden"
      >
        <div class="p-3 flex justify-between items-center border-b bg-gray-50 shrink-0">
          <h3 class="font-medium text-sm sm:text-base">
            {{ title || $t('contracts.preview') }}
          </h3>
          <button
            @click="$emit('close')"
            class="p-1 hover:text-red-600 text-xl"
          >
            ✕
          </button>
        </div>

        <object
          v-if="pdfUrl"
          :data="pdfUrl"
          type="application/pdf"
          class="flex-1 w-full"
          style="min-height: 0"
        >
          <div class="h-full w-full flex flex-col items-center justify-center p-4 space-y-4">
            <p class="text-center text-gray-600 text-sm">
              {{ $t("common.noInlinePdf") }}
            </p>
            <a
              :href="pdfUrl"
              target="_blank"
              rel="noopener"
              class="button-primary px-4 py-2 rounded-md"
            >
              {{ $t("common.openNewTab") }}
            </a>
          </div>
        </object>
      </div>
    </div>
  </teleport>
</template>

<script setup lang="ts">
const props = defineProps<{
  visible: boolean;
  pdfUrl: string;
  title?: string;
}>();

defineEmits(["close"]);
</script>
