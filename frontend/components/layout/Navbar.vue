<template>
  <nav class="bg-white relative">
    <div
      class="max-w-screen-xl mx-auto py-5 px-5 flex items-center justify-between"
    >
      <!-- Logo -->
      <div class="flex items-center space-x-6 mr-12">
        <NuxtLink 
          v-if="authStore.isAuthenticated"
          :to="$localePath('dashboard')" 
          class="flex items-center">
          <span class="text-2xl font-medium"> MyRentHub </span>
        </NuxtLink>
        <NuxtLink 
          v-if="!authStore.isAuthenticated"
          :to="$localePath('login')" 
          class="flex items-center">
          <span class="text-2xl font-medium"> MyRentHub </span>
        </NuxtLink>
      </div>

      <!-- Opciones de navegación (desktop) -->
      <div
        class="hidden xl:flex flex-grow items-center justify-between space-x-6"
      >
        <template v-if="!authStore.isAuthenticated">
          <div class="flex space-x-6 ml-auto">
            <NuxtLink
              :to="$localePath('login')"
              class="font-medium text-base hover:text-info_dark transition-colors duration-200"
            >
              {{ $t("navbar.login") }}
            </NuxtLink>
            <NuxtLink
              :to="$localePath('/register/owner')"
              class="font-medium text-base hover:text-info_dark transition-colors duration-200"
            >
              {{ $t("navbar.register") }}
            </NuxtLink>
          </div>
        </template>

        <template v-else>
          <ul v-if="authStore.user?.role === 'owner'" class="flex space-x-6">
            <li>
              <NuxtLink
                :to="$localePath('properties')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.properties") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('statistics')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.statistics") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('contract-templates')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.contracts") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('chats')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.chats") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('profile')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.profile") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('invitations')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.invitations") }}
              </NuxtLink>
            </li>
          </ul>

          <ul v-if="authStore.user?.role === 'tenant'" class="flex space-x-6">
            <li>
              <NuxtLink
                :to="$localePath('my-home')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.tenant.home") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('payments')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.tenant.payments") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('my-contract')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.tenant.contract") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('profile')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.tenant.profile") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('support')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.tenant.support") }}
              </NuxtLink>
            </li>
          </ul>
        </template>
      </div>

      <!-- Botón de Logout (desktop) -->
      <template v-if="authStore.isAuthenticated">
        <button
          @click="logout"
          class="hidden xl:block ml-4 font-medium text-base hover:text-info_dark transition-colors duration-200"
        >
          {{ $t("navbar.logout") }}
        </button>
      </template>

      <!-- Botón de menú hamburguesa (mobile) -->
      <button @click="toggleMenu" class="text-blue-900 xl:hidden">
        <svg
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"
          />
        </svg>
      </button>
    </div>

    <!-- Menú hamburguesa (mobile) -->
    <div
      :class="[isMenuOpen ? 'block' : 'hidden', 'xl:hidden w-full bg-blue-100']"
    >
      <ul class="flex flex-col space-y-3 p-4">
        <template v-if="!authStore.isAuthenticated">
          <NuxtLink
            :to="$localePath('login')"
            class="font-medium text-base hover:text-info_dark transition-colors duration-200"
          >
            {{ $t("navbar.login") }}
          </NuxtLink>
          <NuxtLink
            :to="$localePath('/register/owner')"
            class="font-medium text-base hover:text-info_dark transition-colors duration-200"
          >
            {{ $t("navbar.register") }}
          </NuxtLink>
        </template>

        <template v-else>
          <ul v-if="authStore.user?.role === 'owner'" class="space-y-2">
            <li>
              <NuxtLink
                :to="$localePath('properties')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.properties") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('statistics')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.statistics") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('contract-templates')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.contracts") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('chats')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.chats") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('profile')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.profile") }}
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('invitations')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.invitations") }}
              </NuxtLink>
            </li>
            <button
              @click="logout"
              class="font-medium text-base hover:text-info_dark transition-colors duration-200"
            >
              {{ $t("navbar.logout") }}
            </button>
          </ul>
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