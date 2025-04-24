<template>
  <button
    :disabled="disabled"
    :title="tooltip"
    :class="[
      'rounded p-1 hover:bg-gray-200 transition',
      active ? 'bg-blue-100 text-blue-600' : ''
    ]"
  >
    <slot />
  </button>
</template>
<script setup lang="ts">
defineProps<{ disabled?: boolean; active?: boolean; tooltip?: string }>();
</script>
