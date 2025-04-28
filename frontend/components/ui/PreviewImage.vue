<template>
  <img
    v-if="!loadingLocal && previewSrc"
    :src="previewSrc"
    :alt="`Vista previa plantilla ${props.templateId}`"
    class="object-contain w-full h-full"
  />

  <div
    v-else
    class="absolute inset-0 flex items-center justify-center bg-gray-50"
  >
    <svg class="animate-spin h-6 w-6 text-gray-400" viewBox="0 0 24 24">
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8v8H4z"
      />
    </svg>
  </div>
</template>

<script setup lang="ts">
import { useContractsStore } from '~/store/contracts';
import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist';
import workerSrc from 'pdfjs-dist/build/pdf.worker?url';

GlobalWorkerOptions.workerSrc = workerSrc;

const props = defineProps<{ templateId: number }>();
const contractsStore = useContractsStore();

const previewSrc = ref<string>('');
const loadingLocal = ref(true);

onMounted(async () => {
  try {
    const blobUrl = await contractsStore.fetchContractTemplatePdf(props.templateId);

    const pdf  = await getDocument(blobUrl).promise;
    const page = await pdf.getPage(1);

    const scale = 1.3;                                 
    const dpr   = window.devicePixelRatio || 1;
    const viewport = page.getViewport({ scale });

    const canvas  = document.createElement('canvas');
    const context = canvas.getContext('2d')!;

    canvas.width  = viewport.width  * dpr;
    canvas.height = viewport.height * dpr;
    canvas.style.width  = `${viewport.width}px`;
    canvas.style.height = `${viewport.height}px`;

    await page.render({
      canvasContext: context,
      viewport,
      transform: dpr !== 1 ? [dpr, 0, 0, dpr, 0, 0] : undefined
    }).promise;

    previewSrc.value = canvas.toDataURL('image/png');
  } catch (err) {
    console.error('Error generando mini-preview:', err);
  } finally {
    loadingLocal.value = false;
  }
})
</script>
