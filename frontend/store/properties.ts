import { defineStore } from "pinia";
import { getCsrfToken } from "#imports";
import type {
  PropertyCollection,
  Property,
  CreatePropertyResponse,
} from "~/types/property";
import type {
  PropertyDetail,
  UpdatePropertyDetailsResponse,
} from "~/types/propertyDetail";
import type { Room, RoomsResponse } from "~/types/room";
import type { Tenant } from "~/types/tenant";

export const usePropertiesStore = defineStore(
  "properties",
  () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseUrl;

    const properties = ref<Property[]>([]);
    const rooms = ref<Room[]>([]);
    const currentProperty = ref<Property | null>(null);
    const currentRoom = ref<Room | null>(null);
    const tenants = ref<Tenant[] | null>([]);
    const currentTenant = ref<Tenant | null>(null);

    const loading = ref(false);
    const error = ref(null);
    const pagination = ref({
      links: {},
      meta: {},
    });

    /**
     * Sends request to get properties
     *
     * @async
     * @returns {*}
     */
    const fetchProperties = async (): Promise<Property[]> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<PropertyCollection>(
          `${apiBaseUrl}/api/properties`,
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

      if (error) {
        error.value = error.message || "Error al obtener propiedades";
        throw error;
      }

      if (!data) throw new Error("No data received");

      properties.value = data.data;
      pagination.value = {
        links: data.links,
        meta: data.meta,
      };

      return data.data;
    };

    /**
     * Sends request to get current property
     *
     * @async
     * @param {number} id
     * @returns {Promise<Property>}
     */

    const fetchProperty = async (id: number): Promise<Property> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Property>(`${apiBaseUrl}/properties/${id}`, {
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

      currentProperty.value = data;
      return data;
    };

    /**
     * Sends request to create property
     *
     * @async
     * @param {Partial<Property>} propertyData
     * @returns {unknown}
     */
    const createProperty = async (propertyData: Partial<Property>) => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<CreatePropertyResponse>(
          `${apiBaseUrl}/properties`,
          {
            method: "POST",
            body: propertyData,
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

      properties.value.push(data.property);
      return data;
    };

    /**
     * Sends request to update property
     *
     * @async
     * @param {Property["id"]} id
     * @param {Partial<Property>} propertyData
     * @returns {unknown}
     */
    const updateProperty = async (
      id: Property["id"],
      propertyData: Partial<Property>
    ) => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Property>(`${apiBaseUrl}/properties/${id}`, {
          method: "PUT",
          body: propertyData,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });
      }, loading);

      if (error) throw error;

      if (data) {
        currentProperty.value = data;

        const index = properties.value.findIndex(
          (property) => property.id === id
        );
        if (index !== -1) {
          properties.value[index] = data;
        }
        return data;
      }
    };

    /**
     * Deletes a property by its id.
     *
     * @async
     * @param {Property["id"]} id - The id of the property to delete.
     * @returns {Promise<{ message_key: string }>} A response object with a message key.
     * @throws An error if the deletion fails.
     */
    const deleteProperty = async (
      id: Property["id"]
    ): Promise<{ message_key: string }> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<{ message_key: string }>(
          `${apiBaseUrl}/api/properties/${id}`,
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

      // Remove deleted property from the state
      properties.value = properties.value.filter(
        (property) => property.id !== id
      );
      return data;
    };

    /**
     * Changes the status of a property.
     *
     * @async
     * @param {Property["id"]} propertyId - The id of the property to update.
     * @param {"available" | "unavailable" | "occupied" | "partially_occupied"} status - The new status.
     * @returns {Promise<Property>} The updated property.
     * @throws An error if the update fails.
     */
    const changePropertyStatus = async (
      propertyId: Property["id"],
      status: "available" | "unavailable" | "occupied" | "partially_occupied"
    ): Promise<Property> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Property>(
          `${apiBaseUrl}/api/properties/${propertyId}/status`,
          {
            method: "PATCH",
            credentials: "include",
            body: { status },
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

      currentProperty.value = data;
      const index = properties.value.findIndex(
        (property) => property.id === propertyId
      );
      if (index !== -1) {
        properties.value[index] = data;
      }
      return data;
    };

    /**
     * Fetches an image (Blob) for a given property and filename
     *
     * @param {number} propertyId
     * @param {string} filename
     * @returns {Promise<Blob>}
     */
    const fetchPropertyImage = async (
      propertyId: number,
      filename: string
    ): Promise<Blob> => {
      const { data, error } = await tryCatch<Blob>(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        // No usamos $fetch porque necesitamos un Blob
        const response = await fetch(
          `${apiBaseUrl}/properties/${propertyId}/image/${filename}`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "image/*",
            },
          }
        );
        if (!response.ok) throw new Error("Failed to fetch image");
        return await response.blob();
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");
      return data;
    };

    /**
     * Sends request to fetch properties
     *
     * @async
     * @param {Property["id"]} propertyId
     * @returns {*}
     */
    const fetchRooms = async (propertyId: Property["id"]) => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<RoomsResponse>(
          `${apiBaseUrl}/properties/${propertyId}/rooms`,
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

      if (data.warning) {
      }

      rooms.value = data.data;
    };

    /**
     * Sends request to get a specific room from a property
     *
     * @async
     * @param {number} propertyId
     * @param {number} roomId
     * @returns {Promise<Room>}
     */
    const fetchRoom = async (
      propertyId: number,
      roomId: number
    ): Promise<Room> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Room>(
          `${apiBaseUrl}/properties/${propertyId}/rooms/${roomId}`,
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

      currentRoom.value = data;
      return data;
    };

    /**
     * Creates a new room for a given property.
     *
     * @async
     * @param {Property["id"]} propertyId - The id of the property.
     * @param {Partial<Room>} roomData - The data for the new room.
     * @returns {Promise<Room>} The newly created room.
     * @throws If the request fails.
     */
    const createRoom = async (
      propertyId: Property["id"],
      roomData: Partial<Room>
    ): Promise<Room> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Room>(
          `${apiBaseUrl}/api/properties/${propertyId}/rooms`,
          {
            method: "POST",
            body: roomData,
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

      rooms.value.push(data);
      return data;
    };

    /**
     * Updates an existing room for a given property.
     *
     * @async
     * @param {Property["id"]} propertyId - The id of the property.
     * @param {Room["id"]} roomId - The id of the room to update.
     * @param {Partial<Room>} roomData - The updated data for the room.
     * @returns {Promise<Room>} The updated room.
     * @throws If the request fails.
     */
    const updateRoom = async (
      propertyId: Property["id"],
      roomId: Room["id"],
      roomData: Partial<Room>
    ): Promise<Room> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Room>(
          `${apiBaseUrl}/api/properties/${propertyId}/rooms/${roomId}`,
          {
            method: "PUT",
            body: roomData,
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

      const index = rooms.value.findIndex((room) => room.id === roomId);
      if (index !== -1) {
        rooms.value[index] = data;
      }
      if (currentRoom.value && currentRoom.value.id === roomId) {
        currentRoom.value = data;
      }
      return data;
    };

    /**
     * Deletes a room for a given property.
     *
     * @async
     * @param {Property["id"]} propertyId - The id of the property.
     * @param {Room["id"]} roomId - The id of the room to delete.
     * @returns {Promise<{ message_key: string }>} A response with a message key.
     * @throws If the deletion fails.
     */
    const deleteRoom = async (
      propertyId: Property["id"],
      roomId: Room["id"]
    ): Promise<{ message_key: string }> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<{ message_key: string }>(
          `${apiBaseUrl}/api/properties/${propertyId}/rooms/${roomId}`,
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

      rooms.value = rooms.value.filter((room) => room.id !== roomId);
      return data;
    };

    /**
     * Changes the status of a room for a given property.
     *
     * @async
     * @param {Property["id"]} propertyId - The id of the property.
     * @param {Room["id"]} roomId - The id of the room.
     * @param {"available" | "unavailable" | "occupied"} status - The new status.
     * @returns {Promise<Room>} The updated room.
     * @throws If the update fails.
     */
    const changeRoomStatus = async (
      propertyId: Property["id"],
      roomId: Room["id"],
      status: "available" | "unavailable" | "occupied"
    ): Promise<Room> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Room>(
          `${apiBaseUrl}/api/properties/${propertyId}/rooms/${roomId}/status`,
          {
            method: "PATCH",
            credentials: "include",
            body: { status },
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

      if (currentRoom.value && currentRoom.value.id === roomId) {
        currentRoom.value = data;
      }
      const index = rooms.value.findIndex((room) => room.id === roomId);
      if (index !== -1) {
        rooms.value[index] = data;
      }
      return data;
    };

    /**
     * Sends request to get room image
     *
     * @async
     * @param {number} propertyId
     * @param {number} roomId
     * @param {string} filename
     * @returns {Promise<Blob>}
     */
    const fetchRoomImage = async (
      propertyId: number,
      roomId: number,
      filename: string
    ): Promise<Blob> => {
      const { data, error } = await tryCatch<Blob>(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        const response = await fetch(
          `${apiBaseUrl}/properties/${propertyId}/rooms/${roomId}/image/${filename}`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "image/*",
            },
          }
        );
        if (!response.ok) throw new Error("Failed to fetch room image");
        return await response.blob();
      }, loading);

      if (error) throw error;
      if (!data) throw new Error("No data received");
      return data;
    };

    /**
     * Saves or updates property details
     *
     * @async
     * @param {number} propertyId
     * @param {Partial<PropertyDetail>} detailsData
     * @returns {Promise<UpdatePropertyDetailsResponse>}
     */
    const savePropertyDetails = async (
      propertyId: number,
      detailsData: Partial<PropertyDetail>
    ): Promise<UpdatePropertyDetailsResponse> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<UpdatePropertyDetailsResponse>(
          `${apiBaseUrl}/properties/${propertyId}/details`,
          {
            method: "PUT",
            body: detailsData,
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

      if (currentProperty.value) {
        currentProperty.value.details = data.details;
      }
      return data;
    };

    /**
     * Fetches tenants for a given property.
     * This endpoint is used when the property is rented as a whole.
     *
     * @async
     * @param {Property["id"]} propertyId - The id of the property.
     * @returns {Promise<Tenant[]>} The array of tenants associated with the property.
     * @throws An error if the request fails.
     */
    const fetchPropertyTenants = async (
      propertyId: Property["id"]
    ): Promise<Tenant[]> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");
        return await $fetch<Tenant[]>(
          `${apiBaseUrl}/api/properties/${propertyId}/tenants`,
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

      tenants.value = data;
      return data;
    };

    /**
     * Fetches the tenant for a specific room.
     * When the property is rented by room, each room typically has one tenant.
     *
     * @async
     * @param {Property["id"]} propertyId - The id of the property.
     * @param {Room["id"]} roomId - The id of the room.
     * @returns {Promise<Tenant[]>} The array of tenants associated with the room.
     * @throws An error if the request fails.
     */
    const fetchRoomTenant = async (
      propertyId: Property["id"],
      roomId: Room["id"]
    ): Promise<Tenant> => {
      const { data, error } = await tryCatch(async () => {
        const csrfToken = await getCsrfToken();
        if (!csrfToken) throw new Error("Error getting CSRF Token");

        return await $fetch<Tenant>(
          `${apiBaseUrl}/api/properties/${propertyId}/rooms/${roomId}/tenant`,
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
      if (!data) throw new Error('No data received');
      
      currentTenant.value = data || null;
      return data;
    };

    return {
      properties,
      currentProperty,
      fetchProperties,
      fetchProperty,
      createProperty,
      updateProperty,
      deleteProperty,
      changePropertyStatus,
      fetchPropertyImage,
      rooms,
      currentRoom,
      fetchRooms,
      fetchRoom,
      createRoom,
      updateRoom,
      deleteRoom,
      changeRoomStatus,
      savePropertyDetails,
      fetchRoomImage,
      tenants,
      currentTenant,
      fetchPropertyTenants,
      fetchRoomTenant,
      loading,
      error,
      pagination,
    };
  },
  {
    persist: { storage: localStorage },
  }
);