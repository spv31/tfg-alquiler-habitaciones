<template>
  <div v-if="message" :class="alertClasses" class="p-3 rounded-lg flex items-center">
    <span class="flex-grow">{{ message }}</span>
    <button @click="closeAlert" class="text-gray-800 ml-3">&times;</button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  message: String,
  type: {
    type: String,
    default: 'error',
  },
});

const emit = defineEmits(['close']);

const closeAlert = () => {
  emit('close');
};

const alertClasses = computed(() => {
  return {
    'bg-red-300 font-medium text-red-900': props.type === 'error',
    'bg-green-300 font-medium text-green-900': props.type === 'success',
    'bg-yellow-300 font-medium text-yellow-900': props.type === 'warning',
    'bg-blue-300 font-medium text-blue-900': props.type === 'info',
  };
});
</script>
