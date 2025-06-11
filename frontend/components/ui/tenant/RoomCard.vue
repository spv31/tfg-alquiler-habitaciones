<template>
  <div class="grid gap-6 grid-cols-1 lg:grid-cols-6 px-6 pb-6 pt-2">
    <div class="col-span-1 lg:col-span-2 flex justify-center">
      <div
        class="relative group overflow-hidden rounded-lg aspect-square min-h-[300px] w-full max-w-md"
      >
        <div v-if="loadingImage" class="absolute inset-0 bg-gray-100 animate-pulse"></div>

        <div v-else-if="!room.main_image_url" class="h-full w-full flex flex-col items-center justify-center bg-gray-50">
          <i class="pi pi-image text-4xl text-gray-300 mb-3"></i>
          <span class="text-gray-400 text-sm">Imagen no disponible</span>
        </div>

        <img
          v-else
          :src="room.main_image_url"
          :alt="imageAltText"
          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        />
      </div>
    </div>

    <div class="col-span-1 lg:col-span-4 flex flex-col gap-4">
      <div class="flex flex-col gap-2 border-b pb-4">
        <div class="flex items-center gap-2">
          <i class="pi pi-map-marker text-red-600" style="font-size: 1.2rem"></i>
          <h2 class="text-2xl font-bold text-gray-900 leading-tight">
            {{ room.property?.address }}
          </h2>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <Tag value="'Hab. ' + room.room_number" class="text-xs font-semibold py-1 px-3 rounded-full" />
          <Tag value="Alquiler por habitación" severity="info" class="text-xs" />
        </div>
      </div>

      <div class="prose max-w-none text-gray-600">{{ room.description }}</div>

      <div
        class="flex flex-col sm:flex-row justify-between items-start gap-4 p-4 bg-gray-50 rounded-xl border border-info/10 shadow-sm"
      >
        <div class="flex items-start gap-4">
          <img
            :src="owner?.profile_image || defaultAvatar"
            class="w-14 h-14 rounded-full object-cover border-4 border-white shadow-md"
            :alt="owner?.name"
          />
          <div class="flex flex-col gap-1">
            <p class="text-gray-900 font-bold flex items-center gap-2">
              <i class="pi pi-user text-blue-600"></i>
              {{ owner?.name }}
              <Tag value="Propietario" severity="info" class="text-xs" />
            </p>
            <a :href="`mailto:${owner?.email}`" class="text-gray-700 hover:text-blue-700 text-sm flex items-center gap-2">
              <i class="pi pi-envelope text-blue-500"></i> {{ owner?.email }}
            </a>
            <p v-if="owner?.phone" class="text-gray-700 text-sm flex items-center gap-2">
              <i class="pi pi-phone text-blue-500"></i> {{ owner.phone }}
            </p>
          </div>
        </div>
      </div>

      <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3">
        <DataTile
          icon="pi-credit-card"
          label="Alquiler / mes"
          :value="room.rental_price.toLocaleString('es-ES',{ style:'currency', currency:'EUR'})"
        />
        <DataTile
          icon="pi-hashtag"
          label="Ref. catastral"
          :value="room.property?.cadastral_reference || '—'"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, ref, computed } from 'vue';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import defaultAvatar from '~/assets/images/default.jpg';
import type { Room } from '~/types/room';

const props = defineProps<{
  data: {
    type: 'Room';
    rentable: Room;
  };
}>();

const room = computed(() => props.data.rentable);
const owner = computed(() => room.value.owner);

const loadingImage = ref(false);
const imageAltText = computed(() => `Imagen de la habitación ${room.value.room_number}`);
</script>

<style scoped>
.prose {
  line-height: 1.6;
  transition: max-height 0.3s ease;
}

.pi {
  font-size: 0.9em;
}

.chip {
  transition: all 0.2s ease;
}

.hover\:shadow-xl {
  transition: box-shadow 0.3s ease;
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

::v-deep .chat-button.p-button {
  @apply bg-info/10 text-gray-600 border border-info/10 !important;
}

::v-deep .chat-button.p-button:hover {
  @apply bg-info/20 text-gray-800 border-info/10 !important;
}

::v-deep .chat-button.p-button:focus {
  @apply border-info/10 !important;
}
</style>
