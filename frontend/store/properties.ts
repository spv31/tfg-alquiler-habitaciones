import { defineStore } from "pinia";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import { getCsrfToken } from "#imports";

export const usePropertiesStore = defineStore(
  "properties",
  () => {
    const properties = ref([]);
    const currentProperty = ref(null);
    const pagination = ref({
      links: {},
      meta: {},
    });
    const loading = ref(false);
    const error = ref(null);

    const rooms = ref([]);
    const currentRoom = ref(null);
    const invitations = ref([]);

    const fetchProperties = async () => {
      try {
        loading.value = true;
        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("No se pudo obtener el token CSRF.");
        }

        const response = await $fetch("http://localhost:8000/api/properties", {
          method: "GET",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
          },
        });
        properties.value = response.data;
        pagination.value = {
          links: response.links,
          meta: response.meta,
        };
      } catch (err) {
        error.value = err.message || "Error al obtener propiedades";
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    const fetchProperty = async (id: any) => {
      try {
        loading.value = true;
        error.value = null;

        const csrfToken = await getCsrfToken();
        if (!csrfToken) {
          throw new Error("No se pudo obtener el token CSRF.");
        }

        const response = await $fetch(
          `http://localhost:8000/api/properties/${id}`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "application/json",
            },
          }
        );
        console.log(response.data);
        currentProperty.value = response.data;
      } catch (err) {
        error.value = err.message || "Error al obtener la propiedad";
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    const createProperty = async (propertyData: any) => {
      try {
        loading.value = true;

        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("No se pudo obtener el token CSRF.");
        }

        const response = await $fetch("http://localhost:8000/api/properties", {
          method: "POST",
          body: propertyData,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });

        properties.value.push(response);
        return response;
      } catch (error) {
        error.value = error;
        console.error(error);
        throw error;
      } finally {
        loading.value = false;
      }
    };

    const updateProperty = async (id: number, propertyData: any) => {
      try {
        loading.value = true;

        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("No se pudo obtener el token CSRF.");
        }

        const response = await $fetch(
          `http://localhost:8000/api/properties/${id}`,
          {
            method: "PUT",
            body: propertyData,
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "application/json",
              "Content-Type": "application/json",
            },
          }
        );

        // Encuentra y actualiza la propiedad en el estado
        const index = properties.value.findIndex((p) => p.id === id);
        if (index !== -1) {
          properties.value[index] = response;
        }

        return response;
      } catch (err) {
        error.value = err;
        console.error(err);
        throw err;
      } finally {
        loading.value = false;
      }
    };

    const fetchRooms = async (propertyId: number) => {
      try {
        loading.value = true;
        const csrfToken = await getCsrfToken();

        const response = await $fetch(
          `http://localhost:8000/api/properties/${propertyId}/rooms`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "application/json",
            },
          }
        );

        rooms.value = response.data;
      } catch (err) {
        error.value = err.message || "Error al obtener habitaciones";
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    const fetchRoom = async (propertyId: number, roomId: number) => {
      try {
        loading.value = true;
        const csrfToken = await getCsrfToken();

        const response = await $fetch(
          `http://localhost:8000/api/properties/${propertyId}/rooms/${roomId}`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              "X-XSRF-TOKEN": csrfToken,
              Accept: "application/json",
            },
          }
        );

        currentRoom.value = response.data;
      } catch (err) {
        error.value = err.message || "Error al obtener la habitación";
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    // Método para guardar detalles opcionales de una propiedad
    const savePropertyDetails = async (
      propertyId: number,
      detailsData: any
    ) => {
      try {
        loading.value = true;
        const csrfToken = await getCsrfToken();

        const response = await $fetch(
          `http://localhost:8000/api/properties/${propertyId}/details`,
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

        if (currentProperty.value) {
          currentProperty.value.details = response.data;
        }
        return response;
      } catch (err) {
        error.value = err.message || "Error al guardar detalles";
        console.error(err);
        throw err;
      } finally {
        loading.value = false;
      }
    };

    const createInvitation = async (invitationData: {
      email: string;
      assignable_id: number;
      assignable_type: "property" | "room";
    }) => {
      try {
        loading.value = true;
        const csrfToken = await getCsrfToken();

        const response = await $fetch("http://localhost:8000/api/invitations", {
          method: "POST",
          body: invitationData,
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });

        invitations.value.push(response.data);
        return response;
      } catch (err) {
        error.value = err.message || "Error al crear invitación";
        console.error(err);
        throw err;
      } finally {
        loading.value = false;
      }
    };

    const fetchInvitations = async () => {
      try {
        loading.value = true;
        const csrfToken = await getCsrfToken();

        const response = await $fetch("http://localhost:8000/api/invitations", {
          method: "GET",
          credentials: "include",
          headers: {
            "X-XSRF-TOKEN": csrfToken,
            Accept: "application/json",
          },
        });

        invitations.value = response.data;
      } catch (err) {
        error.value = err.message || "Error al obtener invitaciones";
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    return {
      properties,
      currentProperty,
      loading,
      error,
      fetchProperties,
      fetchProperty,
      createProperty,
      updateProperty,
      rooms,
      currentRoom,
      invitations,
      fetchRooms,
      fetchRoom,
      savePropertyDetails,
      createInvitation,
      fetchInvitations,
    };
  },
  {
    persist: { storage: localStorage },
  }
);
