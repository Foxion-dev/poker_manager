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
						<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
							Количество входов: {{ totalEntriesCount }}
						</p>
					</div>
				</div>
			</div>
		</div>

		<div v-if="tournament && dynamicPrizeDistribution && dynamicPrizeDistribution.length > 0" class="mb-6">
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
				<h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Распределение призов</h3>
				<div class="space-y-3">
					<div
						v-for="(prize, index) in dynamicPrizeDistribution"
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
				<div class="flex space-x-2">
					<button
						v-if="!tournament.is_finished && location?.is_admin"
						@click="openPrizeDistributionModal"
						class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
					>
						Настроить призы
					</button>
					<button
						v-if="!tournament.is_finished && location?.is_admin"
						@click="openFinishModal"
						:disabled="!canFinishTournament"
						class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-700 hover:to-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
						:title="canFinishTournament ? 'Завершить турнир' : 'Не все участники оплатили вход'"
					>
						Завершить турнир
					</button>
				</div>
			</div>

			<div v-if="!tournament.is_finished && location?.is_admin" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
				<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
					Добавить участника
				</label>
				<div class="flex items-center space-x-2 mb-2">
					<select
						v-model="newParticipantUserId"
						class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
					>
						<option value="">Выберите пользователя локации (опционально)</option>
						<option v-for="locationUser in availableLocationUsers" :key="locationUser.id" :value="locationUser.user_id || locationUser.id">
							{{ locationUser.display_name || locationUser.name }}
						</option>
					</select>
				</div>
				<div class="flex items-center space-x-2">
					<input
						v-model="newParticipantName"
						type="text"
						placeholder="Или введите имя нового участника"
						class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						@keyup.enter="addParticipant"
					/>
					<button
						@click="addParticipant"
						:disabled="(!newParticipantUserId && !newParticipantName) || addingParticipant"
						class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 transition-colors"
					>
						{{ addingParticipant ? 'Добавление...' : 'Добавить' }}
					</button>
				</div>
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
					<div v-if="!tournament.is_finished && location?.is_admin" class="flex items-center space-x-3">
						<div>
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 text-center">
								Ребаи
							</label>
							<div class="flex items-center space-x-1">
								<button
									type="button"
									@click.prevent="decrementRebuy(participant)"
									class="w-8 h-8 flex items-center justify-center text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors border border-gray-300 dark:border-gray-600"
									:disabled="(participant.rebuy || 0) <= 0"
								>
									−
								</button>
								<input
									v-model.number="participant.rebuy"
									type="number"
									min="0"
									class="w-16 px-2 py-1 text-center border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200 text-sm font-semibold [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
									@change="updateParticipant(participant)"
								/>
								<button
									type="button"
									@click.prevent="incrementRebuy(participant)"
									class="w-8 h-8 flex items-center justify-center text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors border border-gray-300 dark:border-gray-600"
								>
									+
								</button>
							</div>
						</div>
						<div>
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 text-center">
								Аддон
							</label>
							<div class="flex items-center justify-center">
								<label class="relative inline-flex items-center cursor-pointer">
									<input
										v-model="participant.addon"
										type="checkbox"
										class="sr-only peer"
										@change="updateParticipant(participant)"
									/>
									<div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
									<span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
										{{ participant.addon ? 'Да' : 'Нет' }}
									</span>
								</label>
							</div>
						</div>
						<div>
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 text-center">
								Оплата
							</label>
							<div class="flex items-center justify-center">
								<label class="relative inline-flex items-center cursor-pointer">
									<input
										v-model="participant.is_paid"
										type="checkbox"
										class="sr-only peer"
										@change="updateParticipant(participant)"
									/>
									<div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
									<span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300" :class="participant.is_paid ? 'text-green-600 dark:text-green-400 font-bold' : 'text-red-600 dark:text-red-400'">
										{{ participant.is_paid ? 'Оплачено' : 'Не оплачено' }}
									</span>
								</label>
							</div>
						</div>
						<div class="flex items-end">
							<button
								@click="removeParticipant(participant)"
								class="px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
								title="Удалить участника"
							>
								🗑️
							</button>
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
						Выберите участников в порядке призовых мест (первый выбранный - последнее призовое место, последний - 1 место)
					</p>

					<div v-if="tournament && dynamicPrizeDistribution && dynamicPrizeDistribution.length > 0" class="mb-6">
						<div class="grid gap-4 mb-6" :class="`grid-cols-${Math.min(dynamicPrizeDistribution.length, 5)}`">
							<div
								v-for="(prize, index) in dynamicPrizeDistribution"
								:key="prize.place"
								class="p-4 rounded-lg text-center"
								:class="index === tournament.prize_distribution.length - 1
									? 'bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-300 dark:border-yellow-700' 
									: index === tournament.prize_distribution.length - 2
									? 'bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-300 dark:border-gray-600' 
									: index === tournament.prize_distribution.length - 3
									? 'bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-300 dark:border-orange-700'
									: 'bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-700'"
							>
								<div class="text-xl font-bold mb-2"
									:class="index === tournament.prize_distribution.length - 1
										? 'text-yellow-600 dark:text-yellow-400' 
										: index === tournament.prize_distribution.length - 2
										? 'text-gray-600 dark:text-gray-400' 
										: index === tournament.prize_distribution.length - 3
										? 'text-orange-600 dark:text-orange-400'
										: 'text-blue-600 dark:text-blue-400'"
								>
									{{ prize.place }} место
								</div>
								<div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
									{{ formatCurrency(prize.prize, tournament.currency) }}
								</div>
								<div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
									{{ prize.percentage }}%
								</div>
								<div v-if="selectedWinners.length > 0 && getWinnerPlace(selectedWinners[selectedWinners.length - index - 1]) === prize.place" class="mt-2 text-xs text-green-600 dark:text-green-400">
									✓ Выбран
								</div>
							</div>
						</div>

						<div class="space-y-2">
							<p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
								Участники ({{ selectedWinners.length }}/{{ maxPrizePlaces }} выбрано):
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
											: getWinnerPlace(participant.id) === 3
											? 'text-orange-600 dark:text-orange-400'
											: 'text-blue-600 dark:text-blue-400'"
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

		<div v-if="showPrizeDistributionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
				<div class="p-6">
					<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Настройка распределения призов</h3>
					<p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
						Настройте количество призовых мест и процент распределения. Сумма процентов должна быть равна 100%.
					</p>

					<div class="space-y-3 mb-6">
						<div
							v-for="(prize, index) in prizeDistributionForm"
							:key="index"
							class="flex items-center space-x-3 p-4 border border-gray-300 dark:border-gray-600 rounded-lg"
						>
							<div class="w-20">
								<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
									Место
								</label>
								<input
									v-model.number="prize.place"
									type="number"
									min="1"
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200 text-sm"
									readonly
								/>
							</div>
							<div class="flex-1">
								<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
									Процент (%)
								</label>
								<input
									v-model.number="prize.percentage"
									type="number"
									step="5"
									min="0"
									max="100"
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200 text-sm"
								/>
							</div>
							<div class="w-32">
								<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
									Приз
								</label>
								<div class="px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">
									{{ formatCurrency((tournament?.prize_pool || 0) * ((prize.percentage || 0) / 100), tournament?.currency) }}
								</div>
							</div>
							<button
								@click="removePrizePlace(index)"
								class="px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors mt-6"
							>
								Удалить
							</button>
						</div>
					</div>

					<div class="mb-6 flex flex-wrap items-center gap-2">
						<button
							@click="addPrizePlace"
							class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
						>
							+ Добавить место
						</button>
						<button
							@click="resetPrizeDistribution"
							class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
						>
							Пересчитать автоматически
						</button>
						<div class="flex items-center space-x-2">
							<label class="relative inline-flex items-center cursor-pointer">
								<input
									v-model="isFixedPrizeDistribution"
									type="checkbox"
									class="sr-only peer"
								/>
								<div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
								<span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
									Зафиксировать
								</span>
							</label>
						</div>
						<button
							v-if="hasFixedPrizeDistribution"
							@click="clearPrizeDistribution"
							class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
						>
							Очистить кастомное
						</button>
					</div>

					<div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
						<p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
							Сумма процентов: 
							<span :class="Math.abs(prizeDistributionForm.reduce((sum, p) => sum + (parseFloat(p.percentage) || 0), 0) - 100) < 0.01 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
								{{ prizeDistributionForm.reduce((sum, p) => sum + (parseFloat(p.percentage) || 0), 0).toFixed(2) }}%
							</span>
						</p>
						<p class="text-xs text-gray-500 dark:text-gray-400">
							Призовой фонд: {{ formatCurrency(tournament?.prize_pool || 0, tournament?.currency) }}
						</p>
					</div>

					<div class="flex space-x-3">
						<button
							@click="savePrizeDistribution"
							class="flex-1 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-colors font-medium"
						>
							Сохранить
						</button>
						<button
							@click="showPrizeDistributionModal = false"
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
const newParticipantUserId = ref('');
const newParticipantName = ref('');
const addingParticipant = ref(false);
const locationUsers = ref([]);

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
		if (location.value && location.value.users) {
			locationUsers.value = location.value.users;
		}
	} catch (error) {
		console.error('Error fetching location:', error);
	}
};

const availableLocationUsers = computed(() => {
	if (!location.value || !location.value.users || !tournament.value || !tournament.value.participants) {
		return locationUsers.value || [];
	}

	const participantUserIds = tournament.value.participants
		.map(p => p.user_id)
		.filter(id => id !== null && id !== undefined);
	
	const participantNames = tournament.value.participants
		.map(p => p.name)
		.filter(name => name && name.trim() !== '');

	return (locationUsers.value || []).filter(locationUser => {
		if (locationUser.user_id) {
			return !participantUserIds.includes(locationUser.user_id);
		}
		if (locationUser.name) {
			return !participantNames.includes(locationUser.name);
		}
		return true;
	});
});

const canFinishTournament = computed(() => {
	if (!tournament.value || !tournament.value.participants || tournament.value.participants.length === 0) {
		return false;
	}
	
	const validParticipants = tournament.value.participants.filter(p => {
		const name = p.display_name || p.name || p.user?.name || '';
		return name && name !== 'Без имени' && name !== 'Неизвестный участник';
	});
	
	if (validParticipants.length === 0) {
		return false;
	}
	
	return validParticipants.every(p => p.is_paid === true);
});

const totalEntriesCount = computed(() => {
	if (!tournament.value || !tournament.value.participants) {
		return 0;
	}
	
	const validParticipants = tournament.value.participants.filter(p => {
		const name = p.display_name || p.name || p.user?.name || '';
		return name && name !== 'Без имени' && name !== 'Неизвестный участник';
	});
	
	const participantsCount = validParticipants.length;
	const totalRebuys = validParticipants.reduce((sum, p) => sum + (p.rebuy || 0), 0);
	const totalAddons = validParticipants.filter(p => p.addon === true).length;
	
	return participantsCount + totalRebuys + totalAddons;
});

const addParticipant = async () => {
	if (!newParticipantUserId.value && !newParticipantName.value?.trim()) {
		alert('Необходимо указать либо пользователя, либо имя участника');
		return;
	}

	addingParticipant.value = true;
	try {
		const data = {};
		if (newParticipantUserId.value) {
			data.user_id = newParticipantUserId.value;
		}
		if (newParticipantName.value?.trim()) {
			data.name = newParticipantName.value.trim();
		}

		const response = await locationService.addTournamentParticipant(route.params.locationId, route.params.id, data);
		
		if (response && response.participants) {
			tournament.value.participants = response.participants.map(p => ({
				...p,
				display_name: p.display_name || p.name || p.user?.name || 'Неизвестный участник',
			}));
		}
		
		if (response && response.users) {
			location.value.users = response.users;
			locationUsers.value = response.users;
		}
		
		newParticipantUserId.value = '';
		newParticipantName.value = '';
	} catch (error) {
		console.error('Error adding participant:', error);
		const errorMessage = error.response?.data?.message || 'Ошибка при добавлении участника';
		alert(errorMessage);
		await fetchTournament();
		await fetchLocation();
	} finally {
		addingParticipant.value = false;
	}
};

const incrementRebuy = async (participant) => {
	participant.rebuy = (participant.rebuy || 0) + 1;
	await updateParticipant(participant);
};

const decrementRebuy = async (participant) => {
	if ((participant.rebuy || 0) > 0) {
		participant.rebuy = (participant.rebuy || 0) - 1;
		await updateParticipant(participant);
	}
};

const removeParticipant = async (participant) => {
	if (!confirm(`Вы уверены, что хотите удалить участника "${participant.display_name || participant.name || participant.user?.name || 'Неизвестный участник'}" из турнира?`)) {
		return;
	}

	saving.value = true;
	try {
		await locationService.removeTournamentParticipant(route.params.locationId, route.params.id, participant.id);
		
		const response = await locationService.removeTournamentParticipant(route.params.locationId, route.params.id, participant.id);
		
		if (response && response.participants) {
			tournament.value.participants = response.participants.map(p => ({
				...p,
				display_name: p.display_name || p.name || p.user?.name || 'Неизвестный участник',
			}));
		}
		
		if (response && response.users) {
			location.value.users = response.users;
			locationUsers.value = response.users;
		}
	} catch (error) {
		console.error('Error removing participant:', error);
		const errorMessage = error.response?.data?.message || 'Ошибка при удалении участника';
		alert(errorMessage);
		await fetchTournament();
		await fetchLocation();
	} finally {
		saving.value = false;
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
					is_paid: p.is_paid ?? false,
					prize: p.prize ?? null,
				};
			}
			return {
				id: p.id,
				rebuy: p.rebuy ?? 0,
				addon: p.addon ?? false,
				is_paid: p.is_paid ?? false,
				prize: p.prize ?? null,
			};
		});

		const response = await locationService.updateTournamentParticipants(
			route.params.locationId,
			route.params.id,
			{ participants: participantsData }
		);
		
		if (tournament.value) {
			const updatedParticipant = tournament.value.participants.find(p => p.id === participant.id);
			if (updatedParticipant) {
				updatedParticipant.rebuy = participant.rebuy ?? 0;
				updatedParticipant.addon = participant.addon ?? false;
				updatedParticipant.is_paid = participant.is_paid ?? false;
			}
			
			if (response && response.total_buyin !== undefined) {
				tournament.value.total_buyin = response.total_buyin;
			}
			if (response && response.prize_pool !== undefined) {
				tournament.value.prize_pool = response.prize_pool;
			}
			if (response && response.prize_distribution !== undefined) {
				tournament.value.prize_distribution = response.prize_distribution;
			}
		}
	} catch (error) {
		console.error('Error updating participant:', error);
		alert('Ошибка при обновлении участника');
		const errorMessage = error.response?.data?.message || 'Ошибка при обновлении участника';
		alert(errorMessage);
		await fetchTournament();
	} finally {
		saving.value = false;
	}
};

const showFinishModal = ref(false);
const showPrizeDistributionModal = ref(false);
const selectedWinners = ref([]);
const prizeDistributionForm = ref([]);
const isFixedPrizeDistribution = ref(false);

const openPrizeDistributionModal = () => {
	if (!tournament.value) return;
	
	const customDistribution = tournament.value.prize_distribution;
	const hasCustomDistribution = customDistribution && Array.isArray(customDistribution) && customDistribution.length > 0 && customDistribution[0].hasOwnProperty('percentage');
	
	isFixedPrizeDistribution.value = hasCustomDistribution;
	
	if (hasCustomDistribution) {
		prizeDistributionForm.value = customDistribution.map(p => ({
			place: p.place,
			percentage: p.percentage,
		}));
	} else {
		const participantsCount = tournament.value.participants?.length || 0;
		const itmPercentage = tournament.value.itm_percentage || 15;
		const itmPlacesFloat = participantsCount * (itmPercentage / 100);
		
		if (itmPlacesFloat < 0.5) {
			alert('Недостаточно участников для расчета призовых мест');
			return;
		}
		
		const itmPlaces = Math.max(1, Math.min(Math.round(itmPlacesFloat), participantsCount));
		
		prizeDistributionForm.value = [];
		for (let place = 1; place <= itmPlaces; place++) {
			let percentage = 0;
			if (itmPlaces === 1) {
				percentage = 100;
			} else if (itmPlaces === 2) {
				percentage = place === 1 ? 60 : 40;
			} else if (itmPlaces === 3) {
				percentage = place === 1 ? 60 : place === 2 ? 30 : 10;
			} else {
				if (place === 1) percentage = 50;
				else if (place === 2) percentage = 25;
				else if (place === 3) percentage = 12.5;
				else percentage = 12.5 / (itmPlaces - 3);
			}
			
			prizeDistributionForm.value.push({
				place: place,
				percentage: percentage,
			});
		}
	}
	
	showPrizeDistributionModal.value = true;
};

const addPrizePlace = () => {
	const maxPlace = prizeDistributionForm.value.length > 0
		? Math.max(...prizeDistributionForm.value.map(p => p.place))
		: 0;
	prizeDistributionForm.value.push({
		place: maxPlace + 1,
		percentage: 0,
	});
};

const removePrizePlace = (index) => {
	prizeDistributionForm.value.splice(index, 1);
	prizeDistributionForm.value.forEach((p, i) => {
		p.place = i + 1;
	});
};

const resetPrizeDistribution = () => {
	if (!tournament.value) return;
	
	const participantsCount = tournament.value.participants?.length || 0;
	const itmPercentage = tournament.value.itm_percentage || 15;
	const itmPlacesFloat = participantsCount * (itmPercentage / 100);
	
	if (itmPlacesFloat < 0.5) {
		alert('Недостаточно участников для расчета призовых мест');
		return;
	}
	
	const itmPlaces = Math.max(1, Math.min(Math.round(itmPlacesFloat), participantsCount));
	
	prizeDistributionForm.value = [];
	for (let place = 1; place <= itmPlaces; place++) {
		let percentage = 0;
		if (itmPlaces === 1) {
			percentage = 100;
		} else if (itmPlaces === 2) {
			percentage = place === 1 ? 60 : 40;
		} else if (itmPlaces === 3) {
			percentage = place === 1 ? 60 : place === 2 ? 30 : 10;
		} else {
			if (place === 1) percentage = 50;
			else if (place === 2) percentage = 25;
			else if (place === 3) percentage = 12.5;
			else percentage = 12.5 / (itmPlaces - 3);
		}
		
		prizeDistributionForm.value.push({
			place: place,
			percentage: percentage,
		});
	}
};

const savePrizeDistribution = async () => {
	if (!isFixedPrizeDistribution.value) {
		await locationService.updateTournament(route.params.locationId, route.params.id, {
			prize_distribution: null,
		});
		await fetchTournament();
		showPrizeDistributionModal.value = false;
		return;
	}
	
	const totalPercentage = prizeDistributionForm.value.reduce((sum, p) => sum + (parseFloat(p.percentage) || 0), 0);
	if (Math.abs(totalPercentage - 100) > 0.01) {
		alert(`Сумма процентов должна быть равна 100%. Текущая сумма: ${totalPercentage.toFixed(2)}%`);
		return;
	}

	try {
		const distribution = prizeDistributionForm.value.map(p => ({
			place: p.place,
			percentage: parseFloat(p.percentage) || 0,
		}));

		await locationService.updateTournament(route.params.locationId, route.params.id, {
			prize_distribution: distribution,
		});

		await fetchTournament();
		showPrizeDistributionModal.value = false;
	} catch (error) {
		console.error('Error saving prize distribution:', error);
		alert('Ошибка при сохранении распределения призов');
	}
};

const clearPrizeDistribution = async () => {
	if (!confirm('Вы уверены, что хотите сбросить кастомное распределение призов? Будет использоваться автоматический расчет на основе ИТМ%.')) {
		return;
	}

	try {
		await locationService.updateTournament(route.params.locationId, route.params.id, {
			prize_distribution: null,
		});

		await fetchTournament();
		showPrizeDistributionModal.value = false;
	} catch (error) {
		console.error('Error clearing prize distribution:', error);
		alert('Ошибка при сбросе распределения призов');
	}
};

const openFinishModal = () => {
	if (!canFinishTournament.value) {
		const unpaidCount = tournament.value.participants.filter(p => {
			const name = p.display_name || p.name || p.user?.name || '';
			return name && name !== 'Без имени' && name !== 'Неизвестный участник' && !p.is_paid;
		}).length;
		alert(`Нельзя завершить турнир: ${unpaidCount} участник(ов) еще не оплатили вход`);
		return;
	}
	if (!tournament.value || !tournament.value.prize_distribution) return;
	selectedWinners.value = [];
	showFinishModal.value = true;
};

const hasFixedPrizeDistribution = computed(() => {
	if (!tournament.value) return false;
	const customDistribution = tournament.value.prize_distribution;
	return customDistribution && Array.isArray(customDistribution) && customDistribution.length > 0 && customDistribution[0].hasOwnProperty('percentage');
});

const dynamicPrizeDistribution = computed(() => {
	if (!tournament.value) return [];
	
	if (hasFixedPrizeDistribution.value) {
		return tournament.value.prize_distribution;
	}
	
	const prizePool = tournament.value.prize_pool || 0;
	if (prizePool <= 0) {
		return [];
	}
	
	const validParticipants = tournament.value.participants?.filter(p => {
		const name = p.display_name || p.name || p.user?.name || '';
		return name && name !== 'Без имени' && name !== 'Неизвестный участник';
	}) || [];
	
	const participantsCount = validParticipants.length;
	if (participantsCount === 0) {
		return [];
	}
	
	const itmPercentage = tournament.value.itm_percentage || 15;
	const itmPlacesFloat = participantsCount * (itmPercentage / 100);
	
	if (itmPlacesFloat < 0.5) {
		return [];
	}
	
	const itmPlaces = Math.max(1, Math.min(Math.round(itmPlacesFloat), participantsCount));
	
	if (itmPlaces === 0 || itmPlaces > participantsCount) {
		return [];
	}
	
	const prizes = [];
	let totalPercentage = 0;
	
	for (let place = 1; place <= itmPlaces; place++) {
		let percentage = 0;
		if (itmPlaces === 1) {
			percentage = 100;
		} else if (itmPlaces === 2) {
			percentage = place === 1 ? 60 : 40;
		} else if (itmPlaces === 3) {
			percentage = place === 1 ? 60 : place === 2 ? 30 : 10;
		} else {
			if (place === 1) percentage = 50;
			else if (place === 2) percentage = 25;
			else if (place === 3) percentage = 12.5;
			else percentage = 12.5 / (itmPlaces - 3);
		}
		
		totalPercentage += percentage;
		const prizeAmount = Math.round((prizePool * (percentage / 100)) / 5) * 5;
		
		prizes.push({
			place: place,
			percentage: percentage,
			prize: prizeAmount,
		});
	}
	
	if (totalPercentage < 100 && prizes.length > 0) {
		const diff = 100 - totalPercentage;
		prizes[0].percentage += diff;
		prizes[0].prize = Math.round((prizePool * (prizes[0].percentage / 100)) / 5) * 5;
	}
	
	return prizes;
});

const maxPrizePlaces = computed(() => {
	if (!dynamicPrizeDistribution.value || dynamicPrizeDistribution.value.length === 0) return 0;
	return dynamicPrizeDistribution.value.length;
});

const toggleWinner = (participantId) => {
	const index = selectedWinners.value.indexOf(participantId);
	if (index === -1) {
		if (selectedWinners.value.length < maxPrizePlaces.value) {
			selectedWinners.value.push(participantId);
		}
	} else {
		selectedWinners.value.splice(index, 1);
	}
};

const getWinnerPlace = (participantId) => {
	const index = selectedWinners.value.indexOf(participantId);
	if (index === -1) return null;
	return maxPrizePlaces.value - index;
};

const getWinnerPrize = (place) => {
	if (!dynamicPrizeDistribution.value || dynamicPrizeDistribution.value.length === 0) return 0;
	const prizeInfo = dynamicPrizeDistribution.value.find(p => p.place === place);
	return prizeInfo ? prizeInfo.prize : 0;
};

const closeFinishModal = () => {
	showFinishModal.value = false;
};

const finishTournament = async () => {
	if (!canFinishTournament.value) {
		const unpaidCount = tournament.value.participants.filter(p => {
			const name = p.display_name || p.name || p.user?.name || '';
			return name && name !== 'Без имени' && name !== 'Неизвестный участник' && !p.is_paid;
		}).length;
		alert(`Нельзя завершить турнир: ${unpaidCount} участник(ов) еще не оплатили вход`);
		return;
	}
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
			const place = maxPrizePlaces.value - index;
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
