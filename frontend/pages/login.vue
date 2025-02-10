<template>
	<LoginForm />
</template>

<script setup>
import LoginForm from '~/components/forms/LoginForm.vue';

definePageMeta({
	'middleware': ['sanctum:guest']
});
</script>