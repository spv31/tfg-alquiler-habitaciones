
<template>
  <PdfViewer
    :src="url"
    :page-scale="1.25"
    class="w-full h-full overflow-auto bg-gray-100"
  />
</template>
<script setup lang="ts">
import PdfViewer from 'pdf-vue3'
import { GlobalWorkerOptions } from "pdfjs-dist";

import workerSrc from "pdfjs-dist/build/pdf.worker?url";
GlobalWorkerOptions.workerSrc = workerSrc;

defineProps<{ url: string }>();
</script>
