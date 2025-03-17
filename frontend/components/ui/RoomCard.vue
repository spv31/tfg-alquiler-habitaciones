<template>
  <div
    class="relative group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition flex flex-col h-full"
  >
    <div class="absolute top-4 right-4 z-10">
      <span
        :class="statusBadgeClasses(room.status)"
        class="px-3 py-1.5 text-xs font-semibold rounded-full shadow-sm"
      >
        {{ statusLabel(room.status, t) }}
      </span>
    </div>

    <div class="h-48 gradient-card rounded-t-3xl overflow-hidden relative">
      <div
        v-if="loadingImage"
        class="absolute inset-0 flex items-center justify-center"
      >
        <div
          class="animate-spin rounded-full h-10 w-10 border-4 border-gray-300 border-t-gray-500"
        ></div>
      </div>

      <img
        v-else-if="roomImage"
        :src="roomImage"
        :alt="`Imagen de la habitación ${room.room_number}`"
        class="absolute inset-0 w-full h-full object-cover"
      />
      <div v-else class="absolute inset-0 flex items-center justify-center">
        <svg
          class="h-16 w-16 text-info/30"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
      </div>
    </div>

    <div class="p-6 flex-1">
      <h2 class="font-semibold text-lg text-gray-900 mb-2">
        {{ $t("properties.detail.room") }} {{ room.room_number }}
      </h2>
      <p class="text-sm text-gray-600">
        {{ room.description || $t("properties.detail.noDescription") }}
      </p>
    </div>

    <div class="px-6 pb-6">
      <div class="flex items-center justify-between text-sm mb-4">
        <span class="text-gray-800" v-html="formattedRentalPrice"></span>
      </div>

      <NuxtLink
        :to="`/${locale}/properties/${room.property_id}/rooms/${room.id}`"
        class="button-card mt-4 block text-center"
      >
        {{ $t("properties.detail.seeComplete") }}
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePropertiesStore } from "~/store/properties";

const { t, locale } = useI18n();
const route = useRoute();

const propertiesStore = usePropertiesStore();
const propertyId = Number(route.params.propertyId);

const props = defineProps<{ room: any }>();

const roomImage = ref<string | null>(null);
const loadingImage = ref(false);
const errorLoadingImage = ref(false);

const failedImageUrls = new Set<String>();

const loadImage = async () => {
  if (!props.room.main_image_url) return;
  if (roomImage.value) return;

  const filename = props.room.main_image_url.split("/").pop() || "";
  if (!filename) return;

  try {
    console.log("Filename: ", filename);
    loadingImage.value = true;
    roomImage.value = await propertiesStore.fetchRoomImageUrl(
      propertyId,
      props.room.id,
      filename
    );
  } catch (error) {
  } finally {
    loadingImage.value = false;
  }
};

const formattedRentalPrice = computed(() => {
  return t("properties.detail.rentalPriceText", {
    price: `<span class='font-bold'>${props.room.rental_price}€</span>`,
  });
});

onMounted(loadImage);
watch(() => props.room, loadImage);
</script>
