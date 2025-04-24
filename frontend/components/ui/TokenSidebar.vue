<template>
  <aside class="space-y-1">
    <h3 class="font-semibold">Variables</h3>
    <button
      v-for="t in tokens"
      :key="t"
      class="text-blue-600 hover:underline block"
      @click="emit('insert', t)"
    >
      %{{ t }}%
    </button>
  </aside>
</template>
<script setup lang="ts">
const props = defineProps<{ tokens?: string[] }>();
const emit = defineEmits(["insert"]);
</script>

