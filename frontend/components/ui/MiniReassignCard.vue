<template>
  <div
    class="w-full border rounded-lg p-3 flex flex-col gap-2 transition relative"
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
        <span v-else class="text-gray-400 text-xl">
          <template v-if="type.startsWith('property')">🏠</template>
          <template v-else-if="type.startsWith('room')">🛏️</template>
          <template v-else-if="type === 'tenant'">🧑</template>
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
