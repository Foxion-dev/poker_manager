<template>
	<div class="mb-6">
		<button
			@click="$router.push('/locations')"
			class="mb-4 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
		>
			<span class="mr-2">←</span>
			Назад к локациям
		</button>
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col gap-4">
				<div class="flex items-center justify-between gap-4">
					<h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white truncate min-w-0">{{ location?.name }}</h2>
					<div v-if="location?.can_manage_admins || location?.is_admin" class="flex items-center gap-2 flex-shrink-0">
						<div class="hidden sm:flex flex-wrap gap-2">
							<button
								v-if="location?.is_public"
								@click="$emit('copy-link')"
								class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
								:title="copied ? 'Скопировано!' : 'Копировать публичную ссылку'"
							>
								<svg v-if="!copied" class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
								</svg>
								<svg v-else class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
								</svg>
								{{ copied ? 'Скопировано!' : 'Копировать' }}
							</button>
							<button
								v-if="location?.can_manage_admins"
								@click="$emit('edit')"
								class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
							>
								Редактировать
							</button>
							<button
								v-if="location?.can_manage_admins"
								@click="$emit('show-admin-form')"
								class="px-4 py-2 text-sm font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors"
							>
								Управление админами
							</button>
							<button
								v-if="location?.can_manage_admins"
								@click="$emit('show-users-form')"
								class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
							>
								Управление пользователями
							</button>
							<button
								v-if="location?.user_id === currentUser?.id"
								@click="$emit('delete')"
								class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
							>
								Удалить
							</button>
						</div>
						<details ref="menuRef" class="relative sm:hidden">
							<summary class="list-none cursor-pointer px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-1">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
									<path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
								</svg>
								Меню
							</summary>
							<div class="absolute right-0 top-full mt-1 py-1 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg z-20">
								<button
									v-if="location?.is_public"
									@click="$emit('copy-link'); menuRef.open = false"
									class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
								>
									<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
									</svg>
									{{ copied ? 'Скопировано!' : 'Копировать ссылку' }}
								</button>
								<button
									v-if="location?.can_manage_admins"
									@click="$emit('edit'); menuRef.open = false"
									class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
								>
									Редактировать
								</button>
								<button
									v-if="location?.can_manage_admins"
									@click="$emit('show-admin-form'); menuRef.open = false"
									class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
								>
									Управление админами
								</button>
								<button
									v-if="location?.can_manage_admins"
									@click="$emit('show-users-form'); menuRef.open = false"
									class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
								>
									Управление пользователями
								</button>
								<button
									v-if="location?.user_id === currentUser?.id"
									@click="$emit('delete'); menuRef.open = false"
									class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
								>
									Удалить
								</button>
							</div>
						</details>
					</div>
				</div>
				<div class="flex flex-col gap-2">
					<div v-if="location?.description" class="text-sm text-gray-600 dark:text-gray-400">
						{{ location.description }}
					</div>
					<div class="flex items-center space-x-3 flex-wrap gap-y-2">
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
						<button
							v-if="location?.is_public && !(location?.can_manage_admins || location?.is_admin)"
							@click="$emit('copy-link')"
							class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
							:title="copied ? 'Скопировано!' : 'Копировать публичную ссылку'"
						>
							<svg v-if="!copied" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
							</svg>
							<svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
							</svg>
							{{ copied ? 'Скопировано!' : 'Копировать ссылку' }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
	location: { type: Object, default: null },
	currentUser: { type: Object, default: null },
	copied: { type: Boolean, default: false },
});

defineEmits(['edit', 'show-admin-form', 'show-users-form', 'delete', 'copy-link']);

const menuRef = ref(null);
</script>
