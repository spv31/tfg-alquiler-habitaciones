<template>
	<RegisterForm></RegisterForm>
</template>

<script setup>

	definePageMeta({
		layout: 'default',
		'middleware': ['sanctum:guest']
	});

</script>