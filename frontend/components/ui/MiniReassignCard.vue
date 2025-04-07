<template>
  <div
    class="w-full border rounded-lg p-3 flex items-center gap-4 transition"
    :title="title"
  >
    <!-- Imagen / ícono principal -->
    <div class="w-12 h-12 flex-shrink-0 rounded bg-gray-100 flex items-center justify-center">
      <img
        v-if="imageUrl"
        :src="imageUrl"
        alt="Imagen"
        class="w-12 h-12 rounded object-cover"
      />
      <span v-else class="text-gray-400 text-xl">
        <!-- Icono según el tipo -->
        <template v-if="type === 'tenant'">🧑</template>
        <template v-else-if="type === 'property'">🏠</template>
        <template v-else-if="type === 'room'">🛏️</template>
      </span>
    </div>

    <!-- Datos (sin fijar ancho; usará el espacio restante) -->
    <div class="flex-1 space-y-1">
      <template v-if="type === 'tenant'">
        <p class="font-semibold text-gray-800">
          {{ (item as Tenant).name }}
        </p>
        <p class="text-sm text-gray-600">
          {{ (item as Tenant).email }}
        </p>
      </template>

      <template v-else-if="type === 'property'">
        <p class="font-semibold text-gray-800">
          {{ (item as Property).address }}
        </p>
        <p class="text-sm text-gray-600 line-clamp-2">
          {{ (item as Property).description }}
        </p>
      </template>

      <template v-else-if="type === 'room'">
        <p class="font-semibold text-gray-800">
          Habitación #{{ (item as Room).room_number }}
        </p>
        <p class="text-sm text-gray-600 line-clamp-2">
          {{ (item as Room).description }}
        </p>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Tenant } from '~/types/tenant'
import type { Property } from '~/types/property'
import type { Room } from '~/types/room'

const props = defineProps<{
  item: Tenant | Property | Room
  type: 'tenant' | 'property' | 'room'
  title?: string
}>()

const imageUrl = computed(() => {
  if (props.type === 'tenant') {
    return (props.item as Tenant).profile_picture
  } else if (props.type === 'property') {
    return (props.item as Property).main_image_url
  } else if (props.type === 'room') {
    return (props.item as Room).main_image
  }
  return ''
})
</script>

<style scoped>
/* Si necesitas truncar a varias líneas, agrega line-clamp. 
   P.ej. .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
   y config en tailwind.config.js:
   plugins: [require('@tailwindcss/line-clamp')]
 */
</style>
