import { useAuthStore } from "~/store/auth";

export default defineNuxtRouteMiddleware((to) => {
  const { locale, localePath } = useI18n();
  const authStore = useAuthStore();

  if (!authStore.isAuthenticated() && (to.path === '/login' || to.path === '/register')) {
    return navigateTo(localePath(to.path));
  }

  if (authStore.isAuthenticated && to.path === localePath('login')) {
    return navigateTo(localePath('dashboard'));
  }
})