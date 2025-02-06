<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Listado de Propiedades</h1>
    <div v-if="loading" class="text-center">
      <p>Cargando propiedades...</p>
    </div>
    <div v-else>
      <ul v-if="properties.length > 0" class="space-y-4">
        <li v-for="property in properties" :key="property.id" class="p-4 bg-gray-100 rounded shadow">
          <h2 class="text-lg font-semibold">{{ property.name }}</h2>
          <p>{{ property.description }}</p>
          <p><strong>Dirección:</strong> {{ property.address }}</p>
        </li>
      </ul>
      <p v-else class="text-center text-gray-500">No tienes propiedades registradas.</p>
    </div>
    <div v-if="error" class="text-center text-red-500 mt-4">
      <p>Error al cargar las propiedades: {{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from "vue";
import { usePropertiesStore } from "~/store/properties";

const propertiesStore = usePropertiesStore();

const { properties, loading, error, fetchProperties } = propertiesStore;

onMounted(async () => {
  try {
    await fetchProperties();
  } catch (e) {
    console.error("Error al obtener propiedades:", e);
  }
});
</script>

<style scoped>
/* Estilo básico */
ul {
  list-style: none;
  padding: 0;
}

li {
  border: 1px solid #ddd;
}
</style>
