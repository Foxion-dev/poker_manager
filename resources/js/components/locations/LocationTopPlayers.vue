<template>
	<div v-if="location?.top_players_by_wins?.length" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
		<div class="flex items-center mb-6">
			<div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
				<span class="text-xl">🏆</span>
			</div>
			<h3 class="text-xl font-bold text-gray-900 dark:text-white">
				Лучшие игроки по победам
			</h3>
		</div>
		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
				<thead class="bg-gray-50 dark:bg-gray-700">
					<tr>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Место
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Игрок
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Побед
						</th>
					</tr>
				</thead>
				<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
					<tr v-for="(player, index) in location.top_players_by_wins" :key="player.user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
						<td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
							{{ index + 1 }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
							{{ player.user.name }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
							{{ player.wins }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div v-if="location?.top_players_by_prize?.length" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
		<div class="flex items-center mb-6">
			<div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
				<span class="text-xl">💵</span>
			</div>
			<h3 class="text-xl font-bold text-gray-900 dark:text-white">
				Лучшие игроки по выигрышу
			</h3>
		</div>
		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
				<thead class="bg-gray-50 dark:bg-gray-700">
					<tr>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Место
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Игрок
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Выигрыш
						</th>
					</tr>
				</thead>
				<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
					<tr v-for="(player, index) in location.top_players_by_prize" :key="player.user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
						<td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
							{{ index + 1 }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
							{{ player.user.name }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 dark:text-green-400">
							{{ formatCurrency(player.total_prize) }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script setup>
import { formatCurrency } from '../../utils/formatLocation';

defineProps({
	location: { type: Object, default: null },
});
</script>
