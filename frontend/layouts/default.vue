<template>
  <div class="min-h-screen bg-primary">
    <Navbar />
    <main class="relative">
      <MyToaster />
      <ConfirmDialog position="top"/>
      <NuxtPage />
    </main>
  </div>
</template>

<script setup lang="ts">
import MyToaster from "~/components/ui/MyToaster.vue";
import ConfirmDialog from "primevue/confirmdialog";
</script>

<style scoped>
main::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 10px;
  background: linear-gradient(to bottom, #e2eaf4 10%, #ecf5ff 100%);
  z-index: 10;
}
</style>
