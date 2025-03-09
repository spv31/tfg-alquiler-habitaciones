<template>
  <div class="py-8 px-4">
    <div class="max-w-3xl mx-auto mb-8">
      <!-- Alert -->
      <div v-if="alertMessage" class="max-w-2xl mx-auto mb-5">
        <Alert
          v-if="alertMessage"
          :message="alertMessage"
          :type="alertType"
          @close="alertMessage = ''"
        />
      </div>

      <!-- Título según el paso -->
      <h1 class="text-3xl font-bold text-gray-900 text-center">
        {{
          step === 1
            ? $t("properties.add_title")
            : $t("properties.optional_title")
        }}
      </h1>
    </div>

    <!-- Form principal con tus clases Tailwind personalizadas -->
    <form @submit.prevent="handleSubmit" class="card-form-two-columns">
      <!-- Paso 1: Campos obligatorios -->
      <PropertyForm
        ref="propertyFormRef"
        v-if="step === 1"
        v-model:propertyData="propertyData"
        v-model:errors="errors"
      />

      <!-- Paso 2: Campos opcionales -->
      <PropertyDetailsForm
        v-else
        v-model:propertyData="propertyData"
        v-model:errors="errors"
        :optional-fields="optionalFields"
      />

      <!-- Botones de navegación -->
      <div class="flex justify-between mt-4">
        <!-- Botón "Volver" (solo step 2) -->
        <button
          v-if="step === 2"
          type="button"
          class="button-secondary"
          @click="prevStep"
        >
          {{ $t("common.back") }}
        </button>

        <!-- Botón "Continuar" (solo step 1) -->
        <button
          v-else
          type="button"
          class="button-primary"
          @click="nextStep"
          :disabled="hasErrors"
        >
          {{ $t("common.continue") }}
        </button>

        <!-- Botón "Guardar" (solo step 2) -->
        <button
          v-if="step === 2"
          type="submit"
          class="button-primary"
          :disabled="hasErrors"
        >
          {{ $t("properties.register_property") }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { usePropertiesStore } from "~/store/properties";

// Store e i18n
const store = usePropertiesStore();
const { t: $t } = useI18n();

// Control de pasos
const step = ref(1);

// Alertas
const alertMessage = ref<string | null>(null);
const alertType = ref<"error" | "success">("error");

// Datos principales de la propiedad
const propertyData = ref({
  address: "",
  cadastral_reference: "",
  description: "",
  total_rooms: 0,
  rental_type: "full",
  main_image: null,
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

// Objeto de errores
const errors = ref<Record<string, string>>({});

const propertyFormRef = ref<InstanceType<typeof PropertyForm> | null>(null);

// Campos opcionales (estadísticos)
const optionalFields = [
  {
    key: "purchase_price",
    label: $t("properties.purchase_price"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "mortgage_cost",
    label: $t("properties.mortgage_cost"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "purchase_taxes",
    label: $t("properties.purchase_taxes"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "renovation_cost",
    label: $t("properties.renovation_cost"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "furniture_cost",
    label: $t("properties.furniture_cost"),
    type: "number",
    placeholder: "0.00",
  },
  { key: "purchase_date", label: $t("properties.purchase_date"), type: "date" },
  {
    key: "estimated_value",
    label: $t("properties.estimated_value"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "annual_insurance_cost",
    label: $t("properties.annual_insurance_cost"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "annual_property_tax",
    label: $t("properties.annual_property_tax"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "annual_community_fees",
    label: $t("properties.annual_community_fees"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "annual_waste_tax",
    label: $t("properties.annual_waste_tax"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "income_tax_percentage",
    label: $t("properties.income_tax_percentage"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "annual_repair_percentage",
    label: $t("properties.annual_repair_percentage"),
    type: "number",
    placeholder: "0.00",
  },
  {
    key: "rental_price",
    label: $t("properties.rental_price"),
    type: "number",
    placeholder: "0.00",
  },
];

// Cálculo para saber si hay errores en el step actual
const hasErrors = computed(() => {
  if (step.value === 1) {
    // Solo valida errores de campos obligatorios
    return Object.keys(errors.value).some(
      (key) =>
        [
          "address",
          "cadastral_reference",
          "description",
          "total_rooms",
        ].includes(key) && errors.value[key] !== ""
    );
  }
  // Paso 2: valora todos los errores
  return Object.values(errors.value).some((err) => err !== "");
});

// Avanzar al paso 2
function nextStep() {
  propertyFormRef.value?.validateAll();

  // Si no hay errores, pasamos al step 2
  if (!hasErrors.value) {
    step.value = 2;
  }
}

// Volver al paso 1
function prevStep() {
  step.value = 1;
}

// Manejar el submit final
async function handleSubmit() {
  // Podrías validar campos opcionales aquí si quieres
  if (!hasErrors.value) {
    try {
      // Enviar al store
      await store.createProperty(propertyData.value);

      alertMessage.value = $t("properties.success_message");
      alertType.value = "success";

      // Reset
      propertyData.value = {
        address: "",
        cadastral_reference: "",
        description: "",
        total_rooms: 0,
        rental_type: "full",
        main_image: null,
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
    } catch (error: any) {
      console.error("Error al registrar la propiedad:", error);
      const errorsFromBackend = error?.data?.errors || {};
      Object.keys(errorsFromBackend).forEach((key) => {
        errors.value[key] = errorsFromBackend[key][0] || "Error desconocido";
      });

      alertMessage.value = $t("properties.error_message");
      alertType.value = "error";
    }
  }
}
</script>
