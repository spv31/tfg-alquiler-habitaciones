<template>
  <Dialog
    v-model:visible="dialogVisible"
    :modal="true"
    :header="$t('rentPayments.createTitle')"
    :style="{ width: '480px' }"
  >
    <form @submit.prevent="submit" class="grid gap-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $t("rentPayments.periodStart") }}
          </label>
          <Datepicker
            v-model="form.period_start"
            dateFormat="dd/mm/yy"
            showIcon
            required
            inputClass="focus:!border-info"
            panelClass="!bg-white !shadow-lg"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $t("rentPayments.periodEnd") }}
          </label>
          <Datepicker
            v-model="form.period_end"
            dateFormat="dd/mm/yy"
            showIcon
            required
            inputClass="focus:!border-info"
            panelClass="!bg-white !shadow-lg"
          />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ $t("rentPayments.dueDate") }}
        </label>
        <Datepicker
          v-model="form.due_date"
          dateFormat="dd/mm/yy"
          showIcon
          required
          inputClass="focus:!border-info"
          panelClass="!bg-white !shadow-lg"
          class="w-full"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ $t("rentPayments.amount") }}
        </label>
        <InputNumber
          v-model="form.amount"
          mode="currency"
          currency="EUR"
          locale="es-ES"
          :min="0"
          :step="0.01"
          required
          class="w-full"
        />
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <button type="button" class="button-secondary" @click="close">
          {{ $t("common.cancel") }}
        </button>
        <button type="submit" class="button-primary" :disabled="submitting">
          {{ $t("common.save") }}
        </button>
      </div>
    </form>
  </Dialog>
</template>

<script setup lang="ts">
import { startOfMonth, endOfMonth, formatISO } from "date-fns";
import { usePaymentsStore } from "@/store/payments";
import { useMyToast } from "#imports";

const props = defineProps<{
  visible: boolean;
  contractId: number;
  defaultAmount: number;
}>();
const emit = defineEmits(["update:visible", "created"]);

const dialogVisible = computed({
  get: () => props.visible,
  set: (val: boolean) => emit("update:visible", val),
});

const paymentsStore = usePaymentsStore();
const { success, error } = useMyToast();
const submitting = ref(false);

const form = ref({
  period_start: startOfMonth(new Date()),
  period_end: endOfMonth(new Date()),
  due_date: startOfMonth(new Date()),
  amount: props.defaultAmount,
});

watch(
  () => props.visible,
  (val) => {
    if (val) {
      form.value.period_start = startOfMonth(new Date());
      form.value.period_end = endOfMonth(new Date());
      form.value.due_date = startOfMonth(new Date());
      form.value.amount = props.defaultAmount;
    }
  }
);

const submit = async () => {
  submitting.value = true;
  try {
    await paymentsStore.createRentPayment({
      contract_id: props.contractId,
      period_start: formatISO(form.value.period_start, {
        representation: "date",
      }),
      period_end: formatISO(form.value.period_end, { representation: "date" }),
      due_date: formatISO(form.value.due_date, { representation: "date" }),
      amount: form.value.amount,
    });
    success("Mensualidad creada");
    emit("created");
    close();
  } catch (e: any) {
    error(e.response?.data?.message || "Error");
  } finally {
    submitting.value = false;
  }
};

const close = () => {
  emit("update:visible", false);
};
</script>
