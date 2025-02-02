import { defineStore } from "pinia";
import { useSanctumAuth } from "#imports";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import type { RegisterUser } from "~/types/auth";

export const useAuthStore = defineStore(
  "auth",
  () => {
    const { isAuthenticated, login, logout, refreshIdentity } =
      useSanctumAuth();

    const user = useSanctumUser();

    const getUser = async () => {
      try {
        await refreshIdentity();
      } catch (error) {
        console.error(error);
      }
    };

    const register = async (userData: RegisterUser) => {
      try {
        await $fetch("/register", {
          method: "POST",
          body: userData,
        });
        await login({ email: userData.email, password: userData.password });
      } catch (error) {
        console.error(error);
        throw error;
      }
    };

    const signIn = async (credentials: { email: string; password: string }) => {
      try {
        await login(credentials);
        await getUser();
      } catch (error) {
        console.error(error);
      }
    };

    const signOut = async () => {
      try {
        await logout();
      } catch (error) {
        console.error(error);
      }
    };

    return {
      user,
      isAuthenticated,
      getUser,
			register,
      signIn,
      signOut,
    };
  },
  {
    persist: {
      storage: localStorage,
    },
  }
);
