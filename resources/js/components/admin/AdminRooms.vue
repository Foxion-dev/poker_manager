<template>
	<div>
		<div class="flex justify-between items-center mb-6">
			<h2 class="text-2xl font-bold text-gray-900 dark:text-white">Управление румами</h2>
			<button
				@click="showForm = true; editingRoom = null"
				class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 font-medium"
			>
				+ Добавить рум
			</button>
		</div>

		<div v-if="showForm" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-gray-700">
			<h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
				{{ editingRoom ? 'Редактировать рум' : 'Создать рум' }}
			</h3>
			<form @submit.prevent="saveRoom" class="space-y-4">
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Название
					</label>
					<input
						v-model="form.name"
						type="text"
						required
						class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
					/>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Иконка (эмодзи)
					</label>
					<input
						v-model="form.icon"
						type="text"
						maxlength="10"
						class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
					/>
				</div>
				<div class="flex space-x-3">
					<button
						type="submit"
						:disabled="loading"
						class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 font-medium disabled:opacity-50"
					>
						{{ loading ? 'Сохранение...' : 'Сохранить' }}
					</button>
					<button
						type="button"
						@click="cancelForm"
						class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200 font-medium"
					>
						Отмена
					</button>
				</div>
			</form>
		</div>

		<div v-if="loading && !showForm" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка...</div>
			</div>
		</div>

		<div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
			<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
				<thead class="bg-gray-50 dark:bg-gray-700">
					<tr>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Иконка
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Название
						</th>
						<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Действия
						</th>
					</tr>
				</thead>
				<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
					<tr v-for="room in rooms" :key="room.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
						<td class="px-6 py-4 whitespace-nowrap text-2xl">
							{{ room.icon || '🏠' }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
							{{ room.name }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
							<button
								@click="editRoom(room)"
								class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4"
							>
								Редактировать
							</button>
							<button
								@click="deleteRoom(room)"
								class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
							>
								Удалить
							</button>
						</td>
					</tr>
					<tr v-if="rooms.length === 0">
						<td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
							Нет румов
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const rooms = ref([]);
const loading = ref(false);
const showForm = ref(false);
const editingRoom = ref(null);
const form = ref({
	name: '',
	icon: '',
});

const fetchRooms = async () => {
	loading.value = true;
	try {
		const response = await api.get('/rooms');
		rooms.value = response.data;
	} catch (error) {
		console.error('Error fetching rooms:', error);
	} finally {
		loading.value = false;
	}
};

const saveRoom = async () => {
	loading.value = true;
	try {
		if (editingRoom.value) {
			await api.put(`/rooms/${editingRoom.value.id}`, form.value);
		} else {
			await api.post('/rooms', form.value);
		}
		await fetchRooms();
		cancelForm();
	} catch (error) {
		console.error('Error saving room:', error);
		alert('Ошибка при сохранении рума');
	} finally {
		loading.value = false;
	}
};

const editRoom = (room) => {
	editingRoom.value = room;
	form.value = {
		name: room.name,
		icon: room.icon || '',
	};
	showForm.value = true;
};

const deleteRoom = async (room) => {
	if (!confirm(`Вы уверены, что хотите удалить рум "${room.name}"?`)) {
		return;
	}

	try {
		await api.delete(`/rooms/${room.id}`);
		await fetchRooms();
	} catch (error) {
		console.error('Error deleting room:', error);
		alert('Ошибка при удалении рума');
	}
};

const cancelForm = () => {
	showForm.value = false;
	editingRoom.value = null;
	form.value = {
		name: '',
		icon: '',
	};
};

onMounted(() => {
	fetchRooms();
});
</script>
