<template>
  <img
    v-if="!loading && src"
    :src="src"
    :alt="`Vista previa contrato #${contractId}`"
    class="object-contain w-full h-full"
  />

  <div
    v-else
    class="absolute inset-0 flex items-center justify-center bg-gray-50"
  >
    <svg class="animate-spin h-6 w-6 text-gray-400" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path  class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
    </svg>
  </div>
</template>

<script setup lang="ts">
import { useContractsStore } from '~/store/contracts'
import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist'
import workerSrc from 'pdfjs-dist/build/pdf.worker?url'

GlobalWorkerOptions.workerSrc = workerSrc

const props = defineProps<{ contractId: number }>()

const contractsStore = useContractsStore()

const src     = ref<string>('')
const loading = ref(true)

onMounted(async () => {
  try {
    const blobUrl = await contractsStore.fetchContractPdf(props.contractId)

    const pdf   = await getDocument(blobUrl).promise
    const page  = await pdf.getPage(1)
    const dpr   = window.devicePixelRatio || 1
    const scale = 1.3
    const vp    = page.getViewport({ scale })

    const canvas = document.createElement('canvas')
    const ctx    = canvas.getContext('2d')!

    canvas.width  = vp.width  * dpr
    canvas.height = vp.height * dpr
    canvas.style.width  = `${vp.width}px`
    canvas.style.height = `${vp.height}px`

    await page.render({
      canvasContext: ctx,
      viewport     : vp,
      transform    : dpr !== 1 ? [dpr,0,0,dpr,0,0] : undefined,
    }).promise

    src.value = canvas.toDataURL('image/png')
    URL.revokeObjectURL(blobUrl)
  } catch (e) {
    console.error('Error generando mini-preview contrato:', e)
  } finally {
    loading.value = false
  }
})
</script>
