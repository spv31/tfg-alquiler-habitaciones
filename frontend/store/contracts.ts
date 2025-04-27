import type {
  ContractTemplate,
  ApiErrorResponse,
} from "~/types/contractTemplate";
import type { Contract } from "~/types/contract";

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

    const fetchContractTemplates = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return $fetch<ContractTemplate[]>(`${apiBaseUrl}/contract-templates`, {
          method: "GET",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
          },
        });
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");

      contractTemplates.value = data;
      return data;
    };

    const fetchContractTemplate = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return $fetch<ContractTemplate>(
          `${apiBaseUrl}/contract-templates/${id}`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "application/json",
            },
          }
        );
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");

      currentContractTemplate.value = data;
      return data;
    };

    const saveContractTemplate = async (form: {
      name: string;
      type: string;
      content: string;
    }) => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return $fetch<ContractTemplate>(`${apiBaseUrl}/contract-templates`, {
          method: "POST",
          body: form,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
          },
        });
      }, loading);

      if (error) throw error;

      // Depending if we also return key with message
      // contractTemplates.value.push(data.contractTemplate);
      contractTemplates.value.push(data!);
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
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return $fetch<ContractTemplate>(
          `${apiBaseUrl}/contract-templates/${id}`,
          {
            method: "POST",
            body: form,
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "application/json",
            },
          }
        );
      }, loading);

      if (error) throw error;

      const index = contractTemplates.value.findIndex(
        (contractTemplate) => contractTemplate.id === id
      );
      if (index != -1) {
        // IT WOULD CHANGE DEPENDING ON VALUE RETURNED
        contractTemplates.value[index] = data!;
      }

      return data;
    };

    const deleteContractTemplate = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return $fetch(`${apiBaseUrl}/contract-templates/${id}`, {
          method: "DELETE",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
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
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");
      });
    };

    const fetchContract = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");
      });
    };

    const saveContract = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");
      });
    };

    const updateContract = async () => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");
      });
    };

    const deleteContract = async (id: number) => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");
      });
    };

    const fetchContractTemplatePdf = async () => {};

    const fetchContractPdf = async () => {};

    const fetchSignedContractPdf = async () => {};

    const downloadContractTemplatePdf = async () => {};

    const downloadContractPdf = async () => {};

    const downloadSignedContractPdf = async () => {};

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
      fetchContractPdf,
      fetchSignedContractPdf,
      downloadContractTemplatePdf,
      downloadContractPdf,
      downloadSignedContractPdf,
    };
  },
  {
    persist: { storage: localStorage },
  }
);
