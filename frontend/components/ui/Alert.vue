<template>
  <div v-if="message" :class="alertClasses" class="p-3 rounded-none flex items-center">
    <span class="flex-grow">{{ message }}</span>
    <button @click="closeAlert">
      <img src="@/assets/icons/close-icon.svg" class="w-6 h-6" alt="Icono Cerrar" />
    </button>
  </div>
</template>

<script setup>

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
    'bg-danger font-medium text-sm': props.type === 'error',
    'bg-green-300 font-medium text-sm text-green-900': props.type === 'success',
    'bg-yellow-300 font-medium text-sm text-yellow-900': props.type === 'warning',
    'bg-blue-300 font-medium text-sm text-blue-900': props.type === 'info',
  };
});
</script>
