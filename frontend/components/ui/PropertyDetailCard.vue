<template>
  <div>
    <h1 class="text-3xl font-bold text-center mb-8">
      {{ $t("properties.detail.title") }}
    </h1>
    <div v-if="!showStats">
      <div
        class="bg-white/90 rounded-3xl shadow-lg overflow-hidden flex flex-col md:flex-row gap-8 p-8"
      >
        <div
          class="md:w-1/2 gradient-card h-64 lg:h-96 rounded-3xl flex items-center justify-center"
        >
          <img
            v-if="propertyImage"
            :src="propertyImage"
            alt="Imagen de la propiedad"
            class="object-cover w-full h-full rounded-3xl"
          />
          <div v-else class="text-center space-y-3">
            <svg
              class="h-12 w-12 text-info/30 mx-auto"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M4 16l4.586-4.586a2 2 0 012.828
             0L16 16m-2-2l1.586-1.586a2 2 0 012.828
             0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V7a2
             2 0 00-2-2H6a2 2 0 00-2 2v11"
              />
            </svg>
            <span class="text-info/30 text-sm font-medium">
              {{ $t("properties.detail.noImage") }}
            </span>
          </div>
        </div>

        <!-- Detalles -->
        <div class="md:w-1/2 space-y-2">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 mb-2">
                {{ property.address }}
              </h2>
              <div class="flex flex-col sm:flex-row sm:items-center gap-1">
                <span
                  :class="statusBadgeClasses(property.status)"
                  class="px-3 py-1.5 text-xs font-semibold rounded-full uppercase tracking-wide"
                >
                  {{ statusLabel(property.status, $t) }}
                </span>
                <span
                  :class="[
                    rentalBadgeClasses(property.rental_type),
                    'px-3 py-1 text-xs font-semibold rounded-full uppercase tracking-wide',
                  ]"
                >
                  {{ rentalTypeLabel(property.rental_type, $t) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Descripción -->
          <dl class="grid grid-cols-2 gap-2">
            <div class="col-span-2">
              <dt class="label">
                {{ $t("properties.detail.description") }}
              </dt>
              <dd
                class="mt-1 text-base font-normal text-gray-600 transition-all duration-300"
                :class="{ 'line-clamp-4': !isExpanded }"
              >
                {{
                  property.description || $t("properties.detail.noDescription")
                }}
              </dd>
              <button
                v-if="!isExpanded"
                @click="isExpanded = true"
                class="text-blue-500 hover:underline mt-1 font-medium text-sm"
              >
                {{ $t("common.readMore") }}
              </button>
              <button
                v-else
                @click="isExpanded = false"
                class="text-blue-500 hover:underline font-medium mt-1 text-sm"
              >
                {{ $t("common.readLess") }}
              </button>
            </div>
            <div>
              <dt class="label">
                {{ $t("properties.detail.cadastral") }}
              </dt>
              <dd class="mt-1 text-gray-600 font-medium">
                {{ property.cadastral_reference }}
              </dd>
            </div>
            <div>
              <dt class="label">
                {{ $t("properties.detail.totalRooms") }}
              </dt>
              <dd class="mt-1 text-gray-600 font-medium">
                {{ property.total_rooms }}
              </dd>
            </div>
          </dl>

          <!-- Acciones -->
          <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
            <NuxtLink :to="`${property.id}/edit`">
              <CircleIconButton :label="$t('common.edit')">
                <template #icon>
                  <svg
                    class="h-5 w-5 text-blue-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15.232 5.232l3.536 3.536m-2.036-5.036
             a2.5 2.5 0 113.536 3.536L6.5
             21.036H3v-3.572L16.732 3.732z"
                    />
                  </svg>
                </template>
              </CircleIconButton>
            </NuxtLink>

            <CircleIconButton
              :label="$t('properties.detail.addStatsButton')"
              @click="showStats = true"
            >
              <template #icon>
                <svg
                  class="h-6 w-6 text-indigo-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 3v18h18M18 9h-6m0
               0V3m0 6l-6 6"
                  />
                </svg>
              </template>
            </CircleIconButton>

            <CircleIconButton
              v-if="isStatusChangeable"
              :label="
                property.status === 'available'
                  ? $t('properties.detail.makeUnavailableButton')
                  : $t('properties.detail.makeAvailableButton')
              "
              @click="showChangeStatusModal = true"
            >
              <template #icon>
                <svg
                  class="h-6 w-6 text-red-600 mx-auto"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 20V4M5 11l7-7 7 7"
                  />
                </svg>
              </template>
            </CircleIconButton>

            <CircleIconButton
              :label="$t('properties.detail.deleteButton')"
              @click="showDeleteModal = true"
            >
              <template #icon>
                <svg
                  class="h-6 w-6 text-red-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </template>
            </CircleIconButton>
          </div>

          <ConfirmDeleteModal
            :show="showDeleteModal"
            @confirm="handleDeleteProperty"
            @cancel="showDeleteModal = false"
          />
        </div>
      </div>
    </div>
    <PropertyStatsCard
      v-else
      :details="property.details"
      @close="showStats = false"
    />

    <StatusChangeModal
      :show="showChangeStatusModal"
      :currentStatus="property.status"
      @cancel="showChangeStatusModal = false"
      @confirm="handleChangeStatus"
    />
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { usePropertiesStore } from "~/store/properties";
import {
  statusBadgeClasses,
  statusLabel,
  rentalBadgeClasses,
  rentalTypeLabel,
} from "~/utils/badges";

const { t: $t } = useI18n();

const store = usePropertiesStore();
const props = defineProps({
  property: {
    type: Object,
    required: true,
  },
  propertyImage: {
    type: String,
    default: null,
  },
});

const showDeleteModal = ref(false);
const isExpanded = ref(false);
const showStats = ref(false);
const showChangeStatusModal = ref(false);

const isStatusChangeable = computed(() => {
  return (
    props.property.status === "available" ||
    props.property.status === "unavailable"
  );
});

const handleDeleteProperty = async () => {
  try {
    await store.deleteProperty(props.propertyId);
    alert($t("propertyDeleted"));
  } catch (error) {
    alert($t("deleteError"));
  }
  showDeleteModal.value = false;
};

const handleChangeStatus = async () => {
  try {
    const newStatus =
      props.property.status === "available" ? "unavailable" : "available";
    await store.changePropertyStatus(props.property.id, newStatus);
    showChangeStatusModal.value = false;
    await store.fetchProperty(props.property.id);

    if (props.property.rental_type === 'per_room') {
      await store.fetchRooms(props.property.id);
    }
  } catch (error) {
    alert($t("common.errorUpdatingStatus"));
  }
};

</script>
<style scoped>
</style>