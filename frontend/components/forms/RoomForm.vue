<template>
  <div class="py-8 px-4">
    <div class="max-w-3xl mx-auto mb-8">
      <h2 class="text-2xl font-bold text-gray-900 text-center">
        {{ $t("properties.detail.rooms.addRoomTitle") }}
      </h2>
    </div>

    <!-- Pasamos el formData y los errors al componente RoomForm -->
    <RoomForm
      :form="formData"
      :errors="errors"
      @submit="handleSubmit"
      @goBack="handleGoBack"
    />
  </div>
</template>

<script setup lang="ts">
import { reactive, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { usePropertiesStore } from "~/store/properties";
import type { Room } from "~/types/room";

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const store = usePropertiesStore();

// Identificador de la propiedad desde la URL /properties/[id]/rooms/add
const propertyId = Number(route.params.id);

// Objeto reactivo para el formulario
const formData = reactive<Partial<Room>>({
  description: "",
  rental_price: 0,
  main_image: "",
});

// Errores de validación
const errors = reactive<Record<string, string>>({});

// Validaciones simples
function validateForm() {
  // Validar descripción
  if (!formData.description || formData.description.length < 3) {
    errors.description = $t("errors.description_short");
  } else {
    errors.description = "";
  }

  // Validar rental_price
  const price = Number(formData.rental_price);
  if (isNaN(price) || price <= 0) {
    errors.rental_price = $t("errors.invalid_number");
  } else {
    errors.rental_price = "";
  }

  // Si quisieras validar la imagen, lo harías aquí
  // if (!formData.main_image) { ... }
}

// Indica si hay errores para, por ejemplo, deshabilitar un botón
const hasErrors = computed(() => {
  return Object.values(errors).some((err) => err !== "");
});

// Maneja el envío del formulario (crear habitación)
async function handleSubmit() {
  validateForm();

  if (!hasErrors.value) {
    try {
      await store.createRoom(propertyId, formData); // Llamada al Pinia Store
      alert("¡Habitación creada con éxito!");
      router.push(`/properties/${propertyId}`);
    } catch (err: any) {
      console.error("Error al crear habitación:", err);
      // Podrías mostrar un mensaje global de error o mapear errores de backend
      errors.description = err?.data?.message || $t("errors.generic_error");
    }
  }
}

// Botón "Volver"
function handleGoBack() {
  router.go(-1);
}
</script>
