<template>
  <div class="rounded-xl border border-blue-100 bg-white">
    <div class="flex items-center gap-2 p-6 pb-4 border-b border-gray-200">
      <i class="pi pi-credit-card text-xl text-info"></i>
      <h3 class="text-lg font-semibold text-gray-900">Pagos</h3>

      <Button
        class="ml-auto"
        icon="pi pi-history"
        label="Histórico"
        severity="secondary"
        text
        @click="showHistory = true"
      />
    </div>

    <div v-if="pending.length" class="space-y-4 p-6">
      <div
        v-for="item in pending"
        :key="item.id"
        class="bg-blue-50 rounded-lg p-4"
      >
        <div class="flex items-start gap-3">
          <div
            class="bg-blue-100 p-3 rounded-full flex items-center justify-center"
          >
            <i
              :class="
                item.source === 'rent'
                  ? 'pi pi-home text-blue-600'
                  : 'pi pi-lightbulb text-orange-500'
              "
            ></i>
          </div>

          <div class="flex-1">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-medium text-gray-900">{{ item.concept }}</p>
                <p class="text-sm text-gray-500">
                  {{ item.source === "rent" ? "Renta mensual" : "Suministro" }}
                </p>
              </div>

              <Badge
                :value="item.paid_at ? 'Pagado' : 'Pendiente'"
                :severity="item.paid_at ? 'success' : 'warn'"
              />
            </div>

            <div class="mt-3 space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Importe:</span>
                <span class="font-semibold text-gray-900">
                  {{ formatCurrency(+item.amount) }}
                </span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-gray-600">Vencimiento:</span>
                <Tag
                  :value="
                    item.due_date ? formatDate(item.due_date) : 'Próximamente'
                  "
                  :severity="dueColor(item.due_date)"
                />
              </div>
            </div>

            <Button
              label="Pagar ahora"
              icon="pi pi-check"
              class="w-full mt-3"
              severity="success"
              :disabled="!!item.paid_at"
              @click="pay(item)"
            />
          </div>
        </div>
      </div>
    </div>

    <div
      v-else
      class="text-center py-8 rounded-lg mt-4"
    >
      <i class="pi pi-check-circle text-3xl text-green-500 mb-3"></i>
      <p class="text-gray-600">¡No tienes pagos pendientes!</p>
      <p class="text-sm text-gray-500 mt-1">Todo está al día</p>
    </div>
  </div>

  <Dialog
    v-model:visible="showHistory"
    header="Histórico de pagos"
    modal
    style="width: 95%; max-width: 700px"
  >
    <div class="overflow-auto">
      <DataTable
        :value="history"
        :loading="loadingHistory"
        scrollable
        scrollHeight="400px"
        class="p-datatable-sm"
      >
        <Column
          field="concept"
          header="Concepto"
          :style="{ minWidth: '120px' }"
        />

        <Column header="Tipo" :style="{ minWidth: '100px' }">
          <template #body="{ data }">
            <Tag
              :value="data.source === 'rent' ? 'Renta' : 'Suministro'"
              :severity="data.source === 'rent' ? 'info' : 'warn'"
            />
          </template>
        </Column>

        <Column
          field="amount"
          header="Importe"
          body-class="text-right font-medium"
          :body="(_, row) => formatCurrency(+row.amount)"
          :style="{ minWidth: '100px' }"
        />

        <Column header="Vencimiento" :style="{ minWidth: '120px' }">
          <template #body="{ data }">
            {{ data.due_date ? formatDate(data.due_date) : "—" }}
          </template>
        </Column>

        <Column field="status" header="Estado" :style="{ minWidth: '100px' }">
          <template #body="{ data }">
            <Badge
              :value="data.status === 'paid' ? 'Pagado' : 'Pendiente'"
              :severity="data.status === 'paid' ? 'success' : 'warn'"
            />
          </template>
        </Column>

        <Column
          field="paid_at"
          header="Fecha pago"
          :body="(_, row) => (row.paid_at ? formatDate(row.paid_at) : '—')"
          :style="{ minWidth: '120px' }"
        />
      </DataTable>
    </div>
  </Dialog>
</template>

<script setup lang="ts">
import { usePaymentsStore, type PendingItem } from "~/store/payments";
import { ref, onMounted, watch } from "vue";

const store = usePaymentsStore();
const { pending, history } = storeToRefs(store);

const showHistory = ref(false);
const loadingPending = ref(false);
const loadingHistory = ref(false);

onMounted(async () => {
  loadingPending.value = true;
  await store.fetchAll();
  loadingPending.value = false;
});
watch(showHistory, async (v) => {
  if (v && history.value.length === 0) {
    loadingHistory.value = true;
    await store.fetchPaid();
    loadingHistory.value = false;
  }
});

const pay = async (item: PendingItem) => {
  try {
    await store.startPayment(item.id, item.source);
  } catch (e) {
    console.error(e);
  }
};

const dueColor = (dateStr: string | null) => {
  if (!dateStr) return "info";
  const today = new Date().setHours(0, 0, 0, 0);
  const dueTime = new Date(dateStr).setHours(0, 0, 0, 0);
  const diffDays = Math.round((dueTime - today) / 8.64e7);

  if (diffDays < 0) return "danger";
  if (diffDays <= 7) return "warn";
  return "success";
};

const formatCurrency = (n: number) =>
  n.toLocaleString("es-ES", { style: "currency", currency: "EUR" });

const formatDate = (s: string) =>
  new Date(s).toLocaleDateString("es-ES", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
</script>
