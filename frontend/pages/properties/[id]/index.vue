<template>
  <div class="min-h-screen">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loader -->
      <div v-if="loading" class="flex flex-col items-center justify-center my-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600 mb-4"></div>
        <p class="text-blue-600 font-medium animate-pulse">{{ $t('properties.detail.loading') }}</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center my-20">
        <div class="inline-flex items-center bg-red-50 px-6 py-4 rounded-lg border border-red-200">
          <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464
                 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <p class="text-red-600 font-medium">{{ $t('properties.detail.error') }} {{ error }}</p>
        </div>
      </div>

      <!-- Contenido principal -->
      <div v-else-if="currentProperty" class="space-y-8">
        <h1 class="text-3xl font-bold text-gray-900 text-center mb-8">
          {{ $t('properties.detail.title') }}
        </h1>

        <!-- Card principal -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col md:flex-row gap-8 p-8">
          <!-- Imagen -->
          <div
            class="md:w-1/2 bg-gradient-to-br from-blue-50 to-blue-100 h-96 rounded-xl flex items-center justify-center">
            <div class="text-center space-y-3">
              <svg class="h-12 w-12 text-blue-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828
                     0L16 16m-2-2l1.586-1.586a2 2 0 012.828
                     0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V7a2
                     2 0 00-2-2H6a2 2 0 00-2 2v11" />
              </svg>
              <span class="text-blue-300 text-sm font-medium">
                Imagen de la propiedad
              </span>
            </div>
          </div>

          <!-- Detalles -->
          <div class="md:w-1/2 space-y-6">
            <div class="flex items-start justify-between">
              <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                  {{ currentProperty.address }}
                </h2>
                <div class="inline-flex items-center space-x-2">
                  <span :class="propertyStatusBadge(currentProperty.status)"
                    class="px-3 py-1.5 text-xs font-semibold rounded-full uppercase tracking-wide">
                    {{ statusLabel(currentProperty.status) }}
                  </span>
                  <span class="text-gray-500 text-sm">
                    {{ $t('properties.detail.cadastral') }}
                    {{ currentProperty.cadastral_reference }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Descripción -->
            <dl class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <dt class="text-sm font-medium text-gray-500">
                  {{ $t('properties.detail.description') }}
                </dt>
                <dd class="mt-1 text-gray-900 font-light italic">
                  {{ currentProperty.description || $t('properties.detail.noDescription') }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">
                  {{ $t('properties.detail.rentalType') }}
                </dt>
                <dd class="mt-1 text-gray-900 font-medium">
                  {{ rentalTypeLabel(currentProperty.rental_type) }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">
                  {{ $t('properties.detail.totalRooms') }}
                </dt>
                <dd class="mt-1 text-gray-900 font-medium">
                  {{ currentProperty.total_rooms }}
                </dd>
              </div>
            </dl>

            <!-- Botones con iconos -->
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
              <!-- Editar propiedad -->
              <button @click="editProperty"
                class="inline-flex items-center bg-blue-600 text-white font-semibold px-4 py-2 rounded hover:bg-blue-700 transition">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5
                       2.5 0 113.536 3.536L6.5
                       21.036H3v-3.572L16.732 3.732z" />
                </svg>
                {{ $t('properties.detail.editButton') }}
              </button>

              <!-- Añadir datos estadísticos -->
              <button @click="addStats"
                class="inline-flex items-center bg-indigo-600 text-white font-semibold px-4 py-2 rounded hover:bg-indigo-700 transition">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <!-- Icono chart -->
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3v18h18M18 9h-6m0 0V3m0 6l-6 6" />
                </svg>
                {{ $t('properties.detail.addStatsButton') }}
              </button>

              <!-- Cambiar estado de la propiedad -->
              <button @click="toggleStatus"
                class="inline-flex items-center bg-red-600 text-white font-semibold px-4 py-2 rounded hover:bg-red-700 transition">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <!-- Icono toggle -->
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7l4-4m0 0l4 4m-4-4v18" />
                </svg>
                {{ toggleStatusLabel }}
              </button>
            </div>
          </div>
        </div>

        <!-- Texto "Añade un inquilino" y botón para abrir formulario -->
        <div class="mt-8 flex flex-col items-center" v-if="currentProperty.rental_type !== 'per_room'">
          <p class="text-gray-700 font-medium mb-4">
            {{ $t('properties.detail.addTenantText') }}
          </p>
          <button @click="showTenantSection = true"
            class="inline-flex items-center bg-blue-600 text-white font-semibold px-4 py-2 rounded hover:bg-blue-700 transition">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <!-- Icono de usuario + plus -->
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9l2 2m0 0l2-2m-2 2v6m-7
                   -4a4 4 0 100-8 4 4 0 000 8zM12
                   14l.01.01M6 18v-1a3 3 0 013-3h6
                   a3 3 0 013 3v1" />
            </svg>
            {{ $t('properties.detail.addTenantButton') }}
          </button>
        </div>

        <!-- Card de formulario para invitar inquilino -->
        <div v-if="showTenantSection" class="mt-8 flex flex-col items-center">
          <!-- Card con más sombra -->
          <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-center">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- icono usuario plus -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9l2 2m0 0l2-2m-2
            2v6m-7-4a4 4 0 100-8
            4 4 0 000 8zM12
            14l.01.01M6 18v-1a3 3
            0 013-3h6a3 3 0 013 3v1" />
              </svg>
              {{ $t('properties.detail.tenantFormTitle') }}
            </h3>

            <form @submit.prevent="inviteTenant" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  {{ $t('properties.detail.tenantEmailLabel') }}
                </label>
                <input type="email" v-model="tenantEmail2" :placeholder="$t('properties.detail.tenantEmailPlaceholder')"
                  class="border border-gray-300 rounded w-full p-2 
                 focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-200" />
              </div>

              <div class="flex items-center justify-end gap-3 mt-4">
                <button type="button" @click="showTenantSection = false"
                  class="inline-flex items-center text-red-600 hover:text-red-800 font-medium text-sm">
                  <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  {{ $t('properties.detail.cancelButton') }}
                </button>

                <button type="submit"
                  class="inline-flex items-center bg-blue-600 text-white font-semibold px-4 py-2 rounded hover:bg-blue-700 transition">
                  {{ $t('properties.detail.sendInvitationButton') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Sección de habitaciones (per_room) -->
        <div v-if="currentProperty.rental_type === 'per_room'" class="mt-12">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">
              {{ $t('properties.detail.roomsTitle') }}
            </h2>
            <span class="text-gray-500 text-sm">
              {{ currentProperty.rooms?.length || 0 }} {{ $t('properties.detail.rooms.title') }}
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Habitaciones existentes -->
            <div v-for="room in currentProperty.rooms" :key="room.id"
              class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300">
              <!-- Imagen -->
              <div class="relative h-48 bg-gradient-to-br from-blue-50 to-blue-100 rounded-t-2xl overflow-hidden">
                <div class="absolute inset-0 flex items-center justify-center">
                  <svg class="h-16 w-16 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <!-- Badge de estado -->
                <span :class="roomStatusBadge(room.status)"
                  class="absolute top-4 right-4 px-3 py-1.5 text-xs font-semibold rounded-full uppercase tracking-wide shadow-sm">
                  {{ statusLabel(room.status) }}
                </span>
              </div>

              <!-- Contenido -->
              <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ $t('properties.detail.room') }} #{{ room.room_number }}
                  </h3>
                  <span class="text-blue-600 font-bold text-lg">
                    {{ room.rental_price }}€
                  </span>
                </div>

                <p class="text-gray-600 text-sm line-clamp-3">
                  {{ room.description || $t('properties.detail.noDescription') }}
                </p>

                <button v-if="room.status === 'available'" @click="sendInvitation(room.id)"
                  class="w-full inline-flex items-center justify-center bg-green-100 text-green-800 hover:bg-green-200 px-4 py-2 rounded-lg transition-colors">
                  <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  {{ $t('properties.detail.sendInvitation') }}
                </button>
              </div>
            </div>

            <!-- Card para añadir nueva habitación -->
            <div
              class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-dashed border-blue-200">
              <div class="h-full flex flex-col items-center justify-center p-8 text-center cursor-pointer"
                @click="addRoom">
                <div class="mb-4 bg-blue-100 p-4 rounded-full">
                  <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                  {{ $t('properties.detail.addRoomButton') }}
                </h3>
                <p class="text-gray-500 text-sm">
                  {{ $t('properties.detail.addRoomHelpText') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Mensaje cuando no hay habitaciones -->
          <div v-if="!currentProperty.rooms?.length" class="mt-8 text-center">
            <div class="bg-blue-50 p-6 rounded-2xl border border-blue-200 inline-block">
              <p class="text-blue-600 font-medium">
                {{ $t('properties.detail.noRoomsPrompt') }}
              </p>
            </div>
          </div>
        </div>

        <!-- No hay habitaciones -->
        <div v-else-if="currentProperty.rental_type === 'per_room'" class="mt-12 text-center text-gray-500">
          <p>{{ $t('properties.detail.noRooms') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { usePropertiesStore } from '~/store/properties'

const { t: $t, locale } = useI18n()
const propertiesStore = usePropertiesStore()
const { currentProperty, loading, error } = storeToRefs(propertiesStore)
const route = useRoute()
const propertyId = Number(route.params.id)

// Variables auxiliares
const showTenantSection = ref(false)
const tenantEmail2 = ref('')

onMounted(async () => {
  try {
    await propertiesStore.fetchProperty(propertyId)
  } catch (e) {
    console.error('Error al obtener la propiedad:', e)
  }
})

// Placeholder occupant data (si la propiedad está 'occupied')
const occupantName = ref('Nombre del Inquilino')
const occupantEmail = ref('inquilino@example.com')

// Botones
const sendTenantInvitation = () => {
  console.log('Enviar invitación a', tenantEmail2.value)
  // Lógica real
  showTenantSection.value = false
  tenantEmail2.value = ''
}

const goToChat = () => {
  console.log('Ir al chat con el inquilino')
}

const addStats = () => {
  const lang = locale.value
  const url = `/${lang}/properties/${propertyId}/details`
  navigateTo(url)
}

const toggleStatus = () => {
  if (!currentProperty.value?.status) return
  const newStatus = currentProperty.value.status === 'available' ? 'unavailable' : 'available'
  console.log('Cambiar estado de la propiedad a', newStatus)
  // Lógica real...
}

const toggleStatusLabel = computed(() => {
  if (currentProperty.value?.status === 'available') {
    return $t('properties.detail.makeUnavailableButton')
  } else {
    return $t('properties.detail.makeAvailableButton')
  }
})

// Utils
const propertyStatusBadge = (status: string) => {
  switch (status) {
    case 'available':
      return 'bg-green-100 text-green-800'
    case 'unavailable':
      return 'bg-red-100 text-red-800'
    case 'occupied':
      return 'bg-yellow-100 text-yellow-800'
    case 'partially_occupied':
      return 'bg-blue-100 text-blue-800'
    default:
      return 'bg-gray-200 text-gray-700'
  }
}

const roomStatusBadge = (status: string) => {
  switch (status) {
    case 'available':
      return 'bg-green-100 text-green-800'
    case 'unavailable':
      return 'bg-red-100 text-red-800'
    case 'occupied':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-gray-200 text-gray-700'
  }
}

const statusLabel = (status: string) => {
  switch (status) {
    case 'available':
      return $t('properties.detail.statusLabel.available')
    case 'unavailable':
      return $t('properties.detail.statusLabel.unavailable')
    case 'occupied':
      return $t('properties.detail.statusLabel.occupied')
    case 'partially_occupied':
      return $t('properties.detail.statusLabel.partially_occupied')
    default:
      return $t('properties.detail.statusLabel.unknown')
  }
}

const rentalTypeLabel = (type: string) => {
  switch (type) {
    case 'entire':
    case 'full':
      return $t('properties.detail.rental_type_full') || 'Completo'
    case 'per_room':
      return $t('properties.detail.rental_type_per_room') || 'Por habitaciones'
    default:
      return 'Desconocido'
  }
}

// Simples placeholders
const editProperty = () => {
  console.log('Editar la propiedad', propertyId)
}
const addRoom = () => {
  const lang = locale.value
  const url = `/${lang}/properties/${propertyId}/rooms/add`
  console.log(url);
  navigateTo(url);
}

const sendInvitation = (roomId: number) => {
  console.log('Enviar invitación para la habitación con id', roomId)
}
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
