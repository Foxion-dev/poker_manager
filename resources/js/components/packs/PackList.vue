<template>
	<div>
		<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
			<div>
				<h2 class="text-3xl font-bold text-gray-900 dark:text-white">Паки турниров</h2>
				<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
					Управление периодами турниров
				</p>
			</div>
			<button
				@click="openCreateForm"
				class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105"
			>
				<span class="mr-2">➕</span>
				Создать пак
			</button>
		</div>

		<div v-if="loading" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка паков...</div>
			</div>
		</div>

		<div v-else-if="packs.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 border border-gray-100 dark:border-gray-700 text-center">
			<span class="text-6xl mb-4 block">📦</span>
			<p class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Нет паков</p>
			<p class="text-gray-600 dark:text-gray-400 mb-6">Создайте первый пак для группировки турниров</p>
			<button
				@click="openCreateForm"
				class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700"
			>
				Создать пак
			</button>
		</div>

		<div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
			<div
				v-for="pack in packs"
				:key="pack.id"
				class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 cursor-pointer"
				@click="viewPack(pack.id)"
			>
				<div class="p-6">
					<div class="flex items-start justify-between mb-4">
						<div class="flex-1">
							<h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ pack.name }}</h3>
							<div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
								<div class="flex items-center">
									<span class="mr-2">📅</span>
									<span>{{ formatDate(pack.start_date) }}</span>
									<span v-if="pack.end_date" class="mx-2">—</span>
									<span v-if="pack.end_date">{{ formatDate(pack.end_date) }}</span>
								</div>
								<div v-if="pack.description" class="mt-2 text-gray-500 dark:text-gray-500">
									{{ pack.description }}
								</div>
							</div>
						</div>
					</div>

					<div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
						<div>
							<p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Байин</p>
							<p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(pack.buyin_usd) }}</p>
						</div>
						<div>
							<p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Кэшаут</p>
							<p
								class="text-lg font-bold"
								:class="pack.cashout_usd > 0 ? 'text-green-600' : 'text-gray-400'"
							>
								{{ pack.cashout_usd > 0 ? formatCurrency(pack.cashout_usd) : '-' }}
							</p>
						</div>
						<div>
							<p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Профит</p>
							<p
								class="text-lg font-bold"
								:class="pack.profit_usd >= 0 ? 'text-green-600' : 'text-red-600'"
							>
								{{ formatCurrency(pack.profit_usd) }}
							</p>
						</div>
						<div>
							<p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">ROI</p>
							<p
								class="text-lg font-bold"
								:class="pack.roi >= 0 ? 'text-green-600' : 'text-red-600'"
							>
								{{ pack.roi }}%
							</p>
						</div>
					</div>

					<div class="mt-4 flex space-x-2">
						<button
							@click.stop="editPack(pack)"
							class="flex-1 px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
						>
							Редактировать
						</button>
						<button
							@click.stop="deletePack(pack.id)"
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
						{{ editingPack ? 'Редактировать пак' : 'Создать пак' }}
					</h3>

					<form @submit.prevent="savePack" class="space-y-4">
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

						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
									Дата начала *
								</label>
								<input
									v-model="form.start_date"
									type="date"
									required
									class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								/>
							</div>
							<div>
								<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
									Дата окончания
								</label>
								<input
									v-model="form.end_date"
									type="date"
									:min="form.start_date"
									class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								/>
							</div>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
									Байин (сумма загрузки) *
								</label>
								<input
									v-model.number="form.buyin"
									type="number"
									step="0.01"
									min="0"
									required
									class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
									placeholder="0.00"
								/>
							</div>
							<div>
								<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
									Кэшаут (сумма выгрузки)
								</label>
								<input
									v-model.number="form.cashout"
									type="number"
									step="0.01"
									min="0"
									class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
									placeholder="0.00"
								/>
							</div>
						</div>

						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								Валюта
							</label>
							<select
								v-model="form.currency_id"
								class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
							>
								<option :value="null">USD (Доллар США)</option>
								<option v-for="currency in currencies" :key="currency.id" :value="currency.id">
									{{ currency.code }} - {{ currency.name }} ({{ currency.symbol }})
								</option>
							</select>
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
								{{ saving ? 'Сохранение...' : (editingPack ? 'Сохранить' : 'Создать') }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { packService } from '../../services/packService';
import { useCurrencyStore } from '../../stores/currencies';

const router = useRouter();
const currencyStore = useCurrencyStore();

const packs = ref([]);
const currencies = ref([]);
const loading = ref(false);
const showForm = ref(false);
const editingPack = ref(null);
const saving = ref(false);

const form = ref({
	name: '',
	start_date: '',
	end_date: '',
	buyin: 0,
	cashout: null,
	currency_id: null,
	description: '',
});

const fetchPacks = async () => {
	loading.value = true;
	try {
		await Promise.all([
			packService.getAll().then(data => packs.value = data),
			currencyStore.fetchCurrencies().then(() => currencies.value = currencyStore.currencies),
		]);
	} catch (error) {
		console.error('Error fetching packs:', error);
	} finally {
		loading.value = false;
	}
};

const openCreateForm = () => {
	editingPack.value = null;
	form.value = {
		name: '',
		start_date: '',
		end_date: '',
		buyin: 0,
		cashout: null,
		currency_id: null,
		description: '',
	};
	showForm.value = true;
};

const editPack = (pack) => {
	editingPack.value = pack;
	form.value = {
		name: pack.name,
		start_date: pack.start_date,
		end_date: pack.end_date || '',
		buyin: pack.buyin,
		cashout: pack.cashout || null,
		currency_id: pack.currency?.id || null,
		description: pack.description || '',
	};
	showForm.value = true;
};

const closeForm = () => {
	showForm.value = false;
	editingPack.value = null;
};

const savePack = async () => {
	saving.value = true;
	try {
		if (editingPack.value) {
			await packService.update(editingPack.value.id, form.value);
		} else {
			await packService.create(form.value);
		}
		closeForm();
		await fetchPacks();
	} catch (error) {
		console.error('Error saving pack:', error);
		alert('Ошибка при сохранении пака');
	} finally {
		saving.value = false;
	}
};

const deletePack = async (id) => {
	if (!confirm('Вы уверены, что хотите удалить этот пак?')) {
		return;
	}

	try {
		await packService.delete(id);
		await fetchPacks();
	} catch (error) {
		console.error('Error deleting pack:', error);
		alert('Ошибка при удалении пака');
	}
};

const viewPack = (id) => {
	router.push(`/packs/${id}`);
};

const formatCurrency = (value) => {
	return new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'USD',
	}).format(value);
};

const formatDate = (dateString) => {
	const date = new Date(dateString);
	return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

onMounted(() => {
	fetchPacks();
});
</script>
