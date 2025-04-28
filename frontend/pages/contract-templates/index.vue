<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="alertMessage" class="mb-4 mx-auto">
      <Alert
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />
    </div>

    <div class="text-center md:text-right">
      <NuxtLink
        :to="$localePath('contract-templates-create')"
        class="button-primary inline-block"
      >
        Añadir una plantilla
      </NuxtLink>
    </div>

    <div
      v-if="!loading"
      class="grid gap-8 mt-8 grid-cols-1 md:grid-cols-2 xl:grid-cols-3"
    >
      <div v-for="tpl in contractTemplates.data" :key="tpl.id">
        <!-- título FUERA del card -->
        <p class="mb-2 font-semibold text-gray-900 text-center">
          {{ tpl.name }}
        </p>

        <!-- card: sólo la preview -->
        <div
          class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition-transform duration-200 hover:shadow-lg hover:-translate-y-1"
        >
          <div class="relative w-full">
            <div class="aspect-[210/297]">
              <Suspense>
                <template #default>
                  <PreviewImage
                    :template-id="tpl.id"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-200 group-hover:scale-[1.02]"
                  />
                </template>
                <template #fallback>
                  <div
                    class="absolute inset-0 flex items-center justify-center bg-gray-50"
                  >
                    <svg
                      class="animate-spin h-6 w-6 text-gray-400"
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
              <div
                class="absolute inset-x-0 bottom-0 flex gap-1 px-2 py-1 bg-white/70 backdrop-blur-sm"
              >
                <button
                  class="button-primary flex-1 px-1 py-0.5 text-xs"
                  @click="openViewer(tpl.id)"
                >
                  Ver PDF
                </button>

                <NuxtLink
                  :to="`/contract-templates/${tpl.id}/edit`"
                  class="button-secondary flex-1 px-1 py-0.5 text-xs text-center"
                >
                  Editar
                </NuxtLink>

                <button
                  v-if="!tpl.is_default"
                  class="button-danger flex-1 px-1 py-0.5 text-xs"
                  @click="deleteTemplate(tpl.id)"
                >
                  Borrar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="flex justify-center items-center mt-10">
      <span class="loader-square"></span>
    </div>

    <teleport to="body">
      <div
        v-if="showId"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
      >
        <div
          class="bg-white w-11/12 md:w-5/6 h-5/6 rounded-lg shadow-lg flex flex-col overflow-hidden"
        >
          <div class="p-2 text-right border-b">
            <button
              @click="closeViewer"
              class="text-xl leading-none hover:text-red-600"
            >
              ×
            </button>
          </div>

          <client-only>
            <PdfViewer v-if="pdfUrl" :url="pdfUrl" class="flex-1" />
          </client-only>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup lang="ts">
import { useContractsStore } from "~/store/contracts";

const contractsStore = useContractsStore();
const { contractTemplates, loading } = storeToRefs(contractsStore);
import { useI18n } from 'vue-i18n'
const { locale } = useI18n()

const showId = ref<number | null>(null);
const pdfUrl = ref<string>("");

const PdfViewer = defineAsyncComponent(
  () => import("~/components/ui/PdfViewer.vue")
);

const openViewer = (id: number) => {
  showId.value = id;
};

const closeViewer = () => {
  showId.value = null;
  pdfUrl.value = "";
};

const routeToEdit = (id: number) =>
  localePath({ name: "contract-templates-edit", params: { id } });

const deleteTemplate = async (id: number) => {
  if (!confirm("¿Eliminar la plantilla?")) return;
  await contractsStore.deleteContractTemplate(id);
};

onMounted(async () => {
  try {
    await contractsStore.fetchContractTemplates();
  } catch (error) {
    console.error(error);
  }
});

watch(showId, async (newId: number) => {
  if (!newId) return;

  const blob = await contractsStore.fetchContractTemplatePdfBlob(newId);

  const base64 = await blobToBase64(blob);
  pdfUrl.value = `data:application/pdf;base64,${base64}`;
});

const blobToBase64 = (blob: Blob): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onloadend = () => resolve((reader.result as string).split(",")[1]);
    reader.onerror = reject;
    reader.readAsDataURL(blob);
  });
};

const getPreviewUrl = async (tplId: number) => {
  try {
    return await contractsStore.fetchContractPdf(tplId);
  } catch (error) {
    console.error(error);
    return "";
  }
};
</script>

<style scoped>
</style>