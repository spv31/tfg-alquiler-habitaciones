<template>
	<nav class="bg-gray-800 border-b-2 border-gray-600">
		<div class="max-w-screen-xl flex items-center justify-between mx-auto p-4">
			<!-- Logo -->
			<div class="flex items-center space-x-6 mr-12">
				<NuxtLink to="/dashboard" class="flex items-center">
					<span class="self-center text-2xl font-semibold text-white">MyRentHub</span>
				</NuxtLink>
			</div>

			<div class="hidden lg:flex flex-grow items-center justify-between space-x-6">
				<!-- Opciones de navegación -->
				<template v-if="!authStore.isAuthenticated">
					<div class="flex space-x-6 ml-auto">
						<NuxtLink to="/login" class="text-white font-semibold text-lg hover:text-gray-300">
							{{ $t('navbar.login') }}
						</NuxtLink>
						<NuxtLink to="/register" class="text-white font-semibold text-lg hover:text-gray-300">
							{{ $t('navbar.register') }}
						</NuxtLink>
					</div>
				</template>

				<template v-else>
					<ul v-if="authStore.user?.role === 'owner'" class="flex space-x-6">
						<li>
							<NuxtLink to="/properties" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.properties') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/statistics" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.statistics') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/contracts" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.contracts') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/chats" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.chats') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/profile" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.profile') }}
							</NuxtLink>
						</li>
					</ul>

					<ul v-if="authStore.user?.role === 'tenant'" class="flex space-x-6">
						<li>
							<NuxtLink to="/my-home" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.tenant.home') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/payments" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.tenant.payments') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/my-contract" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.tenant.contract') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/profile" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.tenant.profile') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/support" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.tenant.support') }}
							</NuxtLink>
						</li>
					</ul>
				</template>
			</div>

			<!-- Botón de Logout (pantallas grandes únicamente) -->
			<template v-if="authStore.isAuthenticated">
				<button @click="logout"
					class="hidden lg:block text-white font-semibold text-lg border border-gray-500 px-4 py-2 rounded hover:bg-gray-600 transition">
					{{ $t('navbar.logout') }}
				</button>
			</template>

			<!-- Botón de menú hamburguesa (pantallas pequeñas) -->
			<button @click="toggleMenu" class="text-white lg:hidden">
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
				</svg>
			</button>
		</div>

		<!-- Menú hamburguesa (móvil y hasta lg) -->
		<div :class="[isMenuOpen ? 'block' : 'hidden', 'lg:hidden w-full bg-gray-900']">
			<ul class="flex flex-col space-y-3 p-4">
				<template v-if="!authStore.isAuthenticated">
					<NuxtLink to="/login" class="text-white font-semibold text-lg hover:text-gray-300">
						{{ $t('navbar.login') }}
					</NuxtLink>
					<NuxtLink to="/register" class="text-white font-semibold text-lg hover:text-gray-300">
						{{ $t('navbar.register') }}
					</NuxtLink>
				</template>

				<template v-else>
					<!-- Opciones para Propietarios -->
					<ul v-if="authStore.user?.role === 'owner'" class="space-y-2">
						<li>
							<NuxtLink to="/properties" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.properties') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/statistics" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.statistics') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/contracts" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.contracts') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/chats" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.chats') }}
							</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/profile" class="text-white font-semibold text-lg hover:text-gray-300">
								{{ $t('navbar.owner.profile') }}
							</NuxtLink>
						</li>
					</ul>

					<!-- Botón de Logout dentro del menú hamburguesa -->
					<button @click="logout"
						class="text-white font-semibold text-lg border border-gray-500 px-4 py-1 rounded hover:bg-gray-600 transition">
						{{ $t('navbar.logout') }}
					</button>
				</template>
			</ul>
		</div>

	</nav>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/store/auth";

const authStore = useAuthStore();
const { t: $t } = useI18n();
const isMenuOpen = ref(false);

const logout = async () => {
	await authStore.signOut();
};

const toggleMenu = () => {
	isMenuOpen.value = !isMenuOpen.value;
};
</script>
