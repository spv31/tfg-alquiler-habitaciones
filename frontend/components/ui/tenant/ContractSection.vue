<template>
  <Card class="rounded-xl border border-info/10 p-6">
    <template #title>
      <div class="flex items-center gap-2 pb-4">
        <i class="pi pi-file-pdf text-2xl text-info"></i>
        <h3 class="text-lg font-semibold text-gray-900">Contrato actual</h3>
      </div>
    </template>

    <template #content>
      <div v-if="!contract" class="text-gray-500 text-center py-8">
        No hay contrato asociado a tu alquiler.
      </div>

      <div v-else class="space-y-6">
        <div
          class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-blue-50 rounded-lg"
        >
          <div class="mb-2">
            <p class="font-medium text-gray-900">Estado actual</p>
            <p class="text-sm text-gray-500">{{ statusText }}</p>
          </div>
          <Tag :value="badgeLabel" :severity="badgeSeverity" />
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
          <div class="flex flex-col items-stretch xl:col-span-3">
            <div
              class="relative group overflow-hidden aspect-[210/297] w-full border border-gray-200 border-b-0 rounded-t-lg"
            >
              <Suspense>
                <template #default>
                  <PreviewContract
                    :contract-id="contract.id"
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
                        cx="12"
                        cy="12"
                        r="10"
                        class="opacity-25"
                        stroke="currentColor"
                        stroke-width="4"
                      />
                      <path
                        fill="currentColor"
                        class="opacity-75"
                        d="M4 12a8 8 0 018-8v8H4z"
                      />
                    </svg>
                  </div>
                </template>
              </Suspense>
            </div>
            <div
              class="w-full p-3 bg-gray-50 border border-gray-200 border-t-0 rounded-b-lg"
            >
              <button
                v-if="canDownload"
                @click="openViewer"
                class="button-primary flex items-center justify-center w-full py-2 rounded-md"
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
                Ver contrato
              </button>
            </div>
          </div>

          <div class="space-y-4 xl:col-span-2">
            <div class="grid grid-cols-1 gap-y-4 text-sm">
              <div class="flex justify-between">
                <span class="font-medium text-gray-600">Inicio:</span>
                <span>{{ startDate }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium text-gray-600">Fin:</span>
                <span>{{ endDate }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium text-gray-600">Renta / mes:</span>
                <span>{{ priceFormatted }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium text-gray-600">Fianza:</span>
                <span>{{ depositFormatted }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium text-gray-600">Suministros:</span>
                <span>
                  {{
                    contract.utilities_included
                      ? "Incluidos"
                      : `Pagas ${utilitiesTenant}%`
                  }}
                </span>
              </div>
              <div class="flex flex-col sm:flex-row flex-wrap gap-4 mt-2">
                <Button
                  v-if="canSign"
                  label="Subir contrato"
                  icon="pi pi-upload"
                  severity="info"
                  class="w-full sm:w-auto whitespace-nowrap"
                  @click="triggerUpload"
                />
                <input
                  type="file"
                  ref="fileInput"
                  class="hidden"
                  accept="application/pdf"
                  @change="handleFileChange"
                />
              </div>
            </div>
          </div>
        </div>

        <PdfViewer
          :visible="showViewer"
          :pdf-url="pdfUrl"
          title="Contrato"
          @close="showViewer = false"
        />
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Card from "primevue/card";
import Button from "primevue/button";
import Tag from "primevue/tag";
import type { Contract } from "@/types/contract";
import { useContractsStore } from "~/store/contracts";

const props = defineProps<{ contract: Contract | null }>();
const contract = computed(() => props.contract);

const showViewer = ref(false);
const pdfUrl = ref<string | null>(null);

const toast = useMyToast();
const { t } = useI18n();

watch(showViewer, async (open) => {
  if (!open) {
    if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value);
    pdfUrl.value = null;
    return;
  }
  if (!contract.value) return;

  try {
    const blob = await useContractsStore().fetchContractPdfBlob(
      contract.value.id
    );
    pdfUrl.value = URL.createObjectURL(blob);
  } catch (err) {
    console.error(err);
    showViewer.value = false;
  }
});

const badgeLabel = computed(() => {
  switch (contract.value?.status) {
    case "draft":
      return "Falta firma";
    case "signed_by_owner":
      return "Falta firma";
    case "active":
      return "Activo";
    case "finished":
      return "Finalizado";
    default:
      return "Desconocido";
  }
});

const badgeSeverity = computed(() => {
  switch (contract.value?.status) {
    case "draft":
      return "warn";
    case "signed_by_owner":
      return "info";
    case "active":
      return "success";
    case "finished":
      return "secondary";
    default:
      return "secondary";
  }
});

const statusText = computed(() => {
  switch (contract.value?.status) {
    case "draft":
      return "Debe firmar el propietario.";
    case "signed_by_owner":
      return "Debe firmar el inquilino.";
    case "active":
      return "El contrato está activo.";
    case "finished":
      return "El contrato ha finalizado.";
    default:
      return "";
  }
});

const dateOptions = {
  day: "2-digit",
  month: "2-digit",
  year: "numeric",
} as const;

const startDate = computed(() =>
  contract.value
    ? new Date(contract.value.start_date).toLocaleDateString(
        "es-ES",
        dateOptions
      )
    : "—"
);

const endDate = computed(() =>
  contract.value
    ? new Date(contract.value.end_date!).toLocaleDateString(
        "es-ES",
        dateOptions
      )
    : "—"
);

const priceFormatted = computed(() =>
  contract.value
    ? (contract.value.price / 12).toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
      })
    : "—"
);

const depositFormatted = computed(() =>
  contract.value
    ? contract.value.deposit.toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
      })
    : "—"
);

const utilitiesTenant = computed(() =>
  !contract.value?.utilities_included
    ? 100 - Number(contract.value?.utilities_proportion)
    : null
);

const canSign = computed(() => contract.value?.status === "signed_by_owner");
const canDownload = computed(() =>
  Boolean(contract.value?.pdf_path_signed || contract.value?.pdf_path)
);

const openViewer = () => {
  if (canDownload.value) showViewer.value = true;
};

const fileInput = ref<HTMLInputElement | null>(null);

const triggerUpload = () => {
  fileInput.value?.click();
};

const handleFileChange = async (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (!input.files?.length || !contract.value) return;

  const file = input.files[0];
  try {
    await useContractsStore().uploadSigned(contract.value.id, {
      file,
      name: file.name,
    });
    toast.success(t('contracts.signed_success_message'));
  } catch (err) {
    console.error(err);
    toast.error(t('errors.contractSignFailed'));
  }
};

onMounted(() => {
  console.log("URL Contrato: ", contract.value?.pdf_path);
});
</script>

<style scoped></style>
