<template>
  <nav class="bg-white relative">
    <div
      class="max-w-screen-xl mx-auto py-4 px-5 flex items-center justify-between"
    >
      <div class="flex items-center space-x-6 mr-12">
        <NuxtLink
          v-if="authStore.isAuthenticated"
          :to="
            authStore.user?.role === 'tenant'
              ? $localePath('/tenant/dashboard')
              : $localePath('dashboard')
          "
          class="flex items-center"
        >
          <img
            src="/logo.png"
            alt="MyRentHub Logo"
            class="h-16 w-auto mt-[-16px]"
          />
        </NuxtLink>
        <NuxtLink v-else :to="$localePath('login')" class="flex items-center">
          <img src="/logo.png" alt="MyRentHub Logo" class="h-16 w-auto" />
        </NuxtLink>
      </div>

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

        <template v-else-if="authStore.user?.role === 'owner'">
          <ul class="flex space-x-6">
            <li>
              <NuxtLink
                :to="$localePath('properties')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
                >{{ $t("navbar.owner.properties") }}</NuxtLink
              >
            </li>
            <li>
              <NuxtLink
                :to="$localePath('statistics')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
                >{{ $t("navbar.owner.statistics") }}</NuxtLink
              >
            </li>
            <li>
              <NuxtLink
                :to="$localePath('contract-templates')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
                >{{ $t("navbar.owner.contracts") }}</NuxtLink
              >
            </li>
            <li>
              <NuxtLink
                :to="$localePath('profile')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
                >{{ $t("navbar.owner.profile") }}</NuxtLink
              >
            </li>
          </ul>
        </template>

        <template v-else>
          <ul class="flex space-x-6">
            <li>
              <NuxtLink
                :to="$localePath('/tenant/dashboard')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                Mi alquiler
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                :to="$localePath('profile')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
                >{{ $t("navbar.tenant.profile") }}</NuxtLink
              >
            </li>
          </ul>
        </template>
      </div>

      <template v-if="authStore.isAuthenticated">
        <button
          @click="logout"
          class="hidden xl:block ml-4 font-medium text-base hover:text-info_dark transition-colors duration-200"
        >
          {{ $t("navbar.logout") }}
        </button>
      </template>

      <button @click="toggleMenu" class="text-blue-900 xl:hidden">
        <svg
          class="w-8 h-8"
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

    <div
      :class="[isMenuOpen ? 'block bg-gray-50' : 'hidden', 'xl:hidden w-full']"
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
                :to="$localePath('profile')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                {{ $t("navbar.owner.profile") }}
              </NuxtLink>
            </li>
            <button
              @click="logout"
              class="font-medium text-base hover:text-info_dark transition-colors duration-200"
            >
              {{ $t("navbar.logout") }}
            </button>
          </ul>

          <ul v-else class="space-y-2">
            <li>
              <NuxtLink
                :to="$localePath('/tenant/dashboard')"
                class="font-medium text-base hover:text-info_dark transition-colors duration-200"
              >
                Mi alquiler
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
