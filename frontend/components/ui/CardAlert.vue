<template>
  <div class="bg-white p-6 rounded-2xl border border-info inline-block">
    <p class="text-blue-600 font-medium">{{ message }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{ message: string }>();
</script>
<style scoped>
</style>