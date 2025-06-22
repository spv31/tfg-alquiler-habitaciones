import type { PersistenceOptions } from "pinia-plugin-persistedstate";

import type { UtilityBill, UtilityBillFilters } from "~/types/utilityBill";
import type { BillShare } from "~/types/billShare";
import type { RentPayment, RentPaymentFilters } from "~/types/rentPayment";
import type { Payment, PaymentFilters } from "~/types/payment";

export interface PendingItem {
  id: number;
  source: "share" | "rent";
  concept: string;
  amount: number;
  status: "pending" | "paid" | "cancelled" | "processing" | "overdue";
  due_date: string;
  paid_at: string | null;
}

export const usePaymentsStore = defineStore(
  "payments",
  () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseURL;
    const loading = ref(false);

    const utilityBills = ref<UtilityBill[]>([]);
    const billShares = ref<Record<number, BillShare[]>>({});
    const rentPayments = ref<RentPayment[]>([]);
    const payments = ref<Payment[]>([]);

    const utilityBillsMeta = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    });

    const emptyMeta = () => ({
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    });

    const tenantBillShares = ref<BillShare[]>([]);
    const tenantBillSharesMeta = reactive(emptyMeta());
    const tenantRentPayments = ref<RentPayment[]>([]);
    const tenantRentPaymentsMeta = reactive(emptyMeta());

    const movements = computed<PendingItem[]>(() => [
      ...tenantBillShares.value.map<PendingItem>((s) => ({
        id: s.id,
        source: "share",
        concept: s.utility_bill?.description ?? "Suministro",
        amount: +s.amount,
        status: s.status,
        due_date: s.utility_bill?.due_date!,
        paid_at: s.paid_at,
      })),
      ...tenantRentPayments.value.map<PendingItem>((r) => ({
        id: r.id,
        source: "rent",
        concept: "Renta mensual",
        amount: +r.amount,
        status: r.status,
        due_date: r.due_date,
        paid_at: r.paid_at,
      })),
    ]);

    const pending = computed(() =>
      movements.value.filter((m) => m.status === "pending")
    );
    const history = computed(() => movements.value);

    // Utility Bills
    const fetchUtilityBills = async (
      filters: UtilityBillFilters = {},
      page = utilityBillsMeta.current_page,
      per = utilityBillsMeta.per_page
    ) => {
      const params: Record<string, string> = {
        page: String(page),
        per_page: String(per),
      };

      Object.entries(filters).forEach(([key, val]) => {
        if (val != null) {
          params[key] = String(val);
        }
      });

      const query = new URLSearchParams(params).toString();
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{
          data: UtilityBill[];
          meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
          };
        }>(`${apiBaseUrl}/utility-bills${query ? `?${query}` : ""}`, {
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

      utilityBills.value = data.data;
      Object.assign(utilityBillsMeta, data.meta);
    };

    const fetchUtilityBill = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: UtilityBill }>(
          `${apiBaseUrl}/utility-bills/${id}`,
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

      const bill = data.data;
      const idx = utilityBills.value.findIndex((b) => b.id === bill.id);
      if (idx !== -1) utilityBills.value[idx] = bill;
      else utilityBills.value.unshift(bill);
      return bill;
    };

    const createUtilityBill = async (payload: FormData) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: UtilityBill }>(`${apiBaseUrl}/utility-bills`, {
          method: "POST",
          body: payload,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      }, loading);
      if (error) throw error;
      if (!data) throw new Error("No data received");

      console.log("Recibo: ", data.data);
      return data.data;
    };

    const updateUtilityBill = async (
      id: number,
      payload: Record<string, any>
    ) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: UtilityBill }>(
          `${apiBaseUrl}/utility-bills/${id}`,
          {
            method: "PUT",
            body: payload,
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

      const bill = data.data;
      const idx = utilityBills.value.findIndex((b) => b.id === bill.id);
      if (idx !== -1) utilityBills.value[idx] = bill;
      return bill;
    };

    const deleteUtilityBill = async (id: number) => {
      const { error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch(`${apiBaseUrl}/utility-bills/${id}`, {
          method: "DELETE",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      }, loading);
      if (error) throw error;

      utilityBills.value = utilityBills.value.filter((b) => b.id !== id);
    };

    // Bill Shares
    const fetchBillShares = async (billId: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: BillShare[] }>(
          `${apiBaseUrl}/utility-bills/${billId}/shares`,
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

      billShares.value[billId] = data.data;
    };

    const createBillShare = async (
      billId: number,
      payload: { tenant_id: number; amount: number }
    ) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: BillShare }>(
          `${apiBaseUrl}/utility-bills/${billId}/shares`,
          {
            method: "POST",
            body: payload,
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

      billShares.value[billId] = [
        ...(billShares.value[billId] || []),
        data.data,
      ];
      return data.data;
    };

    const payBillShare = async (shareId: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ sessionId: string }>(
          `${apiBaseUrl}/bill-shares/${shareId}/pay`,
          {
            method: "POST",
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

      return data.sessionId;
    };

    // Rent Payments
    const fetchRentPayments = async (filters: RentPaymentFilters = {}) => {
      const query = new URLSearchParams(
        filters as Record<string, string>
      ).toString();
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: RentPayment[] }>(
          `${apiBaseUrl}/rent-payments${query ? `?${query}` : ""}`,
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

      rentPayments.value = data.data;
    };

    const fetchRentPayment = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: RentPayment }>(
          `${apiBaseUrl}/rent-payments/${id}`,
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

      const rp = data.data;
      const idx = rentPayments.value.findIndex((p) => p.id === rp.id);
      if (idx !== -1) rentPayments.value[idx] = rp;
      else rentPayments.value.unshift(rp);
      return rp;
    };

    const createRentPayment = async (payload: Record<string, any>) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: RentPayment }>(`${apiBaseUrl}/rent-payments`, {
          method: "POST",
          body: payload,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      }, loading);
      if (error) throw error;
      if (!data) throw new Error("No data received");

      rentPayments.value.unshift(data.data);
      return data.data;
    };

    const updateRentPayment = async (
      id: number,
      payload: Record<string, any>
    ) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: RentPayment }>(
          `${apiBaseUrl}/rent-payments/${id}`,
          {
            method: "PUT",
            body: payload,
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

      const rp = data.data;
      const idx = rentPayments.value.findIndex((p) => p.id === rp.id);
      if (idx !== -1) rentPayments.value[idx] = rp;
      return rp;
    };

    const payRentPayment = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ sessionId: string }>(
          `${apiBaseUrl}/rent-payments/${id}/pay`,
          {
            method: "POST",
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

      return data.sessionId;
    };

    // Payments
    const fetchPayments = async (filters: PaymentFilters = {}) => {
      const query = new URLSearchParams(
        filters as Record<string, string>
      ).toString();
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: Payment[] }>(
          `${apiBaseUrl}/payments${query ? `?${query}` : ""}`,
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

      payments.value = data.data;
    };

    const fetchPayment = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: Payment }>(`${apiBaseUrl}/payments/${id}`, {
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

      const pay = data.data;
      const idx = payments.value.findIndex((p) => p.id === pay.id);
      if (idx !== -1) payments.value[idx] = pay;
      else payments.value.unshift(pay);
      return pay;
    };

    const markPaymentManual = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: Payment }>(
          `${apiBaseUrl}/payments/${id}/manual`,
          {
            method: "POST",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrf,
              Accept: "application/json",
            },
          }
        );
      }, loading);
      if (error) throw error;
      if (!data || !data.data) throw new Error("No data received");

      const pay = data.data;
      const idx = payments.value.findIndex((p) => p.id === pay.id);
      if (idx !== -1) payments.value[idx] = pay;
      return pay;
    };

    const setTenantBillShares = (
      list: BillShare[],
      meta: Partial<typeof tenantBillSharesMeta>
    ) => {
      const map = new Map(tenantBillShares.value.map((s) => [s.id, s]));
      list.forEach((s) => map.set(s.id, s));
      tenantBillShares.value = Array.from(map.values());

      Object.assign(tenantBillSharesMeta, { ...emptyMeta(), ...meta });
    };

    const fetchTenantBillShares = async (
      opts: { status?: "pending" | "paid" | "cancelled" } = {},
      page = tenantBillSharesMeta.current_page,
      per = tenantBillSharesMeta.per_page
    ) => {
      const params: Record<string, string> = {
        page: String(page),
        per_page: String(per),
      };
      if (opts.status) params.status = opts.status;

      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<{ data: BillShare[]; meta: typeof tenantBillSharesMeta }>(
          `${apiBaseUrl}/tenant/bill-shares?${new URLSearchParams(params)}`,
          { credentials: "include", headers: { "X-XSRF-TOKEN": csrf } }
        );
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");

      setTenantBillShares(data.data, data.meta);
    };

    const setTenantRentPayments = (
      list: RentPayment[],
      meta: Partial<typeof tenantRentPaymentsMeta>
    ) => {
      const map = new Map(tenantRentPayments.value.map((p) => [p.id, p]));
      list.forEach((p) => map.set(p.id, p));
      tenantRentPayments.value = Array.from(map.values());
      Object.assign(tenantRentPaymentsMeta, { ...emptyMeta(), ...meta });
    };

    const fetchTenantRentPayments = async (
      opts: { status?: "pending" | "paid" } = {},
      page = tenantRentPaymentsMeta.current_page,
      per = tenantRentPaymentsMeta.per_page
    ) => {
      const params: Record<string, string> = {
        page: String(page),
        per_page: String(per),
      };
      if (opts.status) params.status = opts.status;
      const query = new URLSearchParams(params).toString();

      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("no csrf");
        return $fetch<{
          data: RentPayment[];
          meta: typeof tenantRentPaymentsMeta;
        }>(`${apiBaseUrl}/tenant/rent-payments?${query}`, {
          credentials: "include",
          headers: { "X-XSRF-TOKEN": csrf },
        });
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");

      setTenantRentPayments(data.data, data.meta);
    };

    const fetchPending = () =>
      Promise.all([
        fetchTenantBillShares({ status: "pending" }),
        fetchTenantRentPayments({ status: "pending" }),
      ]);

    const fetchPaid = () =>
      Promise.all([
        fetchTenantBillShares({ status: "paid" }),
        fetchTenantRentPayments({ status: "paid" }),
      ]);

    const fetchAll = async () => {
      await fetchPending();
      await fetchPaid();
    };

    /**
     * Receives stripe session for checkout
     *
     * @param itemId
     * @param source
     */
    const startPayment = async (itemId: number, source: "share" | "rent") => {
      const sessionUrl =
        source === "share"
          ? await payBillShare(itemId)
          : await payRentPayment(itemId);

      window.location.href = sessionUrl;
    };

    const reset = () => {
      utilityBills.value = [];
      billShares.value = {};
      rentPayments.value = [];
      payments.value = [];
      tenantBillShares.value = [];
      tenantRentPayments.value = [];
      Object.assign(tenantBillSharesMeta, emptyMeta());
      Object.assign(tenantRentPaymentsMeta, emptyMeta());
    };

    return {
      utilityBills,
      utilityBillsMeta,
      billShares,
      rentPayments,
      payments,
      tenantBillShares,
      tenantBillSharesMeta,
      tenantRentPayments,
      tenantRentPaymentsMeta,
      setTenantBillShares,
      fetchTenantBillShares,
      fetchTenantRentPayments,
      pending,
      history,
      fetchPending,
      fetchPaid,
      fetchAll,
      startPayment,
      loading,
      fetchUtilityBills,
      fetchUtilityBill,
      createUtilityBill,
      updateUtilityBill,
      deleteUtilityBill,
      fetchBillShares,
      createBillShare,
      payBillShare,
      fetchRentPayments,
      fetchRentPayment,
      createRentPayment,
      updateRentPayment,
      payRentPayment,
      fetchPayments,
      fetchPayment,
      markPaymentManual,
      reset,
    };
  },
  {
    persist: {
      storage: sessionStorage,
      pick: [
        "utilityBills",
        "billShares",
        "rentPayments",
        "payments",
        "tenantBillShares",
        "tenantBillSharesMeta",
        "tenantRentPayments",
        "tenantRentPaymentsMeta",
      ],
    } satisfies PersistenceOptions,
  }
);
