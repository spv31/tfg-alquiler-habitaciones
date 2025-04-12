<template>
  <div
    class="bg-white/90 shadow-xl rounded-xl p-8 flex flex-col items-start transition-all hover:shadow-2xl group"
  >
    <h2 class="title text-center w-full text-2xl font-bold text-gray-800 mb-6">
      {{ $t("common.tenantData") }}
    </h2>

    <div class="relative mb-6 self-center">
      <img
        :src="tenant.profile_picture || defaultAvatar"
        :alt="tenant.name"
        class="w-28 h-28 rounded-full object-cover shadow-sm"
      />
    </div>

    <div class="w-full space-y-4 mb-6">
      <div class="space-y-1">
        <label class="label text-gray-600">{{ $t("common.name") }}</label>
        <p class="text-base text-gray-900 font-medium">{{ tenant.name }}</p>
      </div>

      <div class="space-y-1">
        <label class="label text-gray-600">{{ $t("common.email") }}</label>
        <p class="text-base text-gray-900 break-all">{{ tenant.email }}</p>
      </div>

      <div class="space-y-1" v-if="tenant.phone_number">
        <label class="label text-gray-600">{{ $t("common.phone") }}</label>
        <p class="text-base text-gray-900 flex items-center gap-2">
          <span class="text-purple-600">📞</span>
          {{ tenant.phone_number }}
        </p>
      </div>
    </div>
    <div
      class="flex flex-wrap gap-3 pt-4 border-t border-gray-200 w-full justify-start"
    >
      <CircleIconButton :label="$t('common.chat')" @click="onChat">
        <template #icon>
          <img
            src="@/assets/icons/chat.svg"
            class="w-6 h-6"
            alt="Icono Cerrar"
          />
        </template>
      </CircleIconButton>

      <CircleIconButton :label="$t('common.move')" @click="moveTenant">
        <template #icon>
          <svg
            class="h-5 w-5 text-purple-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 15l7-7 7 7"
            />
          </svg>
        </template>
      </CircleIconButton>

      <CircleIconButton :label="$t('common.remove')" @click="onRemove">
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
              d="M19 7l-.867 
                 12.142A2 2 0 0116.138 21H7.862a2 2 0 
                 01-1.995-1.858L5 7m5 
                 4v6m4-6v6m1-10V4a1 1 
                 0 00-1-1h-4a1 1 
                 0 00-1 1v3M4 
                 7h16"
            />
          </svg>
        </template>
      </CircleIconButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Tenant } from "~/types/tenant";
import defaultAvatar from "~/assets/images/default.jpg";

const { locale } = useI18n();

const props = defineProps<{
  tenant: Tenant;
}>();

onMounted(() => {
  console.log("Tenant ID: ", props.tenant.id);
});

const onChat = () => {};

const onRemove = () => {
  try {
  } catch (error) {
    console.error(error);
  }
};

const moveTenant = () => {
  navigateTo(`/${locale.value}/tenants/${props.tenant.id}/move`);
}
</script>

<style scoped>
</style>
