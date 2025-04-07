<template>
  <div class="py-8 px-4 max-w-7xl mx-auto relative">
    <!-- Encabezado principal -->
    <div class="text-center mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
        Reasignar Inquilino
      </h1>
      <p class="text-gray-500 mt-1">
        Sigue estos pasos para cambiar al inquilino de propiedad o habitación
      </p>
    </div>

    <!-- “Barra” o “Steps” (opcional) -->
    <div class="hidden md:flex justify-evenly items-center mb-8 px-4">
      <div class="flex flex-col items-center text-gray-600">
        <div
          class="w-10 h-10 rounded-full bg-info text-white flex items-center justify-center font-bold mb-2"
        >
          1
        </div>
        <span class="text-sm font-medium">Origen</span>
      </div>
      <ArrowRightIcon class="h-6 w-6 text-gray-400" />
      <div class="flex flex-col items-center text-gray-600">
        <div
          class="w-10 h-10 rounded-full bg-info text-white flex items-center justify-center font-bold mb-2"
        >
          2
        </div>
        <span class="text-sm font-medium">Destino</span>
      </div>
      <ArrowRightIcon class="h-6 w-6 text-gray-400" />
      <div class="flex flex-col items-center text-gray-600">
        <div
          class="w-10 h-10 rounded-full bg-info text-white flex items-center justify-center font-bold mb-2"
        >
          3
        </div>
        <span class="text-sm font-medium">Resumen</span>
      </div>
    </div>

    <!-- Contenedor principal -->
    <div
      class="relative flex flex-col xl:flex-row gap-6 xl:gap-8 items-stretch"
    >
      <!-- COLUMNA 1: ORIGEN -->
      <section
        class="flex-1 bg-white relative z-10 shadow-md rounded-xl border border-gray-100 p-4"
      >
        <h2 class="text-lg font-semibold mb-4 text-center text-gray-700">
          ¿De dónde?
        </h2>
        <div class="space-y-3">
          <MiniReassignCard
            v-if="currentOrigin"
            :item="currentOrigin"
            :type="originType"
            title="Origen actual"
            class="hover:bg-gray-50 cursor-default"
          />
        </div>

        <!-- Flecha entre columnas en móvil -->
        <div class="md:hidden text-center mt-4 text-gray-400">
          <ArrowDownIcon class="h-6 w-6 inline-block" />
        </div>
      </section>

      <!-- COLUMNA 2: DESTINOS POSIBLES -->
      <section
        class="flex-1 bg-white relative z-10 shadow-md rounded-xl border border-gray-100 p-4"
      >
        <h2 class="text-lg font-semibold mb-4 text-center text-gray-700">
          ¿A dónde?
        </h2>

        <div class="space-y-4">
          <!-- Recorremos las propiedades disponibles -->
          <div
            v-for="prop in availableProperties"
            :key="prop.id"
            class="pb-2"
          >
            <!-- Si la propiedad es "per_room", NO la hacemos seleccionable,
                 solo informativa. Si es "full", sí se puede clicar. -->
            <MiniReassignCard
              :item="prop"
              type="property"
              class="mb-2 rounded-lg transition-all"
              :class="{
                'bg-gray-50 border-2 border-transparent hover:border-info cursor-pointer':
                  prop.rental_type === 'full',
                'bg-gray-50 opacity-70 cursor-not-allowed': prop.rental_type === 'per_room'
              }"
              @click="prop.rental_type === 'full' && selectDestination(prop, 'property')"
              :title="prop.rental_type === 'per_room' 
                ? 'Propiedad por habitaciones (selecciona una habitación)' 
                : 'Propiedad seleccionable'"
            />

            <!-- Si la propiedad es "per_room", mostramos sus habitaciones,
                 y solo se pueden seleccionar éstas. -->
            <div
              v-if="prop.rental_type === 'per_room'"
              class="ml-4 pl-4 mt-2 space-y-2 border-l-2 border-gray-100"
            >
              <MiniReassignCard
                v-for="room in getRoomsByProperty(prop.id)"
                :key="room.id"
                :item="room"
                type="room"
                @click="selectDestination(room, 'room')"
                class="bg-gray-50 border-2 border-transparent hover:border-info rounded-lg cursor-pointer transition-all"
              />
            </div>
          </div>
        </div>

        <!-- Flecha entre columnas en móvil -->
        <div class="md:hidden text-center mt-4 text-gray-400">
          <ArrowDownIcon class="h-6 w-6 inline-block" />
        </div>
      </section>

      <!-- COLUMNA 3: RESUMEN -->
      <section
        class="flex-1 bg-white relative z-10 shadow-md rounded-xl border border-gray-100 p-4"
      >
        <h2 class="text-lg font-semibold mb-4 text-center text-gray-700">
          Resumen
        </h2>
        <div class="space-y-4">
          <!-- Inquilino seleccionado -->
          <MiniReassignCard
            v-if="selectedTenant"
            :item="selectedTenant"
            type="tenant"
            title="Inquilino"
            class="bg-gray-50 cursor-default"
          />

          <!-- Destino elegido -->
          <div v-if="destination" class="relative">
            <MiniReassignCard
              :item="destination"
              :type="destinationType"
              title="Nuevo destino"
              class="bg-gray-50 border-l-4 border-info"
            />
            <!-- Botón para deseleccionar -->
            <button
              class="absolute top-2 right-2 text-red-500 text-sm hover:underline"
              @click="clearDestination"
            >
              Quitar
            </button>
          </div>
        </div>
      </section>
    </div>

    <!-- Botón de confirmación -->
    <div class="mt-8 flex justify-center relative z-10">
      <button
        class="button-primary disabled:bg-gray-300"
        :disabled="!destination"
        @click="onReassignTenant"
      >
        Confirmar Reasignación
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ArrowRightIcon, ArrowDownIcon } from "@heroicons/vue/24/outline"

// Tipos
import type { Tenant } from "~/types/tenant"
import type { Property } from "~/types/property"
import type { Room } from "~/types/room"

// Obtenemos el tenantId de la URL
const route = useRoute()
const tenantId = Number(route.params.tenantId)

// Estados
const selectedTenant = ref<Tenant | null>(null)
const currentOrigin = ref<Property | Room | null>(null)
const originType = ref<"property" | "room" | "">("")

const availableProperties = ref<Property[]>([])
const roomsMap = ref<{ [propertyId: number]: Room[] }>({})

const destination = ref<Property | Room | null>(null)
const destinationType = ref<"property" | "room" | "">("")

onMounted(async () => {
  await fetchTenantAndOrigin()
  await fetchAvailableProperties()
})

async function fetchTenantAndOrigin() {
  // Simulación de fetch de un inquilino
  selectedTenant.value = {
    id: tenantId,
    name: "Inquilino de ejemplo",
    email: "test@example.com",
    user_type: "individual",
    profile_picture: "",
    phone_number: "666999111",
    room_id: "2",
  }

  // Simulamos que ese inquilino está en la habitación con id=2
  currentOrigin.value = {
    id: 2,
    property_id: 10,
    room_number: 2,
    description: "Habitación con balcón",
    rental_price: 350,
    status: "occupied",
    main_image: "",
    images: [],
    tenant: selectedTenant.value,
    invitations: [],
    created_at: "",
    updated_at: "",
  }
  originType.value = "room"
}

async function fetchAvailableProperties() {
  // Simulación de fetch de propiedades
  availableProperties.value = [
    {
      id: 10,
      address: "Calle Mayor 123",
      cadastral_reference: "ABC123",
      description: "Propiedad grande (alquiler por habitaciones)",
      rental_type: "per_room",
      status: "partially_occupied",
      total_rooms: 5,
      main_image_url: "",
      images: [],
      created_at: "",
      updated_at: "",
    },
    {
      id: 11,
      address: "Avenida Sol 45",
      cadastral_reference: "XYZ456",
      description: "Piso completo disponible",
      rental_type: "full",
      status: "available",
      total_rooms: 3,
      main_image_url: "",
      images: [],
      created_at: "",
      updated_at: "",
    },
  ]

  roomsMap.value[10] = [
    {
      id: 1001,
      property_id: 10,
      room_number: 1,
      description: "Habitación 1 (disponible)",
      rental_price: 300,
      status: "available",
      main_image: "",
      images: [],
      tenant: null,
      invitations: [],
      created_at: "",
      updated_at: "",
    },
    {
      id: 1002,
      property_id: 10,
      room_number: 2,
      description: "Habitación 2 (ocupada)",
      rental_price: 320,
      status: "occupied",
      main_image: "",
      images: [],
      tenant: null,
      invitations: [],
      created_at: "",
      updated_at: "",
    },
  ]
}

function getRoomsByProperty(propertyId: number): Room[] {
  return roomsMap.value[propertyId] || []
}

function selectDestination(item: Property | Room, type: "property" | "room") {
  destination.value = item
  destinationType.value = type
}

function clearDestination() {
  destination.value = null
  destinationType.value = ""
}

function onReassignTenant() {
  if (!selectedTenant.value || !destination.value) return
  console.log(
    `Reasignando inquilino ${selectedTenant.value.name} =>`,
    destination.value
  )
  // Aquí llamarías a tu API
}
</script>

<style scoped>
@media (max-width: 767px) {
  section {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
  }
}
</style>
