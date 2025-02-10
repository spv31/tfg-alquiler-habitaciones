<template>
  <div v-if="message" :class="alertClasses" class="p-3 rounded-lg flex items-center">
    <span class="flex-grow">{{ message }}</span>
    <button @click="closeAlert" class="text-white ml-3">&times;</button>
  </div>
</template>

<script setup lang="ts">

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
    'bg-red-600 font-medium text-white': props.type === 'error',
    'bg-green-500 font-medium text-white': props.type === 'success',
    'bg-yellow-500 font-medium text-black': props.type === 'warning',
    'bg-blue-500 font-medium text-white': props.type === 'info',
  };
});
</script>
