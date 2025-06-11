import { useAuthStore } from "~/store/auth";
import { useNuxtApp } from "#imports";
import { storeToRefs } from "pinia";

export default defineNuxtRouteMiddleware(async (to, from) => {
  const { $localePath } = useNuxtApp();
  const auth = useAuthStore();
  const { user } = storeToRefs(auth);

  const LOGIN           = $localePath("login");
  const REGISTER_OWNER  = $localePath("register-owner");
  const REGISTER_TENANT = $localePath("register-tenant");
  const RESET_PSW       = $localePath("reset-password");
  const AUTH_PAGES      = [LOGIN, REGISTER_OWNER, REGISTER_TENANT, RESET_PSW];

  const OWNER_HOME  = $localePath("properties");
  const TENANT_HOME = $localePath("tenant-dashboard");

  const localeRoot = $localePath("/");

  if (auth.isAuthenticated && !user.value) {
    await auth.getUser();
  }

  if (!auth.isAuthenticated && !AUTH_PAGES.includes(to.path)) {
    return navigateTo(LOGIN);
  }

  if (
    auth.isAuthenticated &&
    [LOGIN, REGISTER_OWNER, REGISTER_TENANT].includes(from.path) &&
    to.path === localeRoot
  ) {
    const role = user.value?.role;
    if (role === "owner")  return navigateTo(OWNER_HOME);
    if (role === "tenant") return navigateTo(TENANT_HOME);
    return;
  }

  if (auth.isAuthenticated && AUTH_PAGES.includes(to.path)) {
    const role = user.value?.role;
    if (role === "owner")  return navigateTo(OWNER_HOME);
    if (role === "tenant") return navigateTo(TENANT_HOME);
    return;
  }
});
