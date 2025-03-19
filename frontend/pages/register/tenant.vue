<template>
	<div class="py-20 px-4">
    <RegisterTenantForm></RegisterTenantForm>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: "default",
  middleware: ["sanctum:guest"],
});
</script>

<style scoped>

</style>