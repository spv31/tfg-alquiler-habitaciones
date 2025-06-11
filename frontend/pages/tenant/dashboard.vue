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
            v-if="assignedRentable && assignedRentable.type === 'Property'"
            :data="assignedRentable"
            class="border-0"
          />
          <RoomCard
            v-else-if="assignedRentable && assignedRentable.type === 'Room'"
            :data="assignedRentable"
            class="border-0"
          />
          <div v-else class="text-center py-8 text-gray-500">
            <i class="pi pi-inbox text-4xl mb-4"></i>
            <p>No tienes ningún alquiler asignado</p>
          </div>
        </template>
      </Card>

      <!-- <div v-if="assignedRentable" class="grid gap-6 grid-cols-1 lg:grid-cols-5 auto-rows-max">
        <div class="lg:col-span-3 col-span-1">
          <ContractSection :contract="currentContract!" />
        </div>

        <div class="lg:col-span-2 col-span-1 flex flex-col gap-6 h-full">
          <div class="flex-1">
            <Chat
              v-if="chatCtx"
              :context="chatCtx"
              :rentable="assignedRentable"
              :current-user-id="tenantId!"
              :current-user-role="'tenant'"
            />

            <div
              v-else
              class="h-full flex items-center justify-center text-gray-500 border border-info/10 rounded-xl"
            >
              <i class="pi pi-info-circle text-2xl mr-2" />
              Selecciona una propiedad para habilitar el chat
            </div>
          </div>

          <div>
            <div class="rounded-xl border border-blue-100 h-full bg-white">
              <div class="flex items-center gap-3 p-4 border-b border-gray-200">
                <i class="pi pi-credit-card text-2xl text-info"></i>
                <h3 class="text-lg font-semibold text-gray-900">
                  Estado de Pagos
                </h3>
              </div>
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
                      <span class="font-semibold text-lg text-gray-900">
                        ${{ currentContract.rent_amount }}
                      </span>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-gray-600">Próximo vencimiento:</span>
                      <span class="font-medium text-gray-900">
                        05 {{ nextMonth }}
                      </span>
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
            </div>
          </div>
        </div>
      </div> -->
      <div
        v-if="assignedRentable"
        class="grid gap-6 grid-cols-1 lg:grid-cols-5 auto-rows-max"
      >
        <template v-if="currentContract">
          <!-- Mismo diseño si hay contrato -->
          <div class="lg:col-span-3 col-span-1">
            <ContractSection :contract="currentContract" />
          </div>

          <div class="lg:col-span-2 col-span-1 flex flex-col gap-6 h-full">
            <div class="flex-1">
              <Chat
                v-if="chatCtx"
                :context="chatCtx"
                :rentable="assignedRentable"
                :current-user-id="tenantId!"
                :current-user-role="'tenant'"
              />

              <div
                v-else
                class="h-full flex items-center justify-center text-gray-500 border border-info/10 rounded-xl"
              >
                <i class="pi pi-info-circle text-2xl mr-2" />
                Selecciona una propiedad para habilitar el chat
              </div>
            </div>

            <div>
              <!-- Sección de pagos completa -->
              <div class="rounded-xl border border-blue-100 h-full bg-white">
                <div
                  class="flex items-center gap-3 p-4 border-b border-gray-200"
                >
                  <i class="pi pi-credit-card text-2xl text-info"></i>
                  <h3 class="text-lg font-semibold text-gray-900">
                    Estado de Pagos
                  </h3>
                </div>
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
                        <span class="font-semibold text-lg text-gray-900">
                          ${{ currentContract.rent_amount }}
                        </span>
                      </div>
                      <div class="flex justify-between items-center">
                        <span class="text-gray-600">Próximo vencimiento:</span>
                        <span class="font-medium text-gray-900">
                          05 {{ nextMonth }}
                        </span>
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
              </div>
            </div>
          </div>
        </template>

        <template v-else>
          <!-- Diseño adaptado si NO hay contrato -->
          <div class="lg:col-span-3 col-span-1">
            <div class="flex-1 mb-6">
              <Chat
                v-if="chatCtx"
                :context="chatCtx"
                :rentable="assignedRentable"
                :current-user-id="tenantId!"
                :current-user-role="'tenant'"
              />

              <div
                v-else
                class="h-full flex items-center justify-center text-gray-500 border border-info/10 rounded-xl"
              >
                <i class="pi pi-info-circle text-2xl mr-2" />
                Selecciona una propiedad para habilitar el chat
              </div>
            </div>
          </div>

          <div class="lg:col-span-2 col-span-1 flex flex-col gap-6 h-full">
            <ContractSection :contract="null" />

            <div class="rounded-xl border border-blue-100 h-full bg-white">
              <div class="flex items-center gap-3 p-4 border-b border-gray-200">
                <i class="pi pi-credit-card text-2xl text-info"></i>
                <h3 class="text-lg font-semibold text-gray-900">
                  Estado de Pagos
                </h3>
              </div>
              <div class="p-4">
                <div class="text-center py-6 text-gray-500">
                  <i class="pi pi-info-circle text-2xl mb-3"></i>
                  <p>No hay información de pagos disponible</p>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import PropertyCard from "~/components/ui/tenant/PropertyCard.vue";
import RoomCard from "~/components/ui/tenant/RoomCard.vue";
import { useAuthStore } from "~/store/auth";
import { useTenantStore } from "~/store/tenant";
import type { Property } from "~/types/property";
import type { Room } from "~/types/room";

const tenantStore = useTenantStore();
const authStore = useAuthStore();
const { currentContract, assignedRentable, alreadyLoaded } =
  storeToRefs(tenantStore);
const tenantId = computed(() => authStore.user?.id);

const chatCtx = ref<{ ownerId: number; tenantId: number } | null>(null);

watchEffect(() => {
  if (assignedRentable.value && tenantId.value) {
    const ownerId =
      assignedRentable.value.type === "Property"
        ? (assignedRentable.value.rentable as Property).owner?.id
        : (assignedRentable.value.rentable as Room).owner?.id;
    chatCtx.value = {
      ownerId: ownerId!,
      tenantId: tenantId.value,
    };
  } else {
    chatCtx.value = null;
  }
});

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
  if (!alreadyLoaded.value) {
    try {
      await tenantStore.fetchTenantData();
    } catch (error) {
      console.error("Error cargando datos de inquilino:", error);
    }
  }
});
</script>
