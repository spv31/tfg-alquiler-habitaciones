import { useContractsStore } from '../../store/contracts';
<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col justify-center md:flex-row md:items-center gap-4 mb-6">
      <input
        v-model="form.name"
        placeholder="Nombre de la plantilla del contrato"
        class="w-full md:w-3/4 py-2 px-4 rounded-2xl border border-gray-300 outline-none text-sm font-medium text-gray-900 placeholder-gray-400 hover:border-info hover:ring-1 hover:ring-info focus:border-info focus:ring-1 focus:ring-info transition-colors transition-shadow duration-300"
      />

      <div class="w-full md:w-auto flex justify-center md:justify-end">
        <button class="px-4 py-2 button-primary shrink-0" @click="saveTemplate">
          Guardar plantilla
        </button>
      </div>
    </div>

    <TextEditor
      v-model="form.content"
    />
  </div>
</template>
<script setup lang="ts">
import { useContractsStore } from "~/store/contracts";

const contractsStore = useContractsStore();

const form = ref({
  name: "",
  type: "",
  content: "",
});

const saveTemplate = async () => {
  try {
    const data = await contractsStore.saveContractTemplate(form.value);
    console.log(data);
  } catch (error) {
    console.log(error);
  } 
}
</script>

