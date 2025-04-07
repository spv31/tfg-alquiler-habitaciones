<template>
  <div class="flex flex-col gap-4">

    <div class="flex flex-col">
      <label for="description" class="label">
        {{ $t("properties.detail.rooms.description") }}
      </label>
      <textarea
        id="description"
        rows="3"
        class="custom-input"
        v-model="localRoomData.description"
        @input="validateDescription"
      ></textarea>
      <p v-if="localErrors.description" class="error-message">
        {{ localErrors.description }}
      </p>
    </div>

    <div class="flex flex-col">
      <label for="rental_price" class="label">
        {{ $t("properties.detail.rooms.rental_price") }}
      </label>
      <input
        id="rental_price"
        type="number"
        class="custom-input"
        v-model.number="localRoomData.rental_price"
        @input="validateRentalPrice"
      />
      <p v-if="localErrors.rental_price" class="error-message">
        {{ localErrors.rental_price }}
      </p>
    </div>

    <div class="flex flex-col">
      <label for="main_image" class="label">
        {{ $t("properties.main_image") }}
      </label>
      <div class="relative">
        <input
          id="main_image"
          type="file"
          accept="image/*"
          class="sr-only"
          @change="handleImageUpload"
        />
        <label
          for="main_image"
          class="custom-input w-full cursor-pointer inline-block text-center"
        >
          {{ $t("properties.select_file") }}
        </label>
        <span
          v-if="localRoomData.main_image && localRoomData.main_image.name"
          class="ml-2 text-sm text-gray-500"
        >
          {{ localRoomData.main_image.name }}
        </span>
      </div>
      <p v-if="localErrors.main_image" class="error-message">
        {{ localErrors.main_image }}
      </p>
    </div>

  </div>
</template>

<script setup lang="ts">
const { t: $t } = useI18n();

const emits = defineEmits(["update:roomData", "update:errors"]);

const props = defineProps<{
  roomData: Record<string, any>;
  errors: Record<string, any>;
}>();

const localRoomData = computed({
  get: () => props.roomData,
  set: (val) => emits("update:roomData", val),
});
const localErrors = computed({
  get: () => props.errors,
  set: (val) => emits("update:errors", val),
});

function validateDescription() {
  localErrors.value.description =
    !localRoomData.value.description ||
    localRoomData.value.description.trim().length < 3
      ? $t("errors.description_short")
      : "";
}

function validateRentalPrice() {
  const price = Number(localRoomData.value.rental_price);
  localErrors.value.rental_price =
    isNaN(price) || price <= 0 ? $t("errors.invalid_number") : "";
}

function handleImageUpload(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) {
    localErrors.value.main_image = $t("errors.no_image_selected");
    return;
  }
  localErrors.value.main_image = "";
  localRoomData.value.main_image = file;
}

function validateAll() {
  validateDescription();
  validateRentalPrice();
}

defineExpose({ validateAll });
</script>
