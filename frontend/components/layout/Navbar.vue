<template>
  <nav class="bg-white relative">
    <div class="max-w-screen-xl mx-auto p-5 flex items-center justify-between">
      <!-- Logo -->
      <div class="flex items-center space-x-6 mr-12">
        <NuxtLink :to="$localePath('dashboard')" class="flex items-center">
          <span class="self-center text-2xl font-semibold text-blue-900">MyRentHub</span>
        </NuxtLink>
      </div>

      <!-- Opciones de navegación (desktop) -->
      <div class="hidden lg:flex flex-grow items-center justify-between space-x-6">
        <template v-if="!authStore.isAuthenticated">
          <div class="flex space-x-6 ml-auto">
            <NuxtLink :to="$localePath('login')"
              class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
              {{ $t('navbar.login') }}
            </NuxtLink>
            <NuxtLink :to="$localePath('register')"
              class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
              {{ $t('navbar.register') }}
            </NuxtLink>
          </div>
        </template>

        <template v-else>
          <ul v-if="authStore.user?.role === 'owner'" class="flex space-x-6">
            <li>
              <NuxtLink :to="$localePath('properties')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.properties') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('statistics')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.statistics') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('contracts')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.contracts') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('chats')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.chats') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('profile')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.profile') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('invitations')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.invitations') }}
              </NuxtLink>
            </li>
          </ul>

          <ul v-if="authStore.user?.role === 'tenant'" class="flex space-x-6">
            <li>
              <NuxtLink :to="$localePath('my-home')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.tenant.home') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('payments')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.tenant.payments') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('my-contract')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.tenant.contract') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('profile')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.tenant.profile') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('support')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.tenant.support') }}
              </NuxtLink>
            </li>
          </ul>
        </template>
      </div>

      <!-- Botón de Logout (desktop) -->
      <template v-if="authStore.isAuthenticated">
        <button @click="logout"
          class="hidden lg:block ml-4 font-semibold text-blue-900 text-lg hover:text-blue-600 transition-colors duration-200">
          {{ $t('navbar.logout') }}
        </button>
      </template>

      <!-- Botón de menú hamburguesa (mobile) -->
      <button @click="toggleMenu" class="text-blue-900 lg:hidden">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Menú hamburguesa (mobile) -->
    <div :class="[isMenuOpen ? 'block' : 'hidden', 'lg:hidden w-full bg-blue-100 border-t border-blue-300']">
      <ul class="flex flex-col space-y-3 p-4">
        <template v-if="!authStore.isAuthenticated">
          <NuxtLink :to="$localePath('login')"
            class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
            {{ $t('navbar.login') }}
          </NuxtLink>
          <NuxtLink :to="$localePath('register')"
            class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
            {{ $t('navbar.register') }}
          </NuxtLink>
        </template>

        <template v-else>
          <ul v-if="authStore.user?.role === 'owner'" class="space-y-2">
            <li>
              <NuxtLink to="/properties"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.properties') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/statistics"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.statistics') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/contracts"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.contracts') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/chats"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.chats') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/profile"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.profile') }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink :to="$localePath('invitations')"
                class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
                {{ $t('navbar.owner.invitations') }}
              </NuxtLink>
            </li>
          </ul>
          <button @click="logout"
            class="text-blue-900 font-semibold text-lg hover:text-blue-600 transition-colors duration-200">
            {{ $t('navbar.logout') }}
          </button>
        </template>
      </ul>
    </div>
  </nav>
</template>


<script setup lang="ts">
definePageMeta({
  middleware: ['authRedirect']
});
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