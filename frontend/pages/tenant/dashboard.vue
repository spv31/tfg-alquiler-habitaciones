<template>
  <div
    class="min-h-screen max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
  >
    <div class="text-center space-y-2">
      <h1 class="text-3xl font-bold text-gray-900">Panel del Inquilino</h1>
      <p class="text-gray-600 text-lg">
        Bienvenido a tu espacio de gestión de alquiler
      </p>
    </div>

    <div class="space-y-8">
      <Card
        class="rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300"
      >
        <template #title>
          <div class="flex justify-center items-center lg:grid lg:grid-cols-6">
            <div
              class="flex items-center gap-3 lg:col-start-1 lg:col-span-4 lg:ps-6"
            >
              <i class="pi pi-home text-2xl text-info"></i>
              <h2 class="text-2xl font-bold text-gray-900">Tu Arrendamiento</h2>
            </div>
          </div>
        </template>
        <template #content>
          <PropertyCard
            v-if="assignedRentable"
            :data="assignedRentable"
            @open-chat="showChat = true"
            class="border-0"
          />
          <div v-else class="text-center py-8 text-gray-500">
            <i class="pi pi-inbox text-4xl mb-4"></i>
            <p>No tienes ningún alquiler asignado</p>
          </div>
        </template>
      </Card>

      <div class="grid gap-6 md:grid-cols-2">
        <ContractSection :contract="currentContract!" />

        <Card
          class="rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300"
        >
          <template #title>
            <div class="flex items-center gap-3 p-4">
              <i class="pi pi-credit-card text-2xl text-info"></i>
              <h3 class="text-lg font-semibold text-gray-900">
                Estado de Pagos
              </h3>
            </div>
          </template>
          <template #content>
            <div class="p-4 space-y-6">
              <div v-if="currentContract" class="space-y-6">
                <div
                  class="flex items-center justify-between p-4 bg-blue-50 rounded-lg"
                >
                  <div>
                    <p class="font-medium text-gray-900">Estado actual</p>
                    <p class="text-sm text-gray-500">Mes en curso</p>
                  </div>
                  <Badge
                    :value="currentContract.paid ? 'Pagado' : 'Pendiente'"
                    :severity="currentContract.paid ? 'success' : 'warning'"
                    class="text-sm"
                  />
                </div>

                <div class="space-y-4">
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600">Monto mensual:</span>
                    <span class="font-semibold text-lg text-gray-900"
                      >${{ currentContract.rent_amount }}</span
                    >
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600">Próximo vencimiento:</span>
                    <span class="font-medium text-gray-900"
                      >05 {{ nextMonth }}</span
                    >
                  </div>
                </div>

                <Button
                  label="Pagar ahora"
                  icon="pi pi-check"
                  class="w-full"
                  :disabled="currentContract.paid"
                  severity="success"
                />
              </div>

              <div v-else class="text-center py-6 text-gray-500">
                <i class="pi pi-info-circle text-2xl mb-3"></i>
                <p>No hay información de pagos disponible</p>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <Dialog
      v-model:visible="showChat"
      header="Chat con el Propietario"
      :style="{ width: 'min(95vw, 600px)', borderRadius: '1rem' }"
      :modal="true"
    >
      <Chat :rentable="assignedRentable" />
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import PropertyCard from "~/components/ui/tenant/PropertyCard.vue";
import { useTenantStore } from "~/store/tenant";

const tenantStore = useTenantStore();
const { currentContract, assignedRentable, alreadyLoaded } =
  storeToRefs(tenantStore);
const showChat = ref(false);

const nextMonth = computed(() => {
  const months = [
    "Ene",
    "Feb",
    "Mar",
    "Abr",
    "May",
    "Jun",
    "Jul",
    "Ago",
    "Sep",
    "Oct",
    "Nov",
    "Dic",
  ];
  const next = new Date().getMonth() + 1;
  return months[next % 12];
});

onMounted(async () => {
  console.log("fetchTenanantData es llamado dos veces mola");

  await nextTick();

  if (!alreadyLoaded.value) {
    try {
      await tenantStore.fetchTenantData();
    } catch (error) {
      console.error("Error cargando datos de inquilino:", error);
    }
  }
});
</script>
