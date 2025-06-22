<template>
  <section>
    <form class="card-form" @submit.prevent="handleLogin">
      <h2 class="title">{{ $t("login.title") }}</h2>

      <div class="flex flex-col">
        <label class="label">{{ $t("login.email") }}</label>
        <input
          v-model="email"
          type="email"
          class="custom-input"
          placeholder="example@domain.com"
        />
      </div>

      <div class="flex flex-col relative">
        <label class="label">{{ $t("login.password") }}</label>
        <div class="relative">
          <input
            :type="showPassword ? 'text' : 'password'"
            v-model="password"
            class="custom-input-password"
            placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;"
          />
          <button
            type="button"
            @click="togglePasswordVisibility"
            class="button-password"
          >
            <i
              :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"
              class="icon-password"
            ></i>
          </button>
        </div>
      </div>
      <a href="#" @click.prevent="$emit('toggleForm')" class="link">
        {{ $t("login.forgot_password") }}
      </a>

      <Alert
        v-if="alertMessage"
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />

      <button type="submit" class="button-primary">
        {{ $t("login.sign_in") }}
      </button>
    </form>
  </section>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/store/auth";
import { useRouter, useNuxtApp } from "#imports";

const { t: $t } = useI18n();
const { $localePath } = useNuxtApp();
const authStore = useAuthStore();
const router = useRouter();

const { user } = storeToRefs(authStore);

const email = ref("");
const password = ref("");
const showPassword = ref(false);
const alertMessage = ref<string | null>(null);
const alertType = ref<"error" | "success">("error");

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const handleLogin = async () => {
  if (!email.value || !password.value) {
    alertMessage.value = $t("login.errors.fields_required");
    alertType.value = "error";
    return;
  }

  try {
    await authStore.signIn({ email: email.value, password: password.value });
    
    const role = user.value?.role;
    if (role === "owner") {
      router.push($localePath("/statistics"));
    } else if (role === "tenant") {
      router.push($localePath("/tenant/dashboard"));
    } else {
      router.push($localePath("/"));
    }
  } catch (error) {
    alertMessage.value = $t("login.errors.invalid_credentials");
    alertType.value = "error";
  }
};
</script>

<style></style>
