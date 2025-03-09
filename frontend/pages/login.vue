<template>
  <div class="py-20 px-4">
    <LoginForm v-if="!showForgotPassword" @toggleForm="toggleForm" />
    <ForgotPasswordForm v-else @toggleForm="toggleForm" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: ["sanctum:guest"],
});

const showForgotPassword = ref(false);

const toggleForm = () => {
  showForgotPassword.value = !showForgotPassword.value;
};
</script>