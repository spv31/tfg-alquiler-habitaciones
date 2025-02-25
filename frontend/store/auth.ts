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
          throw new Error("Error");
        }

        await $fetch("http://localhost:8000/api/register", {
          method: "POST",
          body: userData,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });

        await login({ email: userData.email, password: userData.password });
      } catch (error) {
        console.error(error);
        throw error;
      }
    };

    const signIn = async (credentials: { email: string; password: string }) => {
      try {
        const response = await login(credentials);
        await getUser();
      } catch (error) {
        console.error(error);
        throw error;
      }
    };

    const forgotPassword = async (email: string) => {
      try {
        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("No se pudo obtener el token CSRF.");
        }

        await $fetch("http://localhost:8000/api/forgot-password", {
          method: "POST",
          body: { email },
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });
      } catch (error) {
        console.error("Error al enviar el correo de restablecimiento:", error);
        throw error;
      }
    };

    const resetPassword = async (data: { token: string; password: string, email: string }) => {
      try {
        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("No se pudo obtener el token CSRF.");
        }

        await $fetch("http://localhost:8000/api/reset-password", {
          method: "POST",
          body: {
            email: data.email, 
            token: data.token,
            password: data.password,
          },          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });
      } catch (error) {
        console.error("Error al restablecer la contraseña:", error);
        throw error;
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
      forgotPassword, 
      resetPassword,
      signOut,
    };
  },
  {
    persist: {
      storage: localStorage,
    },
  }
);
