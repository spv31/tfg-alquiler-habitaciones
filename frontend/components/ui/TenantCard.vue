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
          class="text-sm text-success font-medium flex items-center gap-1 mt-1"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
        <p class="text-base text-gray-600">15/03/2024</p>
      </div>
    </div>

    <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200">
      <CircleIconButton :label="$t('common.chat')" @click="onChat">
        <template #icon>
          <svg
            class="w-6 h-6 text-info"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
            />
          </svg>
        </template>
      </CircleIconButton>

      <CircleIconButton :label="$t('common.move')" @click="moveTenant">
        <template #icon>
          <svg
            class="h-5 w-5 text-purple-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 15l7-7 7 7"
            />
          </svg>
        </template>
      </CircleIconButton>

      <CircleIconButton :label="$t('common.remove')" @click="onRemove">
        <template #icon>
          <svg
            class="h-6 w-6 text-red-600 mx-auto"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M19 7l-.867 
          12.142A2 2 0 0116.138 21H7.862a2 2 0 
          01-1.995-1.858L5 7m5 
          4v6m4-6v6m1-10V4a1 1 
          0 00-1-1h-4a1 1 
          0 00-1 1v3M4 
          7h16"
            />
          </svg>
        </template>
      </CircleIconButton>
      <CircleIconButton
        :label="hasContract ? $t('contracts.view') : $t('contracts.generate')"
        @click="hasContract ? viewContract(contract!.id) : startContractFlow()"
      >
        <template #icon>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6 text-info"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 16h8M8 12h8M8 8h8M4 4h16v16H4V4z"
            />
          </svg>
        </template>
      </CircleIconButton>
      <CircleIconButton
        v-if="hasContract"
        :label="$t('contracts.editContract')"
        @click="editContract(contract!.id)"
      >
        <template #icon>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6 text-info"
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
        </template>
      </CircleIconButton>

      <CircleIconButton
        v-if="hasContract"
        :label="$t('contracts.deleteContract')"
        @click="deleteContract(contract!.id)"
      >
        <template #icon>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6 text-red-600"
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
        </template>
      </CircleIconButton>
      <CircleIconButton
        v-if="hasContract && contract.status !== 'active'"
        :label="$t('contracts.uploadSigned')"
        @click="triggerUpload"
      >
        <template #icon>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6 text-info"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.213 3 21l1.787-4.5 12.075-13.013z"
            />
          </svg>
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
  </div>
</template>
<script setup lang="ts">
import { useContractsStore } from "~/store/contracts";

import type { Tenant } from "~/types/tenant";
import defaultAvatar from "~/assets/images/default.jpg";
import { useMyToast } from "#imports";
import type { Contract } from "../../types/contract";
const { success, error: errorToast, info } = useMyToast();

const props = defineProps<{
  tenant: Tenant;
}>();

const { locale, t } = useI18n();
const contractsStore = useContractsStore();

const contract = computed(() => props.tenant.contract);
const hasContract = computed(() => !!contract.value);
const isDraft = computed(() => contract.value?.status === "draft");
const isOwnerSigned = computed(
  () => contract.value?.status === "signed_by_owner"
);
const isActive = computed(() => contract.value?.status === "active");

const showViewer = ref(false);
const pdfUrl = ref("");
const showUpload = ref(false);
const selectedFile = ref<File | null>(null);

const ContractViewerModal = defineAsyncComponent(
  () => import("~/components/ui/ContractViewerModal.vue")
);
const CircleIconButton = defineAsyncComponent(
  () => import("~/components/ui/CircleIconButton.vue")
);
const UploadSignedDialog = defineAsyncComponent(
  () => import("~/components/ui/UploadSignedDialog.vue")
);
const Alert = defineAsyncComponent(() => import("~/components/ui/Alert.vue"));

const fileInput = ref<HTMLInputElement | null>(null);

const blobToBase64 = (blob: Blob): Promise<string> =>
  new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onloadend = () => resolve((reader.result as string).split(",")[1]);
    reader.onerror = reject;
    reader.readAsDataURL(blob);
  });

const startContractFlow = () => {
  navigateTo({
    path: `/${locale.value}/contracts/create`,
    query: {
      tenantId: props.tenant.id,
      propertyId: props.tenant.property_id,
      roomId: props.tenant.room_id ?? null,
    },
  });
};

const onChat = () => {};
const onRemove = () => {
  error("Eliminación de inquilino en progreso", 5000);
};
const moveTenant = () =>
  navigateTo(`/${locale.value}/tenants/${props.tenant.id}/move`);

const viewContract = async (id: number) => {
  try {
    const blob = await contractsStore.fetchContractPdfBlob(id);
    pdfUrl.value = URL.createObjectURL(blob);
    showViewer.value = true;
  } catch (err) {
    console.error("Error loading contract PDF", err);
  }
};

const editContract = async (id: number) => {
  try {
    info("Testing", 7500);
  } catch (err) {
    console.error(err);
  }
};

const deleteContract = async (id: number) => {
  try {
    info("Testing", 6700);
  } catch (err) {
    console.error(err);
  }
};

const triggerUpload = () => {
  showUpload.value = true;
};

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
  } catch (error) {
    errorToast(t("common.genericError"));
  }
};
</script>

<style scoped></style>
