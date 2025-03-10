<template>
  <section>
    <form class="card-form" @submit.prevent="handleForgotPassword">
      <h2 class="title">
        {{ $t("forgot_password.title") }}
      </h2>

      <div class="flex flex-col">
        <label class="label">{{ $t("forgot_password.email") }}</label>
        <input v-model="email" type="email" class="custom-input" />
      </div>

      <a href="#" class="link" @click.prevent="$emit('toggleForm')">
        {{ $t("forgot_password.back_to_login") }}
      </a>
			
      <Alert
        v-if="alertMessage"
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />

      <button type="submit" class="button-primary">
        {{ $t("forgot_password.submit") }}
      </button>

    </form>
  </section>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/store/auth";

const { t: $t } = useI18n();
const authStore = useAuthStore();

const email = ref("");
const alertMessage = ref<string | null>(null);
const alertType = ref<"error" | "success">("error");

const handleForgotPassword = async () => {
  if (!email.value) {
    alertMessage.value = $t("forgot_password.errors.email_required");
    alertType.value = "error";
    return;
  }

  try {
    await authStore.forgotPassword(email.value);
    alertMessage.value = $t("forgot_password.success");
    alertType.value = "success";
  } catch (error) {
    alertMessage.value = $t("forgot_password.errors.failed");
    alertType.value = "error";
  }
};
</script>

<style>
</style>