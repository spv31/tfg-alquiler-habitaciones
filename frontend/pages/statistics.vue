<template>
  <div
    class="min-h-screen max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
  >
    <div v-if="dashboard" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <Card>
        <template #title>Total Propiedades</template>
        <template #content>
          <p class="text-3xl font-bold text-center">
            {{ dashboard.propertyCount }}
          </p>
        </template>
      </Card>
      <Card>
        <template #title>Propiedades Alquiladas</template>
        <template #content>
          <p class="text-3xl font-bold text-center">
            {{ dashboard.rentedProperties }}
          </p>
        </template>
      </Card>
      <Card>
        <template #title>Habitaciones</template>
        <template #content>
          <p class="text-3xl font-bold text-center">
            {{ dashboard.roomRented }} / {{ dashboard.roomTotal }}
          </p>
        </template>
      </Card>
      <Card>
        <template #title>Patrimonio (€)</template>
        <template #content>
          <p class="text-3xl font-bold text-center">
            {{ formatCurrency(dashboard.netWorth) }}
          </p>
        </template>
      </Card>
    </div>

    <Card v-if="dashboard">
      <template #title
        >Ingresos vs Gastos Mensuales ({{ currentYear }})</template
      >
      <template #content>
        <Chart
          type="line"
          :data="incomeExpenseChartData"
          :options="lineOptions"
          style="min-height: 300px"
        />
      </template>
    </Card>

    <Card v-if="fiscal" class="mt-4">
      <template #title>Días alquilados por tipo ({{ currentYear }})</template>
      <template #content>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <DataTable :value="daysTable" class="p-datatable-sm" stripedRows>
              <Column field="type" header="Tipo">
                <template #body="{ data }">
                  <span class="font-medium">{{ data.type }}</span>
                </template>
              </Column>
              <Column field="days" header="Días">
                <template #body="{ data }">
                  <div class="flex items-center">
                    <span class="mr-2">{{ data.days }}</span>
                    <div class="bg-gray-200 rounded-full h-2 flex-grow">
                      <div
                        class="bg-blue-500 h-2 rounded-full"
                        :style="{ width: data.percentage + '%' }"
                      ></div>
                    </div>
                  </div>
                </template>
              </Column>
              <Column field="percentage" header="%">
                <template #body="{ data }">
                  <Tag :value="data.percentage + '%'" severity="info" />
                </template>
              </Column>
            </DataTable>
          </div>
          <div class="flex justify-center">
            <Chart
              type="doughnut"
              :data="daysByTypeChart"
              :options="doughnutOptions"
              style="height: 250px"
            />
          </div>
        </div>
      </template>
    </Card>

    <Card v-if="fiscal" class="mt-6">
      <template #title>Ingresos por tipo</template>
      <template #content>
        <Chart
          type="bar"
          :data="incomeByTypeChart"
          :options="barOptions"
          style="min-height: 300px"
        />
      </template>
    </Card>

    <Card v-if="fiscal" class="mt-6">
      <template #title>Desglose de gastos (Últimos 12 meses)</template>
      <template #content>
        <div class="mb-6">
          <Chart
            type="bar"
            :data="expensesByCategoryChart"
            :options="stackedBarOptions"
            style="height: 300px"
          />
        </div>

        <div class="space-y-8">
          <div
            v-for="(categoryData, category) in expensesTable"
            :key="category"
            class="bg-gray-50 p-4 rounded-lg"
          >
            <!-- <div class="flex justify-between items-center mb-3">
              <h3 class="font-semibold text-lg capitalize">{{ category }}</h3>
              <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                Total: {{ formatCurrency(expensesCategoryTotals[category]) }}
              </span>
            </div> -->
            <div class="flex justify-between items-center mb-3">
              <h3 class="font-semibold text-lg">
                {{ categoryLabel(category) }}
              </h3>
              <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                Total: {{ formatCurrency(expensesCategoryTotals[category]) }}
              </span>
            </div>

            <DataTable :value="categoryData" class="p-datatable-sm">
              <Column field="type" header="Tipo" />
              <Column header="Importe" class="text-right">
                <template #body="{ data }">
                  <span class="font-medium">{{ data.amount }}</span>
                </template>
              </Column>
              <Column header="Distribución">
                <template #body="{ data }">
                  <div class="flex items-center">
                    <div class="bg-gray-200 rounded-full h-2 flex-grow">
                      <div
                        class="bg-green-500 h-2 rounded-full"
                        :style="{ width: data.percentage + '%' }"
                      ></div>
                    </div>
                    <span class="ml-2 text-sm text-gray-600"
                      >{{ data.percentage }}%</span
                    >
                  </div>
                </template>
              </Column>
              <Column header="% Total">
                <template #body="{ data }">
                  <Tag :value="data.percentageTotal + '%'" severity="success" />
                </template>
              </Column>
            </DataTable>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-200">
          <div class="flex justify-end">
            <div class="text-xl">
              <span class="text-gray-600 mr-3">Gastos totales:</span>
              <span class="font-bold">{{ formatCurrency(expensesTotal) }}</span>
            </div>
          </div>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { useStatisticsStore } from "~/store/statistics";
import DataView from "primevue/dataview";
import Card from "primevue/card";
import Chart from "primevue/chart";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const statisticsStore = useStatisticsStore();
const currentYear = new Date().getFullYear();

const typeLabel = (key: string) => {
  switch (key) {
    case "vivienda_habitual":
    case "vivienda_habitual ":
    case "full":
      return "Vivienda completa";
    case "habitacion":
    case "per_room":
      return "Por habitaciones";
    case "turistica":
      return "Turística";
    case "temporal":
      return "Temporal";
    default:
      return key;
  }
};

onMounted(async () => {
  await statisticsStore.loadDashboard();
  const { from, to } = last12MonthsRange();
  await statisticsStore.loadFiscal(from, to);
});

const dashboard = computed(() => statisticsStore.dashboard);
const fiscal = computed(() => statisticsStore.fiscal);

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
    maximumFractionDigits: 0,
  }).format(value);
};

const getLast12Months = (): string[] => {
  const months: string[] = [];
  const date = new Date();
  date.setDate(1);
  for (let i = 0; i < 12; i++) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    months.unshift(`${y}-${m}`);
    date.setMonth(date.getMonth() - 1);
  }
  return months;
};

const last12MonthsRange = () => {
  const end = new Date();
  const start = new Date();
  start.setDate(1);
  start.setMonth(start.getMonth() - 11);

  const iso = (d: Date) => d.toISOString().substring(0, 10);
  return { from: iso(start), to: iso(end) };
};

const incomeExpenseChartData = computed(() => {
  if (!dashboard.value) return {};
  const months = getLast12Months();
  const incomeData = months.map((m) => dashboard.value!.incomeMonthly[m] ?? 0);
  const expenseData = months.map(
    (m) => dashboard.value!.expenseMonthly[m] ?? 0
  );
  return {
    labels: months,
    datasets: [
      { label: "Ingresos", data: incomeData, fill: false, tension: 0.4 },
      { label: "Gastos", data: expenseData, fill: false, tension: 0.4 },
    ],
  };
});

const categoryLabel = (cat: string) => {
  switch (cat) {
    case "utility":
      return "Suministros";
    case "general":
      return "Gastos generales";
    case "tax":
      return "Impuestos / Tasas";
    default:
      return cat;
  }
};

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: "top" } },
};

const daysTable = computed(() => {
  if (!fiscal.value) return [];

  const totalDays = Object.values(fiscal.value.daysByType).reduce(
    (a, b) => a + b,
    0
  );
  return Object.entries(fiscal.value.daysByType).map(([type, days]) => ({
    type: typeLabel(type),
    days,
    percentage: totalDays > 0 ? Math.round((days / totalDays) * 100) : 0,
  }));
});

const incomeByTypeChart = computed(() => {
  if (!fiscal.value) return { labels: [], datasets: [] };

  const allTypes = ["vivienda_habitual", "habitacion", "turistica", "temporal"];

  const backendData = fiscal.value.incomeByType as Record<string, number>;

  const labels = allTypes.map((key) => typeLabel(key));
  const data = allTypes.map((key) => backendData[key] ?? 0);

  return {
    labels,
    datasets: [
      {
        label: "Ingresos (€)",
        data,
        backgroundColor: ["#3B82F6", "#10B981", "#F59E0B", "#8B5CF6"],
      },
    ],
  };
});

const barOptions = { responsive: true, maintainAspectRatio: false };

const expensesTable = computed(() => {
  if (!fiscal.value || expensesTotal.value === 0) return {};

  const result: Record<string, any[]> = {};
  for (const [cat, types] of Object.entries(fiscal.value.expensesByCat)) {
    const catTotal = expensesCategoryTotals.value[cat];

    result[cat] = Object.entries(types).map(([t, val]: [string, any]) => ({
      type: typeLabel(t),
      amount: formatCurrency(val.amount),
      percentage: Math.round(val.percentage),
      percentageTotal: Math.round((val.amount / expensesTotal.value) * 100),
    }));
  }
  return result;
});

const daysByTypeChart = computed(() => {
  if (!fiscal.value) return { labels: [], datasets: [] };

  return {
    labels: Object.keys(fiscal.value.daysByType).map(typeLabel),
    datasets: [
      {
        data: Object.values(fiscal.value.daysByType),
        backgroundColor: [
          "#42A5F5",
          "#66BB6A",
          "#FFA726",
          "#26C6DA",
          "#7E57C2",
        ],
      },
    ],
  };
});

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: "bottom" } },
};

const expensesTotal = computed(() => {
  if (!fiscal.value) return 0;

  return Object.values(fiscal.value.expensesByCat).reduce((total, category) => {
    return (
      total +
      Object.values(category).reduce(
        (catTotal, typeData) => catTotal + typeData.amount,
        0
      )
    );
  }, 0);
});

const expensesCategoryTotals = computed(() => {
  if (!fiscal.value) return {};

  const totals: Record<string, number> = {};
  for (const [cat, types] of Object.entries(fiscal.value.expensesByCat)) {
    totals[cat] = Object.values(types).reduce(
      (sum, typeData: any) => sum + typeData.amount,
      0
    );
  }
  return totals;
});

const expensesByCategoryChart = computed(() => {
  if (!fiscal.value) return { labels: [], datasets: [] };

  const categories = Object.keys(fiscal.value.expensesByCat);
  const datasets = [];

  // Agrupar por categoría
  datasets.push({
    label: "Total categoría",
    data: categories.map((cat) => expensesCategoryTotals.value[cat]),
    backgroundColor: "#3B82F6",
  });

  return {
    labels: categories.map((c) => c.charAt(0).toUpperCase() + c.slice(1)),
    datasets,
  };
});

const stackedBarOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: { x: { stacked: false }, y: { stacked: false } },
  plugins: { legend: { position: "top" } },
};
</script>

<style scoped>
.p-card {
  display: flex;
  flex-direction: column;
  justify-content: center;
  text-align: center;
}
</style>
