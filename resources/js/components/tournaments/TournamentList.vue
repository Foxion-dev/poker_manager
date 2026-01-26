<template>
	<div class="px-4 py-6 sm:px-0">
		<div class="flex justify-between items-center mb-6">
			<h2 class="text-2xl font-bold text-gray-900 dark:text-white">Турниры</h2>
			<router-link
				to="/tournaments/create"
				class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
			>
				Добавить турнир
			</router-link>
		</div>

		<div v-if="loading" class="text-center py-12">
			<div class="text-gray-500">Загрузка...</div>
		</div>

		<div v-else class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
			<ul class="divide-y divide-gray-200 dark:divide-gray-700">
				<li v-for="tournament in tournaments" :key="tournament.id">
					<div class="px-4 py-4 sm:px-6">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="flex-shrink-0">
									<span class="text-2xl">{{ tournament.room?.icon || '🎰' }}</span>
								</div>
								<div class="ml-4">
									<div class="text-sm font-medium text-gray-900 dark:text-white">
										{{ tournament.room?.name }}
									</div>
									<div class="text-sm text-gray-500 dark:text-gray-400">
										{{ formatDate(tournament.date) }}
									</div>
								</div>
							</div>
							<div class="flex items-center space-x-4">
								<div class="text-right">
									<div class="text-sm font-medium text-gray-900 dark:text-white">
										Байин: {{ formatCurrency(tournament.buyin) }}
									</div>
									<div
										v-if="tournament.cashout"
										class="text-sm"
										:class="getProfit(tournament) >= 0 ? 'text-green-600' : 'text-red-600'"
									>
										Кэшаут: {{ formatCurrency(tournament.cashout) }}
									</div>
									<div v-else class="text-sm text-gray-500">
										Не в деньгах
									</div>
								</div>
								<router-link
									:to="`/tournaments/${tournament.id}/edit`"
									class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
								>
									Редактировать
								</router-link>
							</div>
						</div>
					</div>
				</li>
			</ul>
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
