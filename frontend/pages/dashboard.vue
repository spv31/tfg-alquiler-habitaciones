<template>
	<div>Bienvenido al Dashboard</div>
</template>

<script setup>
definePageMeta({
	'middleware': ['sanctum:auth']
});

</script>