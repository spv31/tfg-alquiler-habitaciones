<template>
  <div class="mt-8 w-full max-w-2xl mx-auto">
    <TenantCard v-if="currentTenant" :tenant="currentTenant" />

    <div v-else class="p-6 bg-white/90 rounded-2xl shadow-lg border border-gray-100">
      <div class="text-center space-y-6">
        <div class="inline-block p-4 bg-info/10 rounded-full">
          <UserPlusIcon class="w-12 h-12 text-info" />
        </div>

        <h3 class="text-xl font-semibold text-gray-800">
          {{ $t('invitations.noTenantTitle') }}
        </h3>
        <p class="text-gray-600 max-w-md mx-auto">
          {{ $t('invitations.noTenantDescription') }}
        </p>

        <button v-if="currentRoom?.status === 'available'" class="button-primary px-8 py-3 rounded-xl flex items-center gap-2 mx-auto"
                @click="showForm = !showForm">
          <PlusIcon class="w-5 h-5" />
          {{ $t('invitations.inviteTenantButton') }}
        </button>
      </div>

      <transition name="slide-fade">
        <div v-if="showForm" class="mt-8 pt-8 border-t border-gray-200">
          <TenantInvitationForm
            :property-id="propertyId"
            :room-id="roomId"
            @invitationSent="handleInvitationSent"
          />
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n }            from 'vue-i18n';
import { usePropertiesStore } from '~/store/properties';
import { PlusIcon, UserPlusIcon } from '@heroicons/vue/24/outline';

const { t }  = useI18n();
const route  = useRoute();
const store  = usePropertiesStore();

const propertyId = Number(route.params.propertyId);
const roomId     = Number(route.params.roomId);     

const { currentRoom, currentTenant } = storeToRefs(store);

const showForm = ref(false);

const handleInvitationSent = () => {
  showForm.value = false;
}

onMounted(async () => {
  if (!isNaN(roomId)) {
    await store.fetchRoomTenant(propertyId, roomId);
  }
});
</script>
