<template>
  <div class="mx-auto max-w-3xl py-10">
    <div class="bg-white p-6 shadow-lg rounded-xl space-y-6">
      <!-- Título centrado -->
      <h2 class="text-xl font-semibold text-gray-800 text-center">
        {{ $t("properties.detail.rooms.addRoomTitle") }}
      </h2>

      <!-- Descripción -->
      <div class="flex flex-col">
        <label for="description" class="font-medium text-gray-700 mb-1">
          {{ $t("properties.detail.rooms.description") }}
        </label>
        <textarea
          id="description"
          v-model="form.description"
          rows="3"
          class="p-2 bg-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          @input="validateDescription"
        ></textarea>
        <p v-if="errors.description" class="text-red-500 text-sm font-medium mt-1">
          {{ errors.description }}
        </p>
      </div>

      <!-- Precio de Alquiler -->
      <div class="flex flex-col">
        <label for="rental_price" class="font-medium text-gray-700 mb-1">
          {{ $t("properties.detail.rooms.rentalPrice") }}
        </label>
        <input
          id="rental_price"
          type="number"
          step="0.01"
          v-model="form.rental_price"
          class="p-2 bg-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          @input="validateRentalPrice"
        />
        <p v-if="errors.rental_price" class="text-red-500 text-sm font-medium mt-1">
          {{ errors.rental_price }}
        </p>
      </div>

      <!-- Imagen principal -->
      <div class="flex flex-col">
        <label for="room_image" class="font-medium text-gray-700 mb-1">
          {{ $t("properties.detail.rooms.mainImage") }}
        </label>
        <input
          id="room_image"
          type="file"
          accept="image/*"
          class="p-2 bg-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          @change="handleImageUpload"
        />
        <p v-if="errors.main_image" class="text-red-500 text-sm font-medium mt-1">
          {{ errors.main_image }}
        </p>
      </div>

      <!-- Botones -->
      <div class="flex justify-between mt-4">
        <button
          type="button"
          @click="goBack"
          class="px-6 py-2 bg-gray-400 text-white font-semibold rounded hover:bg-gray-500 transition"
        >
          {{ $t("common.back") }}
        </button>
        <button
          type="button"
          @click="handleSubmit"
          class="px-6 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed"
          :disabled="hasErrors"
        >
          {{ $t("properties.detail.rooms.saveRoomButton") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({
      description: '',
      rental_price: '',
      main_image: null
    })
  }
})

const emit = defineEmits(['submit', 'goBack'])
const { t: $t } = useI18n()

// Estado del formulario y errores
const form = ref({ ...props.initialData })
const errors = ref<Record<string, string>>({})

// Validaciones
function validateDescription() {
  if (!form.value.description || form.value.description.length < 3) {
    errors.value.description = $t('errors.description_short')
  } else {
    errors.value.description = ''
  }
}

function validateRentalPrice() {
  const price = Number(form.value.rental_price)
  if (isNaN(price) || price <= 0) {
    errors.value.rental_price = $t('errors.invalid_number')
  } else {
    errors.value.rental_price = ''
  }
}

function handleImageUpload(e: Event) {
  const target = e.target as HTMLInputElement
  if (!target.files?.length) return

  const file = target.files[0]
  form.value.main_image = file
  errors.value.main_image = ''
}

// Computed para deshabilitar el botón si hay errores
const hasErrors = computed(() => {
  return Object.values(errors.value).some(err => err !== '')
})

// Botón “Volver”
function goBack() {
  emit('goBack')
}

// Botón “Guardar”
function handleSubmit() {
  validateDescription()
  validateRentalPrice()

  if (!hasErrors.value) {
    emit('submit', form.value)
  }
}
</script>

<style scoped>
</style>
