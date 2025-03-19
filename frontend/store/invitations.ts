import { defineStore } from "pinia";
import { getCsrfToken } from "#imports";
import type {
  InvitationCollection,
  Invitation,
  CreateInvitationResponse,
} from "~/types/invitation";

export const useInvitationsStore = defineStore(
  "invitations",
  () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseURL;

    const invitations = ref<Invitation[]>([]);
    const currentInvitation = ref<Invitation | null>(null);
    const pagination = ref({
      links: {},
      meta: {},
    });
    const loading = ref(false);
    const error = ref(null);

    /**
     * Fetches the list of invitations paginated
     *
     * @async
     * @returns {Promise<Invitation[]>}
     */
    const fetchInvitations = async (): Promise<Invitation[]> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<InvitationCollection>(`${apiBaseUrl}/invitations`, {
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

      invitations.value = data.data;
      pagination.value = {
        links: data.links,
        meta: data.meta,
      };

      return data.data;
    };

    /**
     * Fetches a specific invitation by its id.
     *
     * @async
     * @param {number} invitationId - The id of the invitation.
     * @returns {Promise<Invitation>} The fetched invitation.
     * @throws An error if the request fails.
     */
    const fetchInvitation = async (
      invitationId: number
    ): Promise<Invitation> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Invitation>(
          `${apiBaseUrl}/invitations/${invitationId}`,
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

      currentInvitation.value = data;
      return data;
    };

    /**
     * Creates a new invitation (property or room)
     *
     * @async
     * @param invitationData
     * @returns {Promise<CreateInvitationResponse>}
     */
    const createInvitation = async (invitationData: {
      email: string;
      property_id: number;
      room_id?: number;
    }): Promise<CreateInvitationResponse> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        const formattedData: any = {
          email: invitationData.email,
          property_id: invitationData.property_id,
        };
      
        if (invitationData.room_id) {
          formattedData.room_id = invitationData.room_id; 
        }

        return await $fetch<CreateInvitationResponse>(
          `${apiBaseUrl}/invitations`,
          {
            method: "POST",
            body: formattedData,
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "application/json",
              "Content-Type": "application/json",
            },
          }
        );
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");

      invitations.value.push(data.invitation);

      return data;
    };

    /**
     * Updates an invitation.
     *
     * @async
     * @param {number} invitationId - The id of the invitation.
     * @param {Partial<Invitation>} invitationData - The data to update.
     * @returns {Promise<Invitation>} The updated invitation.
     * @throws An error if the request fails.
     */
    const updateInvitation = async (
      invitationId: number,
      invitationData: Partial<Invitation>
    ): Promise<Invitation> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Invitation>(
          `${apiBaseUrl}/invitations/${invitationId}`,
          {
            method: "PUT",
            body: invitationData,
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "application/json",
              "Content-Type": "application/json",
            },
          }
        );
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");

      currentInvitation.value = data;
      const index = invitations.value.findIndex(
        (inv) => inv.id === invitationId
      );
      if (index !== -1) {
        invitations.value[index] = data;
      }
      return data;
    };

    /**
     * Deletes an invitation.
     *
     * @async
     * @param {number} invitationId - The id of the invitation to delete.
     * @returns {Promise<{ message_key: string }>} A response with a message key.
     * @throws An error if the deletion fails.
     */
    const deleteInvitation = async (
      invitationId: number
    ): Promise<{ message_key: string }> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<{ message_key: string }>(
          `${apiBaseUrl}/invitations/${invitationId}`,
          {
            method: "DELETE",
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

      invitations.value = invitations.value.filter(
        (inv) => inv.id !== invitationId
      );
      return data;
    };

    /**
     * Regenerates an invitation.
     *
     * @async
     * @param {number} invitationId - The id of the invitation to regenerate.
     * @returns {Promise<CreateInvitationResponse>} The response with the new invitation data.
     * @throws An error if the regeneration fails.
     */
    const regenerateInvitation = async (
      invitationId: number
    ): Promise<CreateInvitationResponse> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<CreateInvitationResponse>(
          `${apiBaseUrl}/invitations/${invitationId}/regenerate`,
          {
            method: "POST",
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

      const index = invitations.value.findIndex(
        (inv) => inv.id === invitationId
      );
      if (index !== -1) {
        invitations.value[index] = data.invitation;
      }
      return data;
    };

    return {
      invitations,
      currentInvitation,
      pagination,
      loading,
      error,
      fetchInvitations,
      createInvitation,
      fetchInvitation,
      updateInvitation,
      deleteInvitation,
      regenerateInvitation,
    };
  },
  {
    persist: { storage: localStorage },
  }
);
