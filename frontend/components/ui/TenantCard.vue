<template>
  <div
    class="bg-white shadow-lg rounded-xl p-8 space-y-6 transition-all hover:shadow-3xl group"
  >
    <Alert
      v-if="!hasContract || isDraft || isOwnerSigned"
      :type="!hasContract ? 'info' : isOwnerSigned ? 'success' : 'warning'"
      :message="
        !hasContract
          ? t('contracts.alert.noContract')
          : isOwnerSigned
            ? t('contracts.alert.signedByOwner')
            : t('contracts.alert.pendingSignature')
      "
      class="rounded-lg"
    />

    <div class="flex items-center justify-between border-b pb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-800">
          {{ $t("common.tenantData") }}
        </h2>
        <span
          v-if="isActive"
          class="text-sm text-yellow-500 font-semibold flex items-center gap-1 mt-1"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0
               00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8
               2.034a1 1 0 00-.364 1.118l1.07
               3.292c.3.921-.755 1.688-1.54
               1.118l-2.8-2.034a1 1 0
               00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1
               1 0 00-.364-1.118L2.98
               8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0
               00.951-.69l1.07-3.292z"
            />
          </svg>
          {{ $t("common.activeContract") }}
        </span>
      </div>
      <div class="relative">
        <img
          :src="tenant.profile_picture || defaultAvatar"
          :alt="tenant.name"
          class="w-16 h-16 rounded-full border-4 border-white shadow-lg object-cover"
        />
        <div class="absolute -bottom-1 -right-1 bg-success p-1 rounded-full">
          <svg
            class="w-4 h-4 text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 13l4 4L19 7"
            />
          </svg>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-1">
        <label class="label flex items-center gap-2 text-gray-500">
          <svg
            class="w-5 h-5 text-info"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            />
          </svg>
          {{ $t("common.name") }}
        </label>
        <p class="text-lg font-semibold text-gray-800">{{ tenant.name }}</p>
      </div>

      <div class="space-y-1">
        <label class="label flex items-center gap-2 text-gray-500">
          <svg
            class="w-5 h-5 text-info"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
            />
          </svg>
          {{ $t("common.email") }}
        </label>
        <p class="text-base text-gray-600 break-all font-mono">
          {{ tenant.email }}
        </p>
      </div>

      <div class="space-y-1" v-if="tenant.phone_number">
        <label class="label flex items-center gap-2 text-gray-500">
          <svg
            class="w-5 h-5 text-info"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
            />
          </svg>
          {{ $t("common.phone") }}
        </label>
        <p class="text-base text-gray-600 flex items-center gap-2">
          {{ tenant.phone_number }}
        </p>
      </div>

      <div class="space-y-1">
        <label class="label flex items-center gap-2 text-gray-500">
          <svg
            class="w-5 h-5 text-info"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
            />
          </svg>
          {{ $t("common.contractStart") }}
        </label>
        <p class="text-base text-gray-800">
          {{
            contract?.start_date
              ? new Date(contract?.start_date).toLocaleDateString(locale, {
                  day: "2-digit",
                  month: "2-digit",
                  year: "numeric",
                })
              : "-"
          }}
        </p>
      </div>

      <p
        v-if="hasContract && !hasCurrentMonthRentPayment"
        class="flex items-center gap-1 text-sm font-medium text-yellow-600"
      >
        <i class="pi pi-exclamation-triangle text-xs"></i>
        {{ $t("rentPayments.missingCurrentMonth") }}
      </p>

      <p
        v-if="hasContract && hasCurrentMonthRentPayment"
        class="flex items-center gap-1 text-sm font-medium text-green-600"
      >
        <i class="pi pi-check-circle text-xs"></i>
        {{ $t("rentPayments.currentMonthAlreadyCreated") }}
      </p>

      <button
        v-if="hasContract && !hasCurrentMonthRentPayment"
        @click="openCreateRP(contract!.id)"
        class="flex items-center gap-1.5 text-sm button-primary text-white px-3.5 py-1.5 rounded-full transition-all shadow-sm hover:shadow-md active:scale-[0.98] ring-1 ring-primary-500/20"
        :title="$t('rentPayments.create')"
      >
        <i class="pi pi-plus text-xs"></i>
        <span class="font-medium tracking-tight">{{
          $t("rentPayments.create")
        }}</span>
      </button>
    </div>

    <div class="flex flex-wrap gap-6 pt-8 border-t border-gray-200">
      <CircleIconButton :label="$t('common.chat')" @click="onChat">
        <template #icon>
          <i class="pi pi-comments text-info text-xl"></i>
        </template>
      </CircleIconButton>

      <CircleIconButton :label="$t('common.move')" @click="moveTenant">
        <template #icon>
          <i class="pi pi-directions-alt text-purple-600 text-lg"></i>
        </template>
      </CircleIconButton>

      <CircleIconButton
        :label="hasContract ? $t('contracts.view') : $t('contracts.generate')"
        @click="hasContract ? viewContract(contract!.id) : startContractFlow()"
      >
        <template #icon>
          <i class="pi pi-file text-info text-xl"></i>
        </template>
      </CircleIconButton>

      <CircleIconButton
        v-if="
          hasContract &&
          contract?.status !== 'active' &&
          contract?.status !== 'signed_by_owner'
        "
        :label="$t('contracts.uploadSigned')"
        @click="triggerUpload"
      >
        <template #icon>
          <i class="pi pi-upload text-info text-xl"></i>
        </template>
      </CircleIconButton>

      <CircleIconButton
        :label="$t('rentPayments.list')"
        @click="showList = true"
      >
        <template #icon>
          <i class="pi pi-credit-card text-blue-600 text-xl"></i>
        </template>
      </CircleIconButton>
    </div>

    <ContractViewerModal
      v-if="hasContract"
      :visible="showViewer"
      :pdfUrl="pdfUrl"
      :title="$t('contracts.viewContract')"
      @close="showViewer = false"
    />
    <UploadSignedDialog v-model:visible="showUpload" @submit="handleUpload" />
    <ChatDialog
      v-model:visible="showChat"
      :context="{ ownerId: tenant.property_id, tenantId: tenant.id }"
      :rentable="{
        type: tenant.room_id ? 'Room' : 'Property',
        rentable: tenant,
      }"
      :current-user-id="currentUserId!"
      :current-user-role="currentUserRole!"
    />
  </div>
  <AddRentPaymentForm
    v-model:visible="showCreate"
    v-if="selectedContract"
    :contract-id="selectedContract"
    :default-amount="contract!.price / 12"
    @created="
      () => paymentsStore.fetchRentPayments({ contract_id: selectedContract })
    "
  />
  <Dialog
    v-model:visible="showList"
    :modal="true"
    :header="$t('rentPayments.listTitle')"
    style="width: 95%; max-width: 700px"
  >
    <div class="overflow-x-auto">
      <DataTable
        :value="filteredTablePayments"
        dataKey="id"
        :loading="loading"
        responsiveLayout="stack"
        breakpoint="640px"
        class="p-datatable-sm"
      >
        <Column field="period" :header="$t('rentPayments.period')">
          <template #body="{ data }">
            {{ formatPeriod(data) }}
          </template>
        </Column>

        <Column field="due_date" :header="$t('rentPayments.due')">
          <template #body="{ data }">
            {{ formatDate(data.due_date) }}
          </template>
        </Column>

        <Column
          field="amount"
          :header="$t('rentPayments.amount')"
          class="text-right"
        >
          <template #body="{ data }">{{ data.amount.toFixed(2) }} €</template>
        </Column>

        <Column field="status" :header="$t('rentPayments.status')">
          <template #body="{ data }">
            <Tag
              :value="$t('rentPayments.' + data.status)"
              :severity="data.status === 'paid' ? 'success' : 'warn'"
            />
          </template>
        </Column>

        <Column field="paid_at" :header="$t('rentPayments.paidAt')">
          <template #body="{ data }">
            {{ data.paid_at ? formatDateTime(data.paid_at) : "-" }}
          </template>
        </Column>
      </DataTable>
    </div>
  </Dialog>
</template>
<script setup lang="ts">
import { parseISO, format } from "date-fns";

import { useContractsStore } from "~/store/contracts";
import { usePaymentsStore } from "~/store/payments";
import { useAuthStore } from "~/store/auth";

import type { Tenant } from "~/types/tenant";
import type { Contract } from "../../types/contract";
import defaultAvatar from "~/assets/images/default.jpg";

import { useMyToast } from "#imports";
const { success, error: errorToast } = useMyToast();
const { locale, t } = useI18n();

const props = defineProps<{ tenant: Tenant }>();

const contractsStore = useContractsStore();
const paymentsStore = usePaymentsStore();
const authStore = useAuthStore();

const showCreate = ref(false);
const showList = ref(false);
const showViewer = ref(false);
const showUpload = ref(false);
const showChat = ref(false);
const pdfUrl = ref("");
const selectedContract = ref<number | null>(null);

const contract = computed(() => props.tenant.contract);
const hasContract = computed(() => !!contract.value);
const isDraft = computed(() => contract.value?.status === "draft");
const isOwnerSigned = computed(
  () => contract.value?.status === "signed_by_owner"
);
const isActive = computed(() => contract.value?.status === "active");

const tablePayments = shallowRef<any[]>([]);
const loading = computed(() => paymentsStore.loading);

watch(
  () => [showList.value, contract.value?.id],
  async ([visible, id]) => {
    if (!visible || !id) return;
    tablePayments.value = [];
    await paymentsStore.fetchRentPayments({ contract_id: id });
    tablePayments.value = paymentsStore.rentPayments.map((p) => ({
      ...toRaw(p),
      amount: Number(p.amount),
    }));
  }
);

const formatDate = (d: string) => format(new Date(d), "dd/MM/yyyy");
const formatDateTime = (d: string) => format(new Date(d), "dd/MM/yyyy HH:mm");
const formatPeriod = (r: any) =>
  `${formatDate(r.period_start)} - ${formatDate(r.period_end)}`;

const hasCurrentMonthRentPayment = computed(() => {
  if (!hasContract.value) return true;

  const today = new Date();
  const y = today.getFullYear();
  const m = today.getMonth();

  return paymentsStore.rentPayments
    .filter((rp) => rp.contract?.id === contract.value!.id)
    .some((rp) => {
      const d = parseISO(rp.period_start);
      return d.getFullYear() === y && d.getMonth() === m;
    });
});

const filteredTablePayments = computed(() =>
  !contract.value
    ? []
    : tablePayments.value.filter((p) => p.contract.id === contract.value!.id)
);

const openCreateRP = (id: number) => {
  selectedContract.value = id;
  showCreate.value = true;
};

const onChat = () => (showChat.value = true);
const moveTenant = () =>
  navigateTo(`/${locale.value}/tenants/${props.tenant.id}/move`);
const startContractFlow = () =>
  navigateTo({
    path: `/${locale.value}/contracts/create`,
    query: {
      tenantId: props.tenant.id,
      propertyId: props.tenant.property_id,
      roomId: props.tenant.room_id ?? null,
    },
  });

const viewContract = async (id: number) => {
  try {
    const blob = await contractsStore.fetchContractPdfBlob(id);
    pdfUrl.value = URL.createObjectURL(blob);
    showViewer.value = true;
  } catch (err) {
    console.error("Error loading contract PDF", err);
  }
};

const triggerUpload = () => (showUpload.value = true);

const handleUpload = async (file: { file: File; name: string }) => {
  try {
    const updated: Contract = await contractsStore.uploadSigned(
      contract.value.id,
      file
    );
    if (updated.status === "signed_by_owner") {
      success(t("contracts.signedUploadOwner"));
    } else if (updated.status === "active") {
      success(t("contracts.signedUploadTenant"));
    }
  } catch {
    errorToast(t("common.genericError"));
  }
};

watch(
  () => contract.value?.id,
  async (id) => {
    // 1️⃣ limpias siempre los payments antiguos
    paymentsStore.rentPayments = [];
    tablePayments.value = [];

    // 2️⃣ si hay contrato, cargas los suyos
    if (id) {
      console.log();
      await paymentsStore.fetchRentPayments({ contract_id: id });
      // y aquí, si ya tienes el diálogo abierto, vuelve a rellenar la tabla
      if (showList.value) {
        tablePayments.value = paymentsStore.rentPayments.map((p) => ({
          ...toRaw(p),
          amount: Number(p.amount),
        }));
        console.log("Tabla de pagos: ", tablePayments.value);
      }
    }
  },
  { immediate: true }
);

const currentUserId = computed(() => authStore.user?.id);
const currentUserRole = computed(() => authStore.user?.role);
</script>

<style scoped></style>
