<template>
  <div class="border p-6 mt-4 rounded bg-gray-50 shadow-md w-full max-w-md mx-auto">
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

    <p v-if="errorMessage" class="error-message mb-2">
      {{ errorMessage }}
    </p>

    <p v-if="successMessage" class="text-green-600 mb-2">
      {{ successMessage }}
    </p>

    <div class="flex">
      <button
        class="button-primary"
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
}>();

const invitationsStore = useInvitationsStore();
const { t: $t } = useI18n();

const email = ref("");
const successMessage = ref("");
const errorMessage = ref("");
const loading = ref(false);

const emits = defineEmits(["invitationSent"]);

/**
 * Envía la invitación al backend, usando el store de `invitations`.
 */
const handleSendInvitation = async () => {
  if (!email.value || !email.value.includes("@")) {
    errorMessage.value = $t("invitations.invalidEmailError");
    return;
  }

  successMessage.value = "";
  errorMessage.value = "";
  loading.value = true;

	console.log('Enviar invitacion');
  try {
    const response: CreateInvitationResponse =
      await invitationsStore.createInvitation({
        email: email.value,
        assignable_id: props.propertyId,
        assignable_type: "property",
      });

    successMessage.value = $t("invitations.invitationSuccess");
    email.value = "";

    emits("invitationSent", successMessage.value);
  } catch (error: any) {
    errorMessage.value =
      error?.message || $t("invitations.invitationError");
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
</style>
