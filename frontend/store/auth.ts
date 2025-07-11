import { defineStore } from "pinia";
import { useSanctumAuth } from "#imports";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import type { RegisterUser } from "~/types/registerUser";
import type { CustomUser } from "~/types/customUser";
import { getCsrfToken } from "~/utils/auth";
import { tryCatch } from "~/utils/tryCatch";
import { useContractsStore } from "./contracts";
import { useChatStore } from "./chat";
import { useInvitationsStore } from "./invitations";
import { usePropertiesStore } from "./properties";
import { useTenantStore } from "./tenant";
import { useStatisticsStore } from "./statistics";
import type { User } from "~/types/user";
import { data } from "autoprefixer";
import { usePaymentsStore } from "./payments";

export const useAuthStore = defineStore(
  "auth",
  () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseURL;
    const { isAuthenticated, login, logout, refreshIdentity } =
      useSanctumAuth();

    const user = useSanctumUser<User>() as Ref<User | null>;
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
        if (user.value && "data" in user.value) {
          user.value = user.value.data as User;
        }
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
     * Updates profile data
     * @param payload
     */
    const updateProfile = async (payload: {
      name?: string;
      user_type?: "individual" | "company";
      phone_number?: string | null;
      address?: string | null;
    }) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        const formData = new FormData();
        formData.append("_method", "PUT");

        for (const [key, value] of Object.entries(payload)) {
          if (value != null) {
            formData.append(key, value.toString());
          }
        }

        return await $fetch<{ data: User }>(
          `${apiBaseUrl}/users/${user.value!.id}`,
          {
            method: "POST",
            body: formData,
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrf,
              Accept: "application/json",
            },
          }
        );
      });

      if (error) throw error;
      if (data) user.value = data.data;
    };

    /**
     * Updates profile avatar
     * @param base64
     */
    const changeAvatar = async (base64: string) => {
      const { error, data } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return await $fetch<{ data: User }>(
          `${apiBaseUrl}/users/${user.value!.id}/avatar`,
          {
            method: "POST",
            body: { image_base64: base64 },
            credentials: "include",
            headers: { "X-XSRF-TOKEN": csrf },
          }
        );
      });

      if (error) throw error;
      if (data) user.value = data.data;
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
     * Resend email verification
     */
    const resendVerification = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        await $fetch(
          "http://localhost:8000/api/email/verification-notification",
          {
            method: "POST",
            credentials: "include",
            headers: { "X-XSRF-TOKEN": csrf },
          }
        );
        if (error) throw error;
      })
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
        sessionStorage.clear();
        localStorage.clear();
        const authStore = useAuthStore();
        const chatStore = useChatStore();
        const contractStore = useContractsStore();
        const invitationsStore = useInvitationsStore();
        const propertiesStore = usePropertiesStore();
        const tenantStore = useTenantStore();
        const statisticsStore = useStatisticsStore();
        const paymentsStore = usePaymentsStore();

        authStore.reset();
        chatStore.reset();
        contractStore.reset();
        invitationsStore.reset();
        propertiesStore.reset();
        tenantStore.reset();
        statisticsStore.reset();
        paymentsStore.reset();

        useChatStore().$reset();
        useContractsStore().$reset();
        useInvitationsStore().$reset();
        usePropertiesStore().$reset();
        useTenantStore().$reset();
        usePaymentsStore().$reset();
      });

      if (error) {
        throw error;
      }
    };

    const fetchUserImage = async (
      userId: number,
      filename: string
    ): Promise<Blob> => {
      const { data: any, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        await $fetch(`${apiBaseUrl}/users/${userId}/avatar/${filename}`, {
          method: "GET",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "image/*",
          },
        });
        if (error) throw error;
        return await data.blob();
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");
      return data;
    };

    const fetchUserImageUrl = async (userId: number, filename: string) => {
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
    };

    const reset = () => {
      user.value = null;
      userImagesCache.value = {};
    };

    return {
      user,
      isAuthenticated,
      getUser,
      registerOwner,
      registerTenant,
      updateProfile,
      changeAvatar,
      signIn,
      forgotPassword,
      resetPassword,
      signOut,
      fetchUserImage,
      fetchUserImageUrl,
      userImagesCache,
      resendVerification,
      reset,
    };
  },
  {
    persist: {
      storage: localStorage,
      pick: ["userImagesCache"],
    },
  }
);
