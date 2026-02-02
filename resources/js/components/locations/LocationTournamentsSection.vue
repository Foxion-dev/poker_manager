<template>
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
				v-if="location?.is_admin"
				@click="$emit('create-tournament')"
				class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-colors"
			>
				<span class="mr-2">➕</span>
				Создать турнир
			</button>
		</div>
		<div v-if="tournaments.length > 0" class="space-y-4">
			<div
				v-for="tournament in activeTournaments"
				:key="tournament.id"
				class="relative bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
			>
				<router-link
					:to="`/locations/${locationId}/tournaments/${tournament.id}`"
					class="block"
				>
					<div class="flex items-start justify-between">
						<div class="flex-1">
							<h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ tournament.name }}</h4>
							<div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
								<span>📅 {{ formatDate(tournament.date) }}</span>
								<span>💵 {{ formatBuyin(tournament) }}</span>
								<span>🎯 {{ tournament.format_label }}</span>
							</div>
							<div v-if="tournament.participants && tournament.participants.filter(p => p.name && p.name !== 'Без имени' && p.display_name && p.display_name !== 'Без имени').length > 0" class="mt-3">
								<p class="text-xs font-medium text-gray-600 dark:text-gray-400">
									👥 Участников: {{ tournament.participants.filter(p => p.name && p.name !== 'Без имени' && p.display_name && p.display_name !== 'Без имени').length }}
								</p>
							</div>
						</div>
					</div>
				</router-link>
				<div v-if="location?.is_admin" class="absolute top-4 right-4 flex items-center gap-2 z-10" @click.stop>
					<div class="hidden sm:flex space-x-2">
						<button
							@click.stop="$emit('edit-tournament', tournament)"
							class="px-3 py-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
						>
							Редактировать
						</button>
						<button
							@click.stop="$emit('delete-tournament', tournament.id)"
							class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
						>
							Удалить
						</button>
					</div>
					<details class="relative sm:hidden" @click.stop>
						<summary class="list-none cursor-pointer p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
								<path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
							</svg>
						</summary>
						<div class="absolute right-0 top-full mt-1 py-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg z-20">
							<button
								@click.stop="$emit('edit-tournament', tournament)"
								class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
							>
								Редактировать
							</button>
							<button
								@click.stop="$emit('delete-tournament', tournament.id)"
								class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700"
							>
								Удалить
							</button>
						</div>
					</details>
				</div>
			</div>
			<details v-if="finishedTournaments.length > 0" class="group">
				<summary class="flex items-center justify-between cursor-pointer list-none p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors select-none">
					<span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
						Завершённые турниры ({{ finishedTournaments.length }})
					</span>
					<svg class="w-5 h-5 text-gray-500 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
					</svg>
				</summary>
				<div class="mt-2 space-y-4">
					<div
						v-for="tournament in finishedTournaments"
						:key="tournament.id"
						class="relative bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
					>
						<router-link
							:to="`/locations/${locationId}/tournaments/${tournament.id}`"
							class="block"
						>
							<div class="flex items-start justify-between">
								<div class="flex-1">
									<h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ tournament.name }}</h4>
									<div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
										<span>📅 {{ formatDate(tournament.date) }}</span>
										<span>💵 {{ formatBuyin(tournament) }}</span>
										<span>🎯 {{ tournament.format_label }}</span>
										<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Завершён</span>
									</div>
									<div v-if="tournament.participants && tournament.participants.filter(p => p.name && p.name !== 'Без имени' && p.display_name && p.display_name !== 'Без имени').length > 0" class="mt-3">
										<p class="text-xs font-medium text-gray-600 dark:text-gray-400">
											👥 Участников: {{ tournament.participants.filter(p => p.name && p.name !== 'Без имени' && p.display_name && p.display_name !== 'Без имени').length }}
										</p>
									</div>
								</div>
							</div>
						</router-link>
						<div v-if="location?.is_admin" class="absolute top-4 right-4 flex items-center gap-2 z-10" @click.stop>
							<div class="hidden sm:flex space-x-2">
								<button
									@click.stop="$emit('edit-tournament', tournament)"
									class="px-3 py-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
								>
									Редактировать
								</button>
								<button
									@click.stop="$emit('delete-tournament', tournament.id)"
									class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
								>
									Удалить
								</button>
							</div>
							<details class="relative sm:hidden" @click.stop>
								<summary class="list-none cursor-pointer p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
									<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
										<path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
									</svg>
								</summary>
								<div class="absolute right-0 top-full mt-1 py-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg z-20">
									<button
										@click.stop="$emit('edit-tournament', tournament)"
										class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
									>
										Редактировать
									</button>
									<button
										@click.stop="$emit('delete-tournament', tournament.id)"
										class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700"
									>
										Удалить
									</button>
								</div>
							</details>
						</div>
					</div>
				</div>
			</details>
		</div>
		<div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
			<span class="text-4xl mb-4 block">📭</span>
			<p>Нет турниров в этой локации</p>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue';
import { formatDate, formatBuyin } from '../../utils/formatLocation';

const props = defineProps({
	location: { type: Object, default: null },
	tournaments: { type: Array, default: () => [] },
	locationId: { type: [String, Number], required: true },
});

defineEmits(['create-tournament', 'edit-tournament', 'delete-tournament']);

const activeTournaments = computed(() =>
	(props.tournaments || []).filter((t) => !t.is_finished)
);

const finishedTournaments = computed(() =>
	(props.tournaments || []).filter((t) => t.is_finished)
);
</script>
