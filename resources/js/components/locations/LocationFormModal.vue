<template>
	<div
		v-if="modelValue"
		class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
		@click.self="$emit('close')"
	>
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
			<div class="p-6">
				<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Редактировать локацию</h3>

				<form @submit.prevent="$emit('save')" class="space-y-4">
					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							Название *
						</label>
						<input
							v-model="form.name"
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
							v-model="form.description"
							rows="3"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						></textarea>
					</div>

					<div>
						<label class="flex items-center space-x-2 mb-4">
							<input
								v-model="form.is_public"
								type="checkbox"
								class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
							/>
							<span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
								Публичная локация
							</span>
						</label>
					</div>

					<div v-if="form.is_public">
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							Пароль (обязательно для публичных локаций)
						</label>
						<input
							v-model="form.password"
							type="password"
							:required="form.is_public"
							class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							placeholder="Введите пароль"
						/>
						<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
							Оставьте пустым, чтобы не менять пароль при редактировании
						</p>
					</div>

					<div>
						<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
							Доступные валюты для турниров
						</label>
						<div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50 max-h-48 overflow-y-auto">
							<div class="space-y-2">
								<label
									v-for="currency in currencies"
									:key="currency.id"
									class="flex items-center space-x-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg cursor-pointer transition-colors"
								>
									<input
										type="checkbox"
										:value="currency.id"
										v-model="form.selected_currencies"
										class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
									/>
									<span class="text-sm font-medium text-gray-900 dark:text-white">
										{{ currency.symbol }} {{ currency.name }} ({{ currency.code }})
									</span>
								</label>
							</div>
						</div>
						<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
							Выберите валюты, которые будут доступны при создании турниров в этой локации
						</p>
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
							{{ saving ? 'Сохранение...' : 'Сохранить' }}
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
	form: { type: Object, required: true },
	currencies: { type: Array, default: () => [] },
	saving: Boolean,
});

defineEmits(['close', 'save']);
</script>
