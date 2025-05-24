import type { PersistenceOptions } from "pinia-plugin-persistedstate";
import type { ContractTemplate } from "~/types/contractTemplate";
import type { Contract, StoreContractPayload } from "~/types/contract";

export const useContractsStore = defineStore(
  "contracts",
  () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseURL;

    const contractTemplates = ref<ContractTemplate[]>([]);
    const currentContractTemplate = ref<ContractTemplate | null>(null);

    const contracts = ref<Contract[]>([]);
    const currentContract = ref<Contract | null>(null);

    const loading = ref(false);

    const templatePreviewCache = ref<Record<number, string>>({});
    const lastFetchedPreviewAt = ref<Record<number, number>>({});
    const contractPreviewCache = ref<Record<number, string>>({});
    const lastFetchedContractAt = ref<Record<number, number>>({});
    const PREVIEW_TTL = 30 * 60 * 1000;

    const fetchContractTemplates = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: ContractTemplate[] }>(
          `${apiBaseUrl}/contract-templates`,
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

      contractTemplates.value = data.data;
      return data;
    };

    const fetchContractTemplate = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: ContractTemplate }>(
          `${apiBaseUrl}/contract-templates/${id}`,
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

      currentContractTemplate.value = data.data;
      return data;
    };

    const saveContractTemplate = async (form: {
      name: string;
      type: string;
      content: string;
    }) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<{ data: ContractTemplate }>(
          `${apiBaseUrl}/contract-templates`,
          {
            method: "POST",
            body: form,
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

      contractTemplates.value.push(data.data);
    };

    const updateContractTemplate = async (
      id: number,
      form: {
        name: string;
        type: string;
        content: string;
      }
    ) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        console.log("id: ", id);
        console.log("Formulario: ", form.name);

        return $fetch<{ data: ContractTemplate }>(
          `${apiBaseUrl}/contract-templates/${id}`,
          {
            method: "PUT",
            body: form,
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

      const index = contractTemplates.value.findIndex(
        (contractTemplate) => contractTemplate.id === id
      );
      if (index != -1) {
        contractTemplates.value[index] = data.data;
      }
      return data.data;
    };

    const deleteContractTemplate = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch(`${apiBaseUrl}/contract-templates/${id}`, {
          method: "DELETE",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      });

      if (error) throw error;

      contractTemplates.value = contractTemplates.value.filter(
        (contractTemplate) => contractTemplate.id !== id
      );

      return data;
    };

    const fetchContracts = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
      });
      if (error) throw error;
      if (!data) throw new Error("No data received");

      contracts.value = data.data;
    };

    const fetchContract = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
      });
    };

    const saveContract = async (contract: StoreContractPayload) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<{ data: Contract }>(`${apiBaseUrl}/contracts`, {
          method: "POST",
          body: contract,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      });

      if (error) throw error;
      if (!data) throw new Error("No data received");

      contracts.value.push(data.data);
      return data.data;
    };

    const updateContract = async (
      id: number,
      contract: StoreContractPayload
    ) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<{ data: Contract }>(`${apiBaseUrl}/contracts`, {
          method: "POST",
          body: contract,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });
      });
      if (error) throw error;
      if (!data) throw new Error("No data received");

      const index = contracts.value.findIndex((contract) => contract.id === id);
      if (index !== -1) {
        contracts.value[index] = data.data;
      }
      return data.data;
    };

    const deleteContract = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
      });
      if (error) throw error;
      contracts.value = contracts.value.filter(
        (contract) => contract.id !== id
      );
      currentContract.value = null;
    };

    const fetchContractTemplatePdf = async (id: number) => {
      const now = Date.now();
      const last = lastFetchedPreviewAt.value[id] ?? 0;
      if (templatePreviewCache.value[id] && now - last < PREVIEW_TTL) {
        return templatePreviewCache.value[id];
      }

      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<Blob>(`${apiBaseUrl}/contract-templates/${id}/preview`, {
          method: "GET",
          responseType: "blob",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrf,
            Accept: "application/pdf",
          },
        });
      });

      if (error) throw error;

      const blobUrl = URL.createObjectURL(data!);
      templatePreviewCache.value[id] = blobUrl;
      lastFetchedPreviewAt.value[id] = now;
      return blobUrl;
    };

    const fetchContractTemplatePdfBlob = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<Blob>(`${apiBaseUrl}/contract-templates/${id}/preview`, {
          method: "GET",
          responseType: "blob",
          credentials: "include",
          headers: { "X-XSRF-TOKEN": csrf, Accept: "application/pdf" },
        });
      });
      if (error) throw error;
      return data!;
    };

    const fetchContractPdf = async (id: number): Promise<string> => {
      const now = Date.now();
      const last = lastFetchedContractAt.value[id] ?? 0;

      if (contractPreviewCache.value[id] && now - last < PREVIEW_TTL) {
        return contractPreviewCache.value[id];
      }

      const blob = await fetchContractPdfBlob(id);
      const url = URL.createObjectURL(blob);

      contractPreviewCache.value[id] = url;
      lastFetchedContractAt.value[id] = now;

      return url;
    };

    const fetchContractPdfBlob = async (id: number): Promise<Blob> => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        return $fetch<Blob>(`${apiBaseUrl}/contracts/${id}/pdf`, {
          method: "GET",
          responseType: "blob",
          credentials: "include",
          headers: { "X-XSRF-TOKEN": csrf, Accept: "application/pdf" },
        });
      });

      if (error) throw error;
      return data!;
    };

    const fetchSignedContractPdf = async () => {};

    const downloadContractTemplatePdf = async () => {};

    const downloadContractPdf = async () => {};

    const downloadSignedContractPdf = async () => {};

    const uploadSigned = async (
      contractId: number,
      file: { file: File; name: string }
    ) => {
      const { data, error } = await tryCatch(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");

        const form = new FormData();
        form.append("file", file.file, file.name);
        form.append("name", file.name);

        return $fetch<{ data: Contract }>(
          `${apiBaseUrl}/contracts/${contractId}/signed`,
          {
            method: "POST",
            body: form,
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrf,
              Accept: "application/json",
            },
          }
        );
      });

      if (error) throw error;
      if (!data) throw new Error("No data received");

      const idx = contracts.value.findIndex((c) => c.id === data.data.id);
      if (idx !== -1) contracts.value[idx] = data.data;
      if (currentContract.value?.id === data.data.id)
        currentContract.value = data.data;

      return data.data;
    };

    return {
      contractTemplates,
      currentContractTemplate,
      contracts,
      currentContract,
      fetchContractTemplates,
      fetchContractTemplate,
      saveContractTemplate,
      updateContractTemplate,
      deleteContractTemplate,
      fetchContracts,
      fetchContract,
      saveContract,
      updateContract,
      deleteContract,
      fetchContractTemplatePdf,
      fetchContractTemplatePdfBlob,
      fetchContractPdf,
      fetchContractPdfBlob,
      fetchSignedContractPdf,
      downloadContractTemplatePdf,
      downloadContractPdf,
      downloadSignedContractPdf,
      uploadSigned,
    };
  },
  {
    persist: {
      storage: sessionStorage,
      pick: [
        "contractTemplates",
        "contracts",
        "currentContractTemplate",
        "currentContract",
        "lastFetchedPreviewAt",
        "lastFetchedContractAt",
      ],
    } satisfies PersistenceOptions,
  }
);
