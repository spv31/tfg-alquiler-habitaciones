<template>
  <teleport to="body">
    <div
      v-if="visible"
      class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex justify-center items-start pt-12"
      @click.self="$emit('close')"
    >
      <div class="bg-white w-11/12 md:w-3/4 h-[80vh] rounded-xl shadow-xl flex flex-col">
        <div class="flex justify-between items-center p-4 border-b bg-gray-50">
          <h3 class="font-semibold text-gray-700">{{ title }}</h3>
          <button @click="$emit('close')" class="text-gray-500 hover:text-red-600">
            ✕
          </button>
        </div>
        <client-only>
          <PdfViewer v-if="pdfUrl" :url="pdfUrl" class="flex-1 p-4" />
        </client-only>
      </div>
    </div>
  </teleport>
</template>

<script setup lang="ts">
defineProps<{
  visible: boolean;
  pdfUrl: string;
  title?: string;
}>();

defineEmits(["close"]);

const PdfViewer = defineAsyncComponent(() => import("~/components/ui/PdfViewer.vue"));
</script>
