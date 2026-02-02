<template>
	<div
		v-if="modelValue"
		class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
		@click.self="$emit('close')"
	>
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
			<div class="p-6">
				<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Управление пользователями</h3>

				<div v-if="location?.users?.length" class="mb-6">
					<h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">Текущие пользователи:</h4>
					<div class="space-y-2">
						<div
							v-for="locationUser in location.users"
							:key="locationUser.id"
							class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-0 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
						>
							<div class="flex items-center min-w-0 flex-1">
								<div class="h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-xs sm:text-sm mr-2 sm:mr-3 flex-shrink-0">
									{{ (locationUser.display_name || locationUser.name)?.charAt(0).toUpperCase() }}
								</div>
								<span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate">{{ locationUser.display_name || locationUser.name }}</span>
								<div class="flex flex-wrap items-center gap-1 sm:gap-2 ml-2">
									<span v-if="locationUser.user_id === location.user_id" class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 whitespace-nowrap">
										Создатель
									</span>
									<span v-else-if="location.admins?.some(a => a.id === locationUser.user_id)" class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 whitespace-nowrap">
										Админ
									</span>
								</div>
							</div>
							<button
								v-if="locationUser.user_id !== location.user_id && !location.admins?.some(a => a.id === locationUser.user_id)"
								@click="$emit('remove-user', locationUser.id)"
								class="px-3 py-1 text-xs sm:text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors w-full sm:w-auto"
							>
								Удалить
							</button>
						</div>
					</div>
				</div>

				<div>
					<h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Добавить пользователя:</h4>
					<div class="space-y-3">
						<div class="flex items-center space-x-2">
							<select
								:value="newUserId"
								@input="$emit('update:newUserId', $event.target.value)"
								class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							>
								<option value="">Выберите пользователя системы (опционально)</option>
								<option v-for="user in availableUsersForLocation" :key="user.id" :value="user.id">
									{{ user.name }}
								</option>
							</select>
						</div>
						<div class="flex items-center space-x-2">
							<input
								:value="newUserName"
								@input="$emit('update:newUserName', $event.target.value)"
								type="text"
								placeholder="Или введите имя нового пользователя"
								class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							/>
							<button
								@click="$emit('add-user')"
								:disabled="(!newUserId && !newUserName) || adding"
								class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors"
							>
								{{ adding ? 'Добавление...' : 'Добавить' }}
							</button>
						</div>
					</div>
				</div>

				<div class="flex justify-end space-x-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
					<button
						@click="$emit('close')"
						class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
					>
						Закрыть
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
defineProps({
	modelValue: Boolean,
	location: { type: Object, default: null },
	availableUsersForLocation: { type: Array, default: () => [] },
	newUserId: String,
	newUserName: String,
	adding: Boolean,
});

defineEmits(['close', 'add-user', 'remove-user', 'update:newUserId', 'update:newUserName']);
</script>
