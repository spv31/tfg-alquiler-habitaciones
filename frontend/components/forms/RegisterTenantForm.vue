<template>
  <section>
    <form class="card-form max-w-xl" @submit.prevent="validateForm">
      <Alert
        v-if="alertMessage"
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />

      <h2 class="title">
        {{ $t("register_tenant.title") }}
      </h2>

      <p class="description text-center">
        {{ $t("register_tenant.subtitle") }}
      </p>

      <div class="flex flex-col">
        <label class="label">{{ $t("register_tenant.full_name") }}</label>
        <input
          v-model="userData.name"
          type="text"
          class="custom-input"
          placeholder="e.g. John Doe"
          @input="validateField('name')"
        />
        <span class="error-message">{{ errors.name }}</span>
      </div>

      <div class="flex flex-col">
        <label class="label">{{ $t("register_tenant.email") }}</label>
        <input
          v-model="userData.email"
          type="email"
          class="custom-input"
          placeholder="e.g. example@domain.com"
          @input="validateField('email')"
        />
        <span class="error-message">{{ errors.email }}</span>
      </div>

      <div class="flex flex-col relative">
        <label class="label">{{ $t("register_tenant.password") }}</label>
        <div class="relative">
          <input
            :type="showPassword ? 'text' : 'password'"
            v-model="userData.password"
            class="custom-input-password"
            placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;"
            @input="validateField('password')"
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
        <span class="error-message">{{ errors.password }}</span>
      </div>

      <div class="flex flex-col">
        <label class="label">{{ $t("register_tenant.id_card") }}</label>
        <input
          v-model="userData.identifier"
          type="text"
          class="custom-input"
          placeholder="e.g. ID / Tax ID"
          @input="validateField('identifier')"
        />
        <span class="error-message">{{ errors.identifier }}</span>
      </div>

      <div class="flex flex-col">
        <label class="label">{{ $t("register_tenant.phone") }}</label>
        <input
          v-model="userData.phone_number"
          type="text"
          class="custom-input"
          placeholder="e.g. +1 234 567 890"
          @input="validateField('phone_number')"
        />
        <span class="error-message">{{ errors.phone_number }}</span>
      </div>

      <button type="submit" class="button-primary mt-4">
        {{ $t("register_tenant.sign_up") }}
      </button>
    </form>
  </section>
</template>

<script setup lang="ts">
import type { RegisterUser } from "~/types/registerUser";
import { useAuthStore } from "~/store/auth";

const { t: $t } = useI18n();
const authStore = useAuthStore();
const showPassword = ref(false);
const alertMessage = ref<string | null>(null);
const alertType = ref<"error" | "success">("error");

const route = useRoute();
const emailFromUrl = route.query.email as string | undefined;
const tokenFromUrl = route.query.token as string | undefined;

const userData = ref<RegisterUser>({
  name: "",
  email: emailFromUrl || "",
  password: "",
  user_type: "individual", 
  identifier: "",
  profile_picture: "",
  phone_number: "",
  address: "", 
	token: tokenFromUrl || "",
});

const errors = ref<Record<string, string>>({});

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const validateField = (field: keyof RegisterUser) => {
  switch (field) {
    case "name":
      errors.value.name =
        userData.value.name.length < 1
          ? $t("register_tenant.errors.name_required")
          : "";
      break;
    case "email":
      errors.value.email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
        userData.value.email
      )
        ? ""
        : $t("register_tenant.errors.email_invalid");
      break;
    case "password":
      errors.value.password =
        userData.value.password.length < 6
          ? $t("register_tenant.errors.password_length")
          : "";
      break;
    case "identifier":
      errors.value.identifier =
        userData.value.identifier === ""
          ? $t("register_tenant.errors.identifier_required")
          : "";
      break;
    case "phone_number":
      errors.value.phone_number = /^\d+$/.test(userData.value.phone_number)
        ? ""
        : $t("register_tenant.errors.phone_required");
      break;
  }
};

const validateForm = async () => {
  Object.keys(userData.value).forEach((field) =>
    validateField(field as keyof RegisterUser)
  );

  if (Object.values(errors.value).some((error) => error)) {
    return;
  }

  try {
    await authStore.registerTenant(userData.value);
  } catch (error) {
    alertMessage.value = $t("register_tenant.errors.registration_failed");
    alertType.value = "error";
  }
};
</script>

<style scoped>

</style>
