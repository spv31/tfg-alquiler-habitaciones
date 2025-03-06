<template>
  <div>
    <Navbar />
    <!-- <div class="bg-gray min-h-screen">  -->
    <div class="bg-[#ECF5FF] min-h-screen relative contenta">
      <NuxtPage />
    </div>
  </div>
</template>

<script setup lang="ts">
</script>

<style scoped>

.contenta::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 10px; /* Ajusta el grosor que desees */
  /* Ejemplo de degradado vertical de un gris oscuro (#333) a transparente */
  background: linear-gradient(to bottom, #e2eaf4 10%, #ECF5FF 100%);
  /* Ajusta los colores, el grosor y la dirección según tu preferencia */
  z-index: 10;
}
</style>