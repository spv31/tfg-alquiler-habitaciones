<template>
  <div class="py-10">
    <div class="px-2">
    <form class="flex flex-col gap-5 p-8 px-4 max-w-2xl mx-auto bg-gray-700 shadow-lg rounded-lg text-white font-medium"
      @submit.prevent="validateForm">

      <Alert v-if="alertMessage" :message="alertMessage" :type="alertType" @close="alertMessage = ''" />

      <h2 class="text-2xl font-semibold text-center mb-1">{{ $t('register.title') }}</h2>

      <div v-if="step === 1" class="flex flex-col gap-2">
        <div class="flex items-center justify-center gap-4">
          <label class="flex items-center text-lg">
            <input type="radio" v-model="userData.user_type" value="individual"
              class="w-6 h-6 bg-gray-800 text-gray-300  mr-2" />
            {{ $t('register.individual') }}
          </label>
          <label class="flex items-center text-lg">
            <input type="radio" v-model="userData.user_type" value="company"
              class="w-6 h-6 bg-gray-800 text-gray-300 mr-2" />
            {{ $t('register.company') }}
          </label>
        </div>
        <button @click.prevent="nextStep"
          class="bg-slate-800 font-semibold text-white font-medium p-2 rounded hover:bg-slate-900 mt-4">
          {{ $t('register.continue') }}
        </button>
      </div>

      <transition name="fade">
        <div v-if="step === 2">
          <h3 class="text-xl font-semibold text-center mb-4">
            {{ $t('register.register_as') }} {{ $t(userData.user_type === 'individual' ? 'register.individual' :
              'register.company') }}
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col">
              <label class="mb-1 font-medium text-lg">{{ $t(userData.user_type === 'individual' ? 'register.full_name' :
                'register.company_name') }}</label>
              <input v-model="userData.name" type="text"
                class="p-2 rounded bg-gray-800 text-white font-medium border-none focus:ring-gray-600 outline-none"
                @input="validateField('name')" />
              <span class="text-red-600 font-medium text-m">{{ errors.name }}</span>
            </div>

            <div class="flex flex-col">
              <label class="mb-1 font-medium text-lg">{{ $t('register.email') }}</label>
              <input v-model="userData.email" type="email"
                class="p-2 rounded bg-gray-800 text-white font-medium border-none focus:ring-gray-600 outline-none"
                @input="validateField('email')" />
              <span class="text-red-600 font-medium text-m">{{ errors.email }}</span>
            </div>

            <div class="flex flex-col relative">
              <label class="mb-1 font-medium text-lg">{{ $t('register.password') }}</label>
              <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" v-model="userData.password"
                  class="p-2 rounded bg-gray-800 text-white font-medium border-none focus:ring-gray-600 outline-none w-full pr-10"
                  @input="validateField('password')" />
                <button type="button" @click="togglePasswordVisibility"
                  class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-white font-medium focus:outline-none">
                  <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'" class="text-lg"></i>
                </button>
              </div>
              <span class="text-red-600 font-medium text-m">{{ errors.password }}</span>
            </div>

            <div class="flex flex-col">
              <label class="mb-1 font-medium text-lg">{{ $t(userData.user_type === 'individual' ? 'register.id_card' :
                'register.tax_id') }}</label>
              <input v-model="userData.identifier" type="text"
                class="p-2 rounded bg-gray-800 text-white font-medium border-none focus:ring-gray-600 outline-none"
                @input="validateField('identifier')" />
              <span class="text-red-600 font-medium text-m">{{ errors.identifier }}</span>
            </div>

            <div class="flex flex-col">
              <label class="mb-1 font-medium text-lg">{{ $t('register.phone') }}</label>
              <input v-model="userData.phone_number" type="text"
                class="p-2 rounded bg-gray-800 text-white font-medium border-none focus:ring-gray-600 outline-none"
                @input="validateField('phone_number')" />
              <span class="text-red-600 font-medium text-m">{{ errors.phone_number }}</span>
            </div>

            <div class="flex flex-col">
              <label class="mb-1 font-medium text-lg">{{ $t('register.address') }}</label>
              <input v-model="userData.address" type="text"
                class="p-2 rounded bg-gray-800 text-white font-medium border-none focus:ring-gray-600 outline-none"
                @input="validateField('address')" />
              <span class="text-red-600 font-medium text-m">{{ errors.address }}</span>
            </div>
          </div>

          <div class="flex justify-between mt-4">
            <button @click.prevent="prevStep"
              class="bg-gray-500 font-semibold text-white font-medium p-2 rounded hover:bg-gray-600 px-3">
              {{ $t('register.go_back') }}
            </button>
            <button type="submit" class="bg-slate-800 font-semibold text-white font-medium p-2 rounded hover:bg-gray-900 px-3">
              {{ $t('register.sign_up') }}
            </button>
          </div>
        </div>
      </transition>
    </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { RegisterUser } from '~/types/auth';
import { useAuthStore } from '~/store/auth';
import Alert from '../ui/Alert.vue';

const { t: $t } = useI18n();

const authStore = useAuthStore();
const step = ref(1);
const showPassword = ref(false);
const alertMessage = ref<string | null>(null);
const alertType = ref<'error' | 'success'>('error');

const userData = ref<RegisterUser>({
  name: '',
  email: '',
  password: '',
  user_type: 'individual',
  identifier: '',
  profile_picture: '',
  phone_number: '',
  address: '',
});

const errors = ref<Record<string, string>>({});

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
}

const validateField = (field: keyof RegisterUser) => {
  switch (field) {
    case 'name':
      errors.value.name = userData.value.name.length < 1 ? $t('register.errors.name_required') : '';
      break;
    case 'email':
      errors.value.email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userData.value.email) ? '' : $t('register.errors.email_invalid');
      break;
    case 'password':
      errors.value.password = userData.value.password.length < 6 ? $t('register.errors.password_length') : '';
      break;
    case 'identifier':
      errors.value.identifier = userData.value.identifier === '' ? $t('register.errors.identifier_required') : '';
      break;
    case 'phone_number':
      errors.value.phone_number = /^\d+$/.test(userData.value.phone_number)
        ? ''
        : $t('register.errors.phone_required');
      break;
    case 'address':
      errors.value.address = userData.value.address.length < 5 ? $t('register.errors.address_required') : '';
      break;
  }
};

const validateForm = async () => {
  Object.keys(userData.value).forEach(field => validateField(field as keyof RegisterUser));

  if (Object.values(errors.value).some(error => error)) {
    return;
  }

  try {
    await authStore.register(userData.value);
  } catch (error) {
    alertMessage.value = $t('register.errors.registration_failed');
    alertType.value = 'error';  }

};

const nextStep = () => step.value = 2;
const prevStep = () => step.value = 1;
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
