<template>
	<div>
		<div v-if="stats" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col space-y-4">
				<div class="flex flex-wrap gap-2">
					<button
						@click="setPeriod('month')"
						class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
						:class="selectedPeriod === 'month' 
							? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
							: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
					>
						Месяц
					</button>
					<button
						@click="setPeriod('3months')"
						class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
						:class="selectedPeriod === '3months' 
							? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
							: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
					>
						3 месяца
					</button>
					<button
						@click="setPeriod('6months')"
						class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
						:class="selectedPeriod === '6months' 
							? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
							: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
					>
						Полгода
					</button>
					<button
						@click="setPeriod('year')"
						class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
						:class="selectedPeriod === 'year' 
							? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
							: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
					>
						Год
					</button>
					<button
						@click="setPeriod('custom')"
						class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
						:class="selectedPeriod === 'custom' 
							? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
							: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
					>
						Свой диапазон
					</button>
				</div>
				<div v-if="selectedPeriod === 'custom'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">📅</span>
							Дата начала
						</label>
						<input
							v-model="customDateFrom"
							type="date"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						/>
					</div>
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">📅</span>
							Дата окончания
						</label>
						<input
							v-model="customDateTo"
							type="date"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						/>
					</div>
				</div>
			</div>
		</div>

		<div v-if="loading" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка статистики...</div>
			</div>
		</div>

		<div v-else-if="stats" class="space-y-6">
			<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
					<div class="p-6">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
									Всего турниров
								</p>
								<p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
									{{ stats.total_tournaments }}
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
									Общий профит
								</p>
								<p
									class="mt-2 text-3xl font-bold"
									:class="stats.total_profit >= 0 ? 'text-green-600' : 'text-red-600'"
								>
									{{ formatCurrency(stats.total_profit) }}
								</p>
							</div>
							<div
								class="h-16 w-16 rounded-xl flex items-center justify-center shadow-lg"
								:class="stats.total_profit >= 0 
									? 'bg-gradient-to-br from-green-400 to-green-600' 
									: 'bg-gradient-to-br from-red-400 to-red-600'"
							>
								<span class="text-3xl">💰</span>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
					<div class="p-6">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
									ITM %
								</p>
								<p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
									{{ stats.itm_percentage }}%
								</p>
							</div>
							<div class="h-16 w-16 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-lg">
								<span class="text-3xl">📊</span>
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
									{{ formatCurrency(stats.average_buyin) }}
								</p>
							</div>
							<div class="h-16 w-16 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-lg">
								<span class="text-3xl">📈</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div v-if="stats.roi" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
					<div class="flex items-center">
						<div class="h-12 w-12 rounded-lg bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center mr-4">
							<span class="text-2xl">📉</span>
						</div>
						<div>
							<p class="text-sm font-medium text-gray-600 dark:text-gray-400">ROI</p>
							<p
								class="text-2xl font-bold"
								:class="stats.roi >= 0 ? 'text-green-600' : 'text-red-600'"
							>
								{{ stats.roi }}%
							</p>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
					<div class="flex items-center">
						<div class="h-12 w-12 rounded-lg bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center mr-4">
							<span class="text-2xl">💵</span>
						</div>
						<div>
							<p class="text-sm font-medium text-gray-600 dark:text-gray-400">Средний кэшаут</p>
							<p class="text-2xl font-bold text-gray-900 dark:text-white">
								{{ formatCurrency(stats.average_cashout) }}
							</p>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
					<div class="flex items-center">
						<div class="h-12 w-12 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center mr-4">
							<span class="text-2xl">🏆</span>
						</div>
						<div>
							<p class="text-sm font-medium text-gray-600 dark:text-gray-400">ITM турниров</p>
							<p class="text-2xl font-bold text-gray-900 dark:text-white">
								{{ stats.itm_count }}
							</p>
						</div>
					</div>
				</div>
			</div>

			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
				<div class="flex items-center mb-6">
					<div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
						<span class="text-xl">📊</span>
					</div>
					<h3 class="text-xl font-bold text-gray-900 dark:text-white">
						Динамика банкролла
					</h3>
				</div>
				<div v-if="stats.bankroll_history && stats.bankroll_history.length > 0" class="space-y-3">
					<div
						v-for="(item, index) in stats.bankroll_history.slice(-10)"
						:key="index"
						class="flex justify-between items-center py-3 px-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
					>
						<div class="flex items-center space-x-3">
							<div
								class="h-2 w-2 rounded-full"
								:class="item.balance >= 0 ? 'bg-green-500' : 'bg-red-500'"
							></div>
							<span class="text-sm font-medium text-gray-700 dark:text-gray-300">
								{{ formatDate(item.date) }}
							</span>
						</div>
						<span
							class="text-sm font-bold"
							:class="item.balance >= 0 ? 'text-green-600' : 'text-red-600'"
						>
							{{ formatCurrency(item.balance) }}
						</span>
					</div>
				</div>
				<div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
					<span class="text-4xl mb-4 block">📭</span>
					<p>Нет данных для отображения</p>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useStatisticsStore } from '../../stores/statistics';

const statisticsStore = useStatisticsStore();
const { stats, loading } = storeToRefs(statisticsStore);

const selectedPeriod = ref('month');
const customDateFrom = ref('');
const customDateTo = ref('');

const getDateRange = (period) => {
	const today = new Date();
	const endDate = today.toISOString().split('T')[0];
	let startDate;

	switch (period) {
		case 'month':
			startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
			break;
		case '3months':
			startDate = new Date(today.getFullYear(), today.getMonth() - 2, 1).toISOString().split('T')[0];
			break;
		case '6months':
			startDate = new Date(today.getFullYear(), today.getMonth() - 5, 1).toISOString().split('T')[0];
			break;
		case 'year':
			startDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
			break;
		case 'custom':
			return {
				start_date: customDateFrom.value || null,
				end_date: customDateTo.value || null,
			};
		default:
			return {};
	}

	return {
		start_date: startDate,
		end_date: endDate,
	};
};

const setPeriod = (period) => {
	selectedPeriod.value = period;
	fetchStats();
};

const fetchStats = () => {
	const params = getDateRange(selectedPeriod.value);
	statisticsStore.fetchStats(params);
};

watch([customDateFrom, customDateTo], () => {
	if (selectedPeriod.value === 'custom') {
		fetchStats();
	}
});

const formatCurrency = (value) => {
	return new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'USD',
	}).format(value);
};

const formatDate = (dateString) => {
	const date = new Date(dateString);
	return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

onMounted(() => {
	fetchStats();
});
</script>
