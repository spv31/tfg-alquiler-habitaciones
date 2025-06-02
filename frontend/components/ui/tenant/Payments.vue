<template>
  <Card class="shadow-md">
    <template #title>Pagos</template>

    <template #content>
      <p v-if="!contract" class="text-gray-500 text-center py-4">
        Aún no hay pagos registrados.
      </p>

      <DataTable v-else :value="mockPayments" responsive-layout="scroll">
        <Column field="date" header="Fecha" />
        <Column field="concept" header="Concepto" />
        <Column field="amount" header="Importe" body-class="text-right" />
        <Column field="status" header="Estado" />
      </DataTable>
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import type { Contract } from "@/types/contract";

const mockPayments = [
  {
    date: "2025-05-01",
    concept: "Renta mayo",
    amount: "500€",
    status: "Pagado",
  },
  {
    date: "2025-06-01",
    concept: "Renta junio",
    amount: "500€",
    status: "Pendiente",
  },
];

defineProps<{ contract: Contract | null }>();
</script>

<style scoped></style>
