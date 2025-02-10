<template>
  <div class="min-h-screen py-10 bg-gray-900 text-white">
    <div class="px-2">

      <div class="max-w-3xl mx-auto p-6 bg-gray-800 rounded-lg shadow-lg">

        <Alert v-if="alertMessage" :message="alertMessage" :type="alertType" @close="alertMessage = ''" />

        <h1 class="text-2xl font-bold mb-6 text-center">
          {{ step === 1 ? $t("properties.add_title") : $t("properties.optional_title") }}
        </h1>

        <form @submit.prevent="handleSubmit" class="space-y-6">

          <div v-if="step === 1" class="space-y-4">
            <div class="flex flex-col">
              <label for="address" class="font-medium mb-1">{{ $t("properties.address") }}</label>
              <input id="address" type="text" v-model="propertyData.address"
                class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
                :placeholder="$t('properties.address_placeholder')" @input="validateAddress" />
              <p v-if="errors.address" class="text-red-400 font-medium text-sm">{{ errors.address }}</p>
            </div>

            <div class="flex flex-col">
              <label for="cadastral_reference" class="font-medium mb-1">{{ $t("properties.cadastral_reference")
                }}</label>
              <input id="cadastral_reference" type="text" v-model="propertyData.cadastral_reference"
                class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
                :placeholder="$t('properties.reference_placeholder')" @input="validateCadastralReference" />
              <p v-if="errors.cadastral_reference" class="text-red-400 font-medium text-sm">{{
                errors.cadastral_reference
                }}</p>
            </div>

            <div class="flex flex-col">
              <label for="description" class="font-medium mb-1">{{ $t("properties.description") }}</label>
              <textarea id="description" v-model="propertyData.description" rows="3"
                class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
                :placeholder="$t('properties.description_placeholder')" @input="validateDescription"></textarea>
              <p v-if="errors.description" class="text-red-400 font-medium text-sm">{{ errors.description }}</p>
            </div>

            <div class="flex flex-col">
              <label for="total_rooms" class="font-medium mb-1">{{ $t("properties.total_rooms") }}</label>
              <input id="total_rooms" type="number" v-model.number="propertyData.total_rooms"
                class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
                :placeholder="$t('properties.total_rooms_placeholder')" @input="validateTotalRooms" />
              <p v-if="errors.total_rooms" class="text-red-400 font-medium text-sm">{{ errors.total_rooms }}</p>
            </div>

            <div class="flex flex-col">
              <label class="font-medium mb-1">{{ $t("properties.rental_type") }}</label>
              <div class="flex space-x-4">
                <div class="flex items-center">
                  <input type="radio" id="rental_type_full" v-model="propertyData.rental_type" value="full"
                    class="w-4 h-4 text-sky-500 focus:ring-sky-500" />
                  <label for="rental_type_full" class="ml-2">{{ $t("properties.rental_type_full") }}</label>
                </div>
                <div class="flex items-center">
                  <input type="radio" id="rental_type_per_room" v-model="propertyData.rental_type" value="per_room"
                    class="w-4 h-4 text-sky-500 focus:ring-sky-500" />
                  <label for="rental_type_per_room" class="ml-2">{{ $t("properties.rental_type_per_room") }}</label>
                </div>
              </div>
              <p v-if="errors.rental_type" class="text-red-400 font-medium text-sm">{{ errors.rental_type }}</p>
            </div>



            <div class="flex justify-between mt-6">
              <button @click="nextStep"
                class="px-6 py-3 bg-sky-600 rounded hover:bg-sky-500 transition disabled:bg-gray-500"
                :disabled="hasErrors">
                {{ $t("common.continue") }}
              </button>
            </div>
          </div>

          <div v-if="step === 2">

            <div class="flex mb-6">
              <div class="flex items-center">
                <input id="is_financed" type="checkbox" v-model="propertyData.is_financed"
                  class="w-6 h-6 bg-gray-800 text-sky-500 rounded mr-2" />
                <label for="is_financed" class="font-medium mb-1">{{ $t("properties.is_financed") }}</label>

              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div v-for="field in optionalFields" :key="field.key" class="flex flex-col">
                <label :for="field.key" class="font-medium mb-1">{{ field.label }}</label>

                <input v-if="field.type !== 'checkbox'" :id="field.key" v-model="propertyData[field.key]"
                  :type="field.type" class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
                  :placeholder="field.placeholder" @input="validateField(field.key, field.type)" />

                <input v-else type="checkbox" v-model="propertyData[field.key]"
                  class="w-6 h-6 bg-gray-800 text-sky-500 rounded" />

                <p v-if="errors[field.key]" class="text-red-400 font-medium text-sm">{{ errors[field.key] }}</p>
              </div>
            </div>

            <div class="flex flex-col mt-6 items-center">
              <label for="property_size" class="font-medium mb-1 text-center">{{ $t("properties.property_size")
                }}</label>
              <input id="property_size" type="number" v-model="propertyData.property_size"
                class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500 w-full sm:w-1/2" placeholder=0.00
                @input="validateField('property_size', 'number')" />
              <p v-if="errors.property_size" class="text-red-400 font-medium text-sm text-center">{{
                errors.property_size }}</p>
            </div>

            <div class="flex justify-between mt-6">
              <button @click="prevStep" class="px-6 py-3 bg-gray-600 rounded hover:bg-gray-500 transition">
                {{ $t("common.back") }}
              </button>
              <button type="submit"
                class="px-6 py-3 bg-green-600 rounded hover:bg-green-500 transition disabled:bg-gray-500"
                :disabled="hasErrors">
                {{ $t("properties.register_property") }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePropertiesStore } from "~/store/properties";
import { useI18n } from "vue-i18n";

const store = usePropertiesStore();
const { t: $t } = useI18n();
const step = ref(1);
const alertMessage = ref<string | null>(null);
const alertType = ref<'error' | 'success'>('error');

const propertyData = ref({
  address: "",
  cadastral_reference: "",
  description: "",
  total_rooms: 0,
  rental_type: "full",
  purchase_price: null,
  is_financed: false,
  mortgage_cost: null,
  purchase_taxes: null,
  renovation_cost: null,
  furniture_cost: null,
  purchase_date: "",
  estimated_value: null,
  annual_insurance_cost: null,
  annual_property_tax: null,
  annual_community_fees: null,
  annual_waste_tax: null,
  income_tax_percentage: null,
  annual_repair_percentage: null,
  rental_price: null,
  property_size: null,
});

const errors = ref({});

const optionalFields = [
  { key: "purchase_price", label: $t("properties.purchase_price"), type: "number", placeholder: "0.00" },
  { key: "mortgage_cost", label: $t("properties.mortgage_cost"), type: "number", placeholder: "0.00" },
  { key: "purchase_taxes", label: $t("properties.purchase_taxes"), type: "number", placeholder: "0.00" },
  { key: "renovation_cost", label: $t("properties.renovation_cost"), type: "number", placeholder: "0.00" },
  { key: "furniture_cost", label: $t("properties.furniture_cost"), type: "number", placeholder: "0.00" },
  { key: "purchase_date", label: $t("properties.purchase_date"), type: "date" },
  { key: "estimated_value", label: $t("properties.estimated_value"), type: "number", placeholder: "0.00" },
  { key: "annual_insurance_cost", label: $t("properties.annual_insurance_cost"), type: "number", placeholder: "0.00" },
  { key: "annual_property_tax", label: $t("properties.annual_property_tax"), type: "number", placeholder: "0.00" },
  { key: "annual_community_fees", label: $t("properties.annual_community_fees"), type: "number", placeholder: "0.00" },
  { key: "annual_waste_tax", label: $t("properties.annual_waste_tax"), type: "number", placeholder: "0.00" },
  { key: "income_tax_percentage", label: $t("properties.income_tax_percentage"), type: "number", placeholder: "0.00" },
  { key: "annual_repair_percentage", label: $t("properties.annual_repair_percentage"), type: "number", placeholder: "0.00" },
  { key: "rental_price", label: $t("properties.rental_price"), type: "number", placeholder: "0.00" },
];

const validateAddress = () => {
  errors.value.address = propertyData.value.address.length >= 5 ? "" : $t("errors.address_short");
};

const validateCadastralReference = () => {
  errors.value.cadastral_reference = propertyData.value.cadastral_reference.trim() !== "" ? "" : $t("errors.cadastral_required");
};

const validateDescription = () => {
  errors.value.description = propertyData.value.description.length >= 10 ? "" : $t("errors.description_short");
};

const validateTotalRooms = () => {
  errors.value.total_rooms = propertyData.value.total_rooms >= 1 ? "" : $t("errors.min_rooms");
};

const validateField = (key: string, type: string) => {
  const value = propertyData.value[key];

  if (type === "number") {
    errors.value[key] =
      value === null || value === ""
        ? ""
        : !isNaN(Number(value)) && Number(value) > 0
          ? ""
          : $t("errors.invalid_number");
  } else if (type === "date") {
    errors.value[key] = value === "" || !isNaN(new Date(value).getTime()) ? "" : $t("errors.invalid_date");
  }
};

const hasErrors = computed(() => {
  if (step.value === 1) {
    // Solo valida errores de campos obligatorios en el paso 1
    return Object.keys(errors.value).some(
      (key) =>
        ["address", "cadastral_reference", "description", "total_rooms"].includes(key) &&
        errors.value[key] !== ""
    );
  }
  // Valida todos los errores en el paso 2
  return Object.values(errors.value).some((error) => error !== "");
});

const nextStep = () => {
  if (step.value === 1) {
    validateAddress();
    validateCadastralReference();
    validateDescription();
    validateTotalRooms();

    if (!hasErrors.value) {
      step.value++;
    }
  }
};

const prevStep = () => {
  step.value--;
};

const handleSubmit = async () => {
  optionalFields.forEach((field) => validateField(field.key, field.type));

  if (!hasErrors.value) {
    try {
      const createdProperty = await store.createProperty(propertyData.value);

      alertMessage.value = $t("properties.success_message");
      alertType.value = "success";

      propertyData.value = {
        address: "",
        cadastral_reference: "",
        description: "",
        total_rooms: 0,
        rental_type: "full",
        purchase_price: null,
        is_financed: false,
        mortgage_cost: null,
        purchase_taxes: null,
        renovation_cost: null,
        furniture_cost: null,
        purchase_date: "",
        estimated_value: null,
        annual_insurance_cost: null,
        annual_property_tax: null,
        annual_community_fees: null,
        annual_waste_tax: null,
        income_tax_percentage: null,
        annual_repair_percentage: null,
        rental_price: null,
        property_size: null,
      };
      step.value = 1;
    } catch (error) {
      console.error("Error al registrar la propiedad:", error);
      const errorsFromBackend = error?.data?.errors || {};
      Object.keys(errorsFromBackend).forEach((key) => {
        errors.value[key] = errorsFromBackend[key][0]; // Captura el primer mensaje de error
      });

      alertMessage.value = $t("properties.error_message");
      alertType.value = "error";
    }
  }
};

</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

input,
textarea {
  transition: all 0.3s ease-in-out;
  border: 2px solid transparent;
}

input:focus,
textarea:hover {
  outline: none;
  border-color: gray;
  box-shadow: 0 0 5px rgba(156, 163, 175, 0.5);
}

input:hover,
textarea:hover {
  border-color: rgb(209, 213, 219);
}
</style>