<template>
	<div class="px-4 py-6 sm:px-0">
		<div v-if="loading" class="text-center py-12">
			<div class="text-gray-500">Загрузка статистики...</div>
		</div>

		<div v-else-if="stats" class="space-y-6">
			<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
				<div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
					<div class="p-5">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<span class="text-2xl">🎯</span>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
										Всего турниров
									</dt>
									<dd class="text-lg font-medium text-gray-900 dark:text-white">
										{{ stats.total_tournaments }}
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
					<div class="p-5">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<span class="text-2xl">💰</span>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
										Общий профит
									</dt>
									<dd
										class="text-lg font-medium"
										:class="stats.total_profit >= 0 ? 'text-green-600' : 'text-red-600'"
									>
										{{ formatCurrency(stats.total_profit) }}
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
					<div class="p-5">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<span class="text-2xl">📊</span>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
										ITM %
									</dt>
									<dd class="text-lg font-medium text-gray-900 dark:text-white">
										{{ stats.itm_percentage }}%
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
					<div class="p-5">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<span class="text-2xl">📈</span>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
										Средний байин
									</dt>
									<dd class="text-lg font-medium text-gray-900 dark:text-white">
										{{ formatCurrency(stats.average_buyin) }}
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
				<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
					Динамика банкролла
				</h3>
				<div v-if="stats.bankroll_history && stats.bankroll_history.length > 0" class="space-y-2">
					<div
						v-for="(item, index) in stats.bankroll_history.slice(-10)"
						:key="index"
						class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700"
					>
						<span class="text-sm text-gray-600 dark:text-gray-400">{{ item.date }}</span>
						<span
							class="text-sm font-medium"
							:class="item.balance >= 0 ? 'text-green-600' : 'text-red-600'"
						>
							{{ formatCurrency(item.balance) }}
						</span>
					</div>
				</div>
				<div v-else class="text-gray-500 text-center py-4">
					Нет данных
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useStatisticsStore } from '../../stores/statistics';

const statisticsStore = useStatisticsStore();
const { stats, loading } = storeToRefs(statisticsStore);

const formatCurrency = (value) => {
	return new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'USD',
	}).format(value);
};

onMounted(() => {
	statisticsStore.fetchStats();
});
</script>
