<template>
  <div class="min-h-screen bg-primary">
    <Navbar />
    <main class="relative">
      <NuxtPage />
    </main>
  </div>
</template>

<script setup lang="ts">
</script>

<style scoped>

main::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 10px; 
  background: linear-gradient(to bottom, #e2eaf4 10%, #ECF5FF 100%);
  z-index: 10;
}
</style>