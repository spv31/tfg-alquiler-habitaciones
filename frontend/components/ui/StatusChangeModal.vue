<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-md p-6 max-w-md w-full mx-4 shadow-lg">
      <h2 class="text-xl font-bold mb-4">
        {{ modalTitle }}
      </h2>
      <p class="mb-6">
        {{ modalMessage }}
      </p>
      <div class="flex justify-end space-x-2">
        <button
          @click="$emit('cancel')"
          class="button-secondary"
        >
          {{ $t("properties.detail.statusChangeModal.cancel") }}
        </button>
        <button
          @click="$emit('confirm')"
          class="button-primary"
        >
          {{ confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  currentStatus: {
    type: String, 
    required: true,
  },
});

const { t } = useI18n();

const modalTitle = computed(() => {
  return props.currentStatus === "available"
    ? t("properties.detail.statusChangeModal.titleAvailable")
    : t("properties.detail.statusChangeModal.titleUnavailable");
});

const modalMessage = computed(() => {
  return props.currentStatus === "available"
    ? t("properties.detail.statusChangeModal.messageAvailable")
    : t("properties.detail.statusChangeModal.messageUnavailable");
});

const confirmLabel = computed(() => {
  return props.currentStatus === "available"
    ? t("properties.detail.statusChangeModal.confirmAvailable")
    : t("properties.detail.statusChangeModal.confirmUnavailable");
});
</script>

<style scoped>
</style>
