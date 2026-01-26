<template>
	<div>
		<div class="mb-6">
			<button
				@click="$router.push(`/locations/${locationId}`)"
				class="mb-4 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
			>
				<span class="mr-2">←</span>
				Назад к локации
			</button>
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
				<div class="flex items-center justify-between">
					<div class="flex-1">
						<h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ tournament?.name }}</h2>
						<div class="flex items-center space-x-3 flex-wrap">
							<span class="text-sm text-gray-600 dark:text-gray-400">
								📅 {{ formatDate(tournament?.date) }}
							</span>
							<span class="text-sm text-gray-600 dark:text-gray-400">
								💵 {{ formatBuyin(tournament) }}
							</span>
							<span class="text-sm text-gray-600 dark:text-gray-400">
								🎯 {{ tournament?.format_label }}
							</span>
							<span class="text-sm text-gray-600 dark:text-gray-400">
								💰 ИТМ: {{ tournament?.itm_percentage || 15 }}%
							</span>
							<span
								v-if="tournament?.is_finished"
								class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
							>
								Завершен
							</span>
							<span
								v-else
								class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
							>
								В процессе
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div v-if="tournament && tournament.prize_pool" class="mb-6">
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
				<div class="flex items-center justify-between">
					<div>
						<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Призовой фонд</h3>
						<p class="text-3xl font-bold text-gray-900 dark:text-white">
							{{ formatCurrency(tournament.prize_pool, tournament.currency) }}
						</p>
						<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
							Всего входов: {{ formatCurrency(tournament.total_buyin, tournament.currency) }}
						</p>
					</div>
				</div>
			</div>
		</div>

		<div v-if="tournament && tournament.prize_distribution && tournament.prize_distribution.length > 0" class="mb-6">
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
				<h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Распределение призов</h3>
				<div class="space-y-3">
					<div
						v-for="(prize, index) in tournament.prize_distribution"
						:key="prize.place"
						class="flex items-center justify-between p-4 rounded-lg"
						:class="index === 0 
							? 'bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-300 dark:border-yellow-700' 
							: index === 1 
							? 'bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-300 dark:border-gray-600' 
							: 'bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-300 dark:border-orange-700'"
					>
						<div class="flex items-center space-x-4">
							<div class="text-2xl font-bold"
								:class="index === 0 
									? 'text-yellow-600 dark:text-yellow-400' 
									: index === 1 
									? 'text-gray-600 dark:text-gray-400' 
									: 'text-orange-600 dark:text-orange-400'"
							>
								{{ prize.place }} место
							</div>
							<div>
								<p class="text-xs text-gray-500 dark:text-gray-400">
									{{ prize.percentage }}% от призового фонда
								</p>
							</div>
						</div>
						<div class="text-xl font-bold text-green-600 dark:text-green-400">
							{{ formatCurrency(prize.prize, tournament.currency) }}
						</div>
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
				<div class="text-gray-600 dark:text-gray-400">Загрузка...</div>
			</div>
		</div>

		<div v-if="!loading && tournament" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
			<div class="flex items-center justify-between mb-6">
				<h3 class="text-xl font-bold text-gray-900 dark:text-white">Участники</h3>
				<button
					v-if="!tournament.is_finished && location?.is_admin"
					@click="openFinishModal"
					class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-700 hover:to-emerald-700 transition-colors"
				>
					Завершить турнир
				</button>
			</div>

			<div v-if="tournament.participants && tournament.participants.filter(p => {
				const name = p.display_name || p.name || p.user?.name || '';
				return name && name !== 'Без имени' && name !== 'Неизвестный участник';
			}).length > 0" class="space-y-3">
				<div
					v-for="participant in tournament.participants.filter(p => {
						const name = p.display_name || p.name || p.user?.name || '';
						return name && name !== 'Без имени' && name !== 'Неизвестный участник';
					})"
					:key="participant.id"
					class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
				>
					<div class="flex-1">
						<div class="flex items-center space-x-3">
							<span class="text-lg font-bold text-gray-900 dark:text-white w-8">
								{{ participant.place }}.
							</span>
							<div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
								{{ (participant.display_name || participant.name || participant.user?.name || '?')?.charAt(0).toUpperCase() }}
							</div>
							<span class="text-sm font-medium text-gray-900 dark:text-white">
								{{ participant.display_name || participant.name || participant.user?.name || 'Неизвестный участник' }}
							</span>
						</div>
					</div>
					<div v-if="!tournament.is_finished && location?.is_admin" class="flex items-center space-x-2">
						<div>
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
								Ребаи
							</label>
							<input
								v-model.number="participant.rebuy"
								type="number"
								min="0"
								class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200 text-sm"
								@change="updateParticipant(participant)"
							/>
						</div>
						<div>
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
								Аддон
							</label>
							<input
								v-model="participant.addon"
								type="checkbox"
								class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 mt-2"
								@change="updateParticipant(participant)"
							/>
						</div>
					</div>
					<div v-else class="flex items-center space-x-4">
						<div v-if="participant.rebuy > 0" class="text-sm text-gray-600 dark:text-gray-400">
							Ребаи: {{ participant.rebuy }}
						</div>
						<div v-if="participant.addon" class="text-sm text-gray-600 dark:text-gray-400">
							Аддон: ✓
						</div>
						<div v-if="participant.prize" class="text-sm font-bold text-green-600 dark:text-green-400">
							Приз: {{ formatCurrency(participant.prize, tournament.currency) }}
						</div>
					</div>
				</div>
			</div>
			<div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
				<span class="text-4xl mb-4 block">📭</span>
				<p>Нет участников в этом турнире</p>
			</div>
		</div>

		<div v-if="showFinishModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
				<div class="p-6">
					<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Завершение турнира</h3>
					<p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
						Выберите участников в порядке призовых мест (первый выбранный - 3 место, последний - 1 место)
					</p>

					<div v-if="tournament && tournament.prize_distribution" class="mb-6">
						<div class="grid grid-cols-3 gap-4 mb-6">
							<div
								v-for="(prize, index) in tournament.prize_distribution"
								:key="prize.place"
								class="p-4 rounded-lg text-center"
								:class="index === 2 
									? 'bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-300 dark:border-yellow-700' 
									: index === 1 
									? 'bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-300 dark:border-gray-600' 
									: 'bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-300 dark:border-orange-700'"
							>
								<div class="text-2xl font-bold mb-2"
									:class="index === 2 
										? 'text-yellow-600 dark:text-yellow-400' 
										: index === 1 
										? 'text-gray-600 dark:text-gray-400' 
										: 'text-orange-600 dark:text-orange-400'"
								>
									{{ prize.place }} место
								</div>
								<div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
									{{ formatCurrency(prize.prize, tournament.currency) }}
								</div>
								<div v-if="selectedWinners.length > 0 && getWinnerPlace(selectedWinners[selectedWinners.length - index - 1]) === prize.place" class="mt-2 text-xs text-green-600 dark:text-green-400">
									✓ Выбран
								</div>
							</div>
						</div>

						<div class="space-y-2">
							<p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
								Участники ({{ selectedWinners.length }}/3 выбрано):
							</p>
							<div
								v-for="participant in tournament.participants.filter(p => {
									const name = p.display_name || p.name || p.user?.name || '';
									return name && name !== 'Без имени' && name !== 'Неизвестный участник';
								})"
								:key="participant.id"
								@click="toggleWinner(participant.id)"
								class="flex items-center justify-between p-4 border-2 rounded-lg cursor-pointer transition-colors"
								:class="selectedWinners.includes(participant.id)
									? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700'
									: 'bg-gray-50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'"
							>
								<div class="flex items-center space-x-3">
									<div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
										{{ (participant.display_name || participant.name || participant.user?.name || '?')?.charAt(0).toUpperCase() }}
									</div>
									<div>
										<p class="text-sm font-medium text-gray-900 dark:text-white">
											{{ participant.display_name || participant.name || participant.user?.name || 'Неизвестный участник' }}
										</p>
										<p v-if="selectedWinners.includes(participant.id)" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mt-1">
											{{ getWinnerPlace(participant.id) }} место - {{ formatCurrency(getWinnerPrize(getWinnerPlace(participant.id)), tournament.currency) }}
										</p>
									</div>
								</div>
								<div v-if="selectedWinners.includes(participant.id)" class="flex items-center space-x-2">
									<span class="text-lg font-bold"
										:class="getWinnerPlace(participant.id) === 1
											? 'text-yellow-600 dark:text-yellow-400'
											: getWinnerPlace(participant.id) === 2
											? 'text-gray-600 dark:text-gray-400'
											: 'text-orange-600 dark:text-orange-400'"
									>
										{{ getWinnerPlace(participant.id) }}
									</span>
									<svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
										<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
									</svg>
								</div>
							</div>
						</div>
					</div>

					<div class="flex space-x-3">
						<button
							@click="finishTournament"
							class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-colors font-medium"
						>
							Завершить турнир
						</button>
						<button
							@click="closeFinishModal"
							class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium"
						>
							Отмена
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { locationService } from '../../services/locationService';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { user: currentUser } = storeToRefs(authStore);

const tournament = ref(null);
const location = ref(null);
const loading = ref(false);
const saving = ref(false);

const locationId = computed(() => route.params.locationId);

const fetchTournament = async () => {
	loading.value = true;
	try {
		tournament.value = await locationService.getTournament(route.params.locationId, route.params.id);
		await fetchLocation();
	} catch (error) {
		console.error('Error fetching tournament:', error);
		alert('Ошибка при загрузке турнира');
		router.push(`/locations/${route.params.locationId}`);
	} finally {
		loading.value = false;
	}
};

const fetchLocation = async () => {
	try {
		location.value = await locationService.getById(route.params.locationId);
	} catch (error) {
		console.error('Error fetching location:', error);
	}
};

const updateParticipant = async (participant) => {
	if (saving.value) return;
	
	saving.value = true;
	try {
		const participantsData = tournament.value.participants.map(p => {
			if (p.id === participant.id) {
				return {
					id: p.id,
					rebuy: p.rebuy ?? 0,
					addon: p.addon ?? false,
					prize: p.prize ?? null,
				};
			}
			return {
				id: p.id,
				rebuy: p.rebuy ?? 0,
				addon: p.addon ?? false,
				prize: p.prize ?? null,
			};
		});

		await locationService.updateTournamentParticipants(
			route.params.locationId,
			route.params.id,
			{ participants: participantsData }
		);
		await fetchTournament();
	} catch (error) {
		console.error('Error updating participant:', error);
		alert('Ошибка при обновлении участника');
	} finally {
		saving.value = false;
	}
};

const showFinishModal = ref(false);
const selectedWinners = ref([]);

const openFinishModal = () => {
	if (!tournament.value || !tournament.value.prize_distribution) return;
	selectedWinners.value = [];
	showFinishModal.value = true;
};

const toggleWinner = (participantId) => {
	const index = selectedWinners.value.indexOf(participantId);
	if (index === -1) {
		if (selectedWinners.value.length < 3) {
			selectedWinners.value.push(participantId);
		}
	} else {
		selectedWinners.value.splice(index, 1);
	}
};

const getWinnerPlace = (participantId) => {
	const index = selectedWinners.value.indexOf(participantId);
	if (index === -1) return null;
	return 3 - index;
};

const getWinnerPrize = (place) => {
	if (!tournament.value || !tournament.value.prize_distribution) return 0;
	const prizeInfo = tournament.value.prize_distribution.find(p => p.place === place);
	return prizeInfo ? prizeInfo.prize : 0;
};

const closeFinishModal = () => {
	showFinishModal.value = false;
};

const finishTournament = async () => {
	if (selectedWinners.value.length === 0) {
		alert('Необходимо выбрать хотя бы одного победителя');
		return;
	}

	try {
		const participantsData = tournament.value.participants.map(p => ({
			id: p.id,
			rebuy: p.rebuy ?? 0,
			addon: p.addon ?? false,
			prize: null,
		}));

		selectedWinners.value.forEach((participantId, index) => {
			const place = 3 - index;
			const prize = getWinnerPrize(place);
			const participant = participantsData.find(p => p.id === participantId);
			if (participant) {
				participant.prize = prize;
			}
		});

		await locationService.updateTournamentParticipants(
			route.params.locationId,
			route.params.id,
			{ participants: participantsData }
		);

		await locationService.finishTournament(route.params.locationId, route.params.id);
		await fetchTournament();
		closeFinishModal();
	} catch (error) {
		console.error('Error finishing tournament:', error);
		alert('Ошибка при завершении турнира');
	}
};

const formatCurrency = (value, currency = null) => {
	const numValue = typeof value === 'number' ? value : parseFloat(value) || 0;
	
	if (!currency || currency.code === 'USD') {
		return new Intl.NumberFormat('ru-RU', {
			style: 'currency',
			currency: 'USD',
		}).format(numValue);
	}
	
	return `${currency.symbol}${numValue.toFixed(2)}`;
};

const formatBuyin = (tournament) => {
	if (!tournament || !tournament.buyin) return '';
	if (!tournament.currency || tournament.currency.code === 'USD') {
		return formatCurrency(tournament.buyin);
	}

	const buyinInCurrency = parseFloat(tournament.buyin) || 0;
	const rate = parseFloat(tournament.currency.rate_to_usd || 1);
	const buyinInUSD = buyinInCurrency / rate;

	return `${formatCurrency(buyinInCurrency, tournament.currency)} (${formatCurrency(buyinInUSD)})`;
};

const formatDate = (dateString) => {
	const date = new Date(dateString);
	return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

onMounted(() => {
	fetchTournament();
});
</script>
