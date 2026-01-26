<template>
	<div>
		<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
			<div>
				<h2 class="text-3xl font-bold text-gray-900 dark:text-white">Турниры</h2>
				<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
					Управление вашими турнирами
				</p>
			</div>
			<router-link
				to="/tournaments/create"
				class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105"
			>
				<span class="mr-2">➕</span>
				Добавить турнир
			</router-link>
		</div>

		<div v-if="loading" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка турниров...</div>
			</div>
		</div>

		<div v-else-if="tournaments.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 text-center border border-gray-100 dark:border-gray-700">
			<span class="text-6xl mb-4 block">🎰</span>
			<h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
				Нет турниров
			</h3>
			<p class="text-gray-600 dark:text-gray-400 mb-6">
				Начните отслеживать свой прогресс, добавив первый турнир
			</p>
			<router-link
				to="/tournaments/create"
				class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
			>
				Добавить турнир
			</router-link>
		</div>

		<div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
			<div class="divide-y divide-gray-200 dark:divide-gray-700">
				<div
					v-for="tournament in tournaments"
					:key="tournament.id"
					class="px-6 py-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
				>
					<div class="flex items-center justify-between">
						<div class="flex items-center space-x-4 flex-1">
							<div class="flex-shrink-0">
								<div class="h-14 w-14 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center shadow-md">
									<span class="text-2xl">{{ tournament.room?.icon || '🎰' }}</span>
								</div>
							</div>
							<div class="flex-1 min-w-0">
								<div class="flex items-center space-x-3">
									<p class="text-lg font-semibold text-gray-900 dark:text-white">
										{{ tournament.room?.name }}
									</p>
									<span
										v-if="tournament.place"
										class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
										:class="tournament.place <= 3 
											? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' 
											: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
									>
										{{ tournament.place }} место
									</span>
								</div>
								<div class="mt-1 flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
									<span class="flex items-center">
										<span class="mr-1">📅</span>
										{{ formatDate(tournament.date) }}
									</span>
									<span class="flex items-center">
										<span class="mr-1">💵</span>
										Байин: {{ formatCurrency(tournament.buyin) }}
									</span>
									<span v-if="tournament.bounty_count > 0" class="flex items-center">
										<span class="mr-1">🎁</span>
										{{ tournament.bounty_count }} баунти
									</span>
								</div>
							</div>
						</div>
						<div class="flex items-center space-x-6 ml-4">
							<div class="text-right">
								<div
									v-if="tournament.cashout"
									class="text-lg font-bold"
									:class="getProfit(tournament) >= 0 ? 'text-green-600' : 'text-red-600'"
								>
									{{ formatCurrency(tournament.cashout) }}
								</div>
								<div v-else class="text-sm font-medium text-gray-500 dark:text-gray-400">
									Не в деньгах
								</div>
								<div
									v-if="tournament.cashout"
									class="text-xs mt-1"
									:class="getProfit(tournament) >= 0 ? 'text-green-600' : 'text-red-600'"
								>
									{{ getProfit(tournament) >= 0 ? '+' : '' }}{{ formatCurrency(getProfit(tournament)) }}
								</div>
							</div>
							<router-link
								:to="`/tournaments/${tournament.id}/edit`"
								class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors"
							>
								<span class="mr-1">✏️</span>
								Редактировать
							</router-link>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useTournamentStore } from '../../stores/tournaments';

const tournamentStore = useTournamentStore();
const { tournaments, loading } = storeToRefs(tournamentStore);

const formatCurrency = (value) => {
	return new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'USD',
	}).format(value);
};

const formatDate = (date) => {
	return new Date(date).toLocaleDateString('ru-RU');
};

const getProfit = (tournament) => {
	const totalBuyin = tournament.buyin + (tournament.bounty_count * tournament.buyin);
	return (tournament.cashout || 0) - totalBuyin;
};

onMounted(() => {
	tournamentStore.fetchTournaments();
});
</script>
