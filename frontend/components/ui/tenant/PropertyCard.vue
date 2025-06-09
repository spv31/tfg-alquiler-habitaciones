<template>
  <div class="grid gap-6 grid-cols-1 lg:grid-cols-6 px-6 pb-6 pt-2">
    <div class="col-span-1 lg:col-span-2 flex justify-center">
      <div
        class="relative group overflow-hidden rounded-lg aspect-square min-h-[300px] w-full max-w-md"
      >
        <div
          v-if="loadingImage"
          class="absolute inset-0 bg-gray-100 animate-pulse"
        ></div>
        <div
          v-else-if="!rentable.main_image_url"
          class="h-full w-full flex flex-col items-center justify-center bg-gray-50"
        >
          <i class="pi pi-image text-4xl text-gray-300 mb-3"></i>
          <span class="text-gray-400 text-sm">Imagen no disponible</span>
        </div>
        <img
          v-else
          :src="rentable.main_image_url"
          :alt="imageAltText"
          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        />
      </div>
    </div>

    <!-- Detalles -->
    <div class="col-span-1 lg:col-span-4 flex flex-col gap-4">
      <div class="flex flex-col gap-2 border-b pb-4">
        <div class="flex items-center gap-2">
          <i
            class="pi pi-map-marker text-red-600"
            style="font-size: 1.2rem"
          ></i>
          <h2 class="text-2xl font-bold text-gray-900 leading-tight">
            {{ rentable.address }}
          </h2>
        </div>
        <div class="flex items-center gap-2">
          <Tag
            value="Vivienda completa"
            class="text-xs font-semibold py-1 px-3 rounded-full"
          />
        </div>
      </div>

      <!-- Precio y características principales -->
      <div class="space-y-4">
        <div class="prose max-w-none text-gray-600 relative">
          <div
            ref="descriptionContainer"
            class="overflow-hidden"
            :style="{ maxHeight: isExpanded ? 'none' : '6em' }"
          >
            <p class="m-0">{{ rentable.description }}</p>
          </div>
          <button
            v-if="showExpandButton"
            @click="isExpanded = !isExpanded"
            class="text-primary hover:text-primary-dark text-sm font-medium mt-1 flex items-center"
          >
            {{ isExpanded ? $t("common.readLess") : $t("common.readMore") }}
            <i
              class="pi ml-1"
              :class="isExpanded ? 'pi-chevron-up' : 'pi-chevron-down'"
            ></i>
          </button>
        </div>

        <div
          class="flex flex-col sm:flex-row justify-between items-start gap-4 p-4 bg-gray-50 rounded-xl border border-info/10 shadow-sm"
        >
          <div
            class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-wrap"
          >
            <div class="relative">
              <img
                :src="owner?.profile_image || defaultAvatar"
                :alt="owner?.name || 'Propietario'"
                class="w-14 h-14 rounded-full border-4 border-white shadow-md object-cover"
              />
              <div
                v-if="owner?.profile_image"
                class="absolute -bottom-1 -right-1 bg-green-500 p-1 rounded-full"
              >
                <i class="pi pi-check text-white text-xs"></i>
              </div>
            </div>
            <div class="flex flex-col gap-1 flex-wrap">
              <div class="flex items-center gap-2 flex-wrap">
                <p
                  class="text-gray-900 font-bold flex items-center gap-2 whitespace-nowrap"
                >
                  <i class="pi pi-user text-blue-600"></i>
                  {{ owner!.name }}
                </p>
                <Tag
                  value="Propietario"
                  severity="info"
                  class="text-xs whitespace-nowrap"
                />
              </div>
              <div class="mt-2 flex flex-col gap-1">
                <a
                  :href="`mailto:${owner!.email}`"
                  class="text-gray-700 hover:text-blue-700 text-sm flex items-center gap-2"
                >
                  <i class="pi pi-envelope text-blue-500"></i>
                  {{ owner!.email }}
                </a>
                <p
                  v-if="owner!.phone"
                  class="text-gray-700 text-sm flex items-center gap-2"
                >
                  <i class="pi pi-phone text-blue-500"></i>
                  {{ owner!.phone }}
                </p>
              </div>
            </div>
          </div>

          <Button
            icon="pi pi-comments"
            class="chat-button rounded-lg p-2 h-10"
            aria-label="Abrir chat con propietario"
            @click="emitChat"
          />
        </div>

        <!-- Detalles específicos -->
        <div
          class="grid gap-4 text-sm grid-cols-1 <!-- <640 --> md:grid-cols-2 <!-- 640–1535 --> 3xl:grid-cols-4"
        >
          <div
            class="bg-gray-50 rounded-2xl p-6 shadow-card hover:shadow-card-hover transition-all duration-300 transform hover:-translate-y-1 border border-info/10 w-full min-w-0"
          >
            <div class="grid grid-cols-[auto_1fr] grid-rows-2 gap-y-1">
              <div class="row-span-2 flex items-center justify-center mr-4">
                <div
                  class="w-14 h-14 rounded-xl bg-info/10 flex items-center justify-center"
                >
                  <i class="pi pi-box" style="font-size: 1.5rem"></i>
                </div>
              </div>
              <div class="flex items-end">
                <p
                  class="text-lg sm:text-2xl font-bold text-gray-800 break-words whitespace-normal leading-tight"
                >
                  {{ rentable.details?.property_size }}
                  <span class="text-lg font-medium text-gray-500">m²</span>
                </p>
              </div>
              <div class="flex items-start">
                <p class="text-sm font-medium text-gray-500 tracking-wider">
                  Superficie
                </p>
              </div>
            </div>
          </div>

          <div
            class="bg-gray-50 rounded-2xl p-6 shadow-card hover:shadow-card-hover transition-all duration-300 transform hover:-translate-y-1 border border-info/10 w-full min-w-0"
          >
            <div class="grid grid-cols-[auto_1fr] grid-rows-2 gap-y-1">
              <div class="row-span-2 flex items-center justify-center mr-4">
                <div
                  class="w-14 h-14 rounded-xl bg-info/10 flex items-center justify-center"
                >
                  <i class="pi pi-home" style="font-size: 1.5rem"></i>
                </div>
              </div>

              <div class="flex items-end">
                <p
                  class="text-lg sm:text-2xl font-bold text-gray-800 break-words whitespace-normal leading-none"
                >
                  {{ rentable.total_rooms }}
                </p>
              </div>

              <div class="flex items-start">
                <p
                  class="text-sm font-medium text-gray-500 tracking-wider whitespace-normal break-words"
                >
                  Habitaciones
                </p>
              </div>
            </div>
          </div>

          <div
            class="bg-gray-50 rounded-2xl p-6 shadow-card hover:shadow-card-hover transition-all duration-300 transform hover:-translate-y-1 border border-info/10"
          >
            <div class="grid grid-cols-[auto_1fr] grid-rows-2 gap-y-1">
              <div class="row-span-2 flex items-center justify-center mr-4">
                <div
                  class="w-14 h-14 rounded-xl bg-info/10 flex items-center justify-center"
                >
                  <i class="pi pi-credit-card" style="font-size: 1.5rem"></i>
                </div>
              </div>
              <div class="flex items-end">
                <p class="text-lg font-bold text-gray-800">
                  {{
                    Number(rentable.details?.rental_price).toLocaleString(
                      "es-ES",
                      { style: "currency", currency: "EUR" }
                    )
                  }}
                </p>
              </div>
              <div class="flex items-start">
                <p class="text-sm font-medium text-gray-500 tracking-wider">
                  Alquiler / mes
                </p>
              </div>
            </div>
          </div>

          <div
            class="bg-gray-50 rounded-2xl p-6 shadow-card hover:shadow-card-hover transition-all duration-300 transform hover:-translate-y-1 border border-info/10"
          >
            <div class="grid grid-cols-[auto_1fr] grid-rows-2 gap-y-1">
              <div class="row-span-2 flex items-center justify-center mr-4">
                <div
                  class="w-14 h-14 rounded-xl bg-info/10 flex items-center justify-center"
                >
                  <i class="pi pi-hashtag" style="font-size: 1.5rem"></i>
                </div>
              </div>

              <div class="flex items-end">
                <p class="text-lg font-semibold text-gray-800 break-all">
                  {{ rentable.cadastral_reference || "—" }}
                </p>
              </div>

              <div class="flex items-start">
                <p class="text-sm font-medium text-gray-500 tracking-wider">
                  Ref. catastral
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Owner } from "~/types/owner";
import type { Property } from "~/types/property";
import defaultAvatar from "~/assets/images/default.jpg";

import { useAuthStore } from '~/store/auth';
const authStore = useAuthStore();

const props = defineProps<{
  data: {
    type: "Property";
    rentable: Property;
  };
}>();

const type = computed<String>(() => props.data.type);
const rentable = computed<Property>(() => props.data.rentable);
const owner = computed(() => rentable.value.owner);

const loadingImage = ref(false);
const isExpanded = ref(false);
const showExpandButton = ref(false);
const descriptionContainer = ref<HTMLElement | null>(null);

const checkDescriptionHeight = async () => {
  await nextTick();
  if (descriptionContainer.value) {
    showExpandButton.value = descriptionContainer.value.scrollHeight > 96;
  }
};

watch(checkDescriptionHeight());
onMounted(() => {
  checkDescriptionHeight();
  console.log("Propiedad: ", rentable.value);
  console.log("Superficie: ", rentable.value.details);
  console.log("URL IMAGEN: ", rentable.value.main_image_url);
  console.log("ID Propietario: ", rentable.value.owner);
});

const imageAltText = computed(
  () => `Imagen de la propiedad ${rentable.value?.address}`
);

const emit = defineEmits<{
  (e: 'open-chat', payload: { ownerId: number; tenantId: number }): void
}>()

const emitChat = () => {
  const ownerId  = rentable.value.owner?.id;
  const tenantId = authStore.user?.id;

  console.log('Rentable value: ', rentable.value.owner);
    console.log('Rentable TENANT: ', tenantId);

  if (!ownerId || !tenantId) {
    console.warn('OwnerId o TenantId ausentes');
    return;
  }
  emit('open-chat', { ownerId, tenantId });
}
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

::v-deep .chat-button.p-button {
  @apply bg-info/10 text-gray-600 border border-info/10 !important;
}

::v-deep .chat-button.p-button:hover {
  @apply bg-info/20 text-gray-800 border-info/10 !important;
}

::v-deep .chat-button.p-button:focus {
  @apply border-info/10 !important;
}
</style>
