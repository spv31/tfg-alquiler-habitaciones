<template>
  <div class="py-8 px-4">
    <div class="max-w-3xl mx-auto mb-8">
      <h2 class="text-2xl font-bold text-gray-900 text-center">
        {{ $t("properties.detail.rooms.editRoomTitle") }}
      </h2>
    </div>

    <div v-if="loading" class="flex justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
    </div>

    <div v-else-if="error" class="text-center text-red-600">
      {{ $t("properties.detail.rooms.errorLoadingRoom") }}
    </div>

    <form v-else @submit.prevent="handleSubmit" class="card-form-two-columns">
      <RoomForm
        ref="roomFormRef"
        v-model:roomData="formData"
        v-model:errors="errors"
      />

      <div class="flex justify-between mt-4">
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

const propertyId = Number(route.params.propertyId);
const roomId = Number(route.params.roomId);

const loading = ref(true);
const error = ref<string | null>(null);
const formData = ref<Partial<Room>>({
  description: "",
  rental_price: 0,
  main_image: null,
});
const errors = ref<Record<string, string>>({});
const roomFormRef = ref<InstanceType<typeof RoomForm> | null>(null);

const hasErrors = computed(() => Object.values(errors.value).some((err) => err !== ""));

onMounted(async () => {
  try {
    if (!propertyId || !roomId) throw new Error("IDs no válidos");

    const room = await store.fetchRoom(propertyId, roomId);
    if (room) formData.value = { ...room };
  } catch (err) {
    console.error("Error al cargar la habitación:", err);
    error.value = $t("properties.detail.rooms.errorLoadingRoom");
  } finally {
    loading.value = false;
  }
});

const handleSubmit = async () => {
  roomFormRef.value?.validateAll();
  if (hasErrors.value) return;

  try {
    await store.updateRoom(propertyId, roomId, formData.value);
    alert($t("properties.detail.rooms.roomUpdated"));
    router.push(`/properties/${propertyId}`);
  } catch (err: any) {
    console.error("Error al actualizar la habitación:", err);
    errors.value.description = err?.data?.message || $t("errors.generic_error");
  }
};

const handleGoBack = () => router.go(-1);
</script>
