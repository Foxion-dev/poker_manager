<template>
	<section class="mb-8">
		<div v-if="loading && !loaded" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка...</div>
			</div>
		</div>

		<div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 max-w-2xl">
			<p class="text-gray-600 dark:text-gray-400 mb-6">
				Создайте бота через <a href="https://t.me/BotFather" target="_blank" rel="noopener" class="text-indigo-600 dark:text-indigo-400 hover:underline">@BotFather</a>, получите токен и вставьте его ниже. После включения пользователи смогут привязать аккаунт к боту и добавлять турниры через Telegram.
			</p>
			<form @submit.prevent="save" class="space-y-4">
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Токен бота (BotFather)
					</label>
					<input
						v-model="form.bot_token"
						type="password"
						autocomplete="off"
						:placeholder="settings.has_token ? '••••••••••••••••' : '123456789:ABCdef...'"
						class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
					/>
					<p v-if="settings.has_token" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
						Оставьте пустым, чтобы не менять текущий токен
					</p>
				</div>
				<div class="flex items-center">
					<input
						v-model="form.is_enabled"
						type="checkbox"
						id="is_enabled"
						class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700"
					/>
					<label for="is_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
						Бот включён (пользователи могут привязывать аккаунт и добавлять турниры)
					</label>
				</div>
				<div class="flex items-center gap-3 pt-2">
					<button
						type="submit"
						:disabled="saving"
						class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 font-medium disabled:opacity-50"
					>
						{{ saving ? 'Сохранение...' : 'Сохранить' }}
					</button>
					<span v-if="saveSuccess" class="text-sm text-green-600 dark:text-green-400">Сохранено</span>
					<span v-if="saveError" class="text-sm text-red-600 dark:text-red-400">{{ saveError }}</span>
				</div>
			</form>
		</div>
	</section>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const loading = ref(true);
const loaded = ref(false);
const saving = ref(false);
const saveSuccess = ref(false);
const saveError = ref('');
const settings = reactive({
	has_token: false,
	is_enabled: false,
});
const form = reactive({
	bot_token: '',
	is_enabled: false,
});

const fetchSettings = async () => {
	loading.value = true;
	try {
		const { data } = await api.get('/admin/telegram-settings');
		settings.has_token = data.has_token;
		settings.is_enabled = data.is_enabled;
		form.is_enabled = data.is_enabled;
	} catch (e) {
		saveError.value = 'Не удалось загрузить настройки';
	} finally {
		loading.value = false;
		loaded.value = true;
	}
};

const save = async () => {
	saving.value = true;
	saveSuccess.value = false;
	saveError.value = '';
	try {
		const payload = { is_enabled: form.is_enabled };
		if (form.bot_token) {
			payload.bot_token = form.bot_token;
		}
		const { data } = await api.put('/admin/telegram-settings', payload);
		settings.has_token = data.has_token;
		settings.is_enabled = data.is_enabled;
		form.is_enabled = data.is_enabled;
		form.bot_token = '';
		saveSuccess.value = true;
		setTimeout(() => { saveSuccess.value = false; }, 3000);
	} catch (e) {
		saveError.value = e.response?.data?.message || 'Ошибка при сохранении';
	} finally {
		saving.value = false;
	}
};

onMounted(() => {
	fetchSettings();
});
</script>
