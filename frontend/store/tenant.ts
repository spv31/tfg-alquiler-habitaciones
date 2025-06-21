import type { PersistenceOptions } from "pinia-plugin-persistedstate";
import type { Contract } from "~/types/contract";
import type { Property } from "~/types/property";
import type { Room } from "~/types/room";
import { useAuthStore } from "./auth";
import { usePaymentsStore } from "~/store/payments";

type RentableType = "Property" | "Room";

interface RentableResponse {
  type: RentableType;
  rentable: Property | Room;
}

type TenantDashboardResponse = {
  contract: Contract | null;
  rentable: RentableResponse | null;
};

export const useTenantStore = defineStore(
  "tenant",
  () => {
    const paymentsStore = usePaymentsStore();
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseURL;
    const currentContract = ref<Contract | null>();
    const assignedRentable = ref<RentableResponse | null>();

    const alreadyLoaded = ref(false);
    const loading = ref(false);

    /**
     * Fetch assigned rentable and latest contract assigned to rental
     *
     * @async
     * @returns {*}
     */
    const fetchTenantData = async (): Promise<any> => {
      if (alreadyLoaded.value || loading.value) return;

      await nextTick();
      if (assignedRentable.value || currentContract.value) {
        alreadyLoaded.value = true;
        return;
      }

      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<TenantDashboardResponse>(
          `${apiBaseUrl}/tenant/dashboard`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrf,
              Accept: "application/json",
            },
          }
        );
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");

      currentContract.value = data.contract;
      assignedRentable.value = data.rentable
        ? normalizeRentable(data.rentable, apiBaseUrl)
        : null;

      if ((data as any).bill_shares) {
        const { data: shares, meta } = (data as any).bill_shares;
        paymentsStore.setTenantBillShares(shares, meta);
      }

      alreadyLoaded.value = true;
    };

    /**
     * Fetch latest contract assigned
     *
     * @async
     * @returns {*}
     */
    const fetchTenantContract = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<Contract>(`${apiBaseUrl}/tenant/contract`, {
          method: "GET",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      });

      if (error) throw error;
      if (!data) throw new Error("No data received");

      currentContract.value = data;
    };

    const fetchTenantRentable = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<RentableResponse>(`${apiBaseUrl}/tenant/rentable`, {
          method: "GET",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      });

      if (error) throw error;
      if (!data) throw new Error("No data received");

      assignedRentable.value = normalizeRentable(data, apiBaseUrl);
    };

    const getFileName = (url?: string | null): string | null => {
      if (!url) return null;
      try {
        const path = new URL(url).pathname;
        return path.split("/").filter(Boolean).at(-1) ?? null;
      } catch {
        return url.split("/").filter(Boolean).at(-1) ?? null;
      }
    };

    /**
     * It returns the API rest to get property/room image
     *
     * @param rentable
     * @param fileName
     * @param apiBase
     * @returns
     */
    const buildImageUrl = (
      rentable: RentableResponse,
      fileName: string,
      apiBase: string
    ): string => {
      if (rentable.type === "Property") {
        const prop = rentable.rentable as Property;
        return `${apiBase}/properties/${prop.id}/images/${fileName}`;
      }

      const room = rentable.rentable as Room;
      return `${apiBase}/properties/${room.property_id}/rooms/${room.id}/images/${fileName}`;
    };

    /**
     * Limpia y completa la info de la imagen principal.
     * – main_image_file => solo nombre (opcional)
     * – main_image_url  => URL final a tu API (lista para <img :src>)
     */

    /**
     * It cleans and gets main image info
     *
     * @param incoming
     * @param apiBase
     * @returns
     */
    const normalizeRentable = (
      incoming: RentableResponse,
      apiBase: string
    ): RentableResponse => {
      if (!incoming?.rentable || !("main_image_url" in incoming.rentable)) {
        return incoming;
      }

      const fileName = getFileName(
        (incoming.rentable as any).main_image_url as string | undefined
      );

      if (!fileName) return incoming;

      const cloned: RentableResponse = JSON.parse(JSON.stringify(incoming));

      (cloned.rentable as any).main_image_file = fileName;
      (cloned.rentable as any).main_image_url = buildImageUrl(
        cloned,
        fileName,
        apiBase
      );

      return cloned;
    };

    const reset = () => {
      currentContract.value = null;
      assignedRentable.value = null;
      alreadyLoaded.value = false;
    };

    return {
      currentContract,
      assignedRentable,
      fetchTenantData,
      fetchTenantContract,
      fetchTenantRentable,
      loading,
      alreadyLoaded,
      reset,
    };
  },

  {
    persist: {
      storage: sessionStorage,
      pick: ["currentContract", "assignedRentable"],
    } satisfies PersistenceOptions,
  }
);
