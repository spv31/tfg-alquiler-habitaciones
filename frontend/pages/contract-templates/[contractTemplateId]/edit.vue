<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mx-auto max-w-3xl">
      <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr_auto] gap-3 mb-6">
        <input
          v-model="form.name"
          placeholder="Nombre de la plantilla"
          class="w-full py-2 px-4 rounded-2xl border border-gray-300 text-sm font-medium placeholder-gray-400 focus:border-info focus:ring-1 focus:ring-info"
        >
        
        <div class="relative">
          <select
            v-model="form.type"
            required
            class="w-full py-2 px-4 rounded-2xl border border-gray-300 text-sm font-medium focus:border-info focus:ring-1 focus:ring-info bg-white appearance-none pr-8"
          >
            <option value="" disabled class="text-gray-400">Tipo</option>
            <option 
              v-for="type in ['vivienda_habitual', 'habitacional_temporal', 'habitacion', 'vacacional', 'turistico']"
              :key="type"
              :value="type"
              class="text-gray-700"
            >
              {{ contractTypeLabel(type) }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>

        <button 
          class="button-primary py-2 px-4 rounded-2xl text-sm font-medium whitespace-nowrap"
          @click="updateTemplate"
        >
          Guardar cambios
        </button>
      </div>

      <TextEditor v-if="loaded" v-model="form.content" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRoute, useRouter } from "#imports";
import { useContractsStore } from "~/store/contracts";

const route = useRoute();
const router = useRouter();
const store = useContractsStore();
const { t: $t, locale } = useI18n();

const id = Number(route.params.contractTemplateId);
const loaded = ref(false);
const form = reactive({
  name: "",
  type: "",
  content: "",
});

onMounted(async () => {
  const tpl = await store.fetchContractTemplate(id);
  form.name = tpl.data.name;
  form.type = tpl.data.type;
  form.content = tpl.data.content;
  loaded.value = true;
});

const updateTemplate = async () => {
  try {
    await store.updateContractTemplate(id, form);
    navigateTo(`/${locale.value}/contract-templates?msg=updated`);
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped>
</style>