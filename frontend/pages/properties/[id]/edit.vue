<template>
  <div class="py-8 px-4">
    <div class="max-w-3xl mx-auto mb-8">
      <!-- Alertas -->
      <div v-if="alertMessage" class="max-w-2xl mx-auto mb-5">
        <Alert
          v-if="alertMessage"
          :message="alertMessage"
          :type="alertType"
          @close="alertMessage = ''"
        />
      </div>

      <h1 class="text-3xl font-bold text-gray-900 text-center">
        {{
          step === 1
            ? $t("properties.edit_title")
            : $t("properties.optional_title")
        }}
      </h1>
    </div>

    <form @submit.prevent="handleSubmit" class="card-form-two-columns">
      <PropertyForm
        ref="propertyFormRef"
        v-if="step === 1"
        v-model:propertyData="propertyData"
        v-model:errors="errors"
      />

      <PropertyDetailsForm
        v-else
        v-model:propertyData="propertyData"
        v-model:errors="errors"
        :optional-fields="optionalFields"
      />

      <!-- Botones de navegación -->
      <div class="flex justify-between mt-4">

        <button
          v-if="step === 2"
          type="button"
          class="button-secondary"
          @click="prevStep"
        >
          {{ $t("common.back") }}
        </button>

        <button
          v-else
          type="button"
          class="button-primary"
          @click="nextStep"
          :disabled="hasErrors"
        >
          {{ $t("common.continue") }}
        </button>

        <!-- Botón "Guardar" (solo en el paso 2) -->
        <button
          v-if="step === 2"
          type="submit"
          class="button-primary"
          :disabled="hasErrors"
        >
          {{ $t("properties.save_changes") }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { usePropertiesStore } from "~/store/properties";

const { t: $t } = useI18n();
const route = useRoute();
const store = usePropertiesStore();

const { currentProperty } = storeToRefs(store);

const step = ref(1);

// Alertas
const alertMessage = ref<string | null>(null);
const alertType = ref<"error" | "success">("error");

// Objeto reactivo para datos de la propiedad
const propertyData = reactive({
  id: null,
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

const errors = ref<Record<string, string>>({});

const propertyFormRef = ref<InstanceType<typeof PropertyForm> | null>(null);

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
  {
    key: "purchase_date",
    label: $t("properties.purchase_date"),
    type: "date",
  },
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

const hasErrors = computed(() => {
  if (step.value === 1) {
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
  return Object.values(errors.value).some((err) => err !== "");
});

onMounted(async () => {
  const routePropertyId = Number(route.params.id);

  if (!routePropertyId) {
    alertMessage.value = $t("properties.error_loading_property");
    alertType.value = "error";
    return;
  }

  if (currentProperty.value && currentProperty.value.id === routePropertyId) {
    Object.assign(propertyData, currentProperty.value);
  } else {
    try {
      const fetched = await store.fetchProperty(routePropertyId);
      if (fetched) {
        Object.assign(propertyData, fetched.data);
        if (fetched.data.details) {
          console.log('Datos: ', fetched.data);
          console.log('Financiado: ', fetched.data.details.is_financed);
          Object.assign(propertyData, fetched.data.details);
          console.log('Datos copiados: ', propertyData.is_financed);
        }
      }
    } catch (err) {
      console.error("Error al cargar la propiedad:", err);
      alertMessage.value = $t("properties.error_loading_property");
      alertType.value = "error";
    }
  }
});

function nextStep() {
  propertyFormRef.value?.validateAll();
  if (!hasErrors.value) {
    step.value = 2;
  }
}

function prevStep() {
  step.value = 1;
}

async function handleSubmit() {
  if (!hasErrors.value) {
    try {
      await store.updateProperty(propertyData.id, propertyData);
      alertMessage.value = $t("properties.update_success_message");
      alertType.value = "success";
    } catch (error: any) {
      console.error("Error al actualizar la propiedad:", error);
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

<style scoped>
/* Tus estilos personalizados */
</style>
