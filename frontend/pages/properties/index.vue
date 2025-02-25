<template>
  <!-- Fondo gris claro en toda la página -->
  <div class="bg-gray-50 min-h-screen">
    <div class="max-w-screen-xl mx-auto p-8">
      <h1 class="text-2xl text-center font-bold mb-6 text-gray-800">
        Listado de Propiedades
      </h1>

      <!-- Loader -->
      <div v-if="loading" class="flex flex-col items-center justify-center my-10">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-500 border-solid mb-4"></div>
        <p class="text-gray-700">Cargando propiedades...</p>
      </div>

      <!-- Contenido principal -->
      <div v-else>
        <!-- Mensaje de Error -->
        <div v-if="error" class="text-center text-red-500 mb-6">
          <p>Error al cargar las propiedades: {{ error }}</p>
        </div>

        <!-- Grid de propiedades -->
        <div v-else>
          <div v-if="properties.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <!-- Card de cada propiedad -->
            <div v-for="property in properties" :key="property.id" class="relative bg-white rounded-lg shadow-md overflow-hidden
                     hover:shadow-xl transition-shadow duration-300 flex flex-col">
              <!-- Badge de estado (arriba a la derecha) -->
              <span :class="statusBadgeClasses(property.status)"
                class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold rounded-full uppercase">
                {{ statusLabel(property.status) }}
              </span>

              <!-- Imagen -->
              <div class="bg-gray-300 h-48 w-full flex items-center justify-center">
                <span class="text-gray-500 text-sm">Imagen de la propiedad</span>
              </div>

              <!-- Información -->
              <div class="p-4 flex-1 flex flex-col justify-between">
                <div>
                  <h2 class="text-lg font-semibold mb-1 text-gray-800">
                    {{ property.address }}
                  </h2>
                  <p class="text-gray-700 text-sm mb-2 line-clamp-2" :title="property.description">
                    {{ property.description }}
                  </p>
                </div>
              </div>

              <!-- Footer de la Card -->
              <div class="px-4 py-3 border-t border-gray-100 flex justify-between items-center bg-gray-50">
                <NuxtLink :to="`/properties/${property.id}`" class="...">Ver detalles</NuxtLink>

              </div>
            </div>
          </div>

          <!-- Sin propiedades -->
          <p v-else class="text-center text-gray-500">
            No tienes propiedades registradas.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { usePropertiesStore } from "~/store/properties";

const propertiesStore = usePropertiesStore();
const { properties, loading, error, fetchProperties } = propertiesStore;

/**
 * Retorna las clases CSS según el estado de la propiedad.
 * Aquí puedes ajustar los colores a tu gusto.
 */
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
  // Si quieres “traducir” o formatear el estado, puedes hacerlo aquí.
  // Por ejemplo, en vez de 'available' podrías mostrar 'Disponible'.
  switch (status) {
    case 'available':
      return 'Disponible';
    case 'unavailable':
      return 'No disponible';
    case 'occupied':
      return 'Ocupado';
    case 'partially_occupied':
      return 'Parcial';
    default:
      return 'Desconocido';
  }
};

onMounted(async () => {
  try {
    await fetchProperties();
  } catch (e) {
    console.error(e);
    console.error("Error al obtener propiedades:", e);
  }
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
