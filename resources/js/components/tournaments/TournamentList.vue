<template>
	<div>
		<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
			<div>
				<h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Турниры</h2>
				<p class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
					Управление вашими турнирами
				</p>
			</div>
			<router-link
				to="/tournaments/create"
				class="inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 border border-transparent rounded-lg shadow-sm text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105 w-full sm:w-auto"
			>
				<span class="mr-2">➕</span>
				Добавить турнир
			</router-link>
		</div>

		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 mb-6 border border-gray-100 dark:border-gray-700">
			<div class="space-y-4">
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">📅</span>
							Дата начала
						</label>
						<input
							v-model="dateFrom"
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
							v-model="dateTo"
							type="date"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						/>
					</div>
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">💵</span>
							Байин от
						</label>
						<input
							v-model.number="buyinMin"
							type="number"
							step="0.01"
							min="0"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							placeholder="0.00"
						/>
					</div>
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">💵</span>
							Байин до
						</label>
						<input
							v-model.number="buyinMax"
							type="number"
							step="0.01"
							min="0"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							placeholder="∞"
						/>
					</div>
				</div>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">💰</span>
							Кэшаут от
						</label>
						<input
							v-model.number="cashoutMin"
							type="number"
							step="0.01"
							min="0"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							placeholder="0.00"
						/>
					</div>
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">💰</span>
							Кэшаут до
						</label>
						<input
							v-model.number="cashoutMax"
							type="number"
							step="0.01"
							min="0"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							placeholder="∞"
						/>
					</div>
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">🔀</span>
							Сортировать по
						</label>
						<select
							v-model="sortBy"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						>
							<option value="date">Дате</option>
							<option value="buyin">Байину</option>
							<option value="cashout">Кэшауту</option>
							<option value="place">Месту</option>
						</select>
					</div>
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							<span class="mr-2">📊</span>
							Направление
						</label>
						<select
							v-model="sortOrder"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						>
							<option value="desc">По убыванию</option>
							<option value="asc">По возрастанию</option>
						</select>
					</div>
				</div>
				<div class="flex flex-wrap gap-2">
					<button
						@click="applyTodayFilter"
						class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
					>
						Сегодня
					</button>
					<button
						@click="clearFilter"
						class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
					>
						Сбросить
					</button>
				</div>
			</div>
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

		<div v-else-if="tournaments.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 sm:p-12 text-center border border-gray-100 dark:border-gray-700">
			<span class="text-4xl sm:text-6xl mb-4 block">🎰</span>
			<h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">
				Нет турниров
			</h3>
			<p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mb-6">
				Начните отслеживать свой прогресс, добавив первый турнир
			</p>
			<router-link
				to="/tournaments/create"
				class="inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 border border-transparent rounded-lg shadow-sm text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 w-full sm:w-auto"
			>
				Добавить турнир
			</router-link>
		</div>

		<div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
			<div class="divide-y divide-gray-200 dark:divide-gray-700">
				<div
					v-for="tournament in tournaments"
					:key="tournament.id"
					class="px-4 sm:px-6 py-4 sm:py-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
				>
					<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
						<div class="flex items-start sm:items-center space-x-3 sm:space-x-4 flex-1 min-w-0 w-full sm:w-auto">
							<div class="flex-shrink-0">
								<div v-if="tournament.room?.image" class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl overflow-hidden shadow-md">
									<img :src="getRoomImageUrl(tournament.room.image)" :alt="tournament.room.name" class="h-full w-full object-cover" />
								</div>
								<div v-else class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center shadow-md">
									<span class="text-xl sm:text-2xl">{{ tournament.room?.icon || '🎰' }}</span>
								</div>
							</div>
							<div class="flex-1 min-w-0">
								<div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
									<p class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">
										{{ tournament.room?.name }}
									</p>
									<span
										v-if="tournament.place"
										class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium self-start sm:self-auto"
										:class="tournament.place <= 3 
											? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' 
											: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
									>
										{{ tournament.place }} место
									</span>
								</div>
								<div class="mt-1 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
									<span class="flex items-center">
										<span class="mr-1">📅</span>
										{{ formatDate(tournament.date) }}
									</span>
									<span class="flex items-center">
										<span class="mr-1">💵</span>
										Байин: {{ formatBuyin(tournament) }}
									</span>
									<span v-if="tournament.bounty_count > 0" class="flex items-center">
										<span class="mr-1">🎁</span>
										{{ tournament.bounty_count }} баунти
									</span>
									<span v-if="tournament.rebuy_count > 0 || tournament.double_rebuy" class="flex items-center">
										<span class="mr-1">🔄</span>
										{{ formatRebuys(tournament) }}
									</span>
								</div>
							</div>
						</div>
						<div class="flex flex-col sm:flex-row items-end sm:items-center gap-3 sm:gap-6 sm:ml-4 w-full sm:w-auto">
							<div class="text-left sm:text-right w-full sm:w-auto">
								<div
									v-if="hasAnyCashout(tournament)"
									class="text-sm sm:text-base font-semibold text-green-600 dark:text-green-400 space-y-1"
								>
									<div v-if="tournament.cashout">
										Приз: {{ formatCashout(tournament, tournament.cashout) }}
									</div>
									<div v-if="tournament.cashout_bounty">
										Баунти: {{ formatCashout(tournament, tournament.cashout_bounty) }}
									</div>
								</div>
								<div v-else class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
									Не в деньгах
								</div>
								<div
									v-if="hasAnyCashout(tournament)"
									class="text-xs mt-1 text-green-600 dark:text-green-400"
								>
									{{ getProfit(tournament) >= 0 ? '+' : '' }}{{ formatProfit(tournament) }}
								</div>
							</div>
							<router-link
								:to="`/tournaments/${tournament.id}/edit`"
								class="inline-flex items-center justify-center px-4 py-2 text-xs sm:text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors w-full sm:w-auto"
							>
								<span class="mr-1">✏️</span>
								<span class="hidden sm:inline">Редактировать</span>
								<span class="sm:hidden">Редакт.</span>
							</router-link>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useTournamentStore } from '../../stores/tournaments';

const tournamentStore = useTournamentStore();
const { tournaments, loading } = storeToRefs(tournamentStore);

const today = new Date().toISOString().split('T')[0];
const dateFrom = ref(today);
const dateTo = ref(today);
const buyinMin = ref(null);
const buyinMax = ref(null);
const cashoutMin = ref(null);
const cashoutMax = ref(null);
const sortBy = ref('date');
const sortOrder = ref('desc');

const applyTodayFilter = () => {
	dateFrom.value = today;
	dateTo.value = today;
	buyinMin.value = null;
	buyinMax.value = null;
	cashoutMin.value = null;
	cashoutMax.value = null;
	sortBy.value = 'date';
	sortOrder.value = 'desc';
	fetchTournaments();
};

const clearFilter = () => {
	dateFrom.value = '';
	dateTo.value = '';
	buyinMin.value = null;
	buyinMax.value = null;
	cashoutMin.value = null;
	cashoutMax.value = null;
	sortBy.value = 'date';
	sortOrder.value = 'desc';
	fetchTournaments();
};

const fetchTournaments = () => {
	const params = {};
	if (dateFrom.value) {
		params.date_from = dateFrom.value;
	}
	if (dateTo.value) {
		params.date_to = dateTo.value;
	}
	if (buyinMin.value !== null && buyinMin.value !== '') {
		params.buyin_min = buyinMin.value;
	}
	if (buyinMax.value !== null && buyinMax.value !== '') {
		params.buyin_max = buyinMax.value;
	}
	if (cashoutMin.value !== null && cashoutMin.value !== '') {
		params.cashout_min = cashoutMin.value;
	}
	if (cashoutMax.value !== null && cashoutMax.value !== '') {
		params.cashout_max = cashoutMax.value;
	}
	if (sortBy.value) {
		params.sort_by = sortBy.value;
	}
	if (sortOrder.value) {
		params.sort_order = sortOrder.value;
	}
	tournamentStore.fetchTournaments(params);
};

watch([dateFrom, dateTo, buyinMin, buyinMax, cashoutMin, cashoutMax, sortBy, sortOrder], () => {
	fetchTournaments();
});

const formatCurrency = (value, currencyCode = 'USD', symbol = '$') => {
	const numValue = typeof value === 'number' ? value : parseFloat(value) || 0;
	
	if (currencyCode === 'USD') {
		return new Intl.NumberFormat('ru-RU', {
			style: 'currency',
			currency: 'USD',
		}).format(numValue);
	}
	return `${symbol}${numValue.toFixed(2)}`;
};

const formatBuyin = (tournament) => {
	if (!tournament.currency || tournament.currency.code === 'USD') {
		return formatCurrency(tournament.buyin);
	}

	const buyinInCurrency = parseFloat(tournament.buyin) || 0;
	const buyinInUSD = buyinInCurrency / parseFloat(tournament.currency.rate_to_usd);

	return `${formatCurrency(buyinInCurrency, tournament.currency.code, tournament.currency.symbol)} (${formatCurrency(buyinInUSD)})`;
};

const formatCashout = (tournament, amount) => {
	const value = typeof amount === 'number' || amount !== undefined
		? (typeof amount === 'number' ? amount : parseFloat(amount) || 0)
		: (parseFloat(tournament.cashout) || 0);

	if (!value) {
		return '';
	}
	
	if (!tournament.currency || tournament.currency.code === 'USD') {
		return formatCurrency(value);
	}

	const cashoutInCurrency = value;
	const cashoutInUSD = cashoutInCurrency / parseFloat(tournament.currency.rate_to_usd);

	return `${formatCurrency(cashoutInCurrency, tournament.currency.code, tournament.currency.symbol)} (${formatCurrency(cashoutInUSD)})`;
};

const formatRebuys = (tournament) => {
	const rebuyCount = parseInt(tournament.rebuy_count) || 0;
	const doubleRebuy = tournament.double_rebuy || false;
	const buyin = parseFloat(tournament.buyin) || 0;
	
	if (rebuyCount === 0 && !doubleRebuy) {
		return '';
	}

	let rebuyText = '';
	if (rebuyCount > 0) {
		rebuyText = `${rebuyCount} ребаев`;
	}
	if (doubleRebuy) {
		if (rebuyText) {
			rebuyText += ' + двойной ребай';
		} else {
			rebuyText = 'двойной ребай';
		}
	}

	const rebuyAmount = (rebuyCount * buyin) + (doubleRebuy ? 2 * buyin : 0);
	
	if (!tournament.currency || tournament.currency.code === 'USD') {
		return `${rebuyText} (${formatCurrency(rebuyAmount)})`;
	}

	const rebuyInCurrency = rebuyAmount;
	const rebuyInUSD = rebuyInCurrency / parseFloat(tournament.currency.rate_to_usd);

	return `${rebuyText} (${formatCurrency(rebuyInCurrency, tournament.currency.code, tournament.currency.symbol)} / ${formatCurrency(rebuyInUSD)})`;
};

const formatProfit = (tournament) => {
	const buyin = parseFloat(tournament.buyin) || 0;
	const bountyCount = parseInt(tournament.bounty_count) || 0;
	const rebuyCount = parseInt(tournament.rebuy_count) || 0;
	const doubleRebuy = tournament.double_rebuy || false;
	const cashoutPrize = parseFloat(tournament.cashout) || 0;
	const cashoutBounty = parseFloat(tournament.cashout_bounty) || 0;
	const cashout = cashoutPrize + cashoutBounty;
	const rebuyAmount = (rebuyCount * buyin) + (doubleRebuy ? 2 * buyin : 0);
	const totalBuyin = buyin + (bountyCount * buyin) + rebuyAmount;
	const profit = cashout - totalBuyin;

	if (!tournament.currency || tournament.currency.code === 'USD') {
		return formatCurrency(profit);
	}

	const profitInCurrency = profit;
	const profitInUSD = profitInCurrency / parseFloat(tournament.currency.rate_to_usd);

	return `${formatCurrency(profitInCurrency, tournament.currency.code, tournament.currency.symbol)} (${formatCurrency(profitInUSD)})`;
};

const formatDate = (date) => {
	return new Date(date).toLocaleDateString('ru-RU');
};

const getRoomImageUrl = (imagePath) => {
	if (!imagePath) return null;
	if (imagePath.startsWith('http')) return imagePath;
	return `/storage/${imagePath}`;
};

const getProfit = (tournament) => {
	const buyin = parseFloat(tournament.buyin) || 0;
	const bountyCount = parseInt(tournament.bounty_count) || 0;
	const rebuyCount = parseInt(tournament.rebuy_count) || 0;
	const doubleRebuy = tournament.double_rebuy || false;
	const cashoutPrize = parseFloat(tournament.cashout) || 0;
	const cashoutBounty = parseFloat(tournament.cashout_bounty) || 0;
	const rebuyAmount = (rebuyCount * buyin) + (doubleRebuy ? 2 * buyin : 0);
	const totalBuyin = buyin + (bountyCount * buyin) + rebuyAmount;
	const totalCashout = cashoutPrize + cashoutBounty;
	return totalCashout - totalBuyin;
};

const hasAnyCashout = (tournament) => {
	return !!(tournament.cashout || tournament.cashout_bounty);
};

onMounted(() => {
	fetchTournaments();
});
</script>
