import { defineStore } from "pinia";
import { useSanctumAuth } from "#imports";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import type { RegisterUser } from "~/types/registerUser";
import type { CustomUser } from "~/types/customUser";
import { getCsrfToken } from "~/utils/auth";
import { tryCatch } from "~/utils/tryCatch";

export const useAuthStore = defineStore(
  "auth",
  () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseUrl;
    const { isAuthenticated, login, logout, refreshIdentity } =
      useSanctumAuth();

    const user = useSanctumUser<CustomUser>();
    const loading = ref(false);
    const userImagesCache = ref<Record<string, string>>({});

    /**
     * Gets user
     *
     * @async
     * @returns {*}
     */
    const getUser = async () => {
      const { error } = await tryCatch(async () => {
        await refreshIdentity();
      });

      if (error) {
        console.error(error);
      }
    };

    /**
     * Sends a request to the API to register a new owner
     *
     * @async
     * @param {RegisterUser} user
     * @returns {*}
     */
    const registerOwner = async (user: RegisterUser) => {
      const { error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("Error gettings CSRF Token");
        }

        await $fetch("http://localhost:8000/api/register/owner", {
          method: "POST",
          body: user,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });

        await login({ email: user.email, password: user.password });
      });

      if (error) {
        throw error;
      }
    };

    /**
     * Sends a request to the API to register a new tenant
     *
     * @async
     * @param {RegisterUser} user
     * @returns {*}
     */
    const registerTenant = async (user: RegisterUser) => {
      const { data, error } = await tryCatch(async () => {
        user.user_type = "individual";

        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("Error getting CSRF Token");
        }

        await $fetch("http://localhost:8000/api/register/tenant", {
          method: "POST",
          body: user,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });

        await login({ email: user.email, password: user.password });
      });

      if (error) {
        throw error;
      }
    };

    /**
     * Sends a request to the API to login
     *
     * @async
     * @param {{ email: string; password: string }} credentials
     * @returns {*}
     */
    const signIn = async (credentials: { email: string; password: string }) => {
      const { error } = await tryCatch(async () => {
        await login(credentials);
        await getUser();
      });

      if (error) {
        throw error;
      }
    };

    /**
     * Sends a request to the API to get email to change password
     *
     * @async
     * @param {string} email
     * @returns {*}
     */
    const forgotPassword = async (email: string) => {
      const { error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("Error getting CSRF Token");
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
      });

      if (error) {
        throw error;
      }
    };

    /**
     * Sends a request to the API to change password
     *
     * @async
     * @param {{
     *       token: string;
     *       password: string;
     *       email: string;
     *     }} data
     * @returns {*}
     */
    const resetPassword = async (data: {
      token: string;
      password: string;
      email: string;
    }) => {
      const { error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("Error getting CSRF Token");
        }

        await $fetch("http://localhost:8000/api/reset-password", {
          method: "POST",
          body: {
            email: data.email,
            token: data.token,
            password: data.password,
          },
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });
      });

      if (error) {
        throw error;
      }
    };

    /**
     * Sends a request to the API to logout
     *
     * @async
     * @returns {*}
     */
    const signOut = async () => {
      const { error } = await tryCatch(async () => {
        await logout();
      });

      if (error) {
        throw error;
      }
    };

    const fetchUserImage = async (
      userId: number,
      filename: string
    ): Promise<Blob> => {
      const { data, error } = await tryCatch<Blob>(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        const response = await fetch(
          `${apiBaseUrl}/users/${userId}/avatar/${filename}`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "image/*",
            },
          }
        );
        if (!response.ok) throw new Error("Failed to fetch user image");
        return await response.blob();
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");
      return data;
    };

    const fetchUserImageUrl = async (
      userId: number,
      filename: string,
    ) => {
      const cacheKey = `${userId}-${filename}`;

      if (userImagesCache.value[cacheKey]) {
        return userImagesCache.value[cacheKey];
      }

      try {
        const blob = await fetchUserImage(userId, filename);
        const url = URL.createObjectURL(blob);

        userImagesCache.value[cacheKey] = url;
        return url;
      } catch (error) {
        console.error("Error getting user image:", error);
        return null;
      }
    }

    return {
      user,
      isAuthenticated,
      getUser,
      registerOwner,
      registerTenant,
      signIn,
      forgotPassword,
      resetPassword,
      signOut,
      fetchUserImage,
      fetchUserImageUrl,
      userImagesCache
    };
  },
  {
    persist: {
      storage: localStorage,
    },
  }
);
