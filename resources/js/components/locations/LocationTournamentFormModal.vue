<template>
	<div
		v-if="modelValue"
		class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
		@click.self="$emit('close')"
	>
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
			<div class="p-6">
				<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
					{{ editingTournament ? 'Редактировать турнир' : 'Создать турнир' }}
				</h3>

				<form @submit.prevent="$emit('save')" class="space-y-4">
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Название
							</label>
							<input
								v-model="form.name"
								type="text"
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								placeholder="Необязательно, подставится тип турнира"
							/>
						</div>
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								<span class="mr-2">📅</span>
								Дата *
							</label>
							<AppDatePicker
								:model-value="dateValue"
								@update:model-value="$emit('update:date', $event)"
								:range="false"
								placeholder="Выберите дату"
								:clearable="false"
							/>
						</div>
					</div>

					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Байин *
							</label>
							<div class="flex items-center space-x-2">
								<input
									v-model.number="form.buyin"
									type="number"
									step="0.01"
									min="0"
									required
									class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
									placeholder="0.00"
								/>
								<select
									v-if="currencies.length > 1"
									v-model="form.currency_id"
									required
									class="w-40 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								>
									<option value="">Выберите валюту</option>
									<option v-for="currency in currencies" :key="currency.id" :value="currency.id">
										{{ currency.symbol }} {{ currency.code }}
									</option>
								</select>
								<span v-else-if="currencies.length === 1" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg whitespace-nowrap">
									{{ currencies[0].symbol }} {{ currencies[0].code }}
								</span>
							</div>
						</div>
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Формат *
							</label>
							<select
								v-model="form.format"
								required
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							>
								<option value="classic">Классик</option>
								<option value="classic_bounty">Классик баунти</option>
								<option value="progressive_bounty">Прогрессив баунти</option>
							</select>
						</div>
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Процент ИТМ (%)
							</label>
							<input
								v-model.number="form.itm_percentage"
								type="number"
								step="0.01"
								min="0"
								max="100"
								placeholder="15"
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							/>
							<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
								Процент от входов + ребаев, который идет в призовой фонд
							</p>
						</div>
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Тип рейка
							</label>
							<select
								v-model="form.rake_type"
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200 mb-2"
							>
								<option value="fixed">Фиксированный</option>
								<option value="percentage">Процент</option>
							</select>
						</div>
						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Рейк {{ form.rake_type === 'percentage' ? '(%)' : '(фиксированная сумма)' }}
							</label>
							<input
								v-model.number="form.rake"
								type="number"
								step="0.01"
								min="0"
								:max="form.rake_type === 'percentage' ? 100 : null"
								placeholder="30"
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							/>
							<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
								<span v-if="form.rake_type === 'percentage'">
									Процент рейка от призового фонда (отнимается пропорционально)
								</span>
								<span v-else>
									Фиксированная сумма рейка (отнимается от призового фонда)
								</span>
							</p>
						</div>
					</div>

					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							Участники *
						</label>
						<div class="mb-4">
							<div class="relative mb-2">
								<input
									:value="searchQuery"
									@input="$emit('update:searchQuery', $event.target.value)"
									type="text"
									placeholder="Поиск участников..."
									class="w-full pl-4 pr-10 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								/>
								<button
									v-show="searchQuery"
									type="button"
									@click="$emit('update:searchQuery', '')"
									class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-full text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
									aria-label="Сбросить поиск"
								>
									<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
										<path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
									</svg>
								</button>
							</div>
							<div class="border border-gray-300 dark:border-gray-600 rounded-lg p-3 sm:p-4 bg-gray-50 dark:bg-gray-700/50 max-h-64 overflow-y-auto">
								<div class="space-y-2">
									<button
										v-for="locationUser in filteredUsers"
										:key="locationUser.id || locationUser.user_id"
										type="button"
										@click="$emit('add-participant', locationUser)"
										class="w-full flex items-center space-x-2 sm:space-x-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg cursor-pointer transition-colors text-left"
									>
										<div class="h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-xs sm:text-sm flex-shrink-0">
											{{ (locationUser.display_name || locationUser.name)?.charAt(0).toUpperCase() }}
										</div>
										<span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate">
											{{ locationUser.display_name || locationUser.name }}
										</span>
									</button>
								</div>
							</div>
						</div>

						<div class="mb-4 p-3 sm:p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
							<label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Добавить нового пользователя в локацию и турнир
							</label>
							<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
								<input
									:value="newParticipantName"
									@input="$emit('update:newParticipantName', $event.target.value)"
									type="text"
									placeholder="Введите имя нового пользователя"
									class="flex-1 px-3 sm:px-4 py-2 text-xs sm:text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
									@keyup.enter="$emit('add-new-user')"
								/>
								<button
									type="button"
									@click="$emit('add-new-user')"
									:disabled="!newParticipantName || addingNewUser"
									class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 transition-colors w-full sm:w-auto"
								>
									{{ addingNewUser ? 'Добавление...' : 'Добавить' }}
								</button>
							</div>
						</div>

						<div v-if="validParticipantsCount > 0" class="space-y-2">
							<label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Выбранные участники ({{ validParticipantsCount }})
							</label>
							<div
								v-for="(participant, index) in validParticipants"
								:key="index"
								class="flex items-center justify-between gap-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
							>
								<span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate min-w-0">
									{{ getParticipantDisplayName(participant) }}
								</span>
								<button
									type="button"
									@click="$emit('remove-participant', participant)"
									class="flex-shrink-0 p-1.5 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-lg transition-colors"
									title="Удалить"
								>
									<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
									</svg>
								</button>
							</div>
						</div>
					</div>

					<div class="flex justify-end space-x-3 pt-4">
						<button
							type="button"
							@click="$emit('close')"
							class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
						>
							Отмена
						</button>
						<button
							type="submit"
							:disabled="saving"
							class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors"
						>
							{{ saving ? 'Сохранение...' : (editingTournament ? 'Сохранить' : 'Создать') }}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue';
import AppDatePicker from '../AppDatePicker.vue';

const props = defineProps({
	modelValue: Boolean,
	form: { type: Object, required: true },
	dateValue: [Date, String],
	editingTournament: { type: Object, default: null },
	currencies: { type: Array, default: () => [] },
	searchQuery: String,
	filteredUsers: { type: Array, default: () => [] },
	newParticipantName: String,
	addingNewUser: Boolean,
	saving: Boolean,
	locationUsers: { type: Array, default: () => [] },
});

defineEmits([
	'close', 'save', 'update:date', 'update:searchQuery', 'update:newParticipantName',
	'add-participant', 'add-new-user', 'remove-participant',
]);

const validParticipants = computed(() => {
	return (props.form.participants || []).filter(p => {
		const name = getParticipantDisplayName(p);
		return name && name !== 'Без имени';
	});
});

const validParticipantsCount = computed(() => validParticipants.value.length);

function getParticipantDisplayName(participant) {
	if (participant.user_id) {
		const locationUser = props.locationUsers.find(u => u.user_id == participant.user_id);
		return locationUser ? (locationUser.display_name || locationUser.name) : 'Неизвестный пользователь';
	}
	const name = participant.name || participant.user?.name || '';
	return name && name !== 'Без имени' ? name : '';
}

</script>
