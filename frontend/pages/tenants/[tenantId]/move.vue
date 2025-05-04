<template>
  <div class="py-8 px-4 max-w-7xl mx-auto relative">

    <div ref="alertContainer" v-if="alertMessage" class="mb-6 mx-auto">
      <Alert
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />
    </div>

    <div class="text-center mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
        Reasignar Inquilino
      </h1>
      <p class="text-gray-500 mt-1">
        Sigue estos pasos para cambiar al inquilino de propiedad o habitación
      </p>
    </div>

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

    <div
      class="relative flex flex-col xl:flex-row gap-6 xl:gap-8 items-stretch"
    >
      <section
        class="flex-1 bg-white relative z-10 shadow-md rounded-xl border border-gray-100 p-4"
      >
        <h2 class="text-lg font-semibold mb-4 text-center text-gray-700">
          ¿De dónde?
        </h2>

        <div class="space-y-3">
          <MiniReassignCard
            v-if="currentProperty"
            :item="currentProperty"
            type="property-orig"
            title="Propiedad actual"
            class="cursor-default"
          />

          <div
            v-if="
              currentProperty?.rental_type === 'per_room' && currentRoom
            "
            class="ml-4 pl-4 mt-2 space-y-2 border-l-2 border-gray-100"
          >
            <MiniReassignCard
              :item="currentRoom"
              type="room-orig"
              title="Habitación actual"
              class="cursor-default"
            />
          </div>
        </div>

        <div class="md:hidden text-center mt-4 text-gray-400">
          <ArrowDownIcon class="h-6 w-6 inline-block" />
        </div>
      </section>

      <section
        class="flex-1 bg-white relative z-10 shadow-md rounded-xl border border-gray-100 p-4"
      >
        <h2 class="text-lg font-semibold mb-4 text-center text-gray-700">
          ¿A dónde?
        </h2>

        <div class="space-y-4">
          <div v-for="prop in filteredProperties" :key="prop.id" class="pb-2">
            <MiniReassignCard
              :item="prop"
              :type="prop.rental_type === 'full' ? 'property' : 'property-info'"
              :title="
                prop.rental_type === 'full'
                  ? 'Propiedad disponible (alquiler completo)'
                  : 'Propiedad por habitaciones (selecciona una habitación)'
              "
              class="mb-2 rounded-lg transition-all"
              :class="{
                'bg-gray-50 border-2 border-transparent hover:border-info cursor-pointer':
                  prop.rental_type === 'full',
                'bg-gray-50 opacity-70 cursor-not-allowed':
                  prop.rental_type === 'per_room',
              }"
              @click="
                prop.rental_type === 'full' &&
                  selectDestination(prop, 'property')
              "
            />

            <!-- Show available rooms for per_room properties -->
            <div
              v-if="prop.rental_type === 'per_room'"
              class="ml-4 pl-4 mt-2 space-y-2 border-l-2 border-gray-100"
            >
              <MiniReassignCard
                v-for="room in getAvailableRooms(prop.id)"
                :key="room.id"
                :item="room"
                type="room"
                class="bg-gray-50 border-2 border-transparent hover:border-info rounded-lg cursor-pointer transition-all"
                @click="selectDestination(room, 'room')"
              />
            </div>
          </div>
        </div>

        <div class="md:hidden text-center mt-4 text-gray-400">
          <ArrowDownIcon class="h-6 w-6 inline-block" />
        </div>
      </section>

      <section
        class="flex-1 bg-white relative z-10 shadow-md rounded-xl border border-gray-100 p-4"
      >
        <h2 class="text-lg font-semibold mb-4 text-center text-gray-700">
          Resumen
        </h2>
        <div class="space-y-4">
          <MiniReassignCard
            v-if="selectedTenant"
            :item="selectedTenant"
            type="tenant"
            title="Inquilino"
            class="bg-gray-50 cursor-default"
          />

          <div v-if="destination" class="relative">
            <MiniReassignCard
              :item="destination"
              :type="destinationType"
              title="Nuevo destino"
              class="bg-gray-50 border-l-4 border-info"
            />
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

    <div class="mt-8 flex justify-center space-x-4 relative z-10">
      <button class="button-outline" @click="$router.go(-1)">
        Volver atrás
      </button>

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
import { ArrowRightIcon, ArrowDownIcon } from "@heroicons/vue/24/outline";
import { usePropertiesStore } from "~/store/properties";

import type { Tenant } from "~/types/tenant";
import type { Property } from "~/types/property";
import type { Room } from "~/types/room";

const route = useRoute();
const { locale } = useI18n();;
const propertiesStore = usePropertiesStore();

const alertMessage = ref("");
const alertType = ref<"success" | "error">("success");

const selectedTenant = ref<Tenant | null>(null);
const currentProperty = computed(() => propertiesStore.currentProperty);
const currentRoom = computed(() => propertiesStore.currentRoom);

onMounted(async () => {
  selectedTenant.value = propertiesStore.currentTenant;

  if (!propertiesStore.properties.length) {
    await propertiesStore.fetchProperties();
  }

  for (const prop of propertiesStore.properties) {
    if (prop.rental_type === "per_room") {
      await propertiesStore.fetchRooms(prop.id);
    }
  }
});

// Used only if needed
const originType = computed<"property" | "room" | "">(() => {
  if (!currentProperty?.value) return "";
  return currentProperty.value.rental_type === "full" ? "property" : "room";
});

const destination = ref<Property | Room | null>(null);
const destinationType = ref<"property" | "room" | "">("");

const clearDestination = () => {
  destination.value = null;
  destinationType.value = "";
};

// We only get available or partially_occupied properties for reassignment
const filteredProperties = computed(() => {
  if (!propertiesStore.properties.length) return [];
  return propertiesStore.properties
    .filter((p) => {
      if (p.rental_type === 'full') {
        return p.status === 'available';
      }
      if (p.rental_type === 'per_room') {
        return p.status === 'available' || p.status === 'partially_occupied';  
      }
    })
});

// Given a property id, we return its available rooms to rent
const getAvailableRooms = (propertyId: number): Room[] => {
  const roomGroup = propertiesStore.roomsMap[propertyId];
  return roomGroup?.rooms?.filter((r) => r.status === "available") || [];
};

// Its marks destination
const selectDestination = (
  item: Property | Room,
  type: "property" | "room"
) => {
  destination.value = item;
  destinationType.value = type;
};

// It calls store method to reassign tenant
const onReassignTenant = () => {
  if (!selectedTenant.value || !destination.value) return;
  console.log(
    `Reassigning tenant ${selectedTenant.value.name} =>`,
    destination.value
  );

  propertiesStore
    .reassignTenant(
      selectedTenant.value.id,
      destination.value.id,
      destinationType.value,
      currentProperty.value,
      currentRoom.value,
    )
    .then(() => {
      navigateTo(`/${locale.value}/properties?msg=tenant_reassigned`);
    })
    .catch((err) => {
      console.error("Error reassigning tenant:", err);
      alertMessage.value =
        "Error al reasignar el inquilino. Por favor, inténtelo de nuevo.";
      alertType.value = "error";

      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
};
</script>

<style scoped>
@media (max-width: 767px) {
  section {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
  }
}
</style>
