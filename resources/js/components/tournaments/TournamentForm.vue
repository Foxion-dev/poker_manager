<template>
	<div>
		<div class="mb-6">
			<div class="flex items-center space-x-3 mb-2">
				<div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
					<span class="text-xl">{{ isEdit ? '✏️' : '➕' }}</span>
				</div>
				<h2 class="text-3xl font-bold text-gray-900 dark:text-white">
					{{ isEdit ? 'Редактировать турнир' : 'Создать турнир' }}
				</h2>
			</div>
			<p class="text-sm text-gray-600 dark:text-gray-400 ml-13">
				{{ isEdit ? 'Обновите информацию о турнире' : 'Добавьте новый турнир для отслеживания' }}
			</p>
		</div>

		<form @submit.prevent="handleSubmit" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 border border-gray-100 dark:border-gray-700">
			<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
				<div>
					<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						<span class="mr-2">🎰</span>
						Рум
					</label>
					<select
						v-model="form.room_id"
						required
						class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
					>
						<option value="">Выберите рум</option>
						<option v-for="room in roomStore.rooms" :key="room.id" :value="room.id">
							{{ room.icon }} {{ room.name }}
						</option>
					</select>
				</div>

				<div>
					<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						<span class="mr-2">📅</span>
						Дата
					</label>
					<input
						v-model="form.date"
						type="date"
						required
						class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
					/>
				</div>

				<div>
					<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						<span class="mr-2">💵</span>
						Байин ($)
					</label>
					<input
						v-model.number="form.buyin"
						type="number"
						step="0.01"
						min="0"
						required
						class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						placeholder="0.00"
					/>
				</div>

				<div>
					<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						<span class="mr-2">🎁</span>
						Количество баунти
					</label>
					<input
						v-model.number="form.bounty_count"
						type="number"
						min="0"
						class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						placeholder="0"
					/>
				</div>

				<div>
					<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						<span class="mr-2">🏆</span>
						Место
					</label>
					<input
						v-model.number="form.place"
						type="number"
						min="1"
						class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						placeholder="Не указано"
					/>
				</div>

				<div>
					<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
						<span class="mr-2">💰</span>
						Кэшаут ($)
					</label>
					<input
						v-model.number="form.cashout"
						type="number"
						step="0.01"
						min="0"
						class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
						placeholder="0.00 (оставьте пустым, если не в деньгах)"
					/>
				</div>
			</div>

			<div v-if="error" class="mt-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
				{{ error }}
			</div>

			<div class="mt-8 flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
				<router-link
					to="/tournaments"
					class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
				>
					Отмена
				</router-link>
				<button
					type="submit"
					:disabled="loading"
					class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
				>
					<span v-if="loading" class="mr-2">
						<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
					</span>
					{{ loading ? 'Сохранение...' : 'Сохранить' }}
				</button>
			</div>
		</form>
	</div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useTournamentStore } from '../../stores/tournaments';
import { useRoomStore } from '../../stores/rooms';

const router = useRouter();
const route = useRoute();
const tournamentStore = useTournamentStore();
const roomStore = useRoomStore();

const isEdit = computed(() => !!route.params.id);
const loading = ref(false);
const error = ref('');

const form = ref({
	room_id: '',
	date: '',
	buyin: 0,
	bounty_count: 0,
	place: null,
	cashout: null,
});

onMounted(async () => {
	try {
		await roomStore.fetchRooms();
	} catch (err) {
		error.value = 'Ошибка загрузки румов';
	}

	if (isEdit.value) {
		try {
			const tournament = await tournamentStore.fetchTournament(route.params.id);
			form.value = {
				room_id: tournament.room_id,
				date: tournament.date,
				buyin: tournament.buyin,
				bounty_count: tournament.bounty_count,
				place: tournament.place,
				cashout: tournament.cashout,
			};
		} catch (err) {
			error.value = 'Ошибка загрузки турнира';
		}
	}
});

const handleSubmit = async () => {
	loading.value = true;
	error.value = '';

	try {
		if (isEdit.value) {
			await tournamentStore.updateTournament(route.params.id, form.value);
		} else {
			await tournamentStore.createTournament(form.value);
		}
		router.push({ name: 'Tournaments' });
	} catch (err) {
		if (err.response?.data?.errors) {
			error.value = Object.values(err.response.data.errors).flat().join(', ');
		} else {
			error.value = err.response?.data?.message || 'Ошибка сохранения';
		}
	} finally {
		loading.value = false;
	}
};
</script>
