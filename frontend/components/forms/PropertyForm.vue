<template>
  <div class="flex flex-col gap-4">
    <!-- Dirección -->
    <div class="flex flex-col">
      <label for="address" class="label">
        {{ $t("properties.address") }}
      </label>
      <input
        id="address"
        type="text"
        class="custom-input"
        v-model="localPropertyData.address"
        @input="validateAddress"
        :placeholder="$t('properties.address_placeholder')"
      />
      <p v-if="localErrors.address" class="error-message">
        {{ localErrors.address }}
      </p>
    </div>

    <!-- Referencia catastral -->
    <div class="flex flex-col">
      <label for="cadastral_reference" class="label">
        {{ $t("properties.cadastral_reference") }}
      </label>
      <input
        id="cadastral_reference"
        type="text"
        class="custom-input"
        v-model="localPropertyData.cadastral_reference"
        @input="validateCadastralReference"
        :placeholder="$t('properties.reference_placeholder')"
      />
      <p v-if="localErrors.cadastral_reference" class="error-message">
        {{ localErrors.cadastral_reference }}
      </p>
    </div>

    <!-- Descripción -->
    <div class="flex flex-col">
      <label for="description" class="label">
        {{ $t("properties.description") }}
      </label>
      <textarea
        id="description"
        rows="3"
        class="custom-input"
        v-model="localPropertyData.description"
        @input="validateDescription"
        :placeholder="$t('properties.description_placeholder')"
      ></textarea>
      <p v-if="localErrors.description" class="error-message">
        {{ localErrors.description }}
      </p>
    </div>

    <!-- Total de habitaciones -->
    <div class="flex flex-col">
      <label for="total_rooms" class="label">
        {{ $t("properties.total_rooms") }}
      </label>
      <input
        id="total_rooms"
        type="number"
        class="custom-input"
        v-model.number="localPropertyData.total_rooms"
        @input="validateTotalRooms"
        :placeholder="$t('properties.total_rooms_placeholder')"
      />
      <p v-if="localErrors.total_rooms" class="error-message">
        {{ localErrors.total_rooms }}
      </p>
    </div>

    <!-- Tipo de alquiler -->
    <div class="flex flex-col">
      <label class="label">
        {{ $t("properties.rental_type") }}
      </label>
      <div class="flex gap-4 items-center">
        <div class="flex flex-col">
          <input
            type="radio"
            id="rental_type_full"
            value="full"
            class="custom-radio"
            v-model="localPropertyData.rental_type"
          />
          <label for="rental_type_full" class="ml-1">
            {{ $t("properties.rental_type_full") }}
          </label>
        </div>
        <div class="flex flex-col">
          <input
            type="radio"
            id="rental_type_per_room"
            value="per_room"
            class="custom-radio"
            v-model="localPropertyData.rental_type"
          />
          <label for="rental_type_per_room" class="ml-1">
            {{ $t("properties.rental_type_per_room") }}
          </label>
        </div>
      </div>
      <p v-if="localErrors.rental_type" class="error-message">
        {{ localErrors.rental_type }}
      </p>
    </div>

    <!-- Imagen principal -->
    <div class="flex flex-col">
      <label for="main_image" class="label">
        {{ $t("properties.main_image") }}
      </label>
      <div class="relative">
        <!-- Input file REAL, oculto con .sr-only (o .hidden) -->
        <input
          id="main_image"
          type="file"
          accept="image/*"
          class="sr-only"
          @change="handleImageUpload"
        />

        <!-- Label estilizado como botón -->
        <label
          for="main_image"
          class="custom-input w-full cursor-pointer inline-block text-center"
        >
          {{ $t("properties.select_file") }}
        </label>

        <!-- Mostrar el nombre del archivo si se ha seleccionado -->
        <span
          v-if="localPropertyData.main_image"
          class="ml-2 text-sm text-gray-500"
        >
          {{ localPropertyData.main_image.name }}
        </span>
      </div>
      <p v-if="localErrors.main_image" class="error-message">
        {{ localErrors.main_image }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const { t: $t } = useI18n();

// Emitimos para sincronizar con el padre
const emits = defineEmits(["update:propertyData", "update:errors"]);

// Props que recibimos
const props = defineProps<{
  propertyData: Record<string, any>;
  errors: Record<string, any>;
}>();

// Computed reactivas para propertyData y errors
const localPropertyData = computed({
  get: () => props.propertyData,
  set: (val) => emits("update:propertyData", val),
});
const localErrors = computed({
  get: () => props.errors,
  set: (val) => emits("update:errors", val),
});

// Validaciones simples
function validateAddress() {
  localErrors.value.address =
    localPropertyData.value.address.length >= 5
      ? ""
      : $t("errors.address_short");
}

function validateCadastralReference() {
  localErrors.value.cadastral_reference =
    localPropertyData.value.cadastral_reference.trim() !== ""
      ? ""
      : $t("errors.cadastral_required");
}

function validateDescription() {
  localErrors.value.description =
    localPropertyData.value.description.length >= 10
      ? ""
      : $t("errors.description_short");
}

function validateTotalRooms() {
  localErrors.value.total_rooms =
    localPropertyData.value.total_rooms >= 1 ? "" : $t("errors.min_rooms");
}

function handleImageUpload(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) {
    localErrors.value.main_image = $t("errors.no_image_selected");
    return;
  }
  localErrors.value.main_image = "";
  localPropertyData.value.main_image = file;
}

function validateAll() {
  validateAddress();
  validateCadastralReference();
  validateDescription();
  validateTotalRooms();
}

defineExpose({ validateAll });
</script>
