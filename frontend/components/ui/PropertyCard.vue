<template>
  <div
    class="relative group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 flex flex-col h-full"
  >
    <!-- Bloque absoluto para la “etiqueta” (estado) -->
    <div class="absolute top-4 right-4 z-10">
      <span
        :class="statusBadgeClasses(property.status)"
        class="px-3 py-1.5 text-xs font-semibold rounded-full uppercase tracking-wide shadow-sm"
      >
        {{ statusLabel(property.status, t) }}
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
        v-else-if="propertyImage"
        :src="propertyImage"
        :alt="`Imagen de ${property.address}`"
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
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2 
               l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01
               M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6
               a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
      </div>
    </div>

    <div class="p-6 flex-1">
      <h2 class="title-card">
        {{ property.address }}
      </h2>
      <p class="text-card">
        {{ property.description || "Sin descripción" }}
      </p>
    </div>

    <div class="px-6 pb-6">
      <div class="flex items-center justify-between text-sm">
        <span
          :class="[
            rentalBadgeClasses(property.rental_type),
            'px-3 py-1 text-xs font-semibold rounded-full uppercase tracking-wide',
          ]"
        >
          {{ rentalTypeLabel(property.rental_type, t) }}
        </span>

        <div class="flex items-center space-x-2">
          <span class="text-gray-500 font-medium">
            {{ property.total_rooms }} Habitaciones
          </span>
        </div>
      </div>

      <NuxtLink
        :to="`properties/${property.id}`"
        class="button-card mt-4 block text-center"
      >
        {{ $t("properties.detail.seeComplete") }}
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePropertiesStore } from "~/store/properties";
import {
  statusBadgeClasses,
  statusLabel,
  rentalBadgeClasses,
  rentalTypeLabel,
} from "~/utils/badges";

const props = defineProps({
  property: {
    type: Object,
    required: true,
  },
});

const { t } = useI18n();
const propertyImage = ref<string | null>(null);
const loadingImage = ref(false);
const errorLoadingImage = ref(false);
const propertiesStore = usePropertiesStore();

const failedImageUrls = new Set<string>();

const loadImage = async () => {
  if (!props.property.main_image_url) return;
  if (propertyImage.value) return;

  const filename = props.property.main_image_url.split("/").pop() || "";
  if (!filename) return;

  try {
    loadingImage.value = true;
    propertyImage.value = await propertiesStore.fetchPropertyImageUrl(
      props.property.id,
      filename
    );
  } catch (error) {
    console.error("Error loading image:", error);
  } finally {
    loadingImage.value = false;
  }
};

onMounted(loadImage);
watch(() => props.property, loadImage);
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>