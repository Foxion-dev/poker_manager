<template>
	<div class="px-4 py-6 sm:px-0">
		<h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
			{{ isEdit ? 'Редактировать турнир' : 'Создать турнир' }}
		</h2>

		<form @submit.prevent="handleSubmit" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
			<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
						Рум
					</label>
					<select
						v-model="form.room_id"
						required
						class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
					>
						<option value="">Выберите рум</option>
						<option v-for="room in roomStore.rooms" :key="room.id" :value="room.id">
							{{ room.icon }} {{ room.name }}
						</option>
					</select>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
						Дата
					</label>
					<input
						v-model="form.date"
						type="date"
						required
						class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
					/>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
						Байин
					</label>
					<input
						v-model.number="form.buyin"
						type="number"
						step="0.01"
						min="0"
						required
						class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
					/>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
						Количество баунти
					</label>
					<input
						v-model.number="form.bounty_count"
						type="number"
						min="0"
						class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
					/>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
						Место
					</label>
					<input
						v-model.number="form.place"
						type="number"
						min="1"
						class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
					/>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
						Кэшаут
					</label>
					<input
						v-model.number="form.cashout"
						type="number"
						step="0.01"
						min="0"
						class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
					/>
				</div>
			</div>

			<div v-if="error" class="mt-4 text-red-600 text-sm">
				{{ error }}
			</div>

			<div class="mt-6 flex justify-end space-x-3">
				<router-link
					to="/tournaments"
					class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
				>
					Отмена
				</router-link>
				<button
					type="submit"
					:disabled="loading"
					class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
				>
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
