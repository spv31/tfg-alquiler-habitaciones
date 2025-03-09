<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm px-4"
    @click.self="$emit('close')"
  >
    <!-- Modal contenedor más estrecho: max-w-lg -->
    <div
      class="bg-white rounded-3xl shadow-xl p-6 w-full max-w-lg relative max-h-[90vh] overflow-y-auto"
    >
      <!-- Boolean is_financed arriba, centrado -->
      <div class="text-center mb-2">
        <span
          v-if="details.is_financed"
          class="inline-block px-3 py-1.5 text-sm font-semibold text-green-700 bg-green-100 rounded-full uppercase tracking-wide"
        >
          {{ $t("properties.statuslabel.financed") }}
        </span>
        <span
          v-else
          class="inline-block px-3 py-1.5 text-sm font-semibold text-red-700 bg-red-100 rounded-full uppercase tracking-wide"
        >
          {{ $t("properties.statuslabel.unfinanced") }}
        </span>
      </div>

      <!-- Datos en 2 columnas -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- purchase_price -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.purchase_price") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.purchase_price ?? 0 }}
          </dd>
        </div>

        <!-- mortgage_cost -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.mortgage_cost") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.mortgage_cost ?? 0 }}
          </dd>
        </div>

        <!-- purchase_taxes -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.purchase_taxes") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.purchase_taxes ?? 0 }}
          </dd>
        </div>

        <!-- renovation_cost -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.renovation_cost") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.renovation_cost ?? 0 }}
          </dd>
        </div>

        <!-- furniture_cost -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.furniture_cost") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.furniture_cost ?? 0 }}
          </dd>
        </div>

        <!-- purchase_date -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.purchase_date") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.purchase_date || "N/A" }}
          </dd>
        </div>

        <!-- estimated_value -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.estimated_value") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.estimated_value ?? 0 }}
          </dd>
        </div>

        <!-- annual_insurance_cost -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.annual_insurance_cost") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.annual_insurance_cost ?? 0 }}
          </dd>
        </div>

        <!-- annual_property_tax -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.annual_property_tax") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.annual_property_tax ?? 0 }}
          </dd>
        </div>

        <!-- annual_community_fees -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.annual_community_fees") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.annual_community_fees ?? 0 }}
          </dd>
        </div>

        <!-- annual_waste_tax -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.annual_waste_tax") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.annual_waste_tax ?? 0 }}
          </dd>
        </div>

        <!-- income_tax_percentage -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.income_tax_percentage") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.income_tax_percentage ?? 0 }}%
          </dd>
        </div>

        <!-- annual_repair_percentage -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.annual_repair_percentage") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.annual_repair_percentage ?? 0 }}%
          </dd>
        </div>

        <!-- rental_price -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.rental_price") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.rental_price ?? 0 }}
          </dd>
        </div>
        
        <!-- property_size -->
        <div>
          <dt class="text-sm font-medium text-gray-500">
            {{ $t("properties.property_size") }}
          </dt>
          <dd class="mt-1 text-gray-900 font-medium">
            {{ details.property_size ?? 0 }}
          </dd>
        </div>
      </div>

      <!-- Botones: volver atrás y editar -->
      <div
        class="flex flex-wrap justify-end gap-4 pt-4 mt-4 border-t border-gray-200"
      >
        <!-- Botón Volver (CircleIconButton) -->
        <CircleIconButton :label="$t('properties.detail.cancelButton')" @click="$emit('close')">
          <template #icon>
            <svg
              class="h-5 w-5 text-gray-700 mx-auto"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 19l-7-7 7-7"
              />
            </svg>
          </template>
        </CircleIconButton>

        <!-- Botón Editar (CircleIconButton) -->
        <CircleIconButton :label="$t('properties.detail.editButton')" @click="editDetails">
          <template #icon>
            <svg
              class="h-5 w-5 text-blue-600 mx-auto"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.232 5.232l3.536
                 3.536m-2.036-5.036a2.5 2.5
                 0 113.536 3.536L6.5 21.036H3
                 v-3.572L16.732 3.732z"
              />
            </svg>
          </template>
        </CircleIconButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Tipado de datos
interface PropertyDetail {
  property_id: number;
  purchase_price: number | null;
  is_financed: boolean;
  mortgage_cost: number | null;
  purchase_taxes: number | null;
  renovation_cost: number | null;
  furniture_cost: number | null;
  purchase_date: string | null;
  estimated_value: number | null;
  annual_insurance_cost: number | null;
  annual_property_tax: number | null;
  annual_community_fees: number | null;
  annual_waste_tax: number | null;
  income_tax_percentage: number | null;
  annual_repair_percentage: number | null;
  rental_price: number | null;
  property_size: number | null;
}

const props = defineProps<{
  details: PropertyDetail;
}>();

function goBack() {
  console.log("Volver atrás");
}

function editDetails() {
  console.log("Editar detalles");
}
</script>

<style scoped>
div[role="dialog"] {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>