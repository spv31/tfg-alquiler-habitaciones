<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Paso 0 · selector de plantilla -->
    <section v-if="step === 0">
      <h2 class="text-2xl text-center font-semibold mb-6">
        {{ t("contracts.selectTemplate") }}
      </h2>

      <!-- loader -->
      <div v-if="loading" class="flex justify-center py-10">
        <span class="loader" />
      </div>

      <!-- grid -->
      <div
        v-else
        class="grid gap-8 mt-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3"
      >
        <div
          v-for="tpl in contractTemplates"
          :key="tpl.id"
          class="flex flex-col"
        >
          <div
            class="group relative bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-blue-100 flex-1 flex flex-col"
          >
            <!-- header -->
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
                    ? t("contracts.defaultTemplate")
                    : t("contracts.customTemplate")
                }}
              </p>
            </div>

            <!-- miniatura -->
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
                      class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 text-sm text-gray-400"
                    >
                      {{ t("contracts.noPreview") }}
                    </div>
                  </template>
                </Suspense>
              </div>
            </div>

            <!-- acciones -->
            <div class="p-3 bg-gray-50 border-t border-gray-100">
              <div class="grid gap-2 grid-cols-2">
                <button
                  class="button-primary px-3 py-2 text-sm rounded-md flex items-center justify-center"
                  @click="choose(tpl)"
                >
                  {{ t("common.select") }}
                </button>

                <button
                  class="button-secondary px-3 py-2 text-sm rounded-md flex items-center justify-center"
                  @click="openViewer(tpl.id)"
                >
                  {{ t("common.view") }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- visor PDF -->
      <teleport to="body">
        <div
          v-if="showId"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50"
          @click.self="closeViewer"
        >
          <div
            class="bg-white w-11/12 md:w-[70%] h-[80vh] rounded-xl shadow-2xl flex flex-col"
          >
            <div
              class="p-3 flex justify-between items-center border-b bg-gray-50 sticky top-0"
            >
              <h3 class="font-medium">{{ t("contracts.preview") }}</h3>
              <button @click="closeViewer" class="p-1 hover:text-red-600">
                ✕
              </button>
            </div>
            <iframe
              v-if="pdfUrl"
              :src="pdfUrl"
              class="flex-1 w-full border-none"
            />
          </div>
        </div>
      </teleport>
    </section>

    <!-- Paso 1 · formulario de tokens -->
    <section v-else-if="step === 1" class="space-y-8">
      <!-- Título fuera del card con mejor jerarquía -->
      <div class="text-center space-y-2 mb-8">
        <h2 class="text-2xl font-bold text-gray-900">
          {{ t("contracts.stepFillTokens") }}
        </h2>

        <p class="text-lg text-gray-600">
          {{ selectedTemplate!.name }}
          <span class="text-sm text-blue-600 ml-2"
            >({{ tokens.length }} campos)</span
          >
        </p>

        <!-- Formulario de datos del contrato -->
        <div
          class="bg-white border border-gray-200 rounded-xl shadow-lg p-6 space-y-6"
        >
          <h3
            class="font-semibold text-lg text-gray-800 flex items-center gap-2"
          >
            <svg
              class="w-5 h-5 text-blue-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
              />
            </svg>
            {{ t("contracts.contractData") }}
          </h3>

          <div class="grid md:grid-cols-3 gap-4">
            <!-- Primera fila: 3 inputs -->
            <input
              type="number"
              min="0"
              step="0.01"
              v-model.number="business.price"
              class="custom-input"
              :placeholder="t('contracts.price') + ' *'"
            />

            <input
              type="number"
              min="0"
              step="0.01"
              v-model.number="business.deposit"
              class="custom-input"
              :placeholder="t('contracts.deposit') + ' *'"
            />

            <input
              type="date"
              v-model="business.start_date"
              class="custom-input [&::-webkit-calendar-picker-indicator]:bg-gray-400 hover:[&::-webkit-calendar-picker-indicator]:bg-gray-500"
              :placeholder="t('contracts.startDate') + ' *'"
            />

            <!-- Segunda fila: fecha fin + checkbox + select -->
            <div class="md:col-span-3 grid md:grid-cols-3 gap-4">
              <!-- Columna izquierda - Fecha fin -->
              <div class="md:col-span-1">
                <input
                  type="date"
                  v-model="business.end_date"
                  class="custom-input w-full [&::-webkit-calendar-picker-indicator]:bg-gray-400 hover:[&::-webkit-calendar-picker-indicator]:bg-gray-500"
                  :placeholder="t('contracts.endDate')"
                />
              </div>

              <!-- Columna central - Checkbox -->
              <div class="md:col-span-1 flex items-center justify-center">
                <label class="flex items-center gap-3 cursor-pointer group">
                  <div class="relative flex items-center">
                    <input
                      type="checkbox"
                      v-model="business.utilities_included"
                      class="absolute opacity-0 w-0 h-0"
                    />
                    <div
                      class="w-5 h-5 border-2 border-gray-300 rounded-md group-hover:border-blue-300 transition-all flex items-center justify-center"
                    >
                      <svg
                        v-show="business.utilities_included"
                        class="w-3.5 h-3.5 text-blue-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="3"
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                    </div>
                  </div>
                  <span
                    class="text-sm text-gray-700 group-hover:text-gray-900 transition-colors"
                  >
                    {{ t("contracts.utilitiesIncluded") }}
                  </span>
                </label>
              </div>

              <!-- Columna derecha - Select -->
              <div v-if="!business.utilities_included" class="md:col-span-1">
                <div class="relative w-full">
                  <select
                    v-model="business.utilities_payer"
                    class="custom-input appearance-none pr-8 cursor-pointer w-full"
                  >
                    <option :value="null" disabled selected>
                      {{ t("common.select") }}
                    </option>
                    <option value="tenant">
                      {{ t("contracts.tenant") }}
                    </option>
                    <option value="owner">{{ t("contracts.owner") }}</option>
                    <option value="shared">
                      {{ t("contracts.shared") }}
                    </option>
                  </select>
                  <svg
                    class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Tercera fila: input porcentaje -->
            <div
              v-if="business.utilities_payer === 'shared'"
              class="md:col-span-3 flex justify-center"
            >
              <input
                type="number"
                min="0"
                max="100"
                step="0.01"
                v-model.number="business.utilities_proportion"
                class="custom-input w-full max-w-xs"
                :placeholder="t('contracts.sharedPercent')"
              />
            </div>
          </div>

          <small class="text-sm text-gray-500 block text-center">
            * {{ t("contracts.requiredField") }}
          </small>
        </div>
      </div>

      <!-- Sección principal de campos y preview -->
      <div class="grid gap-8 md:grid-cols-2 lg:gap-12">
        <!-- Formulario de tokens -->
        <div
          class="bg-white border border-gray-200 rounded-xl shadow-lg hover:shadow-xl transition-shadow"
        >
          <div class="p-6 pb-4">
            <h3
              class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2"
            >
              <svg
                class="w-5 h-5 text-blue-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z"
                />
              </svg>
              {{ t("contracts.fillData") }}
            </h3>

            <form
              class="space-y-5 max-h-[70vh] overflow-y-auto pr-3 custom-scroll"
            >
              <div v-for="key in tokens" :key="key" class="space-y-2">
                <label class="text-sm font-medium text-gray-700 capitalize">
                  {{ prettyLabel(key) }}
                </label>
                <input
                  v-model="tokenValues[key]"
                  type="text"
                  placeholder="Ingrese el valor"
                  class="custom-input w-full px-4 py-2.5"
                />
              </div>
            </form>
          </div>

          <!-- Botones de navegación -->
          <div class="border-t p-6 bg-gray-50/50">
            <div class="flex gap-4">
              <button
                type="button"
                @click="step = 0"
                class="button-secondary flex-1 py-3 px-6 text-base font-medium shadow-sm hover:shadow-md transition-all"
              >
                {{ t("common.back") }}
              </button>
              <button
                @click="step = 2"
                class="button-primary flex-1 py-3 px-6 text-base font-medium shadow-sm hover:shadow-md transition-all flex items-center justify-center"
              >
                {{ t("common.continue") }}
                <svg
                  class="w-4 h-4 ml-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Vista previa -->
        <div
          class="bg-white border border-gray-200 rounded-xl shadow-lg h-[90vh] flex flex-col"
        >
          <div class="p-6 pb-4 border-b border-gray-200">
            <h3
              class="text-lg font-semibold text-gray-800 flex items-center gap-2"
            >
              <svg
                class="w-5 h-5 text-gray-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              {{ t("contracts.livePreview") }}
            </h3>
          </div>

          <div
            class="flex-1 overflow-y-auto p-6 bg-gradient-to-b from-gray-50 to-white"
          >
            <div
              v-html="htmlWithTokensReplaced"
              class="prose max-w-none bg-white p-6 rounded-lg shadow-sm border border-gray-100"
            />
            <div
              v-if="!Object.values(tokenValues).some((v) => v)"
              class="h-full flex items-center justify-center text-gray-400 text-sm"
            >
              Completa los campos para ver la previsualización
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Paso 2 · preview final -->
    <section v-else class="space-y-8">
      <Alert
        v-if="alertMessage"
        :message="alertMessage"
        type="error"
        class="mb-4"
        @close="alertMessage = null"
      />
      <div class="text-center space-y-3 mb-8">
        <h2 class="text-2xl font-bold text-gray-900">
          {{ t("contracts.finalPreview") }}
        </h2>
        <div class="flex items-center justify-center gap-2">
          <p class="text-lg text-gray-600">
            {{ selectedTemplate!.name }}
          </p>
          <span class="text-sm text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
            {{ tokens.length }} {{ t("contracts.fieldsFilled") }}
          </span>
        </div>
      </div>

      <div class="max-w-4xl mx-auto">
        <div
          class="bg-white border-2 border-gray-100 rounded-xl shadow-lg overflow-hidden"
        >
          <div class="p-6 bg-gradient-to-b from-gray-50 to-white">
            <div
              class="prose max-w-none mx-auto bg-white p-8 rounded-lg shadow-sm border border-gray-100 overflow-y-auto max-h-[70vh]"
              v-html="htmlWithTokensReplaced"
            />
          </div>

          <div class="border-t p-6 bg-gray-50/50">
            <div class="flex flex-col sm:flex-row gap-4 justify-between">
              <button
                type="button"
                @click="step = 1"
                class="button-secondary px-8 py-3.5 text-base w-full sm:w-auto"
              >
                <svg
                  class="w-5 h-5 mr-2 inline -ml-1"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                  />
                </svg>
                {{ t("common.backToEdit") }}
              </button>
              <button
                type="button"
                class="button-primary px-8 py-3.5 text-base w-full sm:w-auto flex items-center justify-center"
                @click="submitContract"
              >
                {{ t("contracts.generateDocument") }}
                <svg
                  class="w-5 h-5 ml-2 -mr-1"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
type ContractWizardProps = {
  tenantId: number;
  propertyId: number;
  roomId: number | null;
};
const props = defineProps<ContractWizardProps>();

import type { StoreContractPayload } from "~/types/contract";
import { useContractsStore } from "~/store/contracts";
const contractsStore = useContractsStore();
const { contractTemplates, loading } = storeToRefs(contractsStore);
const alertMessage = ref<string | null>(null);

import { useI18n } from "vue-i18n";
import type { ContractTemplate } from "~/types/contractTemplate";
const { t, locale } = useI18n();

const PreviewImage = defineAsyncComponent(
  () => import("~/components/ui/PreviewImage.vue")
);

const step = ref<0 | 1 | 2>(0);
const selectedTemplate = ref<ContractTemplate | null>(null);

onMounted(async () => {
  if (!contractTemplates.value.length) {
    await contractsStore.fetchContractTemplates();
  }
});

const showId = ref<number | null>(null);
const pdfUrl = ref<string>("");

const openViewer = async (id: number) => {
  showId.value = id;
  const blobUrl = await contractsStore.fetchContractTemplatePdf(id);
  const blob = await fetch(blobUrl).then((r) => r.blob());
  pdfUrl.value = await blobToDataUrl(blob);
};

const closeViewer = () => {
  showId.value = null;
  pdfUrl.value = "";
};

const blobToDataUrl = (blob: Blob) => {
  return new Promise<string>((res, rej) => {
    const r = new FileReader();
    r.onload = () => res(r.result as string);
    r.onerror = rej;
    r.readAsDataURL(blob);
  });
};

const choose = (tpl: ContractTemplate) => {
  selectedTemplate.value = tpl;
  tokens.value = extractTokens(tpl.content);

  tokens.value.forEach((k) => {
    tokenValues[k] ||= "";
  });
  step.value = 1;
};

const tokens = ref<string[]>([]);
const tokenValues = reactive<Record<string, string>>({});

const extractTokens = (html: string): string[] => {
  const re = /data-token="(.*?)"/g;
  const found = new Set<string>();
  let match;
  while ((match = re.exec(html)) !== null) {
    found.add(match[1]);
  }
  return [...found];
};

const prettyLabel = (key: string) => {
  return key.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const htmlWithTokensReplaced = computed(() => {
  if (!selectedTemplate.value) return "";
  let html = selectedTemplate.value.content;
  for (const [key, val] of Object.entries(tokenValues)) {
    const rgx = new RegExp(
      `(data-token="${key}".*?>)([\\s\\S]*?)(<\\/span>)`,
      "g"
    );
    html = html.replace(rgx, `$1${val || "____________"}$3`);
  }
  return html;
});

interface BusinessForm {
  price: number | null;
  deposit: number | null;
  utilities_included: boolean;
  utilities_payer: "tenant" | "owner" | "shared" | null;
  utilities_proportion: number | null;
  start_date: string;
  end_date: string;
}

const business = reactive<BusinessForm>({
  price: null,
  deposit: null,
  utilities_included: false,
  utilities_payer: null,
  utilities_proportion: null,
  start_date: "",
  end_date: "",
});

const autoTokenMap: Record<string, keyof BusinessForm> = {
  renta_anual: "price",
  fianza_importe: "deposit",
  gastos_incluidos: "utilities_included",
  gastos_paga: "utilities_payer",
  gastos_porcentaje: "utilities_proportion",
  fecha_inicio: "start_date",
  fecha_fin: "end_date",
};

const payerLabel: Record<"owner" | "tenant" | "shared", string> = {
  owner: t("contracts.owner"),
  tenant: t("contracts.tenant"),
  shared: t("contracts.shared"),
};

watch(
  business,
  () => {
    if (business.utilities_included) {
      business.utilities_payer = "owner";
      business.utilities_proportion = 100;
    } else if (business.utilities_payer !== "shared") {
      business.utilities_proportion = null;
    }

    for (const [token, field] of Object.entries(autoTokenMap)) {
      const v = business[field];
      tokenValues[token] =
        v === null || v === undefined || v === ""
          ? ""
          : typeof v === "boolean"
          ? v
            ? "Sí"
            : "No"
          : String(v);
    }

    tokenValues["gastos_paga"] = business.utilities_payer
      ? payerLabel[business.utilities_payer]
      : "";

    tokenValues["gastos_porcentaje"] =
      business.utilities_payer === "shared" && business.utilities_proportion
        ? String(business.utilities_proportion)
        : "";

    tokenValues["renta_mensual"] = business.price
      ? (business.price / 12).toFixed(2)
      : "";
  },
  { deep: true, immediate: true }
);

const validate = (): string[] => {
  const errors: string[] = [];

  if (!selectedTemplate.value) errors.push(t("errors.noTemplateSelected"));

  if (!business.price) errors.push(t("errors.priceRequired"));
  if (!business.deposit) errors.push(t("errors.depositRequired"));
  if (!business.start_date) errors.push(t("errors.startDateRequired"));

  if (!business.utilities_included && !business.utilities_payer)
    errors.push(t("errors.utilitiesPayerRequired"));

  if (
    business.utilities_payer === "shared" &&
    (business.utilities_proportion === null ||
      business.utilities_proportion < 0 ||
      business.utilities_proportion > 100)
  )
    errors.push(t("errors.sharedPercentRequired"));

  return errors;
};


const buildPayload = (): StoreContractPayload => ({
  contract_template_id: selectedTemplate.value!.id,
  property_id: props.propertyId ?? null,
  room_id: props.roomId === 0 ? null : props.roomId,
  tenant_id: props.tenantId,

  type: selectedTemplate.value!.type ?? null,

  price: Number(business.price),
  deposit: Number(business.deposit),

  utilities_included: business.utilities_included,
  utilities_payer: business.utilities_payer,
  utilities_proportion: business.utilities_proportion,

  start_date: business.start_date,
  end_date: business.end_date || null,
  extension_date: null,

  status: "draft",

  final_content: htmlWithTokensReplaced.value,
  token_values: { ...tokenValues },
});

const saving = ref(false);

const submitContract = async () => {
  alertMessage.value = null;

  const errors = validate();
  if (errors.length) {
    alertMessage.value = errors[0];
    console.log(errors);
    return;
  }

  try {
    saving.value = true;
    const payload = buildPayload();
    console.log('Payload: ', payload);
    await contractsStore.saveContract(payload);

    const successQuery = { msg: "contract_created" };

    if (props.roomId && props.roomId !== 0) {
      navigateTo(`/${locale.value}/properties/${props.propertyId}/rooms/${props.roomId}?msg=${successQuery}`)
    } else {
      navigateTo(`/${locale.value}/properties/${props.propertyId}?msg=${successQuery}`)
    }
  } catch (err: any) {
    console.error(err);
    alertMessage.value = err?.data?.message || t("errors.contractCreateFailed");
    step.value = 2;
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.loader {
  @apply h-6 w-6 rounded-full border-4 border-blue-500 border-t-transparent animate-spin;
}

.custom-scroll {
  scrollbar-width: thin;
  scrollbar-color: #d1d5db #f3f4f6;
}

.custom-scroll::-webkit-scrollbar {
  width: 8px;
}

.custom-scroll::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 4px;
}

.custom-scroll::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;
}

.custom-scroll::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
