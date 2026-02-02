<template>
	<div
		v-if="modelValue"
		class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
		@click.self="$emit('close')"
	>
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
			<div class="p-6">
				<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Управление админами</h3>

				<div v-if="location?.admins?.length" class="mb-6">
					<h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">Текущие админы:</h4>
					<div class="space-y-2">
						<div
							v-for="admin in location.admins"
							:key="admin.id"
							class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-0 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
						>
							<div class="flex items-center min-w-0 flex-1">
								<div class="h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-xs sm:text-sm mr-2 sm:mr-3 flex-shrink-0">
									{{ admin.name?.charAt(0).toUpperCase() }}
								</div>
								<span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate">{{ admin.name }}</span>
								<span v-if="admin.id === location.user_id" class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 whitespace-nowrap">
									Создатель
								</span>
							</div>
							<button
								v-if="admin.id !== location.user_id"
								@click="$emit('remove-admin', admin.id)"
								class="px-3 py-1 text-xs sm:text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors w-full sm:w-auto"
							>
								Удалить
							</button>
						</div>
					</div>
				</div>

				<div>
					<h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">Добавить админа:</h4>
					<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
						<select
							:value="newAdminUserId"
							@input="$emit('update:newAdminUserId', $event.target.value)"
							class="flex-1 px-3 sm:px-4 py-2 text-xs sm:text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						>
							<option value="">Выберите пользователя</option>
							<option v-for="user in availableUsers" :key="user.id" :value="user.id">
								{{ user.name }}
							</option>
						</select>
						<button
							@click="$emit('add-admin')"
							:disabled="!newAdminUserId || adding"
							class="px-4 sm:px-6 py-2 text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors w-full sm:w-auto"
						>
							{{ adding ? 'Добавление...' : 'Добавить' }}
						</button>
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
	availableUsers: { type: Array, default: () => [] },
	newAdminUserId: String,
	adding: Boolean,
});

defineEmits(['close', 'add-admin', 'remove-admin', 'update:newAdminUserId']);
</script>
