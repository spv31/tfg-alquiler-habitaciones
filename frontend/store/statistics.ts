import type { PersistenceOptions } from "pinia-plugin-persistedstate";
import type { DashboardSummary, FiscalData } from "~/types/statistics";

export const useStatisticsStore = defineStore(
  "statistics",
  () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseURL;

    const dashboard = ref<DashboardSummary | null>(
      null
    ) as Ref<DashboardSummary | null>;
    const fiscal = ref<FiscalData | null>(null) as Ref<FiscalData | null>;
    const loading = ref(false);

    const buildFiscalParams = (from?: string, to?: string) => {
      const params = new URLSearchParams();
      if (from) params.append("from", from);
      if (to) params.append("to", to);
      return params.toString() ? `?${params}` : "";
    };

    /**
     * Loads data for dashboard
     */
    const loadDashboard = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<DashboardSummary>(`${apiBaseUrl}/owner/statistics`, {
          method: "GET",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");

      dashboard.value = data as unknown as DashboardSummary;
    };

    /**
     * Loads the fiscal data for a given period
     *
     * @param from
     * @param to
     */
    const loadFiscal = async (from?: string, to?: string) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<FiscalData>(
          `${apiBaseUrl}/owner/fiscal${buildFiscalParams(from, to)}`,
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

      fiscal.value = data as unknown as FiscalData;
    };

    const reset = () => {
      dashboard.value = null;
      fiscal.value = null;
    };

    return {
      dashboard,
      fiscal,
      loading,
      loadDashboard,
      loadFiscal,
      reset,
    };
  },
  {
    persist: {
      storage: sessionStorage,
      pick: ["dashboard", "fiscal"],
    } as PersistenceOptions,
  }
);
