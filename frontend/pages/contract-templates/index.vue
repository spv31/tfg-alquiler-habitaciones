<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="alertMessage" ref="alertRef" class="mb-4 mx-auto">
      <Alert
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />
    </div>

    <div class="text-center md:text-right mb-8">
      <NuxtLink
        :to="$localePath('contract-templates-create')"
        class="button-primary inline-flex items-center px-6 py-3 rounded-lg shadow-md hover:s duration-200"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 mr-2"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
            clip-rule="evenodd"
          />
        </svg>
        Añadir una plantilla
      </NuxtLink>
    </div>

    <div
      v-if="!loading"
      class="grid gap-8 mt-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3"
    >
      <div v-for="tpl in contractTemplates" :key="tpl.id" class="flex flex-col">
        <div
          class="group relative bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-blue-100 flex-1 flex flex-col"
        >
          <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-900 text-center truncate">
              {{ tpl.name }}
            </h3>
            <p
              class="text-xs font-medium text-center mt-1"
              :class="tpl.is_default ? 'text-blue-600' : 'text-gray-600'"
            >
              {{
                tpl.is_default
                  ? "Plantilla predeterminada"
                  : "Plantilla personalizada"
              }}
            </p>
          </div>

          <div class="relative w-full flex-1">
            <div class="aspect-[210/297] relative">
              <span
                class="absolute top-2 right-2 z-10 text-xs px-2 py-1 rounded-full shadow-sm"
                :class="contractTypeBadgeClasses(tpl.type)"
              >
                {{ contractTypeLabel(tpl.type) }}
              </span>

              <Suspense>
                <template #default>
                  <PreviewImage
                    :template-id="tpl.id"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                  />
                </template>
                <template #fallback>
                  <div
                    class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100"
                  >
                    <svg
                      class="animate-spin h-8 w-8 text-gray-400"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      />
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v8H4z"
                      />
                    </svg>
                  </div>
                </template>
              </Suspense>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="p-3 bg-gray-50 border-t border-gray-100">
            <div
              class="grid gap-2"
              :class="tpl.is_default ? 'grid-cols-2' : 'grid-cols-3'"
            >
              <button
                class="button-primary flex items-center justify-center px-3 py-2 text-sm rounded-md"
                @click="openViewer(tpl.id)"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 mr-1.5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
                Ver
              </button>

              <NuxtLink
                :to="`/contract-templates/${tpl.id}/edit`"
                class="button-secondary flex items-center justify-center px-3 py-2 text-sm rounded-md"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 mr-1.5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
                Editar
              </NuxtLink>

              <button
                v-if="!tpl.is_default"
                class="button-danger flex items-center justify-center px-3 py-2 text-sm rounded-md"
                @click="deleteTemplate(tpl.id)"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 mr-1.5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-else class="flex justify-center items-center mt-16">
      <div class="flex space-x-2">
        <div class="w-4 h-4 bg-blue-500 rounded-full animate-bounce"></div>
        <div
          class="w-4 h-4 bg-blue-500 rounded-full animate-bounce delay-100"
        ></div>
        <div
          class="w-4 h-4 bg-blue-500 rounded-full animate-bounce delay-200"
        ></div>
      </div>
    </div>

    <!-- Modal del visor PDF -->
    <teleport to="body">
      <div
        v-if="showId"
        class="fixed inset-0 z-50 flex items-start justify-center bg-black/60 backdrop-blur-sm transition-opacity duration-300 overflow-y-auto py-8"
        @click.self="closeViewer"
      >
        <div
          class="bg-white w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 h-[80vh] rounded-xl shadow-2xl flex flex-col transform transition-all duration-300 mt-8"
          :class="{
            'scale-95 opacity-0': !showId,
            'scale-100 opacity-100': showId,
          }"
        >
          <div
            class="p-3 flex justify-between items-center border-b bg-gray-50 sticky top-0"
          >
            <h3 class="font-medium text-gray-700">Vista previa de plantilla</h3>
            <button
              @click="closeViewer"
              class="text-gray-500 hover:text-red-600 p-1 rounded-full hover:bg-gray-100 transition-colors"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <teleport to="body">
            <div
              v-if="showId"
              class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 sm:p-6"
              @click.self="closeViewer"
            >
              <div
                class="bg-white w-full max-w-[95%] sm:max-w-[90%] md:max-w-[70%] h-[85vh] rounded-xl shadow-2xl flex flex-col overflow-hidden"
              >
                <div
                  class="p-3 flex justify-between items-center border-b bg-gray-50 shrink-0"
                >
                  <h3 class="font-medium text-sm sm:text-base">
                    {{ t("contracts.preview") }}
                  </h3>
                  <button
                    @click="closeViewer"
                    class="p-1 hover:text-red-600 text-xl"
                  >
                    ✕
                  </button>
                </div>

                <object
                  v-if="pdfUrl"
                  :data="pdfUrl"
                  type="application/pdf"
                  class="flex-1 w-full"
                  style="min-height: 0"
                >
                  <div
                    class="h-full w-full flex flex-col items-center justify-center p-4 space-y-4"
                  >
                    <p class="text-center text-gray-600 text-sm">
                      {{ t("common.noInlinePdf") }}
                    </p>
                    <a
                      :href="pdfUrl"
                      target="_blank"
                      rel="noopener"
                      class="button-primary px-4 py-2 rounded-md"
                    >
                      {{ t("common.openNewTab") }}
                    </a>
                  </div>
                </object>
              </div>
            </div>
          </teleport>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRoute } from "#imports";

import { useContractsStore } from "~/store/contracts";
import { contractTypeBadgeClasses, contractTypeLabel } from "~/utils/badges";
import { useMyToast, useMyConfirm, useFlashToast } from "#imports";

const contractsStore = useContractsStore();
const { contractTemplates, loading } = storeToRefs(contractsStore);

const { success, error: errorToast } = useMyToast();
const confirm = useMyConfirm();
const { showFlash } = useFlashToast();
const { t } = useI18n();

const showId = ref<number | null>(null);
const pdfUrl = ref<string | null>(null);

const openViewer = (id: number) => {
  showId.value = id;
};

const closeViewer = () => {
  showId.value = null;
  if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value);
  pdfUrl.value = null;
};

watch(showId, async (newId) => {
  if (!newId) return;
  try {
    const blob = await contractsStore.fetchContractTemplatePdfBlob(newId);
    if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value);
    pdfUrl.value = URL.createObjectURL(blob);
  } catch (err) {
    console.error(err);
    errorToast("Error al cargar la vista previa");
  }
});

const deleteTemplate = (id: number) => {
  confirm.show({
    message: "¿Estás seguro de que deseas eliminar esta plantilla?",
    header: "Confirmación",
    icon: "pi pi-exclamation-triangle text-red-500 text-xl",
    acceptLabel: "Eliminar",
    rejectLabel: "Cancelar",
    acceptSeverity: "danger",
    onAccept: async () => {
      try {
        await contractsStore.deleteContractTemplate(id);
        await contractsStore.fetchContractTemplates();
        success("Plantilla eliminada correctamente");
      } catch (err) {
        console.error(err);
        errorToast("Error al eliminar la plantilla");
      }
    },
  });
};

onMounted(async () => {
  try {
    await contractsStore.fetchContractTemplates();
    showFlash();
  } catch (err) {
    errorToast("Error al cargar las plantillas de contratos");
  }
});

const alertMessage = ref("");
const alertType = ref<"success" | "error" | null>(null);
const alertRef = ref<HTMLElement | null>(null);
</script>

<style scoped></style>
