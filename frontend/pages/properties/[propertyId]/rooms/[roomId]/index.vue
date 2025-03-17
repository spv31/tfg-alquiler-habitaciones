<template>
  <div class="min-h-screen">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loader -->
      <div v-if="loading" class="flex flex-col items-center justify-center my-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600 mb-4"></div>
        <p class="text-blue-600 font-medium animate-pulse">
          {{ $t("properties.detail.loading") }}
        </p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center my-20">
        <div class="inline-flex items-center bg-red-50 px-6 py-4 rounded-lg border border-red-200">
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
              d="M12 9v2m0 4h.01m-6.938 4h13.856
                 c1.54 0 2.502-1.667 1.732-3L13.732 4
                 c-.77-1.333-2.694-1.333-3.464 0L3.34 16
                 c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <p class="text-red-600 font-medium">
            {{ $t("properties.detail.error") }} {{ error }}
          </p>
        </div>
      </div>

      <!-- Room Detail -->
      <div v-else-if="currentRoom" class="space-y-8">
        <RoomDetailCard
          :room="currentRoom"
          :roomImage="roomImage"
          @status-changed="onStatusChanged"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n"
import { usePropertiesStore } from "~/store/properties"

const propertiesStore = usePropertiesStore()

const { t } = useI18n()
const route = useRoute()

const { currentRoom, loading, error } = storeToRefs(propertiesStore)
const { propertyId, roomId } = useRoute().params;

const roomImage = ref<string | null>(null);

onMounted(async () => {
  try {
    await propertiesStore.fetchRoom(propertyId, roomId);

    if (currentRoom.value?.main_image_url) {
      const filename = currentRoom.value.main_image_url.split("/").pop() || "";
      console.log('Filenameee: ', filename);
      roomImage.value = await propertiesStore.fetchRoomImageUrl(propertyId, roomId, filename);
      console.log(roomImage.value);
    }
  } catch (e) {
    console.error("Error al obtener la habitación:", e)
  }
})

const onStatusChanged = (newStatus: string) => {
  console.log("El estado de la habitación ha cambiado a:", newStatus)
}
</script>

<style scoped>
</style>
