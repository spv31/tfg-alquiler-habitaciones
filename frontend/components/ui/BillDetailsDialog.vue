<template>
  <div v-if="bill" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <h3 class="text-lg font-bold mb-4">
          {{ $t("expenses.billDetails.generalInfo") }}
        </h3>

        <div class="space-y-3">
          <div class="flex">
            <span class="font-medium w-40"
              >{{ $t("expenses.table.date") }}:</span
            >
            <span>{{ formatDate(bill.issue_date) }}</span>
          </div>

          <div class="flex" v-if="bill.due_date">
            <span class="font-medium w-40"
              >{{ $t("expenses.billDetails.dueDate") }}:</span
            >
            <span>{{ formatDate(bill.due_date) }}</span>
          </div>

          <div class="flex" v-if="bill.period_start && bill.period_end">
            <span class="font-medium w-40"
              >{{ $t("expenses.billDetails.period") }}:</span
            >
            <span>
              {{ formatDate(bill.period_start) }} -
              {{ formatDate(bill.period_end) }}
            </span>
          </div>

          <div class="flex">
            <span class="font-medium w-40"
              >{{ $t("expenses.table.category") }}:</span
            >
            <span class="capitalize">
              {{ $t(`expenses.selectOptions.${bill.category}`) }}
            </span>
          </div>

          <div class="flex">
            <span class="font-medium w-40"
              >{{ $t("expenses.table.description") }}:</span
            >
            <span>{{ bill.description || "-" }}</span>
          </div>

          <div class="flex">
            <span class="font-medium w-40"
              >{{ $t("expenses.table.amount") }}:</span
            >
            <span class="font-semibold">{{
              formatCurrency(Number(bill.total_amount))
            }}</span>
          </div>

          <div class="flex">
            <span class="font-medium w-40"
              >{{ $t("expenses.table.status") }}:</span
            >
            <Badge
              :value="
                bill.status === 'settled'
                  ? $t('expenses.paid')
                  : $t('expenses.pending')
              "
              :severity="bill.status === 'settled' ? 'success' : 'warning'"
            />
          </div>

          <div class="flex">
            <span class="font-medium w-40"
              >{{ $t("expenses.billDetails.remitToTenants") }}:</span
            >
            <span>{{
              bill.remit_to_tenants ? $t("common.yes") : $t("common.no")
            }}</span>
          </div>
        </div>
      </div>

      <!-- Sección de imagen adjunta -->
      <div v-if="bill.attachment_url">
        <h3 class="text-lg font-bold mb-4">
          {{ $t("expenses.billDetails.attachment") }}
        </h3>
        <img
          :src="bill.attachment_url"
          alt="Bill attachment"
          class="rounded-lg border shadow-sm max-w-full h-auto max-h-80 object-contain"
        />
      </div>
    </div>

    <div
      v-if="
        bill.remit_to_tenants && bill.bill_shares && bill.bill_shares.length
      "
    >
      <h3 class="text-lg font-bold mb-4">
        {{ $t("expenses.billDetails.shares") }}
      </h3>

      <DataTable :value="bill.bill_shares" class="p-datatable-sm">
        <Column :header="$t('expenses.billDetails.tenant')" field="tenant">
          <template #body="{ data }">
            {{ data.tenant?.full_name || "-" }}
          </template>
        </Column>

        <Column :header="$t('expenses.billDetails.amount')" field="amount">
          <template #body="{ data }">
            {{ formatCurrency(Number(data.amount)) }}
          </template>
        </Column>

        <Column :header="$t('expenses.billDetails.status')" field="status">
          <template #body="{ data }">
            <Badge
              :value="$t(`payments.status.${data.status}`)"
              :severity="
                (
                  {
                    paid: 'success',
                    pending: 'warning',
                    cancelled: 'danger',
                  } as Record<string, string>
                )[data.status]
              "
            />
          </template>
        </Column>
      </DataTable>
    </div>

    <div class="flex justify-end pt-4">
      <button
        type="button"
        class="button-primary flex items-center gap-2"
        @click="$emit('close')"
      >
        <i class="pi pi-times text-base" />
        <span>{{ $t("common.close") }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import type { UtilityBill } from "~/types/utilityBill";

const { t: $t } = useI18n();
const props = defineProps<{
  bill: UtilityBill;
}>();

const formatDate = (s: string) =>
  new Date(s).toLocaleDateString("es-ES", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });

const formatCurrency = (n: number) =>
  n.toLocaleString("es-ES", { style: "currency", currency: "EUR" });
</script>
