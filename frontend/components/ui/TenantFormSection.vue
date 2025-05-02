<template>
  <div class="mt-8 w-full max-w-2xl mx-auto">
     <div v-if="currentTenant">
        <TenantCard :tenant="currentTenant" />
      </div>
    <div v-else class="p-6 bg-white/90 rounded-2xl shadow-lg border border-gray-100">
     

      <div class="text-center space-y-6">
        <div class="inline-block p-4 bg-info/10 rounded-full">
          <svg class="w-12 h-12 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
          </svg>
        </div>
        
        <h3 class="text-xl font-semibold text-gray-800">
          {{ $t("invitations.noTenantTitle") }}
        </h3>
        <p class="text-gray-600 max-w-md mx-auto">
          {{ $t("invitations.noTenantDescription") }}
        </p>
        
        <button 
          @click="toggleForm"
          class="button-primary px-8 py-3 rounded-xl flex items-center gap-2 mx-auto"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
          </svg>
          {{ $t("invitations.inviteTenantButton") }}
        </button>
      </div>

      <transition name="slide-fade">
        <div v-if="showForm" class="mt-8 pt-8 border-t border-gray-200">
          <TenantInvitationForm
            :property-id="propertyId"
            :room-id="roomId"
            @invitationSent="handleInvitationSent"
          />
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePropertiesStore } from "~/store/properties";
import type { Tenant } from "~/types/tenant";

const { t: $t } = useI18n();
const route = useRoute();
const propertiesStore = usePropertiesStore();
const { currentProperty, currentTenant, currentRoom } =
  storeToRefs(propertiesStore);

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
  const instance = getCurrentInstance();
  console.log(
    "TenantFormSection montado. uid:",
    instance?.uid,
    " route:",
    route.fullPath
  );
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
