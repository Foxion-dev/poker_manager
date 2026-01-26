<template>
	<div>
		<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
			<div>
				<h2 class="text-3xl font-bold text-gray-900 dark:text-white">Локации</h2>
				<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
					Управление локациями для проведения турниров
				</p>
			</div>
			<button
				@click="openCreateForm"
				class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105"
			>
				<span class="mr-2">➕</span>
				Создать локацию
			</button>
		</div>

		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-gray-700">
			<div class="flex items-center space-x-4 mb-4">
				<button
					@click="filterPublic = null"
					class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
					:class="filterPublic === null 
						? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
						: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
				>
					Все
				</button>
				<button
					@click="filterPublic = true"
					class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
					:class="filterPublic === true 
						? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
						: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
				>
					Публичные
				</button>
				<button
					@click="filterPublic = false"
					class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
					:class="filterPublic === false 
						? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
						: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
				>
					Мои
				</button>
			</div>
		</div>

		<div v-if="loading" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка локаций...</div>
			</div>
		</div>

		<div v-else-if="filteredLocations.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 border border-gray-100 dark:border-gray-700 text-center">
			<span class="text-6xl mb-4 block">📍</span>
			<p class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Нет локаций</p>
			<p class="text-gray-600 dark:text-gray-400 mb-6">Создайте первую локацию для проведения турниров</p>
			<button
				@click="openCreateForm"
				class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700"
			>
				Создать локацию
			</button>
		</div>

		<div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
			<div
				v-for="location in filteredLocations"
				:key="location.id"
				class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 cursor-pointer"
				@click="viewLocation(location.id)"
			>
				<div class="p-6">
					<div class="flex items-start justify-between mb-4">
						<div class="flex-1">
							<h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ location.name }}</h3>
							<div v-if="location.description" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
								{{ location.description }}
							</div>
							<div class="flex items-center space-x-2">
								<span
									class="px-2 py-1 text-xs font-semibold rounded-full"
									:class="location.is_public 
										? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
										: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
								>
									{{ location.is_public ? 'Публичная' : 'Личная' }}
								</span>
								<span class="text-xs text-gray-500 dark:text-gray-500">
									Создатель: {{ location.user?.name }}
								</span>
							</div>
						</div>
					</div>

					<div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
						<div>
							<p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Турниров</p>
							<p class="text-lg font-bold text-gray-900 dark:text-white">{{ location.tournaments_count || 0 }}</p>
						</div>
						<div>
							<p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Средний байин</p>
							<p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(location.average_buyin) }}</p>
						</div>
					</div>

					<div class="mt-4 flex space-x-2">
						<button
							v-if="location.user_id === currentUser?.id"
							@click.stop="editLocation(location)"
							class="flex-1 px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
						>
							Редактировать
						</button>
						<button
							v-if="location.user_id === currentUser?.id"
							@click.stop="deleteLocation(location.id)"
							class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
						>
							Удалить
						</button>
					</div>
				</div>
			</div>
		</div>

		<div
			v-if="showForm"
			class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
			@click.self="closeForm"
		>
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
				<div class="p-6">
					<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
						{{ editingLocation ? 'Редактировать локацию' : 'Создать локацию' }}
					</h3>

					<form @submit.prevent="saveLocation" class="space-y-4">
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
								Пароль (обязательно для публичных локаций) *
							</label>
							<input
								v-model="form.password"
								type="password"
								:required="form.is_public"
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								placeholder="Введите пароль"
							/>
						</div>

						<div class="flex justify-end space-x-3 pt-4">
							<button
								type="button"
								@click="closeForm"
								class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
							>
								Отмена
							</button>
							<button
								type="submit"
								:disabled="saving"
								class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors"
							>
								{{ saving ? 'Сохранение...' : (editingLocation ? 'Сохранить' : 'Создать') }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { locationService } from '../../services/locationService';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';

const router = useRouter();
const authStore = useAuthStore();
const { user: currentUser } = storeToRefs(authStore);

const locations = ref([]);
const loading = ref(false);
const showForm = ref(false);
const editingLocation = ref(null);
const saving = ref(false);
const filterPublic = ref(null);

const form = ref({
	name: '',
	description: '',
	is_public: false,
	password: '',
});

const filteredLocations = computed(() => {
	if (filterPublic.value === null) {
		return locations.value;
	}
	return locations.value.filter(loc => loc.is_public === filterPublic.value);
});

const fetchLocations = async () => {
	loading.value = true;
	try {
		locations.value = await locationService.getAll();
	} catch (error) {
		console.error('Error fetching locations:', error);
	} finally {
		loading.value = false;
	}
};

const openCreateForm = () => {
	editingLocation.value = null;
	form.value = {
		name: '',
		description: '',
		is_public: false,
		password: '',
	};
	showForm.value = true;
};

const editLocation = (location) => {
	editingLocation.value = location;
	form.value = {
		name: location.name,
		description: location.description || '',
		is_public: location.is_public,
		password: '',
	};
	showForm.value = true;
};

const closeForm = () => {
	showForm.value = false;
	editingLocation.value = null;
};

const saveLocation = async () => {
	saving.value = true;
	try {
		const data = { ...form.value };
		if (editingLocation.value && !data.password) {
			delete data.password;
		}
		if (editingLocation.value) {
			await locationService.update(editingLocation.value.id, data);
		} else {
			await locationService.create(data);
		}
		closeForm();
		await fetchLocations();
	} catch (error) {
		console.error('Error saving location:', error);
		alert('Ошибка при сохранении локации');
	} finally {
		saving.value = false;
	}
};

const deleteLocation = async (id) => {
	if (!confirm('Вы уверены, что хотите удалить эту локацию? Все турниры в ней также будут удалены.')) {
		return;
	}

	try {
		await locationService.delete(id);
		await fetchLocations();
	} catch (error) {
		console.error('Error deleting location:', error);
		alert('Ошибка при удалении локации');
	}
};

const viewLocation = (id) => {
	router.push(`/locations/${id}`);
};

const formatCurrency = (value) => {
	return new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'USD',
	}).format(value);
};

onMounted(() => {
	fetchLocations();
});
</script>
