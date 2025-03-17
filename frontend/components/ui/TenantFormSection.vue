<template>
  <div class="mt-4 flex flex-col items-center">
    <button
      class="button-primary"
      @click="toggleForm"
    >
      <span v-if="!showForm">
        {{ $t("invitations.inviteTenantButton") }}
      </span>
      <span v-else>
        {{ $t("invitations.closeFormButton") }}
      </span>
    </button>

    <transition name="fade">
      <div v-if="showForm" class="w-full max-w-md mx-auto mt-4">
        <TenantInvitationForm
          :property-id="property.id"
          @invitationSent="handleInvitationSent"
        />
      </div>
    </transition>

    <div
      v-if="successMessage"
      class="mt-2 text-green-600 bg-green-100 p-3 rounded"
    >
      {{ successMessage }}
    </div>
  </div>
</template>

<script setup lang="ts">
import TenantInvitationForm from "../forms/TenantInvitationForm.vue";

const props = defineProps<{
  property: {
    id: number;
    [key: string]: any;
  };
}>();

const { t: $t } = useI18n();

const showForm = ref(false);
const successMessage = ref("");

const toggleForm = () => {
  showForm.value = !showForm.value;
  if (showForm.value) {
    successMessage.value = "";
  }
};

const handleInvitationSent = (message: string) => {
  successMessage.value = message;
  showForm.value = false;
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
