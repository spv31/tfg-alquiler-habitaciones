<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 flex items-start justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[500px] max-w-lg mt-16 animate-fadeIn">
        <h2 class="text-xl font-bold text-gray-900">{{ $t("properties.detail.confirmDelete") }}</h2>
        <p class="text-sm text-gray-700 mt-2">
          {{ $t("properties.detail.deleteConfirmationMessage") }}
        </p>
        <div class="mt-6 flex justify-end gap-3">
          <button @click="cancel" class="button-secondary">
            {{ $t("common.cancel") }}
          </button>
          <button @click="confirm" class="button-primary bg-red-600 hover:bg-red-700">
            {{ $t("properties.detail.deleteButton") }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
const props = defineProps({
  show: Boolean
});

const emit = defineEmits(["confirm", "cancel"]);

const confirm = () => emit("confirm");
const cancel = () => emit("cancel");
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}
</style>
