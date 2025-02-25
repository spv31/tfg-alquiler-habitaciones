<template>
  <nav class="bg-white border-b-2 border-gray-200">
    <div class="max-w-screen-xl flex items-center justify-between mx-auto p-4">
      <!-- Logo -->
      <div class="flex items-center space-x-6 mr-12">
        <NuxtLink to="/dashboard" class="flex items-center">
          <span class="self-center text-2xl font-semibold text-gray-800">MyRentHub</span>
        </NuxtLink>
      </div>

      <!-- Opciones de navegación en desktop -->
      <div class="hidden lg:flex flex-grow items-center justify-between space-x-6">
        <template v-if="!authStore.isAuthenticated">
          <div class="flex space-x-6 ml-auto">
            <NuxtLink 
              to="/login"
              class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
            >
              {{ $t('navbar.login') }}
            </NuxtLink>
            <NuxtLink 
              to="/register"
              class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
            >
              {{ $t('navbar.register') }}
            </NuxtLink>
          </div>
        </template>

        <template v-else>
          <ul v-if="authStore.user?.role === 'owner'" class="flex space-x-6">
            <li>
              <NuxtLink 
                to="/properties" 
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.properties') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink 
                to="/statistics" 
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.statistics') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink 
                to="/contracts"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.contracts') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink 
                to="/chats"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.chats') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/profile"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.profile') }}
              </NuxtLink>
            </li>
          </ul>

          <ul v-if="authStore.user?.role === 'tenant'" class="flex space-x-6">
            <li>
              <NuxtLink
                to="/my-home"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.tenant.home') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/payments"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.tenant.payments') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/my-contract"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.tenant.contract') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/profile"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.tenant.profile') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/support"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.tenant.support') }}
              </NuxtLink>
            </li>
          </ul>
        </template>
      </div>

      <!-- Botón de Logout (desktop) -->
      <template v-if="authStore.isAuthenticated">
        <button
          @click="logout"
          class="hidden lg:block ml-4 font-semibold text-gray-800 text-lg 
                 border border-blue-500 px-4 py-2 rounded
                 hover:bg-blue-500 hover:text-white transition-colors duration-200"
        >
          {{ $t('navbar.logout') }}
        </button>
      </template>

      <!-- Botón de menú hamburguesa (mobile) -->
      <button @click="toggleMenu" class="text-gray-800 lg:hidden">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Menú hamburguesa (mobile) -->
    <div :class="[isMenuOpen ? 'block' : 'hidden', 'lg:hidden w-full bg-gray-50 border-t border-gray-200']">
      <ul class="flex flex-col space-y-3 p-4">

        <template v-if="!authStore.isAuthenticated">
          <NuxtLink 
            to="/login" 
            class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
          >
            {{ $t('navbar.login') }}
          </NuxtLink>
          <NuxtLink 
            to="/register" 
            class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
          >
            {{ $t('navbar.register') }}
          </NuxtLink>
        </template>

        <template v-else>
          <!-- Propietarios -->
          <ul v-if="authStore.user?.role === 'owner'" class="space-y-2">
            <li>
              <NuxtLink 
                to="/properties" 
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.properties') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/statistics"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.statistics') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/contracts"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.contracts') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/chats"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.chats') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/profile"
                class="text-gray-800 font-semibold text-lg hover:text-blue-500 transition-colors duration-200"
              >
                {{ $t('navbar.owner.profile') }}
              </NuxtLink>
            </li>
          </ul>

          <!-- Logout en mobile -->
          <button
            @click="logout"
            class="text-gray-800 font-semibold text-lg border border-blue-500 px-4 py-1 rounded
                   hover:bg-blue-500 hover:text-white transition-colors duration-200"
          >
            {{ $t('navbar.logout') }}
          </button>
        </template>
      </ul>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue';
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
