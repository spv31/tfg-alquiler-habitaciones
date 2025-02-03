import { defineStore } from "pinia";
import { useSanctumAuth } from "#imports";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import type { CustomUser, RegisterUser } from "~/types/auth";
import { getCsrfToken } from "~/utils/auth";

export const useAuthStore = defineStore(
  "auth",
  () => {
    const { isAuthenticated, login, logout, refreshIdentity } =
      useSanctumAuth();

    const user = useSanctumUser<CustomUser>();

    const getUser = async () => {
      try {
        await refreshIdentity();
      } catch (error) {
        console.error(error);
      }
    };

    const register = async (userData: RegisterUser) => {
      try {
        
        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error('Error');
        }

        await $fetch("http://localhost:8000/api/register", {
          method: "POST",
          body: userData,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken, 
            "Accept": "application/json",
            "Content-Type": "application/json"
          }
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
