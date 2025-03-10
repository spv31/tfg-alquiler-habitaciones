<template>
  <div class="flex flex-col items-center w-16">
    <button
      @click="$emit('click')"
      class="flex items-center justify-center w-12 h-12 bg-white rounded-full
             shadow-md hover:shadow-lg transition p-2"
    >
      <slot name="icon"></slot>
    </button>

    <span class="text-xs mt-2 font-medium text-center">
      {{ label }}
    </span>
  </div>
</template>


<script setup lang="ts">
const props = defineProps({
  label: {
    type: String,
    required: true,
  },
});
defineEmits(["click"]);
</script>

<style scoped>
</style>