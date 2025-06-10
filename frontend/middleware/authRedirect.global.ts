import { useAuthStore } from "~/store/auth";

export default defineNuxtRouteMiddleware(async (to) => {
  const { $localePath } = useNuxtApp();
  const authStore = useAuthStore();

  const login = $localePath("login");
  const register = $localePath("register");
  const resetPassword = $localePath("reset-password");
  const registerOwner = $localePath("/register/owner");
  const registerTenant = $localePath("/register/tenant");

  const ownerDashboard = $localePath("dashboard");
  const tenantDashboard = $localePath("/tenant/dashboard");

  const authPages = [
    login,
    register,
    resetPassword,
    registerOwner,
    registerTenant,
  ];

  const isTenantRegistration = to.path.startsWith("/register/tenant"); 

  await authStore.getUser();

  if (
    !authStore.isAuthenticated &&
    !authPages.includes(to.path) &&
    !isTenantRegistration
  ) {
    return navigateTo(login);
  }

  if (authStore.isAuthenticated && authPages.includes(to.path)) {
    const role = authStore.user?.role;

    if (role === "owner" && to.path !== ownerDashboard) {
      return navigateTo(ownerDashboard);
    }

    if (role === "tenant" && to.path !== tenantDashboard) {
      return navigateTo(tenantDashboard);
    }
  }
});
