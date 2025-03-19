<template>
  <div class="py-20 px-4">
    <RegisterForm></RegisterForm>
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