<template>
  <Dialog
    v-model:visible="visible"
    :modal="true"
    :closable="false"
    :style="{ width: '92vw', maxWidth: '480px' }"
    class="p-fluid"
  >
    <template #header>
      <div class="flex items-center gap-2">
        <i class="pi pi-upload" style="color: #0046de" />
        <span class="font-semibold">{{ $t("contracts.uploadSigned") }}</span>
      </div>
    </template>

    <div class="space-y-6">
      <div>
        <label
          for="docName"
          class="block mb-2 text-sm font-medium text-gray-700"
        >
          {{ $t("contracts.documentName") }}
        </label>
        <InputText
          id="docName"
          v-model="docName"
          :pt="{
            root: { class: 'focus:!ring-info focus:!border-info' },
          }"
        />
      </div>

      <div>
        <label class="block mb-2 text-sm font-medium text-gray-700">
          {{ $t("contracts.uploadFile") }}
        </label>

        <FileUpload
          ref="fileUploadRef"
          name="signedPdf"
          accept="application/pdf"
          mode="advanced"
          customUpload
          :auto="false"
          :maxFileSize="10_000_000"
          :showUploadButton="false"
          :showCancelButton="false"
          :pt="{
            root: {
              class:
                'border-2 border-dashed border-gray-300 rounded-lg relative cursor-pointer',
              style: { minHeight: '150px' },
              onClick: () => fileUploadRef?.choose(), // Click directo en el contenedor
            },
            pcChooseButton: { root: { class: 'hidden' } },
            input: { class: 'hidden' }, // Ocultamos el input nativo
          }"
          @select="onSelect"
        >
          <template #empty>
            <div
              class="flex flex-col items-center justify-center text-center text-info/80 py-10 h-full"
            >
              <i class="pi pi-cloud-upload text-2xl mb-2" />
              <p class="font-medium">{{ $t("contracts.clickOrDrop") }}</p>
              <p class="text-xs mt-1">PDF (max 10&nbsp;MB)</p>
            </div>
          </template>
        </FileUpload>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <Button
          severity="secondary"
          :label="$t('common.cancel')"
          outlined
          @click="close"
        />
        <Button
          :label="$t('common.upload')"
          :disabled="!file"
          @click="emitUpload"
          :style="{ backgroundColor: '#0046de', borderColor: '#0046DE' }"
          class="text-white"
        />
      </div>
    </template>
  </Dialog>
</template>

<script setup lang="ts">
const props = defineProps<{ visible: boolean }>();
const emit = defineEmits<{
  (e: "update:visible", value: boolean): void;
  (e: "submit", payload: { file: File; name: string }): void;
}>();

const docName = ref("");
const file = ref<File | null>(null);
const fileUploadRef = ref(); 
const isFocused = ref(false); 

const manualChooseFile = () => {
  fileUploadRef.value?.choose();
};

const onSelect = (e: any) => {
  file.value = e.files?.[0] ?? null;
  if (!docName.value && file.value) docName.value = file.value.name;
};

const clearFile = () => {
  file.value = null;
};

const close = () => {
  emit("update:visible", false);
};

const emitUpload = () => {
  if (!file.value) return;
  emit("submit", {
    file: file.value,
    name: docName.value,
  });
  close();
};

const visible = ref(props.visible);
watch(
  () => props.visible,
  (v) => (visible.value = v)
);
watch(visible, (v) => emit("update:visible", v));
</script>

<style scoped>
:deep(.p-dialog) {
  max-height: 95vh;
}
:deep(.border-info) {
  border-color: #0046de !important;
}
:deep(.p-fileupload-content .p-fileupload-file-thumbnail) {
  display: none !important;
}
</style>
