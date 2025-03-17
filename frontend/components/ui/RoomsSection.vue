<template>
  <section class="mt-12">

    <div class="relative mb-4">
      <h2 class="text-xl font-bold text-center">
        {{ $t("properties.detail.roomsTitle") }}
      </h2>

      <div
        class="hidden md:block absolute top-1/2 right-0 transform -translate-y-1/2"
      >
        <span class="text-gray-500 text-sm">
          {{ rooms.length }} {{ $t("properties.detail.rooms.title") }}
        </span>
      </div>

      <div class="block md:hidden text-right mt-2">
        <span class="text-gray-500 text-sm">
          {{ rooms.length }} {{ $t("properties.detail.rooms.title") }}
        </span>
      </div>
    </div>

    <div v-if="computedWarningText" class="flex justify-center">
      <div class="w-full">
        <Alert
          :message="computedWarningText"
          type="warning"
          class="mb-4"
          @close="warningMessage = null"
        />
      </div>
    </div>

    <div
      v-if="rooms.length > 0"
      class="flex flex-wrap justify-center gap-6"
    >
      <div 
        v-for="room in rooms" 
        :key="room.id"
        class="w-full md:w-[calc(50%_-_0.75rem)] lg:w-[calc(33.333%_-_1rem)]"
      >
        <RoomCard
          :room="room"
          @send-invitation="onSendInvitation"
        />
      </div>
    </div>

    <div v-else class="mt-2 text-center">
      <div
        class="bg-blue-50 p-6 rounded-2xl border border-blue-200 inline-block"
      >
        <p class="text-blue-600 font-medium">
          {{ $t("properties.detail.noRoomsPrompt") }}
        </p>
      </div>
    </div>


    <div
			v-if="computedWarningText" 
			class="mt-6 text-center">
      <div
        class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition border-2 border-dashed border-blue-200 inline-block"
        @click="goToAddRoom"
      >
        <div
          class="h-full flex flex-col items-center justify-center p-8 text-center cursor-pointer"
        >
          <div class="mb-4 bg-blue-100 p-4 rounded-full">
            <svg
              class="h-8 w-8 text-blue-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"
              />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            {{ $t("properties.detail.addRoomButton") }}
          </h3>
          <p class="text-gray-500 text-sm">
            {{ $t("properties.detail.addRoomHelpText") }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

const props = defineProps<{
  rooms: any[];
  propertyId: number;
  warning?: {
    key: string;
    parms: {
      total_expected: number;
      current: number;
    };
  } | null;
}>();
const { t, locale } = useI18n();

const warningMessage = ref(props.warning || "");

const computedWarningText = computed(() => {
  if (!props.warning) return "";

  const difference = props.warning.parms.total_expected - props.warning.parms.current;

	if (difference === 1) {
    return t("properties.detail.rooms.missing_rooms_warning_singular");
  } else {
    return t("properties.detail.rooms.missing_rooms_warning_plural", {
      missing: difference
    });
  }
});

const goToAddRoom = () => {
  navigateTo(`/${locale.value}/properties/${props.propertyId}/rooms/add`);
};

const onSendInvitation = (roomId: number) => {
  console.log("Enviar invitación para la habitación", roomId);
};
</script>
