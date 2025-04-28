<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
      <input
        v-model="form.name"
        placeholder="Nombre de la plantilla"
        class="w-full md:w-3/4 py-2 px-4 rounded-2xl border border-gray-300 outline-none text-sm font-medium placeholder-gray-400 hover:border-info hover:ring-1 hover:ring-info focus:border-info focus:ring-1 focus:ring-info"
      />

      <div class="w-full md:w-auto flex justify-center md:justify-end">
        <button class="px-4 py-2 button-primary" @click="updateTemplate">
          Guardar cambios
        </button>
      </div>
    </div>

    <TextEditor v-if="loaded" v-model="form.content" />
  </div>
</template>

<script setup lang="ts">
import { useRoute, useRouter } from "#imports";
import { useContractsStore } from "~/store/contracts";

const route = useRoute();
const router = useRouter();
const store = useContractsStore();

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
    router.push({ name: "contract-templates" });
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped>
</style>