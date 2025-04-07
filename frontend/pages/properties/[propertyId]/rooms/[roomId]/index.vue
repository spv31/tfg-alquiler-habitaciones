<template>
  <div class="min-h-screen">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="alertMessage" class="mb-4 mx-auto">
        <Alert
          :message="alertMessage"
          :type="alertType"
          @close="alertMessage = ''"
        />
      </div>

      <!-- Loader -->
      <div
        v-if="loading"
        class="flex flex-col items-center justify-center my-20"
      >
        <div
          class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600 mb-4"
        ></div>
        <p class="text-blue-600 font-medium animate-pulse">
          {{ $t("properties.detail.loading") }}
        </p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center my-20">
        <div
          class="inline-flex items-center bg-red-50 px-6 py-4 rounded-lg border border-red-200"
        >
          <svg
            class="h-6 w-6 text-red-600 mr-3"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464
                 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <p class="text-red-600 font-medium">
            {{ $t("properties.detail.error") }} {{ error }}
          </p>
        </div>
      </div>

      <div v-else-if="currentRoom" class="space-y-8">
        <RoomDetailCard 
          :room="currentRoom" 
          :roomImage="roomImage"
        />

        <TenantFormSection
        />
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { usePropertiesStore } from "~/store/properties";

const propertiesStore = usePropertiesStore();
const { t } = useI18n();
const route = useRoute();

const { currentProperty, currentRoom, currentTenant, loading, error } = storeToRefs(propertiesStore);

const roomImage = ref<string | null>(null);
const successMessage = ref("");
const showForm = ref(false);

const propertyId = Number(route.params.propertyId);
const roomId = Number(route.params.roomId);

onMounted(async () => {
  try {
    await propertiesStore.fetchRoom(propertyId, roomId);
    await propertiesStore.fetchRoomTenant(propertyId, roomId);
    
    if (currentRoom.value?.main_image_url) {
      const filename = currentRoom.value.main_image_url.split("/").pop() || "";
      roomImage.value = await propertiesStore.fetchRoomImageUrl(
        propertyId,
        roomId,
        filename
      );
    }
  } catch (e) {
    console.error("Error al cargar la habitación:", e);
  }
});

const toggleForm = () => {
  showForm.value = !showForm.value;
  if (showForm.value) {
    successMessage.value = "";
  }
};

const handleInvitationSent = (msg: string) => {
  successMessage.value = msg;
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