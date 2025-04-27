<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="alertMessage" class="mb-4 mx-auto">
      <Alert
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />
    </div>

    <div class="text-center md:text-right">
      <NuxtLink
        :to="$localePath('contract-templates-create')"
        class="button-primary inline-block"
      >
        Añadir una plantilla
      </NuxtLink>
    </div>

     <div
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8"
      v-if="!loading"
    >
      <div
        v-for="tpl in contractTemplates.data"
        :key="tpl.id"
        class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition transform hover:shadow-lg hover:-translate-y-0.5"
      >
        <!-- nombre -->
        <div class="px-4 pt-4 pb-2 font-semibold text-gray-900 text-center">
          {{ tpl.name }}
        </div>

        <!-- mini-preview -->
        <Suspense>
          <template #default>
            <PreviewImage :template-id="tpl.id" class="h-64 w-full object-cover"/>
          </template>
          <template #fallback>
            <div class="h-64 w-full flex items-center justify-center bg-gray-50">
              <svg class="animate-spin h-6 w-6 text-gray-400" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
            </div>
          </template>
        </Suspense>
      </div>
    </div>

    <!-- loader global -->
    <div v-else class="flex justify-center items-center mt-10">
      <span class="loader-square"></span>
    </div>
    
  </div>
</template>

<script setup lang="ts">
import { useContractsStore } from "~/store/contracts";

const contractsStore = useContractsStore();
const { contractTemplates, loading } = storeToRefs(contractsStore);

onMounted(async () => {
  try {
    const data = await contractsStore.fetchContractTemplates();
    
    console.log(data);
    for(let element of data) {
      console.log('Plantilla: ', element.name);
    }

    for(const tpl of contractTemplates.value) {
      console.log('tEMPLATE: ', tpl);
    } 
  } catch (error) {
    console.error(error);
  }
})

const getPreviewUrl = async (tplId: number) => {
  try {
    return await contractsStore.fetchContractPdf(tplId);
  } catch (error) {
    console.error(error);
    return '';
  }
}
</script>

<style scoped>
.contract {
  font-family: "Times New Roman", serif;
  font-size: 11pt;
  line-height: 1.4;
}
h1,
h2 {
  text-align: center;
  margin: 0 0 12px;
}
.page-break {
  page-break-before: always;
}
</style>