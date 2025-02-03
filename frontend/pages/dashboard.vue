<template>
	<div class="min-h-screen bg-gray-900">
		<div class="max-w-screen-xl mx-auto p-8">
			<h1 class="text-3xl font-semibold text-white mb-6 text-center">{{ $t('dashboard.title') }}</h1>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<div
					class="bg-sky-700 p-6 rounded-lg shadow-lg border border-sky-800 hover:bg-sky-600 hover:border-sky-500 transition duration-300 ease-in-out">
					<h2 class="text-xl font-semibold text-white">{{ $t('dashboard.properties.title') }}</h2>
					<p class="font-medium text-gray-300 mt-2">{{ $t('dashboard.properties.description') }}</p>
				</div>
				<div
					class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700 hover:bg-gray-700 hover:border-gray-500 transition duration-300 ease-in-out">
					<h2 class="text-xl font-semibold text-white">{{ $t('dashboard.add_property.title') }}</h2>
					<p class="font-medium text-gray-300 mt-2">{{ $t('dashboard.add_property.description') }}</p>
				</div>
				<div
					class="bg-zinc-800 p-6 rounded-lg shadow-lg border border-zinc-700 hover:bg-zinc-700 hover:border-zinc-500 transition duration-300 ease-in-out">
					<h2 class="text-xl font-semibold text-white">{{ $t('dashboard.profile.title') }}</h2>
					<p class="font-medium text-gray-300 mt-2">{{ $t('dashboard.profile.description') }}</p>
				</div>

				<div
					class="bg-stone-800 p-6 rounded-lg shadow-lg border border-stone-700 hover:bg-stone-700 hover:border-stone-500 transition duration-300 ease-in-out">
					<h2 class="text-xl font-semibold text-white">{{ $t('dashboard.statistics.title') }}</h2>
					<p class="font-medium text-gray-300 mt-2">{{ $t('dashboard.statistics.description') }}</p>
				</div>
				<div
					class="bg-indigo-800 p-6 rounded-lg shadow-lg border border-indigo-700 hover:bg-indigo-700 hover:border-indigo-500 transition duration-300 ease-in-out">
					<h2 class="text-xl font-semibold text-white">{{ $t('dashboard.contracts.title') }}</h2>
					<p class="font-medium text-gray-300 mt-2">{{ $t('dashboard.contracts.description') }}</p>
				</div>
				<div
					class="bg-cyan-800 p-6 rounded-lg shadow-lg border border-cyan-700 hover:bg-cyan-700 hover:border-cyan-500 transition duration-300 ease-in-out">
					<h2 class="text-xl font-semibold text-white">{{ $t('dashboard.chats.title') }}</h2>
					<p class="font-medium text-gray-300 mt-2">{{ $t('dashboard.chats.description') }}</p>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
definePageMeta({
	layout: 'default',
	'middleware': ['sanctum:auth']
});
</script>
