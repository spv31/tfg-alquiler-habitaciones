import { useAuthStore } from '~/store/auth';

export default defineNuxtRouteMiddleware((to) => {
  const { $localePath } = useNuxtApp();
  const authStore = useAuthStore();

  const login = $localePath('login');
  const register = $localePath('register');
  const resetPassword = $localePath('reset-password');
  const registerOwner = $localePath('/register/owner');
  const registerTenant = $localePath('/register/tenant');
  const dashboard = $localePath('dashboard');

  const authPages = [login, register, resetPassword, registerOwner, registerTenant];

  const isTenantRegistration = to.path.startsWith('/register/tenant');

  if (!authStore.isAuthenticated && !authPages.includes(to.path) && !isTenantRegistration) {
    return navigateTo(login);
  }

  if (authStore.isAuthenticated && authPages.includes(to.path)) {
    if (to.path !== dashboard) {
      return navigateTo(dashboard);
    }
  }
});
