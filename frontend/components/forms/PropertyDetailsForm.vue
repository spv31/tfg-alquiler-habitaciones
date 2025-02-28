<template>
  <div class="mx-auto max-w-3xl py-10">
    <div class="bg-white p-6 shadow-lg rounded-xl space-y-6">
      <!-- Título centrado -->
      <h2 class="text-xl font-semibold text-gray-800 text-center">
        {{ $t("properties.optional_title") }}
      </h2>

      <!-- Checkbox: ¿Está financiado? -->
      <div class="flex items-center mb-4">
        <input
          id="is_financed"
          type="checkbox"
          v-model="form.is_financed"
          class="w-5 h-5 text-blue-600 bg-gray-200 rounded mr-2 focus:ring-2 focus:ring-blue-500"
        />
        <label for="is_financed" class="text-gray-800 font-medium">
          {{ $t("properties.is_financed") }}
        </label>
      </div>

      <!-- Campos opcionales (excepto property_size) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div
          v-for="field in filteredFields"
          :key="field.key"
          class="flex flex-col"
        >
          <label :for="field.key" class="font-medium text-gray-700 mb-1">
            {{ field.label }}
          </label>
          <!-- Inputs para type!==date -->
          <input
            v-if="field.type !== 'date'"
            :id="field.key"
            v-model="form[field.key]"
            :type="field.type"
            :placeholder="field.placeholder"
            class="p-2 bg-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @input="validateField(field.key, field.type)"
          />
          <!-- Inputs para type===date -->
          <input
            v-else
            :id="field.key"
            type="date"
            v-model="form[field.key]"
            class="p-2 bg-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @change="validateField(field.key, field.type)"
          />
          <!-- Errores -->
          <p
            v-if="errors[field.key]"
            class="text-red-500 text-sm font-medium mt-1"
          >
            {{ errors[field.key] }}
          </p>
        </div>
      </div>

      <!-- Campo property_size en una fila sola, centrado -->
      <div class="flex flex-col items-center">
        <label for="property_size" class="font-medium text-gray-700 mb-1">
          {{ $t("properties.property_size") }}
        </label>
        <input
          id="property_size"
          type="number"
          v-model="form.property_size"
          placeholder="0.00"
          class="p-2 bg-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-48 text-center"
          @input="validateField('property_size', 'number')"
        />
        <p v-if="errors.property_size" class="text-red-500 text-sm font-medium mt-1">
          {{ errors[property_size] }}
        </p>
      </div>

      <!-- Botones -->
      <div class="flex justify-between mt-4">
        <button
          @click="goBack"
          type="button"
          class="px-6 py-2 bg-gray-400 text-white font-semibold rounded hover:bg-gray-500 transition"
        >
          {{ $t("common.back") }}
        </button>
        <button
          @click="handleSubmit"
          class="px-6 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed"
          :disabled="hasErrors"
        >
          {{ $t("properties.register_property") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { usePropertiesStore } from "~/store/properties";

const route = useRoute()
const router = useRouter()
const { t: $t } = useI18n()

// El id de la propiedad desde la URL o donde corresponda
const propertyId = route.params.id

// El store de propiedades
const store = usePropertiesStore()

// Objeto con valores por defecto para evitar que aparezcan undefined
const defaultDetails = {
  is_financed: false,
  purchase_price: null,
  mortgage_cost: null,
  purchase_taxes: null,
  renovation_cost: null,
  furniture_cost: null,
  purchase_date: null,
  estimated_value: null,
  annual_insurance_cost: null,
  annual_property_tax: null,
  annual_community_fees: null,
  annual_waste_tax: null,
  income_tax_percentage: null,
  annual_repair_percentage: null,
  rental_price: null,
  property_size: null
}

// Estado del formulario y errores
const form = ref({ ...defaultDetails })
const errors = ref({})

// Lista de campos opcionales
const optionalFields = [
  { key: 'purchase_price', label: $t('properties.purchase_price'), type: 'number', placeholder: '0.00' },
  { key: 'mortgage_cost', label: $t('properties.mortgage_cost'), type: 'number', placeholder: '0.00' },
  { key: 'purchase_taxes', label: $t('properties.purchase_taxes'), type: 'number', placeholder: '0.00' },
  { key: 'renovation_cost', label: $t('properties.renovation_cost'), type: 'number', placeholder: '0.00' },
  { key: 'furniture_cost', label: $t('properties.furniture_cost'), type: 'number', placeholder: '0.00' },
  { key: 'purchase_date', label: $t('properties.purchase_date'), type: 'date' },
  { key: 'estimated_value', label: $t('properties.estimated_value'), type: 'number', placeholder: '0.00' },
  { key: 'annual_insurance_cost', label: $t('properties.annual_insurance_cost'), type: 'number', placeholder: '0.00' },
  { key: 'annual_property_tax', label: $t('properties.annual_property_tax'), type: 'number', placeholder: '0.00' },
  { key: 'annual_community_fees', label: $t('properties.annual_community_fees'), type: 'number', placeholder: '0.00' },
  { key: 'annual_waste_tax', label: $t('properties.annual_waste_tax'), type: 'number', placeholder: '0.00' },
  { key: 'income_tax_percentage', label: $t('properties.income_tax_percentage'), type: 'number', placeholder: '0.00' },
  { key: 'annual_repair_percentage', label: $t('properties.annual_repair_percentage'), type: 'number', placeholder: '0.00' },
  { key: 'rental_price', label: $t('properties.rental_price'), type: 'number', placeholder: '0.00' },
]

// Filtramos para no incluir 'property_size' en este grupo
const filteredFields = computed(() => {
  return optionalFields.filter(field => field.key !== 'property_size')
})

// Al montar, traemos la propiedad y cargamos sus details en el form
onMounted(async () => {
  try {
    await store.fetchProperty(propertyId)
    // Mezclamos lo que llegue (null o valores) con los defaults
    const details = store.currentProperty.value?.details || {}
    form.value = { ...defaultDetails, ...details }
  } catch (err) {
    console.error(err)
  }
})

/**
 * Validar un campo dependiendo de su tipo
 */
function validateField(key, type) {
  const value = form.value[key]
  if (!value) {
    errors.value[key] = ''
    return
  }
  if (type === 'number') {
    const num = Number(value)
    if (isNaN(num) || num < 0) {
      errors.value[key] = $t('errors.invalid_number')
    } else {
      errors.value[key] = ''
    }
  } else if (type === 'date') {
    const dateVal = new Date(value)
    if (isNaN(dateVal.getTime())) {
      errors.value[key] = $t('errors.invalid_date')
    } else {
      errors.value[key] = ''
    }
  }
}

/**
 * Volver a la vista anterior (o lo que necesites)
 */
function goBack() {
  router.push({ name: 'YourRouteName' })
}

/**
 * Guardar datos
 */
async function handleSubmit() {
  // Validamos cada campo
  filteredFields.value.forEach(field => validateField(field.key, field.type))
  validateField('property_size', 'number')

  const hasErrorsNow = Object.values(errors.value).some(err => err !== '')
  if (!hasErrorsNow) {
    try {
      // Llamamos a la acción del store para guardar los detalles en el backend
      await store.savePropertyDetails(propertyId, form.value)
      // Mensaje de éxito, navegación, etc.
    } catch (err) {
      console.error(err)
    }
  }
}

// Computed para deshabilitar el botón si hay errores
const hasErrors = computed(() => {
  return Object.values(errors.value).some(err => err !== '')
})
</script>
