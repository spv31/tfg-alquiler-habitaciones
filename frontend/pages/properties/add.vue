<template>
  <div class="min-h-screen py-10 bg-gray-900 text-white">
    <div class="max-w-3xl mx-auto p-6 bg-gray-800 rounded-lg shadow-lg">
      <h1 class="text-2xl font-bold mb-6 text-center">
        {{ step === 1 ? $t('properties.add_title') : step === 2 ? $t('properties.add_rooms') : $t('properties.optional_stats') }}
      </h1>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Paso 1: Datos básicos -->
        <div v-if="step === 1" class="space-y-4">
          <div class="flex flex-col">
            <label for="address" class="font-medium mb-1">{{ $t('properties.address') }}</label>
            <input
              id="address"
              type="text"
              v-model="propertyData.address"
              class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
              placeholder="{{ $t('properties.address_placeholder') }}"
            />
          </div>

          <div class="flex flex-col">
            <label for="cadastral_reference" class="font-medium mb-1">{{ $t('properties.cadastral_reference') }}</label>
            <input
              id="cadastral_reference"
              type="text"
              v-model="propertyData.cadastral_reference"
              class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
              placeholder="{{ $t('properties.reference_placeholder') }}"
            />
          </div>

          <div class="flex flex-col">
            <label for="description" class="font-medium mb-1">{{ $t('properties.description') }}</label>
            <textarea
              id="description"
              v-model="propertyData.description"
              rows="3"
              class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
              placeholder="{{ $t('properties.description_placeholder') }}"
            ></textarea>
          </div>

          <div class="flex items-center space-x-4">
            <label class="flex items-center">
              <input
                type="checkbox"
                v-model="propertyData.rental_type"
                true-value="'per_room'"
                false-value="'full'"
                class="w-6 h-6 bg-gray-800 text-sky-500 rounded"
              />
              <span class="ml-2">{{ $t('properties.per_room') }}</span>
            </label>
          </div>

          <div class="flex flex-col">
            <label for="total_rooms" class="font-medium mb-1">{{ $t('properties.total_rooms') }}</label>
            <input
              id="total_rooms"
              type="number"
              v-model.number="propertyData.total_rooms"
              class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
              placeholder="{{ $t('properties.total_rooms_placeholder') }}"
            />
          </div>

          <div class="flex justify-between mt-6">
            <button
              @click="nextStep"
              class="px-6 py-3 bg-sky-600 rounded hover:bg-sky-500 transition"
            >
              {{ $t('common.continue') }}
            </button>
          </div>
        </div>

        <!-- Paso 2: Gestión de habitaciones -->
        <div v-if="step === 2" class="space-y-4">
          <h2 class="text-xl font-bold">{{ $t('properties.add_rooms_title') }}</h2>
          <p class="text-gray-300">{{ $t('properties.add_rooms_description') }}</p>

          <div v-for="i in propertyData.total_rooms" :key="i" class="flex space-x-4 items-center">
            <input
              type="text"
              :placeholder="`${$t('properties.room')} ${i}`"
              class="p-3 bg-gray-700 rounded w-full focus:ring-2 focus:ring-sky-500"
            />
            <input
              type="number"
              placeholder="{{ $t('properties.room_price') }}"
              class="p-3 bg-gray-700 rounded w-1/3 focus:ring-2 focus:ring-sky-500"
            />
          </div>

          <div class="flex justify-between mt-6">
            <button
              @click="prevStep"
              class="px-6 py-3 bg-gray-600 rounded hover:bg-gray-500 transition"
            >
              {{ $t('common.back') }}
            </button>
            <button
              @click="nextStep"
              class="px-6 py-3 bg-sky-600 rounded hover:bg-sky-500 transition"
            >
              {{ $t('common.continue') }}
            </button>
          </div>
        </div>

        <!-- Paso 3: Datos opcionales -->
        <div v-if="step === 3" class="space-y-4">
          <h2 class="text-xl font-bold">{{ $t('properties.optional_title') }}</h2>
          <p class="text-gray-300">{{ $t('properties.optional_description') }}</p>

          <div v-for="field in optionalFields" :key="field.key" class="flex flex-col">
            <label :for="field.key" class="font-medium mb-1">{{ field.label }}</label>
            <input
              :id="field.key"
              v-model="propertyData[field.key]"
              :type="field.type"
              class="p-3 bg-gray-700 rounded focus:ring-2 focus:ring-sky-500"
              :placeholder="field.placeholder"
            />
          </div>

          <div class="flex justify-between mt-6">
            <button
              @click="prevStep"
              class="px-6 py-3 bg-gray-600 rounded hover:bg-gray-500 transition"
            >
              {{ $t('common.back') }}
            </button>
            <button type="submit" class="px-6 py-3 bg-sky-600 rounded hover:bg-sky-500 transition">
              {{ $t('properties.register_property') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
const step = ref(1);
const propertyData = ref({
  address: "",
  cadastral_reference: "",
  description: "",
  rental_type: "full",
  total_rooms: 0,
});

const optionalFields = [
  { key: "purchase_price", label: "Purchase Price", type: "number", placeholder: "0.00" },
  { key: "estimated_value", label: "Estimated Value", type: "number", placeholder: "0.00" },
  { key: "annual_insurance_cost", label: "Annual Insurance Cost", type: "number", placeholder: "0.00" },
];

const nextStep = () => step.value++;
const prevStep = () => step.value--;
const handleSubmit = () => {
  console.log("Submitting property:", propertyData.value);
};
</script>
