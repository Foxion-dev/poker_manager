<template>
	<div>
		<div class="mb-6">
			<button
				@click="$router.push('/packs')"
				class="mb-4 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
			>
				<span class="mr-2">←</span>
				Назад к пакам
			</button>
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
				<div class="flex items-center justify-between">
					<div class="flex-1">
						<h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ pack?.name }}</h2>
						<div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
							<div class="flex items-center">
								<span class="mr-2">📅</span>
								<span>{{ formatDate(pack?.start_date) }}</span>
								<span v-if="pack?.end_date" class="mx-2">—</span>
								<span v-if="pack?.end_date">{{ formatDate(pack?.end_date) }}</span>
							</div>
							<div v-if="pack?.description" class="mt-2 text-gray-500 dark:text-gray-500">
								{{ pack.description }}
							</div>
						</div>
					</div>
					<div class="ml-6">
						<button
							@click="editPack"
							class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors mr-2"
						>
							Редактировать
						</button>
						<button
							@click="deletePack"
							class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
						>
							Удалить
						</button>
					</div>
				</div>
			</div>
		</div>

		<div v-if="loading" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка...</div>
			</div>
		</div>

		<div v-else-if="pack" class="space-y-6">
			<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
					<div class="p-6">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
									Байин
								</p>
								<p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
									{{ formatCurrency(pack.buyin_usd) }}
								</p>
							</div>
							<div class="h-16 w-16 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-lg">
								<span class="text-3xl">💵</span>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
					<div class="p-6">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
									Кэшаут
								</p>
								<p
									class="mt-2 text-3xl font-bold"
									:class="pack.cashout_usd > 0 ? 'text-green-600' : 'text-gray-400'"
								>
									{{ pack.cashout_usd > 0 ? formatCurrency(pack.cashout_usd) : '-' }}
								</p>
							</div>
							<div
								class="h-16 w-16 rounded-xl flex items-center justify-center shadow-lg"
								:class="pack.cashout_usd > 0 
									? 'bg-gradient-to-br from-green-400 to-green-600' 
									: 'bg-gradient-to-br from-gray-400 to-gray-600'"
							>
								<span class="text-3xl">💰</span>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
					<div class="p-6">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
									Профит
								</p>
								<p
									class="mt-2 text-3xl font-bold"
									:class="pack.profit_usd >= 0 ? 'text-green-600' : 'text-red-600'"
								>
									{{ formatCurrency(pack.profit_usd) }}
								</p>
							</div>
							<div
								class="h-16 w-16 rounded-xl flex items-center justify-center shadow-lg"
								:class="pack.profit_usd >= 0 
									? 'bg-gradient-to-br from-green-400 to-green-600' 
									: 'bg-gradient-to-br from-red-400 to-red-600'"
							>
								<span class="text-3xl">📈</span>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
					<div class="p-6">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
									ROI
								</p>
								<p
									class="mt-2 text-3xl font-bold"
									:class="pack.roi >= 0 ? 'text-green-600' : 'text-red-600'"
								>
									{{ pack.roi }}%
								</p>
							</div>
							<div class="h-16 w-16 rounded-xl bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center shadow-lg">
								<span class="text-3xl">📉</span>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
					<div class="p-6">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-gray-600 dark:text-gray-400">
									ITM %
								</p>
								<p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
									{{ pack.itm_percentage }}%
								</p>
							</div>
							<div class="h-16 w-16 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-lg">
								<span class="text-3xl">📊</span>
							</div>
						</div>
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
					<h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Редактировать пак</h3>

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

						<div>
							<label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
								<span class="mr-2">📅</span>
								Диапазон дат
							</label>
							<AppDatePicker
								v-model="packDateRange"
								:range="true"
								:partial-range="true"
								placeholder="Выберите период"
								:clearable="false"
							/>
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
								{{ saving ? 'Сохранение...' : 'Сохранить' }}
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
import { useRoute, useRouter } from 'vue-router';
import { packService } from '../../services/packService';
import { useCurrencyStore } from '../../stores/currencies';
import AppDatePicker from '../AppDatePicker.vue';

const route = useRoute();
const router = useRouter();
const currencyStore = useCurrencyStore();

const pack = ref(null);
const currencies = ref([]);
const loading = ref(false);
const showForm = ref(false);
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

const packDateRange = computed({
	get: () => {
		const start = form.value.start_date ? new Date(form.value.start_date) : null;
		const end = form.value.end_date ? new Date(form.value.end_date) : null;
		if (!start && !end) return null;
		return [start || end, end || start];
	},
	set: (v) => {
		if (!v || !v[0]) {
			form.value.start_date = '';
			form.value.end_date = '';
			return;
		}
		form.value.start_date = v[0].toISOString().split('T')[0];
		form.value.end_date = v[1] ? v[1].toISOString().split('T')[0] : '';
	}
});

const fetchPack = async () => {
	loading.value = true;
	try {
		await Promise.all([
			packService.getById(route.params.id).then(data => {
				pack.value = data;
				form.value = {
					name: data.name,
					start_date: data.start_date,
					end_date: data.end_date || '',
					buyin: data.buyin,
					cashout: data.cashout || null,
					currency_id: data.currency?.id || null,
					description: data.description || '',
				};
			}),
			currencyStore.fetchCurrencies().then(() => currencies.value = currencyStore.currencies),
		]);
	} catch (error) {
		console.error('Error fetching pack:', error);
	} finally {
		loading.value = false;
	}
};

const editPack = () => {
	showForm.value = true;
};

const closeForm = () => {
	showForm.value = false;
};

const savePack = async () => {
	if (!form.value.start_date) {
		alert('Выберите дату начала');
		return;
	}
	saving.value = true;
	try {
		await packService.update(route.params.id, form.value);
		closeForm();
		await fetchPack();
	} catch (error) {
		console.error('Error saving pack:', error);
		alert('Ошибка при сохранении пака');
	} finally {
		saving.value = false;
	}
};

const deletePack = async () => {
	if (!confirm('Вы уверены, что хотите удалить этот пак?')) {
		return;
	}

	try {
		await packService.delete(route.params.id);
		router.push('/packs');
	} catch (error) {
		console.error('Error deleting pack:', error);
		alert('Ошибка при удалении пака');
	}
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
	fetchPack();
});
</script>
