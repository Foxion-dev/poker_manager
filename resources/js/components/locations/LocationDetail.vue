<template>
	<div>
		<div class="mb-6">
			<button
				@click="$router.push('/locations')"
				class="mb-4 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
			>
				<span class="mr-2">←</span>
				Назад к локациям
			</button>
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
				<div class="flex items-center justify-between">
					<div class="flex-1">
						<h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ location?.name }}</h2>
						<div v-if="location?.description" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
							{{ location.description }}
						</div>
						<div class="flex items-center space-x-3">
							<span
								class="px-2 py-1 text-xs font-semibold rounded-full"
								:class="location?.is_public 
									? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
									: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
							>
								{{ location?.is_public ? 'Публичная' : 'Личная' }}
							</span>
							<span class="text-sm text-gray-600 dark:text-gray-400">
								Создатель: {{ location?.user?.name }}
							</span>
						</div>
					</div>
					<div class="ml-6" v-if="location?.can_manage_admins || location?.is_admin">
						<button
							v-if="location?.can_manage_admins"
							@click="editLocation"
							class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors mr-2"
						>
							Редактировать
						</button>
						<button
							v-if="location?.can_manage_admins"
							@click="showAdminForm = true"
							class="px-4 py-2 text-sm font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors mr-2"
						>
							Управление админами
						</button>
						<button
							v-if="location?.is_admin"
							@click="openTournamentForm"
							class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-colors mr-2"
						>
							Создать турнир
						</button>
						<button
							v-if="location?.user_id === currentUser?.id"
							@click="deleteLocation"
							class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
						>
							Удалить
						</button>
					</div>
				</div>
			</div>
		</div>

		<div v-if="showPasswordForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full">
				<div class="p-6">
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
								@click="$router.push('/locations')"
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

		<div v-else-if="location" class="space-y-6">
			<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
					<div class="p-6">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
									Турниров
								</p>
								<p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
									{{ location.tournaments_count }}
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
									{{ formatCurrency(location.average_buyin) }}
								</p>
							</div>
							<div class="h-16 w-16 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-lg">
								<span class="text-3xl">💰</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div v-if="location.top_players_by_wins && location.top_players_by_wins.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
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

			<div v-if="location.top_players_by_prize && location.top_players_by_prize.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
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

			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
					<div class="flex items-center justify-between mb-6">
					<div class="flex items-center">
						<div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
							<span class="text-xl">🎮</span>
						</div>
						<h3 class="text-xl font-bold text-gray-900 dark:text-white">
							Турниры
						</h3>
					</div>
					<button
						v-if="location.is_admin"
						@click="openTournamentForm"
						class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-colors"
					>
						<span class="mr-2">➕</span>
						Создать турнир
					</button>
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
												: participant.place === 2 
												? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' 
												: participant.place === 3 
												? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' 
												: 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
										>
											{{ participant.place }}. {{ participant.user.name }}
											<span v-if="participant.prize" class="ml-1 font-semibold text-green-600">
												({{ formatCurrency(participant.prize) }})
											</span>
										</div>
									</div>
								</div>
							</div>
							<div v-if="location.is_admin" class="ml-4 flex space-x-2">
								<button
									@click="editTournament(tournament)"
									class="px-3 py-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
								>
									Редактировать
								</button>
								<button
									@click="deleteTournament(tournament.id)"
									class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
								>
									Удалить
								</button>
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

		<div
			v-if="showLocationForm"
			class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
			@click.self="closeLocationForm"
		>
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
				<div class="p-6">
					<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Редактировать локацию</h3>

					<form @submit.prevent="saveLocation" class="space-y-4">
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Название *
							</label>
							<input
								v-model="locationForm.name"
								type="text"
								required
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							/>
						</div>

						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Описание
							</label>
							<textarea
								v-model="locationForm.description"
								rows="3"
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							></textarea>
						</div>

						<div>
							<label class="flex items-center space-x-2 mb-4">
								<input
									v-model="locationForm.is_public"
									type="checkbox"
									class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
								/>
								<span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
									Публичная локация
								</span>
							</label>
						</div>

						<div v-if="locationForm.is_public">
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Пароль (обязательно для публичных локаций)
							</label>
							<input
								v-model="locationForm.password"
								type="password"
								:required="locationForm.is_public"
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								placeholder="Введите пароль"
							/>
							<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
								Оставьте пустым, чтобы не менять пароль при редактировании
							</p>
						</div>

						<div class="flex justify-end space-x-3 pt-4">
							<button
								type="button"
								@click="closeLocationForm"
								class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
							>
								Отмена
							</button>
							<button
								type="submit"
								:disabled="savingLocation"
								class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors"
							>
								{{ savingLocation ? 'Сохранение...' : 'Сохранить' }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div
			v-if="showTournamentForm"
			class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
			@click.self="closeTournamentForm"
		>
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
				<div class="p-6">
					<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
						{{ editingTournament ? 'Редактировать турнир' : 'Создать турнир' }}
					</h3>

					<form @submit.prevent="saveTournament" class="space-y-4">
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
									Название *
								</label>
								<input
									v-model="tournamentForm.name"
									type="text"
									required
									class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								/>
							</div>
							<div>
								<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
									Дата *
								</label>
								<input
									v-model="tournamentForm.date"
									type="date"
									required
									class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								/>
							</div>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
									Байин *
								</label>
								<input
									v-model.number="tournamentForm.buyin"
									type="number"
									step="0.01"
									min="0"
									required
									class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
									placeholder="0.00"
								/>
							</div>
							<div>
								<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
									Формат *
								</label>
								<select
									v-model="tournamentForm.format"
									required
									class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								>
									<option value="classic">Классик</option>
									<option value="classic_bounty">Классик баунти</option>
									<option value="progressive_bounty">Прогрессив баунти</option>
								</select>
							</div>
						</div>

						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Участники *
							</label>
							<div class="space-y-2">
								<div
									v-for="(participant, index) in tournamentForm.participants"
									:key="index"
									class="flex items-center space-x-2"
								>
									<select
										v-model="participant.user_id"
										required
										class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
									>
										<option value="">Выберите игрока</option>
										<option v-for="user in allUsers" :key="user.id" :value="user.id">
											{{ user.name }}
										</option>
									</select>
									<input
										v-model.number="participant.place"
										type="number"
										min="1"
										required
										placeholder="Место"
										class="w-24 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
									/>
									<input
										v-model.number="participant.prize"
										type="number"
										step="0.01"
										min="0"
										placeholder="Приз"
										class="w-32 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
									/>
									<button
										type="button"
										@click="removeParticipant(index)"
										class="px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
									>
										Удалить
									</button>
								</div>
								<button
									type="button"
									@click="addParticipant"
									class="w-full px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
								>
									➕ Добавить участника
								</button>
							</div>
						</div>

						<div class="flex justify-end space-x-3 pt-4">
							<button
								type="button"
								@click="closeTournamentForm"
								class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
							>
								Отмена
							</button>
							<button
								type="submit"
								:disabled="savingTournament"
								class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors"
							>
								{{ savingTournament ? 'Сохранение...' : (editingTournament ? 'Сохранить' : 'Создать') }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div
			v-if="showAdminForm"
			class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
			@click.self="showAdminForm = false"
		>
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
				<div class="p-6">
					<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Управление админами</h3>

					<div v-if="location?.admins && location.admins.length > 0" class="mb-6">
						<h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Текущие админы:</h4>
						<div class="space-y-2">
							<div
								v-for="admin in location.admins"
								:key="admin.id"
								class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
							>
								<div class="flex items-center">
									<div class="h-8 w-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm mr-3">
										{{ admin.name?.charAt(0).toUpperCase() }}
									</div>
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ admin.name }}</span>
									<span v-if="admin.id === location.user_id" class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
										Создатель
									</span>
								</div>
								<button
									v-if="admin.id !== location.user_id"
									@click="removeAdmin(admin.id)"
									class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
								>
									Удалить
								</button>
							</div>
						</div>
					</div>

					<div>
						<h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Добавить админа:</h4>
						<div class="flex items-center space-x-2">
							<select
								v-model="newAdminUserId"
								class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							>
								<option value="">Выберите пользователя</option>
								<option v-for="user in availableUsers" :key="user.id" :value="user.id">
									{{ user.name }}
								</option>
							</select>
							<button
								@click="addAdmin"
								:disabled="!newAdminUserId || addingAdmin"
								class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors"
							>
								{{ addingAdmin ? 'Добавление...' : 'Добавить' }}
							</button>
						</div>
					</div>

					<div class="flex justify-end space-x-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
						<button
							@click="showAdminForm = false"
							class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
						>
							Закрыть
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { locationService } from '../../services/locationService';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { user: currentUser } = storeToRefs(authStore);

const location = ref(null);
const tournaments = ref([]);
const allUsers = ref([]);
const loading = ref(false);
const showLocationForm = ref(false);
const showTournamentForm = ref(false);
const showAdminForm = ref(false);
const showPasswordForm = ref(false);
const locationPassword = ref('');
const checkingPassword = ref(false);
const editingTournament = ref(null);
const savingLocation = ref(false);
const savingTournament = ref(false);
const addingAdmin = ref(false);
const newAdminUserId = ref('');

const locationForm = ref({
	name: '',
	description: '',
	is_public: false,
	password: '',
});

const tournamentForm = ref({
	name: '',
	date: '',
	buyin: 0,
	format: 'classic',
	participants: [{ user_id: '', place: 1, prize: null }],
});

const fetchLocation = async (password = null) => {
	loading.value = true;
	try {
		const params = password ? { password } : {};
		location.value = await locationService.getById(route.params.id, params);
		locationForm.value = {
			name: location.value.name,
			description: location.value.description || '',
			is_public: location.value.is_public,
			password: '',
		};
		await fetchTournaments();
		await fetchUsers();
	} catch (error) {
		if (error.response?.status === 403 && error.response?.data?.requires_password) {
			showPasswordForm.value = true;
		} else {
			console.error('Error fetching location:', error);
			alert('Ошибка при загрузке локации');
		}
	} finally {
		loading.value = false;
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

const fetchTournaments = async () => {
	try {
		tournaments.value = await locationService.getTournaments(route.params.id);
	} catch (error) {
		console.error('Error fetching tournaments:', error);
	}
};

const fetchUsers = async () => {
	try {
		const response = await api.get('/users/list');
		allUsers.value = response.data || [];
	} catch (error) {
		console.error('Error fetching users:', error);
	}
};

const editLocation = () => {
	showLocationForm.value = true;
};

const closeLocationForm = () => {
	showLocationForm.value = false;
};

const saveLocation = async () => {
	savingLocation.value = true;
	try {
		const data = { ...locationForm.value };
		if (!data.password) {
			delete data.password;
		}
		await locationService.update(route.params.id, data);
		closeLocationForm();
		await fetchLocation();
	} catch (error) {
		console.error('Error saving location:', error);
		alert('Ошибка при сохранении локации');
	} finally {
		savingLocation.value = false;
	}
};

const deleteLocation = async () => {
	if (!confirm('Вы уверены, что хотите удалить эту локацию? Все турниры в ней также будут удалены.')) {
		return;
	}

	try {
		await locationService.delete(route.params.id);
		router.push('/locations');
	} catch (error) {
		console.error('Error deleting location:', error);
		alert('Ошибка при удалении локации');
	}
};

const openTournamentForm = () => {
	editingTournament.value = null;
	tournamentForm.value = {
		name: '',
		date: '',
		buyin: 0,
		format: 'classic',
		participants: [{ user_id: '', place: 1, prize: null }],
	};
	showTournamentForm.value = true;
};

const editTournament = (tournament) => {
	editingTournament.value = tournament;
	tournamentForm.value = {
		name: tournament.name,
		date: tournament.date,
		buyin: tournament.buyin,
		format: tournament.format,
		participants: tournament.participants.map(p => ({
			user_id: p.user.id,
			place: p.place,
			prize: p.prize || null,
		})),
	};
	showTournamentForm.value = true;
};

const closeTournamentForm = () => {
	showTournamentForm.value = false;
	editingTournament.value = null;
};

const addParticipant = () => {
	const maxPlace = tournamentForm.value.participants.length > 0
		? Math.max(...tournamentForm.value.participants.map(p => p.place || 0))
		: 0;
	tournamentForm.value.participants.push({ user_id: '', place: maxPlace + 1, prize: null });
};

const removeParticipant = (index) => {
	tournamentForm.value.participants.splice(index, 1);
};

const updatePlaceNumbers = () => {
	tournamentForm.value.participants.forEach((p, i) => {
		if (!p.place || p.place < 1) {
			p.place = i + 1;
		}
	});
};

watch(() => tournamentForm.value.participants.length, () => {
	updatePlaceNumbers();
});

const saveTournament = async () => {
	const uniqueUserIds = new Set(tournamentForm.value.participants.map(p => p.user_id));
	const uniquePlaces = new Set(tournamentForm.value.participants.map(p => p.place));

	if (uniqueUserIds.size !== tournamentForm.value.participants.length) {
		alert('Каждый участник может быть добавлен только один раз.');
		return;
	}

	if (uniquePlaces.size !== tournamentForm.value.participants.length) {
		alert('Места участников должны быть уникальными.');
		return;
	}

	if (tournamentForm.value.participants.some(p => !p.user_id)) {
		alert('Все участники должны быть выбраны.');
		return;
	}

	savingTournament.value = true;
	try {
		if (editingTournament.value) {
			await locationService.updateTournament(route.params.id, editingTournament.value.id, tournamentForm.value);
		} else {
			await locationService.createTournament(route.params.id, tournamentForm.value);
		}
		closeTournamentForm();
		await fetchLocation();
	} catch (error) {
		console.error('Error saving tournament:', error);
		const errorMessage = error.response?.data?.message || error.response?.data?.errors?.participants?.[0] || 'Ошибка при сохранении турнира';
		alert(errorMessage);
	} finally {
		savingTournament.value = false;
	}
};

const deleteTournament = async (id) => {
	if (!confirm('Вы уверены, что хотите удалить этот турнир?')) {
		return;
	}

	try {
		await locationService.deleteTournament(route.params.id, id);
		await fetchLocation();
	} catch (error) {
		console.error('Error deleting tournament:', error);
		alert('Ошибка при удалении турнира');
	}
};

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

const availableUsers = computed(() => {
	if (!location.value || !allUsers.value.length) return [];
	const adminIds = [location.value.user_id, ...(location.value.admins?.map(a => a.id) || [])];
	return allUsers.value.filter(user => !adminIds.includes(user.id));
});

const addAdmin = async () => {
	if (!newAdminUserId.value) return;

	addingAdmin.value = true;
	try {
		await locationService.addAdmin(route.params.id, { user_id: newAdminUserId.value });
		newAdminUserId.value = '';
		await fetchLocation();
	} catch (error) {
		console.error('Error adding admin:', error);
		alert(error.response?.data?.message || 'Ошибка при добавлении админа');
	} finally {
		addingAdmin.value = false;
	}
};

const removeAdmin = async (adminId) => {
	if (!confirm('Вы уверены, что хотите удалить этого админа?')) {
		return;
	}

	try {
		await locationService.removeAdmin(route.params.id, adminId);
		await fetchLocation();
	} catch (error) {
		console.error('Error removing admin:', error);
		alert(error.response?.data?.message || 'Ошибка при удалении админа');
	}
};

onMounted(() => {
	fetchLocation();
});
</script>
