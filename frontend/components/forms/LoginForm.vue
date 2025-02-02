<template>
	<div class="py-10">
		<div class="px-2">
			<form v-if="!forgotPasswordMode"
				class="flex flex-col gap-5 p-8 px-4 max-w-md mx-auto bg-gray-700 shadow-lg rounded-lg text-white"
				@submit.prevent="handleLogin">

				<Alert v-if="alertMessage" :message="alertMessage" :type="alertType" @close="alertMessage = ''" />

				<h2 class="text-2xl font-semibold text-center mb-1">{{ $t('login.title') }}</h2>

				<div class="flex flex-col">
					<label class="mb-1 font-medium">{{ $t('login.email') }}</label>
					<input v-model="email" type="email"
						class="p-2 rounded bg-gray-800 text-white border-none focus:ring-gray-600 outline-none" />
				</div>

				<div class="flex flex-col relative">
					<label class="mb-1 font-medium">{{ $t('login.password') }}</label>
					<div class="relative">
						<input :type="showPassword ? 'text' : 'password'" v-model="password"
							class="p-2 rounded bg-gray-800 text-white border-none focus:ring-gray-600 outline-none w-full pr-10" />
						<button type="button" @click="togglePasswordVisibility"
							class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-white focus:outline-none">
							<i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'" class="text-lg"></i>
						</button>
					</div>
				</div>

				<button type="submit" class="bg-slate-800 font-semibold text-white p-2 rounded hover:bg-slate-900">
					{{ $t('login.sign_in') }}
				</button>

				<p class="text-center text-sm mt-2 text-gray-400">
					<a href="#" @click.prevent="toggleForgotPassword" class="hover:text-white">{{ $t('login.forgot_password')
						}}</a>
				</p>
			</form>

			<form v-else class="flex flex-col gap-5 p-8 px-4 max-w-md mx-auto bg-gray-700 shadow-lg rounded-lg text-white"
				@submit.prevent="handleForgotPassword">

				<Alert v-if="alertMessage" :message="alertMessage" :type="alertType" @close="alertMessage = ''" />

				<h2 class="text-2xl font-semibold text-center mb-1">{{ $t('forgot_password.title') }}</h2>

				<div class="flex flex-col">
					<label class="mb-1 font-medium">{{ $t('forgot_password.email') }}</label>
					<input v-model="email" type="email"
						class="p-2 rounded bg-gray-800 text-white border-none focus:ring-gray-600 outline-none" />
				</div>

				<button type="submit" class="bg-slate-800 font-semibold text-white p-2 rounded hover:bg-slate-900">
					{{ $t('forgot_password.submit') }}
				</button>

				<p class="text-center text-sm mt-2 text-gray-400">
					<a href="#" @click.prevent="toggleForgotPassword" class="hover:text-white">{{
						$t('forgot_password.back_to_login') }}</a>
				</p>
			</form>
		</div>
	</div>
</template>

<script setup lang="ts">
import Alert from '../ui/Alert.vue';
import { useAuthStore } from '~/store/auth';

const { t: $t } = useI18n();

const authStore = useAuthStore();
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const forgotPasswordMode = ref(false);
const alertMessage = ref<string | null>(null);
const alertType = ref<'error' | 'success'>('error');

const togglePasswordVisibility = () => {
	showPassword.value = !showPassword.value;
};

const toggleForgotPassword = () => {
	forgotPasswordMode.value = !forgotPasswordMode.value;
	alertMessage.value = null;
};

const handleLogin = async () => {
	if (!email.value || !password.value) {
		alertMessage.value = $t('login.errors.fields_required');
		alertType.value = 'error';
		return;
	}

	try {
		await authStore.signIn({ email: email.value, password: password.value });
		alertMessage.value = $t('login.success');
		alertType.value = 'success';
	} catch (error) {
		alertMessage.value = $t('login.errors.invalid_credentials');
		alertType.value = 'error';
	}
};

const handleForgotPassword = async () => {
	if (!email.value) {
		alertMessage.value = $t('forgot_password.errors.email_required');
		alertType.value = 'error';
		return;
	}

	try {
		await authStore.forgotPassword(email.value);
		alertMessage.value = $t('forgot_password.success');
		alertType.value = 'success';
	} catch (error) {
		alertMessage.value = $t('forgot_password.errors.failed');
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
