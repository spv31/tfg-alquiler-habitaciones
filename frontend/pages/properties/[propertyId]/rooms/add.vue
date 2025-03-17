<template>
  <div class="py-8 px-4">
    <div class="max-w-3xl mx-auto mb-8">
      <h2 class="text-2xl font-bold text-gray-900 text-center">
        {{ $t("properties.detail.rooms.addRoomTitle") }}
      </h2>
    </div>

    <!-- Form principal -->
    <form @submit.prevent="handleSubmit" class="card-form-two-columns">
      <RoomForm
        ref="roomFormRef"
        v-model:roomData="formData"
        v-model:errors="errors"
      />

      <div class="flex justify-end mt-4 gap-2">
        <button type="button" class="button-secondary" @click="handleGoBack">
          {{ $t("common.back") }}
        </button>
        <button type="submit" class="button-primary" :disabled="hasErrors">
          {{ $t("common.save") }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { usePropertiesStore } from "~/store/properties";
import type { Room } from "~/types/room";

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const store = usePropertiesStore();

// Identificador de la propiedad desde la URL (p.ej.: /properties/:id/rooms/add)
const propertyId = Number(route.params.propertyId);

// Objeto reactivo para el formulario
const formData = ref<Partial<Room>>({
  description: "",
  rental_price: 0,
  main_image: null,
});

// Errores de validación
const errors = ref<Record<string, string>>({});

// Referencia al <RoomForm> para invocar validateAll()
const roomFormRef = ref<InstanceType<typeof RoomForm> | null>(null);

// Indica si hay errores (para deshabilitar botón, etc.)
const hasErrors = computed(() => {
  return Object.values(errors.value).some((err) => err !== "");
});

// Crear habitación
async function handleSubmit() {
  // Validamos todos los campos
  roomFormRef.value?.validateAll();

  if (hasErrors.value) return;

  try {
    await store.createRoom(propertyId, formData.value);
    alert($t("properties.detail.rooms.room_created"));
    router.push(`/properties/${propertyId}`);
  } catch (err: any) {
    console.error("Error al crear habitación:", err);
    errors.value.description = err?.data?.message || $t("errors.generic_error");
  }
}

// Botón "Volver"
function handleGoBack() {
  router.go(-1);
}
</script>
