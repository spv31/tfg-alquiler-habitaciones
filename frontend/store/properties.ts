import { defineStore } from "pinia";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import { getCsrfToken } from "#imports";

export const usePropertiesStore = defineStore(
  "properties",
  () => {
    const properties = ref([]);
    const currentProperty = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const fetchProperties = async () => {};

    const createProperty = async (propertyData: any) => {
      try {
        loading.value = true;

        const csrfToken = await getCsrfToken();

        if (!csrfToken) {
          throw new Error("No se pudo obtener el token CSRF.");
        }
				console.log('Token: ', csrfToken);

        console.log("Propiedad: ", propertyData);



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

		return {
      properties,
      currentProperty,
      loading,
      error,
      fetchProperties,
      createProperty,
      updateProperty,
    };
  },
  {
    persist: { storage: localStorage },
  }
);
