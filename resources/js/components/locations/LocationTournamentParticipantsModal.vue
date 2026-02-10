<template>
	<div
		v-if="modelValue"
		class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
		@click.self="$emit('close')"
	>
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col">
			<div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
				<h3 class="text-xl font-bold text-gray-900 dark:text-white">Участники турнира</h3>
			</div>
			<div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
				<div>
					<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						Добавить из локации
					</label>
					<div class="relative mb-2">
						<input
							v-model="searchQuery"
							type="text"
							placeholder="Поиск..."
							class="w-full pl-4 pr-10 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						/>
						<button
							v-show="searchQuery"
							type="button"
							@click="searchQuery = ''"
							class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-full text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
							aria-label="Сбросить поиск"
						>
							<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
								<path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
							</svg>
						</button>
					</div>
					<div class="border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-gray-50 dark:bg-gray-700/50 max-h-48 overflow-y-auto">
						<button
							v-for="locationUser in filteredAvailableUsers"
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
						<div v-if="filteredAvailableUsers.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
							Нет доступных участников
						</div>
					</div>
				</div>

				<div class="p-3 sm:p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
					<label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						Добавить нового участника
					</label>
					<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
						<input
							:value="newParticipantName"
							@input="$emit('update:newParticipantName', $event.target.value)"
							type="text"
							placeholder="Введите имя"
							class="flex-1 px-3 sm:px-4 py-2 text-xs sm:text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							@keyup.enter="$emit('add-new-user')"
						/>
						<button
							type="button"
							@click="$emit('add-new-user')"
							:disabled="!newParticipantName?.trim() || adding"
							class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 transition-colors w-full sm:w-auto"
						>
							{{ adding ? 'Добавление...' : 'Добавить' }}
						</button>
					</div>
				</div>

				<div v-if="validParticipants.length > 0">
					<label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						В турнире ({{ validParticipants.length }})
					</label>
					<div class="space-y-2">
						<div
							v-for="participant in validParticipants"
							:key="participant.id"
							class="flex items-center justify-between gap-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
						>
							<span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate min-w-0">
								{{ participant.display_name || participant.name || participant.user?.name || 'Неизвестный' }}
							</span>
							<button
								type="button"
								@click.stop.prevent="$emit('remove-participant', participant)"
								:disabled="removing"
								class="flex-shrink-0 p-1.5 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-lg transition-colors disabled:opacity-50"
								title="Удалить"
							>
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
								</svg>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700">
				<button
					type="button"
					@click="$emit('close')"
					class="w-full px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
				>
					Закрыть
				</button>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
	modelValue: Boolean,
	participants: { type: Array, default: () => [] },
	availableUsers: { type: Array, default: () => [] },
	newParticipantName: String,
	adding: Boolean,
	removing: Boolean,
});

defineEmits(['close', 'add-participant', 'add-new-user', 'remove-participant', 'update:newParticipantName']);

const searchQuery = ref('');

const validParticipants = computed(() => {
	return (props.participants || []).filter(p => {
		const name = p.display_name || p.name || p.user?.name || '';
		return name && name !== 'Без имени' && name !== 'Неизвестный участник';
	});
});

const filteredAvailableUsers = computed(() => {
	const query = (searchQuery.value || '').trim().toLowerCase();
	let users = props.availableUsers || [];
	if (!query) return users;
	return users.filter(u => {
		const name = (u.display_name || u.name || '').toLowerCase();
		return name.includes(query);
	});
});

watch(() => props.modelValue, (visible) => {
	if (visible) {
		searchQuery.value = '';
	}
});
</script>
