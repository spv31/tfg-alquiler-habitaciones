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

  if (auth.isAuthenticated && !user.value) {
    await auth.getUser();
  }

  const normalizedPath = stripLocalePrefix(to.path);

  if (!auth.isAuthenticated && !AUTH_PATHS.includes(normalizedPath)) {
    return navigateTo($localePath("login"));
  }
});
