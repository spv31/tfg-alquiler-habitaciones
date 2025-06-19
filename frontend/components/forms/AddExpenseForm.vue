<template>
  <form class="space-y-6" @submit.prevent="submit">
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ $t("expenses.modal.issueDate") }}
        </label>
        <DatePicker
          v-model="form.issue_date"
          date-format="dd/mm/yy"
          show-icon
          inputClass="focus:!border-info"
          panelClass="!bg-white !shadow-lg"
          class="w-full"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ $t("expenses.modal.dueDate") }}
        </label>
        <DatePicker
          v-model="form.due_date"
          date-format="dd/mm/yy"
          show-icon
          inputClass="focus:!border-info"
          panelClass="!bg-white !shadow-lg"
          class="w-full"
        />
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ $t("expenses.modal.periodStart") }}
        </label>
        <DatePicker
          v-model="form.period_start"
          date-format="dd/mm/yy"
          show-icon
          inputClass="focus:!border-info"
          panelClass="!bg-white !shadow-lg"
          class="w-full"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ $t("expenses.modal.periodEnd") }}
        </label>
        <DatePicker
          v-model="form.period_end"
          date-format="dd/mm/yy"
          show-icon
          inputClass="focus:!border-info"
          panelClass="!bg-white !shadow-lg"
          class="w-full"
        />
      </div>
    </div>
    <!-- <div class="grid md:grid-cols-2 gap-4">
      <DatePicker
        v-model="form.issue_date"
        date-format="dd/mm/yy"
        show-icon
        inputClass="focus:!border-info"
        panelClass="!bg-white !shadow-lg"
        class="w-full"
      />

      <DatePicker
        v-model="form.due_date"
        date-format="dd/mm/yy"
        show-icon
        inputClass="focus:!border-info"
        panelClass="!bg-white !shadow-lg"
        class="w-full"
      />
    </div> -->

    <Select
      v-model="form.category"
      :options="categories"
      option-label="label"
      option-value="value"
      :placeholder="$t('expenses.modal.categoryPlaceholder')"
      class="w-full"
    />

    <InputNumber
      v-model="form.total_amount"
      :placeholder="$t('expenses.modal.amountPlaceholder')"
      mode="currency"
      currency="EUR"
      locale="es-ES"
      class="w-full"
    />

    <Textarea
      v-model="form.description"
      auto-resize
      rows="3"
      :placeholder="$t('expenses.modal.descriptionPlaceholder')"
      class="w-full"
    />

    <label class="flex items-center gap-3">
      <!-- <ToggleSwitch v-model="form.remit_to_tenants" inputId="remitToggle" /> -->
      <ToggleSwitch
        v-model="form.remit_to_tenants"
        inputId="remitToggle"
        :pt="{
          root: {
            class: 'toggle-info',
          },
          handle: {},
          input: {},
          checked: {
            class: 'bg-info border-info',
          },
        }"
      />

      <span>{{ $t("expenses.modal.remitToTenants") }}</span>
    </label>

    <label v-if="!form.remit_to_tenants" class="flex items-center gap-3">
      <ToggleSwitch v-model="form.owner_paid" inputId="paidToggle" />
      <span>{{ $t("expenses.modal.ownerPaid") }}</span>
    </label>

    <FileUpload
      ref="fileUploadRef"
      name="attachment"
      accept="image/*,application/pdf"
      mode="advanced"
      custom-upload
      :auto="false"
      :maxFileSize="5242880"
      :showUploadButton="false"
      :showCancelButton="false"
      :pt="{
        root: {
          class:
            'border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-info transition-colors',
          style: { minHeight: '150px' },
          onClick: () => fileUploadRef?.choose(),
        },
        pcChooseButton: { root: { class: 'hidden' } },
        input: { class: 'hidden' },
      }"
      @select="onFileSelect"
    >
      <template #empty>
        <div
          class="flex flex-col items-center justify-center text-center text-info/80 pb-4 h-full"
        >
          <i class="pi pi-cloud-upload text-2xl mb-2" />
          <p class="font-medium">{{ $t("expenses.modal.chooseFile") }}</p>
          <p class="text-xs mt-1">PDF, JPG o PNG (máx. 5 MB)</p>
        </div>
      </template>
    </FileUpload>

    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
      <button
        type="button"
        class="button-secondary inline-flex items-center gap-2"
        @click="$emit('cancel')"
      >
        <i class="pi pi-times text-base" />
        <span>{{ $t("common.cancel") }}</span>
      </button>

      <button
        type="submit"
        class="button-primary inline-flex items-center gap-2 disabled:opacity-50"
      >
        <i class="pi pi-save text-base" />
        <span>{{ $t("common.save") }}</span>
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { format } from "date-fns";
import { usePaymentsStore } from "~/store/payments";
import { useMyToast } from "~/composables/useMyToast";
import InputNumber from "primevue/inputnumber";
import Textarea from "primevue/textarea";
import FileUpload from "primevue/fileupload";

const props = defineProps<{ propertyId: number }>();
const emit = defineEmits(["saved", "cancel"]);

const payments = usePaymentsStore();
const { t } = useI18n();
const toast = useMyToast();

const saving = ref(false);
const fileUploadRef = ref();

const form = reactive({
  issue_date: new Date(),
  due_date: new Date(),
  period_start: null as Date | null,
  period_end: null as Date | null,
  category: "general",
  remit_to_tenants: false,
  owner_paid: false,
  total_amount: null as number | null,
  description: "",
  file: null as File | null,
});

const categories = [
  { label: t("expenses.selectOptions.general"), value: "general" },
  { label: t("expenses.selectOptions.utility"), value: "utility" },
  { label: t("expenses.selectOptions.tax"), value: "tax" },
];

const onFileSelect = ({ files }: any) => {
  form.file = files[0] ?? null;
};

const toYMD = (d: Date) => format(d, "yyyy-MM-dd");

const submit = async () => {
  try {
    saving.value = true;

    const fd = new FormData();
    fd.append("property_id", String(props.propertyId));

    fd.append("issue_date", toYMD(form.issue_date));
    fd.append("due_date", toYMD(form.due_date));

    if (form.period_start) {
      fd.append("period_start", toYMD(form.period_start));
    }
    if (form.period_end) {
      fd.append("period_end", toYMD(form.period_end));
    }

    if (form.total_amount !== null) {
      fd.append("total_amount", String(form.total_amount));
    }

    fd.append("category", form.category);
    if (form.description) fd.append("description", form.description);

    fd.append("remit_to_tenants", form.remit_to_tenants ? "1" : "0");

    if (!form.remit_to_tenants) {
      fd.append("status", form.owner_paid ? "settled" : "pending");
    }

    if (form.file) fd.append("attachment", form.file);

    await payments.createUtilityBill(fd);
    toast.success(t("expenses.modal.saved"));
    emit("saved");
  } catch (err: any) {
    if (err?.response?.status === 422 && err.response._data?.errors) {
      const messages = Object.values(err.response._data.errors)
        .flat()
        .join("<br>");
      toast.error(messages, 6000);
    } else {
      console.error(err);
      toast.error(t("expenses.modal.saveError"));
    }
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped></style>
