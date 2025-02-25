<template>
  <div class="bg-gray-50 min-h-screen">
    <div class="max-w-screen-xl mx-auto p-8">
      <!-- Loader / Error -->
      <div v-if="loading" class="flex flex-col items-center justify-center my-10">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-500 border-solid mb-4"></div>
        <p class="text-gray-700">Cargando propiedad...</p>
      </div>

      <div v-else-if="error" class="text-center text-red-500 my-10">
        <p>Error al cargar la propiedad: {{ error }}</p>
      </div>

      <!-- Contenido principal -->
      <div v-else-if="property" class="space-y-8">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">
          Detalles de la Propiedad
        </h1>

        <!-- Sección superior: Imagen y datos principales -->
        <div class="flex flex-col md:flex-row gap-6">
          <!-- Imagen principal (izquierda) -->
          <div class="md:w-1/2 flex items-center justify-center bg-gray-200 h-64 md:h-auto rounded-lg overflow-hidden">
            <!-- Si tienes una imagen real en property.main_image, úsalo en :src -->
            <!-- Aquí ponemos un placeholder o fondo gris -->
            <img
              v-if="property.main_image"
              :src="property.main_image"
              alt="Imagen de la propiedad"
              class="object-cover h-full w-full"
            />
            <span v-else class="text-gray-500 text-sm">Sin imagen disponible</span>
          </div>

          <!-- Datos (derecha) -->
          <div class="md:w-1/2 flex flex-col justify-between">
            <!-- Encabezado con un badge de estado -->
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-800">
                {{ property.address }}
              </h2>
              <!-- Badge de estado de la propiedad -->
              <span
                :class="propertyStatusBadge(property.status)"
                class="px-3 py-1 text-xs font-semibold rounded-full uppercase"
              >
                {{ statusLabel(property.status) }}
              </span>
            </div>

            <!-- Descripción, Cadastral Reference, etc. -->
            <p class="text-gray-700 mb-2">
              <strong>Cadastral Reference:</strong> {{ property.cadastral_reference }}
            </p>
            <p class="text-gray-700 mb-2">
              <strong>Descripción:</strong> {{ property.description || 'Sin descripción' }}
            </p>
            <p class="text-gray-700 mb-2">
              <strong>Tipo de Alquiler:</strong> 
              <span class="capitalize">{{ rentalTypeLabel(property.rental_type) }}</span>
            </p>
            <p class="text-gray-700 mb-2">
              <strong>Estado:</strong> {{ statusLabel(property.status) }}
            </p>
            <p class="text-gray-700 mb-2">
              <strong>Número de habitaciones:</strong> {{ property.total_rooms }}
            </p>

            <!-- Botones de acción (editar propiedad, añadir habitaciones, etc.) -->
            <div class="flex flex-wrap gap-3 mt-4">
              <!-- Botón Editar Propiedad -->
              <button
                class="bg-blue-600 text-white font-semibold px-4 py-2 rounded hover:bg-blue-700 transition"
                @click="editProperty"
              >
                Editar Propiedad
              </button>

              <!-- Botón Agregar Habitación (solo si rental_type = 'by_room', por ejemplo) -->
              <button
                v-if="property.rental_type === 'by_room'"
                class="bg-green-600 text-white font-semibold px-4 py-2 rounded hover:bg-green-700 transition"
                @click="addRoom"
              >
                Añadir Habitación
              </button>
            </div>
          </div>
        </div>

        <!-- Sección de habitaciones (solo si rental_type = 'by_room') -->
        <div v-if="property.rental_type === 'by_room' && property.rooms && property.rooms.length"
             class="mt-10"
        >
          <h2 class="text-lg font-semibold text-gray-800 mb-4">
            Habitaciones de la Propiedad
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <!-- Tarjeta de cada habitación -->
            <div
              v-for="room in property.rooms"
              :key="room.id"
              class="relative bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col"
            >
              <!-- Badge de estado de la habitación -->
              <span
                :class="roomStatusBadge(room.status)"
                class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold rounded-full uppercase"
              >
                {{ statusLabel(room.status) }}
              </span>

              <!-- Imagen de la habitación (placeholder si no hay) -->
              <div class="bg-gray-300 h-40 w-full flex items-center justify-center">
                <span v-if="!room.main_image" class="text-gray-500 text-sm">Sin imagen</span>
                <img
                  v-else
                  :src="room.main_image"
                  alt="Imagen de la habitación"
                  class="object-cover h-full w-full"
                />
              </div>

              <!-- Info de la habitación -->
              <div class="p-4 flex-1 flex flex-col justify-between">
                <div>
                  <h3 class="text-md font-semibold text-gray-800 mb-2">
                    Habitación #{{ room.room_number }}
                  </h3>
                  <p class="text-gray-700 text-sm mb-2 line-clamp-2" :title="room.description">
                    {{ room.description }}
                  </p>
                  <p class="text-gray-700 text-sm font-semibold">
                    Precio: {{ room.rental_price }} €/mes
                  </p>
                </div>
              </div>

              <!-- Footer / Acciones de la habitación -->
              <div class="px-4 py-3 border-t border-gray-100 flex justify-between items-center bg-gray-50">
                <!-- Botón para enviar invitación (si está disponible) -->
                <button
                  v-if="room.status === 'available'"
                  class="text-blue-600 font-bold hover:text-blue-800 transition-colors duration-200 text-sm"
                  @click="sendInvitation(room.id)"
                >
                  Enviar invitación
                </button>
                <!-- ID / Referencia de la habitación -->
                <span class="text-gray-500 text-xs">#{{ room.id }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Si no hay habitaciones pero rental_type = 'by_room', se podría mostrar un aviso -->
        <div v-else-if="property.rental_type === 'by_room'" class="mt-10 text-center text-gray-500">
          <p>Esta propiedad no tiene habitaciones registradas.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useRoute } from 'vue-router'; 
import { usePropertiesStore } from '~/store/properties';

// Asumimos que en tu store ya tienes un método fetchSingleProperty(id) que carga
// la propiedad en currentProperty, y un state property en currentProperty.value.
const propertiesStore = usePropertiesStore();
const { currentProperty, loading, error, fetchProperty } = propertiesStore;

// Obtenemos el ID de la ruta
const route = useRoute();
const propertyId = Number(route.params.id);

onMounted(async () => {
  try {
		console.log('dsa');
    const response = await fetchProperty(propertyId);
		console.log('dsa');
  } catch (e) {
    console.error('Error al obtener la propiedad:', e);
  }
});

// Acceso directo a la propiedad
const property = computed(() => currentProperty.value);

// Funciones para badges de estado (tanto para propiedad como para habitación)
const propertyStatusBadge = (status: string) => {
  // Puedes usar el mismo estilo que usaste en la lista de propiedades
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

const roomStatusBadge = (status: string) => {
  // Iguales colores o puedes diferenciarlos
  switch (status) {
    case 'available':
      return 'bg-green-100 text-green-800';
    case 'unavailable':
      return 'bg-red-100 text-red-800';
    case 'occupied':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-200 text-gray-700';
  }
};

// Traducción de status a texto
const statusLabel = (status: string) => {
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

// Traducción del tipo de alquiler
const rentalTypeLabel = (type: string) => {
  switch (type) {
    case 'entire':
      return 'Completo';
    case 'by_room':
      return 'Por habitaciones';
    default:
      return 'Desconocido';
  }
};

// Acciones de botones
const editProperty = () => {
  // Aquí navegas a la vista /properties/:id/edit o abres un modal, etc.
  console.log('Editar la propiedad', propertyId);
};

const addRoom = () => {
  // Aquí navegas a /properties/:id/rooms/add o abres un modal
  console.log('Agregar habitación a la propiedad', propertyId);
};

const sendInvitation = (roomId: number) => {
  // Lógica para enviar invitación al email. Podrías abrir un pequeño modal
  // pidiendo el correo, o redireccionar a una página de invitación.
  console.log('Enviar invitación para la habitación', roomId);
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2; /* Número de líneas que queremos mostrar */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
