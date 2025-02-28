<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 text-center mb-8">
        Listado de Propiedades
      </h1>

      <!-- Loader -->
      <div v-if="loading" class="flex flex-col items-center justify-center my-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600 mb-4"></div>
        <p class="text-blue-600 font-medium animate-pulse">Cargando propiedades...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center my-20">
        <div class="inline-flex items-center bg-red-50 px-6 py-4 rounded-lg border border-red-200">
          <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <p class="text-red-600 font-medium">Error al cargar propiedades: {{ error }}</p>
        </div>
      </div>

      <!-- Grid de propiedades -->
      <div v-else class="space-y-8">
        <div v-if="properties.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Card de propiedad -->
          <div v-for="property in properties" :key="property.id"
            class="relative group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300">
            <!-- Badge de estado -->
            <div class="absolute top-4 right-4 z-10">
              <span :class="statusBadgeClasses(property.status)"
                class="px-3 py-1.5 text-xs font-semibold rounded-full uppercase tracking-wide shadow-sm">
                {{ statusLabel(property.status) }}
              </span>
            </div>

            <!-- Imagen -->
            <div class="relative h-48 bg-gradient-to-br from-blue-50 to-blue-100 rounded-t-2xl overflow-hidden">
              <div class="absolute inset-0 flex items-center justify-center">
                <svg class="h-16 w-16 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>

            <!-- Contenido -->
            <div class="p-6 space-y-4">
              <h2 class="text-xl font-semibold text-gray-900 truncate">
                {{ property.address }}
              </h2>

              <p class="text-gray-600 text-sm line-clamp-3">
                {{ property.description || 'Sin descripción' }}
              </p>

              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center space-x-2">
                  <span class="text-gray-500">
                    {{ property.total_rooms }} Habitaciones
                  </span>
                </div>
                <span class="font-medium text-blue-600">
                  {{ rentalTypeLabel(property.rental_type) }}
                </span>
              </div>

              <NuxtLink :to="`/properties/${property.id}`"
                class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Ver detalles
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- Sin propiedades -->
        <div v-else class="text-center">
          <div class="bg-blue-50 p-6 rounded-2xl border border-blue-200 inline-block">
            <p class="text-blue-600 font-medium">
              No tienes propiedades registradas
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePropertiesStore } from "~/store/properties";

const propertiesStore = usePropertiesStore();
const { properties, loading, error, fetchProperties } = propertiesStore;

const statusBadgeClasses = (status: string) => {
  switch (status) {
    case 'available':
      return 'bg-green-100 text-green-800';
    case 'unavailable':
      return 'bg-red-100 text-red-800';
    case 'occupied':
      return 'bg-yellow-100 text-yellow-800';
    case 'partially_occupied':
      return 'bg-blue-100 text-blue-800';
    default:
      return 'bg-gray-200 text-gray-700';
  }
};

const statusLabel = (status: string) => {
  switch (status) {
    case 'available': return 'Disponible';
    case 'unavailable': return 'No disponible';
    case 'occupied': return 'Ocupada';
    case 'partially_occupied': return 'Parcial';
    default: return 'Desconocido';
  }
};

const rentalTypeLabel = (type: string) => {
  switch (type) {
    case 'full': return 'Completa';
    case 'per_room': return 'Por habitaciones';
    default: return 'Desconocido';
  }
};

onMounted(async () => {
  try {
    await fetchProperties();
  } catch (e) {
    console.error("Error al obtener propiedades:", e);
  }
});
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>