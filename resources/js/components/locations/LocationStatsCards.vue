<template>
	<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
			<div class="p-6">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
							Турниров
						</p>
						<p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
							{{ location?.tournaments_count }}
						</p>
					</div>
					<div class="h-16 w-16 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-lg">
						<span class="text-3xl">🎯</span>
					</div>
				</div>
			</div>
		</div>

		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
			<div class="p-6">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
							Средний байин
						</p>
						<p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
							{{ formatCurrency(location?.average_buyin) }}
						</p>
					</div>
					<div class="h-16 w-16 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-lg">
						<span class="text-3xl">💰</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { formatCurrency } from '../../utils/formatLocation';

defineProps({
	location: { type: Object, default: null },
});
</script>
