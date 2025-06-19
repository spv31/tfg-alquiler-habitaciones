<template>
  <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="alertMessage" class="mb-4 mx-auto">
      <Alert
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center my-20">
      <div
        class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600 mb-4"
      ></div>
      <p class="text-blue-600 font-medium animate-pulse">
        {{ $t("properties.detail.loading") }}
      </p>
    </div>

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

    <!-- Property -->
    <div v-else-if="currentProperty" class="space-y-8">
      <PropertyDetailCard
        :property="currentProperty"
        :propertyImage="propertyImage"
      />
      <!-- 
      <TenantFormSection v-if="currentProperty.rental_type === 'full'" />

      <RoomsSection
        v-if="currentProperty.rental_type === 'per_room' && roomsReady"
        :rooms="rooms"
        :propertyId="propertyId"
        :warning="roomsWarning"
      />

      <ExpensesSection :property-id="propertyId"/> -->
      <div class="space-y-8">
        <!-- Alquiler por habitaciones: Rooms arriba, gastos debajo -->
        <template
          v-if="currentProperty.rental_type === 'per_room' && roomsReady"
        >
          <RoomsSection
            :rooms="rooms"
            :propertyId="propertyId"
            :warning="roomsWarning"
          />
          <ExpensesSection :property-id="propertyId" />
        </template>

        <template v-else-if="currentProperty.rental_type === 'full'">
          <div class="grid gap-8 md:grid-cols-2">
            <div class="order-1 space-y-6 min-w-0">
              <ExpensesSection :property-id="propertyId" />
            </div>
            <div class="order-2">
              <TenantFormSection />
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { usePropertiesStore } from "~/store/properties";
import { useFlashToast } from "#imports";

const { t: $t, locale } = useI18n();
const { showFlash } = useFlashToast();
const propertiesStore = usePropertiesStore();
const { currentProperty, rooms, loading, error, roomsWarning } =
  storeToRefs(propertiesStore);
const route = useRoute();
const propertyId = Number(route.params.propertyId);

const alertMessage = ref("");
const alertType = ref<"error" | "success">("success");
const propertyImage = ref<string | null>(null);
const roomsReady = ref(false);

onMounted(async () => {
  try {
    showFlash();

    await propertiesStore.fetchProperty(propertyId);
    if (currentProperty.value?.rental_type === "per_room") {
      await propertiesStore.fetchRooms(propertyId);
      roomsReady.value = true;
    } else {
      roomsReady.value = true;
    }

    if (currentProperty.value?.main_image_url) {
      const filename =
        currentProperty.value?.main_image_url.split("/").pop() || "";
      propertyImage.value = await propertiesStore.fetchPropertyImageUrl(
        propertyId,
        filename
      );
    }
  } catch (e) {
    console.error("Error al obtener la propiedad:", e);
  }
});

const goToChat = () => {
  console.log("Ir al chat con el inquilino");
};

const addStats = () => {
  const lang = locale.value;
  const url = `/${lang}/properties/${propertyId}/details`;
  navigateTo(url);
};

const toggleStatus = () => {};

const toggleStatusLabel = computed(() => {
  if (currentProperty.value?.status === "available") {
    return $t("properties.detail.makeUnavailableButton");
  } else {
    return $t("properties.detail.makeAvailableButton");
  }
});

const addRoom = () => {
  const lang = locale.value;
  const url = `/${lang}/properties/${propertyId}/rooms/add`;
  navigateTo(url);
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
