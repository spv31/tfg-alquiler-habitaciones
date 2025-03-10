<template>
  <section>
    <form class="card-form-two-columns" @submit.prevent="validateForm">
      <Alert
        v-if="alertMessage"
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />

      <h2 class="title">
        {{ $t("register.title") }}
      </h2>

      <div v-if="step === 1" class="flex flex-col">
        <div class="flex items-center justify-center gap-4 mb-4">
          <label class="flex items-center text-lg">
            <input
              type="radio"
              v-model="userData.user_type"
              value="individual"
              class="w-6 h-6 bg-gray-200 text-gray-900 mr-2"
            />
            {{ $t("register.individual") }}
          </label>
          <label class="flex items-center text-lg">
            <input
              type="radio"
              v-model="userData.user_type"
              value="company"
              class="w-6 h-6 bg-gray-200 text-gray-900 mr-2"
            />
            {{ $t("register.company") }}
          </label>
        </div>
        <button @click.prevent="nextStep" class="button-primary">
          {{ $t("register.continue") }}
        </button>
      </div>

      <transition name="fade">
        <div v-if="step === 2">
          <h3 class="text-xl font-semibold text-center mb-4">
            {{ $t("register.register_as") }}
            {{
              $t(
                userData.user_type === "individual"
                  ? "register.individual"
                  : "register.company"
              )
            }}
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col">
              <label class="label">
                {{
                  $t(
                    userData.user_type === "individual"
                      ? "register.full_name"
                      : "register.company_name"
                  )
                }}
              </label>
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
              <label class="label">{{ $t("register.email") }}</label>
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
              <label class="label">{{ $t("register.password") }}</label>
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
              <label class="label">
                {{
                  $t(
                    userData.user_type === "individual"
                      ? "register.id_card"
                      : "register.tax_id"
                  )
                }}
              </label>
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
              <label class="label">{{ $t("register.phone") }}</label>
              <input
                v-model="userData.phone_number"
                type="text"
                class="custom-input"
                placeholder="e.g. +1 234 567 890"
                @input="validateField('phone_number')"
              />
              <span class="error-message">{{ errors.phone_number }}</span>
            </div>

            <div class="flex flex-col">
              <label class="label">{{ $t("register.address") }}</label>
              <input
                v-model="userData.address"
                type="text"
                class="custom-input"
                placeholder="e.g. 123 Main St"
                @input="validateField('address')"
              />
              <span class="error-message">{{ errors.address }}</span>
            </div>
          </div>

          <div class="flex justify-between mt-4">
            <button @click.prevent="prevStep" class="button-back">
              <i class="bi bi-arrow-left"></i>
              {{ $t("register.go_back") }}
            </button>

            <button type="submit" class="button-primary">
              {{ $t("register.sign_up") }}
            </button>
          </div>
        </div>
      </transition>
    </form>
  </section>
</template>

<script setup lang="ts">
import type { RegisterUser } from "~/types/registerUser";
import { useAuthStore } from "~/store/auth";

const { t: $t } = useI18n();

const authStore = useAuthStore();
const step = ref(1);
const showPassword = ref(false);
const alertMessage = ref<string | null>(null);
const alertType = ref<"error" | "success">("error");

const userData = ref<RegisterUser>({
  name: "",
  email: "",
  password: "",
  user_type: "individual",
  identifier: "",
  profile_picture: "",
  phone_number: "",
  address: "",
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
          ? $t("register.errors.name_required")
          : "";
      break;
    case "email":
      errors.value.email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
        userData.value.email
      )
        ? ""
        : $t("register.errors.email_invalid");
      break;
    case "password":
      errors.value.password =
        userData.value.password.length < 6
          ? $t("register.errors.password_length")
          : "";
      break;
    case "identifier":
      errors.value.identifier =
        userData.value.identifier === ""
          ? $t("register.errors.identifier_required")
          : "";
      break;
    case "phone_number":
      errors.value.phone_number = /^\d+$/.test(userData.value.phone_number)
        ? ""
        : $t("register.errors.phone_required");
      break;
    case "address":
      errors.value.address =
        userData.value.address.length < 5
          ? $t("register.errors.address_required")
          : "";
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
    await authStore.registerOwner(userData.value);
  } catch (error) {
    alertMessage.value = $t("register.errors.registration_failed");
    alertType.value = "error";
  }
};

const nextStep = () => (step.value = 2);
const prevStep = () => (step.value = 1);
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

input {
  transition: all 0.3s ease-in-out;
  border: 2px solid transparent;
}

input:focus {
  outline: none;
  border-color: gray;
  box-shadow: 0 0 5px rgba(156, 163, 175, 0.5);
}

input:hover {
  border-color: rgb(209, 213, 219);
}
</style>
