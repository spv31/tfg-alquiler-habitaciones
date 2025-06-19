<template>
  <Card
    class="mt-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300"
  >
    <template #title>
      <div class="flex flex-wrap items-center gap-3">
        <i class="pi pi-credit-card text-2xl text-info" />
        <h2 class="text-xl md:text-2xl font-bold text-gray-900">
          {{ $t("expenses.title") }}
        </h2>
        <button
          class="button-primary flex items-center gap-2 ml-auto mt-2 sm:mt-0"
          @click="showAdd = true"
          type="button"
        >
          <i class="pi pi-plus" />
          <span class="hidden sm:inline">{{ $t("expenses.add") }}</span>
          <span class="sm:hidden">{{ $t("expenses.addShort") }}</span>
        </button>
      </div>
    </template>

    <template #content>
      <div class="overflow-x-auto">
        <DataTable
          :value="utilityBills"
          scrollable
          responsiveLayout="scroll"
          class="p-datatable-sm w-full"
          paginator
          lazy
          :rows="utilityBillsMeta.per_page"
          :totalRecords="utilityBillsMeta.total"
          :first="
            (utilityBillsMeta.current_page - 1) * utilityBillsMeta.per_page
          "
          @page="onPage"
        >
          <Column
            field="issue_date"
            :header="$t('expenses.table.date')"
            headerClass="whitespace-nowrap"
            bodyClass=""
          >
            <template #body="{ data }">
              <div class="font-medium whitespace-nowrap">
                {{
                  data && data.issue_date ? formatDate(data.issue_date) : "-"
                }}
              </div>
            </template>
          </Column>
          <Column
            field="category"
            :header="$t('expenses.table.category')"
            headerClass="whitespace-nowrap"
            bodyClass=""
          >
            <template #body="{ data }">
              <span class="capitalize">
                {{
                  data && data.category
                    ? $t(`expenses.selectOptions.${data.category}`)
                    : "-"
                }}
              </span>
            </template>
          </Column>
          <Column
            field="description"
            :header="$t('expenses.table.description')"
            headerClass="whitespace-nowrap"
          >
            <template #body="{ data }">
              <span
                class="block truncate max-w-[200px]"
                v-if="data.description"
              >
                {{ data.description }}
              </span>
              <span v-else>-</span>
            </template>
          </Column>

          <Column
            field="total_amount"
            :header="$t('expenses.table.amount')"
            headerClass="whitespace-nowrap text-right"
            bodyClass="text-right"
          >
            <template #body="{ data }">
              {{
                data && data.total_amount != null
                  ? formatCurrency(Number(data.total_amount))
                  : "-"
              }}
            </template>
          </Column>
          <Column
            field="status"
            :header="$t('expenses.table.status')"
            headerClass="text-center"
            bodyClass="text-center"
          >
            <template #body="{ data }">
              <Badge
                :value="
                  data && data.status === 'settled'
                    ? $t('expenses.paid')
                    : $t('expenses.pending')
                "
                :severity="
                  data && data.status === 'settled' ? 'success' : 'warning'
                "
                class="justify-center"
              />
            </template>
          </Column>
          <Column
            :header="$t('expenses.table.actions')"
            headerClass="text-center"
            bodyClass="text-center"
          >
            <template #body="{ data }">
              <Button
                icon="pi pi-eye text-info"
                class="p-button-text p-button-sm hover:!bg-info/10"
                @click="() => showBill(data)"
                v-tooltip.top="$t('expenses.viewDetails')"
                :disabled="!data || !data.id"
              />
              <Button
                icon="pi pi-trash text-red-500"
                class="p-button-text p-button-sm hover:!bg-danger/10"
                @click="() => askDelete(data)"
                v-tooltip.top="$t('common.delete')"
              />
            </template>
          </Column>
          <template #empty>
            <div class="text-center py-6 text-gray-500">
              {{ $t("expenses.noExpenses") }}
            </div>
          </template>
        </DataTable>
      </div>
    </template>
  </Card>

  <Dialog
    v-model:visible="showAdd"
    modal
    :header="$t('expenses.modal.title')"
    panelClass="w-[90vw] max-w-md"
    class="mx-4"
  >
    <AddExpenseForm
      :property-id="propertyId"
      @saved="onSaved"
      @cancel="showAdd = false"
    />
  </Dialog>
  <Dialog
    v-model:visible="showBillDetails"
    modal
    :header="$t('expenses.billDetails.title')"
    :style="{ width: '90vw', maxWidth: '800px' }"
    class="mx-4"
  >
    <BillDetailsDialog
      v-if="selectedBill"
      :bill="selectedBill"
      @close="showBillDetails = false"
    />
  </Dialog>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { usePaymentsStore } from "~/store/payments";
import type { UtilityBill } from "~/types/utilityBill";
import { useMyConfirm } from "~/composables/useMyConfirm";
import { useMyToast } from "~/composables/useMyToast";
import Card from "primevue/card";
import Dialog from "primevue/dialog";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Badge from "primevue/badge";

const { t: $t } = useI18n();
const props = defineProps<{ propertyId: number }>();
const payments = usePaymentsStore();
const confirm = useMyConfirm();
const toast = useMyToast();
const { utilityBills, utilityBillsMeta } = storeToRefs(payments);

const selectedBill = ref<UtilityBill | null>(null);
const showBillDetails = ref(false);
const showAdd = ref(false);

onMounted(() => {
  payments.fetchUtilityBills({ property_id: props.propertyId });
  console.log("Recibos: ", utilityBills.value);
});

const onPage = ({ page, rows }: { page: number; rows: number }) => {
  payments.fetchUtilityBills({ property_id: props.propertyId }, page + 1, rows);
};

const onSaved = async () => {
  showAdd.value = false;
  await payments.fetchUtilityBills({ property_id: props.propertyId });
};

const askDelete = (bill: UtilityBill) => {
  confirm.show({
    header: $t("expenses.confirm.title"),
    message: $t("expenses.confirm.message", { id: bill.id }),
    acceptLabel: $t("common.delete"),
    rejectLabel: $t("common.cancel"),
    acceptSeverity: "danger",
    onAccept: async () => {
      try {
        await payments.deleteUtilityBill(bill.id);
        toast.success($t("expenses.deleted"));
        payments.fetchUtilityBills(
          { property_id: bill.property?.id },
          payments.utilityBillsMeta.current_page,
          payments.utilityBillsMeta.per_page
        );
      } catch {
        toast.error($t("expenses.deleteError"));
      }
    },
  });
};

const formatDate = (s: string) =>
  new Date(s).toLocaleDateString("es-ES", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });

const formatCurrency = (n: number) =>
  n.toLocaleString("es-ES", { style: "currency", currency: "EUR" });

const showBill = (bill: UtilityBill) => {
  selectedBill.value = bill;
  showBillDetails.value = true;
};
</script>

<style scoped>
:deep(.p-datatable) .p-datatable-thead > tr > th,
:deep(.p-datatable) .p-datatable-tbody > tr > td {
  padding: 0.5rem 0.75rem;
}
</style>
