import { useAuthStore } from "~/store/auth"

export default defineNuxtPlugin(async () => {
  const auth = useAuthStore();
  try {
    await auth.getUser();
  } catch {
    await auth.signOut();
  }
});