<template>
	<nav class="bg-gray-800 border-b-2 border-gray-600">
		<div class="max-w-screen-xl flex items-center justify-between mx-auto p-4">
			<!-- Logo y enlaces de navegación -->
			<div class="flex items-center space-x-6">
				<NuxtLink to="/" class="flex items-center">
					<img class="h-8 mr-3" alt="Logo" />
					<span class="self-center text-2xl font-semibold text-white">MyRentHub</span>
				</NuxtLink>
				<ul class="hidden md:flex space-x-4">
					<li>
						<NuxtLink to="/" class="block py-2 px-4 font-medium text-white">Inicio</NuxtLink>
					</li>
					<li>
						<NuxtLink to="/services" class="block py-2 px-4 font-medium text-white">Servicios</NuxtLink>
					</li>
				</ul>
			</div>

			<div class="flex items-center space-x-4">
				<NuxtLink to="/login"
					class="bg-gray-500 hover:bg-gray-700 py-2 px-4 rounded font-medium text-white hidden md:flex items-center">
					Inicia Sesión
				</NuxtLink>
				<NuxtLink to="/register"
					class="bg-gray-500 hover:bg-gray-700 py-2 px-4 rounded font-medium text-white hidden md:block">
					Regístrate
				</NuxtLink>
				<button @click="toggleMenu" class="text-white md:hidden">
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
					</svg>
				</button>
			</div>
		</div>

		<div :class="[isMenuOpen ? 'block' : 'hidden', 'md:hidden w-full bg-gray-900']">
			<ul class="flex flex-col space-y-2 p-4">
				<li>
					<NuxtLink to="/" class="block py-2 px-4 font-medium text-white">Inicio</NuxtLink>
				</li>
				<li>
					<NuxtLink to="/services" class="block py-2 px-4 font-medium text-white">Servicios</NuxtLink>
				</li>
				<NuxtLink to="/login"
					class="bg-gray-500 hover:bg-gray-700 py-2 px-4 rounded font-medium text-white flex items-center">
					Inicia Sesión
				</NuxtLink>
				<NuxtLink to="/register" class="bg-gray-500 hover:bg-gray-700 py-2 px-4 rounded font-medium text-white">
					Regístrate
				</NuxtLink>
			</ul>
		</div>
	</nav>
</template>

<script setup lang="ts">
const isMenuOpen = ref(false);
const toggleMenu = () => {
	isMenuOpen.value = !isMenuOpen.value;
};
</script>
