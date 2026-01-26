<template>
	<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
		<div class="container mx-auto px-4 py-8">
			<div v-if="showPasswordForm" class="max-w-md mx-auto mt-20">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
					<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Введите пароль</h3>
					<p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
						Эта локация защищена паролем. Введите пароль для доступа.
					</p>
					<form @submit.prevent="submitPassword" class="space-y-4">
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Пароль *
							</label>
							<input
								v-model="locationPassword"
								type="password"
								required
								autofocus
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							/>
						</div>
						<div class="flex justify-end space-x-3 pt-4">
							<button
								type="button"
								@click="$router.push('/')"
								class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
							>
								Отмена
							</button>
							<button
								type="submit"
								:disabled="checkingPassword"
								class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors"
							>
								{{ checkingPassword ? 'Проверка...' : 'Войти' }}
							</button>
						</div>
					</form>
				</div>
			</div>

			<div v-if="loading" class="flex items-center justify-center py-20">
				<div class="text-center">
					<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
					<div class="text-gray-600 dark:text-gray-400">Загрузка...</div>
				</div>
			</div>

			<div v-if="!loading && !showPasswordForm && location" class="max-w-6xl mx-auto">
				<div class="mb-6">
					<button
						@click="$router.push('/')"
						class="mb-4 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
					>
						<span class="mr-2">←</span>
						На главную
					</button>
					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
						<div class="flex items-center justify-between">
							<div class="flex-1">
								<h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ location.name }}</h2>
								<div v-if="location.description" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
									{{ location.description }}
								</div>
								<div class="flex items-center space-x-3">
									<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
										Публичная локация
									</span>
									<span class="text-sm text-gray-600 dark:text-gray-400">
										Создатель: {{ location.user?.name }}
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
						<div class="flex items-center mb-4">
							<div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
								<span class="text-xl">🎮</span>
							</div>
							<h3 class="text-lg font-bold text-gray-900 dark:text-white">
								Всего турниров
							</h3>
						</div>
						<p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
							{{ location.tournaments_count }}
						</p>
					</div>

					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
						<div class="flex items-center mb-4">
							<div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
								<span class="text-xl">💵</span>
							</div>
							<h3 class="text-lg font-bold text-gray-900 dark:text-white">
								Средний байин
							</h3>
						</div>
						<p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
							{{ formatCurrency(location.average_buyin) }}
						</p>
					</div>
				</div>

				<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
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
											Победы
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
										<td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-yellow-600 dark:text-yellow-400">
											{{ player.wins }}
										</td>
									</tr>
									<tr v-if="location.top_players_by_wins.length === 0">
										<td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
											Нет данных
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
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
									<tr v-if="location.top_players_by_prize.length === 0">
										<td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
											Нет данных
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
					<div class="flex items-center justify-between mb-6">
						<div class="flex items-center">
							<div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
								<span class="text-xl">🎮</span>
							</div>
							<h3 class="text-xl font-bold text-gray-900 dark:text-white">
								Последние турниры
							</h3>
						</div>
					</div>
					<div v-if="tournaments.length > 0" class="space-y-4">
						<div
							v-for="tournament in tournaments"
							:key="tournament.id"
							class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
						>
							<div class="flex items-start justify-between">
								<div class="flex-1">
									<h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ tournament.name }}</h4>
									<div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
										<span>📅 {{ formatDate(tournament.date) }}</span>
										<span>💵 {{ formatCurrency(tournament.buyin) }}</span>
										<span>🎯 {{ tournament.format_label }}</span>
									</div>
									<div v-if="tournament.participants && tournament.participants.length > 0" class="mt-3">
										<p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Участники:</p>
										<div class="flex flex-wrap gap-2">
											<div
												v-for="participant in tournament.participants"
												:key="participant.id"
												class="px-3 py-1 text-xs rounded-full"
												:class="participant.place === 1 
													? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' 
													: participant.prize > 0 
														? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
														: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
											>
												{{ participant.user.name }} - {{ participant.place }} место
												<span v-if="participant.prize > 0"> ({{ formatCurrency(participant.prize) }})</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
						<span class="text-4xl mb-4 block">📭</span>
						<p>Нет турниров в этой локации</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { locationService } from '../../services/locationService';

const route = useRoute();
const router = useRouter();

const location = ref(null);
const tournaments = ref([]);
const loading = ref(false);
const showPasswordForm = ref(false);
const locationPassword = ref('');
const checkingPassword = ref(false);

const fetchLocation = async (password = null) => {
	loading.value = true;
	try {
		const params = password ? { password } : {};
		location.value = await locationService.getPublicLocation(route.params.id, password);
		await fetchTournaments(password);
	} catch (error) {
		if (error.response?.status === 403 && error.response?.data?.requires_password) {
			showPasswordForm.value = true;
		} else {
			console.error('Error fetching location:', error);
			alert('Ошибка при загрузке локации');
			router.push('/');
		}
	} finally {
		loading.value = false;
	}
};

const fetchTournaments = async (password = null) => {
	try {
		tournaments.value = await locationService.getPublicTournaments(route.params.id, password, 10);
	} catch (error) {
		console.error('Error fetching tournaments:', error);
	}
};

const submitPassword = async () => {
	checkingPassword.value = true;
	try {
		await fetchLocation(locationPassword.value);
		showPasswordForm.value = false;
		locationPassword.value = '';
	} catch (error) {
		if (error.response?.status === 403) {
			alert('Неверный пароль');
		} else {
			console.error('Error submitting password:', error);
			alert('Ошибка при проверке пароля');
		}
	} finally {
		checkingPassword.value = false;
	}
};

const formatCurrency = (value) => {
	if (value === null || value === undefined) return '$0.00';
	const numValue = typeof value === 'string' ? parseFloat(value) : value;
	if (isNaN(numValue)) return '$0.00';
	return new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'USD',
		minimumFractionDigits: 2,
		maximumFractionDigits: 2,
	}).format(numValue);
};

const formatDate = (dateString) => {
	const date = new Date(dateString);
	return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

onMounted(() => {
	fetchLocation();
});
</script>
