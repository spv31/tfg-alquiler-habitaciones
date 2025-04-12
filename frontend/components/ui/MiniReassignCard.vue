<template>
  <div
    class="w-full rounded-xl bg-primary p-3 flex flex-col gap-2 transition-all duration-300 relative hover:border-gray-300 hover:shadow-lg group shadow-sm hover:-translate-y-0.5 transform-gpu active:scale-95 active:shadow-inner focus-within:ring-2 focus-within:ring-primary-100"
    :title="title"
  >
    <div class="flex items-center gap-4">
      <div
        class="w-12 h-12 flex-shrink-0 rounded bg-gray-100 flex items-center justify-center overflow-hidden"
      >
        <img
          v-if="cardImageUrl"
          :src="cardImageUrl"
          alt="Imagen"
          class="w-12 h-12 rounded object-cover"
        />
        <span v-else class="text-gray-400">
          <template v-if="type.startsWith('property')">
            <!-- Icono de casa -->
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
              />
            </svg>
          </template>
          <template v-else-if="type.startsWith('room')">
            <!-- Icono de cama -->
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 8h16M4 12h16M4 16h16"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </template>
          <template v-else-if="type === 'tenant'">
            <!-- Icono de usuario -->
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
              />
            </svg>
          </template>
        </span>
      </div>

      <div class="flex-1 space-y-1">
        <template v-if="type === 'tenant'">
          <p class="font-semibold text-gray-800">
            {{ (item as Tenant).name }}
          </p>
          <p class="text-sm text-gray-600">
            {{ (item as Tenant).email }}
          </p>
        </template>

        <template
          v-else-if="
            type === 'property' ||
            type === 'property-orig' ||
            type === 'property-info'
          "
        >
          <p class="font-semibold text-gray-800">
            {{ (item as Property).address }}
          </p>
          <p class="text-sm text-gray-600 line-clamp-2">
            {{ (item as Property).description }}
          </p>
        </template>

        <template v-else-if="type === 'room' || type === 'room-orig'">
          <p class="font-semibold text-gray-800">
            Habitación #{{ (item as Room).room_number }}
          </p>
          <p class="text-sm text-gray-600 line-clamp-2">
            {{ (item as Room).description }}
          </p>
        </template>

        <template v-else>
          <p class="font-semibold text-gray-800">Elemento desconocido</p>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePropertiesStore } from "~/store/properties";
import type { Tenant } from "~/types/tenant";
import type { Property } from "~/types/property";
import type { Room } from "~/types/room";

const props = defineProps<{
  item: Tenant | Property | Room;
  type:
    | "tenant"
    | "property"
    | "property-orig"
    | "property-info"
    | "room"
    | "room-orig";
  title?: string;
}>();

const propertiesStore = usePropertiesStore();

const isProperty = computed(() => props.type.startsWith("property"));
const isRoom = computed(() => props.type.startsWith("room"));
const isTenant = computed(() => props.type === "tenant");

const cardImageUrl = ref<string | null>(null);

onMounted(async () => {
  if (isTenant.value) return;

  // Load property image
  if (isProperty.value) {
    const property = props.item as Property;
    if (property.main_image_url) {
      const filename = property.main_image_url.split("/").pop() || "";
      cardImageUrl.value = await propertiesStore.fetchPropertyImageUrl(
        property.id,
        filename
      );
    }
    return;
  }

  // Load room image
  if (isRoom.value) {
    const room = props.item as Room;
    if (room.main_image_url) {
      const filename = room.main_image_url.split("/").pop() || "";
      cardImageUrl.value = await propertiesStore.fetchRoomImageUrl(
        room.property_id,
        room.id,
        filename
      );
    }
  }
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
