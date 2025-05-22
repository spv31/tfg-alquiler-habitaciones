<template>
  <div>
    <h1 class="text-3xl font-bold text-center mb-8">
      {{ $t("properties.detail.room") }} {{ room.room_number }}
    </h1>

    <div v-if="!showStats">
      <div
        class="bg-white/90 rounded-3xl shadow-lg overflow-hidden flex flex-col md:flex-row gap-8 p-8"
      >
        <div
          class="md:w-1/2 gradient-card h-64 lg:h-96 rounded-3xl flex items-center justify-center"
        >
          <img
            v-if="roomImage"
            :src="roomImage"
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
                d="M4 16l4.586-4.586a2 2
                   0 012.828 0L16 16m-2-2l1.586-1.586a2
                   2 0 012.828 0L20 14m-6-6h.01M6 20h12
                   a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2
                   0 00-2 2v11"
              />
            </svg>
            <span class="text-info/30 text-sm font-medium">
              {{ $t("properties.detail.noImageRoom") }}
            </span>
          </div>
        </div>

        <!-- Detalles de la habitación -->
        <div class="md:w-1/2 space-y-2">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 mb-2">
                {{ $t("properties.detail.room") }} {{ room.room_number }}
              </h2>
              <!-- Badges de estado, etc. -->
              <div class="flex flex-col sm:flex-row sm:items-center gap-1">
                <span
                  :class="statusBadgeClasses(room.status)"
                  class="px-3 py-1.5 text-xs font-semibold rounded-full uppercase tracking-wide"
                >
                  {{ statusLabel(room.status, t) }}
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
                {{ room.description || $t("properties.detail.noDescription") }}
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
                {{ t("properties.detail.rentalPriceLabel") }}
              </dt>
              <dd class="mt-1 text-gray-600 font-medium">
                {{
                  t("properties.detail.rentalPriceValue", {
                    price: room.rental_price,
                  })
                }}
              </dd>
            </div>
          </dl>

          <!-- Acciones -->
          <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
            <NuxtLink :to="editRoomLink" class="flex items-center">
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
                      d="M15.232 5.232l3.536 3.536m-2.036
                         -5.036a2.5 2.5 0 113.536 3.536L6.5
                         21.036H3v-3.572L16.732 3.732z"
                    />
                  </svg>
                </template>
              </CircleIconButton>
            </NuxtLink>

            <CircleIconButton
              :label="
                room.status === 'available'
                  ? $t('properties.detail.rooms.makeUnavailableButton')
                  : $t('properties.detail.rooms.makeAvailableButton')
              "
              @click="handleChangeStatus"
            >
              <template #icon>
                <i
                  :class="[
                    'pi',
                    room.status === 'available'
                      ? 'pi-lock text-red-500'
                      : 'pi-unlock text-info',
                    'text-xl',
                  ]"
                />
              </template>
            </CircleIconButton>

            <CircleIconButton
              :label="$t('properties.detail.deleteButton')"
              @click="handleDeleteRoom"
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { statusBadgeClasses, statusLabel } from "~/utils/badges";
import { usePropertiesStore } from "../../store/properties";
import { useMyToast, useMyConfirm } from "#imports";

const props = defineProps<{
  room: any;
  roomImage: {
    type: String;
    default: null;
  };
}>();

const propertiesStore = usePropertiesStore();
const { t, locale } = useI18n();

const toast = useMyToast();
const confirm = useMyConfirm();

const isExpanded = ref(false);
const showStats = ref(false);

const editRoomLink = computed(() => {
  return `/properties/${props.room.property_id}/rooms/${props.room.id}/edit`;
});

const handleDeleteRoom = () => {
  confirm.show({
    message: t("properties.detail.rooms.deleteQuestion"),
    header: t("common.confirmation"),
    icon: "pi pi-exclamation-triangle text-red-500 text-xl",
    acceptLabel: t("common.delete"),
    rejectLabel: t("common.cancel"),
    acceptSeverity: "danger",
    onAccept: async () => {
      try {
        await propertiesStore.deleteRoom(props.room.property_id, props.room.id);
        toast.success(t("properties.detail.rooms.deleteSuccess"));
        navigateTo(
          `/${locale.value}/properties/${props.room.property_id}?msg=room_deleted`
        );
      } catch {
        toast.error(t("common.errorDeleting"));
      }
    },
  });
};

const handleChangeStatus = () => {
  const newStatus =
    props.room.status === "available" ? "unavailable" : "available";

  confirm.show({
    message: t("properties.detail.rooms.statusQuestion", { status: newStatus }),
    header: t("common.confirmation"),
    icon: "pi pi-info-circle text-info text-xl",
    acceptLabel: t("common.accept"),
    rejectLabel: t("common.cancel"),
    acceptSeverity: "info",
    onAccept: async () => {
      try {
        await propertiesStore.changeRoomStatus(
          props.room.property_id,
          props.room.id,
          newStatus
        );
        toast.success(t("properties.detail.statusSuccess"));

        await propertiesStore.fetchRoom(props.room.property_id, props.room.id);
      } catch (e: any) {
        if (e.message === "property_status_change_forbidden_due_to_tenant") {
          toast.warn(t("properties.detail.statusChangeForbidden"));
        } else {
          toast.error(t("common.errorUpdatingStatus"));
        }
      }
    },
  });
};
</script>

<style scoped>
.line-clamp-4 {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
