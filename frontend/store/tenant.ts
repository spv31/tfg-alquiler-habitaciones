import type { PersistenceOptions } from "pinia-plugin-persistedstate";
import type { Contract } from "~/types/contract";
import type { Property } from "~/types/property";
import type { Room } from "~/types/room";

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
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseURL;
    const currentContract = ref<Contract | null>();
    const assignedRentable = ref<RentableResponse | null>();

    /**
     * Fetch assigned rentable and latest contract assigned to rental
     *
     * @async
     * @returns {*}
     */
    const fetchTenantData = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<TenantDashboardResponse>(`${apiBaseUrl}/tenant/dashboard`, {
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

			console.log('Datos recibidos: ', data);
			console.log('Contract: ', data.contract);
			console.log('Rentable: ', data.rentable);

			currentContract.value = data.contract;
      assignedRentable.value = data.rentable;
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

			console.log('Datos recibidos: ', data);
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

			console.log('Datos recibidos: ', data);
			assignedRentable.value = data;
    };

    return {
      currentContract,
      assignedRentable,
      fetchTenantData,
			fetchTenantContract,
			fetchTenantRentable
    };
  },

  {
    persist: {
      storage: sessionStorage,
      pick: ["currentContract", "assignedRentable"],
    } satisfies PersistenceOptions,
  }
);
