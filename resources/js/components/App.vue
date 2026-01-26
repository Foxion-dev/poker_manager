<template>
	<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
		<div class="container mx-auto px-4 py-8">
			<h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
				{{ title }}
			</h1>
			<p class="text-gray-600 dark:text-gray-400">
				Laravel 12 + Vue 3 + Tailwind CSS
			</p>
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue';

const title = ref('Poker Manager');
</script>
