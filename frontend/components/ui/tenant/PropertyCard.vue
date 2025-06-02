<template>
  <div class="grid gap-6 md:grid-cols-2 bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6">
    <!-- Sección de Imagen -->
    <div class="relative group overflow-hidden rounded-lg aspect-square min-h-[300px]">
      <div v-if="loadingImage" class="absolute inset-0 bg-gray-100 animate-pulse"></div>
      
      <div v-else-if="!imageUrl" class="h-full w-full flex flex-col items-center justify-center bg-gray-50">
        <i class="pi pi-image text-4xl text-gray-300 mb-3"></i>
        <span class="text-gray-400 text-sm">Imagen no disponible</span>
      </div>
      
      <img
        v-else
        :src="imageUrl"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        :alt="imageAltText"
        @error="handleImageError"
      />
    </div>

    <!-- Detalles -->
    <div class="flex flex-col gap-4">
      <!-- Encabezado -->
      <div class="flex flex-col gap-2 border-b pb-4">
        <div class="flex items-center gap-2">
          <Tag
            :value="data.type === 'Property' ? 'Propiedad completa' : 'Habitación'"
            class="text-xs font-semibold py-1 px-3 rounded-full"
          />
          <Badge
            :value="statusLabel"
            :severity="statusSeverity"
            class="py-1 px-3 text-xs"
          />
        </div>
        <h2 class="text-2xl font-bold text-gray-900 leading-tight">
          {{ title }}
        </h2>
        <div class="flex items-center gap-2 text-gray-600">
          <i class="pi pi-map-marker text-primary"></i>
          <span class="text-sm">{{ address }}</span>
        </div>
      </div>

      <!-- Precio y características principales -->
      <div class="space-y-4">
        <div v-if="data.type === 'Room'" class="bg-blue-50 p-4 rounded-lg">
          <p class="text-lg font-semibold text-blue-800">
            <i class="pi pi-credit-card mr-2"></i>
            {{ rentalPrice }} / mes
          </p>
        </div>

        <!-- Descripción -->
        <div class="prose max-w-none text-gray-600 relative">
          <div ref="descriptionContainer" class="overflow-hidden" :style="{ maxHeight: isExpanded ? 'none' : '6em' }">
            <p class="m-0">{{ descriptionText }}</p>
          </div>
          <button
            v-if="showExpandButton"
            @click="isExpanded = !isExpanded"
            class="text-primary hover:text-primary-dark text-sm font-medium mt-1 flex items-center"
          >
            {{ isExpanded ? $t('common.readLess') : $t('common.readMore') }}
            <i class="pi ml-1" :class="isExpanded ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
          </button>
        </div>

        <!-- Características -->
        <div v-if="features.length" class="space-y-2">
          <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">
            <i class="pi pi-star mr-2 text-primary"></i>
            Características principales
          </h3>
          <div class="flex flex-wrap gap-2">
            <Chip
              v-for="(feature, index) in features"
              :key="index"
              :label="feature"
              class="text-sm bg-gray-100 hover:bg-gray-200 transition-colors"
            />
          </div>
        </div>

        <!-- Detalles específicos -->
        <div class="grid grid-cols-2 gap-4 text-sm">
          <template v-if="data.type === 'Property'">
            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
              <i class="pi pi-ruler text-primary"></i>
              <div>
                <p class="font-medium text-gray-900">{{ data.rentable.details?.size || 'N/D' }} m²</p>
                <p class="text-xs text-gray-500">Superficie</p>
              </div>
            </div>
            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
              <i class="pi pi-bed text-primary"></i>
              <div>
                <p class="font-medium text-gray-900">{{ data.rentable.total_rooms }}</p>
                <p class="text-xs text-gray-500">Dormitorios</p>
              </div>
            </div>
          </template>

          <template v-if="data.type === 'Room'">
            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
              <i class="pi pi-building text-primary"></i>
              <div>
                <p class="font-medium text-gray-900">#{{ roomNumber }}</p>
                <p class="text-xs text-gray-500">Número</p>
              </div>
            </div>
            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
              <i class="pi pi-calendar text-primary"></i>
              <div>
                <p class="font-medium text-gray-900">{{ formattedDate }}</p>
                <p class="text-xs text-gray-500">Disponible desde</p>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { usePropertiesStore } from '~/store/properties'
import type { Property, Room } from '~/types/property'

const props = defineProps<{
  data: {
    type: 'Property' | 'Room'
    rentable: Property | Room
  }
}>()

const propertiesStore = usePropertiesStore()
const imageUrl = ref<string | null>(null)
const loadingImage = ref(false)
const imageError = ref(false)
const isExpanded = ref(false)
const showExpandButton = ref(false)
const descriptionContainer = ref<HTMLElement | null>(null)

// Utilidades de formato
const formatCurrency = (value: number) => 
  new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(value)

const formatDate = (dateString: string) =>
  new Date(dateString).toLocaleDateString('es-ES')

// Descripción
const descriptionText = computed(() => {
  const text = props.data.type === 'Property' 
    ? (props.data.rentable as Property).description 
    : (props.data.rentable as Room).description
  
  return text || 'Descripción no disponible'
})

// Verificar si se necesita el botón de expandir
const checkDescriptionHeight = async () => {
  await nextTick()
  if (descriptionContainer.value) {
    showExpandButton.value = descriptionContainer.value.scrollHeight > 96
  }
}

watch(descriptionText, checkDescriptionHeight)
onMounted(checkDescriptionHeight)

// Computed properties
const isProperty = computed(() => props.data.type === 'Property')
const currentProperty = computed(() => isProperty.value ? props.data.rentable as Property : null)
const currentRoom = computed(() => !isProperty.value ? props.data.rentable as Room : null)

const imageAltText = computed(() => 
  isProperty.value 
    ? `Imagen de la propiedad ${currentProperty.value?.address}`
    : `Imagen de la habitación ${currentRoom.value?.room_number}`
)

const address = computed(() => {
  if (isProperty.value) {
    return currentProperty.value?.address || 'Dirección no disponible'
  }
  return currentRoom.value?.property?.address || 'Dirección no disponible'
})

const roomNumber = computed(() => 
  !isProperty.value ? currentRoom.value?.room_number : null
)

const statusLabel = computed(() => {
  const statusMap: Record<string, string> = {
    available: 'Disponible',
    occupied: 'Ocupada',
    unavailable: 'No disponible',
    partially_occupied: 'Parcialmente ocupada'
  }
  return statusMap[props.data.rentable.status] || props.data.rentable.status
})

const statusSeverity = computed(() => {
  const status = props.data.rentable.status
  return status === 'available' ? 'success' 
    : status === 'occupied' ? 'danger' 
    : 'warning'
})

const title = computed(() => {
  if (isProperty.value) {
    return currentProperty.value?.description || 'Propiedad sin nombre'
  }
  return `Habitación ${roomNumber.value}`
})

const rentalPrice = computed(() => {
  if (!isProperty.value) {
    return formatCurrency(currentRoom.value?.rental_price || 0)
  }
  return null
})

const features = computed(() => {
  if (isProperty.value) {
    return currentProperty.value?.details?.amenities || []
  }
  return currentRoom.value?.description?.split(',') || []
})

const formattedDate = computed(() => 
  formatDate(props.data.rentable.created_at)
)
</script>

<style scoped>
.prose {
  line-height: 1.6;
  transition: max-height 0.3s ease;
}

.pi {
  font-size: 0.9em;
}

.chip {
  transition: all 0.2s ease;
}

.hover\:shadow-xl {
  transition: box-shadow 0.3s ease;
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}
</style>
