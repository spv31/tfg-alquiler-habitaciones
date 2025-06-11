import { useAuthStore } from "~/store/auth";
import { useNuxtApp } from "#imports";
import { storeToRefs } from "pinia";

const stripLocalePrefix = (path: string) => {
  return path.replace(/^\/[a-z]{2}(\/|$)/, "/");
};

export default defineNuxtRouteMiddleware(async (to, from) => {
  const { $localePath } = useNuxtApp();
  const auth = useAuthStore();
  const { user } = storeToRefs(auth);

  const AUTH_PATHS = [
    "/login",
    "/register/owner",
    "/register/tenant",
    "/reset-password",
  ];

  const OWNER_HOME = $localePath("properties");
  const TENANT_HOME = $localePath("tenant-dashboard");

  const localeRoot = $localePath("/");

  if (auth.isAuthenticated && !user.value) {
    await auth.getUser();
  }

  const normalizedPath = stripLocalePrefix(to.path);
  const fromNormalizedPath = stripLocalePrefix(from.path);

  if (!auth.isAuthenticated && !AUTH_PATHS.includes(normalizedPath)) {
    return navigateTo($localePath("login"));
  }

  if (
    auth.isAuthenticated &&
    AUTH_PATHS.includes(fromNormalizedPath) &&
    to.fullPath === localeRoot
  ) {
    const role = user.value?.role;
    if (role === "owner") return navigateTo(OWNER_HOME);
    if (role === "tenant") return navigateTo(TENANT_HOME);
    return;
  }

  if (auth.isAuthenticated && AUTH_PATHS.includes(normalizedPath)) {
    const role = user.value?.role;
    if (role === "owner") return navigateTo(OWNER_HOME);
    if (role === "tenant") return navigateTo(TENANT_HOME);
    return;
  }
});
