<template>
  <div
    class="relative group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 flex flex-col h-full"
  >
    <!-- Bloque absoluto para la “etiqueta” (estado) -->
    <div class="absolute top-4 right-4 z-10">
      <span
        :class="statusBadgeClasses(property.status)"
        class="px-3 py-1.5 text-xs font-semibold rounded-full uppercase tracking-wide shadow-sm"
      >
        {{ statusLabel(property.status) }}
      </span>
    </div>

    <!-- Bloque superior: imagen/degradado -->
    <div class="h-48 gradient-card rounded-t-3xl overflow-hidden relative">
      <div class="absolute inset-0 flex items-center justify-center">
        <svg
          class="h-16 w-16 text-info/30"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2 
               l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01
               M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6
               a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
      </div>
    </div>

    <div class="p-6 flex-1">
      <h2 class="title-card">
        {{ property.address }}
      </h2>
      <p class="text-card">
        {{ property.description || "Sin descripción" }}
      </p>
    </div>

    <div class="px-6 pb-6">
      <div class="flex items-center justify-between text-sm">
        <!-- <span class="font-medium text-blue-600">
          {{ rentalTypeLabel(property.rental_type) }}
        </span> -->
        <span
          :class="[
            rentalBadgeClasses(property.rental_type),
            'px-3 py-1 text-xs font-semibold rounded-full uppercase tracking-wide',
          ]"
        >
          {{ rentalTypeLabel(property.rental_type) }}
        </span>

        <div class="flex items-center space-x-2">
          <span class="text-gray-500 font-medium">
            {{ property.total_rooms }} Habitaciones
          </span>
        </div>
      </div>

      <NuxtLink
        :to="`/properties/${property.id}`"
        class="button-card mt-4 block text-center"
      >
        Ver completa
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Property } from "~/types/property";
import { statusBadgeClasses, statusLabel, rentalBadgeClasses, rentalTypeLabel} from "~/utils/badges";

defineProps<{
  property: Property;
}>();

</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>