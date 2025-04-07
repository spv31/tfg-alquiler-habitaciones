<template>
  <div class="mt-4 flex flex-col items-center">
    <div v-if="currentTenant">
      <TenantCard v-if="currentTenant" :tenant="currentTenant" />
    </div>

    <div
      v-else-if="
        (roomId && currentRoom?.status === 'available') ||
        (!roomId && currentProperty.data.status === 'available')
      "
    >
      <button class="button-primary" @click="toggleForm">
        <span v-if="!showForm">
          {{ $t("invitations.inviteTenantButton") }}
        </span>
        <span v-else>
          {{ $t("invitations.closeFormButton") }}
        </span>
      </button>
    </div>

    <transition name="fade">
      <div v-if="showForm" class="w-full max-w-md mx-auto mt-4">
        <TenantInvitationForm
          :property-id="propertyId"
          :room-id="roomId"
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
import { usePropertiesStore } from "~/store/properties";
import type { Tenant } from "~/types/tenant";

const { t: $t } = useI18n();
const route = useRoute();
const propertiesStore = usePropertiesStore();
const { currentProperty, currentTenant, currentRoom } = storeToRefs(propertiesStore);

const showForm = ref(false);
const successMessage = ref("");

const propertyId = Number(route.params.propertyId);
const roomId = route.params.roomId ? Number(route.params.roomId) : null;

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

onMounted(async () => {
  try {
    if (roomId !== null && !isNaN(roomId)) {
    } else {
      await propertiesStore.fetchPropertyTenant(propertyId);
    }
    console.log("Inquilino: ", currentTenant.value);
  } catch (error) {
    console.error("Error fetching tenant:", error);
  }
});
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
