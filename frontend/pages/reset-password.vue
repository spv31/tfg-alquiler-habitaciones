<template>
  <div class="py-10">
    <div class="px-2">
      <form
        class="flex flex-col gap-5 p-8 px-4 max-w-md mx-auto bg-gray-700 shadow-lg rounded-lg text-white"
        @submit.prevent="handleResetPassword"
      >
        <Alert v-if="alertMessage" :message="alertMessage" :type="alertType" @close="alertMessage = ''" />

        <h2 class="text-2xl font-semibold text-center mb-1">{{ $t('reset_password.title') }}</h2>

        <!-- Nueva contraseña -->
        <div class="flex flex-col relative">
          <label class="mb-1 font-medium">{{ $t('reset_password.new_password') }}</label>
          <div class="relative">
            <input
              :type="showPassword.new ? 'text' : 'password'"
              v-model="password"
              class="p-2 rounded bg-gray-800 text-white border-none focus:ring-gray-600 outline-none w-full pr-10"
            />
            <button
              type="button"
              @click="togglePasswordVisibility('new')"
              class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-white focus:outline-none"
            >
              <i :class="showPassword.new ? 'bi bi-eye-slash' : 'bi bi-eye'" class="text-lg"></i>
            </button>
          </div>
        </div>

        <!-- Confirmar contraseña -->
        <div class="flex flex-col relative">
          <label class="mb-1 font-medium">{{ $t('reset_password.confirm_password') }}</label>
          <div class="relative">
            <input
              :type="showPassword.confirm ? 'text' : 'password'"
              v-model="confirmPassword"
              class="p-2 rounded bg-gray-800 text-white border-none focus:ring-gray-600 outline-none w-full pr-10"
            />
            <button
              type="button"
              @click="togglePasswordVisibility('confirm')"
              class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-white focus:outline-none"
            >
              <i :class="showPassword.confirm ? 'bi bi-eye-slash' : 'bi bi-eye'" class="text-lg"></i>
            </button>
          </div>
          <!-- Error de contraseñas no coinciden -->
          <span v-if="passwordMismatch" class="text-sm text-red-500 mt-1">
            {{ $t('reset_password.errors.password_mismatch') }}
          </span>
        </div>

        <button type="submit" class="bg-slate-800 font-semibold text-white p-2 rounded hover:bg-slate-900">
          {{ $t('reset_password.submit') }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/store/auth';
import Alert from '~/components/ui/Alert.vue';

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const password = ref('');
const confirmPassword = ref('');
const showPassword = reactive({ new: false, confirm: false }); // Estado independiente para cada input
const alertMessage = ref<string | null>(null);
const alertType = ref<'error' | 'success'>('error');
const passwordMismatch = computed(() => password.value !== confirmPassword.value && confirmPassword.value.length > 0);

const togglePasswordVisibility = (field: 'new' | 'confirm') => {
  showPassword[field] = !showPassword[field];
};

const handleResetPassword = async () => {
  if (!password.value || !confirmPassword.value) {
    alertMessage.value = $t('reset_password.errors.fields_required');
    alertType.value = 'error';
    return;
  }

  if (passwordMismatch.value) {
    alertMessage.value = $t('reset_password.errors.password_mismatch');
    alertType.value = 'error';
    return;
  }

  try {
    const token = route.query.token as string;
    const email = route.query.email as string; 
    if (!token) {
      throw new Error('Token inválido');
    }

    if (!email) {
      throw new Error('Email inválido');
    }

    await authStore.resetPassword({ token, password: password.value, email });

    alertMessage.value = $t('reset_password.success');
    alertType.value = 'success';

    setTimeout(() => {
      router.push('/login');
    }, 2000);
  } catch (error) {
    alertMessage.value = $t('reset_password.errors.failed');
    alertType.value = 'error';
  }
};
</script>

<style>
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
