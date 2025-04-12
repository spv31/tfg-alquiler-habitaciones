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

const { t: $t, locale } = useI18n();
const route = useRoute();
const router = useRouter();
const store = usePropertiesStore();

const propertyId = Number(route.params.propertyId);

const formData = ref<Partial<Room>>({
  description: "",
  rental_price: 0,
  main_image: null,
});

const errors = ref<Record<string, string>>({});

const roomFormRef = ref<InstanceType<typeof RoomForm> | null>(null);

const hasErrors = computed(() => {
  return Object.values(errors.value).some((err) => err !== "");
});

async function handleSubmit() {
  roomFormRef.value?.validateAll();

  if (hasErrors.value) return;

  try {
    await store.createRoom(propertyId, formData.value);
    router.push(`/${locale.value}/properties/${propertyId}?msg=success`);
  } catch (err: any) {
    console.error("Error al crear habitación:", err);
    errors.value.description = err?.data?.message || $t("errors.generic_error");
  }
}

function handleGoBack() {
  router.go(-1);
}
</script>
