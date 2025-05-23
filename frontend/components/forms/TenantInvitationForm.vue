<template>
  <div class="border p-6 mt-4 rounded-2xl bg-gray-50 shadow-md w-full max-w-md mx-auto">
    <div class="flex flex-col mb-3">
      <label for="tenantEmail" class="label">
        {{ $t("invitations.emailLabel") }}
      </label>
      <input
        id="tenantEmail"
        type="email"
        class="custom-input"
        v-model="email"
        :placeholder="$t('invitations.emailPlaceholder')"
      />
    </div>

    <div class="flex justify-center">
      <button
        class="button-primary w-full"
        @click="handleSendInvitation"
        :disabled="loading"
      >
        <span v-if="!loading">{{ $t("invitations.sendButton") }}</span>
        <span v-else>{{ $t("invitations.sending") }}...</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useInvitationsStore } from "~/store/invitations";
import type { CreateInvitationResponse } from "~/types/invitation";

const props = defineProps<{
  propertyId: number;
  roomId?: number;
}>();

const invitationsStore = useInvitationsStore();
const { t: $t } = useI18n();
const toast = useMyToast();

const email = ref("");
const loading = ref(false);

const emits = defineEmits(["invitationSent"]);

const handleSendInvitation = async () => {
  if (!email.value || !email.value.includes("@")) {
    toast.error($t("invitations.invalidEmailError"));
    return;
  }

  loading.value = true;

  try {
    const response: CreateInvitationResponse =
      await invitationsStore.createInvitation({
        email: email.value,
        property_id: props.propertyId,
        room_id: props.roomId,
      });

    toast.success($t("invitations.invitationSuccess"));
    email.value = "";
    emits("invitationSent");
  } catch (error: any) {
    toast.error(error?.message || $t("invitations.invitationError"));
  } finally {
    loading.value = false;
  }
};
</script>
