<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="alertMessage" ref="alertRef" class="mb-4 mx-auto">
      <Alert
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 items-center mb-8 gap-4">
      <div class="hidden md:block"></div>

      <h1 class="text-3xl font-bold text-gray-900 text-center">
        Mis propiedades
      </h1>

      <div class="text-center md:text-right">
        <NuxtLink
          :to="$localePath('/properties/add')"
          class="button-primary inline-block"
        >
          Añadir una nueva propiedad
        </NuxtLink>
      </div>
    </div>

    <!-- Loader -->
    <div v-if="loading" class="flex flex-col items-center justify-center my-20">
      <div
        class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600 mb-4"
      ></div>
      <p class="text-blue-600 font-medium animate-pulse">
        Cargando propiedades...
      </p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center my-20">
      <div
        class="inline-flex items-center bg-red-50 px-6 py-4 rounded-lg border border-red-200"
      >
        <svg
          class="h-6 w-6 text-red-600 mr-3"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
        <p class="text-red-600 font-medium">
          Error al cargar propiedades: {{ error }}
        </p>
      </div>
    </div>

    <!-- Contenido -->
    <div v-else class="space-y-8">
      <!-- Grid de propiedades -->
      <div
        v-if="properties.length > 0"
        class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6 items-stretch"
      >
        <PropertyCard
          v-for="property in properties"
          :key="property.id"
          :property="property"
        />
      </div>

      <!-- Sin propiedades -->
      <div v-else class="text-center">
        <CardAlert message="No tienes propiedades registradas" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePropertiesStore } from "~/store/properties";

const propertiesStore = usePropertiesStore();
const { properties, loading, error } = storeToRefs(propertiesStore);
const { fetchProperties } = propertiesStore;
const route = useRoute();
const router = useRouter();
const { t: $t, locale } = useI18n();

const alertMessage = ref<string | null>(null);
const alertType = ref<"error" | "success">("error");

import { useMyToast } from "#imports";
const { success, info, error: errorToast}  = useMyToast();
const { showFlash } = useFlashToast();

onMounted(async () => {
  try {
    await fetchProperties();
    showFlash();
  } catch (e) {
    console.error('Error: ', e);
    errorToast("Error al obtener las propiedades", 10000);    
  }
});
</script>

<style scoped>
</style>