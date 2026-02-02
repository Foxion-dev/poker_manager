<template>
	<div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full">
			<div class="p-6">
				<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Введите пароль</h3>
				<p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
					Эта локация защищена паролем. Введите пароль для доступа.
				</p>
				<form @submit.prevent="$emit('submit')" class="space-y-4">
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							Пароль *
						</label>
						<input
							:value="password"
							@input="$emit('update:password', $event.target.value)"
							type="password"
							required
							autofocus
							:class="error ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500'"
							class="w-full px-4 py-2 rounded-lg shadow-sm focus:ring-2 dark:bg-gray-700 dark:text-white transition duration-200"
						/>
						<div v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400">
							{{ error }}
						</div>
					</div>
					<div class="flex justify-end space-x-3 pt-4">
						<button
							type="button"
							@click="$emit('cancel')"
							class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
						>
							Отмена
						</button>
						<button
							type="submit"
							:disabled="checking"
							class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors"
						>
							{{ checking ? 'Проверка...' : 'Войти' }}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script setup>
defineProps({
	modelValue: Boolean,
	password: String,
	error: String,
	checking: Boolean,
});

defineEmits(['update:password', 'submit', 'cancel']);
</script>
